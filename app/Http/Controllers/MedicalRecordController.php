<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Validator;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\NursingAssessment;
use App\Models\Service;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::whereHas('medicalRecords')
            ->withCount('medicalRecords')
            ->with([
                'medicalRecords' => function ($q) {
                    $q->latest()->limit(1);
                }
            ]);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('no_rm', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort functionality
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'most_visits':
                $query->orderBy('medical_records_count', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('updated_at', 'ASC');
                break;
            case 'name':
                $query->orderBy('name', 'ASC');
                break;
            default: // latest
                $query->orderBy('updated_at', 'DESC');
                break;
        }

        $patients = $query->paginate(10)->withQueryString();
        $title = "Rekam Medis";
        $description = "Kelola rekam medis pasien";

        return view('pages.clinic.medical-records.index', compact('title', 'description', 'patients', 'sort'));
    }

    /**
     * "Pasien Menunggu" for doctors: patients sent by the nurse (ready) and those being examined.
     * A login linked to a doctor sees only that doctor's patients unless ?all=1.
     */
    public function queue(Request $request)
    {
        $user = $request->user();
        $showAll = $request->boolean('all') || ! $user->doctor_id;
        $doctorFilter = $showAll ? ($request->integer('doctor_id') ?: null) : $user->doctor_id;

        $appointments = Appointment::with(['patient', 'doctor', 'poli', 'nurse', 'nursingAssessment'])
            ->today()
            ->whereIn('status', ['ready', 'in_progress'])
            ->when($doctorFilter, fn ($q, $id) => $q->where('doctor_id', $id))
            ->get()
            // Being examined first, then most urgent triage, then queue order
            ->sortBy(fn ($a) => [
                $a->status === 'in_progress' ? 0 : 1,
                NursingAssessment::triagePriority($a->nursingAssessment?->triage_level),
                $a->queue_number,
            ])
            ->values();

        // Still with the nurse: shown so the doctor knows who is coming
        $atNurse = Appointment::today()
            ->whereIn('status', ['waiting', 'screening'])
            ->count();

        $doctors = Doctor::active()->orderBy('name')->get();
        $title = 'Pasien Menunggu';
        $description = 'Pasien yang sudah diperiksa perawat';

        return view('pages.clinic.medical-records.queue', compact('title', 'description', 'appointments', 'atNurse', 'doctors', 'showAll', 'doctorFilter'));
    }

    public function create(Request $request, $language, $appointmentId = null)
    {
        if (! $appointmentId) {
            return redirect()->route('clinic.medical-records.queue', app()->getLocale());
        }

        $appointment = Appointment::with(['patient', 'doctor', 'poli', 'nurse', 'registeredBy', 'nursingAssessment.nurse', 'nursingAssessment.poli', 'nursingAssessment.doctor'])
            ->findOrFail($appointmentId);

        if ($existing = MedicalRecord::where('appointment_id', $appointment->id)->first()) {
            return redirect()->route('clinic.medical-records.show', [app()->getLocale(), $existing->id])
                ->with('error', 'Rekam medis untuk antrian ini sudah ada. Gunakan tombol Edit bila perlu koreksi.');
        }
        if (! in_array($appointment->status, Appointment::OPEN_STATUSES, true)) {
            return redirect()->route('clinic.appointments.show', [app()->getLocale(), $appointment->id])
                ->with('error', 'Antrian ini sudah selesai atau dibatalkan.');
        }

        $title = "Pemeriksaan Dokter";
        $description = "Form pemeriksaan pasien";

        return view('pages.clinic.medical-records.create', array_merge(
            compact('title', 'description', 'appointment'),
            $this->formData($appointment)
        ));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validators->fails()) {
            return redirect()->back()->withErrors($validators)->withInput();
        }

        try {
            $record = DB::transaction(function () use ($request) {
                // Lock the appointment row so a double submit cannot create two records
                $appointment = Appointment::lockForUpdate()->findOrFail($request->appointment_id);

                if (! in_array($appointment->status, Appointment::OPEN_STATUSES, true)) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'Antrian ini sudah selesai atau dibatalkan, rekam medis tidak bisa dibuat.',
                    ]);
                }

                if (MedicalRecord::where('appointment_id', $appointment->id)->exists()) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'Rekam medis untuk antrian ini sudah ada.',
                    ]);
                }

                // Doctor chosen by the nurse; otherwise the one picked on the form or linked to this login
                $doctorId = $appointment->doctor_id ?: ($request->doctor_id ?: $request->user()->doctor_id);
                if (! $doctorId) {
                    throw ValidationException::withMessages(['doctor_id' => 'Pilih dokter pemeriksa.']);
                }

                $record = new MedicalRecord();
                $record->appointment_id = $appointment->id;
                $record->patient_id = $appointment->patient_id;
                $record->doctor_id = $doctorId;
                $this->fillClinicalFields($record, $request);

                [$prescriptions, $qtyPerMedicine] = $this->normalizePrescriptions($request);
                $record->prescriptions = $prescriptions;
                $record->services_used = $this->normalizeServices($request);
                $record->save();

                $this->applyStockChanges([], $qtyPerMedicine, $record, "Resep {$appointment->queue_number} - {$appointment->patient?->name}");

                $appointment->doctor_id = $appointment->doctor_id ?: $doctorId;
                $appointment->status = 'completed';
                $appointment->save();

                return $record;
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('clinic.medical-records.show', [app()->getLocale(), $record->id])
            ->with('success', 'Rekam medis berhasil disimpan!');
    }

    public function edit($language, $id)
    {
        $record = MedicalRecord::with(['appointment.patient', 'appointment.nurse', 'appointment.registeredBy', 'appointment.poli', 'appointment.payment',
            'appointment.nursingAssessment.nurse', 'doctor', 'editor'])->findOrFail($id);
        $this->authorizeEdit($record);

        $appointment = $record->appointment;
        $itemsLocked = $this->itemsLocked($appointment);

        $title = 'Edit Rekam Medis';
        $description = 'Koreksi rekam medis ' . $appointment?->queue_number;

        return view('pages.clinic.medical-records.edit', array_merge(
            compact('title', 'description', 'record', 'appointment', 'itemsLocked'),
            $this->formData($appointment, $record)
        ));
    }

    /**
     * Correct a saved record. Clinical text can always be fixed; medicines and services only
     * while the visit has no active payment (otherwise refund first), and every stock difference
     * goes through the stock card.
     */
    public function update(Request $request, $language, $id)
    {
        $validators = Validator::make($request->all(), $this->rules(false), $this->messages());

        if ($validators->fails()) {
            return redirect()->back()->withErrors($validators)->withInput();
        }

        try {
            $record = DB::transaction(function () use ($request, $id) {
                $record = MedicalRecord::lockForUpdate()->findOrFail($id);
                $this->authorizeEdit($record);
                $appointment = Appointment::withTrashed()->lockForUpdate()->findOrFail($record->appointment_id);

                $this->fillClinicalFields($record, $request);

                if (! $this->itemsLocked($appointment)) {
                    $oldQty = [];
                    foreach ($record->prescriptions ?? [] as $line) {
                        if (! empty($line['medicine_id'])) {
                            $oldQty[(int) $line['medicine_id']] = ($oldQty[(int) $line['medicine_id']] ?? 0) + (int) ($line['qty'] ?? 0);
                        }
                    }

                    [$prescriptions, $newQty] = $this->normalizePrescriptions($request);
                    $services = $this->normalizeServices($request);
                    $record->prescriptions = $prescriptions;
                    $record->services_used = $services;
                    $this->applyStockChanges($oldQty, $newQty, $record, "Koreksi resep {$appointment->queue_number} - {$appointment->patient?->name}");
                }

                $record->edited_by = $request->user()->id;
                $record->edited_at = now();
                $record->save();

                return $record;
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('clinic.medical-records.show', [app()->getLocale(), $record->id])
            ->with('success', 'Rekam medis berhasil diperbarui. Perubahan tercatat di Audit Log.');
    }

    /** Medicines, services, doctors and history used by the create/edit form */
    private function formData(?Appointment $appointment, ?MedicalRecord $record = null): array
    {
        $usedMedicineIds = collect($record?->prescriptions ?? [])->pluck('medicine_id')->filter()->all();
        $usedServiceIds = collect($record?->services_used ?? [])->pluck('service_id')->filter()->all();

        // Prescribable now, plus anything already on this record so it still shows when editing
        $medicines = Medicine::withTrashed()
            ->where(function ($q) use ($usedMedicineIds) {
                $q->where(function ($q) {
                    $q->whereNull('deleted_at')
                        ->where('status', 'active')
                        ->where('stock', '>', 0)
                        ->where(function ($q) {
                            $q->whereNull('expired_date')->orWhereDate('expired_date', '>=', today());
                        });
                })->orWhereIn('id', $usedMedicineIds);
            })
            ->orderBy('name')
            ->get();

        $services = Service::withTrashed()
            ->where(function ($q) use ($usedServiceIds) {
                $q->where(fn ($q) => $q->whereNull('deleted_at')->where('status', 'active'))
                    ->orWhereIn('id', $usedServiceIds);
            })
            ->orderBy('name')
            ->get();

        $doctors = Doctor::active()->orderBy('name')->get();

        $history = $appointment
            ? MedicalRecord::with('doctor')
                ->where('patient_id', $appointment->patient_id)
                ->where('appointment_id', '!=', $appointment->id)
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        return [
            'patient' => $appointment?->patient,
            'assessment' => $appointment?->nursingAssessment,
            'medicines' => $medicines,
            'services' => $services,
            'doctors' => $doctors,
            'history' => $history,
        ];
    }

    private function rules(bool $creating = true): array
    {
        $rules = array_merge([
            'anamnesa' => 'nullable|string',
            'diagnosis' => 'required|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
            'prescriptions' => 'nullable|array',
            // Availability (archived, inactive, expired, stock) is checked for added quantities in applyStockChanges
            'prescriptions.*.medicine_id' => ['nullable', 'integer', Rule::exists('medicines', 'id')],
            'prescriptions.*.qty' => 'nullable|integer|min:1|max:10000',
            'prescriptions.*.dosage' => 'nullable|string|max:255',
            'prescriptions.*.notes' => 'nullable|string|max:255',
            'services_used' => 'nullable|array',
            'services_used.*.service_id' => ['nullable', 'integer', Rule::exists('services', 'id')],
            'services_used.*.qty' => 'nullable|integer|min:1|max:1000',
        ], NursingAssessment::vitalRules());

        if ($creating) {
            $rules['appointment_id'] = 'required|exists:appointments,id';
            $rules['doctor_id'] = ['nullable', 'integer', Rule::exists('doctors', 'id')->where('status', 'active')->whereNull('deleted_at')];
        }

        return $rules;
    }

    private function messages(): array
    {
        return ['blood_pressure.regex' => 'Format tekanan darah harus seperti 120/80.'];
    }

    private function fillClinicalFields(MedicalRecord $record, Request $request): void
    {
        foreach (['anamnesa', 'diagnosis', 'blood_pressure', 'body_temperature', 'pulse_rate', 'respiratory_rate', 'height', 'weight', 'treatment', 'notes'] as $field) {
            $value = $request->input($field);
            $record->{$field} = $value === '' ? null : $value;
        }
    }

    /** @return array{0: array, 1: array<int,int>} [prescription lines, total qty per medicine id] */
    private function normalizePrescriptions(Request $request): array
    {
        $prescriptions = [];
        $qtyPerMedicine = [];
        foreach ($request->input('prescriptions', []) as $prescription) {
            if (empty($prescription['medicine_id'])) {
                continue;
            }
            $line = [
                'medicine_id' => (int) $prescription['medicine_id'],
                'qty' => max(1, (int) ($prescription['qty'] ?? 1)),
                'dosage' => (string) ($prescription['dosage'] ?? ''),
                'notes' => (string) ($prescription['notes'] ?? ''),
            ];
            $prescriptions[] = $line;
            $qtyPerMedicine[$line['medicine_id']] = ($qtyPerMedicine[$line['medicine_id']] ?? 0) + $line['qty'];
        }

        return [$prescriptions, $qtyPerMedicine];
    }

    private function normalizeServices(Request $request): array
    {
        $servicesUsed = [];
        foreach ($request->input('services_used', []) as $serviceUsed) {
            if (! empty($serviceUsed['service_id'])) {
                $servicesUsed[] = [
                    'service_id' => (int) $serviceUsed['service_id'],
                    'qty' => max(1, (int) ($serviceUsed['qty'] ?? 1)),
                ];
            }
        }

        return $servicesUsed;
    }

    /**
     * Move stock by the difference between the old and new quantities per medicine.
     * Added quantities must be prescribable (not archived, active, not expired, in stock);
     * reduced quantities go back to stock as "return".
     */
    private function applyStockChanges(array $oldQty, array $newQty, MedicalRecord $record, string $note): void
    {
        $ids = array_unique(array_merge(array_keys($oldQty), array_keys($newQty)));
        if (! $ids) {
            return;
        }

        $medicines = Medicine::withTrashed()->whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

        foreach ($ids as $medicineId) {
            $delta = ($newQty[$medicineId] ?? 0) - ($oldQty[$medicineId] ?? 0);
            $medicine = $medicines[$medicineId] ?? null;
            if ($delta <= 0 || ! $medicine) {
                continue;
            }
            if ($medicine->trashed() || $medicine->status !== 'active') {
                throw ValidationException::withMessages(['prescriptions' => "Obat {$medicine->name} tidak aktif."]);
            }
            if ($medicine->expired_date && $medicine->expired_date->lt(today())) {
                throw ValidationException::withMessages(['prescriptions' => "Obat {$medicine->name} sudah kedaluwarsa."]);
            }
            if ($medicine->stock < $delta) {
                throw ValidationException::withMessages([
                    'prescriptions' => "Stok {$medicine->name} tidak cukup (tersedia {$medicine->stock}, dibutuhkan {$delta}).",
                ]);
            }
        }

        foreach ($ids as $medicineId) {
            $delta = ($newQty[$medicineId] ?? 0) - ($oldQty[$medicineId] ?? 0);
            if ($delta > 0) {
                Medicine::moveStock($medicineId, -$delta, 'out', $record, $note);
            } elseif ($delta < 0) {
                Medicine::moveStock($medicineId, -$delta, 'return', $record, $note);
            }
        }
    }

    /** Medicines/services are locked once the visit has an active (pending or paid) payment */
    private function itemsLocked(?Appointment $appointment): bool
    {
        return $appointment && $appointment->payments()->whereIn('status', ['pending', 'paid'])->exists();
    }

    /** Administrators, users not linked to a doctor, or the examining doctor */
    private function authorizeEdit(MedicalRecord $record): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || ! $user->doctor_id || (int) $user->doctor_id === (int) $record->doctor_id) {
            return;
        }

        abort(403, 'Hanya dokter pemeriksa atau Administrator yang dapat mengedit rekam medis ini.');
    }

    public function show($language, $id)
    {
        $record = MedicalRecord::with(['patient', 'doctor', 'appointment'])->findOrFail($id);
        $title = "Detail Rekam Medis";
        $description = "Informasi lengkap rekam medis";

        // Get prescription details
        $prescriptionDetails = $record->getPrescriptionDetails();
        $serviceDetails = $record->getServiceDetails();

        // Get patient history (previous medical records)
        $patientHistory = MedicalRecord::with(['doctor'])
            ->where('patient_id', $record->patient_id)
            ->where('id', '!=', $id) // Exclude current record
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        return view('pages.clinic.medical-records.show', compact(
            'title',
            'description',
            'record',
            'prescriptionDetails',
            'serviceDetails',
            'patientHistory'
        ));
    }

    public function patientHistory($language, $patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $records = MedicalRecord::with(['doctor', 'appointment'])
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'DESC')
            ->get();
        $title = "Riwayat Rekam Medis";
        $description = "Riwayat medis pasien: " . $patient->name;

        return view('pages.clinic.medical-records.patient-history', compact(
            'title',
            'description',
            'patient',
            'records'
        ));
    }

    public function print($language, $id)
    {
        $record = MedicalRecord::with(['patient', 'doctor', 'appointment'])->findOrFail($id);
        $prescriptionDetails = $record->getPrescriptionDetails();
        $serviceDetails = $record->getServiceDetails();

        return view('pages.clinic.medical-records.print', compact(
            'record',
            'prescriptionDetails',
            'serviceDetails'
        ));
    }
}

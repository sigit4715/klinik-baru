<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Validator;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Medicine;
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

    public function create($language, $appointmentId = null)
    {
        $title = "Buat Rekam Medis";
        $description = "Form pemeriksaan pasien";

        $appointment = null;
        $patient = null;
        $doctor = null;

        if ($appointmentId) {
            $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($appointmentId);
            $patient = $appointment->patient;
            $doctor = $appointment->doctor;
        }

        $medicines = Medicine::active()
            ->where('stock', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expired_date')->orWhereDate('expired_date', '>=', today());
            })
            ->get();
        $services = Service::active()->get();

        return view('pages.clinic.medical-records.create', compact(
            'title',
            'description',
            'appointment',
            'patient',
            'doctor',
            'medicines',
            'services'
        ));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,id',
            'anamnesa' => 'nullable|string',
            'diagnosis' => 'required|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
            'blood_pressure' => ['nullable', 'string', 'max:20', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'body_temperature' => 'nullable|numeric|between:30,45',
            'pulse_rate' => 'nullable|integer|between:20,250',
            'respiratory_rate' => 'nullable|integer|between:5,80',
            'height' => 'nullable|numeric|between:20,250',
            'weight' => 'nullable|numeric|between:0.5,400',
            'prescriptions' => 'nullable|array',
            'prescriptions.*.medicine_id' => 'nullable|integer|exists:medicines,id',
            'prescriptions.*.qty' => 'nullable|integer|min:1|max:10000',
            'prescriptions.*.dosage' => 'nullable|string|max:255',
            'prescriptions.*.notes' => 'nullable|string|max:255',
            'services_used' => 'nullable|array',
            'services_used.*.service_id' => 'nullable|integer|exists:services,id',
            'services_used.*.qty' => 'nullable|integer|min:1|max:1000',
        ], [
            'blood_pressure.regex' => 'Format tekanan darah harus seperti 120/80.',
        ]);

        if ($validators->fails()) {
            return redirect()->back()->withErrors($validators)->withInput();
        }

        try {
            $record = DB::transaction(function () use ($request) {
                // Lock the appointment row so a double submit cannot create two records
                $appointment = Appointment::lockForUpdate()->findOrFail($request->appointment_id);

                if ($appointment->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'Antrian ini sudah dibatalkan, rekam medis tidak bisa dibuat.',
                    ]);
                }

                if (MedicalRecord::where('appointment_id', $appointment->id)->exists()) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'Rekam medis untuk antrian ini sudah ada.',
                    ]);
                }

                $record = new MedicalRecord();
                $record->appointment_id = $appointment->id;
                $record->patient_id = $appointment->patient_id;
                $record->doctor_id = $appointment->doctor_id;
                $record->anamnesa = $request->anamnesa;
                $record->diagnosis = $request->diagnosis;
                $record->blood_pressure = $request->blood_pressure;
                $record->body_temperature = $request->body_temperature;
                $record->pulse_rate = $request->pulse_rate;
                $record->respiratory_rate = $request->respiratory_rate;
                $record->height = $request->height;
                $record->weight = $request->weight;
                $record->treatment = $request->treatment;
                $record->notes = $request->notes;

                // Prescriptions: merge duplicate lines, then check and reduce stock under a row lock
                $prescriptions = [];
                foreach ($request->input('prescriptions', []) as $prescription) {
                    if (empty($prescription['medicine_id'])) {
                        continue;
                    }
                    $prescriptions[] = [
                        'medicine_id' => (int) $prescription['medicine_id'],
                        'qty' => (int) ($prescription['qty'] ?? 1),
                        'dosage' => $prescription['dosage'] ?? '',
                        'notes' => $prescription['notes'] ?? '',
                    ];
                }

                $qtyPerMedicine = [];
                foreach ($prescriptions as $prescription) {
                    $qtyPerMedicine[$prescription['medicine_id']] = ($qtyPerMedicine[$prescription['medicine_id']] ?? 0) + $prescription['qty'];
                }

                $medicines = Medicine::whereIn('id', array_keys($qtyPerMedicine))->lockForUpdate()->get()->keyBy('id');
                foreach ($qtyPerMedicine as $medicineId => $qty) {
                    $medicine = $medicines[$medicineId];

                    if ($medicine->status !== 'active') {
                        throw ValidationException::withMessages(['prescriptions' => "Obat {$medicine->name} tidak aktif."]);
                    }
                    if ($medicine->expired_date && $medicine->expired_date->lt(today())) {
                        throw ValidationException::withMessages(['prescriptions' => "Obat {$medicine->name} sudah kedaluwarsa."]);
                    }
                    if ($medicine->stock < $qty) {
                        throw ValidationException::withMessages([
                            'prescriptions' => "Stok {$medicine->name} tidak cukup (tersedia {$medicine->stock}, diminta {$qty}).",
                        ]);
                    }

                    $medicine->decrement('stock', $qty);
                }
                $record->prescriptions = $prescriptions;

                $servicesUsed = [];
                foreach ($request->input('services_used', []) as $serviceUsed) {
                    if (!empty($serviceUsed['service_id'])) {
                        $servicesUsed[] = [
                            'service_id' => (int) $serviceUsed['service_id'],
                            'qty' => (int) ($serviceUsed['qty'] ?? 1),
                        ];
                    }
                }
                $record->services_used = $servicesUsed;

                $record->save();

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

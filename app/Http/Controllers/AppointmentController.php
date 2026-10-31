<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\ClinicSetting;
use App\Models\NursingAssessment;
use App\Models\User;

class AppointmentController extends Controller
{
    public function index()
    {
        $per_page = session('pagination_per_page') ?? 20;
        $appointments = Appointment::with(['patient', 'doctor', 'medicalRecord'])
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('queue_number', 'ASC')
            ->paginate($per_page);
        $title = "Daftar Antrian";
        $description = "Kelola antrian pasien";

        return view('pages.clinic.appointments.index', compact('title', 'description', 'appointments'));
    }

    public function today(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'poli', 'nurse', 'nursingAssessment', 'payment'])
            ->today();

        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(function ($q) use ($like) {
                $q->where('queue_number', 'like', $like)
                    ->orWhereHas('patient', function ($p) use ($like) {
                        $p->where('name', 'like', $like)
                            ->orWhere('no_rm', 'like', $like)
                            ->orWhere('nik', 'like', $like);
                    });
            });
        }

        // Open steps first in flow order, then finished/cancelled
        $appointments = $query->orderByRaw("CASE status
                WHEN 'in_progress' THEN 1
                WHEN 'ready' THEN 2
                WHEN 'screening' THEN 3
                WHEN 'waiting' THEN 4
                WHEN 'completed' THEN 5
                ELSE 6
            END")
            ->orderBy('queue_number', 'ASC')
            ->get();
        $title = "Antrian Hari Ini";
        $description = "Daftar antrian pasien hari ini";
        $nurses = User::activeWithPermission('nursing.manage');

        return view('pages.clinic.appointments.today', compact('title', 'description', 'appointments', 'nurses'));
    }

    /**
     * Registration: patient + nurse only. Time and the registering user are recorded automatically;
     * the nurse fills in complaint, vital signs, triage, poli and doctor.
     */
    public function create(Request $request)
    {
        $title = "Buat Antrian Baru";
        $description = "Form pendaftaran antrian";
        $nurses = User::activeWithPermission('nursing.manage');
        $queue_number = Appointment::generateQueueNumber();

        // Only the selected patient is rendered; the dropdown searches the rest by name/NIK/No. RM
        $selectedPatientId = old('patient_id', $request->query('patient_id'));
        $selectedPatient = $selectedPatientId ? Patient::find($selectedPatientId) : null;

        return view('pages.clinic.appointments.create', compact('title', 'description', 'nurses', 'queue_number', 'selectedPatient'));
    }

    public function store(Request $request)
    {
        $nurseIds = User::activeWithPermission('nursing.manage')->pluck('id')->all();

        $validators = Validator::make($request->all(), [
            'patient_id' => ['required', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            // Required as soon as the clinic has at least one nurse account
            'nurse_id' => [$nurseIds ? 'required' : 'nullable', 'integer', Rule::in($nurseIds)],
            'appointment_date' => 'required|date|after_or_equal:today',
        ], [
            'nurse_id.required' => 'Pilih perawat yang akan menangani pasien.',
            'nurse_id.in' => 'Perawat tidak ditemukan atau tidak aktif.',
            'appointment_date.after_or_equal' => 'Tanggal kunjungan tidak boleh sebelum hari ini.',
        ]);

        $validators->after(function ($validator) use ($request) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $duplicate = Appointment::where('patient_id', $request->patient_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereIn('status', Appointment::OPEN_STATUSES)
                ->exists();
            if ($duplicate) {
                $validator->errors()->add('patient_id', 'Pasien ini masih punya antrian yang belum selesai pada tanggal tersebut.');
            }
        });

        if ($validators->fails()) {
            return redirect()->route('clinic.appointments.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $appointment = new Appointment();
        $appointment->patient_id = $request->patient_id;
        $appointment->nurse_id = $request->nurse_id ?: null;
        $appointment->registered_by = auth()->id();
        $appointment->appointment_date = $request->appointment_date;
        $appointment->appointment_time = now()->format('H:i:s');
        $appointment->status = 'waiting';
        $appointment->saveWithUniqueQueueNumber();

        return redirect()->route('clinic.appointments.today', app()->getLocale())
            ->with('success', "Antrian {$appointment->queue_number} berhasil dibuat.")
            ->with('print_appointment_id', $appointment->id);
    }

    public function show($language, $id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'poli', 'nurse', 'registeredBy', 'nursingAssessment.nurse', 'medicalRecord', 'payment'])->findOrFail($id);
        $title = "Detail Antrian";
        $description = "Informasi lengkap antrian";

        return view('pages.clinic.appointments.show', compact('title', 'description', 'appointment'));
    }

    public function edit($language, $id)
    {
        $appointment = Appointment::with(['patient', 'nurse'])->findOrFail($id);

        if (! $appointment->isAtNurseStage()) {
            return redirect()->route('clinic.appointments.show', [app()->getLocale(), $id])
                ->with('error', 'Antrian yang sudah diperiksa perawat tidak bisa diubah dari pendaftaran.');
        }

        $nurses = User::activeWithPermission('nursing.manage');
        // Keep the current nurse selectable even if the account was deactivated since
        if ($appointment->nurse && ! $nurses->contains('id', $appointment->nurse_id)) {
            $nurses->push($appointment->nurse);
        }
        $selectedPatient = Patient::find(old('patient_id', $appointment->patient_id));
        $title = "Edit Antrian";
        $description = "Ubah data antrian";

        return view('pages.clinic.appointments.edit', compact('title', 'description', 'appointment', 'nurses', 'selectedPatient'));
    }

    public function update(Request $request, $language, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if (! $appointment->isAtNurseStage()) {
            return redirect()->route('clinic.appointments.show', [app()->getLocale(), $id])
                ->with('error', 'Antrian yang sudah diperiksa perawat tidak bisa diubah dari pendaftaran.');
        }

        $nurseIds = User::activeWithPermission('nursing.manage')->pluck('id');
        if ($appointment->nurse_id) {
            $nurseIds->push($appointment->nurse_id);
        }

        $validators = Validator::make($request->all(), [
            'patient_id' => ['required', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'nurse_id' => [$nurseIds->isNotEmpty() ? 'required' : 'nullable', 'integer', Rule::in($nurseIds->all())],
            'appointment_date' => 'required|date',
        ], [
            'nurse_id.required' => 'Pilih perawat yang akan menangani pasien.',
            'nurse_id.in' => 'Perawat tidak ditemukan atau tidak aktif.',
        ]);

        $validators->after(function ($validator) use ($request, $appointment) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $duplicate = Appointment::where('patient_id', $request->patient_id)
                ->where('id', '!=', $appointment->id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereIn('status', Appointment::OPEN_STATUSES)
                ->exists();
            if ($duplicate) {
                $validator->errors()->add('patient_id', 'Pasien ini masih punya antrian yang belum selesai pada tanggal tersebut.');
            }
        });

        if ($validators->fails()) {
            return redirect()->route('clinic.appointments.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        $dateChanged = $appointment->appointment_date?->toDateString() !== \Carbon\Carbon::parse($request->appointment_date)->toDateString();

        $appointment->patient_id = $request->patient_id;
        $appointment->nurse_id = $request->nurse_id ?: null;
        $appointment->appointment_date = $request->appointment_date;

        if ($dateChanged) {
            // Queue numbers are per day, so moving the visit needs a number for the new date
            $appointment->saveWithUniqueQueueNumber();
        } else {
            $appointment->save();
        }

        return redirect()->route('clinic.appointments.index', app()->getLocale())
            ->with('success', 'Antrian berhasil diperbarui!');
    }

    public function updateStatus(Request $request, $language, $id)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Appointment::STATUSES),
        ]);

        $appointment = Appointment::findOrFail($id);

        if (! $appointment->canTransitionTo($request->status)) {
            return response()->json([
                'success' => false,
                'message' => "Status tidak bisa diubah dari {$appointment->status} ke {$request->status}.",
            ], 422);
        }

        $appointment->status = $request->status;
        $appointment->save();

        return response()->json(['success' => true, 'status' => $appointment->status]);
    }

    public function destroy($language, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->medicalRecord()->exists() || $appointment->payments()->exists()) {
            return redirect()->back()
                ->with('error', 'Antrian yang sudah memiliki rekam medis atau pembayaran tidak bisa dihapus. Ubah statusnya menjadi dibatalkan jika perlu.');
        }

        $appointment->delete(); // soft delete: restore from Arsip
        return redirect()->route('clinic.appointments.index', app()->getLocale())
            ->with('success', "Antrian {$appointment->queue_number} dipindahkan ke Arsip.");
    }

    /**
     * Call the next patient.
     * stage=nurse: next "waiting" patient goes to the nurse (optionally only those assigned to nurse_id).
     * stage=doctor: next "ready" patient of the doctor, most urgent triage first.
     */
    public function callNext(Request $request, $language)
    {
        $request->validate([
            'stage' => 'nullable|in:nurse,doctor',
            'doctor_id' => 'nullable|integer|exists:doctors,id',
            'nurse_id' => 'nullable|integer|exists:users,id',
        ]);
        $stage = $request->input('stage', 'nurse');
        $doctorId = $request->doctor_id ?: ($stage === 'doctor' ? $request->user()->doctor_id : null);

        $next = DB::transaction(function () use ($request, $stage, $doctorId) {
            if ($stage === 'nurse') {
                $next = Appointment::today()
                    ->where('status', 'waiting')
                    ->when($request->nurse_id, fn ($q, $nurseId) => $q->where('nurse_id', $nurseId))
                    ->orderBy('queue_number')
                    ->lockForUpdate()
                    ->first();
            } else {
                $next = Appointment::with('nursingAssessment')
                    ->today()
                    ->where('status', 'ready')
                    ->when($doctorId, fn ($q, $id) => $q->where('doctor_id', $id))
                    ->lockForUpdate()
                    ->get()
                    ->sortBy(fn ($a) => [NursingAssessment::triagePriority($a->nursingAssessment?->triage_level), $a->queue_number])
                    ->first();
            }

            if ($next) {
                $next->status = $stage === 'nurse' ? 'screening' : 'in_progress';
                $next->called_at = now();
                $next->save();
            }

            return $next;
        });

        if ($next) {
            return response()->json(['success' => true, 'appointment' => $this->queuePayload($next->load(['patient', 'doctor', 'poli']))]);
        }

        return response()->json([
            'success' => false,
            'message' => $stage === 'nurse' ? 'Tidak ada pasien yang menunggu perawat.' : 'Tidak ada pasien yang menunggu dokter.',
        ]);
    }

    /**
     * Call (or call again) one patient. stage=nurse sends them to the nurse room,
     * stage=doctor to the poli/doctor. Calling again only refreshes called_at so the TV repeats it.
     */
    public function callSpecific(Request $request, $language, $id)
    {
        $request->validate(['stage' => 'nullable|in:nurse,doctor']);
        $stage = $request->input('stage', 'nurse');

        $allowed = $stage === 'nurse'
            ? ['waiting', 'screening']
            // Every open step, so a clinic without a nurse on duty can still send the patient to the doctor
            : Appointment::OPEN_STATUSES;

        $appointment = DB::transaction(function () use ($id, $stage, $allowed, $request) {
            $appointment = Appointment::lockForUpdate()->findOrFail($id);

            if (! $appointment->appointment_date?->isToday() || ! in_array($appointment->status, $allowed, true)) {
                return null;
            }

            $appointment->status = $stage === 'nurse' ? 'screening' : 'in_progress';
            if ($stage === 'doctor' && ! $appointment->doctor_id && $request->user()->doctor_id) {
                $appointment->doctor_id = $request->user()->doctor_id;
            }
            $appointment->called_at = now();
            $appointment->save();

            return $appointment;
        });

        if (! $appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian ini tidak bisa dipanggil (bukan hari ini, sudah selesai, atau dibatalkan).',
            ]);
        }

        return response()->json([
            'success' => true,
            'appointment' => $this->queuePayload($appointment->load(['patient', 'doctor', 'poli'])),
            'message' => 'Antrian berhasil dipanggil',
        ]);
    }

    /**
     * Queue data for the TV screen: who is being called and who is waiting.
     */
    public function getWaitingQueue($language)
    {
        [$waiting, $called] = $this->displayQueues();

        // Only expose what the waiting-room screen needs; no NIK, phone, address, etc.
        return response()->json([
            'success' => true,
            'waiting' => $waiting->map(fn ($a) => $this->queuePayload($a))->values(),
            'in_progress' => $called->map(fn ($a) => $this->queuePayload($a))->values(),
            'waiting_count' => $waiting->count(),
            'in_progress_count' => $called->count(),
        ]);
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     *         [waiting for nurse or doctor, currently called (most recent first)]
     */
    private function displayQueues(): array
    {
        $waiting = Appointment::with(['patient', 'doctor', 'poli'])
            ->today()
            ->whereIn('status', ['waiting', 'ready'])
            ->orderBy('queue_number')
            ->get();

        $called = Appointment::with(['patient', 'doctor', 'poli'])
            ->today()
            ->whereIn('status', ['screening', 'in_progress'])
            ->orderByDesc('called_at')
            ->orderByDesc('updated_at')
            ->get();

        return [$waiting, $called];
    }

    /**
     * Minimal appointment data for the queue screen and voice call.
     */
    private function queuePayload(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'queue_number' => $appointment->queue_number,
            'status' => $appointment->status,
            'patient' => ['name' => $appointment->patient?->name],
            'doctor' => [
                'id' => $appointment->doctor?->id,
                'name' => $appointment->doctor?->name,
            ],
            'destination' => $appointment->destinationLabel(),
            // Changes on every call, so the TV announces a repeated call too
            'call_key' => $appointment->id . '-' . ($appointment->called_at?->timestamp ?? $appointment->updated_at?->timestamp),
        ];
    }

    /**
     * Halaman display antrian untuk layar TV ruang tunggu
     */
    public function display($language)
    {
        [$waiting, $called] = $this->displayQueues();
        $current = $called->first();

        $completed = Appointment::today()
            ->where('status', 'completed')
            ->count();

        $currentCallKey = $current ? $this->queuePayload($current)['call_key'] : null;

        $title = "Display Antrian";
        $description = "Tampilan layar antrian";

        return view('pages.clinic.appointments.display', compact(
            'title',
            'description',
            'waiting',
            'current',
            'completed',
            'currentCallKey'
        ));
    }

    /**
     * Print struk antrian pendaftaran
     */
    public function printStruk($language, $id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'poli', 'nurse'])->findOrFail($id);
        $settings = ClinicSetting::getAllSettings();

        $title = "Struk Antrian";
        $description = "Cetak struk antrian pendaftaran";

        return view('pages.clinic.appointments.print-struk', compact(
            'title',
            'description',
            'appointment',
            'settings'
        ));
    }
}

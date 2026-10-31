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

class AppointmentController extends Controller
{
    public function index()
    {
        $per_page = session('pagination_per_page') ?? 20;
        $appointments = Appointment::with(['patient', 'doctor'])
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('queue_number', 'ASC')
            ->paginate($per_page);
        $title = "Daftar Antrian";
        $description = "Kelola antrian pasien";

        return view('pages.clinic.appointments.index', compact('title', 'description', 'appointments'));
    }

    public function today(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor'])
            ->today();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('no_rm', 'like', "%{$search}%");
            });
        }

        $appointments = $query->orderByRaw("CASE 
                WHEN status = 'waiting' THEN 1 
                WHEN status = 'in_progress' THEN 2 
                ELSE 3 
            END")
            ->orderBy('queue_number', 'ASC')
            ->get();
        $title = "Antrian Hari Ini";
        $description = "Daftar antrian pasien hari ini";
        $doctors = Doctor::active()->orderBy('name')->get();

        return view('pages.clinic.appointments.today', compact('title', 'description', 'appointments', 'doctors'));
    }

    public function create()
    {
        $title = "Buat Antrian Baru";
        $description = "Form pendaftaran antrian";
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::active()->orderBy('name')->get();
        $queue_number = Appointment::generateQueueNumber();

        return view('pages.clinic.appointments.create', compact('title', 'description', 'patients', 'doctors', 'queue_number'));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'patient_id' => ['required', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'doctor_id' => ['required', Rule::exists('doctors', 'id')->where('status', 'active')->whereNull('deleted_at')],
            'appointment_date' => 'required|date',
            'appointment_time' => 'nullable|date_format:H:i,H:i:s',
            'complaint' => 'required|string',
        ], [
            'doctor_id.exists' => 'Dokter tidak ditemukan atau tidak aktif.',
        ]);

        $validators->after(function ($validator) use ($request) {
            $duplicate = Appointment::where('patient_id', $request->patient_id)
                ->where('doctor_id', $request->doctor_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->where('status', '!=', 'cancelled')
                ->exists();
            if ($duplicate) {
                $validator->errors()->add('patient_id', 'Pasien ini sudah terdaftar ke dokter yang sama pada tanggal tersebut.');
            }
        });

        if ($validators->fails()) {
            return redirect()->route('clinic.appointments.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $appointment = new Appointment();
        $appointment->patient_id = $request->patient_id;
        $appointment->doctor_id = $request->doctor_id;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->appointment_time = $request->appointment_time;
        $appointment->complaint = $request->complaint;
        $appointment->status = 'waiting';
        $appointment->saveWithUniqueQueueNumber();

        return redirect()->route('clinic.appointments.index', app()->getLocale())
            ->with('success', 'Antrian berhasil dibuat!');
    }

    public function show($language, $id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'medicalRecord', 'payment'])->findOrFail($id);
        $title = "Detail Antrian";
        $description = "Informasi lengkap antrian";

        return view('pages.clinic.appointments.show', compact('title', 'description', 'appointment'));
    }

    public function edit($language, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::active()->orderBy('name')->get();
        $title = "Edit Antrian";
        $description = "Ubah data antrian";

        return view('pages.clinic.appointments.edit', compact('title', 'description', 'appointment', 'patients', 'doctors'));
    }

    public function update(Request $request, $language, $id)
    {
        $validators = Validator::make($request->all(), [
            'patient_id' => ['required', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'doctor_id' => ['required', Rule::exists('doctors', 'id')->whereNull('deleted_at')],
            'appointment_date' => 'required|date',
            'complaint' => 'required|string',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.appointments.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        $appointment = Appointment::findOrFail($id);
        $dateChanged = $appointment->appointment_date?->toDateString() !== \Carbon\Carbon::parse($request->appointment_date)->toDateString();

        $appointment->patient_id = $request->patient_id;
        $appointment->doctor_id = $request->doctor_id;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->appointment_time = $request->appointment_time;
        $appointment->complaint = $request->complaint;

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

    public function callNext(Request $request, $language)
    {
        $request->validate(['doctor_id' => 'nullable|integer|exists:doctors,id']);

        $next = DB::transaction(function () use ($request) {
            $next = Appointment::today()
                ->waiting()
                ->when($request->doctor_id, fn ($q, $doctorId) => $q->where('doctor_id', $doctorId))
                ->orderBy('queue_number')
                ->lockForUpdate()
                ->first();

            if ($next) {
                $next->status = 'in_progress';
                $next->save();
            }

            return $next;
        });

        if ($next) {
            return response()->json(['success' => true, 'appointment' => $this->queuePayload($next->load(['patient', 'doctor']))]);
        }
        return response()->json(['success' => false, 'message' => 'Tidak ada antrian']);
    }

    /**
     * Panggil antrian spesifik berdasarkan ID
     */
    public function callSpecific($language, $id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id);

        if ($appointment->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Antrian ini sudah dipanggil atau selesai'
            ]);
        }

        $appointment->status = 'in_progress';
        $appointment->save();

        return response()->json([
            'success' => true,
            'appointment' => $this->queuePayload($appointment),
            'message' => 'Antrian berhasil dipanggil'
        ]);
    }

    /**
     * Get daftar antrian yang menunggu untuk API
     */
    public function getWaitingQueue($language)
    {
        $waiting = Appointment::with(['patient', 'doctor'])
            ->today()
            ->waiting()
            ->orderBy('queue_number')
            ->get();

        // Most recently called first, matching the "current" card on the display page
        $inProgress = Appointment::with(['patient', 'doctor'])
            ->today()
            ->where('status', 'in_progress')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Only expose what the waiting-room screen needs; no NIK, phone, address, etc.
        return response()->json([
            'success' => true,
            'waiting' => $waiting->map(fn ($a) => $this->queuePayload($a))->values(),
            'in_progress' => $inProgress->map(fn ($a) => $this->queuePayload($a))->values(),
            'waiting_count' => $waiting->count(),
            'in_progress_count' => $inProgress->count()
        ]);
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
        ];
    }

    /**
     * Halaman display antrian untuk layar TV ruang tunggu
     */
    public function display($language)
    {
        $waiting = Appointment::with(['patient', 'doctor'])
            ->today()
            ->waiting()
            ->orderBy('queue_number')
            ->get();

        $current = Appointment::with(['patient', 'doctor'])
            ->today()
            ->where('status', 'in_progress')
            ->orderBy('updated_at', 'desc')
            ->first();

        $completed = Appointment::today()
            ->where('status', 'completed')
            ->count();

        $title = "Display Antrian";
        $description = "Tampilan layar antrian";

        return view('pages.clinic.appointments.display', compact(
            'title',
            'description',
            'waiting',
            'current',
            'completed'
        ));
    }

    /**
     * Print struk antrian pendaftaran
     */
    public function printStruk($language, $id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id);
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

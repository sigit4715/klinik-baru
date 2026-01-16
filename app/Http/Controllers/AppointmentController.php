<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            ->orderBy('id', 'DESC')
            ->get();
        $title = "Antrian Hari Ini";
        $description = "Daftar antrian pasien hari ini";

        return view('pages.clinic.appointments.today', compact('title', 'description', 'appointments'));
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
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'complaint' => 'required|string',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.appointments.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $appointmentDate = \Carbon\Carbon::parse($request->appointment_date);

        $appointment = new Appointment();
        $appointment->queue_number = Appointment::generateQueueNumber($appointmentDate);
        $appointment->patient_id = $request->patient_id;
        $appointment->doctor_id = $request->doctor_id;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->appointment_time = $request->appointment_time;
        $appointment->complaint = $request->complaint;
        $appointment->status = 'waiting';
        $appointment->save();

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
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'complaint' => 'required|string',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.appointments.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        $appointment = Appointment::findOrFail($id);
        $appointment->patient_id = $request->patient_id;
        $appointment->doctor_id = $request->doctor_id;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->appointment_time = $request->appointment_time;
        $appointment->complaint = $request->complaint;
        $appointment->save();

        return redirect()->route('clinic.appointments.index', app()->getLocale())
            ->with('success', 'Antrian berhasil diperbarui!');
    }

    public function updateStatus(Request $request, $language, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = $request->status;
        $appointment->save();

        return response()->json(['success' => true, 'status' => $appointment->status]);
    }

    public function destroy($language, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        return redirect()->route('clinic.appointments.index', app()->getLocale())
            ->with('success', 'Antrian berhasil dihapus!');
    }

    public function callNext($language)
    {
        $next = Appointment::today()->waiting()->orderBy('queue_number')->first();
        if ($next) {
            $next->status = 'in_progress';
            $next->save();
            return response()->json(['success' => true, 'appointment' => $next->load(['patient', 'doctor'])]);
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
            'appointment' => $appointment,
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

        $inProgress = Appointment::with(['patient', 'doctor'])
            ->today()
            ->where('status', 'in_progress')
            ->orderBy('queue_number')
            ->get();

        return response()->json([
            'success' => true,
            'waiting' => $waiting,
            'in_progress' => $inProgress,
            'waiting_count' => $waiting->count(),
            'in_progress_count' => $inProgress->count()
        ]);
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

<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\NursingAssessment;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Nurse step: complaint, anamnesis, vital signs, triage, then send the patient to a poli/doctor.
 */
class NursingController extends Controller
{
    /** Steps where the nurse may still fill in or correct the form */
    private const EDITABLE_STATUSES = ['waiting', 'screening', 'ready'];

    public function index(Request $request)
    {
        $mine = $request->boolean('mine');

        $appointments = Appointment::with(['patient', 'nurse', 'poli', 'doctor', 'nursingAssessment'])
            ->today()
            ->whereIn('status', self::EDITABLE_STATUSES)
            ->when($mine, fn ($q) => $q->where('nurse_id', $request->user()->id))
            ->orderByRaw("CASE status WHEN 'screening' THEN 1 WHEN 'waiting' THEN 2 ELSE 3 END")
            ->orderBy('queue_number')
            ->get();

        $title = 'Pemeriksaan Perawat';
        $description = 'Anamnesa, tanda vital, triage, poli dan dokter tujuan';

        return view('pages.clinic.nursing.index', compact('title', 'description', 'appointments', 'mine'));
    }

    public function edit($language, $appointmentId)
    {
        $appointment = Appointment::with(['patient', 'nurse', 'registeredBy', 'nursingAssessment.nurse'])->findOrFail($appointmentId);

        if ($response = $this->rejectIfLocked($appointment)) {
            return $response;
        }

        $assessment = $appointment->nursingAssessment ?? new NursingAssessment();
        $polis = Poli::active()->where('type', 'umum')->orderBy('name')->get();
        $doctors = Doctor::active()->orderBy('name')->get();

        // Last visit's measurements help the nurse spot changes
        $lastRecord = MedicalRecord::where('patient_id', $appointment->patient_id)
            ->where('appointment_id', '!=', $appointment->id)
            ->latest()
            ->first();

        $title = 'Pemeriksaan Perawat';
        $description = 'Antrian ' . $appointment->queue_number;

        return view('pages.clinic.nursing.edit', compact('title', 'description', 'appointment', 'assessment', 'polis', 'doctors', 'lastRecord'));
    }

    public function update(Request $request, $language, $appointmentId)
    {
        $validator = Validator::make($request->all(), array_merge([
            'chief_complaint' => 'required|string|max:2000',
            'anamnesa' => 'nullable|string|max:5000',
            'allergy_note' => 'nullable|string|max:255',
            'triage_level' => ['required', Rule::in(array_keys(NursingAssessment::TRIAGE))],
            'poli_id' => ['required', 'integer', Rule::exists('polis', 'id')->where('status', 'active')->whereNull('deleted_at')],
            'doctor_id' => ['required', 'integer', Rule::exists('doctors', 'id')->where('status', 'active')->whereNull('deleted_at')],
            'notes' => 'nullable|string|max:2000',
        ], NursingAssessment::vitalRules()), [
            'chief_complaint.required' => 'Keluhan utama wajib diisi.',
            'triage_level.required' => 'Pilih tingkat triage.',
            'poli_id.required' => 'Pilih poli tujuan.',
            'poli_id.exists' => 'Poli tidak ditemukan atau tidak aktif.',
            'doctor_id.required' => 'Pilih dokter tujuan.',
            'doctor_id.exists' => 'Dokter tidak ditemukan atau tidak aktif.',
            'blood_pressure.regex' => 'Format tekanan darah harus seperti 120/80.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            // A doctor assigned to a poli can only be chosen for that poli; doctors without a poli fit any
            $doctorPoli = Doctor::whereKey($request->doctor_id)->value('poli_id');
            if ($doctorPoli && (int) $doctorPoli !== (int) $request->poli_id) {
                $validator->errors()->add('doctor_id', 'Dokter yang dipilih tidak bertugas di poli tersebut.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $appointment = DB::transaction(function () use ($request, $appointmentId) {
                $appointment = Appointment::lockForUpdate()->findOrFail($appointmentId);

                if (! in_array($appointment->status, self::EDITABLE_STATUSES, true) || $appointment->medicalRecord()->exists()) {
                    throw ValidationException::withMessages([
                        'appointment' => 'Pasien sudah diperiksa dokter atau antrian sudah ditutup. Data perawat tidak bisa diubah lagi.',
                    ]);
                }

                $assessment = NursingAssessment::firstOrNew(['appointment_id' => $appointment->id]);
                $assessment->fill($request->only([
                    'chief_complaint', 'anamnesa', 'allergy_note', 'blood_pressure', 'body_temperature',
                    'pulse_rate', 'respiratory_rate', 'oxygen_saturation', 'height', 'weight',
                    'triage_level', 'poli_id', 'doctor_id', 'notes',
                ]));
                $assessment->patient_id = $appointment->patient_id;
                // The nurse who first filled in the form; later corrections are in the audit log
                $assessment->nurse_id = $assessment->nurse_id ?? $request->user()->id;
                $assessment->save();

                $appointment->poli_id = $assessment->poli_id;
                $appointment->doctor_id = $assessment->doctor_id;
                // Kept on the visit too, so lists and the queue ticket show the complaint
                $appointment->complaint = $assessment->chief_complaint;
                $appointment->status = 'ready';
                $appointment->save();

                return $appointment;
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('clinic.nursing.index', app()->getLocale())
            ->with('success', "Data perawat antrian {$appointment->queue_number} tersimpan. Pasien masuk antrian dokter.");
    }

    private function rejectIfLocked(Appointment $appointment)
    {
        if (in_array($appointment->status, self::EDITABLE_STATUSES, true) && ! $appointment->medicalRecord()->exists()) {
            return null;
        }

        return redirect()->route('clinic.nursing.index', app()->getLocale())
            ->with('error', "Antrian {$appointment->queue_number} sudah diperiksa dokter atau ditutup. Data perawat tidak bisa diubah lagi.");
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        $patients = $query->orderBy('updated_at', 'DESC')->paginate(10)->withQueryString();
        $title = "Rekam Medis";
        $description = "Kelola rekam medis pasien";

        return view('pages.clinic.medical-records.index', compact('title', 'description', 'patients'));
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

        $medicines = Medicine::active()->where('stock', '>', 0)->get();
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
            'diagnosis' => 'required|string',
        ]);

        if ($validators->fails()) {
            return redirect()->back()->withErrors($validators)->withInput();
        }

        $appointment = Appointment::findOrFail($request->appointment_id);

        $record = new MedicalRecord();
        $record->appointment_id = $request->appointment_id;
        $record->patient_id = $appointment->patient_id;
        $record->doctor_id = $appointment->doctor_id;
        $record->anamnesa = $request->anamnesa;
        $record->diagnosis = $request->diagnosis;
        $record->treatment = $request->treatment;
        $record->notes = $request->notes;

        // Process prescriptions
        if ($request->has('prescriptions')) {
            $prescriptions = [];
            foreach ($request->prescriptions as $prescription) {
                if (!empty($prescription['medicine_id'])) {
                    $prescriptions[] = [
                        'medicine_id' => $prescription['medicine_id'],
                        'qty' => $prescription['qty'] ?? 1,
                        'dosage' => $prescription['dosage'] ?? '',
                        'notes' => $prescription['notes'] ?? '',
                    ];

                    // Reduce medicine stock
                    $medicine = Medicine::find($prescription['medicine_id']);
                    if ($medicine) {
                        $medicine->stock -= ($prescription['qty'] ?? 1);
                        $medicine->save();
                    }
                }
            }
            $record->prescriptions = $prescriptions;
        }

        // Process services used
        if ($request->has('services_used')) {
            $servicesUsed = [];
            foreach ($request->services_used as $serviceUsed) {
                if (!empty($serviceUsed['service_id'])) {
                    $servicesUsed[] = [
                        'service_id' => $serviceUsed['service_id'],
                        'qty' => $serviceUsed['qty'] ?? 1,
                    ];
                }
            }
            $record->services_used = $servicesUsed;
        }

        $record->save();

        // Update appointment status
        $appointment->status = 'completed';
        $appointment->save();

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

        return view('pages.clinic.medical-records.show', compact(
            'title',
            'description',
            'record',
            'prescriptionDetails',
            'serviceDetails'
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

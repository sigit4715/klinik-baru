<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'anamnesa',
        'diagnosis',
        'blood_pressure',
        'body_temperature',
        'pulse_rate',
        'respiratory_rate',
        'height',
        'weight',
        'treatment',
        'prescriptions',
        'services_used',
        'notes',
    ];

    protected $casts = [
        'prescriptions' => 'array',
        'services_used' => 'array',
    ];

    // withTrashed: history stays readable after a patient, doctor or visit is archived
    public function appointment()
    {
        return $this->belongsTo(Appointment::class)->withTrashed();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function getPrescriptionDetails()
    {
        if (!$this->prescriptions)
            return [];

        // One query for all lines; archived medicines are still shown on old records
        $medicines = Medicine::withTrashed()
            ->whereIn('id', array_filter(array_column($this->prescriptions, 'medicine_id')))
            ->get()
            ->keyBy('id');

        $details = [];
        foreach ($this->prescriptions as $prescription) {
            $medicine = $medicines->get($prescription['medicine_id'] ?? null);
            if ($medicine) {
                $details[] = [
                    'medicine' => $medicine,
                    'qty' => $prescription['qty'] ?? 0,
                    'dosage' => $prescription['dosage'] ?? '',
                    'notes' => $prescription['notes'] ?? '',
                ];
            }
        }
        return $details;
    }

    public function getServiceDetails()
    {
        if (!$this->services_used)
            return [];

        $services = Service::withTrashed()
            ->whereIn('id', array_filter(array_column($this->services_used, 'service_id')))
            ->get()
            ->keyBy('id');

        $details = [];
        foreach ($this->services_used as $serviceUsed) {
            $service = $services->get($serviceUsed['service_id'] ?? null);
            if ($service) {
                $details[] = [
                    'service' => $service,
                    'qty' => $serviceUsed['qty'] ?? 1,
                ];
            }
        }
        return $details;
    }
}

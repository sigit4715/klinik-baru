<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'anamnesa',
        'diagnosis',
        'treatment',
        'prescriptions',
        'services_used',
        'notes',
    ];

    protected $casts = [
        'prescriptions' => 'array',
        'services_used' => 'array',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getPrescriptionDetails()
    {
        if (!$this->prescriptions)
            return [];

        $details = [];
        foreach ($this->prescriptions as $prescription) {
            $medicine = Medicine::find($prescription['medicine_id'] ?? null);
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

        $details = [];
        foreach ($this->services_used as $serviceUsed) {
            $service = Service::find($serviceUsed['service_id'] ?? null);
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

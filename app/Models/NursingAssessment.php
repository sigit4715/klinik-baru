<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Nurse step of a visit: complaint, anamnesis, vital signs, triage, and where the patient is sent.
 */
class NursingAssessment extends Model
{
    use Auditable;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'nurse_id',
        'chief_complaint',
        'anamnesa',
        'allergy_note',
        'blood_pressure',
        'body_temperature',
        'pulse_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'height',
        'weight',
        'triage_level',
        'poli_id',
        'doctor_id',
        'notes',
    ];

    protected $casts = [
        'body_temperature' => 'decimal:1',
        'height' => 'decimal:1',
        'weight' => 'decimal:1',
    ];

    /** Triage colour => label, in priority order (first = most urgent) */
    public const TRIAGE = [
        'merah' => 'Merah (Gawat Darurat)',
        'kuning' => 'Kuning (Mendesak)',
        'hijau' => 'Hijau (Tidak Mendesak)',
    ];

    public const TRIAGE_BADGE = [
        'merah' => 'danger',
        'kuning' => 'warning',
        'hijau' => 'success',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class)->withTrashed();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class)->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function triageLabel(): ?string
    {
        return self::TRIAGE[$this->triage_level] ?? null;
    }

    public function triageBadge(): string
    {
        return self::TRIAGE_BADGE[$this->triage_level] ?? 'secondary';
    }

    /** Lower number = more urgent; unknown triage sorts last */
    public static function triagePriority(?string $level): int
    {
        $index = array_search($level, array_keys(self::TRIAGE), true);

        return $index === false ? 99 : $index;
    }

    /** Validation rules shared by the nurse form and the doctor's correction of vital signs */
    public static function vitalRules(): array
    {
        return [
            'blood_pressure' => ['nullable', 'string', 'max:20', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'body_temperature' => 'nullable|numeric|between:30,45',
            'pulse_rate' => 'nullable|integer|between:20,250',
            'respiratory_rate' => 'nullable|integer|between:5,80',
            'oxygen_saturation' => 'nullable|integer|between:50,100',
            'height' => 'nullable|numeric|between:20,250',
            'weight' => 'nullable|numeric|between:0.5,400',
        ];
    }
}

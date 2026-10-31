<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'no_rm',
        'name',
        'nik',
        'gender',
        'birth_date',
        'birth_place',
        'phone',
        'whatsapp',
        'email',
        'address',
        'spouse_name',
        'parent_name',
        'blood_type',
        'allergy',
        'emergency_contact',
        'emergency_phone',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public static function generateNoRM()
    {
        $lastPatient = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastPatient ? intval(substr($lastPatient->no_rm, 3)) + 1 : 1;
        return 'RM-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Save with a fresh medical record number, retrying if two registrations collide.
     * no_rm has a unique index.
     */
    public function saveWithUniqueNoRM(int $attempts = 5): void
    {
        for ($i = 1; ; $i++) {
            $this->no_rm = self::generateNoRM();
            try {
                $this->save();
                return;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // NIK is unique too; only retry when the collision is on no_rm
                if ($i >= $attempts || ! str_contains($e->getMessage(), 'no_rm')) {
                    throw $e;
                }
            }
        }
    }
}

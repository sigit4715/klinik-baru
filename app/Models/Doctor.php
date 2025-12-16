<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'sip_number',
        'name',
        'specialization',
        'phone',
        'email',
        'address',
        'photo',
        'schedule',
        'status',
    ];

    protected $casts = [
        'schedule' => 'array',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

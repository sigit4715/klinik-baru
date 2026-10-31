<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'sip_number',
        'name',
        'specialization',
        'poli_id',
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

    public function poli()
    {
        return $this->belongsTo(Poli::class)->withTrashed();
    }

    /** Login account linked to this doctor (users.doctor_id) */
    public function user()
    {
        return $this->hasOne(User::class);
    }

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

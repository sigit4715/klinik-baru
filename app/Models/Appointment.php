<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'complaint',
        'status',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public static function generateQueueNumber($date = null)
    {
        $date = $date ?? today();
        $lastQueue = self::whereDate('appointment_date', $date)
            ->orderBy('queue_number', 'desc')
            ->first();
        $nextNumber = $lastQueue ? intval(substr($lastQueue->queue_number, -3)) + 1 : 1;
        return $date->format('Ymd') . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}

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

    /**
     * The latest payment, so a cancelled payment followed by a new one shows the new one.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public const STATUSES = ['waiting', 'in_progress', 'completed', 'cancelled'];

    /**
     * Allowed status changes. Completed visits are final; cancelled ones can be reopened.
     */
    private const TRANSITIONS = [
        'waiting' => ['in_progress', 'cancelled'],
        'in_progress' => ['waiting', 'completed', 'cancelled'],
        'completed' => [],
        'cancelled' => ['waiting'],
    ];

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * Save with a fresh queue number for the appointment date, retrying on collision.
     * queue_number has a unique index (it already contains the date).
     */
    public function saveWithUniqueQueueNumber(int $attempts = 5): void
    {
        for ($i = 1; ; $i++) {
            $this->queue_number = self::generateQueueNumber(\Carbon\Carbon::parse($this->appointment_date));
            try {
                $this->save();
                return;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                if ($i >= $attempts) {
                    throw $e;
                }
            }
        }
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
        $prefix = $date->format('Ymd') . '-';

        // Parse the numeric part so 1000+ per day keeps counting instead of wrapping
        $max = self::where('queue_number', 'like', $prefix . '%')
            ->pluck('queue_number')
            ->map(fn ($q) => (int) substr($q, strlen($prefix)))
            ->max();

        return $prefix . str_pad(($max ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'queue_number',
        'patient_id',
        'doctor_id',
        'poli_id',
        'nurse_id',
        'appointment_date',
        'appointment_time',
        'complaint',
        'status',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'called_at' => 'datetime',
    ];

    public function poli()
    {
        return $this->belongsTo(Poli::class)->withTrashed();
    }

    /** Nurse chosen at registration */
    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    /** Front-desk user who registered the visit */
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function nursingAssessment()
    {
        return $this->hasOne(NursingAssessment::class);
    }

    public function supportOrders()
    {
        return $this->hasMany(SupportOrder::class)->orderBy('id');
    }

    // withTrashed: visits of archived patients/doctors must still show who they were
    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
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

    /** No active payment yet (none, or the last one was cancelled or fully refunded) */
    public function needsPayment(): bool
    {
        return ! $this->payment || in_array($this->payment->status, ['cancelled', 'refunded'], true);
    }

    /**
     * waiting (menunggu perawat) -> screening (di perawat) -> ready (menunggu dokter)
     * -> in_progress (diperiksa dokter) -> completed. cancelled from any open step.
     */
    public const STATUSES = ['waiting', 'screening', 'ready', 'in_progress', 'completed', 'cancelled'];

    /** Visit is still open: not finished by the doctor and not cancelled */
    public const OPEN_STATUSES = ['waiting', 'screening', 'ready', 'in_progress'];

    public const STATUS_LABELS = [
        'waiting' => 'Menunggu Perawat',
        'screening' => 'Di Perawat',
        'ready' => 'Menunggu Dokter',
        'in_progress' => 'Diperiksa Dokter',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const STATUS_BADGES = [
        'waiting' => 'warning',
        'screening' => 'primary',
        'ready' => 'secondary',
        'in_progress' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    /**
     * Allowed status changes. Completed visits are final; cancelled ones can be reopened.
     * waiting -> in_progress stays allowed so a clinic can skip the nurse step.
     */
    private const TRANSITIONS = [
        'waiting' => ['screening', 'ready', 'in_progress', 'cancelled'],
        'screening' => ['waiting', 'ready', 'in_progress', 'cancelled'],
        'ready' => ['screening', 'in_progress', 'cancelled'],
        'in_progress' => ['waiting', 'ready', 'completed', 'cancelled'],
        'completed' => [],
        'cancelled' => ['waiting'],
    ];

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'secondary';
    }

    /** Patient is at, or waiting for, the nurse */
    public function isAtNurseStage(): bool
    {
        return in_array($this->status, ['waiting', 'screening'], true);
    }

    /** Where the patient should go when called, used by the TV display and voice call */
    public function destinationLabel(): string
    {
        if ($this->isAtNurseStage()) {
            return 'Ruang Perawat';
        }

        $parts = array_filter([$this->poli?->name, $this->doctor?->name]);

        return $parts ? implode(' - ', $parts) : 'Ruang Pemeriksaan';
    }

    /** Open visit with no doctor exam whose support exams are all finished: can go straight to the cashier */
    public function canCompleteSupportOnly(): bool
    {
        if (! in_array($this->status, self::OPEN_STATUSES, true) || $this->medicalRecord) {
            return false;
        }
        $active = $this->supportOrders->where('status', '!=', 'cancelled');

        return $active->isNotEmpty() && ! $active->contains(fn ($o) => $o->isOpen());
    }

    /** Complaint from the nurse step, falling back to the one typed at registration (old visits) */
    public function complaintText(): ?string
    {
        return $this->nursingAssessment?->chief_complaint ?: $this->complaint;
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

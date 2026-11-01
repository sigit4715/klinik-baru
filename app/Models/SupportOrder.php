<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

/** One support examination (lab/radiology/EKG) ordered for a visit */
class SupportOrder extends Model
{
    use Auditable;

    protected $fillable = ['appointment_id', 'patient_id', 'service_id', 'poli_id', 'qty', 'ordered_from', 'notes'];

    protected $casts = [
        'result_at' => 'datetime',
        'qty' => 'integer',
    ];

    protected $attributes = ['status' => 'ordered', 'qty' => 1];

    public const STATUS_LABELS = [
        'ordered' => 'Dipesan',
        'in_progress' => 'Dikerjakan',
        'done' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const STATUS_BADGES = [
        'ordered' => 'warning',
        'in_progress' => 'info',
        'done' => 'success',
        'cancelled' => 'secondary',
    ];

    public const SOURCES = [
        'pendaftaran' => 'Pendaftaran',
        'perawat' => 'Perawat',
        'dokter' => 'Dokter',
    ];

    public const OPEN_STATUSES = ['ordered', 'in_progress'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class)->withTrashed();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class)->withTrashed();
    }

    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function resultBy()
    {
        return $this->belongsTo(User::class, 'result_by');
    }

    public function files()
    {
        return $this->hasMany(SupportOrderFile::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'secondary';
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }
}

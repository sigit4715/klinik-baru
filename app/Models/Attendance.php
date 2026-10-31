<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use Auditable;

    /** Device details are noise in the audit log */
    protected array $auditExclude = ['user_agent'];

    protected $fillable = [
        'user_id', 'staff_schedule_id', 'work_date', 'shift_name', 'scheduled_start', 'scheduled_end', 'status',
        'check_in_at', 'check_in_lat', 'check_in_lng', 'check_in_accuracy', 'check_in_distance',
        'check_out_at', 'check_out_lat', 'check_out_lng', 'check_out_accuracy', 'check_out_distance',
        'late_minutes', 'early_leave_minutes', 'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'is_manual' => 'boolean',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
    ];

    public const STATUS_LABELS = [
        'present' => 'Hadir',
        'late' => 'Terlambat',
        'permission' => 'Izin',
        'sick' => 'Sakit',
        'leave' => 'Cuti',
        'absent' => 'Alpa',
    ];

    public const STATUS_BADGES = [
        'present' => 'success',
        'late' => 'warning',
        'permission' => 'info',
        'sick' => 'info',
        'leave' => 'primary',
        'absent' => 'danger',
    ];

    /** Statuses that mean the employee did not come in (no check-in times) */
    public const ABSENCE_STATUSES = ['permission', 'sick', 'leave', 'absent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(StaffSchedule::class, 'staff_schedule_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'secondary';
    }

    /** Worked minutes between check-in and check-out */
    public function workedMinutes(): ?int
    {
        if (! $this->check_in_at || ! $this->check_out_at) {
            return null;
        }

        return (int) $this->check_in_at->diffInMinutes($this->check_out_at);
    }

    public static function formatMinutes(?int $minutes): string
    {
        if ($minutes === null) {
            return '-';
        }

        return intdiv($minutes, 60) . 'j ' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . 'm';
    }
}

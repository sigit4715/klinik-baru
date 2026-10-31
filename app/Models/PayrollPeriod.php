<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use Auditable;

    protected $fillable = ['period', 'notes'];

    /** Same as the column default, so a freshly created model is a draft without reloading */
    protected $attributes = ['status' => 'draft'];

    protected $casts = [
        'calculated_at' => 'datetime',
        'locked_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'locked' => 'Dikunci',
        'paid' => 'Sudah Dibayar',
    ];

    public const STATUS_BADGES = [
        'draft' => 'secondary',
        'locked' => 'primary',
        'paid' => 'success',
    ];

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function start(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->period . '-01')->startOfDay();
    }

    public function end(): Carbon
    {
        return $this->start()->endOfMonth();
    }

    public function label(): string
    {
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $start = $this->start();

        return $months[$start->month] . ' ' . $start->year;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'secondary';
    }
}

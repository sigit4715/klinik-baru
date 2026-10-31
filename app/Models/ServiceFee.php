<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

/** Doctor fee for one service: default (doctor_id null) or an override for one doctor */
class ServiceFee extends Model
{
    use Auditable;

    protected $fillable = ['service_id', 'doctor_id', 'fee_type', 'fee_value'];

    protected $casts = [
        'fee_value' => 'decimal:2',
    ];

    public const TYPES = [
        'fixed' => 'Nominal (Rp per tindakan)',
        'percent' => 'Persen dari harga layanan',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    /** Fee for one billed line */
    public function feeFor(int $qty, float $price): float
    {
        return round($this->fee_type === 'percent'
            ? $qty * $price * (float) $this->fee_value / 100
            : $qty * (float) $this->fee_value, 2);
    }

    public function label(): string
    {
        return $this->fee_type === 'percent'
            ? rtrim(rtrim(number_format((float) $this->fee_value, 2, ',', '.'), '0'), ',') . '%'
            : 'Rp ' . number_format((float) $this->fee_value, 0, ',', '.');
    }
}

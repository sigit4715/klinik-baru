<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

class Payment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'invoice_number',
        'appointment_id',
        'patient_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'cash_received',
        'change_amount',
        'payment_method',
        'status',
        'items',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'items' => 'array',
    ];

    /** "refunded" = fully refunded. A partial refund keeps "paid" with refunded_amount > 0. */
    public const STATUSES = ['pending', 'paid', 'cancelled', 'refunded'];

    /** Statuses whose money was received; refunds are subtracted separately by refund date */
    public const REVENUE_STATUSES = ['paid', 'refunded'];

    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'paid' => 'Lunas',
        'cancelled' => 'Dibatalkan',
        'refunded' => 'Refund',
    ];

    // withTrashed: payments of archived patients/visits must stay readable
    public function appointment()
    {
        return $this->belongsTo(Appointment::class)->withTrashed();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class)->latest();
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeRevenue($query)
    {
        return $query->whereIn('status', self::REVENUE_STATUSES);
    }

    /** Amount that can still be refunded */
    public function refundableAmount(): float
    {
        if ($this->status !== 'paid') {
            return 0.0;
        }

        return max(0.0, round((float) $this->total - (float) $this->refunded_amount, 2));
    }

    public function statusLabel(): string
    {
        if ($this->status === 'paid' && (float) $this->refunded_amount > 0) {
            return 'Lunas (refund sebagian)';
        }

        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Net revenue between two moments: money received (by payment date)
     * minus refunds (by refund date).
     *
     * @return array{gross: float, refunds: float, net: float}
     */
    public static function revenueBetween($start, $end): array
    {
        $gross = (float) self::revenue()->whereBetween('created_at', [$start, $end])->sum('total');
        $refunds = (float) PaymentRefund::whereBetween('created_at', [$start, $end])->sum('amount');

        return ['gross' => $gross, 'refunds' => $refunds, 'net' => $gross - $refunds];
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public static function generateInvoiceNumber()
    {
        $prefix = 'INV-' . today()->format('Ymd') . '-';
        $last = self::where('invoice_number', 'like', $prefix . '%')->max('invoice_number');
        $nextNumber = $last ? intval(substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Save with a fresh invoice number, retrying if another request took the same number.
     * invoice_number has a unique index, so a collision raises instead of duplicating.
     */
    public function saveWithUniqueInvoiceNumber(int $attempts = 5): void
    {
        for ($i = 1; ; $i++) {
            $this->invoice_number = self::generateInvoiceNumber();
            try {
                $this->save();
                return;
            } catch (UniqueConstraintViolationException $e) {
                if ($i >= $attempts) {
                    throw $e;
                }
            }
        }
    }

    public function calculateTotal()
    {
        $this->total = $this->subtotal - $this->discount + $this->tax;
        return $this->total;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

class Payment extends Model
{
    use HasFactory;

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
        'items' => 'array',
    ];

    public const STATUSES = ['pending', 'paid', 'cancelled'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
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

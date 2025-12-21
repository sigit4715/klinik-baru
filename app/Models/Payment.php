<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'items' => 'array',
    ];

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
        $today = today();
        $lastPayment = self::whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->first();
        $nextNumber = $lastPayment ? intval(substr($lastPayment->invoice_number, -4)) + 1 : 1;
        return 'INV-' . $today->format('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotal()
    {
        $this->total = $this->subtotal - $this->discount + $this->tax;
        return $this->total;
    }
}

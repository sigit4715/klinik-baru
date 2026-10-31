<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    use Auditable;

    protected $fillable = ['payment_id', 'amount', 'reason', 'refund_method', 'restocked'];

    protected $casts = [
        'amount' => 'decimal:2',
        'restocked' => 'boolean',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function auditDescription(): ?string
    {
        return 'Refund ' . ($this->payment?->invoice_number ?? '#' . $this->payment_id);
    }
}

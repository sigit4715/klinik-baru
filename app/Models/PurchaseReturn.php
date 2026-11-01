<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/** Retur barang ke supplier */
class PurchaseReturn extends Model
{
    use Auditable;

    protected $fillable = ['supplier_id', 'purchase_id', 'return_date', 'reason'];

    protected $casts = [
        'return_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function saveWithNumber(): void
    {
        Purchase::saveNumbered($this, 'return_number', 'RTR', Carbon::parse($this->return_date));
    }
}

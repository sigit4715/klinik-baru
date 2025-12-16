<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'stock',
        'min_stock',
        'buy_price',
        'sell_price',
        'expired_date',
        'status',
    ];

    protected $casts = [
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'expired_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expired_date')
            ->where('expired_date', '<=', now()->addDays($days));
    }

    public static function generateCode()
    {
        $lastMedicine = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastMedicine ? intval(substr($lastMedicine->code, 4)) + 1 : 1;
        return 'MED-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $guarded = ['id'];

    public const TYPES = [
        'initial' => 'Stok awal',
        'in' => 'Stok masuk',
        'out' => 'Keluar (resep)',
        'return' => 'Retur / pengembalian',
        'adjustment' => 'Penyesuaian (stok opname)',
        'disposal' => 'Dibuang (rusak/kedaluwarsa)',
    ];

    /** Types a user can enter by hand on the stock card page */
    public const MANUAL_TYPES = ['in', 'return', 'adjustment', 'disposal'];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

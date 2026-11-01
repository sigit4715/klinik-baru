<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

/** Penerimaan barang: one supplier invoice (faktur) */
class Purchase extends Model
{
    use Auditable;

    protected $fillable = ['supplier_id', 'invoice_number', 'purchase_date', 'notes'];

    protected $casts = [
        'purchase_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function saveWithNumber(int $attempts = 5): void
    {
        self::saveNumbered($this, 'purchase_number', 'PB', Carbon::parse($this->purchase_date), $attempts);
    }

    /** Shared numbering for purchases (PB-...) and returns (RTR-...): PREFIX-Ymd-NNN */
    public static function saveNumbered(Model $model, string $column, string $prefix, Carbon $date, int $attempts = 5): void
    {
        $base = $prefix . '-' . $date->format('Ymd') . '-';
        for ($i = 1; ; $i++) {
            $max = $model->newQuery()->where($column, 'like', $base . '%')->pluck($column)
                ->map(fn ($n) => (int) substr($n, strlen($base)))->max();
            $model->{$column} = $base . str_pad((string) (($max ?? 0) + 1), 3, '0', STR_PAD_LEFT);
            try {
                $model->save();

                return;
            } catch (UniqueConstraintViolationException $e) {
                if ($i >= $attempts) {
                    throw $e;
                }
            }
        }
    }
}

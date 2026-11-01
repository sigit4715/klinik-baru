<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Medicine extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'code',
        'name',
        'generic_name',
        'dosage_form',
        'strength',
        'drug_class',
        'rack_location',
        'manufacturer',
        'barcode',
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

    /** Stock changes are already traced on the stock card */
    protected array $auditExclude = ['stock'];

    /** Golongan obat (regulasi) */
    public const DRUG_CLASSES = [
        'bebas' => 'Obat Bebas',
        'bebas_terbatas' => 'Obat Bebas Terbatas',
        'keras' => 'Obat Keras',
        'psikotropika' => 'Psikotropika',
        'narkotika' => 'Narkotika',
        'jamu' => 'Jamu / Herbal',
        'alkes' => 'Alat Kesehatan / BMHP',
    ];

    public const DOSAGE_FORMS = ['Tablet', 'Kaplet', 'Kapsul', 'Sirup', 'Suspensi', 'Drop', 'Puyer', 'Salep', 'Krim', 'Gel', 'Injeksi', 'Infus', 'Suppositoria', 'Inhaler', 'Tetes Mata', 'Tetes Telinga', 'Lainnya'];

    public function drugClassLabel(): ?string
    {
        return self::DRUG_CLASSES[$this->drug_class] ?? null;
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Change stock and write a stock card line in one locked transaction.
     *
     * @param int $change positive adds stock, negative removes it
     * @throws \Illuminate\Validation\ValidationException when stock would go below zero
     */
    public static function moveStock(int $medicineId, int $change, string $type, ?Model $reference = null, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($medicineId, $change, $type, $reference, $notes) {
            $medicine = static::withTrashed()->lockForUpdate()->findOrFail($medicineId);
            $before = (int) $medicine->stock;
            $after = $before + $change;

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'stock' => "Stok {$medicine->name} tidak cukup (tersedia {$before}, dibutuhkan " . abs($change) . ').',
                ]);
            }

            $medicine->stock = $after;
            $medicine->save();

            $movement = new StockMovement([
                'medicine_id' => $medicine->id,
                'type' => $type,
                'quantity' => $change,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $notes ? Str::limit($notes, 250) : null,
            ]);
            $movement->user_id = auth()->id();
            if ($reference) {
                $movement->reference()->associate($reference);
            }
            $movement->save();

            return $movement;
        });
    }

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

    /** Days before expiry that trigger a warning (Profil Klinik, default 30) */
    public static function expiryWarningDays(): int
    {
        return max(1, (int) (ClinicSetting::get('expiry_warning_days', 30) ?: 30));
    }

    public static function generateCode()
    {
        $lastMedicine = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastMedicine ? intval(substr($lastMedicine->code, 4)) + 1 : 1;
        return 'MED-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}

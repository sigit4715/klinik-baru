<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'code',
        'name',
        'category',
        'poli_id',
        'description',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public const CATEGORIES = [
        'konsultasi' => 'Konsultasi',
        'tindakan' => 'Tindakan',
        'penunjang' => 'Penunjang (Lab/Radiologi/EKG)',
    ];

    public function poli()
    {
        return $this->belongsTo(Poli::class)->withTrashed();
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    public function scopeSupport($query)
    {
        return $query->where('category', 'penunjang');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public static function generateCode()
    {
        $lastService = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastService ? intval(substr($lastService->code, 4)) + 1 : 1;
        return 'SRV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}

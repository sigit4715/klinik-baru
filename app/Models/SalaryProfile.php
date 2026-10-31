<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class SalaryProfile extends Model
{
    use Auditable;

    protected $fillable = [
        'base_salary', 'sitting_fee', 'fixed_allowance', 'allowance_note', 'deduct_absence',
        'late_fee_per_incident', 'late_fee_per_minute', 'earns_service_fee', 'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'sitting_fee' => 'decimal:2',
        'fixed_allowance' => 'decimal:2',
        'late_fee_per_incident' => 'decimal:2',
        'late_fee_per_minute' => 'decimal:2',
        'deduct_absence' => 'boolean',
        'earns_service_fee' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use Auditable;

    /** Long detail arrays are noise in the audit log; totals are logged */
    protected array $auditExclude = ['service_details'];

    protected $fillable = [
        'payroll_period_id', 'user_id', 'employee_name', 'role_label',
        'scheduled_shifts', 'attended_shifts', 'absent_shifts', 'leave_shifts', 'late_count', 'late_minutes',
        'base_salary', 'sitting_fee_rate', 'sitting_fee_total', 'service_count', 'service_fee_total', 'allowance_total',
        'absence_deduction', 'late_deduction', 'manual_additions', 'manual_deductions', 'net_total',
        'service_details', 'adjustments',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'sitting_fee_rate' => 'decimal:2',
        'sitting_fee_total' => 'decimal:2',
        'service_fee_total' => 'decimal:2',
        'allowance_total' => 'decimal:2',
        'absence_deduction' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'manual_additions' => 'decimal:2',
        'manual_deductions' => 'decimal:2',
        'net_total' => 'decimal:2',
        'service_details' => 'array',
        'adjustments' => 'array',
    ];

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grossTotal(): float
    {
        return (float) $this->base_salary + (float) $this->sitting_fee_total + (float) $this->service_fee_total
            + (float) $this->allowance_total + (float) $this->manual_additions;
    }

    public function deductionTotal(): float
    {
        return (float) $this->absence_deduction + (float) $this->late_deduction + (float) $this->manual_deductions;
    }

    /** Recompute manual totals from the adjustment lines, then the net amount */
    public function refreshTotals(): void
    {
        $add = $deduct = 0.0;
        foreach ($this->adjustments ?? [] as $line) {
            if (($line['type'] ?? '') === 'deduct') {
                $deduct += (float) ($line['amount'] ?? 0);
            } else {
                $add += (float) ($line['amount'] ?? 0);
            }
        }
        $this->manual_additions = round($add, 2);
        $this->manual_deductions = round($deduct, 2);
        $this->net_total = round($this->grossTotal() - $this->deductionTotal(), 2);
    }

    public static function rupiah($amount): string
    {
        $amount = (float) $amount;

        return ($amount < 0 ? '- ' : '') . 'Rp ' . number_format(abs($amount), 0, ',', '.');
    }
}

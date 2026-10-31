<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\ServiceFee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Monthly payslip calculation.
 *
 *   net = base salary - absence deduction - late deduction
 *       + sitting fee x attended shifts + doctor service fees + fixed allowance
 *       + manual additions - manual deductions
 *
 * - Attendance comes from AttendanceService::reportRows (present/late = attended, absent = alpa;
 *   permission/sick/leave are not deducted).
 * - Absence deduction = base salary / scheduled shifts x absent shifts (when enabled for the employee).
 * - Service fees: service lines of payments with status "paid" made in the month, for visits of the
 *   doctor linked to the login. Fully refunded payments are excluded. Fee rule: doctor override,
 *   else the default for the service, else nothing.
 */
class PayrollService
{
    public function __construct(private AttendanceService $attendance)
    {
    }

    /** @return int number of payslips written */
    public function calculate(PayrollPeriod $period): int
    {
        if (! $period->isDraft()) {
            throw ValidationException::withMessages(['period' => 'Periode yang sudah dikunci tidak bisa dihitung ulang.']);
        }

        $start = $period->start();
        $end = $period->end();

        return DB::transaction(function () use ($period, $start, $end) {
            $users = User::with(['salaryProfile', 'roleModel'])->whereHas('salaryProfile')->get();
            $written = [];

            foreach ($users as $user) {
                $computed = $this->computeFor($user, $start, $end);

                // Inactive employees are only paid for what they actually did in the period
                if (! $user->is_active && $computed['attended_shifts'] === 0 && $computed['service_count'] === 0) {
                    continue;
                }
                if (! $user->is_active) {
                    $computed['base_salary'] = 0;
                    $computed['absence_deduction'] = 0;
                    $computed['allowance_total'] = 0;
                }

                $payslip = Payslip::firstOrNew(['payroll_period_id' => $period->id, 'user_id' => $user->id]);
                // Manual bonus/deduction lines entered earlier are kept on recalculation
                $payslip->fill($computed);
                $payslip->adjustments = $payslip->adjustments ?? [];
                $payslip->refreshTotals();
                $payslip->save();
                $written[] = $payslip->id;
            }

            // Employees whose salary settings were removed since the last calculation
            Payslip::where('payroll_period_id', $period->id)->whereNotIn('id', $written ?: [0])->delete();

            $period->calculated_at = now();
            $period->save();

            return count($written);
        });
    }

    public function computeFor(User $user, $start, $end): array
    {
        $profile = $user->salaryProfile;
        $rows = $this->attendance->reportRows($start->copy(), $end->copy(), $user->id);

        $scheduled = $rows->whereNotNull('schedule')->count();
        $attended = $rows->whereIn('status', ['present', 'late'])->count();
        $absent = $rows->where('status', 'absent')->count();
        $leave = $rows->whereIn('status', ['permission', 'sick', 'leave'])->count();
        $lateRows = $rows->where('status', 'late');
        $lateCount = $lateRows->count();
        $lateMinutes = (int) $lateRows->sum(fn ($r) => $r['attendance']?->late_minutes ?? 0);

        $base = (float) $profile->base_salary;
        $absenceDeduction = $profile->deduct_absence && $scheduled > 0
            ? round($base / $scheduled * $absent, 2)
            : 0;
        $lateDeduction = round($lateCount * (float) $profile->late_fee_per_incident + $lateMinutes * (float) $profile->late_fee_per_minute, 2);

        [$serviceCount, $serviceTotal, $serviceLines] = $profile->earns_service_fee && $user->doctor_id
            ? $this->serviceFees($user->doctor_id, $start, $end)
            : [0, 0.0, []];

        return [
            'employee_name' => $user->name,
            'role_label' => $user->roleLabel(),
            'scheduled_shifts' => $scheduled,
            'attended_shifts' => $attended,
            'absent_shifts' => $absent,
            'leave_shifts' => $leave,
            'late_count' => $lateCount,
            'late_minutes' => $lateMinutes,
            'base_salary' => $base,
            'sitting_fee_rate' => (float) $profile->sitting_fee,
            'sitting_fee_total' => round((float) $profile->sitting_fee * $attended, 2),
            'service_count' => $serviceCount,
            'service_fee_total' => $serviceTotal,
            'allowance_total' => (float) $profile->fixed_allowance,
            'absence_deduction' => $absenceDeduction,
            'late_deduction' => $lateDeduction,
            'service_details' => $serviceLines,
        ];
    }

    /** @return array{0: int, 1: float, 2: array} [number of services, total fee, lines] */
    public function serviceFees(int $doctorId, $start, $end): array
    {
        $payments = Payment::with('appointment.patient')
            ->where('status', 'paid')
            ->where('created_at', '>=', $start->copy()->startOfDay())
            ->where('created_at', '<=', $end->copy()->endOfDay())
            ->whereHas('appointment', fn ($q) => $q->where('doctor_id', $doctorId))
            ->orderBy('created_at')
            ->get();

        $rules = ServiceFee::where(fn ($q) => $q->whereNull('doctor_id')->orWhere('doctor_id', $doctorId))->get();
        $override = $rules->whereNotNull('doctor_id')->keyBy('service_id');
        $default = $rules->whereNull('doctor_id')->keyBy('service_id');

        $count = 0;
        $total = 0.0;
        $lines = [];
        foreach ($payments as $payment) {
            foreach ($payment->items ?? [] as $item) {
                if (($item['type'] ?? null) !== 'service') {
                    continue;
                }
                $serviceId = (int) ($item['id'] ?? 0);
                $rule = $override->get($serviceId) ?? $default->get($serviceId);
                $qty = (int) ($item['qty'] ?? 1);
                $price = (float) ($item['price'] ?? 0);
                $fee = $rule ? $rule->feeFor($qty, $price) : 0.0;

                $count += $qty;
                $total += $fee;
                $lines[] = [
                    'date' => $payment->created_at->format('Y-m-d'),
                    'invoice' => $payment->invoice_number,
                    'patient' => $payment->appointment?->patient?->name,
                    'service' => $item['name'] ?? '-',
                    'qty' => $qty,
                    'price' => $price,
                    'rule' => $rule?->label() ?? 'Belum ada tarif',
                    'fee' => $fee,
                ];
            }
        }

        return [$count, round($total, 2), $lines];
    }
}

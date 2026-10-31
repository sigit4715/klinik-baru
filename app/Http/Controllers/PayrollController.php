<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Monthly payroll: calculate (draft) -> manual adjustments -> lock -> mark paid.
 */
class PayrollController extends Controller
{
    public function __construct(private PayrollService $payroll)
    {
    }

    public function index()
    {
        $periods = PayrollPeriod::withCount('payslips')->withSum('payslips', 'net_total')->orderByDesc('period')->get();
        $title = 'Penggajian';
        $description = 'Hitung dan bayar gaji bulanan';

        return view('pages.clinic.payroll.index', compact('title', 'description', 'periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['period' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/']], [
            'period.required' => 'Pilih bulan.',
            'period.regex' => 'Format bulan tidak valid.',
        ]);

        $period = PayrollPeriod::firstOrCreate(['period' => $data['period']]);
        if ($period->isDraft()) {
            $count = $this->payroll->calculate($period);
            $message = "Gaji {$period->label()} dihitung untuk {$count} karyawan.";
        } else {
            $message = "Gaji {$period->label()} sudah dikunci, tidak dihitung ulang.";
        }

        return redirect()->route('clinic.payroll.show', [app()->getLocale(), $period->id])->with('success', $message);
    }

    public function show($language, $id)
    {
        $period = PayrollPeriod::with(['lockedBy', 'paidBy'])->findOrFail($id);
        $payslips = $period->payslips()->orderBy('employee_name')->get();
        $title = 'Penggajian ' . $period->label();
        $description = 'Daftar slip gaji';

        return view('pages.clinic.payroll.show', compact('title', 'description', 'period', 'payslips'));
    }

    public function recalculate($language, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        try {
            $count = $this->payroll->calculate($period);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', "Dihitung ulang dari absensi dan tindakan terbaru untuk {$count} karyawan. Bonus/potongan manual tetap.");
    }

    public function changeStatus(Request $request, $language, $id)
    {
        $action = $request->validate(['action' => 'required|in:lock,unlock,paid'])['action'];

        $message = DB::transaction(function () use ($id, $action, $request) {
            $period = PayrollPeriod::lockForUpdate()->findOrFail($id);
            $allowed = ['lock' => 'draft', 'unlock' => 'locked', 'paid' => 'locked'];
            if ($period->status !== $allowed[$action]) {
                throw ValidationException::withMessages(['status' => 'Status periode tidak bisa diubah dengan cara ini.']);
            }

            if ($action === 'lock') {
                $period->status = 'locked';
                $period->locked_at = now();
                $period->locked_by = $request->user()->id;
                $message = 'Gaji dikunci. Slip bisa dicetak dan dilihat karyawan.';
            } elseif ($action === 'unlock') {
                $period->status = 'draft';
                $period->locked_at = null;
                $period->locked_by = null;
                $message = 'Kunci dibuka. Gaji bisa dihitung ulang dan diubah.';
            } else {
                $period->status = 'paid';
                $period->paid_at = now();
                $period->paid_by = $request->user()->id;
                $message = 'Gaji ditandai sudah dibayar. Data periode ini tidak bisa diubah lagi.';
            }
            $period->save();

            return $message;
        });

        return back()->with('success', $message);
    }

    public function payslip($language, $id)
    {
        $payslip = Payslip::with(['period', 'user'])->findOrFail($id);
        $title = 'Slip Gaji ' . $payslip->employee_name;
        $description = $payslip->period->label();

        return view('pages.clinic.payroll.payslip', compact('title', 'description', 'payslip'));
    }

    /** Manual bonus / deduction lines (draft only) */
    public function adjust(Request $request, $language, $id)
    {
        $data = $request->validate([
            'adjustments' => 'nullable|array|max:30',
            'adjustments.*.type' => 'required|in:add,deduct',
            'adjustments.*.label' => 'nullable|string|max:100',
            'adjustments.*.amount' => 'nullable|numeric|min:0|max:999999999999',
        ]);

        DB::transaction(function () use ($data, $id) {
            $payslip = Payslip::lockForUpdate()->findOrFail($id);
            if (! $payslip->period->isDraft()) {
                throw ValidationException::withMessages(['adjustments' => 'Slip yang sudah dikunci tidak bisa diubah.']);
            }

            $lines = [];
            foreach ($data['adjustments'] ?? [] as $line) {
                if (empty($line['label']) || (float) ($line['amount'] ?? 0) <= 0) {
                    continue;
                }
                $lines[] = ['type' => $line['type'], 'label' => $line['label'], 'amount' => round((float) $line['amount'], 2)];
            }
            $payslip->adjustments = $lines;
            $payslip->refreshTotals();
            $payslip->save();
        });

        return back()->with('success', 'Bonus/potongan disimpan.');
    }

    public function export($language, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $payslips = $period->payslips()->orderBy('employee_name')->get();

        return response()->streamDownload(function () use ($payslips) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Nama', 'Bagian', 'Shift Terjadwal', 'Shift Hadir', 'Alpa', 'Izin/Sakit/Cuti', 'Terlambat (kali)', 'Terlambat (menit)',
                'Gaji Pokok', 'Uang Duduk', 'Jumlah Tindakan', 'Fee Tindakan', 'Tunjangan', 'Bonus Manual', 'Potongan Alpa', 'Potongan Terlambat', 'Potongan Manual', 'Gaji Bersih'], ';');
            foreach ($payslips as $p) {
                fputcsv($out, [
                    $p->employee_name, $p->role_label, $p->scheduled_shifts, $p->attended_shifts, $p->absent_shifts, $p->leave_shifts,
                    $p->late_count, $p->late_minutes, $p->base_salary, $p->sitting_fee_total, $p->service_count, $p->service_fee_total,
                    $p->allowance_total, $p->manual_additions, $p->absence_deduction, $p->late_deduction, $p->manual_deductions, $p->net_total,
                ], ';');
            }
            fclose($out);
        }, "gaji-{$period->period}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Printable payslip; employees may print their own once the period is locked */
    public function print(Request $request, $language, $id)
    {
        $payslip = Payslip::with('period')->findOrFail($id);
        $user = $request->user();
        $isOwnPublished = $payslip->user_id === $user->id && ! $payslip->period->isDraft() && $user->can('payroll.self');
        abort_unless($user->can('payroll.manage') || $isOwnPublished, 403);

        $clinicSettings = ClinicSetting::getAllSettings();

        return view('pages.clinic.payroll.print', compact('payslip', 'clinicSettings'));
    }

    /** "Slip Gaji Saya": the employee's own locked/paid payslips */
    public function mine(Request $request)
    {
        $payslips = Payslip::with('period')
            ->where('user_id', $request->user()->id)
            ->whereHas('period', fn ($q) => $q->whereIn('status', ['locked', 'paid']))
            ->get()
            ->sortByDesc(fn ($p) => $p->period->period)
            ->values();

        $title = 'Slip Gaji Saya';
        $description = 'Slip gaji yang sudah dikunci';

        return view('pages.clinic.payroll.mine', compact('title', 'description', 'payslips'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentRefund;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Revenue reports. Income = payments received (by payment date) minus refunds (by refund date).
 * Grouping is done in PHP so the reports work the same on MySQL/MariaDB and SQLite.
 */
class ReportController extends Controller
{
    /**
     * Laporan Pendapatan Harian
     */
    public function daily(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $title = 'Laporan Pendapatan Harian';
        $description = 'Laporan pendapatan harian klinik';

        $selectedDate = Carbon::parse($request->get('date', Carbon::today()->format('Y-m-d')));
        [$start, $end] = [$selectedDate->copy()->startOfDay(), $selectedDate->copy()->endOfDay()];

        $payments = $this->payments($start, $end)->sortByDesc('created_at')->values();
        $refunds = $this->refunds($start, $end);

        $grossIncome = $payments->sum('total');
        $totalRefunds = $refunds->sum('amount');
        $totalIncome = $grossIncome - $totalRefunds;
        $totalTransactions = $payments->count();
        $paymentMethods = $this->byMethod($payments, $refunds);

        return view('pages.clinic.reports.daily', compact(
            'title',
            'description',
            'payments',
            'refunds',
            'selectedDate',
            'grossIncome',
            'totalRefunds',
            'totalIncome',
            'totalTransactions',
            'paymentMethods'
        ));
    }

    /**
     * Laporan Pendapatan Bulanan
     */
    public function monthly(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);

        $title = 'Laporan Pendapatan Bulanan';
        $description = 'Laporan pendapatan bulanan klinik';

        $selectedMonth = Carbon::parse($request->get('month', Carbon::today()->format('Y-m')) . '-01');
        [$start, $end] = [$selectedMonth->copy()->startOfMonth(), $selectedMonth->copy()->endOfMonth()];

        $payments = $this->payments($start, $end)->sortByDesc('created_at')->values();
        $refunds = $this->refunds($start, $end);

        // Daily breakdown: one row per day that had a payment or a refund
        $paymentsByDay = $payments->groupBy(fn ($p) => $p->created_at->toDateString());
        $refundsByDay = $refunds->groupBy(fn ($r) => $r->created_at->toDateString());
        $dailyBreakdown = $paymentsByDay->keys()->merge($refundsByDay->keys())->unique()->sort()->values()
            ->map(function ($date) use ($paymentsByDay, $refundsByDay) {
                $gross = $paymentsByDay->get($date, collect())->sum('total');
                $refund = $refundsByDay->get($date, collect())->sum('amount');

                return (object) [
                    'date' => $date,
                    'transactions' => $paymentsByDay->get($date, collect())->count(),
                    'refund' => $refund,
                    'total' => $gross - $refund,
                ];
            });

        $totalRefunds = $refunds->sum('amount');
        $totalIncome = $payments->sum('total') - $totalRefunds;
        $totalTransactions = $payments->count();
        $paymentMethods = $this->byMethod($payments, $refunds);

        return view('pages.clinic.reports.monthly', compact(
            'title',
            'description',
            'payments',
            'refunds',
            'selectedMonth',
            'totalRefunds',
            'totalIncome',
            'totalTransactions',
            'paymentMethods',
            'dailyBreakdown'
        ));
    }

    /**
     * Laporan Pendapatan Tahunan
     */
    public function yearly(Request $request)
    {
        $request->validate(['year' => 'nullable|integer|min:2000|max:2100']);

        $title = 'Laporan Pendapatan Tahunan';
        $description = 'Laporan pendapatan tahunan klinik';

        $year = (int) $request->get('year', Carbon::today()->year);
        [$start, $end] = [Carbon::create($year)->startOfYear(), Carbon::create($year)->endOfYear()];

        $payments = $this->payments($start, $end);
        $refunds = $this->refunds($start, $end);

        $paymentsByMonth = $payments->groupBy(fn ($p) => (int) $p->created_at->month);
        $refundsByMonth = $refunds->groupBy(fn ($r) => (int) $r->created_at->month);

        $monthlyData = collect();
        for ($i = 1; $i <= 12; $i++) {
            $gross = $paymentsByMonth->get($i, collect())->sum('total');
            $refund = $refundsByMonth->get($i, collect())->sum('amount');
            $monthlyData->put($i, [
                'month' => $i,
                'month_name' => Carbon::create()->month($i)->translatedFormat('F'),
                'refund' => $refund,
                'total' => $gross - $refund,
                'transactions' => $paymentsByMonth->get($i, collect())->count(),
            ]);
        }

        $totalRefunds = $refunds->sum('amount');
        $totalIncome = $payments->sum('total') - $totalRefunds;
        $totalTransactions = $payments->count();
        $paymentMethods = $this->byMethod($payments, $refunds);

        $availableYears = Payment::query()->pluck('created_at')
            ->map(fn ($date) => Carbon::parse($date)->year)
            ->push(Carbon::today()->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('pages.clinic.reports.yearly', compact(
            'title',
            'description',
            'year',
            'monthlyData',
            'totalRefunds',
            'totalIncome',
            'totalTransactions',
            'paymentMethods',
            'availableYears'
        ));
    }

    private function payments(Carbon $start, Carbon $end): Collection
    {
        return Payment::revenue()
            ->whereBetween('created_at', [$start, $end])
            ->with(['patient', 'appointment.doctor'])
            ->get();
    }

    private function refunds(Carbon $start, Carbon $end): Collection
    {
        return PaymentRefund::with(['payment.patient', 'user'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get();
    }

    /** Net amount per payment method; refunds count against the method used to pay them back */
    private function byMethod(Collection $payments, Collection $refunds): Collection
    {
        $methods = $payments->groupBy('payment_method')->map->sum('total');

        foreach ($refunds->groupBy('refund_method')->map->sum('amount') as $method => $amount) {
            $methods[$method] = ($methods[$method] ?? 0) - $amount;
        }

        return $methods;
    }
}

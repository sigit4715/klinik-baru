<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Laporan Pendapatan Harian
     */
    public function daily(Request $request)
    {
        $title = 'Laporan Pendapatan Harian';
        $description = 'Laporan pendapatan harian klinik';

        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $payments = Payment::where('status', 'paid')
            ->whereDate('created_at', $selectedDate)
            ->with(['patient', 'appointment.doctor'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalIncome = $payments->sum('total');
        $totalTransactions = $payments->count();
        $paymentMethods = $payments->groupBy('payment_method')->map->sum('total');

        return view('pages.clinic.reports.daily', compact(
            'title',
            'description',
            'payments',
            'selectedDate',
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
        $title = 'Laporan Pendapatan Bulanan';
        $description = 'Laporan pendapatan bulanan klinik';

        $month = $request->get('month', Carbon::today()->format('Y-m'));
        $selectedMonth = Carbon::parse($month . '-01');

        $payments = Payment::where('status', 'paid')
            ->whereYear('created_at', $selectedMonth->year)
            ->whereMonth('created_at', $selectedMonth->month)
            ->with(['patient', 'appointment.doctor'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Daily breakdown for the month
        $dailyBreakdown = Payment::where('status', 'paid')
            ->whereYear('created_at', $selectedMonth->year)
            ->whereMonth('created_at', $selectedMonth->month)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as transactions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalIncome = $payments->sum('total');
        $totalTransactions = $payments->count();
        $paymentMethods = $payments->groupBy('payment_method')->map->sum('total');

        return view('pages.clinic.reports.monthly', compact(
            'title',
            'description',
            'payments',
            'selectedMonth',
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
        $title = 'Laporan Pendapatan Tahunan';
        $description = 'Laporan pendapatan tahunan klinik';

        $year = $request->get('year', Carbon::today()->year);

        // Monthly breakdown for the year
        $monthlyBreakdown = Payment::where('status', 'paid')
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, SUM(total) as total, COUNT(*) as transactions')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Fill in missing months with zero
        $monthlyData = collect();
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData->put($i, [
                'month' => $i,
                'month_name' => Carbon::create()->month($i)->translatedFormat('F'),
                'total' => $monthlyBreakdown->get($i)->total ?? 0,
                'transactions' => $monthlyBreakdown->get($i)->transactions ?? 0,
            ]);
        }

        $totalIncome = $monthlyBreakdown->sum('total');
        $totalTransactions = $monthlyBreakdown->sum('transactions');

        // Payment method breakdown for the year
        $paymentMethods = Payment::where('status', 'paid')
            ->whereYear('created_at', $year)
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // Available years for dropdown
        $availableYears = Payment::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([Carbon::today()->year]);
        }

        return view('pages.clinic.reports.yearly', compact(
            'title',
            'description',
            'year',
            'monthlyData',
            'totalIncome',
            'totalTransactions',
            'paymentMethods',
            'availableYears'
        ));
    }
}

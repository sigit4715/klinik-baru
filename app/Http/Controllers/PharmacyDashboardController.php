<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\StockMovement;

class PharmacyDashboardController extends Controller
{
    public function index()
    {
        $days = Medicine::expiryWarningDays();
        $active = fn () => Medicine::where('status', 'active');

        $lowStock = $active()->lowStock()->orderBy('stock')->get();
        $expired = $active()->whereNotNull('expired_date')->whereDate('expired_date', '<', today())->where('stock', '>', 0)->orderBy('expired_date')->get();
        $expiring = $active()->whereNotNull('expired_date')->whereDate('expired_date', '>=', today())
            ->whereDate('expired_date', '<=', today()->addDays($days))->where('stock', '>', 0)->orderBy('expired_date')->get();
        $stockValue = (float) Medicine::where('stock', '>', 0)->selectRaw('SUM(stock * buy_price) as v')->value('v');

        $todayRecords = MedicalRecord::with(['patient', 'doctor', 'appointment'])->whereDate('created_at', today())->latest()->get()
            ->filter(fn ($r) => ! empty($r->prescriptions));

        $topOut = StockMovement::with('medicine')->where('type', 'out')->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('medicine_id, -SUM(quantity) as qty')->groupBy('medicine_id')->orderByDesc('qty')->limit(10)->get();

        $recentPurchases = Purchase::with('supplier')->latest('id')->limit(5)->get();

        $title = 'Dashboard Farmasi';
        $description = 'Stok, kedaluwarsa, dan resep hari ini';

        return view('pages.clinic.pharmacy.dashboard', compact('title', 'description', 'days', 'lowStock', 'expired', 'expiring', 'stockValue', 'todayRecords', 'topOut', 'recentPurchases'));
    }
}

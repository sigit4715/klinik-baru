<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Medicine;
use App\Models\Service;
use App\Models\MedicalRecord;
use Carbon\Carbon;

class ClinicDashboardController extends Controller
{
    public function index()
    {
        $title = "Dashboard Klinik";
        $description = "Ringkasan statistik klinik";

        // Statistics
        $totalPatients = Patient::count();
        $todayAppointments = Appointment::today()->count();
        $waitingAppointments = Appointment::today()->waiting()->count();
        $inProgressAppointments = Appointment::today()->where('status', 'in_progress')->count();
        $completedToday = Appointment::today()->where('status', 'completed')->count();

        // Revenue statistics
        $today = today();
        $startOfWeek = $today->copy()->startOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();

        // Net revenue (payments minus refunds); endOfDay so today is counted (today() alone is 00:00)
        $todayRevenue = Payment::revenueBetween($today->copy()->startOfDay(), $today->copy()->endOfDay())['net'];
        $weekRevenue = Payment::revenueBetween($startOfWeek, $today->copy()->endOfDay())['net'];
        $monthRevenue = Payment::revenueBetween($startOfMonth, $today->copy()->endOfDay())['net'];

        // Alerts
        $lowStockMedicines = Medicine::lowStock()->count();
        $expiringMedicines = Medicine::expiringSoon(30)->count();

        // Today's queue
        $todayQueue = Appointment::with(['patient', 'doctor'])
            ->today()
            ->orderBy('queue_number')
            ->limit(10)
            ->get();

        // Active doctors
        $doctors = Doctor::where('status', 'active')->get();

        // Recent patients
        $recentPatients = Patient::orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        return view('pages.clinic.dashboard.index', compact(
            'title',
            'description',
            'totalPatients',
            'todayAppointments',
            'waitingAppointments',
            'inProgressAppointments',
            'completedToday',
            'todayRevenue',
            'weekRevenue',
            'monthRevenue',
            'lowStockMedicines',
            'expiringMedicines',
            'todayQueue',
            'doctors',
            'recentPatients'
        ));
    }
}

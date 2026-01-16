<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientReportController extends Controller
{
    /**
     * Keywords untuk setiap kategori (dicari di diagnosis, treatment, anamnesa)
     */
    protected $categoryKeywords = [
        'kb' => ['KB', 'keluarga berencana', 'kontrasepsi', 'IUD', 'pil kb', 'suntik kb', 'implan', 'akseptor', 'AKDR', 'MOW', 'MOP', 'kondom'],
        'imunisasi' => ['imunisasi', 'vaksin', 'vaksinasi', 'BCG', 'polio', 'DPT', 'campak', 'hepatitis', 'MR', 'MMR', 'IPV', 'HiB', 'PCV', 'rotavirus'],
        'ibu_hamil' => ['hamil', 'kehamilan', 'ANC', 'prenatal', 'antenatal', 'trimester', 'bumil', 'gravida', 'gestasi'],
        'melahirkan' => ['melahirkan', 'persalinan', 'anak lahir', 'bersalin', 'partus', 'postpartum', 'nifas', 'BBL', 'lahir', 'PNC', 'post natal'],
    ];

    /**
     * Laporan KB
     */
    public function kb(Request $request)
    {
        return $this->generateReport($request, 'kb', 'Laporan KB (Keluarga Berencana)', 'Laporan pasien KB');
    }

    /**
     * Laporan Imunisasi
     */
    public function imunisasi(Request $request)
    {
        return $this->generateReport($request, 'imunisasi', 'Laporan Imunisasi', 'Laporan pasien imunisasi');
    }

    /**
     * Laporan Ibu Hamil
     */
    public function ibuHamil(Request $request)
    {
        return $this->generateReport($request, 'ibu_hamil', 'Laporan Ibu Hamil', 'Laporan pemeriksaan ibu hamil');
    }

    /**
     * Laporan Melahirkan
     */
    public function melahirkan(Request $request)
    {
        return $this->generateReport($request, 'melahirkan', 'Laporan Melahirkan', 'Laporan pasien melahirkan');
    }

    /**
     * Laporan Pasien Umum
     */
    public function umum(Request $request)
    {
        $title = 'Laporan Pasien Umum';
        $description = 'Laporan pasien selain KB, Imunisasi, Ibu Hamil, Melahirkan';

        $period = $request->get('period', 'all');

        // Get filter values
        $filters = $this->getFilterValues($request, $period);

        // Build query excluding all category keywords
        $query = MedicalRecord::with(['patient', 'doctor', 'appointment']);

        // Exclude all categories
        foreach ($this->categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                $query->whereRaw(
                    "CONCAT(LOWER(COALESCE(diagnosis,'')), ' ', LOWER(COALESCE(treatment,'')), ' ', LOWER(COALESCE(anamnesa,''))) NOT LIKE ?",
                    ['%' . strtolower($keyword) . '%']
                );
            }
        }

        // Apply period filter
        $this->applyPeriodFilter($query, $period, $request);

        $records = $query->orderBy('created_at', 'desc')->get();
        $statistics = $this->calculateStatistics($records);

        return view('pages.clinic.patient-reports.report', compact(
            'title',
            'description',
            'records',
            'statistics',
            'period',
            'filters'
        ))->with('category', 'umum');
    }

    /**
     * Generate report berdasarkan kategori
     */
    private function generateReport(Request $request, $category, $title, $description)
    {
        $period = $request->get('period', 'all');

        // Get filter values
        $filters = $this->getFilterValues($request, $period);

        $keywords = $this->categoryKeywords[$category] ?? [];

        // Build query with OR conditions for keywords
        $query = MedicalRecord::with(['patient', 'doctor', 'appointment']);

        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhereRaw(
                    "CONCAT(LOWER(COALESCE(diagnosis,'')), ' ', LOWER(COALESCE(treatment,'')), ' ', LOWER(COALESCE(anamnesa,''))) LIKE ?",
                    ['%' . strtolower($keyword) . '%']
                );
            }
        });

        // Apply period filter
        $this->applyPeriodFilter($query, $period, $request);

        $records = $query->orderBy('created_at', 'desc')->get();
        $statistics = $this->calculateStatistics($records);

        return view('pages.clinic.patient-reports.report', compact(
            'title',
            'description',
            'records',
            'statistics',
            'period',
            'filters'
        ))->with('category', $category);
    }

    /**
     * Get filter values based on period
     */
    private function getFilterValues(Request $request, $period)
    {
        return [
            'date_from' => $request->get('date_from', Carbon::today()->format('Y-m-d')),
            'date_to' => $request->get('date_to', Carbon::today()->format('Y-m-d')),
            'month_from' => $request->get('month_from', Carbon::today()->format('Y-m')),
            'month_to' => $request->get('month_to', Carbon::today()->format('Y-m')),
            'year_from' => $request->get('year_from', Carbon::today()->year),
            'year_to' => $request->get('year_to', Carbon::today()->year),
        ];
    }

    /**
     * Apply period filter to query (with date range support)
     */
    private function applyPeriodFilter($query, $period, $request)
    {
        switch ($period) {
            case 'daily':
                $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
                $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));
                $query->whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo);
                break;
            case 'monthly':
                $monthFrom = $request->get('month_from', Carbon::today()->format('Y-m'));
                $monthTo = $request->get('month_to', Carbon::today()->format('Y-m'));
                $startDate = Carbon::parse($monthFrom . '-01')->startOfMonth();
                $endDate = Carbon::parse($monthTo . '-01')->endOfMonth();
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
                break;
            case 'yearly':
                $yearFrom = $request->get('year_from', Carbon::today()->year);
                $yearTo = $request->get('year_to', Carbon::today()->year);
                $query->whereYear('created_at', '>=', $yearFrom)
                    ->whereYear('created_at', '<=', $yearTo);
                break;
            case 'all':
                // No filter - show all data
                break;
        }
    }

    /**
     * Calculate statistics from records
     */
    private function calculateStatistics($records)
    {
        $total = $records->count();
        $maleCount = $records->filter(fn($r) => $r->patient->gender == 'L')->count();
        $femaleCount = $records->filter(fn($r) => $r->patient->gender == 'P')->count();

        // Group by doctor
        $doctorStats = $records->groupBy(fn($r) => $r->doctor->name ?? 'Unknown')
            ->map->count()
            ->sortDesc();

        return [
            'total' => $total,
            'male' => $maleCount,
            'female' => $femaleCount,
            'by_doctor' => $doctorStats,
        ];
    }
}

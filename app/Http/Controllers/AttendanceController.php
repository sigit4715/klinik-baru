<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\StaffSchedule;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * "Absensi Saya": the logged-in employee checks in/out from the clinic location.
 */
class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $settings = $this->service->settings();
        $context = $this->service->context($user);

        $upcoming = StaffSchedule::with(['shift', 'attendance'])
            ->where('user_id', $user->id)
            ->whereDate('work_date', '>=', today()->toDateString())
            ->whereDate('work_date', '<=', today()->addDays(13)->toDateString())
            ->orderBy('work_date')
            ->get();

        $month = $request->query('month') && preg_match('/^\d{4}-\d{2}$/', $request->query('month'))
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth()
            : today()->startOfMonth();
        $rows = $this->service->reportRows($month->copy(), $month->copy()->endOfMonth(), $user->id);

        $title = 'Absensi Saya';
        $description = 'Absen masuk/pulang dan jadwal kerja';

        return view('pages.clinic.attendance.my', [
            'title' => $title,
            'description' => $description,
            'settings' => $settings,
            'configured' => $this->service->isConfigured($settings),
            'context' => $context,
            'upcoming' => $upcoming,
            'rows' => $rows,
            'month' => $month,
        ]);
    }

    public function checkIn(Request $request)
    {
        return $this->record($request, 'checkIn', 'Absen masuk berhasil pada pukul %s.');
    }

    public function checkOut(Request $request)
    {
        return $this->record($request, 'checkOut', 'Absen pulang berhasil pada pukul %s.');
    }

    private function record(Request $request, string $method, string $message)
    {
        $position = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0|max:100000',
        ], [
            'latitude.required' => 'Lokasi belum terbaca. Izinkan akses lokasi di browser lalu coba lagi.',
            'longitude.required' => 'Lokasi belum terbaca. Izinkan akses lokasi di browser lalu coba lagi.',
            'accuracy.required' => 'Lokasi belum terbaca. Izinkan akses lokasi di browser lalu coba lagi.',
        ]);

        try {
            /** @var Attendance $attendance */
            $attendance = $this->service->{$method}($request->user(), array_map('floatval', $position), $request);
        } catch (ValidationException $e) {
            return redirect()->route('clinic.attendance.my', app()->getLocale())->withErrors($e->errors());
        }

        $time = ($method === 'checkIn' ? $attendance->check_in_at : $attendance->check_out_at)->format('H:i');
        $extra = $method === 'checkIn' && $attendance->status === 'late' ? " Terlambat {$attendance->late_minutes} menit." : '';

        return redirect()->route('clinic.attendance.my', app()->getLocale())
            ->with('success', sprintf($message, $time) . $extra);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Role;
use App\Models\StaffSchedule;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Attendance report per employee, CSV export, and admin corrections
 * (manual check-in/out, permission/sick/leave/absent).
 */
class AttendanceReportController extends Controller
{
    public function __construct(private AttendanceService $service)
    {
    }

    public function index(Request $request)
    {
        [$from, $to, $userId, $role, $employees] = $this->filters($request);
        $rows = $this->service->reportRows($from, $to, $userId, $employees->pluck('id')->all());
        $summary = $this->summary($rows, $employees);
        $roles = $this->roles();

        $title = 'Laporan Absensi';
        $description = 'Rekap kehadiran karyawan';

        return view('pages.clinic.attendance.report', compact('title', 'description', 'from', 'to', 'userId', 'role', 'employees', 'rows', 'summary', 'roles'));
    }

    public function export(Request $request)
    {
        [$from, $to, $userId, , $employees] = $this->filters($request);
        $rows = $this->service->reportRows($from, $to, $userId, $employees->pluck('id')->all());
        $filename = 'absensi-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows names correctly
            fputcsv($out, ['Tanggal', 'Nama', 'Bagian', 'Shift', 'Jadwal Mulai', 'Jadwal Selesai', 'Masuk', 'Pulang', 'Status',
                'Terlambat (menit)', 'Pulang Cepat (menit)', 'Lama Kerja (menit)', 'Jarak Masuk (m)', 'Jarak Pulang (m)', 'Tidak Absen Pulang', 'Manual', 'Keterangan'], ';');
            foreach ($rows as $row) {
                $a = $row['attendance'];
                fputcsv($out, [
                    $row['date']->format('Y-m-d'),
                    $row['user']?->name,
                    $row['user']?->roleLabel(),
                    $row['shift'],
                    $row['start']?->format('Y-m-d H:i'),
                    $row['end']?->format('Y-m-d H:i'),
                    $a?->check_in_at?->format('Y-m-d H:i'),
                    $a?->check_out_at?->format('Y-m-d H:i'),
                    $row['status'] === 'upcoming' ? 'Belum' : (Attendance::STATUS_LABELS[$row['status']] ?? $row['status']),
                    $a?->late_minutes ?? 0,
                    $a?->early_leave_minutes ?? 0,
                    $a?->workedMinutes(),
                    $a?->check_in_distance,
                    $a?->check_out_distance,
                    $row['missing_checkout'] ? 'Ya' : '',
                    $a?->is_manual ? 'Ya' : '',
                    $a?->notes,
                ], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function edit($language, $userId, $date)
    {
        $employee = User::findOrFail($userId);
        $day = $this->parseDate($date);
        $schedule = StaffSchedule::with('shift')->where('user_id', $employee->id)->whereDate('work_date', $day->toDateString())->first();
        $attendance = Attendance::with('recorder')->where('user_id', $employee->id)->whereDate('work_date', $day->toDateString())->first();

        $title = 'Koreksi Absensi';
        $description = $employee->name . ' - ' . $day->format('d/m/Y');

        return view('pages.clinic.attendance.correct', compact('title', 'description', 'employee', 'day', 'schedule', 'attendance'));
    }

    public function update(Request $request, $language, $userId, $date)
    {
        $employee = User::findOrFail($userId);
        $day = $this->parseDate($date);

        $data = $request->validate([
            'status' => 'required|in:attended,permission,sick,leave,absent,reset',
            'check_in_time' => 'nullable|required_if:status,attended|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'notes' => 'required_unless:status,reset|nullable|string|max:500',
        ], [
            'check_in_time.required_if' => 'Isi jam masuk untuk status Hadir.',
            'notes.required_unless' => 'Isi keterangan/alasan koreksi.',
        ]);

        DB::transaction(function () use ($request, $data, $employee, $day) {
            $schedule = StaffSchedule::with('shift')->where('user_id', $employee->id)->whereDate('work_date', $day->toDateString())->first();
            $attendance = Attendance::lockForUpdate()->where('user_id', $employee->id)->whereDate('work_date', $day->toDateString())->first();

            if ($data['status'] === 'reset') {
                $attendance?->delete();

                return;
            }

            $attendance ??= new Attendance(['user_id' => $employee->id, 'work_date' => $day->toDateString()]);
            $attendance->user_id = $employee->id;
            $attendance->work_date = $day->toDateString();
            [$start, $end] = $schedule?->shift ? $schedule->window() : [null, null];
            if ($schedule) {
                $attendance->staff_schedule_id = $schedule->id;
                $attendance->shift_name = $schedule->shift?->name;
                $attendance->scheduled_start = $start;
                $attendance->scheduled_end = $end;
            }

            if ($data['status'] === 'attended') {
                $checkIn = $this->timeOnShift($day, $data['check_in_time'], $start);
                $checkOut = ! empty($data['check_out_time']) ? $this->timeOnShift($day, $data['check_out_time'], $start) : null;
                if ($checkOut && $checkOut->lte($checkIn)) {
                    $checkOut->addDay();
                }

                $attendance->check_in_at = $checkIn;
                $attendance->check_out_at = $checkOut;
                $tolerance = $schedule?->shift?->late_tolerance_minutes ?? 0;
                $isLate = $start && $checkIn->gt($start->copy()->addMinutes($tolerance));
                $attendance->status = $isLate ? 'late' : 'present';
                $attendance->late_minutes = $isLate ? (int) $start->diffInMinutes($checkIn) : 0;
                $attendance->early_leave_minutes = $end && $checkOut && $checkOut->lt($end) ? (int) $checkOut->diffInMinutes($end) : 0;
            } else {
                $attendance->status = $data['status'];
                $attendance->check_in_at = null;
                $attendance->check_out_at = null;
                $attendance->late_minutes = 0;
                $attendance->early_leave_minutes = 0;
            }

            $attendance->notes = $data['notes'];
            $attendance->is_manual = true;
            $attendance->recorded_by = $request->user()->id;
            $attendance->save();
        });

        return redirect()->route('clinic.attendance-report.index', [app()->getLocale(),
            'from' => $day->copy()->startOfMonth()->toDateString(), 'to' => $day->copy()->endOfMonth()->toDateString(), 'user_id' => $employee->id])
            ->with('success', "Absensi {$employee->name} tanggal {$day->format('d/m/Y')} diperbarui.");
    }

    /** A time typed for a night shift that is earlier than the shift start belongs to the next day */
    private function timeOnShift(Carbon $day, string $time, ?Carbon $shiftStart): Carbon
    {
        $at = $day->copy()->setTimeFromTimeString($time);
        if ($shiftStart && $at->lt($shiftStart->copy()->subHours(6))) {
            $at->addDay();
        }

        return $at;
    }

    private function filters(Request $request): array
    {
        $from = $this->parseDate($request->query('from'), today()->startOfMonth());
        $to = $this->parseDate($request->query('to'), today()->endOfMonth());
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        // Keep reports bounded
        if ($from->diffInDays($to) > 366) {
            $to = $from->copy()->addDays(366);
        }

        $role = $request->query('role') ?: null;
        $userId = $request->integer('user_id') ?: null;
        $employees = User::activeWithPermission('attendance.self');
        // Inactive staff with attendance in the period still belong in the report
        $withRecords = User::whereIn('id', Attendance::whereDate('work_date', '>=', $from->toDateString())
            ->whereDate('work_date', '<=', $to->toDateString())->select('user_id'))->get();
        $employees = $employees->merge($withRecords)->unique('id')->sortBy('name')->values();
        if ($role) {
            $employees = $employees->where('role', $role)->values();
        }

        return [$from, $to, $userId, $role, $employees];
    }

    private function summary($rows, $employees)
    {
        return $employees->map(function (User $employee) use ($rows) {
            $own = $rows->filter(fn ($r) => $r['user']?->id === $employee->id);
            $count = fn (string $status) => $own->where('status', $status)->count();

            return [
                'user' => $employee,
                'scheduled' => $own->whereNotNull('schedule')->count(),
                'present' => $count('present'),
                'late' => $count('late'),
                'late_minutes' => $own->sum(fn ($r) => $r['attendance']?->late_minutes ?? 0),
                'early_minutes' => $own->sum(fn ($r) => $r['attendance']?->early_leave_minutes ?? 0),
                'permission' => $count('permission'),
                'sick' => $count('sick'),
                'leave' => $count('leave'),
                'absent' => $count('absent'),
                'missing_checkout' => $own->where('missing_checkout', true)->count(),
                'worked_minutes' => $own->sum(fn ($r) => $r['attendance']?->workedMinutes() ?? 0),
            ];
        })->filter(fn ($s) => $s['scheduled'] > 0 || $s['present'] + $s['late'] + $s['permission'] + $s['sick'] + $s['leave'] + $s['absent'] > 0)->values();
    }

    private function roles()
    {
        return Role::where('is_system', false)->orderBy('label')->get()->filter(fn (Role $r) => $r->hasPermission('attendance.self'));
    }

    private function parseDate(?string $value, ?Carbon $default = null): Carbon
    {
        if ($value && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::parse($value)->startOfDay();
        }
        abort_if($default === null, 404);

        return $default->copy()->startOfDay();
    }
}

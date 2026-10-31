<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClinicSetting;
use App\Models\StaffSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Check-in / check-out rules.
 *
 * - Check-in is open from (shift start - early minutes) until the shift ends.
 *   Later than start + tolerance counts as "late" (late_minutes is counted from the shift start).
 * - Check-out is possible from check-in until CHECKOUT_GRACE_HOURS after the shift end.
 *   Leaving before the shift end is recorded as early_leave_minutes.
 * - Both need a GPS fix inside the clinic radius with acceptable accuracy.
 *   Times always come from the server clock, never from the browser.
 */
class AttendanceService
{
    public const CHECKOUT_GRACE_HOURS = 6;

    public const SETTING_DEFAULTS = [
        'attendance_latitude' => null,
        'attendance_longitude' => null,
        'attendance_radius' => 100,
        'attendance_max_accuracy' => 100,
        'attendance_early_minutes' => 60,
        'attendance_allow_unscheduled' => 0,
    ];

    public function settings(): array
    {
        $stored = ClinicSetting::whereIn('key', array_keys(self::SETTING_DEFAULTS))->pluck('value', 'key');
        $values = [];
        foreach (self::SETTING_DEFAULTS as $key => $default) {
            $values[$key] = $stored[$key] ?? $default;
        }

        return [
            'latitude' => is_numeric($values['attendance_latitude']) ? (float) $values['attendance_latitude'] : null,
            'longitude' => is_numeric($values['attendance_longitude']) ? (float) $values['attendance_longitude'] : null,
            'radius' => (int) $values['attendance_radius'],
            'max_accuracy' => (int) $values['attendance_max_accuracy'],
            'early_minutes' => (int) $values['attendance_early_minutes'],
            'allow_unscheduled' => (bool) (int) $values['attendance_allow_unscheduled'],
        ];
    }

    public function isConfigured(?array $settings = null): bool
    {
        $settings ??= $this->settings();

        return $settings['latitude'] !== null && $settings['longitude'] !== null && $settings['radius'] > 0;
    }

    /** Great-circle distance in metres (Haversine) */
    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return (int) round($earth * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * What the employee can do right now.
     *
     * @return array{action: ?string, schedule: ?StaffSchedule, attendance: ?Attendance, message: ?string}
     *         action: check_in | check_out | null
     */
    public function context(User $user, ?Carbon $now = null): array
    {
        $now ??= now();
        $settings = $this->settings();

        // 1. An open attendance (checked in, not out) that can still be closed
        $open = Attendance::with('schedule.shift')
            ->where('user_id', $user->id)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->whereDate('work_date', '>=', $now->copy()->subDay()->toDateString())
            ->orderByDesc('check_in_at')
            ->get()
            ->first(fn (Attendance $a) => $now->lte($this->checkoutDeadline($a)));

        if ($open) {
            return ['action' => 'check_out', 'schedule' => $open->schedule, 'attendance' => $open, 'message' => null];
        }

        // 2. A schedule (yesterday's night shift or today's) whose check-in window is open
        $schedules = StaffSchedule::with(['shift', 'attendance'])
            ->where('user_id', $user->id)
            ->whereDate('work_date', '>=', $now->copy()->subDay()->toDateString())
            ->whereDate('work_date', '<=', $now->toDateString())
            ->orderBy('work_date')
            ->get();

        foreach ($schedules as $schedule) {
            if ($schedule->attendance || ! $schedule->shift) {
                continue;
            }
            [$start, $end] = $schedule->window();
            if ($now->betweenIncluded($start->copy()->subMinutes($settings['early_minutes']), $end)) {
                return ['action' => 'check_in', 'schedule' => $schedule, 'attendance' => null, 'message' => null];
            }
        }

        $today = $schedules->first(fn ($s) => $s->work_date->isSameDay($now));
        $todayAttendance = $today?->attendance
            ?? Attendance::where('user_id', $user->id)->whereDate('work_date', $now->toDateString())->first();

        if ($todayAttendance) {
            return ['action' => null, 'schedule' => $today, 'attendance' => $todayAttendance, 'message' => $todayAttendance->check_out_at
                ? 'Absensi hari ini sudah lengkap.'
                : 'Absensi hari ini sudah tercatat.'];
        }

        if ($today && $today->shift) {
            [$start] = $today->window();
            $opensAt = $start->copy()->subMinutes($settings['early_minutes']);
            if ($now->lt($opensAt)) {
                return ['action' => null, 'schedule' => $today, 'attendance' => null,
                    'message' => 'Absen masuk dibuka mulai pukul ' . $opensAt->format('H:i') . '.'];
            }

            return ['action' => null, 'schedule' => $today, 'attendance' => null, 'message' => 'Jadwal shift hari ini sudah lewat.'];
        }

        // 3. No schedule today
        if ($settings['allow_unscheduled']) {
            return ['action' => 'check_in', 'schedule' => null, 'attendance' => null, 'message' => 'Anda tidak punya jadwal hari ini. Absen tetap dicatat tanpa jadwal.'];
        }

        return ['action' => null, 'schedule' => null, 'attendance' => null, 'message' => 'Anda tidak punya jadwal hari ini.'];
    }

    public function checkoutDeadline(Attendance $attendance): Carbon
    {
        if ($attendance->scheduled_end) {
            return $attendance->scheduled_end->copy()->addHours(self::CHECKOUT_GRACE_HOURS);
        }

        // Without a schedule: until the end of the work date
        return $attendance->work_date->copy()->endOfDay();
    }

    /**
     * @param array{latitude: float, longitude: float, accuracy: float} $position
     */
    public function checkIn(User $user, array $position, Request $request): Attendance
    {
        return DB::transaction(function () use ($user, $position, $request) {
            // Serialise this user's attendance actions so a double tap cannot create two records
            User::whereKey($user->id)->lockForUpdate()->first();

            $now = now();
            $context = $this->context($user, $now);
            if ($context['action'] !== 'check_in') {
                throw ValidationException::withMessages(['attendance' => $context['message'] ?? 'Absen masuk tidak tersedia saat ini.']);
            }

            [$distance, $accuracy] = $this->assertInsideRadius($position);
            $schedule = $context['schedule'];

            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->check_in_at = $now;
            $attendance->check_in_lat = $position['latitude'];
            $attendance->check_in_lng = $position['longitude'];
            $attendance->check_in_accuracy = $accuracy;
            $attendance->check_in_distance = $distance;
            $attendance->ip_address = $request->ip();
            $attendance->user_agent = substr((string) $request->userAgent(), 0, 255);

            if ($schedule) {
                [$start, $end] = $schedule->window();
                $attendance->staff_schedule_id = $schedule->id;
                $attendance->work_date = $schedule->work_date;
                $attendance->shift_name = $schedule->shift->name;
                $attendance->scheduled_start = $start;
                $attendance->scheduled_end = $end;

                $isLate = $now->gt($start->copy()->addMinutes($schedule->shift->late_tolerance_minutes));
                $attendance->status = $isLate ? 'late' : 'present';
                $attendance->late_minutes = $isLate ? (int) $start->diffInMinutes($now) : 0;
            } else {
                $attendance->work_date = $now->toDateString();
                $attendance->status = 'present';
            }

            $attendance->save();

            return $attendance;
        });
    }

    public function checkOut(User $user, array $position, Request $request): Attendance
    {
        return DB::transaction(function () use ($user, $position, $request) {
            User::whereKey($user->id)->lockForUpdate()->first();

            $now = now();
            $context = $this->context($user, $now);
            if ($context['action'] !== 'check_out') {
                throw ValidationException::withMessages(['attendance' => $context['message'] ?? 'Absen pulang tidak tersedia saat ini.']);
            }

            [$distance, $accuracy] = $this->assertInsideRadius($position);

            $attendance = Attendance::lockForUpdate()->findOrFail($context['attendance']->id);
            $attendance->check_out_at = $now;
            $attendance->check_out_lat = $position['latitude'];
            $attendance->check_out_lng = $position['longitude'];
            $attendance->check_out_accuracy = $accuracy;
            $attendance->check_out_distance = $distance;
            $attendance->early_leave_minutes = $attendance->scheduled_end && $now->lt($attendance->scheduled_end)
                ? (int) $now->diffInMinutes($attendance->scheduled_end)
                : 0;
            $attendance->save();

            return $attendance;
        });
    }

    /** @return array{0: int, 1: int} [distance, accuracy] in metres */
    private function assertInsideRadius(array $position): array
    {
        $settings = $this->settings();
        if (! $this->isConfigured($settings)) {
            throw ValidationException::withMessages(['attendance' => 'Lokasi klinik untuk absensi belum diatur. Hubungi admin.']);
        }

        $accuracy = (int) round($position['accuracy']);
        if ($settings['max_accuracy'] > 0 && $accuracy > $settings['max_accuracy']) {
            throw ValidationException::withMessages([
                'attendance' => "Sinyal GPS kurang akurat (±{$accuracy} m, maksimal ±{$settings['max_accuracy']} m). Aktifkan GPS/lokasi akurat lalu coba lagi.",
            ]);
        }

        $distance = self::distanceMeters($settings['latitude'], $settings['longitude'], $position['latitude'], $position['longitude']);
        if ($distance > $settings['radius']) {
            throw ValidationException::withMessages([
                'attendance' => "Anda berada {$distance} m dari klinik. Absen hanya bisa dalam radius {$settings['radius']} m.",
            ]);
        }

        return [$distance, $accuracy];
    }

    /**
     * Status of each scheduled day in a date range, plus attendance without a schedule.
     * Scheduled days with no record become "absent" once the shift is over, "upcoming" before that.
     *
     * @return \Illuminate\Support\Collection<int, array> rows sorted by date then name
     */
    public function reportRows(Carbon $from, Carbon $to, ?int $userId = null, ?array $userIds = null)
    {
        $now = now();
        $schedules = StaffSchedule::with(['user', 'shift', 'attendance.recorder'])
            ->whereDate('work_date', '>=', $from->toDateString())->whereDate('work_date', '<=', $to->toDateString())
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($userIds !== null, fn ($q) => $q->whereIn('user_id', $userIds))
            ->get();

        $attendances = Attendance::with(['user', 'recorder'])
            ->whereDate('work_date', '>=', $from->toDateString())->whereDate('work_date', '<=', $to->toDateString())
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($userIds !== null, fn ($q) => $q->whereIn('user_id', $userIds))
            ->get()
            ->keyBy(fn ($a) => $a->user_id . '|' . $a->work_date->toDateString());

        $rows = collect();
        foreach ($schedules as $schedule) {
            $key = $schedule->user_id . '|' . $schedule->work_date->toDateString();
            $attendance = $schedule->attendance ?? $attendances->get($key);
            $attendances->forget($key);
            [$start, $end] = $schedule->shift ? $schedule->window() : [null, null];

            $status = $attendance?->status ?? ($end && $now->gt($end) ? 'absent' : 'upcoming');
            $rows->push([
                'date' => $schedule->work_date->copy(),
                'user' => $schedule->user,
                'shift' => $schedule->shift?->name ?? $attendance?->shift_name,
                'start' => $start,
                'end' => $end,
                'schedule' => $schedule,
                'attendance' => $attendance,
                'status' => $status,
                'missing_checkout' => $attendance && $attendance->check_in_at && ! $attendance->check_out_at && $end && $now->gt($end),
            ]);
        }

        // Check-ins without a schedule (allowed by setting, or schedule removed afterwards)
        foreach ($attendances as $attendance) {
            $rows->push([
                'date' => $attendance->work_date->copy(),
                'user' => $attendance->user,
                'shift' => $attendance->shift_name ?? 'Tanpa jadwal',
                'start' => $attendance->scheduled_start,
                'end' => $attendance->scheduled_end,
                'schedule' => null,
                'attendance' => $attendance,
                'status' => $attendance->status,
                'missing_checkout' => $attendance->check_in_at && ! $attendance->check_out_at && $now->gt($this->checkoutDeadline($attendance)),
            ]);
        }

        return $rows->sortBy(fn ($r) => [$r['date']->toDateString(), $r['user']?->name])->values();
    }
}

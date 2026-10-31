<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\StaffSchedule;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Weekly roster: one shift per employee per day.
 * Employees are active users whose role has "attendance.self".
 */
class StaffScheduleController extends Controller
{
    public function index(Request $request)
    {
        $weekStart = $this->weekStart($request->query('week'));
        $dates = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));
        $role = $request->query('role');
        $employees = $this->employees($role);

        $schedules = StaffSchedule::with('attendance')
            ->whereIn('user_id', $employees->pluck('id'))
            ->whereDate('work_date', '>=', $weekStart->toDateString())
            ->whereDate('work_date', '<=', $weekStart->copy()->addDays(6)->toDateString())
            ->get()
            ->keyBy(fn ($s) => $s->user_id . '|' . $s->work_date->toDateString());

        $shifts = WorkShift::orderBy('start_time')->get();
        $roles = Role::where('is_system', false)->orderBy('label')->get()
            ->filter(fn (Role $r) => $r->hasPermission('attendance.self'));

        $title = 'Jadwal Karyawan';
        $description = 'Jadwal shift mingguan';

        return view('pages.clinic.attendance.schedules', compact('title', 'description', 'weekStart', 'dates', 'employees', 'schedules', 'shifts', 'roles', 'role'));
    }

    public function update(Request $request)
    {
        $weekStart = $this->weekStart($request->input('week'));
        $weekEnd = $weekStart->copy()->addDays(6);
        $employeeIds = $this->employees(null)->pluck('id')->all();
        $shiftIds = WorkShift::pluck('id')->all();

        $request->validate([
            'schedule' => 'nullable|array',
            'schedule.*' => 'array',
            'schedule.*.*' => 'nullable|integer',
        ]);

        $created = $updated = $removed = 0;
        $kept = [];

        DB::transaction(function () use ($request, $weekStart, $weekEnd, $employeeIds, $shiftIds, &$created, &$updated, &$removed, &$kept) {
            foreach ((array) $request->input('schedule', []) as $userId => $days) {
                if (! in_array((int) $userId, $employeeIds, true)) {
                    continue;
                }
                foreach ((array) $days as $date => $shiftId) {
                    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        continue;
                    }
                    $day = Carbon::parse($date);
                    if ($day->lt($weekStart) || $day->gt($weekEnd)) {
                        continue;
                    }
                    $shiftId = $shiftId ? (int) $shiftId : null;
                    if ($shiftId && ! in_array($shiftId, $shiftIds, true)) {
                        continue;
                    }

                    $schedule = StaffSchedule::with('attendance')->where('user_id', $userId)->whereDate('work_date', $date)->first();

                    if (! $shiftId) {
                        if ($schedule && $schedule->attendance) {
                            // Already attended: keep the schedule so the report stays consistent
                            $kept[] = $day->format('d/m');
                        } elseif ($schedule) {
                            $schedule->delete();
                            $removed++;
                        }
                        continue;
                    }

                    if (! $schedule) {
                        $schedule = new StaffSchedule(['user_id' => (int) $userId, 'work_date' => $date, 'work_shift_id' => $shiftId]);
                        $schedule->created_by = $request->user()->id;
                        $schedule->save();
                        $created++;
                    } elseif ((int) $schedule->work_shift_id !== $shiftId) {
                        $schedule->work_shift_id = $shiftId;
                        $schedule->save();
                        $updated++;
                    }
                }
            }
        });

        $message = "Jadwal disimpan: {$created} baru, {$updated} diubah, {$removed} dihapus.";
        if ($kept) {
            $message .= ' Jadwal yang sudah ada absensinya tidak dihapus (' . implode(', ', array_unique($kept)) . ').';
        }

        return redirect()->route('clinic.schedules.index', [app()->getLocale(), 'week' => $weekStart->toDateString(), 'role' => $request->input('role')])
            ->with('success', $message);
    }

    /** Copy last week's shifts into empty days of this week */
    public function copyPrevious(Request $request)
    {
        $weekStart = $this->weekStart($request->input('week'));
        $previousStart = $weekStart->copy()->subWeek();
        $employees = $this->employees($request->input('role'));

        $source = StaffSchedule::whereIn('user_id', $employees->pluck('id'))
            ->whereDate('work_date', '>=', $previousStart->toDateString())
            ->whereDate('work_date', '<=', $previousStart->copy()->addDays(6)->toDateString())
            ->get();

        $copied = 0;
        DB::transaction(function () use ($source, $request, &$copied) {
            foreach ($source as $item) {
                $target = $item->work_date->copy()->addWeek()->toDateString();
                $exists = StaffSchedule::where('user_id', $item->user_id)->whereDate('work_date', $target)->exists();
                if ($exists) {
                    continue;
                }
                $schedule = new StaffSchedule(['user_id' => $item->user_id, 'work_date' => $target, 'work_shift_id' => $item->work_shift_id, 'notes' => $item->notes]);
                $schedule->created_by = $request->user()->id;
                $schedule->save();
                $copied++;
            }
        });

        return redirect()->route('clinic.schedules.index', [app()->getLocale(), 'week' => $weekStart->toDateString(), 'role' => $request->input('role')])
            ->with('success', "{$copied} jadwal disalin dari minggu lalu (hari yang sudah terisi tidak diubah).");
    }

    private function weekStart(?string $value): Carbon
    {
        $date = $value && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? Carbon::parse($value) : today();

        return $date->startOfWeek(Carbon::MONDAY);
    }

    private function employees(?string $role)
    {
        return User::activeWithPermission('attendance.self')
            ->when($role, fn ($users) => $users->where('role', $role))
            ->values();
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use Auditable;

    protected $fillable = ['name', 'start_time', 'end_time', 'late_tolerance_minutes', 'color', 'status'];

    protected $casts = [
        'late_tolerance_minutes' => 'integer',
    ];

    public function schedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Night shift: ends on the next day */
    public function crossesMidnight(): bool
    {
        return substr($this->end_time, 0, 5) <= substr($this->start_time, 0, 5);
    }

    /** @return array{0: Carbon, 1: Carbon} start and end of this shift for a work date */
    public function windowFor(CarbonInterface $date): array
    {
        $day = Carbon::parse($date->format('Y-m-d'));
        $start = $day->copy()->setTimeFromTimeString($this->start_time);
        $end = $day->copy()->setTimeFromTimeString($this->end_time);
        if ($this->crossesMidnight()) {
            $end->addDay();
        }

        return [$start, $end];
    }

    public function timeLabel(): string
    {
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5) . ($this->crossesMidnight() ? ' (+1)' : '');
    }
}

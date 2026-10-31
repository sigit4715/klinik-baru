<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/** One shift for one employee on one date */
class StaffSchedule extends Model
{
    use Auditable;

    protected $fillable = ['user_id', 'work_date', 'work_shift_id', 'notes'];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id');
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function window(): array
    {
        return $this->shift->windowFor($this->work_date);
    }

    public function startsAt(): Carbon
    {
        return $this->window()[0];
    }

    public function endsAt(): Carbon
    {
        return $this->window()[1];
    }
}

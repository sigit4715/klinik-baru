<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * role and is_active are intentionally not mass assignable;
     * set them explicitly after an authorization check.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * users.role stores the role name (e.g. "admin", "cashier").
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->roleModel?->is_system;
    }

    public function hasPermission(string $permission): bool
    {
        return (bool) $this->roleModel?->hasPermission($permission);
    }

    public function permissions(): array
    {
        return $this->roleModel?->effectivePermissions() ?? [];
    }

    public function roleLabel(): string
    {
        return $this->roleModel?->label ?? ucfirst((string) $this->role);
    }

    /** Doctor profile linked to this login (doctor_id is set explicitly, not mass assigned) */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    /**
     * Active users whose (non-administrator) role has the permission,
     * e.g. activeWithPermission('nursing.manage') lists the nurses for the registration form.
     */
    public static function activeWithPermission(string $permission)
    {
        $roleNames = Role::where('is_system', false)->get()
            ->filter(fn (Role $role) => $role->hasPermission($permission))
            ->pluck('name');

        return static::whereIn('role', $roleNames)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /** Has this user's name been recorded on clinic work (needed for payroll/attendance history)? */
    public function hasClinicActivity(): bool
    {
        return Appointment::withTrashed()->where('registered_by', $this->id)->orWhere('nurse_id', $this->id)->exists()
            || NursingAssessment::where('nurse_id', $this->id)->exists()
            || Payment::where('cashier_id', $this->id)->exists()
            || MedicalRecord::where('edited_by', $this->id)->exists()
            || Attendance::where('user_id', $this->id)->exists()
            || Payslip::where('user_id', $this->id)->exists();
    }

    public function salaryProfile()
    {
        return $this->hasOne(SalaryProfile::class);
    }

    public function schedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

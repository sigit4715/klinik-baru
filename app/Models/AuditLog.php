<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /** Labels for the audit log page */
    public const EVENTS = [
        'created' => 'Dibuat',
        'updated' => 'Diubah',
        'deleted' => 'Diarsipkan / dihapus',
        'restored' => 'Dipulihkan',
        'login' => 'Login',
        'logout' => 'Logout',
        'login_failed' => 'Login gagal',
    ];

    public const MODELS = [
        Patient::class => 'Pasien',
        Doctor::class => 'Dokter',
        Medicine::class => 'Obat',
        Service::class => 'Layanan',
        Appointment::class => 'Antrian',
        MedicalRecord::class => 'Rekam Medis',
        Payment::class => 'Pembayaran',
        PaymentRefund::class => 'Refund',
        User::class => 'User',
        Role::class => 'Role',
        ClinicSetting::class => 'Pengaturan Klinik',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param User|null $actor who did it; defaults to the logged-in user
     *                         (login events pass the user because the guard is not set yet)
     */
    public static function record(string $event, ?Model $model = null, array $old = [], array $new = [], ?string $description = null, ?User $actor = null): void
    {
        $user = $actor ?? auth()->user();
        $request = app()->runningInConsole() ? null : request();

        static::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'event' => $event,
            'auditable_type' => $model ? $model::class : null,
            'auditable_id' => $model?->getKey(),
            'description' => $description ? Str::limit($description, 250) : null,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? Str::limit((string) $request->userAgent(), 250) : null,
            'url' => $request ? Str::limit($request->fullUrl(), 250) : null,
        ]);
    }

    public function modelLabel(): string
    {
        return self::MODELS[$this->auditable_type] ?? ($this->auditable_type ? class_basename($this->auditable_type) : '-');
    }

    public function eventLabel(): string
    {
        return self::EVENTS[$this->event] ?? $this->event;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * New roles: Perawat and "Pendaftaran & Kasir" (combined front desk).
 * Existing roles may have been customised, so permissions are only added, never removed.
 */
return new class extends Migration
{
    private array $newRoles = [
        'nurse' => ['Perawat', 'Pemeriksaan awal: anamnesa, tanda vital, triage, poli & dokter tujuan', [
            'patients.view', 'appointments.view', 'appointments.call', 'queue.display',
            'nursing.manage', 'medical_records.view', 'doctors.view',
        ]],
        'registration_cashier' => ['Pendaftaran & Kasir', 'Pendaftaran pasien, antrian dan pembayaran', [
            'patients.view', 'patients.manage', 'appointments.view', 'appointments.manage', 'appointments.call',
            'queue.display', 'doctors.view', 'payments.view', 'payments.manage', 'reports.finance',
        ]],
    ];

    /** role => permissions to add to an existing role */
    private array $grants = [
        'doctor' => ['medical_records.edit'],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->newRoles as $name => [$label, $description, $permissions]) {
            if (DB::table('roles')->where('name', $name)->exists()) {
                continue;
            }
            DB::table('roles')->insert([
                'name' => $name,
                'label' => $label,
                'description' => $description,
                'permissions' => json_encode($permissions),
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($this->grants as $name => $add) {
            $role = DB::table('roles')->where('name', $name)->first();
            if (! $role) {
                continue;
            }
            $current = json_decode($role->permissions ?? '[]', true) ?: [];
            $merged = array_values(array_unique(array_merge($current, $add)));
            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode($merged),
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Roles may already be assigned to users; leave them in place.
    }
};

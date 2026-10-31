<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roles with a configurable list of permissions (see config/permissions.php).
 * users.role keeps storing the role name, so existing users keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('label', 100);
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            // System role (admin): always has every permission and cannot be deleted
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['admin', 'Administrator', 'Akses penuh ke semua menu', [], true],
            ['doctor', 'Dokter', 'Pemeriksaan pasien dan rekam medis', [
                'patients.view', 'appointments.view', 'appointments.call', 'queue.display',
                'medical_records.view', 'medical_records.manage', 'medicines.view', 'services.view', 'doctors.view',
            ], false],
            ['cashier', 'Kasir', 'Pembayaran dan laporan keuangan', [
                'patients.view', 'appointments.view', 'payments.view', 'payments.manage', 'reports.finance',
            ], false],
            ['pharmacist', 'Apoteker', 'Pengelolaan obat dan stok', [
                'patients.view', 'appointments.view', 'medical_records.view', 'medicines.view', 'medicines.manage',
            ], false],
            ['staff', 'Staff Pendaftaran', 'Pendaftaran pasien dan antrian', [
                'patients.view', 'patients.manage', 'appointments.view', 'appointments.manage', 'appointments.call',
                'queue.display', 'doctors.view',
            ], false],
        ];

        foreach ($defaults as [$name, $label, $description, $permissions, $isSystem]) {
            DB::table('roles')->insert([
                'name' => $name,
                'label' => $label,
                'description' => $description,
                'permissions' => json_encode($permissions),
                'is_system' => $isSystem,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

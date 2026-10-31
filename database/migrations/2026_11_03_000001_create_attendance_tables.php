<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Staff attendance: work shifts, one schedule per employee per day, and the attendance record
 * (check-in/out time and GPS position, or leave/sick marked by an admin).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('work_shifts')) {
            Schema::create('work_shifts', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->time('start_time');
                // Earlier than start_time means the shift ends the next day (night shift)
                $table->time('end_time');
                $table->unsignedSmallInteger('late_tolerance_minutes')->default(15);
                $table->string('color', 20)->default('#3b82f6');
                $table->string('status', 20)->default('active');
                $table->timestamps();
            });

            $now = now();
            DB::table('work_shifts')->insert([
                ['name' => 'Pagi', 'start_time' => '07:00:00', 'end_time' => '14:00:00', 'late_tolerance_minutes' => 15, 'color' => '#10b981', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Siang', 'start_time' => '14:00:00', 'end_time' => '21:00:00', 'late_tolerance_minutes' => 15, 'color' => '#f59e0b', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Malam', 'start_time' => '21:00:00', 'end_time' => '07:00:00', 'late_tolerance_minutes' => 15, 'color' => '#6366f1', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (! Schema::hasTable('staff_schedules')) {
            Schema::create('staff_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('work_date');
                $table->foreignId('work_shift_id')->constrained('work_shifts')->restrictOnDelete();
                $table->string('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'work_date']);
                $table->index('work_date');
            });
        }

        if (! Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('staff_schedule_id')->nullable()->constrained('staff_schedules')->nullOnDelete();
                // Date the shift starts (a night shift checked out next morning keeps its start date)
                $table->date('work_date');
                // Snapshot of the shift, so later schedule/shift changes do not rewrite history
                $table->string('shift_name', 50)->nullable();
                $table->dateTime('scheduled_start')->nullable();
                $table->dateTime('scheduled_end')->nullable();
                // present, late, permission, sick, leave, absent
                $table->string('status', 20);
                $table->dateTime('check_in_at')->nullable();
                $table->decimal('check_in_lat', 10, 7)->nullable();
                $table->decimal('check_in_lng', 10, 7)->nullable();
                $table->unsignedInteger('check_in_accuracy')->nullable();
                $table->unsignedInteger('check_in_distance')->nullable();
                $table->dateTime('check_out_at')->nullable();
                $table->decimal('check_out_lat', 10, 7)->nullable();
                $table->decimal('check_out_lng', 10, 7)->nullable();
                $table->unsignedInteger('check_out_accuracy')->nullable();
                $table->unsignedInteger('check_out_distance')->nullable();
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_leave_minutes')->default(0);
                $table->string('notes', 500)->nullable();
                // Entered or corrected by an admin instead of the employee's own check-in
                $table->boolean('is_manual')->default(false);
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'work_date']);
                $table->index(['work_date', 'status']);
            });
        }

        // Permissions: every clinic role except admin (who has all) may check in
        $grants = [
            'doctor' => ['attendance.self'],
            'nurse' => ['attendance.self'],
            'pharmacist' => ['attendance.self'],
            'cashier' => ['attendance.self'],
            'staff' => ['attendance.self'],
            'registration_cashier' => ['attendance.self'],
        ];
        foreach ($grants as $name => $add) {
            $role = DB::table('roles')->where('name', $name)->first();
            if (! $role) {
                continue;
            }
            $current = json_decode($role->permissions ?? '[]', true) ?: [];
            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_values(array_unique(array_merge($current, $add)))),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('staff_schedules');
        Schema::dropIfExists('work_shifts');
    }
};

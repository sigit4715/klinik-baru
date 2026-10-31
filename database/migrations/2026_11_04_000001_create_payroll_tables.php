<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Payroll: salary settings per employee, doctor fee per service, monthly periods and payslips.
 * Payslips keep a snapshot of every amount so later rate changes do not rewrite history.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('salary_profiles')) {
            Schema::create('salary_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->decimal('base_salary', 14, 2)->default(0);
                // Paid per attended shift (uang duduk)
                $table->decimal('sitting_fee', 14, 2)->default(0);
                $table->decimal('fixed_allowance', 14, 2)->default(0);
                $table->string('allowance_note')->nullable();
                // Base salary / scheduled shifts x absent shifts
                $table->boolean('deduct_absence')->default(true);
                $table->decimal('late_fee_per_incident', 14, 2)->default(0);
                $table->decimal('late_fee_per_minute', 14, 2)->default(0);
                // Doctor fee per service (needs the login linked to a doctor)
                $table->boolean('earns_service_fee')->default(false);
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('service_fees')) {
            Schema::create('service_fees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                // Null = default for every doctor; otherwise an override for one doctor
                $table->foreignId('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
                // fixed (Rp per tindakan) or percent (of the billed price)
                $table->string('fee_type', 10)->default('fixed');
                $table->decimal('fee_value', 14, 2)->default(0);
                $table->timestamps();

                $table->unique(['service_id', 'doctor_id']);
            });
        }

        if (! Schema::hasTable('payroll_periods')) {
            Schema::create('payroll_periods', function (Blueprint $table) {
                $table->id();
                // YYYY-MM
                $table->string('period', 7)->unique();
                // draft -> locked -> paid
                $table->string('status', 20)->default('draft');
                $table->timestamp('calculated_at')->nullable();
                $table->timestamp('locked_at')->nullable();
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payslips')) {
            Schema::create('payslips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                $table->string('employee_name');
                $table->string('role_label')->nullable();
                $table->unsignedSmallInteger('scheduled_shifts')->default(0);
                $table->unsignedSmallInteger('attended_shifts')->default(0);
                $table->unsignedSmallInteger('absent_shifts')->default(0);
                $table->unsignedSmallInteger('leave_shifts')->default(0);
                $table->unsignedSmallInteger('late_count')->default(0);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->decimal('base_salary', 14, 2)->default(0);
                $table->decimal('sitting_fee_rate', 14, 2)->default(0);
                $table->decimal('sitting_fee_total', 14, 2)->default(0);
                $table->unsignedInteger('service_count')->default(0);
                $table->decimal('service_fee_total', 14, 2)->default(0);
                $table->decimal('allowance_total', 14, 2)->default(0);
                $table->decimal('absence_deduction', 14, 2)->default(0);
                $table->decimal('late_deduction', 14, 2)->default(0);
                $table->decimal('manual_additions', 14, 2)->default(0);
                $table->decimal('manual_deductions', 14, 2)->default(0);
                $table->decimal('net_total', 14, 2)->default(0);
                // Service fee lines: date, invoice, patient, service, qty, price, fee
                $table->json('service_details')->nullable();
                // Manual lines: [{type: add|deduct, label, amount}]
                $table->json('adjustments')->nullable();
                $table->timestamps();

                $table->unique(['payroll_period_id', 'user_id']);
            });
        }

        // Every employee role may see its own payslips
        foreach (['doctor', 'nurse', 'pharmacist', 'cashier', 'staff', 'registration_cashier'] as $name) {
            $role = DB::table('roles')->where('name', $name)->first();
            if (! $role) {
                continue;
            }
            $current = json_decode($role->permissions ?? '[]', true) ?: [];
            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_values(array_unique(array_merge($current, ['payroll.self'])))),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('service_fees');
        Schema::dropIfExists('salary_profiles');
    }
};

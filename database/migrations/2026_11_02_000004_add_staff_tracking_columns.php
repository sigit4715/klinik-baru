<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who did what, for later payroll/attendance reports:
 * - users.doctor_id links a login to a doctor profile ("Pasien Menunggu" shows only that doctor's patients)
 * - payments.cashier_id is the cashier who received the payment
 * - medical_records.edited_by / edited_at for corrected records
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'doctor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('doctor_id')->nullable()->unique()->constrained('doctors')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('payments', 'cashier_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('cashier_id')->nullable()->after('patient_id')->constrained('users')->nullOnDelete();
            });
        }

        Schema::table('medical_records', function (Blueprint $table) {
            if (! Schema::hasColumn('medical_records', 'edited_by')) {
                $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('medical_records', 'edited_at')) {
                $table->timestamp('edited_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            if (Schema::hasColumn('medical_records', 'edited_by')) {
                $table->dropConstrainedForeignId('edited_by');
            }
            if (Schema::hasColumn('medical_records', 'edited_at')) {
                $table->dropColumn('edited_at');
            }
        });
        if (Schema::hasColumn('payments', 'cashier_id')) {
            Schema::table('payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('cashier_id'));
        }
        if (Schema::hasColumn('users', 'doctor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
                $table->dropUnique(['doctor_id']);
                $table->dropColumn('doctor_id');
            });
        }
    }
};

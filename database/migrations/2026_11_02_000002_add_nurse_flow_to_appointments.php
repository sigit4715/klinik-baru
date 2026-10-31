<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registration no longer picks the doctor or the complaint; the nurse does that.
 * - doctor_id and complaint become nullable
 * - status becomes a string so new steps (screening, ready) need no ALTER ENUM
 * - who registered the patient, which nurse was chosen, and when the patient was last called
 *
 * Safe to re-run after a partial failure: each step checks the current schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
            $table->text('complaint')->nullable()->change();
            $table->string('status', 20)->default('waiting')->change();
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'poli_id')) {
                $table->foreignId('poli_id')->nullable()->after('doctor_id')->constrained('polis')->nullOnDelete();
            }
            if (! Schema::hasColumn('appointments', 'nurse_id')) {
                $table->foreignId('nurse_id')->nullable()->after('poli_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('appointments', 'registered_by')) {
                $table->foreignId('registered_by')->nullable()->after('nurse_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('appointments', 'called_at')) {
                $table->timestamp('called_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['registered_by', 'nurse_id', 'poli_id'] as $column) {
                if (Schema::hasColumn('appointments', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            if (Schema::hasColumn('appointments', 'called_at')) {
                $table->dropColumn('called_at');
            }
        });
        // doctor_id/complaint/status are left as they are: rows created by the new flow
        // may have no doctor or complaint, so reverting them would fail.
    }
};

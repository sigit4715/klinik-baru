<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Surat medis: keterangan sehat, keterangan sakit, rujukan.
 * Letters are never deleted; a wrong letter is voided with a reason.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medical_letters')) {
            Schema::create('medical_letters', function (Blueprint $table) {
                $table->id();
                $table->string('letter_number', 50)->unique();
                // healthy, sick, referral
                $table->string('type', 20);
                $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
                $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();
                $table->date('issued_date');
                // Keterangan sehat
                $table->string('purpose')->nullable();
                // Keterangan sakit
                $table->unsignedSmallInteger('rest_days')->nullable();
                $table->date('rest_from')->nullable();
                $table->date('rest_until')->nullable();
                $table->boolean('show_diagnosis')->default(false);
                // Rujukan
                $table->string('referred_to')->nullable();
                $table->string('referred_department')->nullable();
                $table->text('referral_reason')->nullable();
                $table->text('findings')->nullable();
                $table->text('therapy')->nullable();
                // Shared
                $table->text('diagnosis')->nullable();
                $table->text('notes')->nullable();
                // Snapshot: blood_pressure, body_temperature, pulse_rate, respiratory_rate, height, weight
                $table->json('vitals')->nullable();
                $table->string('status', 20)->default('valid');
                $table->string('void_reason')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['type', 'issued_date']);
            });
        }

        foreach (['doctor' => ['letters.view', 'letters.manage']] as $name => $add) {
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
        Schema::dropIfExists('medical_letters');
    }
};

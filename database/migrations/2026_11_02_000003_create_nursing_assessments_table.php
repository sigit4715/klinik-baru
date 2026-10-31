<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nurse step (one per visit): complaint, anamnesis, vital signs, triage and the poli/doctor the patient is sent to.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nursing_assessments')) {
            return;
        }

        Schema::create('nursing_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            // The nurse who actually filled in the form (logged-in user)
            $table->foreignId('nurse_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('chief_complaint')->nullable();
            $table->text('anamnesa')->nullable();
            $table->string('allergy_note')->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->decimal('body_temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('pulse_rate')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->unsignedTinyInteger('oxygen_saturation')->nullable();
            $table->decimal('height', 5, 1)->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            // merah, kuning, hijau
            $table->string('triage_level', 10)->nullable();
            $table->foreignId('poli_id')->nullable()->constrained('polis')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursing_assessments');
    }
};

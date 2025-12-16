<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();
            $table->text('complaint');
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Vital Signs - all nullable/optional
            $table->string('blood_pressure', 20)->nullable()->after('diagnosis')->comment('Tekanan darah, contoh: 120/80');
            $table->decimal('body_temperature', 4, 1)->nullable()->after('blood_pressure')->comment('Suhu badan dalam Celsius');
            $table->integer('pulse_rate')->nullable()->after('body_temperature')->comment('Denyut nadi per menit');
            $table->integer('respiratory_rate')->nullable()->after('pulse_rate')->comment('Pernapasan per menit');
            $table->decimal('height', 5, 1)->nullable()->after('respiratory_rate')->comment('Tinggi badan dalam cm');
            $table->decimal('weight', 5, 1)->nullable()->after('height')->comment('Berat badan dalam kg');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn([
                'blood_pressure',
                'body_temperature',
                'pulse_rate',
                'respiratory_rate',
                'height',
                'weight'
            ]);
        });
    }
};

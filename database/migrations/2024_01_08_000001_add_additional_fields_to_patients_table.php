<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('whatsapp', 20)->nullable()->after('phone');
            $table->string('spouse_name')->nullable()->after('address')->comment('Nama suami untuk pasien hamil');
            $table->string('parent_name')->nullable()->after('spouse_name')->comment('Nama orang tua untuk pasien anak-anak');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['whatsapp', 'spouse_name', 'parent_name']);
        });
    }
};

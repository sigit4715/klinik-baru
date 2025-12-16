<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('clinic_settings')->insert([
            ['key' => 'clinic_name', 'value' => 'Klinik Sehat Sentosa', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'clinic_address', 'value' => 'Jl. Kesehatan No. 123, Jakarta', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'clinic_phone', 'value' => '021-1234567', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'clinic_whatsapp', 'value' => '081234567890', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'clinic_email', 'value' => 'info@kliniksehat.com', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'receipt_footer', 'value' => 'Terima kasih atas kunjungan Anda. Semoga lekas sembuh!', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_settings');
    }
};

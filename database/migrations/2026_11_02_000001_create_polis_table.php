<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Poli (clinic units). Type "umum" is an examination room, "penunjang" is a support unit (lab, radiologi).
 * Doctors can be assigned to a poli so the nurse can pick poli then doctor.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('polis')) {
            Schema::create('polis', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name', 100);
                $table->string('type', 20)->default('umum');
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->softDeletes();
            });

            $now = now();
            DB::table('polis')->insert([
                ['code' => 'UMUM', 'name' => 'Poli Umum', 'type' => 'umum', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'KIA', 'name' => 'Poli KIA / Kebidanan', 'type' => 'umum', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (! Schema::hasColumn('doctors', 'poli_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->foreignId('poli_id')->nullable()->after('specialization')->constrained('polis')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('doctors', 'poli_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropConstrainedForeignId('poli_id');
            });
        }
        Schema::dropIfExists('polis');
    }
};

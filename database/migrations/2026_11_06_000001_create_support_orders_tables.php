<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Poli penunjang (lab, radiologi, EKG): services get a category, support orders per visit,
 * and result files stored on the private disk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'category')) {
                // konsultasi, tindakan, penunjang
                $table->string('category', 20)->default('tindakan')->after('name');
            }
            if (! Schema::hasColumn('services', 'poli_id')) {
                $table->foreignId('poli_id')->nullable()->after('category')->constrained('polis')->nullOnDelete();
            }
        });

        $now = now();
        foreach ([['LAB', 'Laboratorium'], ['RAD', 'Radiologi / Rontgen'], ['EKG', 'EKG']] as [$code, $name]) {
            if (! DB::table('polis')->where('code', $code)->exists()) {
                DB::table('polis')->insert(['code' => $code, 'name' => $name, 'type' => 'penunjang', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        if (! Schema::hasTable('support_orders')) {
            Schema::create('support_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('appointment_id')->constrained('appointments')->restrictOnDelete();
                $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
                $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
                $table->foreignId('poli_id')->nullable()->constrained('polis')->nullOnDelete();
                $table->unsignedSmallInteger('qty')->default(1);
                $table->foreignId('ordered_by')->nullable()->constrained('users')->nullOnDelete();
                // pendaftaran, perawat, dokter
                $table->string('ordered_from', 20);
                // ordered -> in_progress -> done; cancelled
                $table->string('status', 20)->default('ordered');
                $table->text('notes')->nullable();
                $table->text('result_text')->nullable();
                $table->foreignId('result_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('result_at')->nullable();
                $table->string('cancel_reason')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('support_order_files')) {
            Schema::create('support_order_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('support_order_id')->constrained('support_orders')->cascadeOnDelete();
                $table->string('original_name');
                $table->string('path');
                $table->string('mime', 100);
                $table->unsignedInteger('size');
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        $grants = [
            'doctor' => ['support.order', 'support.view'],
            'nurse' => ['support.order', 'support.view'],
            'staff' => ['support.order'],
            'registration_cashier' => ['support.order'],
        ];
        foreach ($grants as $name => $add) {
            $role = DB::table('roles')->where('name', $name)->first();
            if (! $role) {
                continue;
            }
            $current = json_decode($role->permissions ?? '[]', true) ?: [];
            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_values(array_unique(array_merge($current, $add)))),
                'updated_at' => $now,
            ]);
        }

        if (! DB::table('roles')->where('name', 'support_staff')->exists()) {
            DB::table('roles')->insert([
                'name' => 'support_staff',
                'label' => 'Petugas Penunjang',
                'description' => 'Analis lab / radiografer: input hasil pemeriksaan penunjang',
                'permissions' => json_encode(['patients.view', 'appointments.view', 'support.view', 'support.manage', 'attendance.self', 'payroll.self']),
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_order_files');
        Schema::dropIfExists('support_orders');
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'poli_id')) {
                $table->dropConstrainedForeignId('poli_id');
            }
            if (Schema::hasColumn('services', 'category')) {
                $table->dropColumn('category');
            }
        });
        // Penunjang polis and the role may be in use; leave them.
    }
};

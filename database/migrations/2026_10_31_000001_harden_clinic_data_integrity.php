<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - Stop cascading deletes that silently erased medical and payment history.
 * - Make queue numbers and one-record-per-visit unique at the database level.
 * - Allow optional patient contact fields (the form already treats them as optional).
 * - Add indexes used by the daily queue and revenue reports.
 */
return new class extends Migration
{
    /** table => columns whose foreign key should restrict deletes */
    private array $foreignKeys = [
        'appointments' => ['patient_id' => 'patients', 'doctor_id' => 'doctors'],
        'medical_records' => ['appointment_id' => 'appointments', 'patient_id' => 'patients', 'doctor_id' => 'doctors'],
        'payments' => ['appointment_id' => 'appointments', 'patient_id' => 'patients'],
    ];

    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
            $table->string('emergency_contact')->nullable()->change();
            $table->string('emergency_phone')->nullable()->change();
        });

        $this->rebuildForeignKeys('restrict');

        Schema::table('appointments', function (Blueprint $table) {
            $table->unique('queue_number');
            $table->index(['appointment_date', 'status']);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->unique('appointment_id');
        });

        // Left behind by down() on MySQL; the unique index now covers the foreign key
        if (Schema::hasIndex('medical_records', 'medical_records_appointment_id_index')) {
            Schema::table('medical_records', function (Blueprint $table) {
                $table->dropIndex('medical_records_appointment_id_index');
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            // MySQL needs a plain index for the foreign key before the unique one can go
            $table->index('appointment_id', 'medical_records_appointment_id_index');
            $table->dropUnique(['appointment_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['appointment_date', 'status']);
            $table->dropUnique(['queue_number']);
        });

        $this->rebuildForeignKeys('cascade');
    }

    private function rebuildForeignKeys(string $onDelete): void
    {
        foreach ($this->foreignKeys as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach (array_keys($columns) as $column) {
                    $table->dropForeign([$column]);
                }
            });

            Schema::table($tableName, function (Blueprint $table) use ($columns, $onDelete) {
                foreach ($columns as $column => $references) {
                    $table->foreign($column)->references('id')->on($references)->onDelete($onDelete);
                }
            });
        }
    }
};

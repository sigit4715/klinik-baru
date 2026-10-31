<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * - Stop cascading deletes that silently erased medical and payment history.
 * - Make queue numbers and one-record-per-visit unique at the database level.
 * - Allow optional patient contact fields (the form already treats them as optional).
 * - Add indexes used by the daily queue and revenue reports.
 *
 * Safe to re-run after a partial failure (MySQL DDL is not transactional):
 * every index step checks whether it already exists.
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

        // Older data can contain duplicate queue numbers (the race this migration fixes).
        // Give the later duplicates the next free number of the same day.
        $this->renumberDuplicateQueueNumbers();

        if (! Schema::hasIndex('appointments', 'appointments_queue_number_unique')) {
            Schema::table('appointments', fn (Blueprint $table) => $table->unique('queue_number'));
        }
        if (! Schema::hasIndex('appointments', 'appointments_appointment_date_status_index')) {
            Schema::table('appointments', fn (Blueprint $table) => $table->index(['appointment_date', 'status']));
        }

        // Duplicate medical records for one visit cannot be merged automatically.
        // If any exist, keep them and rely on the application check instead of a unique index.
        $duplicateRecords = DB::table('medical_records')
            ->select('appointment_id')
            ->groupBy('appointment_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('appointment_id');

        if ($duplicateRecords->isEmpty()) {
            if (! Schema::hasIndex('medical_records', 'medical_records_appointment_id_unique')) {
                Schema::table('medical_records', fn (Blueprint $table) => $table->unique('appointment_id'));
            }
            // Left behind by down() on MySQL; the unique index now covers the foreign key
            if (Schema::hasIndex('medical_records', 'medical_records_appointment_id_index')) {
                Schema::table('medical_records', fn (Blueprint $table) => $table->dropIndex('medical_records_appointment_id_index'));
            }
        } else {
            Log::warning('Unique index on medical_records.appointment_id skipped: duplicate records exist for appointments '
                . $duplicateRecords->implode(', '));
        }

        if (! Schema::hasIndex('payments', 'payments_status_created_at_index')) {
            Schema::table('payments', fn (Blueprint $table) => $table->index(['status', 'created_at']));
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('payments', 'payments_status_created_at_index')) {
            Schema::table('payments', fn (Blueprint $table) => $table->dropIndex(['status', 'created_at']));
        }

        if (Schema::hasIndex('medical_records', 'medical_records_appointment_id_unique')) {
            Schema::table('medical_records', function (Blueprint $table) {
                // MySQL needs a plain index for the foreign key before the unique one can go
                $table->index('appointment_id', 'medical_records_appointment_id_index');
                $table->dropUnique(['appointment_id']);
            });
        }

        if (Schema::hasIndex('appointments', 'appointments_appointment_date_status_index')) {
            Schema::table('appointments', fn (Blueprint $table) => $table->dropIndex(['appointment_date', 'status']));
        }
        if (Schema::hasIndex('appointments', 'appointments_queue_number_unique')) {
            Schema::table('appointments', fn (Blueprint $table) => $table->dropUnique(['queue_number']));
        }

        $this->rebuildForeignKeys('cascade');
    }

    private function renumberDuplicateQueueNumbers(): void
    {
        $duplicates = DB::table('appointments')
            ->select('queue_number')
            ->groupBy('queue_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('queue_number');

        foreach ($duplicates as $queueNumber) {
            $prefix = substr($queueNumber, 0, 9); // "Ymd-"
            $ids = DB::table('appointments')->where('queue_number', $queueNumber)->orderBy('id')->pluck('id');

            foreach ($ids->slice(1) as $id) {
                $max = DB::table('appointments')
                    ->where('queue_number', 'like', $prefix . '%')
                    ->pluck('queue_number')
                    ->map(fn ($q) => (int) substr($q, strlen($prefix)))
                    ->max();

                DB::table('appointments')->where('id', $id)
                    ->update(['queue_number' => $prefix . str_pad(($max ?? 0) + 1, 3, '0', STR_PAD_LEFT)]);
            }
        }
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

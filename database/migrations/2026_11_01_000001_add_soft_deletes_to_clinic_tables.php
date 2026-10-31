<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting patients, doctors, medicines, services and appointments now moves them
 * to the archive (deleted_at) so history stays intact and they can be restored.
 */
return new class extends Migration
{
    private array $tables = ['patients', 'doctors', 'medicines', 'services', 'appointments'];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->softDeletes());
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropSoftDeletes());
            }
        }
    }
};

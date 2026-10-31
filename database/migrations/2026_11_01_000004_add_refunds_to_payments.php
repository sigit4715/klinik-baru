<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refunds: full or partial, with reason, recorded on the date the money goes back.
 * Also grants the new stock permission to roles that could already manage medicines,
 * because stock is no longer edited through the medicine form.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // enum -> string so the new "refunded" status fits
            $table->string('status', 20)->default('pending')->change();
        });

        if (! Schema::hasColumn('payments', 'refunded_amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('refunded_amount', 12, 2)->default(0)->after('change_amount');
            });
        }

        // Guarded so the migration can be re-run after a partial failure (MySQL DDL is not transactional)
        if (! Schema::hasTable('payment_refunds')) {
            Schema::create('payment_refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
                $table->decimal('amount', 12, 2);
                $table->text('reason');
                $table->string('refund_method', 20);
                $table->boolean('restocked')->default(false);
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('created_at');
            });
        }

        if (Schema::hasTable('roles')) {
            foreach (DB::table('roles')->where('is_system', false)->get() as $role) {
                $permissions = json_decode($role->permissions ?? '[]', true) ?: [];
                if (in_array('medicines.manage', $permissions, true) && ! in_array('medicines.stock', $permissions, true)) {
                    $permissions[] = 'medicines.stock';
                    DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode(array_values($permissions))]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');

        if (Schema::hasColumn('payments', 'refunded_amount')) {
            Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('refunded_amount'));
        }

        DB::table('payments')->where('status', 'refunded')->update(['status' => 'paid']);
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending')->change();
        });
    }
};

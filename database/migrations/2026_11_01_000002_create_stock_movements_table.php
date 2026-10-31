<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kartu stok: every change to a medicine's stock is recorded with the balance before/after.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            // initial, in, out, return, adjustment, disposal
            $table->string('type', 20);
            // Signed change: positive adds stock, negative removes it
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->nullableMorphs('reference');
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['medicine_id', 'created_at']);
        });

        // Opening balance so every card starts from the current stock
        $now = now();
        DB::table('medicines')->orderBy('id')->each(function ($medicine) use ($now) {
            DB::table('stock_movements')->insert([
                'medicine_id' => $medicine->id,
                'type' => 'initial',
                'quantity' => (int) $medicine->stock,
                'stock_before' => 0,
                'stock_after' => (int) $medicine->stock,
                'notes' => 'Saldo awal saat kartu stok diaktifkan',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

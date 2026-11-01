<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Farmasi: more medicine details, suppliers, purchase receipts (faktur) and returns to suppliers.
 * Stock changes still go through stock_movements (types "in" and "supplier_return").
 */
return new class extends Migration
{
    private array $medicineColumns = [
        'generic_name' => 255, 'dosage_form' => 50, 'strength' => 50, 'drug_class' => 50,
        'rack_location' => 50, 'manufacturer' => 255, 'barcode' => 100,
    ];

    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $after = 'name';
            foreach ($this->medicineColumns as $column => $length) {
                if (! Schema::hasColumn('medicines', $column)) {
                    $table->string($column, $length)->nullable()->after($after);
                }
                $after = $column;
            }
        });

        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->string('phone', 30)->nullable();
                $table->text('address')->nullable();
                $table->string('notes')->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                // Internal number, e.g. PB-20261107-001
                $table->string('purchase_number', 30)->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
                // Supplier's invoice (faktur) number
                $table->string('invoice_number', 100)->nullable();
                $table->date('purchase_date');
                $table->decimal('total', 14, 2)->default(0);
                $table->string('notes', 500)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('purchase_date');
            });
        }

        if (! Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
                $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
                $table->unsignedInteger('qty');
                $table->decimal('buy_price', 14, 2);
                $table->date('expired_date')->nullable();
                $table->string('batch_number', 50)->nullable();
                $table->decimal('subtotal', 14, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number', 30)->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
                $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
                $table->date('return_date');
                $table->decimal('total', 14, 2)->default(0);
                $table->string('reason', 500);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
                $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
                $table->unsignedInteger('qty');
                $table->decimal('price', 14, 2);
                $table->decimal('subtotal', 14, 2);
                $table->timestamps();
            });
        }

        $grants = [
            'pharmacist' => ['pharmacy.dashboard', 'medicines.stock', 'suppliers.manage', 'purchases.manage', 'purchases.return'],
        ];
        foreach ($grants as $name => $add) {
            $role = DB::table('roles')->where('name', $name)->first();
            if (! $role) {
                continue;
            }
            $current = json_decode($role->permissions ?? '[]', true) ?: [];
            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_values(array_unique(array_merge($current, $add)))),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
        Schema::table('medicines', function (Blueprint $table) {
            foreach (array_keys($this->medicineColumns) as $column) {
                if (Schema::hasColumn('medicines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

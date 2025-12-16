<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('unit');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(10);
            $table->decimal('buy_price', 12, 2);
            $table->decimal('sell_price', 12, 2);
            $table->date('expired_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicines');
    }
};

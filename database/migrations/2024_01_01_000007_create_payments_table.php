<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('payment_method', ['cash', 'debit', 'credit', 'transfer', 'bpjs'])->default('cash');
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->json('items');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};

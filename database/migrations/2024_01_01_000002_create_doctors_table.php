<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('sip_number')->unique();
            $table->string('name');
            $table->string('specialization');
            $table->string('phone');
            $table->string('email')->unique();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->json('schedule')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctors');
    }
};

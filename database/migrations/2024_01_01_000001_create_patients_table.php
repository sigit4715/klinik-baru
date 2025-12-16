<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('no_rm')->unique();
            $table->string('name');
            $table->string('nik')->unique();
            $table->enum('gender', ['L', 'P']);
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable();
            $table->string('allergy')->nullable();
            $table->string('emergency_contact');
            $table->string('emergency_phone');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patients');
    }
};

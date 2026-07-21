<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('level');
            $table->string('name');
            $table->unsignedInteger('hourly_rate');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('classes');
    }
};

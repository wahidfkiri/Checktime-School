<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('penalty_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('absence_count')->default(1);
            $table->decimal('absence_rate', 5, 2)->default(7.00);
            $table->unsignedInteger('late_minutes')->default(30);
            $table->decimal('late_rate', 5, 2)->default(5.00);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('penalty_rules');
    }
};

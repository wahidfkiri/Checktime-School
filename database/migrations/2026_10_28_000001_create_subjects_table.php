<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table des matières (objet école LOCAL, scopé par client_id).
 * Remplace à terme le champ texte libre employee_schedules.subject.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subjects')) {
            return;
        }

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');                 // ex: Mathématiques
            $table->string('code')->nullable();     // ex: MATHS
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            // Une matière est unique par école (sur le nom)
            $table->unique(['client_id', 'name']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};

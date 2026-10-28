<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot N-N : un enseignant (Employee) enseigne plusieurs matières,
 * une matière est enseignée par plusieurs enseignants.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_subject')) {
            return;
        }

        Schema::create('employee_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_subject');
    }
};

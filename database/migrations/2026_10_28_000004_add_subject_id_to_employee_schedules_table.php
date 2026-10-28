<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Relie chaque vacation à une matière (subject_id) au lieu du texte libre `subject`.
 * Nullable + rétro-compatible : l'ancien texte reste tant que le backfill n'est pas fait.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_schedules')) {
            return;
        }
        if (Schema::hasColumn('employee_schedules', 'subject_id')) {
            return;
        }

        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->foreignId('subject_id')
                ->nullable()
                ->after('class_id')
                ->constrained('subjects')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_schedules', 'subject_id')) {
            Schema::table('employee_schedules', function (Blueprint $table) {
                $table->dropConstrainedForeignId('subject_id');
            });
        }
    }
};

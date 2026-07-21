<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_schedules', 'is_working_day')) {
                $table->boolean('is_working_day')->default(true)->after('day_of_week');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->dropColumn('is_working_day');
        });
    }
};

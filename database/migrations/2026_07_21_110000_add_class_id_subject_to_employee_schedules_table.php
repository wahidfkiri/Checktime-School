<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('employee_id')->constrained('classes')->nullOnDelete();
            $table->string('subject')->nullable()->after('class_id');
        });
    }

    public function down(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('class_id');
            $table->dropColumn('subject');
        });
    }
};

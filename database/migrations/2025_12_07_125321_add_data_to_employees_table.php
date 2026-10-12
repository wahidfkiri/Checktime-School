<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'area_name')) {
                $table->string('area_name')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('employees', 'dept_name')) {
                $table->string('dept_name')->nullable()->after('area_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('area_name');
            $table->dropColumn('dept_name');    
        });
    }
};

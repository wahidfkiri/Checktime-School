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
        Schema::table('employee_permissions', function (Blueprint $table) {
            $table->date('date_debut')->nullable()->after('date');
            $table->date('date_fin')->nullable()->after('date_debut');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_permissions', function (Blueprint $table) {
            $table->dropColumn('date_debut');
            $table->dropColumn('date_fin');
        });
    }
};

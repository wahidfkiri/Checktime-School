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
        Schema::table('leaves', function (Blueprint $table) {
            if (!Schema::hasColumn('leaves', 'reason')) {
                $table->text('reason')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('leaves', 'status')) {
                $table->string('status')->default('pending')->after('reason');
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
        Schema::table('leaves', function (Blueprint $table) {
            //
        });
    }
};

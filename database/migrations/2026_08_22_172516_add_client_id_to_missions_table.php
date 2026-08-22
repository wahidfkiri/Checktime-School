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
    public function up(): void
{
    Schema::table('missions', function (Blueprint $table) {
        $table->unsignedBigInteger('client_id')->nullable()->after('id');
    });
}

public function down(): void
{
    Schema::table('missions', function (Blueprint $table) {
        $table->dropColumn('client_id');
    });
}
};

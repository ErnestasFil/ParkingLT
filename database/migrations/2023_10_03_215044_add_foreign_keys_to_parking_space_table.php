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
        Schema::table('parking_space', function (Blueprint $table) {
            $table->foreign(['fk_Parking_zoneid'], 'has_parking_space')->references(['id'])->on('parking_zone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parking_space', function (Blueprint $table) {
            $table->dropForeign('has_parking_space');
        });
    }
};

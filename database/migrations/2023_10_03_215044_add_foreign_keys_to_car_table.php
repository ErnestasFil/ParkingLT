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
        Schema::table('car', function (Blueprint $table) {
            $table->foreign(['fk_Userid'], 'has_car')->references(['id'])->on('user');
            $table->foreign(['fuel_type'], 'car_ibfk_1')->references(['id_Fuel'])->on('fuel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('car', function (Blueprint $table) {
            $table->dropForeign('has_car');
            $table->dropForeign('car_ibfk_1');
        });
    }
};

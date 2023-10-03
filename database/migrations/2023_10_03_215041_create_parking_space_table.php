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
        Schema::create('parking_space', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('fk_Parking_zoneid')->index('has_parking_space');
            $table->integer('space_number');
            $table->multiPolygon('location_polygon');
            $table->boolean('invalid_place')->default(false);
            $table->string('street');
            $table->text('information');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_space');
    }
};

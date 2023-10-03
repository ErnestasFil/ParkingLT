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
        Schema::create('parking_zone', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('colour');
            $table->integer('paying_time');
            $table->double('price');
            $table->multiPolygon('location_polygon');
            $table->text('information');
            $table->string('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_zone');
    }
};

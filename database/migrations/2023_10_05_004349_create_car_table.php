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
        Schema::create('car', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('fk_Userid')->index('has_car');
            $table->string('manufacturer');
            $table->string('model');
            $table->string('license_plate');
            $table->string('reg_certificate');
            $table->integer('fuel_type')->index('fuel_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car');
    }
};

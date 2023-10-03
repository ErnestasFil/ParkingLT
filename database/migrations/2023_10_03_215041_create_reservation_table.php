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
        Schema::create('reservation', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('fk_Userid')->index('has_reservation');
            $table->integer('fk_Parking_spaceid')->index('is_reserved');
            $table->integer('fk_Privilegeid')->nullable()->index('has_discount');
            $table->dateTime('date_from');
            $table->dateTime('date_until');
            $table->double('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservation');
    }
};

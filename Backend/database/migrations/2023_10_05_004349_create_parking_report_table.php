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
        Schema::create('parking_report', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('fk_Carid')->index('is_added');
            $table->integer('fk_Adminid')->index('create_parking_report');
            $table->integer('fk_Parking_spaceid')->index('has_parking_info');
            $table->dateTime('date');
            $table->text('information');
            $table->double('fine');
            $table->dateTime('pay_until');
            $table->integer('status')->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_report');
    }
};

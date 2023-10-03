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
        Schema::table('parking_report_photo', function (Blueprint $table) {
            $table->foreign(['fk_Parking_reportid'], 'has_parking_rPhoto')->references(['id'])->on('parking_report');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parking_report_photo', function (Blueprint $table) {
            $table->dropForeign('has_parking_rPhoto');
        });
    }
};

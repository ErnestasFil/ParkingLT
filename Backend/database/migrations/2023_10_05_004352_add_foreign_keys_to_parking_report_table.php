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
        Schema::table('parking_report', function (Blueprint $table) {
            $table->foreign(['fk_Parking_spaceid'], 'has_parking_info')->references(['id'])->on('parking_space');
            $table->foreign(['status'], 'parking_report_ibfk_1')->references(['id_Status'])->on('status');
            $table->foreign(['fk_Adminid'], 'create_parking_report')->references(['id'])->on('user');
            $table->foreign(['fk_Carid'], 'is_added')->references(['id'])->on('car');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parking_report', function (Blueprint $table) {
            $table->dropForeign('has_parking_info');
            $table->dropForeign('parking_report_ibfk_1');
            $table->dropForeign('create_parking_report');
            $table->dropForeign('is_added');
        });
    }
};

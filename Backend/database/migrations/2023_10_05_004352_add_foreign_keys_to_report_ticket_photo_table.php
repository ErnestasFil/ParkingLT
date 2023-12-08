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
        Schema::table('report_ticket_photo', function (Blueprint $table) {
            $table->foreign(['fk_Report_ticketid'], 'has_report_photo')->references(['id'])->on('report_ticket');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_ticket_photo', function (Blueprint $table) {
            $table->dropForeign('has_report_photo');
        });
    }
};

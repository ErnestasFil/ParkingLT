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
        Schema::table('reservation', function (Blueprint $table) {
            $table->foreign(['fk_Userid'], 'has_reservation')->references(['id'])->on('user');
            $table->foreign(['fk_Privilegeid'], 'has_discount')->references(['id'])->on('privilege');
            $table->foreign(['fk_Parking_spaceid'], 'is_reserved')->references(['id'])->on('parking_space');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation', function (Blueprint $table) {
            $table->dropForeign('has_reservation');
            $table->dropForeign('has_discount');
            $table->dropForeign('is_reserved');
        });
    }
};

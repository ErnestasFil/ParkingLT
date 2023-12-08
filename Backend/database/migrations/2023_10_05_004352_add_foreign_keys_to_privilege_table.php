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
        Schema::table('privilege', function (Blueprint $table) {
            $table->foreign(['fk_Userid'], 'have_privilege')->references(['id'])->on('user');
            $table->foreign(['fk_Adminid'], 'confirm_privilege')->references(['id'])->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('privilege', function (Blueprint $table) {
            $table->dropForeign('have_privilege');
            $table->dropForeign('confirm_privilege');
        });
    }
};

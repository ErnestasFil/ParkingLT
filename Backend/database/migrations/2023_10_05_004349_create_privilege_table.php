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
        Schema::create('privilege', function (Blueprint $table) {
            $table->integer('id', true);
            $table->double('discount');
            $table->boolean('confirmed')->default(false);
            $table->dateTime('date_from');
            $table->dateTime('date_until');
            $table->integer('fk_Userid')->index('have_privilege');
            $table->integer('fk_Adminid')->nullable()->index('confirm_privilege');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('privilege');
    }
};

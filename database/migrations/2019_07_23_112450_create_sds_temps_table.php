<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sds_temps', function (Blueprint $table) {
            $table->bigIncrements('st_sl_id');
            $table->integer('st_com_id');
            $table->mediumText('st_title');
            $table->mediumText('st_message');
            $table->integer('st_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sds_temps');
    }
};

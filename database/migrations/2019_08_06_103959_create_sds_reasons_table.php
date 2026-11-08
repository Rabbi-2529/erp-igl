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
        Schema::create('sds_reasons', function (Blueprint $table) {
            $table->bigIncrements('sr_slid');
            $table->tinyInteger('sr_category');
            $table->string('sr_reason');
            $table->dateTime('sr_reg_date');
            $table->tinyInteger('sr_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sds_reasons');
    }
};

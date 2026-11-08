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
        Schema::create('sds_find_uses', function (Blueprint $table) {
            $table->bigIncrements('sf_slid');
            $table->string('sf_howto');
            $table->date('sf_create_date');
            $table->integer('sf_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sds_find_uses');
    }
};

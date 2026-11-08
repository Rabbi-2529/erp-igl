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
        Schema::create('sds_reason_coms', function (Blueprint $table) {
            $table->bigIncrements('src_slid');
            $table->integer('src_company_id');
            $table->tinyInteger('src_reason_id');
            $table->date('src_create_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sds_reason_coms');
    }
};

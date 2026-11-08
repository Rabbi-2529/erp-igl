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
        Schema::create('sds_team_names', function (Blueprint $table) {
            $table->bigIncrements('tl_slid');
            $table->integer('tl_com_id');
            $table->string('tl_user_id');
            $table->mediumText('tl_team_name');
            $table->dateTime('tl_date');
            $table->integer('tl_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sds_team_names');
    }
};

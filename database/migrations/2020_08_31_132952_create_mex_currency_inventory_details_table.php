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
        Schema::create('mex_currency_inventory_details', function (Blueprint $table) {
            $table->bigIncrements('mex_curr_invdet_id');
            $table->unsignedInteger('mex_curr_invdet_com_id');
            $table->unsignedInteger('mex_curr_invdet_country_id');
            $table->unsignedInteger('mex_curr_invdet_currinv_id');
            $table->unsignedInteger('mex_curr_invdet_currinv_sell_id')->nullable();
            $table->unsignedInteger('mex_curr_invdet_curr_id');
            $table->unsignedInteger('mex_curr_invdet_buy_id');
            $table->unsignedInteger('mex_curr_invdet_sell_id')->nullable();
            $table->string('mex_curr_invdet_slno')->comments('serial number')->nullable();
            $table->tinyInteger('mex_curr_invdet_sell_status')->default('0')->comments('0=not sell,1=sell,2=refund,3=waranty');
            $table->tinyInteger('mex_curr_invdet_status')->default('1')->comments('1=active 0=inactive');
            $table->unsignedInteger('mex_curr_invdet_submit_by')->nullable();
            $table->dateTime('mex_curr_invdet_submit_at')->nullable();
            $table->unsignedInteger('mex_curr_invdet_update_by')->nullable();
            $table->dateTime('mex_curr_invdet_update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mex_currency_inventory_details');
    }
};

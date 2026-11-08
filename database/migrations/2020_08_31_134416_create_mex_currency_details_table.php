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
        Schema::create('mex_currency_details', function (Blueprint $table) {
            $table->bigIncrements('mex_curr_det_id');
            $table->integer('mex_curr_id');
            $table->unsignedInteger('mex_curr_det_country_id');
            $table->unsignedInteger('mex_curr_det_com_id')->nullable();
            $table->unsignedInteger('mex_curr_det_type_id')->nullable();
            $table->string('mex_curr_det_sup')->nullable();
            $table->string('mex_curr_det_curr_name')->nullable();
            $table->decimal('mex_curr_det_buy_price',12,2)->comments('currduct buy price');
            $table->decimal('mex_curr_det_sell_price',12,2)->comments('currduct sell price');
            $table->string('mex_curr_det_curr_description')->nullable();
            $table->tinyInteger('mex_curr_det_short_qty')->comments('short Quantity')->nullable();
            $table->integer('mex_curr_det_available_qty')->default(0);
            $table->tinyInteger('mex_curr_det_status')->nullable()->comments('0=deactive,1=active');
            $table->unsignedInteger('mex_curr_det_submit_by')->nullable();
            $table->dateTime('mex_curr_det_submit_at')->nullable();
            $table->unsignedInteger('mex_curr_det_update_by')->nullable();
            $table->dateTime('mex_curr_det_update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mex_currency_details');
    }
};

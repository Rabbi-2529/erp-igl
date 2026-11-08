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
        Schema::create('inv_product_inventory_details', function (Blueprint $table) {
            $table->bigIncrements('inv_pro_invdet_id');
            $table->unsignedInteger('inv_pro_invdet_com_id');
            $table->unsignedInteger('inv_pro_invdet_proinv_id');
            $table->unsignedInteger('inv_pro_invdet_proinv_sell_id')->nullable();
            $table->unsignedInteger('inv_pro_invdet_pro_id');
            $table->unsignedInteger('inv_pro_invdet_buy_id');
            $table->unsignedInteger('inv_pro_invdet_sell_id')->nullable();
            $table->string('inv_pro_invdet_slno')->comments('serial number')->nullable();
            $table->tinyInteger('inv_pro_invdet_sell_status')->default('0')->comments('0=not sell,1=sell,2=refund,3=waranty');
            $table->tinyInteger('inv_pro_invdet_status')->default('1')->comments('1=active 0=inactive');
            $table->unsignedInteger('inv_pro_invdet_submit_by')->nullable();
            $table->dateTime('inv_pro_invdet_submit_at')->nullable();
            $table->unsignedInteger('inv_pro_invdet_update_by')->nullable();
            $table->dateTime('inv_pro_invdet_update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_product_inventory_details');
    }
};

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
        Schema::create('inv_product_details', function (Blueprint $table) {
            $table->bigIncrements('inv_pro_det_id');
            $table->unsignedInteger('inv_pro_det_com_id')->nullable();
            $table->unsignedInteger('inv_pro_det_type_id')->nullable();
            $table->string('inv_pro_det_sup')->nullable();
            $table->string('inv_pro_det_pro_id')->nullable();
            $table->decimal('inv_pro_det_buy_price',12,2)->comments('product buy price');
            $table->decimal('inv_pro_det_sell_price',12,2)->comments('product sell price');
            $table->integer('inv_pro_det_pro_warranty')->default(0)->comments('0= no warranty');
            $table->string('inv_pro_det_pro_description')->nullable();
            $table->tinyInteger('inv_pro_det_short_qty')->comments('short Quantity')->nullable();
            $table->integer('inv_pro_det_available_qty')->default(0);
            $table->tinyInteger('inv_pro_det_status')->nullable()->comments('0=deactive,1=active');
            $table->unsignedInteger('inv_pro_det_submit_by')->nullable();
            $table->dateTime('inv_pro_det_submit_at')->nullable();
            $table->unsignedInteger('inv_pro_det_update_by')->nullable();
            $table->dateTime('inv_pro_det_update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_product_details');
    }
};

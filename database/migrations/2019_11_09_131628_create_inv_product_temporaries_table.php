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
        Schema::create('inv_product_temporaries', function (Blueprint $table) {
            $table->bigIncrements('inv_pro_temp_id');
            $table->unsignedInteger('inv_pro_temp_user_id');
            $table->integer('inv_pro_temp_cus_id')->nullable();
            $table->unsignedInteger('inv_pro_temp_pro_id');
            $table->string('inv_pro_temp_pro_name');
            $table->string('inv_pro_temp_type_name');
            $table->decimal('inv_pro_temp_short_qty',8,2)->default('0');
            $table->string('inv_pro_temp_short_remarks')->nullable();
            $table->integer('inv_pro_temp_qty');
            $table->string('inv_pro_temp_invoice_no')->nullable();
            $table->decimal('inv_pro_temp_unit_price',12,2);
            $table->date('inv_pro_temp_exp_date')->nullable();
            $table->tinyInteger('inv_pro_temp_slno')->nullable();
            $table->tinyInteger('inv_pro_temp_deal_type');
            $table->tinyInteger('inv_pro_temp_type')->default('1');
            $table->tinyInteger('inv_pro_temp_status');
            $table->dateTime('inv_pro_temp_created_at');
            $table->dateTime('inv_pro_temp_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_product_temporaries');
    }
};

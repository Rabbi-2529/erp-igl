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
        Schema::create('mex_currency_temporaries', function (Blueprint $table) {
            $table->bigIncrements('mex_curr_temp_id');
            $table->unsignedInteger('mex_curr_temp_user_id');
            $table->integer('mex_curr_temp_cus_id')->nullable();
            $table->unsignedInteger('mex_curr_temp_curr_id');
            $table->unsignedInteger('mex_curr_temp_country_id');
            $table->string('mex_curr_temp_curr_name');
            $table->string('mex_curr_temp_type_name')->nullable();
            $table->decimal('mex_curr_temp_short_qty',8,2)->default('0');
            $table->string('mex_curr_temp_short_remarks')->nullable();
            $table->integer('mex_curr_temp_qty');
            $table->string('mex_curr_temp_invoice_no')->nullable();
            $table->decimal('mex_curr_temp_unit_price',12,2);
            $table->date('mex_curr_temp_exp_date')->nullable();
            $table->tinyInteger('mex_curr_temp_slno')->nullable();
            $table->tinyInteger('mex_curr_temp_deal_type');
            $table->tinyInteger('mex_curr_temp_type')->default('1');
            $table->tinyInteger('mex_curr_temp_status');
            $table->dateTime('mex_curr_temp_created_at')->nullable();
            $table->dateTime('mex_curr_temp_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mex_currency_temporaries');
    }
};

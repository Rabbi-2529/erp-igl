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
        Schema::create('inv_customers', function (Blueprint $table) {
            $table->bigIncrements('inv_cus_id');
            $table->unsignedInteger('inv_cus_com_id');
            $table->string('inv_cus_name');
            $table->string('inv_cus_com_name')->nullable();
            $table->string('inv_cus_mobile');
            $table->string('inv_cus_email')->nullable();
            $table->string('inv_cus_address')->nullable();
            $table->string('inv_cus_website')->nullable();
            $table->tinyInteger('inv_cus_type')->default('1')->comments('1=generl 2 =customer');
            $table->tinyInteger('inv_cus_customer_type')->comments('1=local 2=global');
            $table->tinyInteger('inv_cus_status')->comments('1=active,0=inactive');
            $table->unsignedInteger('inv_cus_submit_by')->nullable();
            $table->dateTime('inv_cus_submit_at')->nullable();
            $table->unsignedInteger('inv_cus_update_by')->nullable();
            $table->dateTime('inv_cus_update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_customers');
    }
};

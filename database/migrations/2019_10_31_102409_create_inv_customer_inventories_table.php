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
        Schema::create('inv_customer_inventories', function (Blueprint $table) {
            $table->bigIncrements('inv_cus_inv_id');
            $table->unsignedInteger('inv_cus_inv_com_id');
            $table->unsignedInteger('inv_cus_inv_cus_id');
            $table->unsignedInteger('inv_cus_inv_proinv_memo_no')->nullable();
            $table->decimal('inv_cus_inv_debit',12,2)->default('0');
            $table->decimal('inv_cus_inv_credit',12,2)->default('0');
            $table->tinyInteger('inv_cus_inv_tran_type')->comments('1=sell product,2=deposit,3=withdraw,4=payment-collection, 5=payment-refund');
            $table->string('inv_cus_inv_description',250)->nullable();
            $table->date('inv_cus_inv_issue_date');
            $table->tinyInteger('inv_cus_inv_status')->nullable()->comments('1=active,0=inactive');
            $table->unsignedInteger('inv_cus_inv_submit_by')->nullable();
            $table->dateTime('inv_cus_inv_submit_at')->nullable();
            $table->unsignedInteger('inv_cus_inv_update_by')->nullable();
            $table->dateTime('inv_cus_inv_update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_customer_inventories');
    }
};

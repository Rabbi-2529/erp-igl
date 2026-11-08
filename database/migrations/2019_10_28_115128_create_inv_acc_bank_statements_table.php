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
        Schema::create('inv_acc_bank_statements', function (Blueprint $table) {
            $table->bigIncrements('inv_abs_id')->unsigned();
            $table->integer('inv_abs_company_id')->unsigned()->comment('company id from admin_user table');
            $table->unsignedInteger('inv_abs_inventory_id')->nullable()->comment('inventory id');
            $table->unsignedInteger('inv_abs_reference_id')->nullable()->comment('customer/supplier id');
            $table->unsignedInteger('inv_abs_reference_type')->comment('1=customer, 2=supplier');
            $table->unsignedInteger('inv_abs_bank_id')->comment('bank id from inv_acc_bank_infos table');
            $table->decimal('inv_abs_debit', 12,2)->default(0)->comment('debit amount');
            $table->decimal('inv_abs_credit', 12,2)->default(0)->comment('credit amount');
            $table->date('inv_abs_transaction_date')->comment('transaction date');
            $table->string('inv_abs_voucher_no', 128)->nullable()->comment('transaction voucher date');
            $table->string('inv_abs_description', 255)->nullable()->comment('transaction details');
            $table->unsignedInteger('inv_abs_contra_transaction_id')->nullable()->comment('if this transaction is contra then another transaction id this is');

            $table->tinyInteger('inv_abs_status')->default(1);
            $table->unsignedInteger('inv_abs_submit_by')->nullable();
            $table->dateTime('inv_abs_submit_at')->nullable();
            $table->unsignedInteger('inv_abs_updated_by')->nullable();
            $table->dateTime('inv_abs_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_acc_bank_statements');
    }
};

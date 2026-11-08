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
        Schema::create('mex_acc_bank_statements', function (Blueprint $table) {
            $table->bigIncrements('mex_abs_id')->unsigned();
            $table->integer('mex_abs_company_id')->unsigned()->comment('company id from admin_user table');
            $table->unsignedInteger('mex_abs_inventory_id')->nullable()->comment('inventory id');
            $table->unsignedInteger('mex_abs_reference_id')->nullable()->comment('customer/supplier id');
            $table->unsignedInteger('mex_abs_reference_type')->comment('1=customer, 2=supplier');
            $table->unsignedInteger('mex_abs_bank_id')->comment('bank id from mex_acc_bank_infos table');
            $table->decimal('mex_abs_debit', 12,2)->default(0)->comment('debit amount');
            $table->decimal('mex_abs_credit', 12,2)->default(0)->comment('credit amount');
            $table->date('mex_abs_transaction_date')->comment('transaction date');
            $table->string('mex_abs_voucher_no', 128)->nullable()->comment('transaction voucher date');
            $table->string('mex_abs_description', 255)->nullable()->comment('transaction details');
            $table->unsignedInteger('mex_abs_contra_transaction_id')->nullable()->comment('if this transaction is contra then another transaction id this is');

            $table->tinyInteger('mex_abs_status')->default(1);
            $table->unsignedInteger('mex_abs_submit_by')->nullable();
            $table->dateTime('mex_abs_submit_at')->nullable();
            $table->unsignedInteger('mex_abs_updated_by')->nullable();
            $table->dateTime('mex_abs_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mex_acc_bank_statements');
    }
};

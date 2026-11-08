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
        Schema::create('inv_acc_bank_infos', function (Blueprint $table) {
            $table->bigIncrements('inv_abi_id')->unsigned();
            $table->integer('inv_abi_company_id')->unsigned()->comment('company id from admin user table');
            $table->string('inv_abi_bank_id',64);
            $table->string('inv_abi_branch_name',64);
            $table->string('inv_abi_account_name',64);
            $table->string('inv_abi_account_no',64);
            $table->date('inv_abi_open_date')->comment('bank account open date');
            $table->tinyInteger('inv_abi_account_type')->default(1)->comment('1=Bank, 2=Cash');
            $table->tinyInteger('inv_abi_status')->default(1);
            $table->unsignedInteger('inv_abi_submit_by')->nullable();
            $table->dateTime('inv_abi_submit_at')->nullable();
            $table->unsignedInteger('inv_abi_updated_by')->nullable();
            $table->dateTime('inv_abi_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_bank_infos');
    }
};

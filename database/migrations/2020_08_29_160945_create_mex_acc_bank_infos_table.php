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
        Schema::create('mex_acc_bank_infos', function (Blueprint $table) {
            $table->bigIncrements('mex_abi_id')->unsigned();
            $table->integer('mex_abi_company_id')->unsigned()->comment('company id from admin user table');
            $table->string('mex_abi_bank_id',64);
            $table->string('mex_abi_branch_name',64);
            $table->string('mex_abi_account_name',64);
            $table->string('mex_abi_account_no',64);
            $table->date('mex_abi_open_date')->comment('bank account open date');
            $table->tinyInteger('mex_abi_account_type')->default(1)->comment('1=Bank, 2=Cash');
            $table->tinyInteger('mex_abi_status')->default(1);
            $table->unsignedInteger('mex_abi_submit_by')->nullable();
            $table->dateTime('mex_abi_submit_at')->nullable();
            $table->unsignedInteger('mex_abi_updated_by')->nullable();
            $table->dateTime('mex_abi_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mex_acc_bank_infos');
    }
};

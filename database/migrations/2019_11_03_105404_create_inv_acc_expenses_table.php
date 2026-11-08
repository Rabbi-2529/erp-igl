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
        Schema::create('inv_acc_expenses', function (Blueprint $table) {
            $table->bigIncrements('inv_acc_exp_id');
            $table->integer('inv_acc_exp_company_id');
            $table->string('inv_acc_exp_expense_name');
            $table->tinyInteger('inv_acc_exp_status')->default(1);
            $table->integer('inv_acc_exp_created_by');
            $table->integer('inv_acc_exp_updated_by')->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_acc_expenses');
    }
};

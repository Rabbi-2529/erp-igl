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
        Schema::create('inv_acc_expense_categories', function (Blueprint $table) {
            $table->bigIncrements('inv_acc_exp_cat_category_id');
            $table->integer('inv_acc_exp_cat_company_id');
            $table->string('inv_acc_exp_cat_category_name');
            $table->tinyInteger('inv_acc_exp_cat_status')->default(1);
            $table->integer('inv_acc_exp_cat_created_by');
            $table->integer('inv_acc_exp_cat_updated_by')->default(null);
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
        Schema::dropIfExists('inv_acc_categories');
    }
};

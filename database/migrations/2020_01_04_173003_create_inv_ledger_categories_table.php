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
        Schema::create('inv_ledger_categories', function (Blueprint $table) {
            $table->bigIncrements('inv_ledg_cat_cat_id');
            $table->integer('inv_ledg_cat_company_id');
            $table->string('inv_ledg_cat_category_name');
            $table->tinyInteger('inv_ledg_cat_status')->default(1);
            $table->tinyInteger('inv_ledg_cat_created_by')->nullable();
            $table->dateTime('inv_ledg_cat_created_at')->nullable();
            $table->tinyInteger('inv_ledg_cat_updated_by')->nullable();
            $table->dateTime('inv_ledg_cat_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_ledger_categories');
    }
};

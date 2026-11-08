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
        Schema::create('inv_ledgers', function (Blueprint $table) {
            $table->bigIncrements('inv_ledg_id');
            $table->integer('inv_ledg_company_id');
            $table->integer('inv_ledg_category_id');
            $table->string('inv_ledg_ledger_name');
            $table->tinyInteger('inv_ledg_status')->default(1);
            $table->tinyInteger('inv_ledg_created_by')->nullable();
            $table->dateTime('inv_ledg_created_at')->nullable();
            $table->tinyInteger('inv_ledg_updated_by')->nullable();
            $table->dateTime('inv_ledg_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_ledgers');
    }
};

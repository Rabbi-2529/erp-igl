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
        Schema::create('inv_supplier_products', function (Blueprint $table) {
            $table->bigIncrements('inv_sup_pro_id');
            $table->unsignedInteger('inv_sup_pro_com_id');
            $table->unsignedInteger('inv_sup_pro_sup_id');
            $table->unsignedInteger('inv_sup_pro_pro_id');
            $table->tinyInteger('inv_sup_pro_status');
            $table->unsignedInteger('inv_sup_pro_submit_by')->nullable();
            $table->dateTime('inv_sup_pro_submit_at')->nullable();
            $table->unsignedInteger('inv_sup_pro_update_by')->nullable();
            $table->dateTime('inv_sup_pro_update_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_supplier_products');
    }
};

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
        Schema::create('inv_product_types', function (Blueprint $table) {
            $table->bigIncrements('inv_pro_type_id');
            $table->unsignedInteger('inv_pro_type_com_id')->nullable();
            $table->unsignedInteger('inv_pro_type_grp_id')->nullable();
            $table->string('inv_pro_type_name')->nullable();
            $table->tinyInteger('inv_pro_type_status')->nullable()->comments('1=active,0=inactive');
            $table->unsignedInteger('inv_pro_type_submit_by')->nullable();
            $table->dateTime('inv_pro_type_submit_at')->nullable();
            $table->unsignedInteger('inv_pro_type_updated_by')->nullable();
            $table->dateTime('inv_pro_type_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_product_types');
    }
};

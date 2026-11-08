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
        Schema::create('employee_comissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->double('comission',8,2)->nullable();
            $table->string('invoice_no')->nullable();
            $table->unsignedInteger('emp_id');
            $table->double('invoice_pro_qty',8,2);
            $table->double('invoice_pro_price',8,2);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_comissions');
    }
};

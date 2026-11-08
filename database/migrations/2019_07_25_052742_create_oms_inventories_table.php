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
        Schema::create('oms_inventories', function (Blueprint $table) {
            $table->bigIncrements('sl_id');
            $table->integer('oms_com_id')->nullable();
            $table->integer('oms_staff_id')->nullable();
            $table->integer('oms_what_for')->nullable();
            $table->mediumText('oms_reason')->nullable();
            $table->mediumText('oms_dfrom')->nullable();
            $table->mediumText('oms_mode')->nullable();
            $table->mediumText('oms_dto')->nullable();
            $table->integer('oms_person')->nullable();
            $table->date('oms_require_date')->nullable();
            $table->mediumText('oms_return_system')->nullable();
            $table->integer('oms_debit')->nullable();
            $table->integer('oms_credit')->nullable();
            $table->mediumText('oms_payment')->nullable();
            $table->dateTime('oms_insert_at')->nullable();
            $table->dateTime('oms_update_date')->nullable();
            $table->dateTime('oms_transaction_date')->nullable();
            $table->date('oms_bill_date')->nullable();
            $table->integer('oms_status')->nullable();
            $table->integer('oms_balance_status')->nullable();
            $table->integer('time_status')->nullable();
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
        Schema::dropIfExists('oms_inventories');
    }
};

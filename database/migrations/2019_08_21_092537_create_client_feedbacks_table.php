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
        Schema::create('client_feedbacks', function (Blueprint $table) {
            $table->bigIncrements('cf_id');
            $table->integer('cf_company_id')->nullable();
            $table->mediumText('cf_mobile')->nullable();
            $table->integer('cf_entry_by')->nullable();
            $table->mediumText('cf_call_duration')->nullable();
            $table->mediumText('cf_client_feedback')->nullable();
            $table->date('cf_next_date')->nullable();
            $table->integer('cf_result')->nullable();
            $table->string('cf_client_message')->nullable();
            $table->dateTime('cf_date')->nullable();
            $table->tinyInteger('cf_status')->nullable();
            $table->float('cf_price')->nullable();
            $table->tinyInteger('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_feedbacks');
    }
};

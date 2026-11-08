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
        Schema::create('sds_sms', function (Blueprint $table) {
            $table->bigIncrements('sms_id');
            $table->integer('sms_cf_id')->nullable();
            $table->date('sms_cf_next_date')->nullable();
            $table->string('sms_mobileno1')->nullable();
            $table->string('sms_mobileno2')->nullable();
            $table->string('sms_mobileno3')->nullable();
            $table->text('sms_text')->nullable();
            $table->dateTime('sms_sent_at')->nullable();
            $table->integer('sms_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sds_sms');
    }
};

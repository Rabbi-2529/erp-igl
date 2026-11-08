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
        Schema::create('sds_query_books', function (Blueprint $table) {
            $table->bigIncrements('qb_id');
            $table->Integer('qb_company_id');
            $table->tinyInteger('qb_entry_by');
            $table->mediumInteger('qb_serial');
            $table->string('qb_mobile');
            $table->string('qb_mobile1');
            $table->string('qb_mobile2');
            $table->string('qb_phone');
            $table->string('qb_name');
            $table->string('qb_company_name');
            $table->tinyInteger('qb_find_us');
            $table->string('qb_address');
            $table->tinyInteger('qb_reason');
            $table->string('qb_email');
            $table->tinyInteger('qb_result');
            $table->date('qb_birth_date');
            $table->date('qb_marriage_date');
            $table->dateTime('qb_submit_date');
            $table->tinyInteger('qb_status');
            $table->tinyInteger('qb_feedback');
            $table->tinyInteger('qb_staff_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sds_query_books');
    }
};

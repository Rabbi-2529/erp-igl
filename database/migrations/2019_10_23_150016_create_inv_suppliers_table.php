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
        Schema::create('inv_suppliers', function (Blueprint $table) {
            $table->bigIncrements('inv_sup_id');
            $table->unsignedInteger('inv_sup_com_id')->nullable();
            $table->string('inv_sup_com_name')->nullable();
            $table->string('inv_sup_address')->nullable();
            $table->string('inv_sup_person')->nullable();
            $table->string('inv_sup_mobile')->nullable();
            $table->string('inv_sup_phone')->nullable();
            $table->string('inv_sup_email')->nullable();
            $table->string('inv_sup_website')->nullable();
            $table->string('inv_sup_complain_num')->nullable();
            $table->string('inv_sup_open_due_bal')->nullable();
            $table->tinyInteger('inv_sup_type')->comments('1-local,2=global');
            $table->tinyInteger('inv_sup_status')->nullable()->comments('1=active,0=inactive');
            $table->unsignedInteger('inv_sup_submit_by')->nullable();
            $table->dateTime('inv_sup_submit_at')->nullable();
            $table->unsignedInteger('inv_sup_update_by')->nullable();
            $table->dateTime('inv_sup_update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_suppliers');
    }
};

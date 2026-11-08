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
        Schema::create('mex_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('com_id');
            $table->string('name');
            $table->string('com_name')->nullable();
            $table->string('mobile');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address');
            $table->tinyInteger('type');
            $table->tinyInteger('bal_type');
            $table->string('identity')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('submit_by')->nullable();
            $table->dateTime('submit_at')->nullable();
            $table->unsignedInteger('update_by')->nullable();
            $table->dateTime('update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mex_customers');
    }
};

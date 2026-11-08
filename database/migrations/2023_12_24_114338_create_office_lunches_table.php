<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('office_lunches', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('total_meal');
            $table->dateTime('lunch_in');
            $table->dateTime('lunch_cancel');
            $table->string('user_ip');
            $table->string('notes');
            $table->string('cancel_reason');
            $table->smallInteger('status')->default(1)->comments('1=Employee, 2=Guest');
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
        Schema::dropIfExists('office_lunches');
    }
};

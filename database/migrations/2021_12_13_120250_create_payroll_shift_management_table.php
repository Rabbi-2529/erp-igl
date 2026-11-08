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
        Schema::create('payroll_shift_management', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->string('working_hour');
            $table->unsignedInteger('shift_hour')->comments('in minute');
            $table->unsignedInteger('late_entry')->comments('in minute');
            $table->smallInteger('status')->default(1)->comments('1=active,2=inactive');
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
        Schema::dropIfExists('payroll_shift_management');
    }
};

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
        Schema::create('admin_users', function (Blueprint $table) {
            $table->bigIncrements('au_id');
            $table->integer('au_company_id');
            $table->string('au_company_name');
            $table->string('au_name');
            $table->string('au_user_id');
            $table->string('au_email');
            $table->string('au_mobile');
            $table->string('au_password');
            $table->string('au_address');
            $table->string('au_company_img');
            $table->string('au_company_logo');
            $table->date('au_created_date');
            $table->string('au_expired_date');
            $table->integer('au_status');
            $table->string('au_permission_status')->nullable();
            $table->integer('au_user_type');
            $table->integer('au_team_id');
            $table->mediumText('au_api_key');
            $table->mediumText('au_sender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_users');
    }
};

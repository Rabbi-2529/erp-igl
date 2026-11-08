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
        if (Schema::hasColumn('inv_acc_bank_statements', 'inv_abs_service_charge')) {
            return;
        }

        Schema::table('inv_acc_bank_statements', function (Blueprint $table) {
            $table->decimal('inv_abs_service_charge', 12,2)->default(0)->after('inv_abs_credit')->comment('bank service charge amount (set on the service charge transaction)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inv_acc_bank_statements', function (Blueprint $table) {
            $table->dropColumn('inv_abs_service_charge');
        });
    }
};

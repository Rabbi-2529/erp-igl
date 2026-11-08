<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts Phase 2: cheque register and bank reconciliation ticks (new tables only).
 * Same SQL as database/accounts_phase2.sql (this database is managed by hand).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('acc_cheques') || !Schema::hasTable('acc_bank_recon_marks')) {
            DB::unprepared(file_get_contents(database_path('accounts_phase2.sql')));
        }
    }

    public function down()
    {
        Schema::dropIfExists('acc_bank_recon_marks');
        Schema::dropIfExists('acc_cheques');
    }
};

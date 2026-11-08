<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts Phase 3a: chart of accounts, journal vouchers and their lines (new tables only).
 * Same SQL as database/accounts_phase3a.sql (this database is managed by hand).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('acc_coa') || !Schema::hasTable('acc_journals') || !Schema::hasTable('acc_journal_lines')) {
            DB::unprepared(file_get_contents(database_path('accounts_phase3a.sql')));
        }
    }

    public function down()
    {
        Schema::dropIfExists('acc_journal_lines');
        Schema::dropIfExists('acc_journals');
        Schema::dropIfExists('acc_coa');
    }
};

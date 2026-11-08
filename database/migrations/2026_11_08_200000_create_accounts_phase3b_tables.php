<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts Phase 3b: settings, fiscal year closing, audit trail, voucher approvals and budgets (new tables only).
 * Same SQL as database/accounts_phase3b.sql (this database is managed by hand).
 */
return new class extends Migration
{
    public function up()
    {
        foreach (['acc_settings', 'acc_fiscal_years', 'acc_audit_log', 'acc_journal_approvals', 'acc_budgets'] as $table) {
            if (!Schema::hasTable($table)) {
                DB::unprepared(file_get_contents(database_path('accounts_phase3b.sql')));
                return;
            }
        }
    }

    public function down()
    {
        foreach (['acc_budgets', 'acc_journal_approvals', 'acc_audit_log', 'acc_fiscal_years', 'acc_settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

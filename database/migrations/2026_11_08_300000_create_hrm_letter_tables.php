<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * HRM Letters: letter types, templates, letters, approval history, signatories, letterhead PAD and module roles (new tables only).
 * Same SQL as database/hrm_letters.sql (this database is managed by hand).
 */
return new class extends Migration
{
    const TABLES = ['hrm_letter_types', 'hrm_letter_templates', 'hrm_letters', 'hrm_letter_approvals', 'hrm_letter_signatories', 'hrm_company_letterheads', 'hrm_letter_roles'];

    public function up()
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table)) {
                DB::unprepared(file_get_contents(database_path('hrm_letters.sql')));
                return;
            }
        }
    }

    public function down()
    {
        foreach (array_reverse(self::TABLES) as $table) {
            Schema::dropIfExists($table);
        }
    }
};

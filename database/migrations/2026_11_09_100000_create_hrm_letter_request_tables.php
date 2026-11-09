<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * HRM Letters: employees ask HR for a letter (new tables only).
 * Same SQL as database/hrm_letter_requests.sql (this database is managed by hand).
 */
return new class extends Migration
{
    const TABLES = ['hrm_letter_requests', 'hrm_letter_request_types'];

    public function up()
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table)) {
                DB::unprepared(file_get_contents(database_path('hrm_letter_requests.sql')));
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * HRM: employee exits (new table only).
 * Same SQL as database/hrm_employee_exits.sql (this database is managed by hand).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('hrm_employee_exits')) {
            DB::unprepared(file_get_contents(database_path('hrm_employee_exits.sql')));
        }
    }

    public function down()
    {
        Schema::dropIfExists('hrm_employee_exits');
    }
};

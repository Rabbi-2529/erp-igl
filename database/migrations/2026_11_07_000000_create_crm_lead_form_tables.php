<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CRM automatic lead form: two new tables only (existing CRM tables are not changed).
 * Same SQL as database/crm_lead_form.sql (this database is managed by hand).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('crm_lead_forms') || !Schema::hasTable('crm_lead_submissions')) {
            DB::unprepared(file_get_contents(database_path('crm_lead_form.sql')));
        }
    }

    public function down()
    {
        Schema::dropIfExists('crm_lead_submissions');
        Schema::dropIfExists('crm_lead_forms');
    }
};

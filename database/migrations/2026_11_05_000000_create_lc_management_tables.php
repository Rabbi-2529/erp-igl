<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private $tables = [
        'lc_shipment_items', 'lc_shipments', 'lc_documents', 'lc_costs',
        'lc_lcs', 'lc_file_items', 'lc_files', 'lc_currencies',
    ];

    /**
     * Run the migrations.
     * The schema lives in database/lc_management.sql (this app's databases are managed by hand).
     *
     * @return void
     */
    public function up()
    {
        $sql = file_get_contents(database_path('lc_management.sql'));
        foreach (array_filter(array_map('trim', explode(';', preg_replace('/^--.*$/m', '', $sql)))) as $statement) {
            DB::statement($statement);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};

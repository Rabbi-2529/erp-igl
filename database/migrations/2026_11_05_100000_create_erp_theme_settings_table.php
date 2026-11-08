<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Theme Colors: one row per company with its own colours (JSON). No row = the default FelnaTech theme.
 * The same table is created by database/theme_settings.sql (this database is managed by hand).
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('erp_theme_settings')) {
            return;
        }
        Schema::create('erp_theme_settings', function (Blueprint $table) {
            $table->increments('ets_id');
            $table->unsignedInteger('ets_company_id')->unique('erp_theme_settings_company');
            $table->text('ets_settings')->comment('JSON: primary, accent, sidebar, search, page, text (#rrggbb), heading_font, style (felna|classic)');
            $table->unsignedInteger('ets_updated_by')->nullable();
            $table->dateTime('ets_updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_theme_settings');
    }
};

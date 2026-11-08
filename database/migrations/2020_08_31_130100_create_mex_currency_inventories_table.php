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
        Schema::create('mex_currency_inventories', function (Blueprint $table) {
            $table->bigIncrements('mex_curr_inv_id');
            $table->unsignedInteger('mex_curr_inv_com_id');
            $table->unsignedInteger('mex_curr_inv_country_id');
            $table->unsignedInteger('mex_curr_inv_party_id')->comments('supplier and customer id');
            $table->unsignedInteger('mex_curr_inv_prodet_id')->nullable();
            $table->string('mex_curr_inv_invoice_no');
            $table->decimal('mex_curr_inv_total_qty',8,2);
            $table->decimal('mex_curr_inv_short_qty',8,2)->default('0');
            $table->string('mex_curr_inv_short_remarks')->nullable();
            $table->decimal('mex_curr_inv_qty',8,2)->nullable();
            $table->decimal('mex_curr_inv_unit_price',12,2);
            $table->decimal('mex_curr_inv_debit',12,2);
            $table->decimal('mex_curr_inv_credit',12,2);
            $table->date('mex_curr_inv_issue_date');
            $table->string('mex_curr_inv_barcode')->nullable();
            $table->date('mex_curr_inv_exp_date')->nullable();
            $table->string('mex_curr_inv_tran_desc',255)->nullable();
            $table->tinyInteger('mex_curr_inv_deal_type')->comments('1=supplier, 2=customer');
            $table->tinyInteger('mex_curr_inv_tran_type')->comments('1=buy/sell product, 2=refund buy/sell product, 3=opening-balance/deposit-withdraw balance,4=supplier-payment/customer-payment-collection,5=supplier-payment-collection/customer-payment-refund');
            $table->tinyInteger('mex_curr_inv_confirm')->default(0)->comments('0=not confirm , 1 = confirm');
            $table->tinyInteger('mex_curr_inv_status')->default('1')->comments('1=active 0=inactive');
            $table->unsignedInteger('mex_curr_inv_sell_ref')->nullable();
            $table->tinyInteger('mex_curr_inv_edit_count')->default(0)->comments('0=not edit, 1 =edited');
            $table->dateTime('mex_curr_inv_submit_at')->nullable();
            $table->unsignedInteger('mex_curr_inv_submit_by')->nullable();
            $table->dateTime('mex_curr_inv_update_at')->nullable();
            $table->unsignedInteger('mex_curr_inv_update_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mex_currency_inventories');
    }
};

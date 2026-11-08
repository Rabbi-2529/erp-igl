<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MexCurrencyInventory extends Model
{
    protected $fillable =
    [
        'mex_curr_inv_id',
        'mex_curr_inv_country_id',
        'mex_curr_inv_com_id',
                'mex_curr_inv_branch_id',
        'mex_curr_inv_party_id',
        'mex_curr_inv_prodet_id',
        'mex_curr_inv_invoice_no',
        'mex_curr_inv_total_qty',
        'mex_curr_inv_short_qty',
        'mex_curr_inv_short_remarks',
        'mex_curr_inv_qty',
        'mex_curr_inv_unit_price',
        'mex_curr_inv_service_charges',
        'mex_curr_inv_delivery_charges',
        'mex_curr_inv_debit',
        'mex_curr_inv_credit',
        'mex_curr_inv_issue_date',
        'mex_curr_inv_barcode',
        'mex_curr_inv_exp_date',
        'mex_curr_inv_tran_desc',
        'mex_curr_inv_deal_type',
        'mex_curr_inv_tran_type',
        'mex_curr_inv_confirm',
        'mex_curr_inv_status',
        'mex_curr_inv_sell_ref',
        'mex_curr_inv_edit_count',
        'mex_curr_inv_submit_at',
        'mex_curr_inv_submit_by',
        'mex_curr_inv_update_at',
        'mex_curr_inv_update_by',
    ];
    protected $table = 'mex_currency_inventories';
    protected $primaryKey = 'mex_curr_inv_id';
    public $incrementing = true;
    public $timestamps = false;

    // ============= For  Accounts Query =============
    public function country_currency(){
        return $this->belongsTo('App\Models\MexCountry','mex_curr_inv_country_id','id');
    }
    public function currency_type(){
        return $this->belongsTo('App\Models\MexCurrencyDetail','mex_curr_inv_prodet_id','mex_curr_det_id');
    }
    public function customer_info(){
        return $this->belongsTo('App\Models\MexCustomer','mex_curr_inv_party_id','id');
    }
    public function getSoldByInfo(){
        return $this->belongsTo('App\Models\Admin_user','mex_curr_inv_submit_by','au_id');
    }
    public static function getSupCreditByID($party_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_status', 1)
            ->sum('inv_pro_inv_credit');
    }
    public static function getCusCreditByID($party_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type',2)
            ->sum('inv_pro_inv_credit');
    }




    public static function getSupDebitByID($party_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_status', 1)->sum('inv_pro_inv_debit');
    }
    public static function getCusDebitByID($party_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_deal_type',2)
            ->where('inv_pro_inv_status', 1)->sum('inv_pro_inv_debit');
    }





    public static function getSupBalanceByID($party_id)
    {
        return (Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type', 1)
            ->sum('inv_pro_inv_credit'))-
        (Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type', 1)
            ->sum('inv_pro_inv_debit'));
    }
    public static function getCusBalanceByID($party_id)
    {
        return (Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type',2)
            ->sum('inv_pro_inv_credit'))-
        (Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type',2)
            ->sum('inv_pro_inv_debit'));
    }






    public static function getCreditForLedgerByInvoice($invoiceNo)
    {
        return Inv_product_inventory::where('inv_pro_inv_invoice_no',$invoiceNo)->where('inv_pro_inv_deal_type',1)->where('inv_pro_inv_status',1)->sum('inv_pro_inv_credit');
    }
    public static function getDebitForLedgerByInvoice($invoiceNo)
    {
        return Inv_product_inventory::where('inv_pro_inv_invoice_no',$invoiceNo)->where('inv_pro_inv_deal_type',1)->where('inv_pro_inv_status',1)->sum('inv_pro_inv_debit');
    }
    public static function getRunningBalanceByDate($lastDate,$supplier_id)
    {
        return (Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)->where('inv_pro_inv_deal_type',1)->where('inv_pro_inv_status',1)->where('inv_pro_inv_issue_date','<=',$lastDate)->sum('inv_pro_inv_credit'))-(Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)->where('inv_pro_inv_deal_type',1)->where('inv_pro_inv_status',1)->where('inv_pro_inv_issue_date','<=',$lastDate)->sum('inv_pro_inv_debit'));
    }
    public static function getOpenningBalance($startDate,$supplier_id)
    {
        return (Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)->where('inv_pro_inv_deal_type',1)->where('inv_pro_inv_status',1)->where('inv_pro_inv_issue_date','<',$startDate)->sum('inv_pro_inv_credit'))-(Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)->where('inv_pro_inv_deal_type',1)->where('inv_pro_inv_status',1)->where('inv_pro_inv_issue_date','<',$startDate)->sum('inv_pro_inv_debit'));
    }

    public static function getCreditByInvoiceNo($invoiceNo)
    {
       return Inv_product_inventory::where('inv_pro_inv_invoice_no',$invoiceNo)->sum('inv_pro_inv_credit');
    }
    public static function getDebitByInvoiceNo($invoiceNo)
    {
       return Inv_product_inventory::where('inv_pro_inv_invoice_no',$invoiceNo)->sum('inv_pro_inv_debit');
    }
    public static function getTotalDiscountBySupplierID($supplier_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)->where('inv_pro_inv_status',1)->where('inv_pro_inv_tran_type',6)->where('inv_pro_inv_deal_type',1)->sum('inv_pro_inv_debit');

    }
    public static function getTotalDiscountByCustomerID($customer_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id',$customer_id)->where('inv_pro_inv_status',1)->where('inv_pro_inv_tran_type',6)->where('inv_pro_inv_deal_type',2)->sum('inv_pro_inv_credit');
    }
    public static function getIssueDateByInvoice($invoiceNo)
    {
        return Inv_product_inventory::where('inv_pro_inv_invoice_no',$invoiceNo)->first();
    }
    public function getProductInfo()
    {
        return $this->belongsTo('App\Inv_product_detail','inv_pro_inv_prodet_id','inv_pro_det_id');
    }

    public function getCustomerInfo(){
        return $this->belongsTo('App\Inv_customer','inv_pro_inv_party_id','inv_cus_id');
    }

    public static function getInvoice($partyID){
        $com = Auth::user()->au_company_id;
        return $invoice = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                               ->where('inv_pro_inv_party_id',$partyID)
                                               ->first();
    }


    public function getSupplierInfo(){
        return $this->belongsTo('App\Inv_supplier','inv_pro_inv_party_id','inv_sup_id');
    }

    public function getProductWarranty(){
        return $this->belongsTo('App\Inv_product_detail','inv_pro_inv_prodet_id','inv_pro_det_id');
    }

    public static function getTotalDebit($invoiceId){
        $com = Auth::user()->au_company_id;
        $total = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                            ->where('inv_pro_inv_invoice_no',$invoiceId)
                                            ->sum('inv_pro_inv_debit');
        return $total;
    }

    public static function getTotalCredit($invoiceId){
        $com = Auth::user()->au_company_id;
        $total = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                            ->where('inv_pro_inv_invoice_no',$invoiceId)
                                            ->sum('inv_pro_inv_credit');
        return $total;
    }

    public static function ProductSerial($pro_inv_id){
        return Inv_product_inventory_detail::where('inv_pro_invdet_proinv_id', $pro_inv_id)->pluck('inv_pro_invdet_slno')->toArray();
    }

    public static function ProductSerialSell($pro_inv_id){
        return Inv_product_inventory_detail::where('inv_pro_invdet_proinv_sell_id', $pro_inv_id)->pluck('inv_pro_invdet_slno')->toArray();
    }


    public static function product_buy_return_available_qty($pro_inv_id) {
        $inv = Inv_product_inventory::where('inv_pro_inv_id', $pro_inv_id)
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_tran_type', 1)
            ->first();
        $return_qty = Inv_product_inventory::where('inv_pro_inv_sell_ref', $pro_inv_id)
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_tran_type', 2)
            ->where('inv_pro_inv_prodet_id', $inv->inv_pro_inv_prodet_id)
            ->sum('inv_pro_inv_qty');
        $available_return_qty = $inv->inv_pro_inv_qty - $return_qty;
        return $available_return_qty;
    }


    public static function product_sell_return_available_qty($pro_inv_id) {
        $inv = Inv_product_inventory::where('inv_pro_inv_id', $pro_inv_id)
            ->where('inv_pro_inv_deal_type', 2)
            ->where('inv_pro_inv_tran_type', 1)
            ->first();
        $return_qty = Inv_product_inventory::where('inv_pro_inv_sell_ref', $pro_inv_id)
            ->where('inv_pro_inv_deal_type', 2)
            ->where('inv_pro_inv_tran_type', 2)
            ->where('inv_pro_inv_prodet_id', $inv->inv_pro_inv_prodet_id)
            ->sum('inv_pro_inv_qty');
        $available_return_qty = $inv->inv_pro_inv_qty - $return_qty;
        return $available_return_qty;
    }

    public static function profitLoss($pro_id){
        $pro_loss = Inv_product_inventory::where('inv_pro_inv_prodet_id',$pro_id)
                                            ->where('inv_pro_inv_deal_type',2)
                                            ->where('inv_pro_inv_tran_type',2)
                                            ->first();

                                            return $pro_loss;
    }

    public static function get_discount_amount($inv_id){
        $discount = Inv_product_inventory::where('inv_pro_inv_invoice_no',$inv_id)
                                                ->where('inv_pro_inv_deal_type',2)
                                                ->where('inv_pro_inv_tran_type',12)
                                                ->first();
        if (!empty($discount)) {
            $discount_amount = $discount->inv_pro_inv_credit;
        }else {
            $discount_amount = 0;
        }
        return $discount_amount;
    }
}

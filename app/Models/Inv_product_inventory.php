<?php

namespace App\Models;
use Auth;
use DB;

use Illuminate\Database\Eloquent\Model;

class Inv_product_inventory extends Model
{
    //
    protected $fillable =
    [
        'inv_pro_inv_id',
        'inv_pro_inv_com_id',
        'inv_pro_inv_branch_id',
        'inv_pro_inv_party_id',
        'inv_pro_inv_project_id',
        'inv_pro_inv_invoice_no',
        'inv_pro_inv_total_qty',
        'inv_pro_inv_short_qty',
        'inv_pro_inv_short_remarks',
        'inv_pro_inv_qty',
        'inv_pro_inv_unit_price',
        'inv_pro_inv_service_charges',
        'inv_pro_inv_delivery_charges',
        'inv_pro_inv_debit',
        'inv_pro_inv_credit',
        'inv_pro_inv_issue_date',
        'inv_pro_inv_barcode',
        'inv_pro_inv_exp_date',
        'inv_pro_inv_tran_desc',
        'inv_pro_inv_deal_type',
        'inv_pro_inv_tran_type',
        'inv_pro_inv_confirm',
        'inv_pro_inv_status',
        'inv_pro_inv_sell_ref',
        'inv_pro_inv_edit_count',
        'inv_pro_inv_submit_at',
        'inv_pro_inv_submit_by',
        'inv_pro_inv_update_at',
        'inv_pro_inv_update_by',
    ];
    protected $table = 'inv_product_inventories';
    protected $primaryKey = 'inv_pro_inv_id';
    public $incrementing = true;
    public $timestamps = false;
    
    public function product_detail() {
        return $this->belongsTo('App\Models\Inv_product_detail', 'inv_pro_inv_project_id', 'inv_pro_det_id');
    }

    public static function projectAmount($proId){
        $amount = Inv_product_inventory::where('inv_pro_inv_project_id',$proId)
//                                       ->where('inv_pro_inv_deal_type',3)
//                                       ->where('inv_pro_inv_tran_type',15)
                                       ->first();

        if (isset($amount->inv_pro_inv_debit)) {
            return $amount->inv_pro_inv_debit;
        }else {
            return $amount = '';
        }

    }

    public static function projectRestAmount($proId){
        $amount = Inv_product_inventory::select(DB::raw('sum(inv_pro_inv_credit - inv_pro_inv_debit) as total'))
                                       ->where('inv_pro_inv_project_id',$proId)
                                       ->where('inv_pro_inv_deal_type',3)
                                       ->whereIn('inv_pro_inv_tran_type',[15,16,17,18,19])
                                       ->get();
        foreach ($amount as $value) {
            $total = $value->total;
        }
        return $total;
    }

    // ============= For Accounts Query =============
    
    /**
     * Get Supplier Credit by ID with Branch Filter
     */
    public static function getSupCreditByID($party_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADDED BRANCH ID
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_status', 1)
            ->sum('inv_pro_inv_credit');
    }
    
    /**
     * Get Customer Credit by ID with Branch Filter
     */


    /**
     * Get Supplier Debit by ID with Branch Filter
     */
    public static function getSupDebitByID($party_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADDED BRANCH ID
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_status', 1)
            ->sum('inv_pro_inv_debit');
    }
    
    /**
     * Get Customer Debit by ID with Branch Filter
     */
public static function getCusDebitByID($party_id)
{
    return Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
        ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
        ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADD THIS
        ->where('inv_pro_inv_isproject', 0) // ADD THIS
        ->where('inv_pro_inv_deal_type', 2)
        ->where('inv_pro_inv_status', 1)
        ->sum('inv_pro_inv_debit');
}

public static function getCusCreditByID($party_id)
{
    return Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
        ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
        ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADD THIS
        ->where('inv_pro_inv_isproject', 0) // ADD THIS
        ->where('inv_pro_inv_deal_type', 2)
        ->where('inv_pro_inv_status', 1)
        ->sum('inv_pro_inv_credit');
}

    /**
     * Get Supplier Balance by ID with Branch Filter
     */
    public static function getSupBalanceByID($party_id)
    {
        return (Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADDED BRANCH ID
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type', 1)
            ->sum('inv_pro_inv_credit')) -
        (Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADDED BRANCH ID
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type', 1)
            ->sum('inv_pro_inv_debit'));
    }
    
    /**
     * Get Customer Balance by ID with Branch Filter
     */
    public static function getCusBalanceByID($party_id)
    {
        return (Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADDED BRANCH ID
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type',2)
            ->sum('inv_pro_inv_credit')) -
        (Inv_product_inventory::where('inv_pro_inv_party_id', $party_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADDED BRANCH ID
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type',2)
            ->sum('inv_pro_inv_debit'));
    }

    public static function getCreditForLedgerByInvoice($invoiceNo)
    {
        return Inv_product_inventory::where('inv_pro_inv_invoice_no',$invoiceNo)
               ->where('inv_pro_inv_deal_type',1)
               ->where('inv_pro_inv_status',1)
               ->sum('inv_pro_inv_credit');
    }
    
    public static function getDebitForLedgerByInvoice($invoiceNo)
    {
        return Inv_product_inventory::where('inv_pro_inv_invoice_no',$invoiceNo)
               ->where('inv_pro_inv_deal_type',1)
               ->where('inv_pro_inv_status',1)
               ->sum('inv_pro_inv_debit');
    }
    
    public static function getRunningBalanceByDate($lastDate,$supplier_id)
    {
        return (Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)
            ->where('inv_pro_inv_deal_type',1)
            ->where('inv_pro_inv_status',1)
            ->where('inv_pro_inv_issue_date','<=',$lastDate)
            ->sum('inv_pro_inv_credit')) -
        (Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)
            ->where('inv_pro_inv_deal_type',1)
            ->where('inv_pro_inv_status',1)
            ->where('inv_pro_inv_issue_date','<=',$lastDate)
            ->sum('inv_pro_inv_debit'));
    }
    
    public static function getOpenningBalance($startDate,$supplier_id)
    {
        return (Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)
            ->where('inv_pro_inv_deal_type',1)
            ->where('inv_pro_inv_status',1)
            ->where('inv_pro_inv_issue_date','<',$startDate)
            ->sum('inv_pro_inv_credit')) -
        (Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)
            ->where('inv_pro_inv_deal_type',1)
            ->where('inv_pro_inv_status',1)
            ->where('inv_pro_inv_issue_date','<',$startDate)
            ->sum('inv_pro_inv_debit'));
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
        return Inv_product_inventory::where('inv_pro_inv_party_id',$supplier_id)
               ->where('inv_pro_inv_status',1)
               ->where('inv_pro_inv_tran_type',6)
               ->where('inv_pro_inv_deal_type',1)
               ->sum('inv_pro_inv_debit');
    }
    
    public static function getTotalDiscountByCustomerID($customer_id)
    {
        return Inv_product_inventory::where('inv_pro_inv_party_id',$customer_id)
               ->where('inv_pro_inv_status',1)
               ->where('inv_pro_inv_tran_type',6)
               ->where('inv_pro_inv_deal_type',2)
               ->sum('inv_pro_inv_credit');
    }
    
    public static function getIssueDateByInvoice($invoiceNo)
    {
        return Inv_product_inventory::where('inv_pro_inv_invoice_no',$invoiceNo)->first();
    }
    
    public function getProductInfo()
    {
        return $this->belongsTo('App\Models\Inv_product_detail','inv_pro_inv_project_id','inv_pro_det_id');
    }

    public function getCustomerInfo(){
        return $this->belongsTo('App\Models\Inv_customer','inv_pro_inv_party_id','inv_cus_id');
    }

    public static function getInvoice($partyID){
        $com = Auth::user()->au_company_id;
        return $invoice = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                               ->where('inv_pro_inv_party_id',$partyID)
                                               ->first();
    }

    public function getSoldByInfo(){
        return $this->belongsTo('App\Models\Admin_user','inv_pro_inv_submit_by','au_id');
    }

    public function getSupplierInfo(){
        return $this->belongsTo('App\Models\Inv_supplier','inv_pro_inv_party_id','inv_sup_id');
    }

    public function getProductWarranty(){
        return $this->belongsTo('App\Models\Inv_product_detail','inv_pro_inv_project_id','inv_pro_det_id');
    }
    
    public function getProductWarrantydetails(){
        return $this->belongsTo('App\Models\Inv_product','inv_pro_inv_project_id','inv_pro_id');
    }
    
    public function getActualProduct(){
        return $this->hasOneThrough(
            'App\Models\Inv_product',
            'App\Models\Inv_product_detail',
            'inv_pro_det_id',
            'inv_pro_id',
            'inv_pro_inv_project_id',
            'inv_pro_det_pro_id'
        );
    }
    
    public function users(){
        return $this->belongsTo('App\Models\Admin_user','inv_employee_id');
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
            ->where('inv_pro_inv_project_id', $inv->inv_pro_inv_project_id)
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
            ->where('inv_pro_inv_project_id', $inv->inv_pro_inv_project_id)
            ->sum('inv_pro_inv_qty');
        $available_return_qty = $inv->inv_pro_inv_qty - $return_qty;
        return $available_return_qty;
    }

    public static function profitLoss($pro_id){
        $pro_loss = Inv_product_inventory::where('inv_pro_inv_project_id',$pro_id)
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

    public static function buyDate($invId){
        $com = Auth::user()->au_company_id;
        $buyDate = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                        ->where('inv_pro_inv_id',$invId)
                                        ->where('inv_pro_inv_deal_type',1)
                                        ->where('inv_pro_inv_tran_type',1)
                                        ->first();
        return $buyDate->inv_pro_inv_issue_date;
    }
}
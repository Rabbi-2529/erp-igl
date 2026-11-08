<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Inv_customer_inventory extends Model
{
    //
    protected $fillable =
    [
        'inv_cus_inv_id',
        'inv_cus_inv_com_id',
                'inv_cus_inv_branch_id',
        'inv_cus_inv_cus_id',
        'inv_cus_inv_proinv_memo_no',
        'inv_cus_inv_debit',
        'inv_cus_inv_credit',
        'inv_cus_inv_tran_type',
        'inv_cus_inv_description',
        'inv_cus_inv_issue_date',
        'inv_cus_inv_status',
        'inv_cus_inv_submit_by',
        'inv_cus_inv_submit_at',
        'inv_cus_inv_update_by',
        'inv_cus_inv_update_at',
    ];
    protected $primaryKey = 'inv_cus_inv_id';
    protected $table = 'inv_customer_inventories';
    public $incrementing = true;
    public $timestamps = false;

    public function getCustomerInfo()
    {
       return $this->belongsTo('App\Models\Inv_customer','inv_cus_inv_cus_id','inv_cus_id');
    }
    public static function getCustomerCreditByID($customer_id)
    {
        return Inv_customer_inventory::where('inv_cus_inv_cus_id', $customer_id)
            ->where('inv_cus_inv_com_id', Auth::user()->au_company_id)
               ->where('inv_cus_inv_branch_id', Auth::user()->au_branch_id)
            ->where('inv_cus_inv_status', 1)
            ->sum('inv_cus_inv_credit');
    }
    public static function getCustomerDebitByID($customer_id)
    {
        return Inv_customer_inventory::where('inv_cus_inv_cus_id', $customer_id)
            ->where('inv_cus_inv_com_id', Auth::user()->au_company_id)
              ->where('inv_cus_inv_branch_id', Auth::user()->au_branch_id)
            ->where('inv_cus_inv_status', 1)
            ->sum('inv_cus_inv_debit');
    }
    public static function getCustomerBalanceByID($customer_id)
    {
        return (Inv_customer_inventory::where('inv_cus_inv_cus_id', $customer_id)
            ->where('inv_cus_inv_com_id', Auth::user()->au_company_id)
              ->where('inv_cus_inv_branch_id', Auth::user()->au_branch_id)
            ->where('inv_cus_inv_status', 1)
            ->sum('inv_cus_inv_credit'))-
        (Inv_customer_inventory::where('inv_cus_inv_cus_id', $customer_id)
            ->where('inv_cus_inv_com_id', Auth::user()->au_company_id)
              ->where('inv_cus_inv_branch_id', Auth::user()->au_branch_id)
            ->where('inv_cus_inv_status', 1)
            ->sum('inv_cus_inv_debit'));
    }
}

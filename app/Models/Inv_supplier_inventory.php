<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Inv_supplier_inventory extends Model
{
    //
    protected $fillable =
    [
        'inv_sup_inv_id',
        'inv_sup_inv_com_id',
          'inv_sup_inv_branch_id',
        'inv_sup_inv_sup_id',
        'inv_sup_inv_proinv_memo_no',
        'inv_sup_inv_debit',
        'inv_sup_inv_credit',
        'inv_sup_inv_tran_type',
        'inv_sup_inv_description',
        'inv_sup_inv_issue_date',
        'inv_sup_inv_status',
        'inv_sup_inv_submit_by',
        'inv_sup_inv_submit_at',
        'inv_sup_inv_update_by',
        'inv_sup_inv_update_at',
    ];
    protected $table = 'inv_supplier_inventories';
    protected $primaryKey = 'inv_sup_inv_id';
    public $incrementing = true;
    public $timestamps = false;


    public static function getsupplierCreditByID($supplier_id)
    {
        return Inv_supplier_inventory::where('inv_sup_inv_sup_id', $supplier_id)
            ->where('inv_sup_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_sup_inv_status', 1)
            ->sum('inv_sup_inv_credit');
    }
    public static function getsupplierDebitByID($supplier_id)
    {
        return Inv_supplier_inventory::where('inv_sup_inv_sup_id', $supplier_id)
            ->where('inv_sup_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_sup_inv_status', 1)
            ->sum('inv_sup_inv_debit');
    }
    public static function getsupplierBalanceByID($supplier_id)
    {
        return (Inv_supplier_inventory::where('inv_sup_inv_sup_id', $supplier_id)
            ->where('inv_sup_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_sup_inv_status', 1)
            ->sum('inv_sup_inv_credit'))-
        (Inv_supplier_inventory::where('inv_sup_inv_sup_id', $supplier_id)
            ->where('inv_sup_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_sup_inv_status', 1)
            ->sum('inv_sup_inv_debit'));
    }
}

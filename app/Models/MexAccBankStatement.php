<?php

namespace App\Models;
use DB;
use Auth;

use Illuminate\Database\Eloquent\Model;

class MexAccBankStatement extends Model
{
    protected $fillable =
    [
    	'mex_abs_id',
    	'mex_abs_company_id',
        	'mex_abs_branch_id',
    	'mex_abs_inventory_id',
    	'mex_abs_reference_id',
    	'mex_abs_reference_type',
    	'mex_abs_bank_id',
    	'mex_abs_debit',
    	'mex_abs_credit',
    	'mex_abs_transaction_date',
    	'mex_abs_voucher_no',
    	'mex_abs_description',
    	'mex_abs_contra_transaction_id',
    	'mex_abs_status',
    	'mex_abs_submit_by',
    	'mex_abs_submit_at',
    	'mex_abs_updated_by',
    	'mex_abs_updated_at'
    	
    ];
    protected $dates = [
        'inv_abs_transaction_date',
    ];

    protected $table = 'mex_acc_bank_statements';
    protected $primaryKey = 'mex_abs_id';
    public $incrementing = true;
    public $timestamps = false;

    public static function get_Bank_Name_By_Bank_ID($id)
    {
        $acc_bank= Inv_acc_bank_info::where('inv_abi_id' , $id)->first();
        $acc_bank_id = @$acc_bank->inv_abi_bank_id;
      // dd($acc_bank_id);
        if($acc_bank_id != '') {
           return Inv_bank::where('id',$acc_bank_id)->first();
       } else {
        return "";
       }
    }
    public static function get_Bank_Name_By_Refer_ID($id)
    {
        $acc_bank_id=DB::table('mex_acc_bank_infos')->where('mex_abi_id',$id)->pluck('mex_abi_bank_id');
      return DB::table('inv_banks')->select('bank_name')->where('id',$acc_bank_id)->first();
       
    }
    public  static function getCashBankIdByCompanyID()
    {
        return MexAccBankInfo::where('mex_abi_company_id',Auth::user()->au_company_id)->where('mex_abi_account_type',2)->first()->inv_abi_id;
    }
    public  static function getAvailableCashBalanceByCompanyID()
    {
        $cashBankid=MexAccBankStatement::getCashBankIdByCompanyID();

        $creditBalance= MexAccBankStatement::where('mex_abs_company_id',Auth::user()->au_company_id)->where('mex_abs_status',1)->where('inv_abs_bank_id',$cashBankid)->sum('inv_abs_credit');
        $debitBalance=MexAccBankStatement::where('mex_abs_company_id',Auth::user()->au_company_id)->where('mex_abs_status',1)->where('inv_abs_bank_id',$cashBankid)->sum('inv_abs_debit');
        return ($creditBalance-$debitBalance);
    }
    //================ 16-11-19 ======================

    public static function getTotalExpensesByCategory($exCatId){
        $expensesId=Inv_acc_expense::getTotalExpensesIdByCategoryId($exCatId);
        //dd($expensesId);
        $expenses=MexAccBankStatement::whereIn('mex_abs_reference_id',$expensesId)
                                        ->where('mex_abs_company_id',Auth::user()->au_company_id)
                                        ->where('mex_abs_status',1)
                                        ->whereIn('mex_abs_reference_type',[3,4])
                                        // ->orWhere('inv_abs_reference_type',4)
                                        ->sum('mex_abs_debit');
        return $expenses;
    }

    public static function getTotalExpensesByExpenses($expId)
    {
        $expenses=Inv_acc_bank_statement::where('inv_abs_reference_id',$expId)
                                        ->where('inv_abs_status',1)
                                        ->whereIn('inv_abs_reference_type',[3,4])
                                        // ->orWhere('inv_abs_reference_type',4)
                                        ->sum('inv_abs_debit');
        return $expenses;
    }
    public static function getAvailableBalanceByBankId($bankId)
    {
        $creditBalance=MexAccBankStatement::where('mex_abs_bank_id',$bankId)->where('mex_abs_status',1)->sum('mex_abs_credit');
        $debitBalance=MexAccBankStatement::where('mex_abs_bank_id',$bankId)->where('mex_abs_status',1)->sum('mex_abs_debit');
        return ($creditBalance-$debitBalance);
    }

    // =========18-11-19 =================
    public static function getTotalCreditByBankId($bankId)
    {
        return MexAccBankStatement::where('mex_abs_bank_id',$bankId)->where('mex_abs_status',1)->where('mex_abs_company_id',Auth::user()->au_company_id)->sum('mex_abs_credit');
    }
    public static function getTotalDebitByBankId($bankId)
    {
        return MexAccBankStatement::where('mex_abs_bank_id',$bankId)->where('mex_abs_status',1)->where('mex_abs_company_id',Auth::user()->au_company_id)->sum('mex_abs_debit');
    }
    public static function getOpenningBalance($start_date)
    {

        $creditBalance=Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)->where('inv_abs_status',1)->where('inv_abs_transaction_date','<=',$start_date)->sum('inv_abs_credit');
        $debitBalance=Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)->where('inv_abs_status',1)->where('inv_abs_transaction_date','<=',$start_date)->sum('inv_abs_debit');
        return ($creditBalance-$debitBalance);
        
    }
}

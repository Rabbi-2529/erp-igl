<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class Inv_acc_bank_statement extends Model
{
    protected $fillable =
    [
    	'inv_abs_id',
    	'inv_abs_company_id',
            	'inv_abs_branch_id',
    	'inv_abs_inventory_id',
    	'inv_abs_reference_id',
    	'inv_abs_reference_type',
    	'inv_abs_bank_id',
    	'inv_abs_debit',
    	'inv_abs_credit',
    	'inv_abs_service_charge',
    	'inv_abs_transaction_date',
    	'inv_abs_voucher_no',
    	'inv_abs_description',
    	'inv_abs_contra_transaction_id',
    	'inv_abs_status',
    	'inv_abs_submit_by',
    	'inv_abs_submit_at',
    	'inv_abs_updated_by',
    	'inv_abs_updated_at'

    ];
    protected $dates = [
        'inv_abs_transaction_date',
    ];

    protected $table = 'inv_acc_bank_statements';
    protected $primaryKey = 'inv_abs_id';
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
        $acc_bank_id=DB::table('inv_acc_bank_infos')->where('inv_abi_id',$id)->pluck('inv_abi_bank_id');
      return DB::table('inv_banks')->select('bank_name')->where('id',$acc_bank_id)->first();

    }
    public  static function getCashBankIdByCompanyID()
    {
        return Inv_acc_bank_info::where('inv_abi_company_id',Auth::user()->au_company_id)->where('inv_abi_branch_id',Auth::user()->au_branch_id)->where('inv_abi_account_type',2)->orderBy('inv_abi_id')->first()->inv_abi_id;
    }
    public  static function getAvailableCashBalanceByCompanyID()
    {
        $cashBankid=Inv_acc_bank_statement::getCashBankIdByCompanyID();

        $creditBalance= Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)->where('inv_abs_branch_id',Auth::user()->au_branch_id)->where('inv_abs_status',1)->where('inv_abs_bank_id',$cashBankid)->sum('inv_abs_credit');
        $debitBalance=Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)->where('inv_abs_branch_id',Auth::user()->au_branch_id)->where('inv_abs_status',1)->where('inv_abs_bank_id',$cashBankid)->sum('inv_abs_debit');
        return ($creditBalance-$debitBalance);
    }
    //================ 16-11-19 ======================

    public static function getTotalExpensesByCategory($exCatId){
        $expensesId=Inv_acc_expense::getTotalExpensesIdByCategoryId($exCatId);
        //dd($expensesId);
        $expenses=Inv_acc_bank_statement::whereIn('inv_abs_reference_id',$expensesId)
                                        ->where('inv_abs_company_id',Auth::user()->au_company_id)
                                           ->where('inv_abs_branch_id',Auth::user()->au_branch_id)
                                        ->where('inv_abs_status',1)
                                        ->whereIn('inv_abs_reference_type',[3,4])
                                        // ->orWhere('inv_abs_reference_type',4)
                                        ->sum('inv_abs_debit');
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
    $creditBalance = Inv_acc_bank_statement::where('inv_abs_bank_id', $bankId)
                    ->where('inv_abs_company_id', Auth::user()->au_company_id)
                    ->where('inv_abs_branch_id', Auth::user()->au_branch_id)
                    ->where('inv_abs_status', 1)
                    ->sum('inv_abs_credit');
                    
    $debitBalance = Inv_acc_bank_statement::where('inv_abs_bank_id', $bankId)
                    ->where('inv_abs_company_id', Auth::user()->au_company_id)
                    ->where('inv_abs_branch_id', Auth::user()->au_branch_id)
                    ->where('inv_abs_status', 1)
                    ->sum('inv_abs_debit');
                    
    return ($creditBalance - $debitBalance);
}
    // =========18-11-19 =================
    public static function getTotalCreditByBankId($bankId)
    {
        return Inv_acc_bank_statement::where('inv_abs_bank_id',$bankId)->where('inv_abs_status',1)->where('inv_abs_company_id',Auth::user()->au_company_id)->where('inv_abs_branch_id',Auth::user()->au_branch_id)->sum('inv_abs_credit');
    }
    public static function getTotalDebitByBankId($bankId)
    {
        return Inv_acc_bank_statement::where('inv_abs_bank_id',$bankId)->where('inv_abs_status',1)->where('inv_abs_company_id',Auth::user()->au_company_id)->where('inv_abs_branch_id',Auth::user()->au_branch_id)->sum('inv_abs_debit');
    }
    public static function getOpenningBalance($start_date)
    {
        // Balance before the start date; the start date's own transactions are listed in the statement
        $creditBalance=Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)->where('inv_abs_branch_id',Auth::user()->au_branch_id)->where('inv_abs_status',1)->where('inv_abs_transaction_date','<',$start_date)->sum('inv_abs_credit');
        $debitBalance=Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)->where('inv_abs_branch_id',Auth::user()->au_branch_id)->where('inv_abs_status',1)->where('inv_abs_transaction_date','<',$start_date)->sum('inv_abs_debit');
        return ($creditBalance-$debitBalance);

    }

    /**
     * Readable transaction type of a statement row.
     * Codes 7 and 8 are shared, so money in / out decides the label.
     */
    public static function getReferenceTypeLabel($statement)
    {
        $moneyIn = $statement->inv_abs_credit > 0;

        switch ((int) $statement->inv_abs_reference_type) {
            case 1:  return 'Customer Transaction';
            case 2:  return 'Supplier Transaction';
            case 3:
            case 4:
            case 9:  return 'Expenses';
            case 5:  return 'Contra Transaction';
            case 6:  return 'Bank Deposit';
            case 7:  return $moneyIn ? 'Loan Received' : 'Bank Withdraw';
            case 8:  return $moneyIn ? 'Income' : 'Loan Payment';
            case 10: return 'OMS Bill Payment';
            case 11: return 'Bank Service Charge';
            case 12: return 'Loan Transfer';
            case 13: return 'LC Payment';
            case 14: return $moneyIn ? 'Salary Refund' : 'Salary Payment';
            case 15: return $moneyIn ? 'VAT Refund' : 'VAT Payment';
            default: return 'Other';
        }
    }

}

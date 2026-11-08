<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MexAccBankInfo extends Model
{
    protected $fillable =
    [
    	'mex_abi_id',
    	'mex_abi_company_id',
        	'mex_abi_branch_id',
    	'mex_abi_bank_id',
    	'mex_abi_branch_name',
    	'mex_abi_account_name',
    	'mex_abi_account_no',
    	'mex_abi_open_date',
    	'mex_abi_account_type',
    	'mex_abi_status',
    	'mex_abi_submit_by',
    	'mex_abi_submit_at',
    	'mex_abi_updated_by',
    	'mex_abi_updated_at'
    ];

    protected $table = 'mex_acc_bank_infos';
    protected $primaryKey = 'mex_abi_id';
    public $incrementing = true;
    public $timestamps = false;


    public function bank_info()
    {
    	return $this->belongsTo('App\Models\Inv_bank', 'mex_abi_bank_id', 'id');
    }
    public static function singleBankTotalBalance($bank_id)
    {
         $total_credit = Inv_acc_bank_statement::where('inv_abs_bank_id', $bank_id)->sum('inv_abs_credit');
        $total_debit = Inv_acc_bank_statement::where('inv_abs_bank_id', $bank_id)->sum('inv_abs_debit');
        $balance = $total_credit - $total_debit;
        return $balance;
    }
}

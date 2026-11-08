<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_acc_bank_info extends Model
{
	protected $fillable =
    [
    	'inv_abi_id',
    	'inv_abi_company_id',
        	'inv_abi_branch_id',
    	'inv_abi_bank_id',
    	'inv_abi_branch_name',
    	'inv_abi_account_name',
    	'inv_abi_account_no',
    	'inv_abi_open_date',
    	'inv_abi_account_type',
    	'inv_abi_status',
    	'inv_abi_submit_by',
    	'inv_abi_submit_at',
    	'inv_abi_updated_by',
    	'inv_abi_updated_at'
    ];

    protected $table = 'inv_acc_bank_infos';
    protected $primaryKey = 'inv_abi_id';
    public $incrementing = true;
    public $timestamps = false;


    public function bank_info()
    {
    	return $this->belongsTo('App\Models\Inv_bank', 'inv_abi_bank_id', 'id');
    }
    public static function singleBankTotalBalance($bank_id)
    {
         $total_credit = Inv_acc_bank_statement::where('inv_abs_bank_id', $bank_id)->sum('inv_abs_credit');
        $total_debit = Inv_acc_bank_statement::where('inv_abs_bank_id', $bank_id)->sum('inv_abs_debit');
        $balance = $total_credit - $total_debit;
        return $balance;
    }
}

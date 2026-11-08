<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Inv_acc_expense extends Model
{
	protected $table = 'inv_acc_expenses';
   // protected $primaryKey = 'inv_abi_id';
    public $incrementing = true;
    public $timestamps = false;

    public function getCategoryByCatID()
    {
    	return $this->belongsTo('App\Models\Inv_acc_expense_category','inv_acc_exp_category_id','inv_acc_exp_cat_category_id');
    }

    //===============16-11-19 ===============
    public static function getTotalExpensesIdByCategoryId($exCatId)
    {
    	return Inv_acc_expense::where('inv_acc_exp_category_id',$exCatId)->where('inv_acc_exp_company_id',Auth::user()->au_company_id)->where('inv_acc_exp_branch_id',Auth::user()->au_branch_id)->where('inv_acc_exp_status',1)->pluck('inv_acc_exp_id')->toArray();
    }
    public static function getExpenseNameById($expId)
    {

        return Inv_acc_expense::where('inv_acc_exp_id',$expId)->first()->inv_acc_exp_expense_name;
    }

}

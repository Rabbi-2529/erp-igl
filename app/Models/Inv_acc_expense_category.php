<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_acc_expense_category extends Model
{

    protected $table = 'inv_acc_expense_categories';
    protected $primaryKey = 'inv_acc_exp_cat_category_id';
    public $incrementing = true;
    public $timestamps = false;


    //============== 16-11-19 ===========

    public static function getCategoryNameById($cat_id)
    {
    	return Inv_acc_expense_category::where('inv_acc_exp_cat_category_id',$cat_id)->first()->inv_acc_exp_cat_category_name;
    }
}

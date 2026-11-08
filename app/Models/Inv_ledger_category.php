<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_ledger_category extends Model
{
    protected $fillable =
    [
        'inv_ledg_cat_cat_id',
        'inv_ledg_cat_company_id',
            'inv_ledg_cat_branch_id',
        'inv_ledg_cat_category_name',
        'inv_ledg_cat_status',
        'inv_ledg_cat_created_by',
        'inv_ledg_cat_created_at',
        'inv_ledg_cat_updated_by',
        'inv_ledg_cat_updated_at',
    ];
    protected $table = 'inv_ledger_categories';
    protected $primaryKey = 'inv_ledg_cat_cat_id';
    public $incrementing = true;
    public $timestamps = false;

    public static function getCategoryNameById($cat_id)
    {
    	return Inv_ledger_category::where('inv_ledg_cat_cat_id',$cat_id)->first()->inv_ledg_cat_category_name;
    }

    public function getLedgers(){
        return $this->hasMany('App\Models\Inv_ledger','inv_ledg_category_id','inv_ledg_cat_cat_id');
    }

}

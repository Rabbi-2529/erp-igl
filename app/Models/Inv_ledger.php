<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_ledger extends Model
{
    protected $fillable =
    [
        'inv_ledg_id',
        'inv_ledg_company_id',
                'inv_ledg_branch_id',
        'inv_ledg_category_id',
        'inv_ledg_ledger_name',
        'inv_ledg_status',
        'inv_ledg_created_by',
        'inv_ledg_created_at',
        'inv_ledg_updated_at',
        'inv_ledg_updated_by',
    ];
    protected $table = 'inv_ledgers';
    protected $primaryKey = 'inv_ledg_id';
    public $incrementing = true;
    public $timestamps = false;

    public function getLedgersname(){
        return $this->belongsTo('App\Models\Inv_ledger_category','inv_ledg_category_id','inv_ledg_cat_cat_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MexCurrencyInventoryDetail extends Model
{
    protected $fillable =
    [
        'mex_curr_invdet_id',
        'mex_curr_invdet_country_id',
        'mex_curr_invdet_com_id',
                'mex_curr_invdet_branch_id',
        'mex_curr_invdet_currinv_id',
        'mex_curr_invdet_currinv_sell_id',
        'mex_curr_invdet_curr_id',
        'mex_curr_invdet_buy_id',
        'mex_curr_invdet_sell_id',
        'mex_curr_invdet_slno',
        'mex_curr_invdet_sell_status',
        'mex_curr_invdet_status',
        'mex_curr_invdet_submit_by',
        'mex_curr_invdet_submit_at',
        'mex_curr_invdet_update_by',
        'mex_curr_invdet_update_at',
    ];
    protected $table = 'mex_currency_inventory_details';
    protected $primaryKey = 'mex_curr_invdet_id';
    public $incrementing = true;
    public $timestamps = false;
}

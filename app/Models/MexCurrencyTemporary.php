<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MexCurrencyTemporary extends Model
{
    protected $table = 'mex_currency_temporaries';
    protected $fillable =
    [
        'mex_curr_temp_user_id',
        'mex_curr_temp_cus_id',
        'mex_curr_temp_curr_id',
        'mex_curr_temp_curr_name',
        'mex_curr_temp_type_name',
        'mex_curr_temp_total_qty',
        'mex_curr_temp_short_qty',
        'mex_curr_temp_short_remarks',
        'mex_curr_temp_qty',
        'mex_curr_temp_invoice_no',
        'mex_curr_temp_unit_price',
        'mex_curr_temp_exp_date',
        'mex_curr_temp_slno',
        'mex_curr_temp_deal_type',
        'mex_curr_temp_type',
        'mex_curr_temp_status',
        'mex_curr_temp_created_at',
        'mex_curr_temp_updated_at',
    ];
    protected $primaryKey = 'mex_curr_temp_id';
    public $incrementing = true;
    public $timestamps = false;

    public function sold_by(){
        return $this->belongsTo('App\Models\Admin_user','mex_curr_temp_user_id','au_id');
    }
}

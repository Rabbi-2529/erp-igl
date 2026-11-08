<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sds_reason extends Model
{
    //
    protected $fillable =
    [
    	'sr_category',
    	'sr_reason',
    	'sr_reg_date',
    	'sr_status'
    ];
    protected $table = 'sds_reason';
    protected $primaryKey = 'sr_slid';
    public $incrementing = true;
    public $timestamps = false;


    public function reason_info() {
        return $this->belongsTo('App\Models\Sds_reason_category', 'sr_catagory', 'sc_slid');
    }


}

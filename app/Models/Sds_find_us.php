<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sds_find_us extends Model
{
    //
    protected $fillable =
    [
    	'sf_howto',
    	'sf_create_date',
    	'sf_status'
    ];
    protected $table = 'sds_findus';
    protected $primaryKey = 'sf_slid';
    public $incrementing = true;
    public $timestamps = false;
}

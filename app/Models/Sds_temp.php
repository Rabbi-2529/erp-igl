<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sds_temp extends Model
{
    //
    protected $fillable = ['st_title','st_message'];
    protected $table = 'sds_temp';
    protected $primaryKey = 'st_sl_id';
    public $incrementing = true;
    public $timestamps = false;
}

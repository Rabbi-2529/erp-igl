<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sds_reason_category extends Model
{
    //
    protected $fillable = ['sc_catagory'];
    protected $table = 'sds_reason_catagory';
    protected $primaryKey = 'sc_slid';
    public $incrementing = true;
    public $timestamps = false;

    public function reason_infos(){
      return $this->hasMany('App\Models\Sds_reason','sr_catagory','sc_slid');
    }

}

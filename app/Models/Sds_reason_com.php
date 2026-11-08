<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sds_reason_com extends Model
{
    //
    protected $fillable =
    [
      'src_company_id',
       'src_branch_id',
      'src_reson_id',
      'src_create_date',
    ];
    protected $table = 'sds_reason_com';
    protected $primaryKey = 'src_slid';
    public $incrementing = true;
    public $timestamps = false;

    public function getReasonName(){
      return $this->belongsTo('App\Models\Sds_reason','src_reson_id','sr_slid');
    }

}

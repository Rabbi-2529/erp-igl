<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sds_feedback_msg extends Model
{
    //
    protected $fillable =
    [
      'fm_msg',
    ];
    protected $table = 'sds_feedback_msg';
    protected $primaryKey = 'fm_id';
    public $incrementing = true;
    public $timestamps = false;

}

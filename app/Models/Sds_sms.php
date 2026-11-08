<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sds_sms extends Model
{
    //
    protected $fillable =
    [
      'sms_cf_id',
      'sms_cf_next_date',
      'sms_mobileno1',
      'sms_mobileno2',
      'sms_mobileno3',
      'sms_text',
      'sms_sent_at',
      'sms_status',
    ];
    protected $table = 'sds_sms';
    protected $primaryKey = 'sms_id';
    public $incrementing = true;
    public $timestamps = false;
}

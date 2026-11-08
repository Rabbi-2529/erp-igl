<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Oms_inventory extends Model
{
    //
    protected $fillable =
    [
    'oms_reason',
        'sl_id',
        'oms_branch_id',
    'oms_bill_date',
    'oms_staff_id',
    'oms_payment',
    'oms_person',
    'oms_debit',
    'oms_insert_at',
    'oms_what_for',
    'oms_mode',
    'oms_dto',
    'oms_dfrom',
    'oms_status'
    ];
    protected $table = 'oms_inventory';
    protected $primaryKey = 'sl_id';
    public $incrementing = true;
    public $timestamps = false;

    public function employee_info() {
        return $this->belongsTo('App\Models\Admin_user', 'oms_staff_id', 'au_id');
    }
    public function what_info() {
        return $this->belongsTo('App\Models\Sds_query_book', 'oms_what_for', 'qb_id');
    }

    public function what_info_oms() {
        return $this->belongsTo('App\Models\Sds_query_book', 'oms_what_for', 'qb_id');
    }

    public function visitInfo(){
        return $this->belongsTo('App\Models\Client_feedback','oms_what_for','cf_qb_id');
    }
    public function projects(){
        return $this->belongsTo('App\Models\Projects','oms_project_id','id');
    }
       public function mode(){
        return $this->hasOne(Conveyance_Mode::class,'id','oms_mode');
    }
      public function reason(){
        return $this->hasOne(Oms_Reason::class,'id','oms_reason');
    }


    public static function notifycus($cfId,$billDate){
        // dd($cfId);
        $notify = Sds_sms::whereDate('sms_sent_at', $billDate)
                         ->where('sms_cf_id',$cfId)
                         ->where('sms_status',2)
                         ->first();
        // dd($notify);
        if ($notify != null) {
          return true;
        }else{
          return false;
        }
      }
}

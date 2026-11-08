<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;
use DB;

class Client_feedback extends Model
{
    //
    protected $fillable =
    [
    	'cf_company_id',
          	'cf_branch_id',
    	'cf_qb_id',
    	'cf_entry_by',
    	'cf_call_duration',
    	'cf_client_feedback',
    	'cf_next_date',
    	'cf_result',
    	'cf_client_message',
    	'cf_date',
    	'cf_status',
    	'cf_price',
    	'cf_connect_us',
    	'status'
    ];
    protected $table = 'client_feedback';
    protected $primaryKey = 'cf_id';
    public $incrementing = true;
    public $timestamps = false;



    // public function today_fu(){
    //     return $this->belongsTo('App\')
    // }
    public static function user_today_follow($au_id,$company_id,$branch_id){
        $today = Carbon::now()->format('Y-m-d');
        return Client_feedback::where('cf_date',$today)
                              ->where('cf_entry_by',$au_id)
                              ->where('cf_company_id',$company_id)
                                  ->where('cf_branch_id',$branch_id)
                              ->get();
    }

    public static function userFollowUp($au_id,$today){
      // dd($au_id);
      $company_id = Auth::user()->au_company_id;
         $branch_id = Auth::user()->au_branch_id;
      $today = Carbon::now()->format('Y-m-d');
      // return Client_feedback::where('cf_date',$today)
      //                       ->where('cf_entry_by',$au_id)
      //                       ->where('cf_company_id',$company_id)
      //                       ->get();
          $follow =  DB::table('client_feedback')
          ->select(DB::raw('count(*) as user_count'))
          ->where('cf_date',$today)
          ->where('cf_entry_by',$au_id)
           ->where('cf_company_id',$company_id)
                                       ->where('cf_branch_id',$branch_id)
          ->get();
      foreach ($follow as $value) {
        return $value->user_count;
      }
  }


  public static function userFollowUpDuplicate($au_id){
    // dd($au_id);
    $company_id = Auth::user()->au_company_id;
       $branch_id = Auth::user()->au_branch_id;
    $today = Carbon::now()->format('Y-m-d');
    // return Client_feedback::where('cf_date',$today)
    //                       ->where('cf_entry_by',$au_id)
    //                       ->where('cf_company_id',$company_id)
    //                       ->get();
        $follow =  DB::table('client_feedback')
        ->select(DB::raw('count(*) as user_count'))
        ->where('cf_date',$today)
        ->where('cf_entry_by',$au_id)
        ->where('cf_company_id',$company_id)
                                    ->where('cf_branch_id',$branch_id)
        ->groupBy('cf_qb_id')
        ->get();
    foreach ($follow as $value) {
      return $value->user_count;
    }

}

public static function userFollowUpWeekly($au_id,$com,$date,$branch_id){
    // dd($today);
    // $today = Carbon::now()->format('Y-m-d');
    // return Client_feedback::where('cf_date',$today)
    //                       ->where('cf_entry_by',$au_id)
    //                       ->where('cf_company_id',$company_id)
    //                       ->get();
        $follow =  DB::table('client_feedback')
        ->select(DB::raw('count(*) as user_count'))
        ->where('cf_date',$date)
        ->where('cf_entry_by',$au_id)
         ->where('cf_company_id',$com)
               ->where('cf_branch_id',$branch_id)
        ->get();
        // dd($follow);
    foreach ($follow as $value) {
      return $value->user_count;
    }
}

    public function feedback_info(){
      return $this->belongsTo('App\Models\Admin_user','cf_entry_by','au_id');
    }

    public function followup_info(){
      return $this->hasMany('App\Models\Sds_query_book','cf_id','qb_id');
    }

    public function orderInfo(){
      return $this->belongsTo('App\Models\Sds_query_book','cf_qb_id','qb_id');
    }

    public static function notifycus($cus_feed_id){
      $notify = Sds_sms::where('sms_cf_id',$cus_feed_id)
                       ->first();

      if (!empty($notify)) {
        return $notify->sms_status;
      }else{
        return false;
      }
    }


    public function feedbackinfo(){
      return $this->belongsTo('App\Models\Sds_feedback_msg','cf_client_feedback','fm_id');
    }

    public function cus_mobile(){
      return $this->belongsTo('App\Models\Sds_query_book','cf_qb_id','qb_id');
    }

}

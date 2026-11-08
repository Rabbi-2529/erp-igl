<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    protected $table = 'employee_attendances';
    protected $fillable =
    [
        'company_id',
                'branch_id',
        'user_id',
        'check_in',
        'check_out',
            'status',
    'in_click',
    'out_click',
    'user_ip',
    'user_ip_checkout',
        'total_hour',
        'status'
    ];


    public static function checkInByUser($userId,$date){
        // dd($date);
        // $out = date('Y-m-d H:i:s', strtotime($date));

        // dd($date);
        $checkin = EmployeeAttendance::where('user_id',$userId)->whereDate('check_in',$date)->first();
        // dd($checkin);
        if ($checkin == null) {
            return 0;
        }else {
            $checkIn = date('H:i:s', strtotime($checkin->check_in));
            return $checkIn;
        }

    }

    public static function checkOutByUser($userId,$date){
        // dd($date);
        // $out = date('Y-m-d H:i:s', strtotime($date));

        // dd($date);
        $checkin = EmployeeAttendance::where('user_id',$userId)->whereDate('check_out',$date)->first();
        // dd($checkin);
        if ($checkin == null) {
            return 0;
        }else {
            $checkOut = date('H:i:s', strtotime($checkin->check_out));
            return $checkOut;
        }

    }
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
}

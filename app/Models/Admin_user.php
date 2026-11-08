<?php

namespace App\Models;

use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin_user extends Authenticatable
{
    //
    protected $table = 'admin_user';
    protected $fillable =
    [
    'au_id',
    'au_company_id',
    'au_branch_id',
    'au_branch_name',
    'au_company_name',
    'au_name',
    'au_user_id',
    'au_email',
    'au_mobile',
    'au_password',
    'au_address',
    'au_user_type',
    'au_api_key',
    'au_ip_address',
    'au_working_hour',
    'au_work_shift',
    'au_status',
    'au_permission_status',
    'au_sender_id',
    'au_team_id',
    'au_company_img',
    'au_company_logo',
    'au_user_salary',
    'au_user_facility',
      'au_blood_group', 
    ];

    protected $primaryKey = 'au_id';
    public $incrementing = true;
    public $timestamps = false;
    public static function getAllBloodGroups()
    {
        return [
            'A+',
            'A-',
            'B+',
            'B-',
            'AB+',
            'AB-',
            'O+',
            'O-',
        ];
    }
    // Relation With Department table

    public function department(){
        return $this->hasOne(Department::Class, 'id','au_department');
    }

    public function inventories(){
        return $this->hasMany(Inv_product_inventory::Class, 'inv_employee_id','au_id');
    }



    public static function userDetail($value){
        $user = Admin_user::where('au_company_id',Auth::user()->au_company_id)->where('au_id',$value)->first();
        return $user->au_name;
    }

    public function team_info() {
    	return $this->belongsTo('App\Models\Sds_team_name', 'au_team_id', 'tl_sl_id');
    }

    public function customers() {
        return $this->hasMany('App\Models\Sds_query_book', 'qb_entry_by', 'au_id');
    }

    public static function team_member($team_id, $company_id) {
        return Admin_user::where('au_company_id', $company_id)
                ->where('au_team_id', $team_id)
                ->whereIn('au_user_type', [6,5])
                ->get();
    }

    public static function teams($company_id) {
        return Admin_user::where('au_company_id', $company_id)
                ->where('au_user_type', 5)
                ->get();
    }

    public static function users($company_id) {
        return Admin_user::where('au_company_id', $company_id)
                ->where('au_user_type', 6)
                ->get();
    }

    public static function tl_name($team_id,$company_id){
        return Admin_user::where('au_company_id',$company_id)
                         ->where('au_team_id',$team_id)
                         ->where('au_user_type',5)
                         ->first();
    }

    public static function company_info($com_id){
        $admin_query = Admin_user::where('au_user_type',4)
                                ->where('au_company_id',$com_id)
                                ->first();
        return $admin_query;
    }

    public static function company_logo($com_id){
        $logo = Admin_user::where('au_company_id',$com_id)

                          ->first();
        return $logo;
    }
    public static function userName($id){
        $name = Admin_user::where('au_company_id',Auth::user()->au_company_id)
                          ->whereIn('au_id',$id)
                          ->get();
        $names = array();
        foreach ($name as $value) {
            $names[] = $value->au_name;
        }
        $name = implode(', ',$names);
        return $name;
    }
    public static function attendanceUserMobile($userId){
        $com = Auth::user()->au_company_id;
        $user = DB::table('admin_user')
        ->where('au_company_id',$com)
        ->where('au_id',$userId)
        ->first();
        return $user->au_mobile;
    }
    public static function illegalLlegalEntry($userIp){
        $com = Auth::user()->au_company_id;
        $com_ip = DB::table('admin_user')
                ->where('au_company_id', $com)
                ->first();

        if (in_array($userIp, explode(",",$com_ip->au_ip_address))) {
            return "Legal";
        } else{
            return "Illegal";
        }
    }

    public static function attendanceUser($userId){
        $com = Auth::user()->au_company_id;
        $user = DB::table('admin_user')
            ->where('au_company_id', $com)
            ->where('au_id', $userId)
            ->first();
    
        // Check if $user is null
        if ($user) {
            return $user->au_name;
        } else {
            // Handle the case where user is not found
            return 'User not found';  // Or return null, or an empty string, as needed
        }
    }
     public static function shiftDataByUserId($id)
    {
        $shift = PayrollShiftManagement::where('id',$id)
                    ->first();
        return $shift->name;
    }
    public static function userDepartment($depId){

        $dep = Department::where('id',$depId)
                    ->first();
        return $dep->name;
    }



  //team leader data
    public static function teamLeaderNameById($id)
    {
        $user = Admin_user::find($id);

        $team_leader = Admin_user::where('au_company_id',$user->au_company_id)
                            ->where('au_team_id',$user->au_team_id)
                            ->where('au_user_type',5)
                            ->first();
        return $team_leader->au_name;
    }
   public static function teamLeaderIdById($id)
    {
        $user = Admin_user::find($id);
        $team_leader = Admin_user::where('au_company_id',$user->au_company_id)
                            ->where('au_team_id',$user->au_team_id)
                            ->where('au_user_type',5)
                            ->first();
        return $team_leader->au_user_id;
    }


    public static function departmentHeadNameById($id)
    {
        $user = Admin_user::find($id);
        $dep_head = Admin_user::where('au_id',$user->au_dep_head_id)
                          ->first();
        return $dep_head->au_name;
    }

    public static function departmentHeadIdById($id)
    {
        $user = Admin_user::find($id);
        $dep_head = Admin_user::where('au_id',$user->au_dep_head_id)
                          ->first();
        return $dep_head->au_user_id;
    }

    //hr admin data
    public static function hrAdminNameById($id)
    {
        $user = Admin_user::find($id);
        $hr_admin = Admin_user::where('au_company_id',$user->au_company_id)
                            ->where('au_user_type','4')
                            ->first();
        return $hr_admin->au_name;
    }

    public static function hrAdminIdById($id)
    {
        $user = Admin_user::find($id);
        $hr_admin = Admin_user::where('au_company_id',$user->au_company_id)
                            ->where('au_user_type','4')
                            ->first();
        return $hr_admin->au_user_id;
    }

    public function branch()
{
    return $this->belongsTo(Branch::class, 'au_branch_id', 'branch_id');
}

}

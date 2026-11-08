<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\EmployeeActivity;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\Sds_team_name;
use App\Models\Inv_product_inventory_detail;
use App\Models\Inv_product_detail;
use App\Models\Admin_user;
use App\Models\Client_feedback;
use App\Models\Sds_query_book;
use App\Models\OfficeLunch;
use App\Models\UserFlexiload;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeBloodDonate;

use App\Models\Holiday;
use App\Models\Weekends;

use App\Models\Department;
use App\Models\PayrollReferance;
use App\Models\PayrollShiftManagement;
use App\Models\PayrollExperience;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp;


 class UserController extends Controller
{
   public function home(){

        $com = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $teamId = Auth::user()->au_team_id;
        $user = Auth::user()->au_id;
        $today = Carbon::now()->format('Y-m-d');
        $todayDateStart = Carbon::now()->format('Y-m-d') . " 00:00:00";
        $todayDateEnd = Carbon::now()->format('Y-m-d') . " 23:59:59";
        $today_feedback_client = 0;
$today_feedback=0;
        $total = '';
        $feedback = '';
        $lifetimeRev = '';
        $currentMonth = '';
        $defaultNotes = '';

        $comDetails = Admin_user::where('au_company_id', $com)->where('au_branch_id', $branch_id)->first();

        $allowedIpAddresses = explode(',', $comDetails->au_ip_address);

        $shiftTime = explode(',', $comDetails->au_work_shift);

        $shiftStart = Carbon::parse($shiftTime[0]);

        $lunchInTimeLimit = "09:30:00";
        $lunchCancelTimeLimit = "10:00:00";

        $currentTime = Carbon::now();

        $lunchInTimeLimitCarbon = Carbon::parse($lunchInTimeLimit);
        $lunchCancelTimeLimitCarbon = Carbon::parse($lunchCancelTimeLimit);

        $timeMatch = $currentTime->gte($lunchInTimeLimitCarbon);

        $cancelTimeMatch = $currentTime->gte($lunchCancelTimeLimitCarbon);

        $userIpAddress = $_SERVER['REMOTE_ADDR'];

        $isIpAddressValid = in_array($userIpAddress, $allowedIpAddresses);

        $checkIn = EmployeeAttendance::where('company_id', $com)
        ->where('branch_id', $branch_id)
            ->where('user_id', $user)
            ->whereBetween('check_in', [$todayDateStart, $todayDateEnd])
            ->orderBy('check_in', 'desc')
            ->first();

        $checkOut = EmployeeAttendance::where('company_id', $com)
            ->where('user_id', $user)
            ->where('branch_id', $branch_id)
            ->whereBetween('check_out', [$todayDateStart, $todayDateEnd])
            ->orderBy('check_out', 'desc')
            ->first();

        $todayLunch = OfficeLunch::where('user_id', $user)
            ->whereDate('lunch_in', Carbon::today())
            ->first();

        $hasMarked = !is_null($todayLunch);

        $permission = Admin_user::where('au_id', $user)->first();
        $permissionStatus = explode('-', $permission->au_permission_status);

            // Check if user has permission 94 (assuming 94 represents lunchstatus permission)
        $hasPermission94 = in_array("94", $permissionStatus);
        if ($hasMarked) {
            $hasCancelled = !is_null($todayLunch->lunch_cancel);
        }else {
            $hasCancelled = false;
        }

        $check = $checkIn !== null;
        $outCheck = $checkOut !== null;

        $teamMembers = array_merge([$user], Admin_user::team_member($teamId, $com,$branch_id)->pluck('au_id')->toArray());

            if (Auth::user()->au_user_type == 4) {

                $feedback = Client_feedback::where('cf_date',$today)
                    ->where('cf_company_id',$com)
                     ->where('cf_branch_id',$branch_id)
                    ->count();

                $lifetimeRev = Client_feedback::where('cf_company_id', $com)
                               ->where('cf_branch_id',$branch_id)
                    ->where('cf_client_feedback', 6)
                    ->sum('cf_price');

                $currentMonth = Client_feedback::where('cf_company_id', $com)
                               ->where('cf_branch_id',$branch_id)
                    ->where('cf_client_feedback', 6)
                    ->whereMonth('cf_date', Carbon::now()->month)
                    ->sum('cf_price');

                $total = Sds_query_book::where('qb_submit_date',$today)
                                        ->where('qb_company_id',$com)
                                                       ->where('qb_branch_id',$branch_id)
                                        ->count();

                $all_clients = Client_feedback::where('cf_company_id',$com)
                 ->where('cf_branch_id',$branch_id)
                    ->where('cf_next_date',$today)
                    ->pluck('cf_qb_id');

                $all_latest_feedback_id = array();
                foreach ($all_clients as $client_id) {
                    $client_last_feedback = Client_feedback::select('cf_id')->where('cf_company_id',$com)
                         ->where('cf_branch_id',$branch_id)
                        ->where('cf_qb_id', $client_id)
                        ->orderBy('cf_id', 'DESC')
                        ->first();
                    if(!empty($client_last_feedback)) {
                        $all_latest_feedback_id[] = $client_last_feedback->cf_id;
                    }
                }


                if (!empty($all_latest_feedback_id)) {
    $today_feedback_client = Client_feedback::where('cf_company_id', $com)
        ->where('cf_branch_id', $branch_id)
        ->whereIn('cf_id', $all_latest_feedback_id)
        ->where('cf_next_date', $today)
        ->count();
}


            } elseif (Auth::user()->au_user_type == 5) {
                $feedback = Client_feedback::where('cf_date',$today)
                                        ->where('cf_company_id',$com)
                                        ->where('cf_branch_id',$branch_id)
                                        ->whereIn('cf_entry_by', $teamMembers)
                                        ->count();

                $totalRevenue = Client_feedback::where('cf_company_id', $com)
                     ->where('cf_branch_id',$branch_id)
                    ->whereIn('cf_entry_by', $teamMembers)
                    ->where('cf_client_feedback', 6)
                    ->selectRaw('SUM(cf_price) as total_sales')
                    ->first();

                $lifetimeRev = $totalRevenue->total_sales ?? 0;

                $currentMonth = Client_feedback::where('cf_company_id', $com)
                     ->where('cf_branch_id',$branch_id)
                    ->whereIn('cf_entry_by', $teamMembers)
                    ->where('cf_client_feedback', 6)
                    ->whereMonth('cf_date', Carbon::now()->month)
                    ->sum('cf_price');
                $total = Sds_query_book::where('qb_submit_date',$today)
                                        ->where('qb_company_id',$com)
                                             ->where('qb_branch_id',$branch_id)
                                        ->whereIn('qb_entry_by',$teamMembers)
                                        ->count();

                $all_clients = Client_feedback::where('cf_company_id',$com)
                     ->where('cf_branch_id',$branch_id)
                    ->where('cf_next_date',$today)
                    ->whereIn('cf_entry_by',$teamMembers)
                    ->where('cf_next_date',$today)
                    ->pluck('cf_qb_id');

                $all_latest_feedback_id = array();
                foreach ($all_clients as $client_id) {
                    $client_last_feedback = Client_feedback::select('cf_id')->where('cf_company_id',$com)
                         ->where('cf_branch_id',$branch_id)
                        ->whereIn('cf_entry_by',$teamMembers)
                        ->where('cf_qb_id', $client_id)
                        ->orderBy('cf_id', 'DESC')
                        ->first();
                    if(!empty($client_last_feedback)) {
                        $all_latest_feedback_id[] = $client_last_feedback->cf_id;
                    }
                }
if (!empty($all_latest_feedback_id)) {
                $today_feedback_client = Client_feedback::where('cf_company_id',$com)
                     ->where('cf_branch_id',$branch_id)
                    ->whereIn('cf_entry_by',$teamMembers)
                    ->whereIn('cf_id', $all_latest_feedback_id)
                    ->where('cf_next_date',$today)
                    ->count();

}
            } elseif (Auth::user()->au_user_type == 6) {
                    $feedback = Client_feedback::where('cf_date',$today)
                                            ->where('cf_company_id',$com)
                                         ->where('cf_branch_id',$branch_id)
                                            ->where('cf_entry_by',$user)
                                            ->count();

                    $lifetimeRev = Client_feedback::where([
                        ['cf_company_id', $com],
                            ['cf_branch_id', $branch_id],
                        ['cf_entry_by', $user],
                        ['cf_client_feedback', 6],
                    ])->sum('cf_price');

                    $currentMonth = Client_feedback::where([
                        ['cf_company_id', $com],
                           ['cf_branch_id', $branch_id],
                        ['cf_entry_by', $user],
                        ['cf_client_feedback', 6],
                        [DB::raw('MONTH(cf_date)'), date('m')]
                    ])->sum('cf_price');

                    $total = Sds_query_book::where('qb_submit_date',$today)
                                        ->where('qb_company_id',$com)
                                           ->where('qb_branch_id',$branch_id)
                                        ->where('qb_entry_by',$user)
                                        ->count();

                    $my_clients = Sds_query_book::where('qb_company_id',$com)
                               ->where('qb_branch_id',$branch_id)
                        ->where('qb_entry_by',$user)
                        ->pluck('qb_id')->toArray();
                    $all_clients = Client_feedback::select('cf_qb_id')
                        ->where('cf_company_id',$com)
                         ->where('cf_branch_id',$branch_id)
                        ->whereIn('cf_qb_id',$my_clients)
                        ->where('cf_next_date',$today)
                        ->pluck('cf_qb_id')
                        ->toArray();

                    $all_latest_feedback_id = array();
                    foreach ($all_clients as $client_id) {
                        $client_last_feedback = Client_feedback::select('cf_id')->where('cf_company_id',$com)
                         ->where('cf_branch_id',$branch_id)
                            ->whereIn('cf_qb_id',$my_clients)
                            ->where('cf_qb_id', $client_id)
                            ->orderBy('cf_id', 'DESC')
                            ->first();
                        if(!empty($client_last_feedback)) {
                            $all_latest_feedback_id[] = $client_last_feedback->cf_id;
                        }
                    }
if (!empty($all_latest_feedback_id)) {
                    $today_feedback_client = Client_feedback::where('cf_company_id',$com)
                     ->where('cf_branch_id',$branch_id)
                        ->whereIn('cf_qb_id',$my_clients)
                        ->whereIn('cf_id', $all_latest_feedback_id)
                        ->where('cf_next_date',$today)
                        ->count();
}

            } elseif (Auth::user()->au_user_type == 7) {

                    $admin_access = Auth::user()->agm_permission;
                    $agm = explode('-',$admin_access);

                    foreach(Admin_user::team_member($agm, $branch_id) as $member) {
                        $teamMembers[] = $member->au_id;

                    }

                    $feedback = Client_feedback::where('cf_date',$today)
                                            ->where('cf_company_id',$com)
                                             ->where('cf_branch_id',$branch_id)
                                            ->whereIn('cf_entry_by',$teamMembers)
                                            ->count();

                    $totalRevenue = Client_feedback::where('cf_company_id', $com)
                     ->where('cf_branch_id',$branch_id)
                        ->whereIn('cf_entry_by', $teamMembers)
                        ->where('cf_client_feedback', 6)
                        ->sum('cf_price');

                    $lifetimeRev = $totalRevenue;

                    $currentMonthRev = Client_feedback::where('cf_company_id', $com)
                     ->where('cf_branch_id',$branch_id)
                        ->whereIn('cf_entry_by', $teamMembers)
                        ->where('cf_client_feedback', 6)
                        ->whereMonth('cf_date', Carbon::now()->month)
                        ->sum('cf_price');

                    $currentMonth = $currentMonthRev;

                    $total = Sds_query_book::where('qb_submit_date',$today)
                                                ->where('qb_company_id',$com)
                                                 ->where('qb_branch_id',$branch_id)
                                                ->whereIn('qb_entry_by',$teamMembers)
                                                ->count();

                    $all_clients = Client_feedback::select('cf_qb_id')
                        ->where('cf_company_id',$com)
                         ->where('cf_branch_id',$branch_id)
                        ->whereIn('cf_entry_by',$teamMembers)
                        ->where('cf_next_date',$today)
                        ->pluck('cf_qb_id')
                        ->toArray();

                    $all_latest_feedback_id = array();
                    foreach ($all_clients as $client_id) {
                        $client_last_feedback = Client_feedback::select('cf_id')->where('cf_company_id',$com)
                         ->where('cf_branch_id',$branch_id)
                            ->whereIn('cf_entry_by',$teamMembers)
                            ->where('cf_qb_id', $client_id)
                            ->orderBy('cf_id', 'DESC')
                            ->first();
                        if(!empty($client_last_feedback)) {
                            $all_latest_feedback_id[] = $client_last_feedback->cf_id;
                        }
                    }
if (!empty($all_latest_feedback_id)) {
                    $today_feedback_client = Client_feedback::where('cf_company_id',$com)
                     ->where('cf_branch_id',$branch_id)
                        ->whereIn('cf_entry_by',$teamMembers)
                        ->whereIn('cf_id', $all_latest_feedback_id)
                        ->where('cf_next_date',$today)
                        ->count();
}

            }

        $todayAttendanceCount = EmployeeAttendance::where('company_id',$com)
         ->where('branch_id',$branch_id)
                                  ->whereBetween('created_at',[$todayDateStart,$todayDateEnd])
                                  ->count();

        $employeCount = Admin_user::where('au_company_id',$com)
                                 ->where('au_branch_id',$branch_id)
                                  ->where('au_status',1)
                                  ->whereIn('au_user_type', [4,5,6])
                                  ->count();

        $todayAttendance = EmployeeAttendance::where('company_id',$com)
         ->where('branch_id',$branch_id)
                                    ->whereBetween('created_at',[$todayDateStart,$todayDateEnd])
                                    ->get();

        $todayLunchUsers = OfficeLunch::where('company_id', $com)
          ->where('branch_id',$branch_id)
                        ->whereDate('lunch_in', now()->toDateString())
                        ->get();
        
        // Inventory figures for the dashboard, scoped by role (see App\Services\DashboardStats)
        $dash = new \App\Services\DashboardStats(Auth::user());
        $dashMonth = (int) request('m', Carbon::now()->month);
        $dashYear = (int) request('y', Carbon::now()->year);
        if ($dashMonth < 1 || $dashMonth > 12) { $dashMonth = Carbon::now()->month; }
        if ($dashYear < 2000 || $dashYear > Carbon::now()->year + 1) { $dashYear = Carbon::now()->year; }
        $dashFrom = Carbon::create($dashYear, $dashMonth, 1)->startOfMonth();
        $dashTo = $dashFrom->copy()->endOfMonth();

        $monthTotals = $dash->totals($dashFrom, $dashTo);
        $todayTotals = $dash->totals(Carbon::today(), Carbon::today());
        $yearly = $dash->yearly($dashYear);
        $topProducts = $dash->topProducts($dashYear);
        $todaySales = $dash->salesOn(Carbon::today());
        $customerDue = $dash->customerDue();
        $supplierDue = $dash->supplierDue();
        $lastSale = $dash->lastSale();

        // month summary (pie + table) on the dashboard
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $monthLabel = $monthNames[$dashMonth - 1] . ' ' . $dashYear;
        $tk = function ($v) { return 'TK ' . number_format((float) $v, 2); };
        if ($dash->canSeeCosts) {
            $pieRows = [
                ['label' => 'Total Sale', 'key' => 'sales', 'y' => round($monthTotals['sales'], 2)],
                ['label' => 'Total Purchase', 'key' => 'purchase', 'y' => round($monthTotals['purchases'], 2)],
                ['label' => 'Total Expense', 'key' => 'expense', 'y' => round($monthTotals['expenses'], 2)],
                ['label' => 'Employee Salary', 'key' => 'salary', 'y' => round($monthTotals['salary'], 2)],
                ['label' => 'Service', 'key' => 'service', 'y' => round($monthTotals['service'], 2)],
            ];
        } else {
            $pieRows = [
                ['label' => 'Sales', 'key' => 'sales', 'y' => round($monthTotals['sales'], 2)],
                ['label' => 'Service', 'key' => 'service', 'y' => round($monthTotals['service'], 2)],
                ['label' => 'Collected', 'key' => 'collected', 'y' => round($monthTotals['collections'], 2)],
            ];
        }
        $pieHasData = collect($pieRows)->sum('y') > 0;

        // older names still used by the view
        $salesData = $yearly['sales'];
        $purchaseData = $yearly['purchases'] ?? array_fill(0, 12, 0);
        $totalSalesSellPrice = $todayTotals['sales'] + $todayTotals['service'];
        $totalPurchasesBuyPrice = $todayTotals['purchases'] ?? 0;
        $lastSaleSellPrice = $lastSale ? $lastSale->amount : 0;
        $totalSalesBuyPrice = 0;
        $totalPurchasesSellPrice = 0;
        $lastSaleBuyPrice = 0;

$currentUserAbsentCount = 0;
$currentUserWorkingDays = 0;
$currentUserAbsentDates = [];

$currentUserMonth = Carbon::now()->month;
$currentUserYear = Carbon::now()->year;
$currentUserToday = Carbon::now();
$currentUserYesterday = Carbon::now()->subDay();
$currentUserStartOfMonth = Carbon::now()->startOfMonth();

// Get holidays for current month
$currentUserHolidayDays = Holiday::where('company_id', $com)
    ->where('branch_id', $branch_id)
    ->where('year', $currentUserYear)
    ->where('month', $currentUserMonth)
    ->pluck('date')
    ->map(function($day) {
        return (int)$day;
    })
    ->toArray();

// Get attendance records for current user
$currentUserAttendance = EmployeeAttendance::where('company_id', $com)
    ->where('branch_id', $branch_id)
    ->where('user_id', $user)
    ->whereMonth('check_in', $currentUserMonth)
    ->whereYear('check_in', $currentUserYear)
    ->get()
    ->keyBy(function($item) {
        return Carbon::parse($item->check_in)->format('Y-m-d');
    });

// Loop through each day from start of month to yesterday
$currentDate = clone $currentUserStartOfMonth;
while ($currentDate <= $currentUserYesterday) {
    $dateString = $currentDate->format('Y-m-d');
    $currentDay = (int)$currentDate->format('d');
    
    // Check if it's weekend
    $isWeekend = Weekends::isWeekend($dateString);
    
    // Check if it's a holiday
    $isHoliday = in_array($currentDay, $currentUserHolidayDays);
    
    // If it's a working day (not weekend and not holiday)
    if (!$isWeekend && !$isHoliday) {
        $currentUserWorkingDays++;
        
        // Check if user attended on this day
        if (!isset($currentUserAttendance[$dateString])) {
            $currentUserAbsentCount++;
            $currentUserAbsentDates[] = $dateString;
        }
    }
    
    $currentDate->addDay();
}

$hasThreeOrMoreAbsents = $currentUserAbsentCount >= 3;      
if (!empty($all_latest_feedback_id)) {
$today_feedback= Client_feedback::where('cf_company_id',$com)
                     ->where('cf_branch_id',$branch_id)
                        ->whereIn('cf_id', $all_latest_feedback_id)
                        ->where('cf_next_date',$today)
                        ->count();

}
        $lunchCount = $todayLunchUsers->sum('total_meal');
        
        

        return view('page.home',compact('total','today_feedback_client','salesData', 'purchaseData', 'today_feedback','totalSalesBuyPrice', 'totalSalesSellPrice',
    'totalPurchasesBuyPrice', 'totalPurchasesSellPrice', 'lastSaleBuyPrice', 'lastSaleSellPrice','topProducts','hasPermission94','feedback','today','lifetimeRev','currentMonth','check','outCheck','todayAttendanceCount','employeCount','todayAttendance','hasMarked','isIpAddressValid','timeMatch','defaultNotes','lunchCount','cancelTimeMatch','hasCancelled',   'currentUserWorkingDays', 'currentUserAbsentDates',
            'dash', 'dashMonth', 'dashYear', 'monthTotals', 'todayTotals', 'yearly', 'todaySales', 'customerDue', 'supplierDue', 'lastSale',
            'monthNames', 'monthLabel', 'tk', 'pieRows', 'pieHasData'));
    }
  
  
    public function create_team()
    {
        $company_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $teams = Sds_team_name::where('tl_branch_id', $branch_id)->get();
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $team_leaders = Admin_user::where('au_user_type', '5')
                ->where('au_company_id', $com_id)
                ->where('au_branch_id', $branch_id)
                ->get();


        return view('page.hrm.create-team', compact('teams','team_leaders'));
    }
    public function create_team_insert(Request $request)
    {
        $request->validate([
            'tl_team_name' => 'required',
        ], [
            'tl_team_name.required' => 'Provide Team Name',
        ]);

        $time = now();
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $team = new Sds_team_name;
        $team->tl_user_id = 0;
        $team->tl_com_id = $com_id;
        $team->tl_branch_id = $branch_id;
        $team->tl_team_name = $request->tl_team_name;
        $team->tl_date = $time;
        $team->tl_status = 1;
        $team->save();

        return redirect('/create_team')->with(['msg' => 'Team Created Successfully']);
    }
    public function team_update($id){
        $team = Sds_team_name::where('tl_sl_id', $id)->first();
        return view('pages.team_update',compact('team'));
    }
    public function team_update_submit(Request $request,$id){
        $errmsg =
        [
            'tl_team_name.required' => 'Should Enter Team Name',
        ];
        $request->validate([
            'tl_team_name' => 'required',
            ],$errmsg);
        $team_name = Input::get('tl_team_name');
        $table = Sds_team_name::where('tl_sl_id',$id)->update(['tl_team_name' => $team_name]);
        return redirect('/create_team')->with(['msg1' => 'Updated Successfully']);
    }
    public function user_register(){
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        // Get all teams
        $teams = Sds_team_name::where('tl_com_id', $com_id)->where('tl_branch_id', $branch_id)->get();

        // Get the IDs of teams that have a team leader assigned
        $teams_with_leader_ids = Admin_user::where('au_user_type', 5)->pluck('au_team_id')->toArray();

        // Filter the teams to exclude those with team leaders assigned
        $unassigned_teams = $teams->reject(function ($team) use ($teams_with_leader_ids) {
            return in_array($team->tl_sl_id, $teams_with_leader_ids);
        });

        // Retrieve other necessary data
        $au_mobile = Admin_user::all();
        $today = Carbon::now()->format('Y-m-d');
        $users = Admin_user::where('au_user_type', 6)->whereDate('au_created_date', $today)->get();
        $depts = Department::all();
        $shifts = PayrollShiftManagement::where('company_id', $com_id)->where('branch_id', $branch_id)->where('status', 1)->get();

    // Pass the $teams variable to the view
    return view('page.hrm.add_employee', compact('users','unassigned_teams', 'teams', 'au_mobile', 'depts', 'shifts'));
}

    public function user_register_insert(Request $request){

        $table = Input::all();
        $message =
        [
            'au_mobile.required' => 'Mobile Number Required',
            'au_mobile.regex' => 'Invalid Mobile Number',

        ];
        $table = $request->validate([
              'au_mobile' => 'required||regex:/(01)[0-9]*$/',
              'au_user_id' => 'required|max:12'
            ], $message);

        $com_id = Auth::user()->au_company_id;
        $com_name = Auth::user()->au_company_name;
        $branch_id = Auth::user()->au_branch_id;
        $branch_name = Auth::user()->au_branch_name;
            $table = new Admin_user;
            $date = Carbon::now()->format('Y-m-d');
            $table ->au_company_id = $com_id;
             $table ->au_branch_id = $branch_id;
               $table ->au_branch_id = $branch_id;
            $table ->au_company_name = $com_name;
                 $table ->au_branch_name = $branch_name;
            $table->au_company_ip = Auth::user()->au_ip_address;
            $table ->au_name = Input::get('au_name');
            $table ->au_user_id = Input::get('au_user_id');
            $table ->au_email = Input::get('au_email');
            $table ->au_mobile = Input::get('au_mobile');
            $table ->au_password = md5(Input::get('au_password'));
            $table ->au_address = Input::get('au_address');
            $table ->au_status = 1;
            $table ->au_user_type = Input::get('au_user_type');
            $table->au_user_salary =Input::get('au_user_salary');
            $table->au_user_facility = Input::get('au_user_facility');
            if ($request->hasfile('au_company_img')) {
                $file = $request->file('au_company_img');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . "." . $extension;

                $path = $request->au_company_img->storeAs('image', $filename);

                $table->au_company_img = $filename;
            }else{
                $table->au_company_img = "";
            }
            if(Input::get('au_user_type') == '6') {
                $table ->au_team_id = Input::get('au_team_id_user');
            } elseif(Input::get('au_user_type') == '5') {
                $table ->au_team_id = Input::get('au_team_id_team');
            }

            $table ->hr_admin_id = Auth::user()->au_id;

            $table ->au_created_date = $date;

            $table ->au_department = implode('-',$request->input('au_department'));
            $table ->au_shifts = $request->input('au_shifts');
            $table ->job_type = $request->input('job_type');

            $table -> save();



            $api_sender = Admin_user::where('au_company_id',$com_id)
            ->where('au_branch_id',$branch_id)
                              ->where('au_user_type',4)
                              ->first();

            $number = "8801521556212";

            $message = "Dear, " .Input::get('au_name') .".Your Phone Number is: ".Input::get('au_mobile') ." and Password: ". Input::get('au_password')." www.iglweb.com";
            $message = urlencode($message);
            $api_key = $api_sender->au_api_key;
            $sender_id = $api_sender->au_sender_id;
            $client = new \GuzzleHttp\Client();
            $api_url = "http://sms.iglweb.com/api/v1/send?api_key=". $api_key ."&contacts=". $number ."&senderid=". $sender_id ."&msg=".$message;
            $response = $client->request('GET', "$api_url");

            $json_response = $response->getBody()->getContents();
            $api_response = json_decode($json_response);

            if ($api_response->code == "445000") {
                $type = 'success';
                $table->save();
                $msg = "SMS Sending Successfully!";
            } else if ($api_response->code == "445040") {
                $type = 'danger';
                $msg = "SMS Sending failed because of invalid API key";
            } else if ($api_response->code == "445080") {
                $type = 'danger';
                $msg = "SMS Sending failed because of invalid Sender ID";
            } else if ($api_response->code == "445120") {
                $type = 'danger';
                $msg = "SMS Sending failed because of your sms balance is low";
            } else if ($api_response->code == "445110") {
                $type = 'danger';
                $msg = "SMS Sending failed because of Client number are invalid";
            } else {
                $type = "danger";
                $msg = "SMS Sending failed because of ". $api_response->message;
            }
            session()->flash('type', $type);
            session()->flash('message', $msg);



        return redirect('/user_register')->with(['msg' => 'User Created Successfully']);
    }

    /**
     * Who may open an employee's profile (it shows NID, family and address details):
     * yourself; root; the company admin / owner; a team leader for their own team; an AGM for the
     * teams they manage; staff with the CRM "team member list" permission for team members of their branch.
     */
    private function canViewProfile($viewer, $target)
    {
        $type = (int) $viewer->au_user_type;
        if ((int) $viewer->au_id === (int) $target->au_id || $type === 1) {
            return true;
        }
        if ((int) $viewer->au_company_id !== (int) $target->au_company_id) {
            return false;
        }
        if (in_array($type, [3, 4], true)) {
            return true;
        }
        if ($type === 5 && $viewer->au_team_id && (int) $viewer->au_team_id === (int) $target->au_team_id) {
            return true;
        }
        if ($type === 7) {
            $teams = array_filter(explode('-', (string) $viewer->agm_permission));
            if ($target->au_team_id && in_array((string) $target->au_team_id, $teams, true)) {
                return true;
            }
        }
        $memberListCode = \App\Models\Au_access::where('permission_name', 'crm_team_member_list')->value('code');
        if ($memberListCode !== null
            && in_array((string) $memberListCode, explode('-', (string) $viewer->au_permission_status), true)
            && (int) $target->au_user_type === 6
            && (int) $target->au_branch_id === (int) $viewer->au_branch_id) {
            return true;
        }
        return false;
    }

    /** Company admin (4), company owner (3) or root (1) */
    private function isCompanyAdmin()
    {
        return in_array((int) Auth::user()->au_user_type, [1, 3, 4], true);
    }

    public function userSettings(Request $request,$id){
        // Only the company admin may change an employee's settings (password, IP, shift, ...), and only in their company
        $target = Admin_user::find($id);
        if (!$target || !$this->isCompanyAdmin()
            || ((int) Auth::user()->au_user_type !== 1 && (int) $target->au_company_id !== (int) Auth::user()->au_company_id)) {
            return redirect()->back()->with('error', 'Only the company admin can change these settings.');
        }

        $table = $request->validate([
            'au_user_id' => 'required|max:12'
          ]);
        // dd(Carbon::now()->format('Y-m-d H:i:s'));
        // dd(date('H:i:s', strtotime($request->working_shift_end)));
      $workingShiftBegin = array();
      $workingShiftBegin[] = date('H:i:s', strtotime($request->working_shift_begin));

      $workingShiftEnd = array();
      $workingShiftEnd[] = date('H:i:s', strtotime($request->working_shift_end));
    //   dd($workingShiftEnd);
      $merge = array_merge($workingShiftBegin,$workingShiftEnd);
      $workingShift = implode(',',$merge);

      $settings = Admin_user::find($id);
      $settings->au_name = $request->input('user');
      $settings->au_mobile = $request->input('cell');

      if ($request->input('entry') != '') {
        $settings->au_password = md5($request->input('entry'));
      }
      $settings->au_user_id = $request->input('au_user_id');
      $settings->au_email = $request->input('email');
      $settings->au_address = $request->input('address');
      $settings->au_ip_address = $request->ip_address;
      $settings->au_working_hour = $request->working_hour * 60;
      $settings->au_work_shift = $workingShift;

        if ($request->hasfile('logo') != '') {
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filename = time().random_int(10,99) . "." . $extension;

            $path = $request->logo->storeAs('image', $filename);

                $settings->au_company_logo = $filename;

          }
        if ($request->hasfile('au_company_img')) {
            $file = $request->file('au_company_img');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . "." . $extension;

            $request->au_company_img->storeAs('image', $filename);

            $settings->au_company_img = $filename;
        }

        if( Auth::user()->au_user_type == 4 ){
            $settings ->au_department = implode('-',$request->input('au_department'));
            $settings ->au_shifts = $request->input('au_shifts');

        }

      $settings->save();
      return redirect()->back();
    }

    public function mobileno_exists(Request $request){
        $com_id = Auth::user()->au_company_id;
          $branch_id = Auth::user()->au_branch_id;
        $exist = Admin_user::where('au_company_id',$com_id)->where('au_branch_id',$branch_id)->where('au_mobile', $request->au_mobile)->first();
        $exist_2 = Admin_user::where('au_company_id',$com_id)->where('au_branch_id',$branch_id)->where('persional_mobile', $request->au_mobile)->first();
        $exist_3 = Admin_user::where('au_company_id',$com_id)->where('au_branch_id',$branch_id)->where('f_mobile', $request->au_mobile)->first();
        $exist_4 = Admin_user::where('au_company_id',$com_id)->where('au_branch_id',$branch_id)->where('m_mobile', $request->au_mobile)->first();
        if(!empty($exist)) {
            return "true";
        }
        else if(!empty($exist_2)){
            return "true";
        }
        else if(!empty($exist_3)){
            return "true";
        }
        else if(!empty($exist_4)){
            return "true";
        }
        else {
            return "false";
        }
    }
    public function team_leaders(){
        $com_id = Auth::user()->au_company_id;
         $branch_id = Auth::user()->au_branch_id;
        
        $team_leaders = Admin_user::where('au_user_type','5')->where('au_company_id',$com_id)->where('au_branch_id',$branch_id)->get();
        return view('page.hrm.manage-team',compact('team_leaders'));
    }
    public function team_leader_suspend($id){
        $team_leader = Admin_user::find($id);
        $team_leader ->au_status = 0;
        $team_leader ->save();
        return redirect('/team_leaders');
    }
    public function team_leader_active($id){
        $team_leader = Admin_user::find($id);
        $team_leader ->au_status = 1;
        $team_leader ->save();
        return redirect('/team_leaders');
    }
public function user_list(){
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    if (Auth::user()->au_user_type == 3) {
        // Type 3 user: show both 4 and 6 from all branches
        $users = Admin_user::whereIn('au_user_type', [4, 6])
            ->where('au_company_id', $com_id)
            ->withCount('customers')
            ->orderBy('au_user_id', 'asc')
            ->get();
    } else {
        // Other users: show only type 6 from same branch
        $users = Admin_user::where('au_user_type', 6)
            ->where('au_company_id', $com_id)
            ->where('au_branch_id', $branch_id)
            ->withCount('customers')
            ->orderBy('au_user_id', 'asc')
            ->get();
    }

    return view('page.hrm.manage_employee', compact('users'));
}

    public function user_profile($id){

        $com_id = Auth::user()->au_company_id;
               $branch_id = Auth::user()->au_branch_id;
        $depts = Department::all();
        $shifts = PayrollShiftManagement::where('company_id',$com_id)->where('branch_id',$branch_id)->where('status',1)->get();
        $user_infos = Admin_user::find($id);

        $au_id = Auth::user()->au_id;
        $experience = PayrollExperience::where('ex_level','Experienced')->where('user_id', $au_id)->get();
        $reference = PayrollReferance::where('user_id', $au_id)->get();

        if (!$user_infos) {
            abort(404);
        }
        if (!$this->canViewProfile(Auth::user(), $user_infos)) {
            return redirect('/user_profile/' . $au_id)
                ->with('notPermitted', 'You can only open your own profile.');
        }

        // who may see which buttons (Settings: company admin only; password / my-info: own profile only)
        $isOwnProfile = (int) $user_infos->au_id === (int) $au_id;
        $isCompanyAdmin = $this->isCompanyAdmin();

        // names for the ids stored on the employee (no lookups inside the view)
        $depIds = array_filter(explode('-', (string) $user_infos->au_department));
        $departmentNames = $depIds ? Department::whereIn('id', $depIds)->pluck('name')->implode(', ') : '';
        $shiftName = $user_infos->au_shifts ? PayrollShiftManagement::where('id', $user_infos->au_shifts)->value('name') : '';
        $place = [
            'division' => $user_infos->divison ? DB::table('divisions')->where('id', $user_infos->divison)->value('name') : '',
            'district' => $user_infos->district ? DB::table('districts')->where('id', $user_infos->district)->value('name') : '',
            'thana'    => $user_infos->thana ? DB::table('upazilas')->where('id', $user_infos->thana)->value('name') : '',
        ];
        // the profile's own photo (falls back to the default picture)
        $profileImage = ($user_infos->au_company_img && file_exists(base_path('public/profile/' . $user_infos->au_company_img)))
            ? asset('public/profile/' . $user_infos->au_company_img)
            : asset('assets/img/icons/user.png');

        // Team and recent activity
        $team = $user_infos->au_team_id ? Sds_team_name::where('tl_sl_id', $user_infos->au_team_id)->first() : null;
        $teamLeader = $team ? Admin_user::where('au_company_id', $user_infos->au_company_id)
            ->where('au_team_id', $team->tl_sl_id)->where('au_user_type', 5)->where('au_status', 1)->first() : null;
        $teamSize = $team ? Admin_user::where('au_company_id', $user_infos->au_company_id)
            ->where('au_team_id', $team->tl_sl_id)->where('au_status', 1)->count() : 0;
        $shiftHours = $user_infos->au_shifts ? PayrollShiftManagement::where('id', $user_infos->au_shifts)->value('working_hour') : null;
        $shiftHours = $shiftHours ?: $user_infos->au_work_shift;
        $shiftTiming = '';
        $times = array_filter(explode(',', (string) $shiftHours));
        if (count($times) === 2) {
            $shiftTiming = date('h:i A', strtotime($times[0])) . ' - ' . date('h:i A', strtotime(end($times)));
        }
        $activities = EmployeeActivity::where('user_id', $user_infos->au_id)
            ->where('company_id', $user_infos->au_company_id)
            ->orderByDesc('activity_date')->orderByDesc('id')->limit(5)
            ->get(['id', 'w_title', 'w_d', 'activity_date', 'created_at'])
            ->map(function ($a) {
                // the activity is saved as rich text: show a plain-text summary
                $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace(['</li>', '<br>', '<br/>', '</p>'], ' ', (string) $a->w_d)))));
                return (object) [
                    'date'  => $a->activity_date ?: optional($a->created_at)->format('Y-m-d'),
                    'title' => $a->w_title ?: 'Activity',
                    'text'  => \Illuminate\Support\Str::limit($text, 160),
                ];
            });
        $canSeeLeader = $teamLeader && $this->canViewProfile(Auth::user(), $teamLeader);

        return view('page.hrm.user_profile',compact('user_infos','depts','shifts','experience','reference',
            'isOwnProfile', 'isCompanyAdmin', 'departmentNames', 'shiftName', 'place', 'profileImage',
            'team', 'teamLeader', 'teamSize', 'shiftTiming', 'activities', 'canSeeLeader'));
    }
    public function user_profile_status(Request $request,$id){
        $admin = Admin_user::find($id);
        $admin->au_status = 0;
        $admin->save();
        return redirect('/admin_list');
    }
    public function user_profile_status_activate(Request $request,$id){
        $admin = Admin_user::find($id);
        $admin->au_status = 1;
        $admin->save();
        return redirect('/admin_list');
    }
    public function user_edit($id){
        $user_edits = Admin_user::find($id);
        $com_id = Auth::user()->au_company_id;
          $branch_id = Auth::user()->au_branch_id;
        $departments = Department::all();
        $shifts = PayrollShiftManagement::where('company_id',$com_id)->where('branch_id',$branch_id)->get();
        $teams = Sds_team_name::where('tl_com_id',$com_id)->where('tl_branch_id',$branch_id)->get();

        // Retrieve unassigned teams of type 6 for the current company
        $unassigned_teams = Admin_user::where('au_user_type', 6)
                                       ->where('au_company_id', $com_id)
                                                 ->where('au_branch_id', $branch_id)
                                       ->get();


        return view('page.hrm.employee_edit', compact('user_edits', 'unassigned_teams', 'teams', 'departments', 'shifts'));
    }
    public function user_list_activate(Request $request,$id){
        $user = Admin_user::find($id);
        $user->au_status = 1;
        $user-> save();
        return redirect('/user_list');
    }
    public function user_list_deactivate(Request $request,$id){
        $user = Admin_user::find($id);
        $user->au_status = 0;
        $user-> save();
        return redirect('/user_list');
    }

    public function user_flexi_activate(Request $request,$id){
        $user = Admin_user::find($id);
        $user->au_flexi_status = 1;
        $user-> save();
        return redirect('/user_list');
    }
    public function user_flexi_deactivate(Request $request,$id){
        $user = Admin_user::find($id);
        $user->au_flexi_status = 0;
        $user-> save();
        return redirect('/user_list');
    }

    public function teamLeaderExistAjax(Request $request){
        $teamLeader = Admin_user::where('au_company_id',Auth::user()->au_company_id)
        ->where('au_branch_id',Auth::user()->au_branch_id)
                           ->where('au_team_id',$request->value)
                           ->where('au_id',$request->user)
                           ->first();
                        //    dd($teamLeader);
        if ($teamLeader == null)  {
            return 445404;
        }else {
            return 445405;
        }
    }

    public function user_update(Request $request,$id){

        $workingShiftBegin = array();
        $workingShiftBegin[] = $request->working_shift_begin;
        $workingShiftEnd = array();
        $workingShiftEnd[] = $request->working_shift_end;
        $merge = array_merge($workingShiftBegin,$workingShiftEnd);
        $workingShift = implode(',',$merge);
        // dd($workingShift);
        $user = Admin_user::find($id);
        $user->au_name = $request->input('au_name');
        if( Auth::user()->au_user_type == 1 ){
          $user->au_user_type = 4;
        }else {
          $user->au_user_type = $request->input('au_user_type');
        }
        $user->au_email = $request->input('au_email');
        $user->au_mobile = $request->input('au_mobile');
        if ($request->input('au_password') != '') {
            $user->au_password = md5($request->input('au_password'));
          }
        $user->au_status = $request->status;
        $user->au_flexi_permission = $request->flexi_status;
        $user->au_department = $request->department;
        $user->au_shifts = $request->au_shifts;
        $user->au_address = $request->input('au_address');
        $user->au_ip_address = $request->ip_address;
        $user->au_working_hour = $request->working_hour;
        $user->au_work_shift = $workingShift;

        /*dump(app()->make('path.public')."/../..");
        dd(public_path('/'));*/
        if ($request->hasfile('au_company_img')) {
            $file = $request->file('au_company_img');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . "." . $extension;

            $request->au_company_img->storeAs('image', $filename);

            $user->au_company_img = $filename;
        }
        if(Input::get('au_user_type') == '6') {
                $user ->au_team_id = Input::get('au_team_id_user');
            } elseif(Input::get('au_user_type') == '5') {
                Admin_user::where('au_company_id',Auth::user()->au_company_id)
                ->where('au_branch_id',Auth::user()->au_branch_id)
                          ->where('au_team_id',$request->au_team_id_team)
                          ->where('au_user_type',5)
                          ->update([ 'au_user_type' => 6 ]);
                Admin_user::where('au_company_id',Auth::user()->au_company_id)
                  ->where('au_branch_id',Auth::user()->au_branch_id)
                        ->where('au_id',$id)
                        ->update([ 'au_user_type' => 5 ]);

                $user ->au_team_id = Input::get('au_team_id_team');
            }
        $user ->au_user_id = Input::get('au_user_id');
        $user->au_user_salary = $request->input('au_user_salary'); 
        $user->au_user_facility = $request->input('au_user_facility'); 

        $user->save();
        return redirect()->back()->with(['update' => 'User Profile is updated Successfully']);
    }


public function agm_list()
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $agm_list = Admin_user::where('au_user_type', '7')
        ->where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id)
        ->get();

    return view('pages.agm', compact('agm_list'));
}

    public function agm_suspend($id){
        Admin_user::where('au_id',$id)->update(['au_status' => 0]);
        return redirect()->back()->with(['suspend' => 'Suspended!']);
    }
    public function agm_active($id){
        Admin_user::where('au_id',$id)->update(['au_status' => 1]);
        return redirect()->back()->with(['active' => 'Activated']);
    }

 public function team_list($id)
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $admin_user = Admin_user::where('au_id', $id)->first();
    $abc = explode('-', $admin_user->agm_permission);

    $team_leaders = Admin_user::where('au_user_type', '5')
        ->where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id)
        ->get();

    return view('pages.team_list', compact('team_leaders', 'admin_user', 'abc'));
}

    public function agm_team_submit(Request $request,$id){
        //dd($request->all());

        $agm = Admin_user::find($id);
        if (!empty($request->team)) {
            $permission = implode('-',$request->team);
        }else{
            $permission = null;
        }
        $agm->agm_permission = $permission;
        // dd($agm);
        $agm->save();
        return redirect()->back()->with(['success' => 'Access Given Successfully']);
    }

public function agm_team_leaders($id)
{
    $abc = Admin_user::where('au_id', $id)->first();
    $def = explode('-', $abc->agm_permission);

    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $team_leaders = Admin_user::where('au_user_type', '5')
        ->where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id)
        ->whereIn('au_team_id', $def)
        ->get();

    return view('pages.agm_team_list', compact('team_leaders'));
}


    public function userFlexiPermission(Request $request,$id){
        $user = Admin_user::where('au_id',$id)->first();
        // $user->au_flexi_status = $request->status;
        $user->au_flexi_balance = $request->balance;
        $user->save();
        return redirect()->back()->with(['success' => 'Flexi Data Updated Successfully']);
    }

    public function userFlexiload(Request $request){
        // dd($request->all());
        $pass = md5($request->pass);
        // dd($pass);
        if (Auth::user()->au_flexi_status == 1 && Auth::user()->au_password == $pass) {
            $number = Auth::user()->au_mobile;
        try {
            $result = substr($number, 0, 3);
            if ($result == '019' || $result == '014') {
                $operator = 'blink';
            }elseif ($result == '013' || $result == '017') {
                $operator = 'gp';
            }elseif ($result == '018') {
                $operator = 'robi';
            }elseif ($result == '015') {
                $operator = 'teletalk';
            }

            $userFlexi = new UserFlexiload();
            $userFlexi->user_id = Auth::user()->au_id;
            $userFlexi->company_id = Auth::user()->au_company_id;
            $userFlexi->branch_id = Auth::user()->au_branch_id;
            $userFlexi->amount = Auth::user()->au_flexi_balance;


            $apikey = config('services.flexiload.api_key');      // from .env (FLEXI_API_KEY)
            $amount = Auth::user()->au_flexi_balance;
            $client = new Client();
            $url = "http://sms.iglweb.com/api/v1/send-load?api_key=" . $apikey . "&pin=" . config('services.flexiload.pin') . "&number=" . $number . "&amount=" . $amount . "&number_type=1&operator=" . $operator;
            // dd($url);
            $res = $client->request('GET', $url);

            $ret = $res->getBody();
            $userFlexi->save();
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => 'Something Went Wrong' . $e->getMessage()]);
        }


        return redirect()->back()->with(['success' => 'Flexi Balance Give Successfully']);
        }else {
            return redirect()->back()->with(['notPermitted' => 'You are not Permitted or Password is incorrect']);
        }

    }

    public function userFlexiHistory(Request $request){
        $userHistory = UserFlexiload::with('userDetails')->where('user_id',$request->userData)->get();
        return view('pages.ajax.user-flexi-history',compact('userHistory'));
    }

public function userCompanyFlexiHistory(Request $request)
{
    $userHistory = UserFlexiload::with('userDetails')
        ->where('company_id', $request->companyId)
        ->where('branch_id', $request->branchId) // ✅ added branch filter
        ->get();

    return view('pages.ajax.user-flexi-history', compact('userHistory'));
}


    //employee information
public function employee_list()
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;
    $team_id = Auth::user()->au_team_id;

    $users = Admin_user::where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id) // ✅ added branch condition
        ->orderBy('au_mobile', 'asc')
        ->get();

    return view('employee.employee_list', compact('users'));
}

 public function daodayBirthDate() {
    $today_day = date('d');   // '31'
    $today_month = date('m'); // '10' for October

    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $users = Admin_user::where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id)
        ->whereRaw("SUBSTRING_INDEX(orginal_birthday, '/', 1) = ?", [$today_day])
        ->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(orginal_birthday, '/', -2), '/', 1) = ?", [$today_month])
        ->orderBy('orginal_birthday', 'asc')
        ->get();

    return view('employee.today_birthDay', compact('users'));
}


    // public function employee_dob(){
    //     $com_id = Auth::user()->au_company_id;
    //     $team_id = Auth::user()->au_team_id;
    //     //$users = Admin_user::select('au_name','au_user_id','au_department','orginal_birthday')->get();
    //     $users = Admin_user::where('au_company_id', $com_id)->orderBy('orginal_birthday', 'asc')->get();


    //     return view('employee.employee_dob', compact('users'));
    // }
    public function employee_dob()
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $users = Admin_user::where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id) // ✅ Filter by branch
        ->orderBy('orginal_birthday', 'asc')
        ->get();

    return view('employee.employee_dob', compact('users'));
}

    public function employee_details($id)
    {
        $user = Admin_user::where('au_id',$id)->first();
        $experience = PayrollExperience::where('ex_level','Experienced')->where('user_id', $id)->get();
        $reference = PayrollReferance::where('user_id', $id)->get();

        return view('employee.employee_details', compact('user','experience','reference'));
    }
    //user profile update
    public function profileUpdateStep_1()
    {
        $user = Admin_user::where('au_id',Auth::user()->au_id)->first();
        return view('pages.user_update_steap_1', compact('user'));
    }
    public function user_update_step_1(Request $request, $id)
    {
        $table = Input::all();
        if ($request->input('f_mobile') == $request->input('m_mobile')) {
           return redirect()->back()->with(['error' => 'Father and mother mobile number will not be same.']);
        }
         if ($request->input('nid') == null && $request->input('birth_certificate') == null && $request->input('driving_licence') == null && $request->input('passport') == null) {
            return redirect()->back()->with(['error' => 'You have to give at lest one information of NID or Birth Certificate or Driving Licence or Passport']);
        }

//         $phn_nmbr_valid_1 = substr($request->input('persional_mobile'), 0, 3);
        $phn_nmbr_valid_2 = substr($request->input('f_mobile'), 0, 3);
        $phn_nmbr_valid_3 = substr($request->input('m_mobile'), 0, 3);

        if ($phn_nmbr_valid_2 != '017' && $phn_nmbr_valid_2 != '019' && $phn_nmbr_valid_2 != '013' && $phn_nmbr_valid_2 != '014' && $phn_nmbr_valid_2 != '018' && $phn_nmbr_valid_2 != '016' && $phn_nmbr_valid_2 != '015') {
            return redirect()->back()->with(['error' => 'Your phone number is not valide']);
        }
        if ($phn_nmbr_valid_3 != '017' && $phn_nmbr_valid_3 != '019' && $phn_nmbr_valid_3 != '013' && $phn_nmbr_valid_2 != '014' && $phn_nmbr_valid_3 != '018' && $phn_nmbr_valid_3 != '016' && $phn_nmbr_valid_3 != '015') {
            return redirect()->back()->with(['error' => 'Your phone number is not valide']);
        }
//        $mobile_number_count_1 = strlen($request->input('persional_mobile'));
        $mobile_number_count_2 = strlen($request->input('f_mobile'));
        $mobile_number_count_3 = strlen($request->input('m_mobile'));
//        if ($mobile_number_count_1 != 11) {
//            return redirect()->back()->with(['error' => 'Your phone number must be 11 cherecter']);
//        }
        if ($mobile_number_count_2 != 11) {
            return redirect()->back()->with(['error' => 'Your phone number must be 11 cherecter']);
        }
        if ($mobile_number_count_3 != 11) {
            return redirect()->back()->with(['error' => 'Your phone number must be 11 cherecter']);
        }

        $user = Admin_user::find($id);
//        $user->au_name = $request->input('au_name');
        $user->au_email = $request->input('au_email');
//        $user->persional_mobile = $request->input('persional_mobile');
        $user->f_name = $request->input('f_name');
        $user->f_mobile = $request->input('f_mobile');
        $user->m_name = $request->input('m_name');
        $user->m_mobile = $request->input('m_mobile');
        $user->au_address = $request->input('au_address');
        $user->au_parmament_address = $request->input('au_parmament_address');
        $user->nid = $request->input('nid');
        $user->orginal_birthday = $request->input('orginal_birthday');
        $user->cerfiticate_birthday = $request->input('cerfiticate_birthday');
        $user->birth_certificate = $request->input('birth_certificate');
        $user->driving_licence = $request->input('driving_licence');
        $user->passport = $request->input('passport');
        $user->nationality = $request->input('nationality');
        $user->divison = $request->input('divison');
        $user->district = $request->input('district');
        $user->thana = $request->input('thana');
        if ($request->hasFile('au_company_img')) {
            $file = $request->file('au_company_img');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . "." . $extension;

            // Saving the file to 'storage/app/public/image' directory
            $file->storeAs('public/image', $filename);

            $user->au_company_img = $filename;
        }

        $user->save();

        return redirect()->route('addReferance')->with(['msg' => 'Your Profile Updated Successfully']);
    }

    public function addReferance()
    {
        return view('pages.user_update_steap_2');
    }
    public function user_update_step_2(Request $request)
    {
        //$total_insert = count($request->input('ref_name'));

        $table = Input::all();
        foreach ($request->input('ref_name') as $key => $value) {
            $ref = new PayrollReferance();
            $ref->user_id = Auth::user()->au_id;
            $ref->ref_name = $request->input('ref_name')[$key];
            $ref->ref_number = $request->input('ref_number')[$key];
            $ref->company_name = $request->input('company_name')[$key];
            $ref->company_address = $request->input('company_address')[$key];
            $ref->designation = $request->input('designation')[$key];
            $ref->save();
        }

        return redirect()->route('addExperience')->with(['msg' => 'Your Profile Updated Successfully']);

    }
    //user experience
    public function addExperience()
    {
        return view('pages.user_update_steap_3');
    }
    public function user_update_step_3(Request $request)
    {
       // return $request;
       $table = Input::all();
        if($request->input('ex_level') == 'Experienced'){
            $request->validate([
                'ex_level' => 'required',
                'company_name' => 'required',
                'designation' => 'required',
                'company_address' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'dep_head_name' => 'required',
                'dep_head_phn' => 'required',
                'leave_reason' => 'required',
            ]);
            foreach ($request->input('company_name') as $key => $value) {
                $ex = new PayrollExperience();
                $ex->user_id = Auth::user()->au_id;
                $ex->ex_level = $request->input('ex_level');
                $ex->company_name = $request->input('company_name')[$key];
                $ex->designation = $request->input('designation')[$key];
                $ex->company_address = $request->input('company_address')[$key];
                $merge = array($request->input('start_date')[$key],$request->input('end_date')[$key]);
                $ex->duration = implode(',',$merge);;
                $ex->dep_head_name = $request->input('dep_head_name')[$key];
                $ex->dep_head_phn = $request->input('dep_head_phn')[$key];
                $ex->leave_reason = $request->input('leave_reason')[$key];
                $ex->save();
            }
        }else{
                $ex = new PayrollExperience();
                $ex->user_id = Auth::user()->au_id;
                $ex->ex_level = $request->input('ex_level');
                $ex->save();

        }
        $id = Auth::user()->au_id;
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $depts = Department::all();
        $shifts = PayrollShiftManagement::where('company_id',$com_id)->where('branch_id',$branch_id)->where('status',1)->get();
        $user_infos = Admin_user::find($id);

        session()->flash('type', 'success');
        session()->flash('msg', 'Your Profile Updated Successfully');

        return redirect('user_profile/'.$id)->with(['msg' => 'Your Experience Updated Successfully']);

        // return view('pages.user_profile',compact('user_infos','depts','shifts'));

        //return redirect()->route('dashboard')->with(['msg' => 'Your Profile Updated Successfully']);

    }
    public function experienceDayCount(Request $request)
    {
        $date1 = Carbon::createFromFormat('Y-m-d', $request->startDate);
        $date2 = Carbon::createFromFormat('Y-m-d', $request->endDate);

        $interval = $date1->diffInDays($date2);
        $dd = date_diff($date1,$date2);

        return $dd->y."Y ".$dd->m."M ".$dd->d."D";
    }

    public function experienceEdit($id)
    {
        $ex = PayrollExperience::find($id);
        return view('pages.user_update_experience',compact('ex'));
    }
    public function experienceUpdate(Request $request , $id)
    {
        $ex = PayrollExperience::find($id);
        $ex->company_name = $request->input('company_name');
        $ex->designation = $request->input('designation');
        $ex->company_address = $request->input('company_address');
        $merge = array($request->input('start_date'),$request->input('end_date'));
        $ex->duration = implode(',',$merge);;
        $ex->dep_head_name = $request->input('dep_head_name');
        $ex->dep_head_phn = $request->input('dep_head_phn');
        $ex->leave_reason = $request->input('leave_reason');
        $ex->save();
        return redirect()->back()->with(['msg' => 'Your Experience Updated Successfully']);
    }
      //reference update
    public function referenceEdit($id)
    {
        $ref = PayrollReferance::find($id);
        return view('pages.user_update_reference',compact('ref'));
    }
    public function referenceUpdate(Request $request , $id)
    {
        $ref = PayrollReferance::find($id);
        $ref->ref_name = $request->input('ref_name');
        $ref->ref_number = $request->input('ref_number');
        $ref->company_name = $request->input('company_name');
        $ref->company_address = $request->input('company_address');
        $ref->designation = $request->input('designation');
        $ref->save();
        return redirect()->back()->with(['msg' => 'Your Reference Updated Successfully']);
    }
    public function distic_list_by_divison_id(Request $request)
    {
        $districts = DB::table('districts')->where('division_id', $request->divison_id)->get();
        return $districts;
    }
    public function thana_list_by_district_id(Request $request)
    {
        $upazilas = DB::table('upazilas')->where('district_id', $request->district_id)->get();
        return $upazilas;
    }

     //user profile update 2
    public function myInfoUpdate_1()
    {
         $user = Admin_user::where('au_id',Auth::user()->au_id)->first();
        return view('profile_update.myInfoUpdate_1',compact('user'));
    }
    public function myInfoUpdate_1_update(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'au_email' => 'required|email',
            'persional_mobile' => 'required|digits:11',
            'au_company_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
             'au_blood_group' => 'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ]);

        // Validate the phone number prefix
        $validPrefixes = ['017', '019', '013','014', '018', '016', '015'];
        $phonePrefix = substr($request->input('persional_mobile'), 0, 3);
        if (!in_array($phonePrefix, $validPrefixes)) {
            return redirect()->back()->with(['error' => 'Your phone number is not valid']);
        }

        // Find the authenticated user
        $user = Admin_user::find(Auth::user()->au_id);

        // Update user fields
        $user->au_name = $request->input('au_name');
        $user->au_email = $request->input('au_email');
        $user->persional_mobile = $request->input('persional_mobile');
        $user->au_created_date = $request->input('au_created_date');
$user->au_blood_group = $request->input('au_blood_group');
        // Check and handle the image upload
        if ($request->hasFile('au_company_img')) {
            $image = $request->file('au_company_img');

            // Define the old file path and delete if exists
            if (!empty($user->au_company_img)) {
                $file_path = public_path('profile/' . $user->au_company_img);
            
                if (File::exists($file_path) && File::isFile($file_path)) {
                    unlink($file_path);
                }
            }


            // Generate a new file name
            $imageName = $user->au_name . '-'.time().'.'.$request->file('au_company_img')->getClientOriginalExtension();
            $image->move(public_path('profile'), $imageName);

            // Update the user's image path
            $user->au_company_img = '' . $imageName;
        }

        // Save user
        $user->save();

        // Redirect with a success message
        return redirect()->route('page.hrm.user_profile', ['id' => $user->au_id]);
    }

    public function myBioUpdate_1()
    {
        $user = Admin_user::where('au_id',Auth::user()->au_id)->first();
       // return $user->divison;

         $districts_name = DB::table('districts')->where('division_id','=',$user->divison)->get();
         $thana_name = DB::table('upazilas')->where('district_id','=',$user->district)->get();

        return view('profile_update.mybioUpdate_1',compact('user','districts_name','thana_name'));
    }
    public function myBioUpdate_1_update(Request $request)
    {
        $table = Input::all();


        $orginal_birth_date = $request->orginal_birthday;


        $certificate_date = $request->cerfiticate_birthday;

        if ($request->input('f_mobile') != null && $request->input('m_mobile') != null) {
            if ($request->input('f_mobile') == $request->input('m_mobile')) {
                return redirect()->back()->with(['error' => 'Father and mother mobile number cannot be the same.']);
            }
        }

        if ($request->input('spouse_name') == null && $request->input('f_mobile') == null && $request->input('m_mobile') == null) {
            return redirect()->back()->with(['error' => 'Father or Mother Number is Required']);
        }

        if ($request->input('nid') == null && $request->input('birth_certificate') == null && $request->input('driving_licence') == null && $request->input('passport') == null) {
            return redirect()->back()->with(['error' => 'You must provide at least one piece of identification: NID, Birth Certificate, Driving Licence, or Passport']);
        }

        if ($request->input('f_mobile') != null) {
            $phn_nmbr_valid_2 = substr($request->input('f_mobile'), 0, 3);

            if (!in_array($phn_nmbr_valid_2, ['017', '019', '013', '014', '018', '016', '015'])) {
                return redirect()->back()->with(['error' => 'Your Father number is not valid']);
            }
        }

        if ($request->input('m_mobile') != null) {
            $phn_nmbr_valid_3 = substr($request->input('m_mobile'), 0, 3);

            if (!in_array($phn_nmbr_valid_3, ['017', '019', '013', '014', '018', '016', '015'])) {
                return redirect()->back()->with(['error' => 'Your Mother Contact does not match any Bangladeshi SIM Operator']);
            }

            $mobile_number_count_3 = strlen($request->input('m_mobile'));
            if ($mobile_number_count_3 != 11) {
                return redirect()->back()->with(['error' => 'Your Mobile Contact must be 11 characters']);
            }
        }

        $mobile_number_count_2 = strlen($request->input('f_mobile'));

        if ($request->input('f_mobile') != null) {
            if ($mobile_number_count_2 != 11) {
                return redirect()->back()->with(['error' => 'Your Father Contact must be 11 characters']);
            }
        }

        $user = Admin_user::find(Auth::user()->au_id);
        $user->f_name = $request->input('f_name');
        $user->f_mobile = $request->input('f_mobile');
        $user->spouse = $request->input('spouse_name');
        $user->m_name = $request->input('m_name');
        $user->m_mobile = $request->input('m_mobile');
        $user->au_address = $request->input('au_address');
        $user->au_parmament_address = $request->input('au_parmament_address');
        $user->nid = $request->input('nid');
        $user->orginal_birthday = $orginal_birth_date;
        $user->cerfiticate_birthday = $certificate_date;
        $user->birth_certificate = $request->input('birth_certificate');
        $user->driving_licence = $request->input('driving_licence');
        $user->passport = $request->input('passport');
        $user->nationality = $request->input('nationality');
        $user->divison = $request->input('divison');
        $user->district = $request->input('district');
        $user->thana = $request->input('thana');
        $user->save();


        return redirect()->route('page.hrm.user_profile', ['id' => $user]);

    }
    public function send_otp(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string',
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        // Retrieve authenticated user's company ID
        $com = Auth::user()->au_company_id;
         $branch_id = Auth::user()->au_branch_id;
        $sms_setup = Admin_user::where('au_company_id', $com)
        ->where('au_branch_id', $branch_id)
            ->where('au_user_type', 4)
            ->first();

        if (!$sms_setup) {
            return response()->json(['error' => 'SMS setup not found'], 404);
        }

        $sender_id = $sms_setup->au_sender_id;
        $api_key = $sms_setup->au_api_key;

        // Log the api_key and sender_id for debugging (consider removing in production)
        Log::info('API Key: ' . $api_key);
        Log::info('Sender ID: ' . $sender_id);

        $mobile_number = $request->input('mobile');
        $code = str_shuffle(substr('0123456789', 0, 4));
        session(['key' => $code]);
        $message = "Your Verification Code is " . $code;

        // Ensure $message is a string before using urlencode
        if (!is_string($message)) {
            Log::error('Message is not a string');
            return response()->json(['error' => 'Internal server error'], 500);
        }

        $api_url = "http://sms.iglweb.com/api/v1/send?api_key=" . $api_key . "&senderid=" . $sender_id . "&contacts=" . $mobile_number . "&msg=" . urlencode($message);

        $client = new Client();
        try {
            $response = $client->get($api_url);

            // Convert the response body to a string if it is an array or object
            $responseBody = (string) $response->getBody();
            Log::info('API Response: ' . $responseBody);

            // Check if the response status is OK
            if ($response->getStatusCode() === 200) {
                return response()->json(['status' => 'success', 'message' => 'OTP sent successfully']);
            } else {
                // Handle unexpected response status
                Log::error('Unexpected response status: ' . $response->getStatusCode());
                return response()->json(['error' => 'Unexpected response from SMS service'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP', 'details' => $e->getMessage()], 500);
        }
    }
    public function changeName(Request $request){
        //return $request;

        if ($request->session()->has('key')) {
            if (session('key') == $request->verification_code){
                //update name
                $user = Admin_user::find(Auth::user()->au_id);
                $user_name = $user->au_name;

               $user->au_name = $request->name;
               $user->save();
               //send log to 999 number
                $com = Auth::user()->au_company_id;
                $branch_id = Auth::user()->au_branch_id;
                $sms_setup = Admin_user::where('au_company_id',$com)
                ->where('au_branch_id',$branch_id)
                    ->where('au_user_type',4)
                    ->first();
                $sender_id = $sms_setup->au_sender_id;
                $api_key = $sms_setup->au_api_key;

                $mobile_number = '01958666999';
                $old_name = $user_name;


                $message= $user_name. ' has change his name to: '. $request->name; ;

                $api_url = "http://sms.iglweb.com/api/v1/send?api_key=" . $api_key . "&senderid=" . $sender_id . "&contacts=" . $mobile_number . "&msg=". $message;

                $client = new GuzzleHttp\Client();
                $response = $client->get($api_url);
                $ret = $response->getBody();

                return response()->json([
                    'message' => 'Name change successfully',
                    'state' => '400',
                ]);

            }
            return response()->json([
                'error' => 'Verification code invalid',
                'state' => '500',
            ]);
         //   return redirect()->back()->with(['error' => 'Verification code invalid']);

        }
    }
    public function change_password(Request $request)
    {
        $user = Admin_user::find(Auth::user()->au_id);
        $old = (string) $request->input('old_password');
        $new = (string) $request->input('new_password');
        $confirm = $request->input('confirm_password');

        $fail = function ($message, $field) {
            // 'state' kept for older pages: '401' meant success there
            return response()->json(['ok' => false, 'field' => $field, 'message' => $message, 'state' => '500'], 422);
        };

        if ($old === '' || $new === '') {
            return $fail('Please fill in every field.', $old === '' ? 'old_password' : 'new_password');
        }
        if (md5($old) !== $user->au_password) {
            return $fail('Your current password is not correct.', 'old_password');
        }
        if (strlen($new) < 6) {
            return $fail('The new password must have at least 6 characters.', 'new_password');
        }
        if ($confirm !== null && $new !== (string) $confirm) {
            return $fail('The new password and its confirmation do not match.', 'confirm_password');
        }
        if (md5($new) === $user->au_password) {
            return $fail('The new password is the same as the current one.', 'new_password');
        }

        $user->au_password = md5($new);   // same hashing the login uses
        $user->save();

        return response()->json(['ok' => true, 'message' => 'Password changed. Use the new password next time you log in.', 'state' => '401']);
    }

    // System Settings
    public function systemSettings() {

        $user_id = Auth::user()->au_id;
        $user = Admin_user::find($user_id);
        $depts = Department::all();

        return view ('page.system-settings', compact('user','depts'));
    }


    public function cron_user_deactivate()
    {
        $start_date = Carbon::now()->subDays(5);
        $end_date = Carbon::now();

        // Get active user IDs
        $activeUserIds = EmployeeAttendance::whereBetween('check_in', [$start_date, $end_date])
            ->groupBy('user_id')
            ->pluck('user_id');

        // Inactivate users
        Admin_User::whereNotIn('au_id', $activeUserIds)
            ->whereNotIn('au_id', [1, 2, 64, 59])
            ->whereNot('au_department', '2')
            ->get()
            ->each(function ($user) {
                $user->au_status = 0;
                $user->save();
            });

        return response('', 200);
    }

    public function reason()
    {
        $reasons = DB::table('reasons')->get();
        return view('root.reason',compact('reasons'));
    }

    public function reason_add(Request $request)
    {
        DB::table('reasons')->insert(['name' => $request->name]);
        return back()->with('success','Reason added successfully');
    }

    public function reason_update(Request $request)
    {
        DB::table('reasons')->where('id',$request->id)->update(['name' => $request->name]);
        return back()->with('update','Reason updated successfully');
    }

    public function reason_delete($id)
    {
        DB::table('reasons')->delete($id);
        return back()->with('delete','Reason deleted successfully');
    }

    public function mode()
    {
        $modes = DB::table('conveyance_modes')->get();
        return view('root.mode',compact('modes'));
    }

    public function mode_add(Request $request)
    {
         DB::table('conveyance_modes')->insert(['name' => $request->name]);
        return back()->with('success','Mode added successfully');

    }
    public function mode_update(Request $request)
    {
        DB::table('conveyance_modes')->where('id',$request->id)->update(['name' => $request->name]);
        return back()->with('update','Mode updated successfully');
    }
    public function mode_delete($id)
    {
        DB::table('conveyance_modes')->delete($id);
        return back()->with('delete','Mode deleted successfully');
    }
 public function employee_bg() {
        $com_id = Auth::user()->au_company_id;
         $branch_id = Auth::user()->au_branch_id;
        // Retrieve users with 'au_user_id' and 'au_blood_group' fields ordered by 'au_blood_group'
        $users = Admin_user::where('au_company_id', $com_id)
                        ->where('au_branch_id', $branch_id)
                           ->orderBy('au_blood_group', 'asc')
                           ->select('au_user_id', 'au_blood_group', 'au_name', 'au_department')
                           ->get();
    
        // Fetch blood donation records and their status for each user
        $bloodDonationRecords = EmployeeBloodDonate::whereIn('employee_id', $users->pluck('au_user_id'))
                                                   ->get();
    
        // Combine user data with their blood donation status
        $usersWithBloodDonationStatus = $users->map(function ($user) use ($bloodDonationRecords) {
            $donationRecord = $bloodDonationRecords->firstWhere('employee_id', $user->au_user_id);
    
            // If no donation record exists, set the status as 'Available' by default
            $user->donation_status = $donationRecord ? $donationRecord->status : 'Available';
            
            return $user;
        });
    
        // Return the view with users data
        return view('employee.employee_bg', compact('usersWithBloodDonationStatus'));
    }
    public function getBloodDonationUsers()
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    if (Auth::user()->au_user_type == 4) {
        // Admin: Get all employees in same company and branch with donation records
        $donations = EmployeeBloodDonate::with('adminUser')
            ->whereHas('adminUser', function ($query) use ($com_id, $branch_id) {
                $query->where('au_company_id', $com_id)
                      ->where('au_branch_id', $branch_id); // ✅ Filter by branch
            })
            ->orderBy('donate_date', 'desc')
            ->get();
    } else {
        // Regular user: only their own donation record
        $donations = EmployeeBloodDonate::with('adminUser')
            ->whereHas('adminUser', function ($query) {
                $query->where('au_user_id', Auth::user()->au_user_id);
            })
            ->orderBy('donate_date', 'desc')
            ->get();
    }

    return view('blood_donates.donated_users', compact('donations'));
}

    

    public function bloodDonate()
    {
        // Get the company ID and user ID of the authenticated user
        $com_id = Auth::user()->au_company_id;
        $user_id = Auth::user()->au_user_id;
        $branch_id = Auth::user()->au_branch_id;
        // Check if the user is an admin (au_user_type == 4)
        if (Auth::user()->au_user_type == 4) {
            // If the user is an admin, retrieve all users within the company and their donations
            $users = Admin_user::where('au_company_id', $com_id)
                              ->where('au_branch_id', $branch_id)
                               // Assuming donation relationship exists
                               ->orderBy('au_blood_group', 'asc')
                               ->select('au_user_id', 'au_blood_group', 'au_name', 'au_department')
                               ->get();
        } else {
            // If the user is not an admin, only retrieve their own record and donation
            $users = Admin_user::where('au_user_id', $user_id)
                               ->where('au_company_id', $com_id)
                               ->where('au_branch_id', $branch_id)
                               ->select('au_user_id', 'au_blood_group', 'au_name', 'au_department')
                               ->get();
        }
    
        // Return the users to the view with the form
        return view('blood_donates.employee_blooddonate', compact('users', 'user_id'));
    }
    
    /**
     * Store a blood donation record.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function storeBloodDonation(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'employee_id' => 'required|exists:admin_user,au_user_id',
            'donate_date' => 'required|date_format:d/m/Y', // Ensure the date is in dd-mm-yyyy format
            'for_whom' => 'nullable|string|max:255',
        ]);
        
        // Convert the donate_date to the correct format (yyyy-mm-dd)
        $donateDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->donate_date)->format('Y-m-d');
    
        // Get the authenticated user's company ID
        $com_id = Auth::user()->au_company_id;
         $branch_id = Auth::user()->au_branch_id;
        // Create a new blood donation record
        $bloodDonation = new EmployeeBloodDonate();
        $bloodDonation->employee_id = $request->employee_id;
        $bloodDonation->donate_date = $donateDate;  // Use the formatted date
        $bloodDonation->for_whom = $request->for_whom;
        
        // Assuming checkStatus needs both the donate date and employee_id
        
    
        $bloodDonation->save();
    
        // Optionally, you can return a success response or redirect to another page
        return redirect()->route('payroll.blood_donates.index')->with('success', 'Blood donation record has been successfully created.');
    }
    
    /**
     * Show the form for editing a blood donation record.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function editBloodDonation($id)
{
    // Retrieve the blood donation record by ID
    $bloodDonation = EmployeeBloodDonate::findOrFail($id);

    // Get the company ID of the authenticated user
    $com_id = Auth::user()->au_company_id;
     $branch_id = Auth::user()->au_branch_id;
    // Retrieve users within the same company, ordered by 'au_blood_group'
    $users = Admin_user::where('au_company_id', $com_id)
                       ->where('au_branch_id', $branch_id)
                       ->orderBy('au_blood_group', 'asc')
                       ->select('au_user_id', 'au_blood_group', 'au_name', 'au_department')
                       ->get();

    // Format the donation date as 'd-m-Y'
    $bloodDonation->donate_date = \Carbon\Carbon::parse($bloodDonation->donate_date)->format('d-m-Y');

    // Return the edit form with the current blood donation record and user list
    return view('blood_donates.edit_employee_blooddonate', compact('bloodDonation', 'users'));
}

    /**
     * Update the specified blood donation record.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateBloodDonation(Request $request, $id)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:admin_user,au_user_id', // Ensure employee exists
            'donate_date' => 'required|date_format:d-m-Y',  // Validate the date format
            'for_whom' => 'required|string',
        ]);
    
        // Retrieve the record to update
        $bloodDonation = EmployeeBloodDonate::findOrFail($id);
    
        // Convert the donate_date to 'Y-m-d' for database storage
        $donateDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->donate_date)->format('Y-m-d');
    
        // Update the blood donation record
        $bloodDonation->update([
            'employee_id' => $request->employee_id,
            'donate_date' => $donateDate, // Store the date in 'Y-m-d' format
            'for_whom' => $request->for_whom,
        ]);
    
        // Redirect to the list page with a success message
        return redirect()->route('payroll.blood_donates.index')->with('success', 'Blood donation record has been successfully updated.');
    }
    /**
     * Delete the specified blood donation record.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function deleteBloodDonation($id)
    {
        // Find the blood donation record by ID and delete it
        $bloodDonation = EmployeeBloodDonate::findOrFail($id);
        $bloodDonation->delete();

        // Redirect to the list page with success message
        return redirect()->route('payroll.blood_donates.index')->with('success', 'Blood donation record has been successfully deleted.');
    }

  }

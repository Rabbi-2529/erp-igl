<?php

namespace App\Http\Controllers;
use Rats\Zkteco\Lib\ZKTeco;
use Auth;
use Carbon\Carbon;
use DateTime;
use App\Models\EmployeeAttendance;
use App\Models\Admin_user;
use App\Models\Weekends;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\PayrollShiftManagement;
use DB;
use PDF;


use Illuminate\Http\Request;

class AttendanceController extends Controller
{

public function attendanceCheckIn(Request $request){
    $checkOutTime = Carbon::now()->format('Y-m-d H:i:s');
    $empId = Auth::user()->au_id;
    $com = Auth::user()->au_company_id;
      $branch = Auth::user()->au_branch_id;
    $checkIn = Carbon::now()->format('Y-m-d H:i:s');
    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $branchDetails = Admin_user::where('au_branch_id', $branch)->first();
    $expectedIpAddress = explode(',', $branchDetails->au_ip_address);
    $userIpAddress = $_SERVER['REMOTE_ADDR'];

    if (!$request->has('reason')) {
        if (!in_array($userIpAddress, $expectedIpAddress)) {
            return redirect()->back()->with('ip_validation', true);
        }
    }

    $au_user = Admin_user::where('au_id', $empId)->first();
    if ($au_user->au_shifts != null) {
        $auWorkshift = PayrollShiftManagement::where('id', $au_user->au_shifts)->first();
        $workShift = explode(',', $auWorkshift->working_hour);
    } else {
        $workShift = explode(',', $branchDetails->au_work_shift);
    }

    $begin = strlen($workShift[0]);
    $lateCheckIn = $workShift[0];

    $checkEntry = PayrollShiftManagement::where('id', 1)->get(['late_entry']);
    foreach ($checkEntry as $checkEntry) {
        $checkVal = (int) $checkEntry->late_entry;
    }

    $dbtimestamp = strtotime($lateCheckIn);
    if (time() - $dbtimestamp > $checkVal * 60) {
        $status = 1;
        $message = "You are Late";
        $type = "danger";
    } else if (!$dbtimestamp) {
        $status = 2;
    } else {
        $status = 3;
        $message = "You are Entering in the exact Working hour";
        $type = "success";
    }

    $selectedReason = $request->input('reason');
    $selectedForWhom = $request->input('for_whom');

    $present = new EmployeeAttendance();
    $present->company_id = $com;
    $present->user_id = $empId;
    $present->branch_id = Auth::user()->au_branch_id; // ✅ Added this line
    $present->check_in = $checkIn;
    $present->in_click = 1;
    $present->company_ip = Auth::user()->au_company_ip;
    $present->user_ip = $userIpAddress;
    $present->au_checkin_reason = $selectedReason;
    $present->for_whom = $selectedForWhom;
    $present->status = $status;
    $present->save();

    return redirect()->back()->with(['msg' => $message , 'type' => $type]);

    // session(['ip_validation' => true]); // This line won't be reached — consider moving above return if needed
}


public function attendanceCheckOut(Request $request)
{
    $empId = Auth::user()->au_id;
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id; // Get branch_id
    $checkOutTime = Carbon::now()->format('Y-m-d H:i:s');

    // Fetch today's check-in record by user, company and branch
    $checkOut = EmployeeAttendance::where('company_id', $com)
                                  ->where('branch_id', $branch)
                                  ->where('user_id', $empId)
                                  ->orderBy('check_in', 'desc')
                                  ->first();

    $out = date('Y-m-d', strtotime($checkOut->check_in));
    $today = Carbon::now()->format('Y-m-d');
    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $branchDetails = Admin_user::where('au_branch_id', $branch)->first();
    $au_user = Admin_user::where('au_id', $empId)->first();
    if ($au_user->au_shifts != null) {
        $auWorkshift = PayrollShiftManagement::where('id', $au_user->au_shifts)->first();
        $workShift = explode(',', $auWorkshift->working_hour);
    } else {
        $workShift = explode(',', $branchDetails->au_work_shift);
    }

    $lateCheckIn = $workShift[0];
    $checkOutOver = $workShift[1];

    $outTime = Carbon::now()->format('H:i:s');
    $date1 = Carbon::parse($checkOut->check_in);
    $date2 = Carbon::parse($outTime);
    $difference = $date1->diffInMinutes($date2);

    $date3 = new DateTime($checkOut->check_in);
    $date4 = new DateTime($outTime);
    $difference1 = $date3->diff($date4);

    if ($out == $today) {
        $checkOut->check_out = $checkOutTime;

        if ($difference < $branchDetails->au_working_hour) {
            $checkOut->incomplete_hour = $difference1->h;
            $checkOut->incomplete_minute = $difference1->i;
            $checkOut->incomplete_sec = $difference1->s;
            $checkOut->status = 4; // Incomplete Shift
            $type = "warning";
            $message = "Your Shift is Incomplete";
        } elseif ($difference > $branchDetails->au_working_hour) {
            $checkOut->over_hour = $difference1->h;
            $checkOut->over_minute = $difference1->i;
            $checkOut->over_sec = $difference1->s;
            $checkOut->status = 5; // Over Hour
            $type = "success";
            $message = "You Have Worked Extra Hours";
        } else {
            $checkOut->total_hour = $difference1->h;
            $checkOut->status = 6; // Perfect
            $type = "success";
            $message = "You have completed your shift perfectly";
        }

        $checkOut->out_click = 2;
        $checkOut->user_ip_checkout = $_SERVER['REMOTE_ADDR'];
        $checkOut->save();
    }

    return redirect()->back()->with(['checkout' => $message, 'type' => $type]);
}




   public function attendanceShow(Request $request)
{
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;
    $userType = Auth::user()->au_user_type;
    $uid = Auth::user()->au_id;

    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $branchDetails = Admin_user::where('au_branch_id', $branch)->first();
    $expectedIp = $branchDetails->au_ip_address;
    $expectedIpAddress = $branchDetails->au_ip_address;

    $shiftTime = explode(',', $branchDetails->au_work_shift);
    $expectedCheckIn = $shiftTime[0];

    $request_month = $request->month ?? Carbon::now()->format('m');
    $request_year = $request->year ?? Carbon::now()->format('Y');

    $start_date = $request_year . '-' . $request_month . '-01';
    $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');

    $lateCheckIns = EmployeeAttendance::where('company_id', $com)
        ->where('branch_id', $branch)
        ->whereMonth('created_at', $request_month)
        ->whereYear('created_at', $request_year)
        ->whereTime('check_in', '>', $expectedCheckIn)
        ->get();

    $monthLeave = Leave::where('company_id', $com)
        ->where('branch_id', $branch)
        ->whereMonth(DB::raw('STR_TO_DATE(leave_date_accept_start, "%d/%m/%Y")'), '=', $request_month)
        ->whereYear(DB::raw('STR_TO_DATE(leave_date_accept_start, "%d/%m/%Y")'), '=', $request_year)
        ->get();

    $usersAttendance = EmployeeAttendance::where('company_id', $com)
        ->where('branch_id', $branch)
        ->where('user_id', $uid)
        ->whereMonth('created_at', $request_month)
        ->whereYear('created_at', $request_year)
        ->get();

    $totalWeekends = 0;
    $weekends = [];
    for ($day = 1; $day <= $days_in_month; $day++) {
        $currentDate = Carbon::create($request_year, $request_month, $day);
        if ($currentDate->dayOfWeek == Carbon::FRIDAY) {
            $totalWeekends++;
            $weekends[] = $currentDate->toDateString();
        }
    }

    $holidays = Holiday::where('company_id', $com)
        ->where('branch_id', $branch)
        ->where('year', $request_year)
        ->where('month', $request_month)
        ->get()
        ->map(function ($holiday) use ($request_year, $request_month) {
            return Carbon::create($request_year, $request_month, $holiday->date)->toDateString();
        })
        ->toArray();

    $nonWorkingDays = array_merge($weekends, $holidays);

    // Calculate attendance data for graph
    $attendanceData = [];
    $consecutiveAbsences = 0;
    $maxConsecutiveAbsences = 0;
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $currentDate = Carbon::create($request_year, $request_month, $day);
        $dateString = $currentDate->toDateString();
        
        // Check if it's a working day
        $isWorkingDay = !in_array($dateString, $nonWorkingDays);
        
        if ($isWorkingDay) {
            // Check if attendance exists for this day
            $attendance = $usersAttendance->filter(function($att) use ($dateString) {
                return Carbon::parse($att->created_at)->toDateString() == $dateString;
            })->first();
            
            $isPresent = !is_null($attendance);
            $isLate = false;
            
            if ($isPresent && $attendance) {
                $checkInTime = strtotime($attendance->check_in);
                $expectedTime = strtotime($expectedCheckIn);
                $isLate = ($checkInTime - $expectedTime) > (15 * 60);
            }
            
            if (!$isPresent) {
                $consecutiveAbsences++;
                if ($consecutiveAbsences > $maxConsecutiveAbsences) {
                    $maxConsecutiveAbsences = $consecutiveAbsences;
                }
            } else {
                $consecutiveAbsences = 0;
            }
            
            $attendanceData[] = [
                'date' => $day,
                'date_string' => $dateString,
                'is_working_day' => $isWorkingDay,
                'is_present' => $isPresent,
                'is_late' => $isLate,
                'is_weekend' => in_array($dateString, $weekends),
                'is_holiday' => in_array($dateString, $holidays),
                'consecutive_absences' => $consecutiveAbsences
            ];
        }
    }
    
    // Calculate statistics
    $totalWorkingDays = count(array_filter($attendanceData, function($day) {
        return $day['is_working_day'];
    }));
    
    $presentDays = count(array_filter($attendanceData, function($day) {
        return $day['is_working_day'] && $day['is_present'];
    }));
    
    $absentDays = $totalWorkingDays - $presentDays;
    $lateDays = count(array_filter($attendanceData, function($day) {
        return $day['is_working_day'] && $day['is_present'] && $day['is_late'];
    }));
    
    $showDangerAlert = $maxConsecutiveAbsences >= 3;
    $showLateAlert = $lateDays > 0;

    return view('page.attendance.show', compact(
        'comDetails',
        'branchDetails',
        'expectedIpAddress',
        'expectedIp',
        'usersAttendance',
        'userType',
        'days_in_month',
        'request_year',
        'request_month',
        'lateCheckIns',
        'monthLeave',
        'totalWeekends',
        'nonWorkingDays',
        'holidays',
        'attendanceData',
        'presentDays',
        'absentDays',
        'lateDays',
        'totalWorkingDays',
        'showDangerAlert',
        'showLateAlert',
        'maxConsecutiveAbsences'
    ));
}


   public function attendanceTotalShow(Request $request)
{
    // Get company and branch info
    $com = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;
    $userType = Auth::user()->au_user_type;
    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $branchDetails = Admin_user::where('au_branch_id', $branchId)->first();
    $userComId = Admin_user::all();
    $uid = Auth::user()->au_id;

    // Shift time for expected check-in
    $shiftTime = explode(',', $branchDetails->au_work_shift);
    $totalWeekends = 0;
    $expectedCheckIn = $shiftTime[0];

    // Requested month/year or current
    if ($request->has('searchbtn')) {
        $request_month = $request->month;
        $request_year = $request->year;
        $start_date = $request_year.'-'.$request_month.'-1';
        $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
    } else {
        $request_month = Carbon::now()->format('m');
        $request_year = Carbon::now()->format('Y');
        $start_date = $request_year.'-'.$request_month.'-1';
        $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
    }

    // Initialize total holidays
    $totalHolidays = 0;

    // Fetch late check-ins
    $lateCheckIns = EmployeeAttendance::where('company_id', $com)
        ->where('branch_id', $branchId)
        ->whereMonth('created_at', $request_month)
        ->whereYear('created_at', $request_year)
        ->whereTime('check_in', '>', $expectedCheckIn)
        ->get();
    $allLeaves = Leave::where('company_id', $com)
        ->where('branch_id', $branchId)
        ->get();
    // Fetch leaves
    $monthLeave = $allLeaves->filter(function($leave) use ($request_month, $request_year) {
        if (empty($leave->leave_date_accept_start)) {
            return false;
        }
        
        try {
            // Check if date is in dd/mm/YYYY format
            if (strpos($leave->leave_date_accept_start, '/') !== false) {
                $date = Carbon::createFromFormat('d/m/Y', $leave->leave_date_accept_start);
            } else {
                $date = Carbon::parse($leave->leave_date_accept_start);
            }
            
            // Filter by month and year
            return $date->month == $request_month && $date->year == $request_year;
        } catch (\Exception $e) {
            return false;
        }
    });

    // Calculate weekends
    for ($day = 1; $day <= $days_in_month; $day++) {
        $currentDate = Carbon::create($request_year, $request_month, $day);
        if ($currentDate->dayOfWeek == Carbon::FRIDAY) {
            $totalWeekends++;
        }
    }

    // Holidays
    $holidays = Holiday::where('company_id', $com)
        ->where('branch_id', $branchId)
        ->where('year', $request_year)
        ->where('month', $request_month)
        ->pluck('date')
        ->toArray();

    $totalHolidays = count($holidays);

    // Check permission
    $hasPermission = DB::table('au_accesses')
        ->where('access_id', 100)
        ->where('status', 1)
        ->where('permission_user_type', Auth::user()->au_user_type)
        ->exists();

    // Attendance summary
    if ($hasPermission) {
        // Same department users
        $usersAttendance = DB::table('employee_attendances')
            ->select(
                'user_id',
                DB::raw('SUM(incomplete_hour) as incom_hour'),
                DB::raw('SUM(incomplete_minute) as incom_minute'),
                DB::raw('SUM(incomplete_sec) as incom_sec'),
                DB::raw('SUM(over_hour) as over_hour'),
                DB::raw('SUM(over_minute) as over_minute'),
                DB::raw('SUM(over_sec) as over_sec'),
                DB::raw('count(check_in) as check_in')
            )
            ->join('admin_user', 'employee_attendances.user_id', '=', 'admin_user.au_id')
            ->whereMonth('employee_attendances.created_at', $request_month)
            ->whereYear('employee_attendances.created_at', $request_year)
            ->where('employee_attendances.company_id', $com)
            ->where('employee_attendances.branch_id', $branchId)
            ->where('admin_user.au_department', Auth::user()->au_department)
            ->groupBy('user_id')
            ->orderBy('user_id', 'asc')
            ->get();
    } else {
        // All users
        $usersAttendance = DB::table('employee_attendances')
            ->select(
                'user_id',
                DB::raw('SUM(incomplete_hour) as incom_hour'),
                DB::raw('SUM(incomplete_minute) as incom_minute'),
                DB::raw('SUM(incomplete_sec) as incom_sec'),
                DB::raw('SUM(over_hour) as over_hour'),
                DB::raw('SUM(over_minute) as over_minute'),
                DB::raw('SUM(over_sec) as over_sec'),
                DB::raw('count(check_in) as check_in')
            )
            ->whereMonth('employee_attendances.created_at', $request_month)
            ->whereYear('employee_attendances.created_at', $request_year)
            ->where('employee_attendances.company_id', $com)
            ->where('employee_attendances.branch_id', $branchId)
            ->groupBy('user_id')
            ->orderBy('user_id', 'asc')
            ->get();
    }

    // Return view
    return view('page.attendance.total-show', compact(
        'comDetails',   'branchDetails','userComId', 'usersAttendance', 'userType',
        'days_in_month', 'request_year', 'request_month',
        'lateCheckIns', 'monthLeave', 'totalWeekends', 'totalHolidays'
    ));
}

  public function getEmployeeDetails(Request $request)
{
    $userid = $request->userid;
    $employee = Admin_user::where('au_id', $userid)->first();
    
    return response()->json($employee);
}  


     //today attendance show
 public function todayAttendanceShow(Request $request)
{
    // Get the late entry settings from PayrollShiftManagement
    $checkEntry = PayrollShiftManagement::where('id', 1)->get(['late_entry']);
    foreach ($checkEntry as $checkEntry) {
        $checkVal = (int)$checkEntry->late_entry;
    }

    // Get company details and user info
    $userComId = Admin_user::all();
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id; // Get branch ID
    $userType = Auth::user()->au_user_type;
    $comDetails = Admin_user::where('au_company_id', $com)->first();
     $branchDetails = Admin_user::where('au_branch_id', $branch)->first();
    $uid = Auth::user()->au_id;

    // Get the department of the logged-in user
    $userDepartment = Auth::user()->au_department;

    // Get the current time period for filtering
    $startTime = Carbon::now()->format('Y-m-d').' 00:00:00';
    $endTime = Carbon::now()->format('Y-m-d').' 23:59:59';

    // Check if the logged-in user has permission with access_id 100
    $hasPermission = DB::table('au_accesses')
        ->where('access_id', 100)
        ->where('permission_user_type', Auth::user()->au_user_type)
        ->where('status', 1)
        ->exists();

    // If the user has permission 100, filter attendance by their department and branch
    if ($hasPermission) {
        $usersAttendance = DB::table('employee_attendances')
            ->select('employee_attendances.*', 'admin_user.au_mobile')
            ->join('admin_user', 'employee_attendances.user_id', '=', 'admin_user.au_id')
            ->where('employee_attendances.company_id', $com)
            ->where('employee_attendances.branch_id', $branch) // Filter by branch
            ->where('admin_user.au_department', $userDepartment)
            ->whereBetween('employee_attendances.created_at', [$startTime, $endTime])
            ->orderBy('admin_user.au_mobile', 'DESC')
            ->get();
    } else {
        $usersAttendance = DB::table('employee_attendances')
            ->select('employee_attendances.*', 'admin_user.au_mobile')
            ->join('admin_user', 'employee_attendances.user_id', '=', 'admin_user.au_id')
            ->where('employee_attendances.company_id', $com)
            ->where('employee_attendances.branch_id', $branch) // Filter by branch
            ->whereBetween('employee_attendances.created_at', [$startTime, $endTime])
            ->orderBy('admin_user.au_mobile', 'DESC')
            ->get();
    }

    return view('page.attendance.today-attendance', compact('comDetails', 'userComId',  'branchDetails', 'usersAttendance', 'userType', 'checkVal'));
}

public function todayIntimeAttendance(Request $request)
{
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id; // Add branch ID
    $userType = Auth::user()->au_user_type;
    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $uid = Auth::user()->au_id;

    $startTime = Carbon::now()->format('Y-m-d').' 00:00:00';
    $endTime = Carbon::now()->format('Y-m-d').' 23:59:59';

    $usersAttendance = EmployeeAttendance::where('company_id', $com)
                            ->where('branch_id', $branch) // Filter by branch
                            ->whereBetween('created_at', [$startTime, $endTime])
                            ->get();

    return view('attendance.today-intime-attendance', compact('comDetails',  'branchDetails', 'usersAttendance', 'userType'));
}

   public function getExistingHolidays(Request $request)
{
    $company_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $month = $request->input('month', date('m'));
    $year = $request->input('year', date('Y'));

    $existingHolidays = Holiday::where('month', $month)
                               ->where('year', $year)
                               ->where('company_id', $company_id)
                               ->where('branch_id', $branch_id) // Filter by branch
                               ->get(['date']);

    return response()->json($existingHolidays);
}

public function officeWeekendHoliday()
{
    $company_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $weekend = Weekends::where('company_id', $company_id)
                       ->where('branch_id', $branch_id) // Filter by branch
                       ->first();

    $holiday = Holiday::where('company_id', $company_id)
                      ->where('branch_id', $branch_id) // Filter by branch
                      ->get();

    return view('page.attendance.weekend', compact('weekend', 'holiday'));
}

  public function officeHoliday(Request $request)
{
    $company_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $existHoliday = Holiday::where('company_id', $company_id)
                           ->where('branch_id', $branch_id) // Filter by branch
                           ->get();

    if ($request->has('searchbtn')) {
        $request_month = $request->month;
        $request_year = $request->year;
        $start_date = $request_year . '-' . $request_month . '-1';
        $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
    } else {
        $request_month = Carbon::now()->format('m');
        $request_year = Carbon::now()->format('Y');
        $start_date = $request_year . '-' . $request_month . '-1';
        $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
    }

    $firstDayOfMonth = Carbon::createFromDate($request_year, $request_month, 1)->dayOfWeek;

    return view('page.attendance.holiday', compact('request_year', 'request_month', 'start_date', 'days_in_month', 'firstDayOfMonth', 'existHoliday'));
}

public function addWeekends(Request $request)
{
    $company_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $request->validate([
        'weekend' => 'required',
    ], [
        'weekend.required' => 'Please select at least one weekend day!',
    ]);

    // Check for existing record based on company and branch
    $holidays = Weekends::where('company_id', $company_id)
                        ->where('branch_id', $branch_id)
                        ->first();

    if ($holidays) {
        $holidays->weekend = implode(',', $request->input('weekend'));
        $holidays->save();
    } else {
        $holidays = new Weekends;
        $holidays->company_id = $company_id;
        $holidays->branch_id = $branch_id;
        $holidays->weekend = implode(',', $request->input('weekend'));
        $holidays->save();
    }

    session()->flash('type', 'success');
    session()->flash('message', 'Holiday ' . ($holidays->wasRecentlyCreated ? 'Added' : 'Updated') . ' Successfully');
    return redirect()->back();
}


  public function storeHolidays(Request $request)
{
    $company_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    $selectedDates = $request->input('selected_dates', []);
    $uncheckedDates = $request->input('unchecked_dates', []);

    $selectedDates = array_map(function ($date) {
        return Carbon::parse($date);
    }, $selectedDates);

    $uncheckedDates = array_map(function ($date) {
        return Carbon::parse($date);
    }, $uncheckedDates);

    // Remove unchecked dates
    if (!empty($uncheckedDates)) {
        foreach ($uncheckedDates as $date) {
            Holiday::where('company_id', $company_id)
                ->where('branch_id', $branch_id)
                ->where('date', $date->day)
                ->where('month', $date->month)
                ->where('year', $date->year)
                ->delete();
        }
    }

    $uniqueDates = [];

    foreach ($selectedDates as $date) {
        $exists = Holiday::where('company_id', $company_id)
            ->where('branch_id', $branch_id)
            ->where('date', $date->day)
            ->where('month', $date->month)
            ->where('year', $date->year)
            ->exists();

        if (!$exists) {
            $uniqueDates[] = [
                'company_id' => $company_id,
                'branch_id' => $branch_id,
                'date' => $date->day,
                'month' => $date->month,
                'year' => $date->year,
                'description' => '',
            ];
        }
    }

    if (!empty($uniqueDates)) {
        Holiday::insert($uniqueDates);
    }

    return response()->json(['success' => 'Holidays saved successfully.']);
}

public function attendanceDetailsAjax(Request $request)
{
    // Fetch company and branch details for the authenticated user
    $company_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    // Fetch company details (optional if used in the view)
    $comDetails = Admin_user::where('au_company_id', $company_id)->first();

    // Fetch user details for the requested user ID within the same company
    $userDet = Admin_user::where('au_company_id', $company_id)
                         ->where('au_id', $request->userid)
                         ->first();

    // Ensure the user exists
    if (!$userDet) {
        abort(404, 'User not found.');
    }

    // Extract user ID and request parameters
    $user = $userDet->au_id;
    $request_year = $request->year;
    $request_month = $request->month;

    // Calculate the start date and total days in the requested month
    $start_date = Carbon::create($request_year, $request_month, 1);
    $days_in_month = $start_date->daysInMonth;

    // Retrieve holidays for the selected month, year, company, and branch
    $holidays = Holiday::where('company_id', $company_id)
                       ->where('branch_id', $branch_id)
                       ->where('year', $request_year)
                       ->where('month', $request_month)
                       ->pluck('date')
                       ->toArray();

    // Pass data to the view
    return view('page.attendance.monthly-report-ajax', compact(
        'comDetails', 'days_in_month', 'request_year', 'request_month', 'user', 'holidays'
    ));
}

    public function addShift(){
        return view('page.hrm.add-shift');
    }

  public function addShiftStore(Request $request)
{
    $message = [
        'shift_name.required' => 'Shift Name is Required',
        'working_shift_end.required' => 'Working Shift End is Required',
        'working_shift_begin.required' => 'Working Shift Begin is Required',
        'shift_h.required' => 'Shift Hour is Required',
        'late.required' => 'Late Time is Required',
        'e_leave.required' => 'Early Leave Time is Required',
    ];

    $request->validate([
        'shift_name' => 'required',
        'working_shift_begin' => 'required',
        'working_shift_end' => 'required',
        'shift_h' => 'required',
        'late' => 'required',
        'e_leave' => 'required',
    ], $message);

    $workingShiftBegin = [date('H:i:s', strtotime($request->working_shift_begin))];
    $workingShiftEnd = [date('H:i:s', strtotime($request->working_shift_end))];

    $workingShift = implode(',', array_merge($workingShiftBegin, $workingShiftEnd));

    $shift = new PayrollShiftManagement();
    $shift->company_id = Auth::user()->au_company_id;
    $shift->branch_id = Auth::user()->au_branch_id; // ✅ Add branch_id here
    $shift->name = $request->shift_name;
    $shift->working_hour = $workingShift;
    $shift->shift_hour = $request->shift_h * 60;
    $shift->late_entry = $request->late;
    $shift->e_leave = $request->e_leave;
    $shift->save();

    session()->flash('type', 'success');
    session()->flash('message', 'Shift Added Successfully');
    return redirect()->back();
}


public function editShift($id)
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $shift = PayrollShiftManagement::where('company_id', $companyId)
                                   ->where('branch_id', $branchId)
                                   ->where('id', $id)
                                   ->firstOrFail(); // Use firstOrFail for safer error handling

    return view('page.hrm.shift-edit', compact('shift'));
}

   public function updateShiftStore(Request $request, $id)
{
    $message = [
        'shift_name.required' => 'Shift Name is Required',
        'working_shift_end.required' => 'Working Shift End is Required',
        'working_shift_begin.required' => 'Working Shift Begin is Required',
        'shift_h.required' => 'Shift Hour is Required',
        'late.required' => 'Late Time is Required',
        'e_leave.required' => 'Early Leave Time is Required',
    ];

    $request->validate([
        'shift_name' => 'required',
        'working_shift_begin' => 'required',
        'working_shift_end' => 'required',
        'shift_h' => 'required',
        'late' => 'required',
        'e_leave' => 'required',
    ], $message);

    $workingShift = implode(',', [
        date('H:i:s', strtotime($request->working_shift_begin)),
        date('H:i:s', strtotime($request->working_shift_end)),
    ]);

    $shift = PayrollShiftManagement::findOrFail($id);
    $shift->company_id = Auth::user()->au_company_id;
    $shift->branch_id = Auth::user()->au_branch_id; // ✅ Add this line
    $shift->name = $request->shift_name;
    $shift->working_hour = $workingShift;
    $shift->shift_hour = $request->shift_h * 60;
    $shift->late_entry = $request->late;
    $shift->e_leave = $request->e_leave;
    $shift->save();

    session()->flash('type', 'success');
    session()->flash('message', 'Shift Updated Successfully');
    return redirect()->back();
}

public function shiftList()
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $shifts = PayrollShiftManagement::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('status', 1)
                ->get();

    return view('page.hrm.manage-shift', compact('shifts'));
}
   public function shiftUpdate(Request $request, $id)
{
    $message = [
        'shift_name.required' => 'Shift Name is Required',
        'working_shift_end.required' => 'Working Shift End is Required',
        'working_shift_begin.required' => 'Working Shift Begin is Required',
        'shift_h.required' => 'Shift Hour is Required',
        'late.required' => 'Late Time is Required',
        'e_leave.required' => 'Early Leave Time is Required',
    ];

    $request->validate([
        'shift_name' => 'required',
        'working_shift_begin' => 'required',
        'working_shift_end' => 'required',
        'shift_h' => 'required',
        'late' => 'required',
        'e_leave' => 'required',
    ], $message);

    $workingShift = implode(',', [
        date('H:i:s', strtotime($request->working_shift_begin)),
        date('H:i:s', strtotime($request->working_shift_end)),
    ]);

    $shift = PayrollShiftManagement::findOrFail($id);
    $shift->company_id = Auth::user()->au_company_id;
    $shift->branch_id = Auth::user()->au_branch_id; // ✅ Add this line
    $shift->name = $request->shift_name;
    $shift->working_hour = $workingShift;
    $shift->shift_hour = $request->shift_h * 60;
    $shift->late_entry = $request->late;
    $shift->e_leave = $request->e_leave;
    $shift->save();

    session()->flash('type', 'success');
    session()->flash('message', 'Shift Updated Successfully');
    return redirect()->back();
}


   public function detailsDownloadPrint(Request $request) {
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id; // ✅ Get current branch ID
    $userType = Auth::user()->au_user_type;

    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $uid = $request->user;

    if ($request->has('month') && $request->has('year') && $request->has('user')) {
        $request_month = $request->month;
        $request_year = $request->year;
        $start_date = $request_year . '-' . $request_month . '-01';
        $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
    } else {
        $request_month = Carbon::now()->format('m');
        $request_year = Carbon::now()->format('Y');
        $start_date = $request_year . '-' . $request_month . '-01';
        $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
    }

    $usersAttendance = EmployeeAttendance::where('company_id', $com)
        ->where('branch_id', $branch) // ✅ Filter by branch
        ->where('user_id', $uid)
        ->whereMonth('created_at', $request_month)
        ->whereYear('created_at', $request_year)
        ->get();

    $holidays = Holiday::where('company_id', $com)
        ->where('branch_id', $branch) // ✅ Filter by branch
        ->where('year', $request_year)
        ->where('month', $request_month)
        ->pluck('date')
        ->toArray();

    $pdf = PDF::loadView('attendance.print-details', compact(
        'comDetails',
        'request_year',
        'request_month',
        'usersAttendance',
        'uid',
        'days_in_month',
        'holidays'
    ));

    return $pdf->download('attendance-report.pdf');
}
    public function todayAttendancePrint()
        {
            $com = Auth::user()->au_company_id;
            $branch = Auth::user()->au_branch_id; // ✅ Get branch ID
            $userType = Auth::user()->au_user_type;
            $uid = Auth::user()->au_id;

            $comDetails = Admin_user::where('au_company_id', $com)->first();
            $userComId = Admin_user::all();

            $startTime = Carbon::now()->format('Y-m-d') . ' 00:00:00';
            $endTime = Carbon::now()->format('Y-m-d') . ' 23:59:59';

            $usersAttendance = EmployeeAttendance::where('company_id', $com)
                                    ->where('branch_id', $branch) // ✅ Filter by branch
                                    ->whereBetween('created_at', [$startTime, $endTime])
                                    ->get();

            $pdf = PDF::loadView('attendance.today-attendance-print', compact(
                'comDetails',
                'userComId',
                'usersAttendance',
                'userType'
            ));

            return $pdf->download('today-attendance-report.pdf');
        }

}

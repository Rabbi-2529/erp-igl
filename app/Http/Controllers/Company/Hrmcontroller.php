<?php
namespace App\Http\Controllers\Company;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Admin_user;
use App\Models\EmployeeActivity;
use App\Models\EmployeeAttendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Department;

use App\Models\PayrollShiftManagement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
class Hrmcontroller extends Controller
{
    /**
     * Show all branches and today's activities for the company
     */
    public function showBranchesAndActivities()
    {
        // Get the current company ID from logged in user
        $companyId = Auth::user()->au_company_id;
        
        // Get all branches for this company
        $branches = Branch::where('company_id', $companyId)->get();

        // Get today's date
        $today = Carbon::today()->format('Y-m-d');

        // Get all today's activities for the company across all branches
        $todayActivities = EmployeeActivity::where('company_id', $companyId)
            ->whereDate('created_at', $today)
            ->with(['user', 'branch'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group activities by branch
        $activitiesByBranch = [];
        foreach ($branches as $branch) {
            $branchActivities = $todayActivities->where('branch_id', $branch->branch_id);
            $activitiesByBranch[$branch->branch_id] = [
                'branch' => $branch,
                'activities' => $branchActivities,
                'activity_count' => $branchActivities->count()
            ];
        }

        return view('company.hrm.branches-activities', [
            'branches' => $branches,
            'todayActivities' => $todayActivities,
            'activitiesByBranch' => $activitiesByBranch,
            'today' => $today
        ]);
    }

    /**
     * Show today's activities across all branches
     */
    public function showTodayActivities()
    {
        $companyId = Auth::user()->au_company_id;
        $today = Carbon::today();

        $todayActivities = EmployeeActivity::where('company_id', $companyId)
            ->whereDate('created_at', $today)
            ->with(['user', 'branch'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('company.hrm.today-activities', [
            'todayActivities' => $todayActivities,
            'today' => $today->format('Y-m-d')
        ]);
    }

    /**
     * Show activities for a specific branch
     */
    public function showBranchActivities($branchId)
    {
        $companyId = Auth::user()->au_company_id;
        
        // Verify branch belongs to company
        $branch = Branch::where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $today = Carbon::today();

        $branchActivities = EmployeeActivity::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('company.hrm.branch-activities', [
            'branch' => $branch,
            'activities' => $branchActivities,
            'today' => $today->format('Y-m-d')
        ]);
    }

    /**
     * Show activities by date range (filter)
     */
    public function showActivitiesByDate(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $query = EmployeeActivity::where('company_id', $companyId)
            ->with(['user', 'branch'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        $branches = Branch::where('company_id', $companyId)->get();

        return view('company.hrm.activities-by-date', [
            'activities' => $activities,
            'branches' => $branches,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedBranch' => $branchId
        ]);
    }

    /**
     * Get branch activities via AJAX
     */
    public function getBranchActivities($branchId)
    {
        $companyId = Auth::user()->au_company_id;
        $today = Carbon::today();

        $activities = EmployeeActivity::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'activities' => $activities,
            'activity_count' => $activities->count()
        ]);
    }

    /**
     * Dashboard summary
     */
    public function dashboard()
    {
        $companyId = Auth::user()->au_company_id;
        $today = Carbon::today();

        // Total branches
        $totalBranches = Branch::where('company_id', $companyId)->count();

        // Today's total activities
        $todayActivitiesCount = EmployeeActivity::where('company_id', $companyId)
            ->whereDate('created_at', $today)
            ->count();

        // Activities by branch for today
        $activitiesByBranch = EmployeeActivity::where('company_id', $companyId)
            ->whereDate('created_at', $today)
            ->with('branch')
            ->get()
            ->groupBy('branch_id');

        $branchStats = [];
        foreach ($activitiesByBranch as $branchId => $activities) {
            $branch = $activities->first()->branch;
            $branchStats[] = [
                'branch_name' => $branch ? $branch->branch_name : 'Unknown Branch',
                'activity_count' => $activities->count()
            ];
        }

        // Recent activities
        $recentActivities = EmployeeActivity::where('company_id', $companyId)
            ->with(['user', 'branch'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('company.hrm.dashboard', [
            'totalBranches' => $totalBranches,
            'todayActivitiesCount' => $todayActivitiesCount,
            'branchStats' => $branchStats,
            'recentActivities' => $recentActivities,
            'today' => $today->format('Y-m-d')
        ]);
    }


         public function allBranchesAttendance(Request $request)
{
    $companyId = Auth::user()->au_company_id;
    
    // Get all branches for this company
    $branches = Branch::where('company_id', $companyId)->get();

    // Get date filters
    $request_month = $request->month ?? Carbon::now()->format('m');
    $request_year = $request->year ?? Carbon::now()->format('Y');
    
    $start_date = $request_year . '-' . $request_month . '-01';
    $days_in_month = Carbon::make($start_date)->daysInMonth;

    // Get attendance summary for all branches
    $attendanceSummary = [];
    $totalStats = [
        'total_employees' => 0,
        'total_present' => 0,
        'total_absent' => 0,
        'total_late' => 0,
        'total_leave' => 0,
        'total_attendance_rate' => 0
    ];

    foreach ($branches as $branch) {
        // Get branch employees count
        $employeesCount = Admin_user::where('au_company_id', $companyId)
            ->where('au_branch_id', $branch->branch_id)
            ->whereIn('au_user_type', [5, 6]) // Team leaders and users
            ->count();

        // Get present count for this branch
        $presentCount = EmployeeAttendance::where('company_id', $companyId)
            ->where('branch_id', $branch->branch_id)
            ->whereMonth('created_at', $request_month)
            ->whereYear('created_at', $request_year)
            ->distinct('user_id')
            ->count('user_id');

        // Get late count
        $lateCount = EmployeeAttendance::where('company_id', $companyId)
            ->where('branch_id', $branch->branch_id)
            ->whereMonth('created_at', $request_month)
            ->whereYear('created_at', $request_year)
            ->where('status', 1) // Late status
            ->distinct('user_id')
            ->count('user_id');

        // Calculate absent (simplified calculation)
        $absentCount = max(0, $employeesCount - $presentCount);
        $attendanceRate = $employeesCount > 0 ? round(($presentCount / $employeesCount) * 100, 2) : 0;

        $attendanceSummary[$branch->branch_id] = [
            'branch' => $branch,
            'employees_count' => $employeesCount,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'attendance_rate' => $attendanceRate
        ];

        // Update total stats
        $totalStats['total_employees'] += $employeesCount;
        $totalStats['total_present'] += $presentCount;
        $totalStats['total_absent'] += $absentCount;
        $totalStats['total_late'] += $lateCount;
    }

    // Calculate overall attendance rate
    if ($totalStats['total_employees'] > 0) {
        $totalStats['total_attendance_rate'] = round(($totalStats['total_present'] / $totalStats['total_employees']) * 100, 2);
    }

    return view('company.hrm.attendance.all-branches-attendance', compact(
        'branches',
        'attendanceSummary',
        'totalStats',
        'request_month',
        'request_year',
        'days_in_month'
    ));
}

    /**
     * Show detailed attendance for a specific branch
     */
    public function branchAttendanceDetail($branchId, Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        
        // Verify branch belongs to company
        $branch = Branch::where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        // Get date filters
        $request_month = $request->month ?? Carbon::now()->format('m');
        $request_year = $request->year ?? Carbon::now()->format('Y');

        // Get employees for this branch
        $employees = Admin_user::where('au_company_id', $companyId)
            ->where('au_branch_id', $branchId)
            ->whereIn('au_user_type', [5, 6])
            ->get();

        // Get attendance data for these employees
        $attendanceData = [];
        foreach ($employees as $employee) {
            $attendanceRecords = EmployeeAttendance::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('user_id', $employee->au_id)
                ->whereMonth('created_at', $request_month)
                ->whereYear('created_at', $request_year)
                ->get();

            $presentDays = $attendanceRecords->count();
            $lateDays = $attendanceRecords->where('status', 1)->count();
            $incompleteDays = $attendanceRecords->where('status', 4)->count();
            $overtimeDays = $attendanceRecords->where('status', 5)->count();

            $attendanceData[$employee->au_id] = [
                'employee' => $employee,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'incomplete_days' => $incompleteDays,
                'overtime_days' => $overtimeDays,
                'records' => $attendanceRecords
            ];
        }

        // Calculate days in month for calendar view
        $start_date = $request_year . '-' . $request_month . '-01';
        $days_in_month = Carbon::make($start_date)->daysInMonth;

        // Get holidays for this branch
        $holidays = Holiday::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('year', $request_year)
            ->where('month', $request_month)
            ->pluck('date')
            ->toArray();

        return view('company.hrm.attendance.branch-attendance-detail', compact(
            'branch',
            'employees',
            'attendanceData',
            'request_month',
            'request_year',
            'days_in_month',
            'holidays'
        ));
    }

    /**
     * Show today's attendance across all branches
     */
    public function todayAllBranchesAttendance()
    {
        $companyId = Auth::user()->au_company_id;
        $today = Carbon::today();

        // Get all branches
        $branches = Branch::where('company_id', $companyId)->get();

        // Get today's attendance for all branches
        $todayAttendance = [];
        foreach ($branches as $branch) {
            $attendance = DB::table('employee_attendances as ea')
                ->select(
                    'ea.*',
                    'au.au_name',
                    'au.au_user_id',
                    'au.au_department',
                    'au.au_shifts'
                )
                ->leftJoin('admin_user as au', 'ea.user_id', '=', 'au.au_id')
                ->where('ea.company_id', $companyId)
                ->where('ea.branch_id', $branch->branch_id)
                ->whereDate('ea.created_at', $today)
                ->orderBy('ea.check_in', 'desc')
                ->get();

            $checkedIn = $attendance->count();
            $checkedOut = $attendance->where('check_out', '!=', null)->count();
            $lateComers = $attendance->where('status', 1)->count();

            $todayAttendance[$branch->branch_id] = [
                'branch' => $branch,
                'attendance' => $attendance,
                'checked_in' => $checkedIn,
                'checked_out' => $checkedOut,
                'late_comers' => $lateComers,
                'pending_checkout' => $checkedIn - $checkedOut
            ];
        }

        return view('company.hrm.attendance.today-all-branches-attendance', compact(
            'branches',
            
            'todayAttendance',
            'today'
        ));
    }

    /**
     * Download attendance report for a branch
     */
    public function downloadBranchAttendanceReport($branchId, Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        
        // Verify branch belongs to company
        $branch = Branch::where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $request_month = $request->month ?? Carbon::now()->format('m');
        $request_year = $request->year ?? Carbon::now()->format('Y');

        // Get employees and their attendance
        $employees = Admin_user::where('au_company_id', $companyId)
            ->where('au_branch_id', $branchId)
            ->whereIn('au_user_type', [5, 6])
            ->get();

        $attendanceData = [];
        foreach ($employees as $employee) {
            $attendanceRecords = EmployeeAttendance::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('user_id', $employee->au_id)
                ->whereMonth('created_at', $request_month)
                ->whereYear('created_at', $request_year)
                ->get();

            $presentDays = $attendanceRecords->count();
            $lateDays = $attendanceRecords->where('status', 1)->count();

            $attendanceData[] = [
                'employee_name' => $employee->au_name,
                'employee_id' => $employee->au_user_id,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => Carbon::create($request_year, $request_month, 1)->daysInMonth - $presentDays
            ];
        }

        $pdf = PDF::loadView('company.hrm.attendance.reports.branch-attendance-pdf', compact(
            'branch',
            'attendanceData',
            'request_month',
            'request_year'
        ));

        return $pdf->download("attendance-report-{$branch->branch_name}-{$request_month}-{$request_year}.pdf");
    }

    /**
     * Download comprehensive company attendance report
     */
    public function downloadCompanyAttendanceReport(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        
        $request_month = $request->month ?? Carbon::now()->format('m');
        $request_year = $request->year ?? Carbon::now()->format('Y');

        // Get all branches
        $branches = Branch::where('company_id', $companyId)->get();

        $companyReport = [];
        foreach ($branches as $branch) {
            $employeesCount = Admin_user::where('au_company_id', $companyId)
                ->where('au_branch_id', $branch->branch_id)
                ->whereIn('au_user_type', [5, 6])
                ->count();

            $presentCount = EmployeeAttendance::where('company_id', $companyId)
                ->where('branch_id', $branch->branch_id)
                ->whereMonth('created_at', $request_month)
                ->whereYear('created_at', $request_year)
                ->distinct('user_id')
                ->count('user_id');

            $lateCount = EmployeeAttendance::where('company_id', $companyId)
                ->where('branch_id', $branch->branch_id)
                ->whereMonth('created_at', $request_month)
                ->whereYear('created_at', $request_year)
                ->where('status', 1)
                ->distinct('user_id')
                ->count('user_id');

            $companyReport[] = [
                'branch_name' => $branch->branch_name,
                'employees_count' => $employeesCount,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'absent_count' => $employeesCount - $presentCount,
                'attendance_rate' => $employeesCount > 0 ? round(($presentCount / $employeesCount) * 100, 2) : 0
            ];
        }

        $pdf = PDF::loadView('company.hrm.attendance.reports.company-attendance-pdf', compact(
            'companyReport',
            'request_month',
            'request_year'
        ));

        return $pdf->download("company-attendance-report-{$request_month}-{$request_year}.pdf");
    }

    /**
     * Show attendance analytics and dashboard
     */
    public function attendanceAnalytics(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        
        $request_month = $request->month ?? Carbon::now()->format('m');
        $request_year = $request->year ?? Carbon::now()->format('Y');

        // Get monthly attendance trend
        $monthlyTrend = DB::table('employee_attendances')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(DISTINCT user_id) as unique_attendance')
            )
            ->where('company_id', $companyId)
            ->whereYear('created_at', $request_year)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Get branch-wise performance
            $branchPerformance = DB::table('employee_attendances as ea')
                ->join('auth_branch as b', 'ea.branch_id', '=', 'b.branch_id')
                ->select(
                    'b.branch_name',
                    DB::raw('COUNT(DISTINCT ea.user_id) as present_employees'),
                    DB::raw("(SELECT COUNT(*) FROM admin_user WHERE au_company_id = {$companyId} AND au_branch_id = b.branch_id AND au_user_type IN (5,6)) as total_employees"),
                    DB::raw("(ROUND((COUNT(DISTINCT ea.user_id) / (SELECT COUNT(*) FROM admin_user WHERE au_company_id = {$companyId} AND au_branch_id = b.branch_id AND au_user_type IN (5,6))) * 100, 2)) as attendance_rate")
                )
                ->where('ea.company_id', $companyId)
                ->whereMonth('ea.created_at', $request_month)
                ->whereYear('ea.created_at', $request_year)
                ->groupBy('b.branch_id', 'b.branch_name')
                ->get();

        // Get late comers statistics
        $lateComers = DB::table('employee_attendances as ea')
            ->join('admin_user as au', 'ea.user_id', '=', 'au.au_id')
            ->join('auth_branch as b', 'ea.branch_id', '=', 'b.branch_id')
            ->select(
                'au.au_name',
                'b.branch_name',
                DB::raw('COUNT(ea.id) as late_count')
            )
            ->where('ea.company_id', $companyId)
            ->where('ea.status', 1) // Late status
            ->whereMonth('ea.created_at', $request_month)
            ->whereYear('ea.created_at', $request_year)
            ->groupBy('ea.user_id', 'au.au_name', 'b.branch_name')
            ->orderBy('late_count', 'desc')
            ->limit(10)
            ->get();

        return view('company.hrm.attendance.attendance-analytics', compact(
            'monthlyTrend',
            'branchPerformance',
            'lateComers',
            'request_month',
            'request_year'
        ));
    }

    /**
     * Show employee-wise attendance details
     */
public function employeeWiseAttendance(Request $request)
{
    // Get company and user info
    $companyId = Auth::user()->au_company_id;
    $userType = Auth::user()->au_user_type;
    
    // Get company details (first user in company)
    $comDetails = Admin_user::where('au_company_id', $companyId)->first();
    
    // Get all employees in the company for dropdown
    $userComId = Admin_user::where('au_company_id', $companyId)
        ->whereIn('au_user_type', [5, 6]) // Assuming 5,6 are employee types
        ->get();

    // Requested month/year or current
    if ($request->has('month') && $request->month) {
        try {
            $date = Carbon::createFromFormat('Y-m', $request->month);
            $request_month = $date->format('m');
            $request_year = $date->format('Y');
        } catch (\Exception $e) {
            $request_month = Carbon::now()->format('m');
            $request_year = Carbon::now()->format('Y');
        }
    } else {
        $request_month = Carbon::now()->format('m');
        $request_year = Carbon::now()->format('Y');
    }
    
    $branch_id = $request->branch_id ?? null;
    $employee_id = $request->employee_id ?? null;

    // Get all branches for filter (company-wide)
    $branches = Branch::where('company_id', $companyId)->get();

    // Calculate weekends for the month
    $start_date = $request_year.'-'.$request_month.'-1';
    $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
    $totalWeekends = 0;
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $currentDate = Carbon::create($request_year, $request_month, $day);
        if ($currentDate->dayOfWeek == Carbon::FRIDAY) { // Adjust based on your weekends
            $totalWeekends++;
        }
    }

    // Get total holidays for the company
    $holidays = Holiday::where('company_id', $companyId)
        ->where('year', $request_year)
        ->where('month', $request_month)
        ->get();
    
    $totalHolidays = $holidays->count();

    // Get leaves for the company
    $monthLeave = Leave::where('company_id', $companyId)
        ->whereMonth('leave_date_accept_start', '=', $request_month)
        ->whereYear('leave_date_accept_start', '=', $request_year)
        ->get();

    // Get company work shift (from first user)
    $comWorkShift = $comDetails->au_work_shift ?? '09:00,18:00';
    $shiftTime = explode(',', $comWorkShift);
    $expectedCheckIn = $shiftTime[0] ?? '09:00';

    // Get late check-ins for the whole company
    $lateCheckIns = EmployeeAttendance::where('company_id', $companyId)
        ->whereMonth('created_at', $request_month)
        ->whereYear('created_at', $request_year)
        ->whereTime('check_in', '>', $expectedCheckIn)
        ->get();

    // Build query for attendance summary - COMPANY WIDE
    $attendanceQuery = DB::table('employee_attendances')
        ->select(
            'user_id',
            DB::raw('SUM(incomplete_hour) as incom_hour'),
            DB::raw('SUM(incomplete_minute) as incom_minute'),
            DB::raw('SUM(incomplete_sec) as incom_sec'),
            DB::raw('SUM(over_hour) as over_hour'),
            DB::raw('SUM(over_minute) as over_minute'),
            DB::raw('SUM(over_sec) as over_sec'),
            DB::raw('COUNT(check_in) as check_in_count'),
            DB::raw('MIN(check_in) as first_check_in'),
            DB::raw('MAX(check_out) as last_check_out')
        )
        ->whereMonth('employee_attendances.created_at', $request_month)
        ->whereYear('employee_attendances.created_at', $request_year)
        ->where('employee_attendances.company_id', $companyId);

    // Apply filters if provided
    if ($branch_id) {
        $attendanceQuery->where('employee_attendances.branch_id', $branch_id);
    }
    
    if ($employee_id) {
        $attendanceQuery->where('employee_attendances.user_id', $employee_id);
    }

    $usersAttendance = $attendanceQuery
        ->groupBy('user_id')
        ->orderBy('user_id', 'asc')
        ->get();

    // Calculate attendance details for the summary table
    $attendanceDetails = [];
    $daysInMonth = Carbon::create($request_year, $request_month, 1)->daysInMonth;
    
    // Get all employees for the summary
    $employeesQuery = Admin_user::where('au_company_id', $companyId)
        ->whereIn('au_user_type', [5, 6])
        ->with('branch');

    if ($branch_id) {
        $employeesQuery->where('au_branch_id', $branch_id);
    }

    if ($employee_id) {
        $employeesQuery->where('au_id', $employee_id);
    }

    $employees = $employeesQuery->get();

    foreach ($employees as $employee) {
        $attendanceRecords = EmployeeAttendance::where('company_id', $companyId)
            ->where('user_id', $employee->au_id)
            ->whereMonth('check_in', $request_month)
            ->whereYear('check_in', $request_year)
            ->get();
        
        $totalPresent = $attendanceRecords->count();
        
        // Calculate late entries
        $totalLate = 0;
        foreach ($attendanceRecords as $record) {
            if ($record->check_in) {
                $checkInTime = Carbon::parse($record->check_in);
                $expectedTime = Carbon::parse($expectedCheckIn);
                if ($checkInTime->gt($expectedTime->addMinutes(15))) { // 15 minutes grace period
                    $totalLate++;
                }
            }
        }
        
        // Calculate overtime (assuming > 8 hours is overtime)
        $totalOvertime = $attendanceRecords->where('total_hour', '>', 8)->count();
        
        // Calculate incomplete (no check-out or < 8 hours)
        $totalIncomplete = $attendanceRecords->filter(function ($record) {
            return is_null($record->check_out) || $record->total_hour < 8;
        })->count();

        // Calculate attendance metrics
        $workingDays = $daysInMonth - $totalWeekends - $totalHolidays;
        $attendanceRate = $workingDays > 0 ? 
            round(($totalPresent / $workingDays) * 100, 2) : 0;

        $attendanceDetails[] = [
            'employee' => $employee,
            'total_present' => $totalPresent,
            'total_late' => $totalLate,
            'total_overtime' => $totalOvertime,
            'total_incomplete' => $totalIncomplete,
            'attendance_rate' => $attendanceRate,
            'working_days' => $workingDays,
            'expected_days' => $daysInMonth,
            'weekends' => $totalWeekends,
            'holidays' => $totalHolidays
        ];
    }

    // Return view with all data
    return view('company.hrm.attendance.employee-wise-attendance', compact(
        'attendanceDetails',
        'branches',
        'request_month',
        'request_year',
        'branch_id',
        'employee_id',
        'userType',
        'usersAttendance',
        'comWorkShift',
        'comDetails',
        'userComId',
        'days_in_month',
        'lateCheckIns',
        'monthLeave',
        'totalWeekends',
        'totalHolidays'
    ));
}

    /**
     * Get employee details for modal
     */
    public function getEmployeeAttendanceDetails(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        $userId = $request->userid;
        $year = $request->year;
        $month = $request->month;

        $user = Admin_user::where('au_company_id', $companyId)
            ->where('au_id', $userId)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $attendanceRecords = EmployeeAttendance::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        $html = view('company.hrm.attendance.employee-attendance-details', compact(
            'user', 'attendanceRecords', 'year', 'month'
        ))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Export today's attendance to PDF
     */
    public function exportTodayAttendancePDF()
    {
        $companyId = Auth::user()->au_company_id;
        $today = Carbon::today();

        // Get all branches with today's attendance
        $branches = Branch::where('company_id', $companyId)->get();
        
        $todayAttendance = [];
        foreach ($branches as $branch) {
            $attendance = DB::table('employee_attendances as ea')
                ->select(
                    'ea.*',
                    'au.au_name',
                    'au.au_user_id',
                    'au.au_department'
                )
                ->leftJoin('admin_user as au', 'ea.user_id', '=', 'au.au_id')
                ->where('ea.company_id', $companyId)
                ->where('ea.branch_id', $branch->branch_id)
                ->whereDate('ea.created_at', $today)
                ->orderBy('ea.check_in', 'desc')
                ->get();

            $todayAttendance[$branch->branch_id] = [
                'branch' => $branch,
                'attendance' => $attendance
            ];
        }

        $pdf = PDF::loadView('company.hrm.reports.today-attendance-pdf', compact(
            'branches',
            'todayAttendance',
            'today'
        ));

        return $pdf->download("today-attendance-report-{$today->format('Y-m-d')}.pdf");
    }



        public function employeeList(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        
        $branch_id = $request->branch_id ?? null;
        $department_id = $request->department_id ?? null;
        $user_type = $request->user_type ?? null;
        $status = $request->status ?? 'active';

        // Get all branches and departments for filters
        $branches = Branch::where('company_id', $companyId)->get();
        $departments = Department::all();

        // Build query for employees
        $employeesQuery = Admin_user::where('au_company_id', $companyId)
            ->with(['branch', 'department']);

        // Apply filters
        if ($branch_id) {
            $employeesQuery->where('au_branch_id', $branch_id);
        }

        if ($department_id) {
            $employeesQuery->where('au_department', $department_id);
        }

        if ($user_type) {
            $employeesQuery->where('au_user_type', $user_type);
        }

        if ($status === 'active') {
            $employeesQuery->where('au_status', 1);
        } elseif ($status === 'inactive') {
            $employeesQuery->where('au_status', 0);
        }

        $employees = $employeesQuery->orderBy('au_name')->get();

        // Employee statistics
        $totalEmployees = $employees->count();
        $activeEmployees = $employees->where('au_status', 1)->count();
        $teamLeaders = $employees->where('au_user_type', 5)->count();
        $regularUsers = $employees->where('au_user_type', 6)->count();

        return view('company.hrm.employees.employee-list', compact(
            'employees',
            'branches',
            'departments',
            'branch_id',
            'department_id',
            'user_type',
            'status',
            'totalEmployees',
            'activeEmployees',
            'teamLeaders',
            'regularUsers'
        ));
    }

    /**
     * Show employee details
     */
    public function employeeDetail($employeeId)
    {
        $companyId = Auth::user()->au_company_id;
        
        $employee = Admin_user::where('au_company_id', $companyId)
            ->with(['branch', 'department', 'team_info'])
            ->findOrFail($employeeId);

        // Get current month attendance summary
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');

        $attendanceSummary = EmployeeAttendance::where('company_id', $companyId)
            ->where('user_id', $employeeId)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->selectRaw('
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as overtime_days,
                SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as incomplete_days
            ')
            ->first();

        // Get last 5 attendance records
        $recentAttendance = EmployeeAttendance::where('company_id', $companyId)
            ->where('user_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get shift information if available
        $shift = null;
        if ($employee->au_work_shift) {
            $shift = PayrollShiftManagement::find($employee->au_work_shift);
        }

        return view('company.hrm.employees.employee-detail', compact(
            'employee',
            'attendanceSummary',
            'recentAttendance',
            'shift',
            'currentMonth',
            'currentYear'
        ));
    }

    /**
     * Show employee attendance history
     */
  
    /**
     * Show employee edit form
     */
    public function editEmployee($employeeId)
    {
        $companyId = Auth::user()->au_company_id;
        
        $employee = Admin_user::where('au_company_id', $companyId)->findOrFail($employeeId);
        
        $branches = Branch::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();
        $shifts = PayrollShiftManagement::where('company_id', $companyId)->get();
        $bloodGroups = Admin_user::getAllBloodGroups();

        return view('company.hrm.employees.edit-employee', compact(
            'employee',
            'branches',
            'departments',
            'shifts',
            'bloodGroups'
        ));
    }

    /**
     * Update employee information
     */
    public function updateEmployee(Request $request, $employeeId)
    {
        $companyId = Auth::user()->au_company_id;
        
        $employee = Admin_user::where('au_company_id', $companyId)->findOrFail($employeeId);

        $request->validate([
            'au_name' => 'required|string|max:255',
            'au_email' => 'required|email|unique:admin_user,au_email,' . $employeeId . ',au_id',
            'au_mobile' => 'required|string|max:15',
            'au_branch_id' => 'required|exists:auth_branch,branch_id',
            'au_department' => 'nullable|exists:departments,id',
            'au_user_type' => 'required|in:4,5,6',
            'au_work_shift' => 'nullable|exists:payroll_shift_management,id',
            'au_blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ]);

        try {
            $employee->update([
                'au_name' => $request->au_name,
                'au_email' => $request->au_email,
                'au_mobile' => $request->au_mobile,
                'au_branch_id' => $request->au_branch_id,
                'au_department' => $request->au_department,
                'au_user_type' => $request->au_user_type,
                'au_work_shift' => $request->au_work_shift,
                'au_blood_group' => $request->au_blood_group,
                'au_address' => $request->au_address,
                'au_user_salary' => $request->au_user_salary,
                'au_user_facility' => $request->au_user_facility,
            ]);

            // Update branch name if branch changed
            if ($request->au_branch_id != $employee->getOriginal('au_branch_id')) {
                $branch = Branch::find($request->au_branch_id);
                $employee->update(['au_branch_name' => $branch->branch_name]);
            }

            return redirect()->route('company.hrm.employees.detail', $employeeId)
                ->with('success', 'Employee information updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update employee information: ' . $e->getMessage());
        }
    }

    /**
     * Toggle employee status (Active/Inactive)
     */
    public function toggleEmployeeStatus($employeeId)
    {
        $companyId = Auth::user()->au_company_id;
        
        $employee = Admin_user::where('au_company_id', $companyId)->findOrFail($employeeId);

        try {
            $employee->update([
                'au_status' => $employee->au_status ? 0 : 1
            ]);

            $status = $employee->au_status ? 'activated' : 'deactivated';
            return redirect()->back()
                ->with('success', "Employee {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update employee status: ' . $e->getMessage());
        }
    }

    /**
     * Download employee profile PDF
     */
    public function downloadEmployeeProfile($employeeId)
    {
        $companyId = Auth::user()->au_company_id;
        
        $employee = Admin_user::where('au_company_id', $companyId)
            ->with(['branch', 'department'])
            ->findOrFail($employeeId);

        // Get current month attendance summary
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');

        $attendanceSummary = EmployeeAttendance::where('company_id', $companyId)
            ->where('user_id', $employeeId)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->selectRaw('
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as overtime_days
            ')
            ->first();

        $pdf = PDF::loadView('company.hrm.reports.employee-profile-pdf', compact(
            'employee',
            'attendanceSummary',
            'currentMonth',
            'currentYear'
        ));

        return $pdf->download("employee-profile-{$employee->au_name}.pdf");
    }
    
    
    /**
 * Show all employees for transfer with filtering options
 */
public function showEmployeesForTransfer(Request $request)
{
    $companyId = Auth::user()->au_company_id;
    
    // Get filter parameters
    $branch_id = $request->branch_id ?? null;
    $department_id = $request->department_id ?? null;
    $user_type = $request->user_type ?? null;
    $search = $request->search ?? null;
    
    // Get all branches and departments for filters
    $branches = Branch::where('company_id', $companyId)->get();
    $departments = Department::all();
    
    // Build query for employees
    $employeesQuery = Admin_user::where('au_company_id', $companyId)
        ->with(['branch', 'department'])
        ->where('au_status', 1); // Only active employees
    
    // Apply filters
    if ($branch_id) {
        $employeesQuery->where('au_branch_id', $branch_id);
    }
    
    if ($department_id) {
        $employeesQuery->where('au_department', $department_id);
    }
    
    if ($user_type) {
        $employeesQuery->where('au_user_type', $user_type);
    }
    
    if ($search) {
        $employeesQuery->where(function($q) use ($search) {
            $q->where('au_name', 'like', "%{$search}%")
              ->orWhere('au_user_id', 'like', "%{$search}%")
              ->orWhere('au_email', 'like', "%{$search}%")
              ->orWhere('au_mobile', 'like', "%{$search}%");
        });
    }
    
    $employees = $employeesQuery->orderBy('au_name')->paginate(20);
    
    // Get all branches for transfer dropdown
    $allBranches = Branch::where('company_id', $companyId)->get();
    
    return view('company.hrm.employees.transfer-employees', compact(
        'employees',
        'branches',
        'allBranches',
        'departments',
        'branch_id',
        'department_id',
        'user_type',
        'search'
    ));
}

/**
 * Transfer multiple employees to another branch
 */
public function transferEmployees(Request $request)
{
    $companyId = Auth::user()->au_company_id;
    
    $request->validate([
        'employee_ids' => 'required|array',
        'employee_ids.*' => 'exists:admin_user,au_id,au_company_id,' . $companyId,
        'new_branch_id' => 'required|exists:auth_branch,branch_id',
        'transfer_date' => 'required|date|after_or_equal:today',
        'transfer_reason' => 'nullable|string|max:500'
    ]);
    
    try {
        DB::beginTransaction();
        
        // Get the new branch
        $newBranch = Branch::where('company_id', $companyId)
            ->where('branch_id', $request->new_branch_id)
            ->firstOrFail();
        
        $transferredEmployees = [];
        $failedTransfers = [];
        
        foreach ($request->employee_ids as $employeeId) {
            try {
                $employee = Admin_user::where('au_company_id', $companyId)
                    ->where('au_id', $employeeId)
                    ->first();
                
                // Check if employee is already in the target branch
                if ($employee->au_branch_id == $request->new_branch_id) {
                    $failedTransfers[] = [
                        'employee' => $employee->au_name,
                        'reason' => 'Already in the selected branch'
                    ];
                    continue;
                }
                
                // Get old branch name for history
                $oldBranch = Branch::find($employee->au_branch_id);
                
                // Update employee branch
                $employee->update([
                    'au_branch_id' => $request->new_branch_id,
                    'au_branch_name' => $newBranch->branch_name,
                    'transfer_date' => $request->transfer_date,
                    'transfer_reason' => $request->transfer_reason
                ]);
                
                // Record transfer history
                DB::table('employee_transfer_history')->insert([
                    'employee_id' => $employeeId,
                    'company_id' => $companyId,
                    'old_branch_id' => $oldBranch->branch_id ?? null,
                    'old_branch_name' => $oldBranch->branch_name ?? 'Unknown',
                    'new_branch_id' => $newBranch->branch_id,
                    'new_branch_name' => $newBranch->branch_name,
                    'transfer_date' => $request->transfer_date,
                    'transfer_reason' => $request->transfer_reason,
                    'transferred_by' => Auth::user()->au_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $transferredEmployees[] = $employee->au_name;
                
            } catch (\Exception $e) {
                $failedTransfers[] = [
                    'employee_id' => $employeeId,
                    'reason' => $e->getMessage()
                ];
            }
        }
        
        DB::commit();
        
        // Prepare response message
        $message = '';
        if (count($transferredEmployees) > 0) {
            $message .= 'Successfully transferred ' . count($transferredEmployees) . ' employee(s): ' . implode(', ', $transferredEmployees) . '. ';
        }
        
        if (count($failedTransfers) > 0) {
            $message .= 'Failed to transfer ' . count($failedTransfers) . ' employee(s).';
        }
        
        $responseType = count($failedTransfers) == 0 ? 'success' : 'warning';
        
        return redirect()->route('company.hrm.employees.transfer.list')
            ->with($responseType, $message)
            ->with('transferred_employees', $transferredEmployees)
            ->with('failed_transfers', $failedTransfers);
            
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Failed to transfer employees: ' . $e->getMessage())
            ->withInput();
    }
}

/**
 * Show transfer history for employees
 */


/**
 * Quick transfer - transfer single employee via AJAX
 */
public function quickTransferEmployee(Request $request)
{
    $companyId = Auth::user()->au_company_id;
    
    $request->validate([
        'employee_id' => 'required|exists:admin_user,au_id,au_company_id,' . $companyId,
        'new_branch_id' => 'required|exists:auth_branch,branch_id',
        'transfer_reason' => 'nullable|string|max:500'
    ]);
    
    try {
        $employee = Admin_user::where('au_company_id', $companyId)
            ->where('au_id', $request->employee_id)
            ->firstOrFail();
        
        $newBranch = Branch::where('company_id', $companyId)
            ->where('branch_id', $request->new_branch_id)
            ->firstOrFail();
        
        // Check if same branch
        if ($employee->au_branch_id == $request->new_branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Employee is already in this branch'
            ], 400);
        }
        
        // Get old branch
        $oldBranch = Branch::find($employee->au_branch_id);
        
        // Update employee
        $employee->update([
            'au_branch_id' => $request->new_branch_id,
            'au_branch_name' => $newBranch->branch_name,
            'transfer_date' => now()->format('Y-m-d'),
            'transfer_reason' => $request->transfer_reason
        ]);
        
        // Record history
        DB::table('employee_transfer_history')->insert([
            'employee_id' => $employee->au_id,
            'company_id' => $companyId,
            'old_branch_id' => $oldBranch->branch_id ?? null,
            'old_branch_name' => $oldBranch->branch_name ?? 'Unknown',
            'new_branch_id' => $newBranch->branch_id,
            'new_branch_name' => $newBranch->branch_name,
            'transfer_date' => now()->format('Y-m-d'),
            'transfer_reason' => $request->transfer_reason,
            'transferred_by' => Auth::user()->au_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Employee transferred successfully',
            'employee' => [
                'id' => $employee->au_id,
                'name' => $employee->au_name,
                'old_branch' => $oldBranch->branch_name ?? 'Unknown',
                'new_branch' => $newBranch->branch_name
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to transfer employee: ' . $e->getMessage()
        ], 500);
    }
}



public function atTotalShow(Request $request)
{
    $com = Auth::user()->au_company_id;
    $userType = Auth::user()->au_user_type;
    
    // Get company details
    $comDetails = Admin_user::where('au_company_id', $com)->first();
    
    // Get all branches for the filter dropdown
    $branches = Branch::where('company_id', $com)->get();
    
    // Get selected branch from request (if any)
    $selectedBranch = $request->branch_id;
    
    // Get all users in the company - filter by branch if selected
    $userQuery = Admin_user::where('au_company_id', $com);
    
    if ($selectedBranch && $selectedBranch != '') {
        $userQuery->where('au_branch_id', $selectedBranch);
    }
    
    $userComId = $userQuery->get();
    $uid = Auth::user()->au_id;

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

    // Calculate weekends (Friday as weekend - adjust as needed)
    $totalWeekends = 0;
    for ($day = 1; $day <= $days_in_month; $day++) {
        $currentDate = Carbon::create($request_year, $request_month, $day);
        if ($currentDate->dayOfWeek == Carbon::FRIDAY) {
            $totalWeekends++;
        }
    }

    // Get holidays with branch filter
    $holidaysQuery = Holiday::where('company_id', $com);
    if ($selectedBranch && $selectedBranch != '') {
        $holidaysQuery->where('branch_id', $selectedBranch);
    }
    
    $holidays = $holidaysQuery
        ->where('year', $request_year)
        ->where('month', $request_month)
        ->pluck('date')
        ->toArray();

    $totalHolidays = count($holidays);

    // Get company work shift
    $comWorkShift = Admin_user::where('au_company_id', $com)->first();
    $shiftTime = explode(',', $comWorkShift->au_work_shift ?? '09:00:00,18:00:00');
    $expectedCheckIn = $shiftTime[0];

    // Fetch late check-ins with branch filter
    $lateCheckInsQuery = EmployeeAttendance::where('company_id', $com)
        ->whereMonth('created_at', $request_month)
        ->whereYear('created_at', $request_year)
        ->whereTime('check_in', '>', $expectedCheckIn);
    
    if ($selectedBranch && $selectedBranch != '') {
        $lateCheckInsQuery->where('branch_id', $selectedBranch);
    }
    
    $lateCheckIns = $lateCheckInsQuery->get();

    // Fetch leaves with branch filter
    $monthLeaveQuery = Leave::where('company_id', $com)
        ->whereMonth(DB::raw('STR_TO_DATE(leave_date_accept_start, "%d/%m/%Y")'), '=', $request_month)
        ->whereYear(DB::raw('STR_TO_DATE(leave_date_accept_start, "%d/%m/%Y")'), '=', $request_year);
    
    if ($selectedBranch && $selectedBranch != '') {
        $monthLeaveQuery->where('branch_id', $selectedBranch);
    }
    
    $monthLeave = $monthLeaveQuery->get();

    // Check permission
    $hasPermission = DB::table('au_accesses')
        ->where('access_id', 100)
        ->where('status', 1)
        ->where('permission_user_type', Auth::user()->au_user_type)
        ->exists();

    // Attendance summary with branch filter
    if ($hasPermission) {
        // Same department users
        $usersAttendance = DB::table('employee_attendances')
            ->select(
                'user_id',
                DB::raw('count(check_in) as check_in')
            )
            ->join('admin_user', 'employee_attendances.user_id', '=', 'admin_user.au_id')
            ->whereMonth('employee_attendances.created_at', $request_month)
            ->whereYear('employee_attendances.created_at', $request_year)
            ->where('employee_attendances.company_id', $com)
            ->where('admin_user.au_department', Auth::user()->au_department);
            
        if ($selectedBranch && $selectedBranch != '') {
            $usersAttendance->where('employee_attendances.branch_id', $selectedBranch);
        }
        
        $usersAttendance = $usersAttendance
            ->groupBy('user_id')
            ->orderBy('user_id', 'asc')
            ->get();
    } else {
        // All users
        $usersAttendance = DB::table('employee_attendances')
            ->select(
                'user_id',
                DB::raw('count(check_in) as check_in')
            )
            ->whereMonth('employee_attendances.created_at', $request_month)
            ->whereYear('employee_attendances.created_at', $request_year)
            ->where('employee_attendances.company_id', $com);
            
        if ($selectedBranch && $selectedBranch != '') {
            $usersAttendance->where('employee_attendances.branch_id', $selectedBranch);
        }
        
        $usersAttendance = $usersAttendance
            ->groupBy('user_id')
            ->orderBy('user_id', 'asc')
            ->get();
    }

    // Also get users who have NO attendance records for this month
    $usersWithAttendance = $usersAttendance->pluck('user_id')->toArray();
    $allUsersQuery = Admin_user::where('au_company_id', $com)
        ->whereIn('au_user_type', [5, 6]); // Assuming these are employee types
    
    if ($selectedBranch && $selectedBranch != '') {
        $allUsersQuery->where('au_branch_id', $selectedBranch);
    }
    
    $allUsers = $allUsersQuery->get();
    
    $usersWithoutAttendance = $allUsers->whereNotIn('au_id', $usersWithAttendance);
    
    // Add users without attendance to the collection with zero values
    foreach ($usersWithoutAttendance as $user) {
        $usersAttendance->push((object) [
            'user_id' => $user->au_id,
            'check_in' => 0
        ]);
    }

    // Sort users by ID
    $usersAttendance = $usersAttendance->sortBy('user_id')->values();

    // Get selected branch name for display
    $selectedBranchName = 'All Branches';
    if ($selectedBranch && $selectedBranch != '') {
        $branch = Branch::find($selectedBranch);
        $selectedBranchName = $branch ? $branch->branch_name : 'Selected Branch';
    }

    // Return view
    return view('company.hrm.attendance.total-show', compact(
        'comDetails',
        'userComId',
        'usersAttendance',
        'userType',
        'days_in_month',
        'request_year',
        'request_month',
        'lateCheckIns',
        'monthLeave',
        'totalWeekends',
        'totalHolidays',
        'branches',
        'selectedBranch',
        'selectedBranchName'
    ));
}




}
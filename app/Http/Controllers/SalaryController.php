<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin_user;
use App\Models\EmployeeAttendance;
use App\Models\Leave;
use App\Models\Holiday;
use App\Models\SalaryGenerate;
use App\Models\SalaryRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use DB;
class SalaryController extends Controller
{
    /**
     * Display the salary generation form.
     *
     * @return \Illuminate\View\View
     */   
public function showSalaryGenerationForm(Request $request)
{
    $user = Auth::user();
    $com_id = $user->au_company_id;
    $branch_id = $user->au_branch_id;

    // Get current month and year or default to the current date
    $monthYear = $request->input('month_year', Carbon::now()->format('Y-m'));

    // Validate the format of $monthYear
    if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
        Log::error("Invalid Month-Year format: $monthYear");
        return redirect()->back()->with('error', 'Invalid month-year format.');
    }

    // Split the monthYear into year and month
    [$year, $month] = explode('-', $monthYear);

    Log::info("Received Month-Year: $monthYear");

    // Retrieve all distinct months for display (filtered by company & branch)
    $distinctMonths = SalaryGenerate::select('month', 'year')
        ->whereHas('user', function ($query) use ($com_id, $branch_id) {
            $query->where('au_company_id', $com_id)
                  ->where('au_branch_id', $branch_id);
        })
        ->distinct()
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();

    // Retrieve all salary records and group them by month-year (filtered by company & branch)
    $records = SalaryGenerate::whereHas('user', function ($query) use ($com_id, $branch_id) {
            $query->where('au_company_id', $com_id)
                  ->where('au_branch_id', $branch_id);
        })
        ->with('approvedBy')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get()
        ->groupBy(function ($record) {
            return $record->year . '-' . str_pad($record->month, 2, '0', STR_PAD_LEFT);
        });

    // Initialize monthStatus array
    $monthStatus = [];

    foreach ($records as $key => $monthRecords) {
        Log::info("Processing Month: $key");

        foreach ($monthRecords as $record) {
            Log::info("Record ID: {$record->id}, Raw Status: {$record->status}");
        }

        $statuses = $monthRecords->pluck('status')->map(fn($status) => (int) $status === 1 ? 'Approved' : 'Not Approved');

        $monthStatus[$key] = $statuses->contains('Approved');
    }

    return view('page.salary_records.generate', [
        'records' => $records,
        'distinctMonths' => $distinctMonths,
        'month_year' => $monthYear,
        'monthStatus' => $monthStatus,
    ]);
}

    /**
     * Generate salary for the selected month and year.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'month_year' => 'required|date_format:m-Y',
        ]);

        // Extract month and year from the input
        $monthYear = $request->input('month_year');
        $selectedYear = Carbon::createFromFormat('m-Y', $monthYear)->year;
        $selectedMonth = Carbon::createFromFormat('m-Y', $monthYear)->month;

        // Fetch attendance data and holidays for the selected month and year
        $employees = Admin_user::where('au_company_id', Auth::user()->au_company_id)
        ->where('au_branch_id', Auth::user()->au_branch_id)
                            ->where('au_status', 1)
                            ->get();

        foreach ($employees as $employee) {
            $userId = $employee->au_id;
            $baseSalary = $employee->au_user_salary;

            if (is_null($baseSalary) || $baseSalary <= 0) {
                continue;
            }

            $dailySalary = $baseSalary / 30;

            // Fetch attendance data for the selected month and year
            $attendances = EmployeeAttendance::where('user_id', $userId)
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->get();

            // Fetch approved leave data for the selected month and year
            $leaveRecords = Leave::where('user_id', $userId)
            ->whereIn('status', [1, 2, 3])
            ->get()
            ->filter(function ($leave) use ($selectedYear, $selectedMonth) {
                try {
                    $date = Carbon::createFromFormat('d/m/Y', $leave->leave_date_accept_start);
                    return $date->year == $selectedYear && $date->month == $selectedMonth;
                } catch (\Exception $e) {
                    return false;
                }
            });

            // Calculate weekends and holidays for the selected month and year
            $startDate = Carbon::create($selectedYear, $selectedMonth, 1);
            $endDate = $startDate->copy()->endOfMonth();

            // Count Fridays as weekends
            $totalWeekends = 0;
            while ($startDate <= $endDate) {
                if ($startDate->isFriday()) {
                    $totalWeekends++;
                }
                $startDate->addDay();
            }

            // Fetch total holidays for the selected month and year
            $totalHolidays = Holiday::where('company_id', Auth::user()->au_company_id)
            ->where('branch_id', Auth::user()->au_branch_id)
                ->where('year', $selectedYear)
                ->where('month', $selectedMonth)
                ->count();

            $workingDays = Carbon::create($selectedYear, $selectedMonth, 1)->daysInMonth;
            $totalWorkingDays = $workingDays - $totalWeekends - $totalHolidays;

            // Calculate late data
            $lateData = $this->calculateLateData($attendances);
            $lateCount = $lateData['late_count'];
            $lateCutDays = $lateData['late_cut_days'];

            // Calculate absent count directly in the generate method
            $totalAttendanceDays = $attendances->count();
            $totalLeaveDays = $leaveRecords->sum('total_day_accepted');
            
            $absentCount = max(0, $totalWorkingDays - $totalAttendanceDays);

            // Get the salary advance record ID and amount
            $salaryAdvance = $this->getSalaryAdvance($userId, $selectedMonth, $selectedYear);
            $salaryAdvanceId = $salaryAdvance['id'];
            $salaryAdvanceAmount = $salaryAdvance['amount'];
            $salaryAdvance = $this->getSalaryAdvance($userId, $selectedMonth, $selectedYear);
            $salaryAdvanceId = $salaryAdvance['id'];
            $salaryAdvanceAmount = $salaryAdvance['amount'];
            
            // Update release_amount if applicable
            if (!empty($salaryAdvance['record'])) {
                $record = $salaryAdvance['record'];
                $record->release_amount += $salaryAdvanceAmount;
            
                if ($record->release_amount >= $record->amount) {
                    $record->paid = 1;
                }
            
                $record->save();
            }


            $totalLatecut = $dailySalary * $lateCutDays;
            $totalAbsentcut = $dailySalary * $absentCount;
            $totalacceptdays = $dailySalary * $totalLeaveDays;

            $totalDeductions = ($totalAbsentcut + $totalLatecut + $salaryAdvanceAmount)-$totalacceptdays;

            $finalSalary = $baseSalary - $totalDeductions;

            SalaryGenerate::updateOrCreate(
                ['user_id' => $userId, 'year' => $selectedYear, 'month' => $selectedMonth],
                [
                    'basic_salary' => $baseSalary,
                    'total_late_cut' => $dailySalary * $lateCutDays,
                    'total_absent_cut' => $dailySalary * $absentCount,
                    'total_paid_leave' => $totalLeaveDays,
                    'totalacceptdays'=>$totalacceptdays,
                    'final_salary' => $finalSalary,
                    'salary_advance_id' => $salaryAdvanceId,
                    'salary_loan_id' => null,
                    'company_id' => Auth::user()->au_company_id,
                    'generated_by' => Auth::id(), 
                ]
            );
        }

        return redirect()->back()->with('success', 'Salaries for the selected month and year have been generated successfully.');
    }

    /**
     * Calculate late data based on attendance records.
     *
     * @param \Illuminate\Support\Collection $attendances
     * @return array
     */
     private function calculateLateData($attendances)
{
    $lateData = [
        'late_count' => 0,
        'late_cut_days' => 0,
        'total_late_cut' => 0,
    ];

    // Initialize counts for different late thresholds
    $lateCounts = [
        'after_9_15' => 0, // More than 9:15
        'after_9_30' => 0, // More than 9:30
        'after_9_45' => 0, // More than 9:45
    ];

    foreach ($attendances as $attendance) {
        $checkInTime = Carbon::parse($attendance->check_in)->format('H:i:s');

        if ($checkInTime > '09:45:00') {
            $lateCounts['after_9_45'] += 1;
        } elseif ($checkInTime > '09:30:00') {
            $lateCounts['after_9_30'] += 1;
        } elseif ($checkInTime > '09:15:00') {
            $lateCounts['after_9_15'] += 1;
        }
    }

    // Apply cut rules
    $cutAfter915 = $this->calculateFullDaysCut($lateCounts['after_9_15'], 3); // 3 late = 1 day cut
    $cutAfter930 = $this->calculateFullDaysCut($lateCounts['after_9_30'], 2); // 2 late = 1 day cut
    $cutAfter945 = $this->calculateFullDaysCut($lateCounts['after_9_45'], 1); // 1 late = 1 day cut

    // Total cut
    $lateData['total_late_cut'] = $cutAfter915 + $cutAfter930 + $cutAfter945;
    $lateData['late_cut_days'] = floor($lateData['total_late_cut']);
    $lateData['late_count'] = $lateData['late_cut_days'];

    return $lateData;
}
    private function calculateFullDaysCut($count, $threshold)
    {
        return floor($count / $threshold); // Use floor to round down
    }

    /**
     * Get the salary advance for the user.
     *
     * @param int $userId
     * @param int $month
     * @param int $year
     * @return array
     */
private function getSalaryAdvance($userId, $month, $year)
{
    // Get the advance which is not yet fully paid or fully released
    $salaryAdvance = SalaryRecord::where('employee_id', $userId)
    
        ->where('paid', '!=', 1)
        ->whereColumn('release_amount', '<', 'amount') // release < amount
        ->first();

    return $salaryAdvance ? [
        'id' => $salaryAdvance->id,
        'amount' => $salaryAdvance->amount - $salaryAdvance->release_amount, // only remaining amount
        'record' => $salaryAdvance // pass the record for updating later
    ] : [
        'id' => null,
        'amount' => 0,
        'record' => null
    ];
}


     /**
     * Approve salary.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Approve salary.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveSalary($monthYear)
    {
        // Validate the format of monthYear
        if (!preg_match('/^\d{2}-\d{4}$/', $monthYear)) {
            Log::error("Invalid Month-Year format: $monthYear");
            return redirect()->back()->with('error', 'Invalid month-year format.');
        }
    
        // Split the monthYear into month and year
        [$month, $year] = explode('-', $monthYear);
    
        Log::info("Approving salaries for Month-Year: $monthYear");
    
        // Get all records for the given month and year
        $records = SalaryGenerate::where('month', $month)
            ->where('year', $year)
            ->whereHas('user', function ($query) {
                $query->where('au_company_id', Auth::user()->au_company_id)->where('au_branch_id', Auth::user()->au_branch_id);
            })
            ->get();
    
        if ($records->isEmpty()) {
            Log::info("No records found for Month-Year: $monthYear");
            return redirect()->back()->with('error', 'No records found for the selected month and year.');
        }
    
        // Check if all records are already approved
        $allApproved = $records->every(function($record) {
            return (int) $record->status === 1; // Ensure type casting to integer
        });
    
        if ($allApproved) {
            return redirect()->back()->with('info', 'Salaries for this month are already approved.');
        }
    
        // Update the status of all records to 'Approved'
        foreach ($records as $record) {
            $record->update([
                'status' => 1, // Mark as Approved
                'approved_by' => Auth::id(), // Set the ID of the user who approved
                'approved_date' => now() // Set the current date as approved date
            ]);
        }
    
        Log::info("Salaries for Month-Year: $monthYear have been approved.");
    
        return redirect()->back()->with('success', 'Salaries approved successfully.');
    }
    
    /**
     * View salary chart.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
   public function viewSalaryChart(Request $request, $month, $year)
{
    $monthNumber = $request->input('month', date('n')); 

    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;
    $userType = Auth::user()->au_user_type;
    $uid = Auth::user()->au_id;

    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $userComId = Admin_user::where('au_company_id', $com)->where('au_branch_id', $branch_id)->get();

    $shiftTime = explode(',', $comDetails->au_work_shift);
    $expectedCheckIn = $shiftTime[0];

    // Initialize total weekends and holidays
    $totalWeekends = 0;
    $totalHolidays = 0;

    $request_month = str_pad($month, 2, '0', STR_PAD_LEFT);
    $request_year = $year;
    $start_date = $request_year . '-' . $request_month . '-01';
    $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');

    // Fetch late check-ins (filtered by company and branch)
    $lateCheckIns = EmployeeAttendance::where('company_id', $com)
        ->where('branch_id', $branch_id)
        ->whereMonth('created_at', $request_month)
        ->whereYear('created_at', $request_year)
        ->whereTime('check_in', '>', $expectedCheckIn)
        ->get();

    // Fetch month leaves (filtered by company and branch)
    $monthLeave = Leave::where('company_id', $com)
        ->where('branch_id', $branch_id)
        ->whereMonth('leave_date_accept_start', $request_month)
        ->whereYear('leave_date_accept_start', $request_year)
        ->get();

    // Calculate total weekends in the month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $currentDate = Carbon::create($request_year, $request_month, $day);
        if ($currentDate->dayOfWeek == Carbon::FRIDAY) {
            $totalWeekends++;
        }
    }

    // Fetch holidays for the month and year (filtered by company and branch)
    $holidays = Holiday::where('company_id', $com)
        ->where('branch_id', $branch_id)
        ->where('year', $request_year)
        ->where('month', $request_month)
        ->pluck('date')
        ->toArray();

    $totalHolidays = count($holidays);

    // Fetch users' attendance summary (filtered by company and branch)
    $usersAttendance = DB::table('employee_attendances')
        ->select('user_id', 
                 DB::raw('SUM(incomplete_hour) as incom_hour'),
                 DB::raw('SUM(incomplete_minute) as incom_minute'),
                 DB::raw('SUM(incomplete_sec) as incom_sec'),
                 DB::raw('SUM(over_hour) as over_hour'),
                 DB::raw('SUM(over_minute) as over_minute'),
                 DB::raw('SUM(over_sec) as over_sec'),
                 DB::raw('count(check_in) as check_in'))
        ->whereMonth('created_at', $request_month)
        ->whereYear('created_at', $request_year)
        ->where('company_id', $com)
        ->where('branch_id', $branch_id)
        ->groupBy('user_id')
        ->get();

    return view('page.salary_records.employee_salary_details', compact(
        'comDetails',
        'monthNumber',
        'userComId',
        'usersAttendance',
        'userType',
        'days_in_month',
        'request_year',
        'request_month',
        'lateCheckIns',
        'monthLeave',
        'totalWeekends',
        'totalHolidays'
    ));
}


public function printSalaryChart($month, $year)
{
    $monthName = date('F', mktime(0, 0, 0, $month, 1));
    
    // Fetch salary records for the given month and year without checking approval status
    $salaries = SalaryGenerate::where('month', $month)
        ->where('year', $year)
        ->with('user') // Assuming you have a relationship to fetch employee details
        ->get();

    // Retrieve additional details for employees and sort them by name ascending
    $employees = Admin_user::whereIn('au_id', $salaries->pluck('user_id'))
        ->orderBy('au_user_id', 'asc') // SORT HERE
        ->get()
        ->mapWithKeys(function ($employee) use ($salaries, $month, $year) {
            $salary = $salaries->where('user_id', $employee->au_id)->first();
            if ($salary) {
                $attendances = EmployeeAttendance::where('user_id', $employee->au_id)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->get();
                return [
                    $employee->au_id => [
                        'name' => $employee->au_name,
                        'user_id' => $employee->au_user_id,
                        'basic_salary' => $salary->basic_salary,
                        'total_late_cut' => $salary->total_late_cut,
                        'total_absent_cut' => $salary->total_absent_cut,
                        'total_paid_leave' => $salary->total_paid_leave,
                        'total_accept_days' => ($salary->basic_salary / 30) * $salary->total_paid_leave, // ✅ this line
                        'salary_advance' => $salary->salaryAdvance ? $salary->salaryAdvance->amount : 0,
                        'salary_loan' => $salary->salaryLoan ? $salary->salaryLoan->amount : 0,
                        'final_salary' => $salary->final_salary,
                        'attendance_count' => $attendances->count(),
                    ]
                ];
            }
            return [];
        });

    // Check if there are any approved records
    $approvedBy = $salaries->pluck('approved_by')->filter()->first();
    if (!$approvedBy) {
        $approvedBy = 'Not Approved Yet';
    } else {
        $approvedBy = Admin_user::find($approvedBy)->au_name; // Fetch the name of the approver
    }

    return view('page.salary_records.print-salary', [
        'employees' => $employees,
        'month' => $monthName, 
        'year' => $year,
        'approvedBy' => $approvedBy
    ]);
}

    
}

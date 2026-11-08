@php
$permission = App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)
    ->where('au_branch_id', Auth::user()->au_branch_id)
    ->first();
$permissionStatus = explode('-', $permission->au_permission_status);

$checkOut = App\Models\EmployeeAttendance::where('company_id', $permission->au_company_id)
                ->where('branch_id', Auth::user()->au_branch_id)
                ->where('user_id', Auth::user()->au_id)
                ->orderBy('check_in', 'desc')
                ->first();

// Get current month's absent days count for the logged-in user
$today = Carbon\Carbon::now();
$yesterday = Carbon\Carbon::now()->subDay();
$startOfMonth = Carbon\Carbon::now()->startOfMonth();

// Get all holidays for current month
$holidayDays = App\Models\Holiday::where('company_id', Auth::user()->au_company_id)
    ->where('branch_id', Auth::user()->au_branch_id)
    ->where('year', $today->year)
    ->where('month', $today->month)
    ->pluck('date')
    ->map(function($day) {
        return (int)$day;
    })
    ->toArray();

// Get all attendance records for current month
$attendanceRecords = App\Models\EmployeeAttendance::where('company_id', Auth::user()->au_company_id)
    ->where('branch_id', Auth::user()->au_branch_id)
    ->where('user_id', Auth::user()->au_id)
    ->whereMonth('check_in', $today->month)
    ->whereYear('check_in', $today->year)
    ->get()
    ->keyBy(function($item) {
        return Carbon\Carbon::parse($item->check_in)->format('Y-m-d');
    });

// Get work shift start time
$companyWorkShift = App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)
    ->where('au_branch_id', Auth::user()->au_branch_id)
    ->first();
$workShiftStart = $companyWorkShift ? explode(',', $companyWorkShift->au_work_shift)[0] : '09:00:00';
$workShiftTimestamp = strtotime($workShiftStart);
$lateThreshold = 15 * 60; // 15 minutes late threshold

// Calculate statistics
$absentCount = 0;
$presentCount = 0;
$lateCount = 0;
$workingDays = 0;
$absentDates = [];
$presentDates = [];
$lateDates = [];

// Loop through each day from start of month to yesterday
$currentDate = clone $startOfMonth;
while ($currentDate <= $yesterday) {
    $dateString = $currentDate->format('Y-m-d');
    $currentDay = (int)$currentDate->format('d');
    
    // Check if it's weekend (only Friday)
    $dayOfWeek = date('N', strtotime($dateString));
    $isWeekend = ($dayOfWeek == 5); // 5 = Friday only
    
    // Check if it's a holiday
    $isHoliday = in_array($currentDay, $holidayDays);
    
    // If it's a working day (not weekend and not holiday)
    if (!$isWeekend && !$isHoliday) {
        $workingDays++;
        
        // Check if user attended on this day
        if (isset($attendanceRecords[$dateString])) {
            $presentCount++;
            $presentDates[] = $dateString;
            
            // Check if late
            $checkInTime = date('H:i:s', strtotime($attendanceRecords[$dateString]->check_in));
            $checkInTimestamp = strtotime($checkInTime);
            
            if ($checkInTimestamp - $workShiftTimestamp > $lateThreshold) {
                $lateCount++;
                $lateDates[] = $dateString;
            }
        } else {
            $absentCount++;
            $absentDates[] = $dateString;
        }
    }
    
    $currentDate->addDay();
}

$attendancePercentage = $workingDays > 0 ? round(($presentCount / $workingDays) * 100) : 0;
$latePercentage = $presentCount > 0 ? round(($lateCount / $presentCount) * 100) : 0;

// Determine if user has 3 or more absents
$hasThreeOrMoreAbsents = $absentCount >= 3;
@endphp

@php($inClick = empty($checkOut->in_click) ? 0 : $checkOut->in_click)
@php($outClick = empty($checkOut->out_click) ? 0 : $checkOut->out_click)

@php($out = date('Y-m-d', strtotime($checkOut)))
@php($todayDate = Carbon\Carbon::now()->format('Y-m-d'))

@extends('layouts.master')
@section('content')
@section('title', 'Dashboard')

<style>
    /* dynamic dashboard (month filter, scope line, empty states) */
    .dash-scope { margin: 0 0 12px; color: var(--erp-muted); }
    .dash-scope strong { color: var(--erp-text); }
    .dash-empty { margin: 30px 0; text-align: center; color: var(--erp-muted); }
    .dash-month-form { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
    .dash-month-form select.form-control { width: auto; min-width: 120px; flex: 1 1 120px; height: 34px; }
    .dash-month-form .btn { flex: 0 0 auto; }
    .dash-month-table { margin: 10px 0 0; font-size: 13px; }
    .dash-month-table caption { padding: 0 0 4px; font-weight: 700; color: var(--erp-text); }
    .dash-month-table th, .dash-month-table td { padding: 4px 6px !important; border-top: 1px solid var(--erp-border) !important; }
    .dash-month-table th { font-weight: 400; color: var(--erp-muted); }
    /* no fixed heights: panels are as tall as what they hold */
    .dash-grid .panel { margin-bottom: 16px; }
    .dash-grid .todayssaletitle { height: auto !important; max-height: 360px; overflow-y: auto; }
    .dash-grid .message_inner { height: auto !important; }
    .dash-grid .message_widgets { margin-right: 0; }
    .dash-grid .message_widgets .table { margin-bottom: 10px; }
    .dash-fill { display: flex; flex-direction: column; }
    .dash-fill > .panel-body { flex: 1 1 auto; display: flex; flex-direction: column; }
    .dash-chart-box { position: relative; flex: 1 1 auto; min-height: 260px; }
    .dash-chart-box > canvas { position: absolute; left: 0; top: 0; }
    .dash-year-box { position: relative; height: 300px; }
    .dash-grid #chartContainer.piechartcontainer { height: 230px !important; }
    .dash-grid .dash-scope { margin-bottom: 8px; }
    /* every card on one line on a computer screen; they wrap on tablets and phones */
    .dash-cards { display: flex; flex-wrap: wrap; }
    .dash-cards > [class*="col-"] { float: none; width: auto; flex: 1 1 0; min-width: 200px; }
    .dash-cards .small-box { margin-bottom: 14px; }
    .dash-cards .small-box .inner { padding: 10px 12px; }
    .dash-cards .small-box p { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 50px; }
    .dash-cards .small-box .icon { font-size: 56px; top: 6px; right: 10px; }
    .dash-cards .small-box .icon > * { font-size: inherit; }

    .notice-container {
        width: 100%;
        background-color: var(--erp-notice, var(--erp-green-btn)); /* logo green, or the Theme Colors main colour */
        color: var(--erp-on-primary);
        text-align: left;
        box-sizing: border-box;
        overflow: hidden;
        white-space: nowrap;
    }

    .animated-text {
        margin-top: 8px;
        animation: marquee 30s linear infinite;
        font-weight: bold;
    }

    @keyframes marquee {
        0% {
            transform: translateX(100%);
        }

        100% {
            transform: translateX(-100%);
        }
    }
    
    /* Danger color style for absent warning */
    .absent-warning {
        background-color: var(--erp-red) !important;
        color: var(--erp-on-accent) !important;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 15px;
        text-align: center;
        font-weight: bold;
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
        100% {
            opacity: 1;
        }
    }
    
    .absent-badge {
        background-color: var(--erp-red);
        color: var(--erp-on-accent);
        border-radius: 50%;
        padding: 2px 8px;
        font-size: 12px;
        margin-left: 8px;
        animation: pulse 1s infinite;
        display: inline-block;
    }

    /* Activity Button Styles - Right Side */
    .activity-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--erp-green-btn);
        border: 2px solid #ffffff;
        color: var(--erp-on-primary);
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        z-index: 9997;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: btnPulse 1.5s infinite;
    }

    .activity-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 25px rgba(var(--erp-green-rgb), 0.5);
        background: var(--erp-green-btn-dark);
    }
    
    @keyframes btnPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(var(--erp-green-rgb), 0.7);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(var(--erp-green-rgb), 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(var(--erp-green-rgb), 0);
        }
    }

    /* Activity Sidebar Styles - Right Side */
    .activity-sidebar {
        position: fixed;
        right: -400px;
        top: 0;
        width: 400px;
        height: 100vh;
        background: #1a1a1a;
        box-shadow: -2px 0 20px rgba(0,0,0,0.5);
        z-index: 9999;
        transition: right 0.3s ease-in-out;
        overflow-y: auto;
        padding: 0;
        border-left: 3px solid var(--erp-dash-ok);
    }

    .activity-sidebar.open {
        right: 0;
    }

    .activity-sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 9998;
        display: none;
    }

    .activity-sidebar-overlay.open {
        display: block;
    }

    .activity-sidebar-header {
        background: var(--erp-green-btn);
        color: var(--erp-on-primary);
        padding: 20px;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 1;
        border-bottom: 2px solid #ffffff;
    }

    .activity-sidebar-header h3 {
        margin: 0;
        color: white;
        font-size: 20px;
        font-weight: bold;
    }

    .activity-sidebar-header p {
        margin: 5px 0 0;
        opacity: 0.9;
        font-size: 12px;
        color: #ffffff;
    }

    .activity-sidebar-close {
        position: absolute;
        top: 15px;
        left: 20px;
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .activity-sidebar-close:hover {
        transform: scale(1.1);
        color: var(--erp-dash-title);
    }

    .activity-stat-card {
        background: #2c2c2c;
        margin: 15px;
        padding: 20px;
        border-radius: 10px;
        transition: transform 0.2s;
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid var(--erp-dash-ok);
    }

    .activity-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(var(--erp-dash-ok-rgb), 0.2);
    }

    .activity-stat-card .stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 24px;
    }

    .activity-stat-card .stat-content {
        flex: 1;
    }

    .activity-stat-card h2 {
        font-size: 28px;
        margin: 0;
        font-weight: bold;
        color: #ffffff;
    }

    .activity-stat-card p {
        margin: 0;
        color: #b3b3b3;
        font-size: 13px;
    }

    .stat-success {
        border-left: 4px solid var(--erp-dash-ok);
    }
    .stat-success .stat-icon {
        color: var(--erp-dash-ok);
        background: rgba(var(--erp-dash-ok-rgb), 0.15);
    }

    .stat-danger {
        border-left: 4px solid var(--erp-dash-bad);
    }
    .stat-danger .stat-icon {
        color: var(--erp-dash-bad);
        background: rgba(var(--erp-dash-bad-rgb), 0.15);
    }

    .stat-warning {
        border-left: 4px solid var(--erp-dash-late);
    }
    .stat-warning .stat-icon {
        color: var(--erp-dash-late);
        background: rgba(var(--erp-dash-late-rgb), 0.15);
    }

    .stat-primary {
        border-left: 4px solid var(--erp-dash-info);
    }
    .stat-primary .stat-icon {
        color: var(--erp-dash-info);
        background: rgba(var(--erp-dash-info-rgb), 0.12);
    }

    .stat-info {
        border-left: 4px solid var(--erp-dash-ok);
    }
    .stat-info .stat-icon {
        color: var(--erp-dash-ok);
        background: rgba(var(--erp-dash-ok-rgb), 0.15);
    }

    .progress-bar-custom {
        height: 6px;
        background: #3a3a3a;
        border-radius: 3px;
        overflow: hidden;
        margin: 8px 0 5px;
    }

    .progress-fill {
        height: 100%;
        background: var(--erp-dash-ok);
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    .warning-badge {
        background: var(--erp-dash-bad);
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        display: inline-block;
        margin-top: 5px;
    }

    .attendance-table {
        margin: 15px;
    }

    .attendance-table h5 {
        margin-bottom: 10px;
        font-size: 14px;
        font-weight: bold;
        color: #ffffff;
    }

    .attendance-table table {
        width: 100%;
        background: #2c2c2c;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }

    .attendance-table th {
        background: var(--erp-dash-ok);
        color: #1a1a1a;
        padding: 8px 10px;
        font-size: 12px;
    }

    .attendance-table td {
        padding: 6px 10px;
        font-size: 11px;
        border-bottom: 1px solid #3a3a3a;
        color: #d3d3d3;
    }

    .absent-date {
        color: var(--erp-dash-bad);
        font-weight: bold;
    }

    .present-date {
        color: var(--erp-dash-ok);
    }

    .stat-percent {
        font-size: 11px;
        color: #888888;
    }
    
    /* Scrollbar styling for sidebar */
    .activity-sidebar::-webkit-scrollbar {
        width: 5px;
    }
    
    .activity-sidebar::-webkit-scrollbar-track {
        background: #1a1a1a;
    }
    
    .activity-sidebar::-webkit-scrollbar-thumb {
        background: var(--erp-dash-ok);
        border-radius: 5px;
    }
    
    .activity-sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--erp-dash-ok-dark);
    }
</style>


<div class="notice-container">
    <p class="animated-text">
        Hey... Welcome to IGL ERP v4 🎸. 
        Branch: {{ auth()->user()->au_branch_name }}. 
        This version is currently under construction—it might take a minute to get all the features sorted. 
        Until then, feel free to explore and hit us up if you run into any issues. Meanwhile, stick with the older version for the major stuff. ⚡
    </p>
</div>

@if (in_array(8, $permissionStatus))
    <ol class="breadcrumb">
        @if ($check == false)
            <li>
                <form action="{{ route('payroll.check-in') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <b>Check In</b>
                    </button>
                </form>
            </li>
        @endif

        @if ($check == true && $outCheck == false)
            @if (!$today_feedback_client)
                <li>
                    <form action="{{ route('payroll.check-out') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure...?')">
                            <b>Check Out</b>
                        </button>
                    </form>
                </li>
            @else
                <li>
                    <button class="btn btn-sm btn-danger" disabled>
                        <b>Check Out</b>
                    </button>
                </li>
            @endif
        @endif
    
        @if (!$timeMatch)
            @if (!$hasMarked && $isIpAddressValid && $hasPermission94)
                <li>
                    <form id="lunchMarkForm" action="{{ route('lunch-mark') }}" method="POST">
                        @csrf
                        <button type="button" class="btn btn-sm btn-primary" onclick="showLunchInConfirmation()">
                            <b>Lunch Request</b>
                        </button>
                        <input type="hidden" id="notesInput" name="notes" value="{{ $defaultNotes }}">
                    </form>
                </li>

                {{-- Modal for notes --}}
                <div class="modal fade" id="notesModal" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="notesModalLabel">Enter Notes</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="notesForm" action="" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="notes">Notes:</label>
                                        <textarea class="form-control" id="notes" name="notes"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="submitLunchMarkFormWithNotes()">Save Notes & Mark Lunch</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- NO CONDITION --}}
        @endif

        @if (!$cancelTimeMatch)
            @if ($hasMarked && !$hasCancelled)
                <li>
                    <form id="lunchCancelForm" action="{{ route('cancel-lunch') }}" method="POST">
                        @csrf
                        <button type="button" class="btn btn-sm btn-danger" onclick="openCancelReasonModal()">
                            <b>Lunch Cancel</b>
                        </button>
                        <input type="hidden" id="notesInput" name="notes" value="">
                    </form>
                </li>
            @endif

            <div id="cancelReasonModal" class="modal fade">
                <div class="modal-dialog">
                    <form action="{{ route('cancel-lunch') }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Cancel Today Lunch Request</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Cancel Reason:</label>
                                    <div>
                                        <label class="radio-inline">
                                            <input type="radio" name="cancel_reason" value="Visit"> Visit
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="cancel_reason" value="Service"> Service
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="cancel_reason" value="Setup"> Setup
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="cancel_reason" value="Emergency Leave"> Emergency Leave
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @else
            {{-- NO CONDITION --}}
        @endif
    </ol>
@endif

<div class="row dash-cards">
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
    <div class="small-box bg-green whitecolor dash-card">
        <div class="inner">
       <h4>
    <span class="count-number">
        @if(Auth::check() && Auth::user()->au_user_type == 4)
            {{ $today_feedback }}
        @else
            {{ $today_feedback_client }}
        @endif
    </span>
</h4>
            <p>Today's Follow Up</p>
        </div>
        <div class="icon">
            <i class="fa fa-users"></i>
        </div>
        <a href="{{ url('/todays_followup')}}"
            class="small-box-footer">More Info</a>
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
    <div class="small-box bg-pase whitecolor dash-card">
        <div class="inner">
            <h4><span class="count-number">{{ $feedback}}</span></h4>
            <p>Given Follow Up</p>
        </div>
        <div class="icon">
            <i class="fa fa-user"></i>
        </div>
        <a href="{{ url('/given_followup')}}" class="small-box-footer">More Info</a>
    </div>
</div>

@if (Auth::user()->au_user_type == 4)
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
    <div class="small-box bg-darkgreen whitecolor dash-card">
        <div class="inner">
            <h4><span class="count-number">{{ $lunchCount }}</span></h4>
            <p>Today Lunch</p>
        </div>
        <div class="icon">
            <i class="fa fa-user"></i>
        </div>
        <a href="{{ route('today_office_lunch')}}" class="small-box-footer">All User</a>
    </div>
</div>
@endif

<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
    <div class="small-box bg-bringal whitecolor dash-card">
        <div class="inner">
            <h4><span class="count-number">{{ $total}}</span></h4>

            <p>Total Customer</p>
        </div>
        <div class="icon">
            <i class="fa fa-shopping-bag"></i>
        </div>
        <a href="{{ url('/followup_list')}}"
            class="small-box-footer">More Info</a>
    </div>
</div>

@if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
    @php($intime = [])
    @php($latetime = [])
    @php($comWorkShift = \App\Models\Admin_user::where('au_company_id',Auth::user()->au_company_id)->first())
    @php($workshift = explode(',',$comWorkShift->au_work_shift))

    @foreach ($todayAttendance as $user)
    @php($lateCheckIn = $workshift[0])
    @php($dbtimestamp = strtotime($lateCheckIn))

    @php($checkIn = strtotime($user->check_in))
    @php($abcd = date('H:i:s', $checkIn))
    @php($defg = strtotime($abcd))

    @if ( $defg - $dbtimestamp > 15*60 )
    @php( $latetime[] = $user->id)

    @else
    @php( $intime[] = $user->id)

    @endif
    @endforeach

    <div class="small-box bg-darkgreen whitecolor dash-card">
        <div class="inner">
            <h4>
                <span class="count-number">{{ $todayAttendanceCount }}/{{ $employeCount }}</span> | 
                <span class="count-number">
                    (<a href="{{ route('todayIntimeAttendance') }}" style="color: var(--erp-dash-in); text-decoration: none">{{ count($intime) }}</a>/
                    <a href="{{ route('todayLateAttendance') }}" style="color: var(--erp-dash-out); text-decoration: none">{{ count($latetime) }}</a>)
                </span>
                @if($hasThreeOrMoreAbsents)
                <span class="absent-badge" title="You have {{ $absentCount }} absent days this month">
                    <i class="fa fa-exclamation"></i> {{ $absentCount }}
                </span>
                @endif
            </h4>
            <p>Today Attendance</p>
        </div>
        <div class="icon">
            <i class="fa fa-money"></i>
        </div>
        <a href="{{ route('payroll.today-attendance-show') }}" class="small-box-footer">More Info</a>
    </div>
</div>

@else
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
    <div class="small-box bg-darkgreen whitecolor dash-card">
        <div class="inner">
            <h4><span class="count-number">TK 00</span> </h4>

            <p>Target This Month</p>
        </div>
        <div class="icon">
            <i class="fa fa-money"></i>
        </div>
        <a href="#"
            class="small-box-footer">More Info</a>
    </div>
</div>

@endif

</div>


<div class="row d-flex flex-wrap dash-grid">
    {{-- $monthNames, $monthLabel, $pieRows, $pieHasData and $tk come from UserController@home --}}

    <div class="col-sm-12">
        <p class="dash-scope">
            <i class="fa fa-filter"></i> Figures below: <strong>{{ $dash->scopeLabel }}</strong>
            @if($dash->scope === 'team') (sales entered by your team) @elseif($dash->scope === 'self') (sales you entered) @endif
        </p>
    </div>

    <div class="col-sm-12 col-md-7 d-flex">
        <div class="panel panel-bd flex-fill w-100 dash-fill dash-accent-green">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4 class="best-sale-title">Best Sale Product, {{ $dashYear }}</h4>
                    <a href="{{ route('topSellingProducts') }}" class="btn btn-success text-white best-sale-seeall">See All</a>
                </div>
            </div>
            <div class="panel-body">
                @if($topProducts->isEmpty())
                    <p class="dash-empty">No product sales in {{ $dashYear }} yet.</p>
                @else
                    <div class="dash-chart-box"><canvas id="topSellingChart"></canvas></div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-5 d-flex">
        <div class="panel panel-bd flex-fill w-100 dash-accent-lightblue">
            <div class="panel-heading">
                <form method="GET" action="{{ route('home') }}" class="dash-month-form" id="form1">
                    <select name="m" class="form-control dont-select-me" aria-label="Month">
                        @foreach($monthNames as $i => $name)
                            <option value="{{ $i + 1 }}" @selected($dashMonth === $i + 1)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="y" class="form-control dont-select-me" aria-label="Year">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                            <option value="{{ $y }}" @selected($dashYear === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="btn filterbutton"><i class="fa fa-search"></i> Filter</button>
                </form>
            </div>
            <div class="panel-body">
                @if($pieHasData)
                    <div id="chartContainer" class="piechartcontainer"></div>
                @else
                    <p class="dash-empty">No figures for {{ $monthLabel }}.</p>
                @endif
                <table class="table table-condensed dash-month-table">
                    <caption>{{ $monthLabel }}</caption>
                    @foreach($pieRows as $row)
                        <tr><th><span class="dash-swatch" style="background: var(--erp-chart-{{ $row['key'] }})"></span>{{ $row['label'] }}</th><td class="text-right">{{ $tk($row['y']) }}</td></tr>
                    @endforeach
                    @if($dash->canSeeCosts)
                        <tr><th><span class="dash-swatch" style="background: var(--erp-chart-collected)"></span>Collected from customers</th><td class="text-right">{{ $tk($monthTotals['collections']) }}</td></tr>
                    @endif
                    <tr><th>Invoices</th><td class="text-right">{{ $monthTotals['invoices'] }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8 d-flex">
        <div class="panel panel-bd flex-fill w-100 dash-accent-black">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4 class="charttitle">{{ $dash->canSeeCosts ? 'Sales and Purchases' : 'Sales' }}, {{ $dashYear }} (TK)</h4>
                </div>
                <div class="panel-body">
                    <div class="dash-year-box"><canvas id="yearlyreportall"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 d-flex">
        <div class="panel panel-bd lobidisable flex-fill w-100 dash-accent-red">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Todays Overview</h4>
                </div>
            </div>
            <div class="panel-body">
                <div class="message_inner">
                    <div class="message_widgets">
                        <table class="table table-bordered table-striped table-hover">
                            <tr><th>Todays Report</th><th class="text-right">TK</th></tr>
                            <tr><th><span class="dash-swatch" style="background: var(--erp-chart-sales)"></span>Total Sales</th><td class="text-right">{{ $tk($todayTotals['sales'] + $todayTotals['service']) }}</td></tr>
                            <tr><th><span class="dash-swatch" style="background: var(--erp-chart-collected)"></span>Collected</th><td class="text-right">{{ $tk($todayTotals['collections']) }}</td></tr>
                            @if($dash->canSeeCosts)
                                <tr><th><span class="dash-swatch" style="background: var(--erp-chart-purchase)"></span>Total Purchase</th><td class="text-right">{{ $tk($todayTotals['purchases']) }}</td></tr>
                                <tr><th><span class="dash-swatch" style="background: var(--erp-chart-expense)"></span>Expenses</th><td class="text-right">{{ $tk($todayTotals['expenses']) }}</td></tr>
                            @endif
                            <tr><th>Invoices</th><td class="text-right">{{ $todayTotals['invoices'] }}</td></tr>
                        </table>

                        <table class="table table-bordered table-striped table-hover">
                            <tr>
                                <th>Last Sale</th>
                                <td class="text-right">
                                    @if($lastSale)
                                        {{ $tk($lastSale->amount) }}<br>
                                        <small class="text-muted">{{ $lastSale->invoice }} &middot; {{ $lastSale->customer }} &middot; {{ $lastSale->date }}</small>
                                    @else
                                        <span class="text-muted">No sales yet</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 {{ $supplierDue !== null ? 'col-md-6' : 'col-md-12' }} d-flex">
        <div class="panel panel-bd flex-fill w-100 dash-accent-green">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4 class="charttitle">Customer Due</h4>
                    <a href="{{ route('customer.customer_list') }}" class="btn btn-success text-white best-sale-seeall">See All</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive todayssaletitle">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr><th>SL.</th><th>Customer Name</th><th>Last Sale</th><th class="text-right">Due Amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($customerDue['rows'] as $i => $row)
                                <tr><td>{{ $i + 1 }}</td><td>{{ $row->name }}</td><td>{{ $row->last_date }}</td><td class="text-right">{{ $tk($row->due) }}</td></tr>
                            @empty
                                <tr><td class="text-center" colspan="4">No customer due</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" align="right"><b>Total{{ $customerDue['count'] > $customerDue['rows']->count() ? ' (all ' . $customerDue['count'] . ' customers)' : '' }}:</b></td>
                                <td class="text-right"><b>{{ $tk($customerDue['total']) }}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($supplierDue !== null)
    <div class="col-sm-12 col-md-6 d-flex">
        <div class="panel panel-bd flex-fill w-100 dash-accent-red">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4 class="charttitle">Supplier Due</h4>
                    <a href="{{ route('inventory.supplier.accounts.account-statement') }}" class="btn btn-success text-white best-sale-seeall">See All</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive todayssaletitle">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr><th>SL.</th><th>Supplier Name</th><th>Last Purchase</th><th class="text-right">Due Amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($supplierDue['rows'] as $i => $row)
                                <tr><td>{{ $i + 1 }}</td><td>{{ $row->name }}</td><td>{{ $row->last_date }}</td><td class="text-right">{{ $tk($row->due) }}</td></tr>
                            @empty
                                <tr><td class="text-center" colspan="4">No supplier due</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" align="right"><b>Total{{ $supplierDue['count'] > $supplierDue['rows']->count() ? ' (all ' . $supplierDue['count'] . ' suppliers)' : '' }}:</b></td>
                                <td class="text-right"><b>{{ $tk($supplierDue['total']) }}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-sm-12 col-md-12 d-flex">
        <div class="panel panel-bd flex-fill w-100 dash-accent-lightblue">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4 class="charttitle">Todays Sales Report</h4>
                    <a href="{{ route('reports.sell-reports') }}" class="btn btn-success text-white best-sale-seeall">See All</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive todayssaletitle">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr><th>SL.</th><th>Customer Name</th><th>Invoice No</th><th>Sold By</th><th class="text-right">Total Amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($todaySales['rows'] as $i => $row)
                                <tr><td>{{ $i + 1 }}</td><td>{{ $row->customer }}</td><td>{{ $row->invoice }}</td><td>{{ $row->seller_name }}</td><td class="text-right">{{ $tk($row->amount) }}</td></tr>
                            @empty
                                <tr><td class="text-center" colspan="5">No sales today</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" align="right"><b>Total{{ $todaySales['count'] > $todaySales['rows']->count() ? ' (all ' . $todaySales['count'] . ' invoices)' : '' }}:</b></td>
                                <td class="text-right"><b>{{ $tk($todaySales['total']) }}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Read by assets/js/dashboard.js --}}
    <input type="hidden" id="dash-month-summary" value="{{ json_encode($pieRows) }}">
    <input type="hidden" id="dash-month-title" value="{{ $monthLabel }}">
    <input type="hidden" id="totalsalep" value="{{ $pieRows[0]['y'] }}">
    <input type="hidden" id="totalplurchasep" value="{{ $monthTotals['purchases'] ?? 0 }}">
    <input type="hidden" id="totalexpensep" value="{{ $monthTotals['expenses'] ?? 0 }}">
    <input type="hidden" id="totalemployeesalaryp" value="{{ $monthTotals['salary'] ?? 0 }}">
    <input type="hidden" id="totalservicep" value="{{ $monthTotals['service'] }}">
    <input type="hidden" id="month" value="{{ $monthNames[$dashMonth - 1] }}">
    <input type="hidden" id="tlvmonthsale" value="{{ $monthTotals['sales'] }}">
    <input type="hidden" id="tlvmonthpurchase" value="{{ $monthTotals['purchases'] ?? 0 }}">
    <input type="hidden" id="salspurhcaselabel" value="Sales And Purchase Report Summary - {{ $dashYear }}">
    <input type="hidden" id="bestsalelabel" value="{{ $topProducts->pluck('name')->implode(',') }},">
    <input type="hidden" id="bestsaledata" value="{{ $topProducts->pluck('qty')->implode(',') }},">
    <input type="hidden" id="bestsalemax" value="{{ (float) $topProducts->max('qty') }}">

</div>

<div id="ipMismatchModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('payroll.check-in') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">IP Address Mismatch</h4>
                </div>
                <div class="modal-body">
                    <p>Your current IP address does not match the expected IP address for check-in.<br>
                      Please ensure why you are using the out of network to proceed with check-in.</p>
                    
                    <div class="form-group">
                        <label for="reason">Reason:</label>
                        <select class="form-control" id="reason" name="reason">
                            <option selected disabled value="">Select Reason</option>
                            <option value="visit">Visit</option>
                            <option value="service">Service</option>
                            <option value="setup">Setup</option>
                            <option value="Page Moderator">Page Moderator</option>
                        </select>
                    </div>

                    <div id="forWhomGroup" class="form-group" style="display: none;">
                        <label for="for_whom">For Whom:</label>
                        <select class="form-control itemSearch" id="for_whom" name="for_whom"></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Activity Button -->
<button class="activity-btn" onclick="toggleActivitySidebar()">
    <i class="fa fa-calendar-check-o"></i>
</button>

<!-- Activity Sidebar Overlay -->
<div class="activity-sidebar-overlay" id="activitySidebarOverlay" onclick="closeActivitySidebar()"></div>

<!-- Activity Sidebar -->
<div class="activity-sidebar" id="activitySidebar">
    <div class="activity-sidebar-header">
        <button class="activity-sidebar-close" onclick="closeActivitySidebar()">&times;</button>
        <h3><i class="fa fa-user-circle"></i> My Activity</h3>
        <p>{{ Auth::user()->au_name }} | {{ Carbon\Carbon::now()->format('F Y') }}</p>
    </div>
    
    <!-- Working Days -->
    <div class="activity-stat-card stat-primary">
        <div class="stat-icon">
            <i class="fa fa-calendar"></i>
        </div>
        <div class="stat-content">
            <h2>{{ $workingDays }}</h2>
            <p>Total Working Days</p>
            <div class="progress-bar-custom">
                <div class="progress-fill" style="width: 100%"></div>
            </div>
        </div>
    </div>
    
    <!-- Present Days -->
    <div class="activity-stat-card stat-success">
        <div class="stat-icon">
            <i class="fa fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h2>{{ $presentCount }}</h2>
            <p>Present Days</p>
            <div class="progress-bar-custom">
                <div class="progress-fill" style="width: {{ $attendancePercentage }}%"></div>
            </div>
            <small class="stat-percent">{{ $attendancePercentage }}% Attendance Rate</small>
        </div>
    </div>
    
    <!-- Absent Days -->
    <div class="activity-stat-card stat-danger">
        <div class="stat-icon">
            <i class="fa fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <h2>{{ $absentCount }}</h2>
            <p>Absent Days</p>
            @if($absentCount >= 3)
                <span class="warning-badge">
                    <i class="fa fa-exclamation-triangle"></i> Warning: 3+ Absents
                </span>
            @endif
        </div>
    </div>
    
    <!-- Late Arrivals -->
    <div class="activity-stat-card stat-warning">
        <div class="stat-icon">
            <i class="fa fa-clock-o"></i>
        </div>
        <div class="stat-content">
            <h2>{{ $lateCount }}</h2>
            <p>Late Arrivals</p>
            <div class="progress-bar-custom">
                <div class="progress-fill" style="width: {{ $latePercentage }}%; background: var(--erp-dash-late);"></div>
            </div>
            <small class="stat-percent">{{ $latePercentage }}% of present days</small>
        </div>
    </div>
    
    <!-- Attendance Rate Overall -->
    <div class="activity-stat-card stat-info">
        <div class="stat-icon">
            <i class="fa fa-line-chart"></i>
        </div>
        <div class="stat-content">
            <h2>{{ $attendancePercentage }}%</h2>
            <p>Overall Attendance Rate</p>
            <div class="progress-bar-custom">
                <div class="progress-fill" style="width: {{ $attendancePercentage }}%"></div>
            </div>
        </div>
    </div>
    
    @if(count($absentDates) > 0)
    <div class="attendance-table">
        <h5 style="color: var(--erp-dash-bad);">
            <i class="fa fa-calendar-times-o"></i> Absent Dates ({{ count($absentDates) }})
        </h5>
        <div style="max-height: 200px; overflow-y: auto;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absentDates as $date)
                    <tr>
                        <td class="absent-date">{{ date('d-m-Y', strtotime($date)) }}</td>
                        <td>{{ date('l', strtotime($date)) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    
    @if(count($lateDates) > 0)
    <div class="attendance-table">
        <h5 style="color: var(--erp-dash-late);">
            <i class="fa fa-hourglass-half"></i> Late Arrival Dates ({{ count($lateDates) }})
        </h5>
        <div style="max-height: 200px; overflow-y: auto;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lateDates as $date)
                    <tr>
                        <td>{{ date('d-m-Y', strtotime($date)) }}</td>
                        <td>{{ date('l', strtotime($date)) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    
    @if(count($presentDates) > 0)
    <div class="attendance-table">
        <h5 style="color: var(--erp-dash-ok);">
            <i class="fa fa-check-square-o"></i> Present Dates ({{ count($presentDates) }})
        </h5>
        <div style="max-height: 200px; overflow-y: auto;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($presentDates, 0, 20) as $date)
                    <tr>
                        <td class="present-date">{{ date('d-m-Y', strtotime($date)) }}</td>
                        <td>{{ date('l', strtotime($date)) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($presentDates) > 20)
                <p class="text-center text-muted" style="font-size: 11px; margin-top: 5px; color: #888;">... and {{ count($presentDates) - 20 }} more days</p>
            @endif
        </div>
    </div>
    @endif
</div>

<script src="{{ asset('assets/js/Chart.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/canvasjs.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/dashboard.js') }}?v={{ @filemtime(base_path('assets/js/dashboard.js')) }}" type="text/javascript"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
// Activity Sidebar Functions - Auto open on page load and remember state
function toggleActivitySidebar() {
    const sidebar = document.getElementById('activitySidebar');
    const overlay = document.getElementById('activitySidebarOverlay');
    
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
    
    // Save state to localStorage
    const isOpen = sidebar.classList.contains('open');
    localStorage.setItem('activitySidebarOpen', isOpen);
    
    // Prevent body scroll when sidebar is open
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

function closeActivitySidebar() {
    const sidebar = document.getElementById('activitySidebar');
    const overlay = document.getElementById('activitySidebarOverlay');
    
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    
    // Save closed state to localStorage
    localStorage.setItem('activitySidebarOpen', false);
}

function openActivitySidebar() {
    const sidebar = document.getElementById('activitySidebar');
    const overlay = document.getElementById('activitySidebarOverlay');
    
    sidebar.classList.add('open');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    
    // Save open state to localStorage
    localStorage.setItem('activitySidebarOpen', true);
}

// Auto open sidebar on page load if it was open before or first time visit
document.addEventListener('DOMContentLoaded', function() {
    const savedState = localStorage.getItem('activitySidebarOpen');
    
    // If no saved state (first time visit), default to open
    if (savedState === null || savedState === 'true') {
        openActivitySidebar();
    }
});

// Close sidebar with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeActivitySidebar();
    }
});
</script>

<script>
// Year chart: sales (and purchases for the admin) in TK per month. Chart.js 2.5 options.
document.addEventListener("DOMContentLoaded", function () {
    var el = document.getElementById('yearlyreportall');
    if (!el) { return; }
    var themeChart = window.ERP_CHART || { sales: '#12a852', purchase: '#c4161f', salesFill: 'rgba(18, 168, 82, 0.15)', purchaseFill: 'rgba(196, 22, 31, 0.10)' };
    var money = function (v) { return 'TK ' + Number(v).toLocaleString('en-US', { maximumFractionDigits: 2 }); };
    var datasets = [{
        label: 'Sales',
        data: @json($yearly['sales']),
        borderColor: themeChart.sales,
        backgroundColor: themeChart.salesFill,
        pointBackgroundColor: themeChart.sales,
        pointRadius: 3,
        lineTension: 0.15,
        borderWidth: 2,
        fill: true
    }];
    @if($yearly['purchases'] !== null)
    datasets.push({
        label: 'Purchases',
        data: @json($yearly['purchases']),
        borderColor: themeChart.purchase,
        backgroundColor: themeChart.purchaseFill,
        pointBackgroundColor: themeChart.purchase,
        pointRadius: 3,
        lineTension: 0.15,
        borderWidth: 2,
        fill: true
    });
    @endif
    new Chart(el.getContext('2d'), {
        type: 'line',
        data: { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { position: 'top' },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: { label: function (item, data) { return data.datasets[item.datasetIndex].label + ': ' + money(item.yLabel); } }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        suggestedMax: datasets.some(function (d) { return d.data.some(function (v) { return v > 0; }); }) ? undefined : 1000, // empty year: whole-number scale
                        callback: function (v) { return money(v); }
                    },
                    gridLines: { color: document.documentElement.getAttribute('data-theme') === 'dark' ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)' }
                }],
                xAxes: [{ gridLines: { display: false } }]
            }
        }
    });
});
</script>

<script>
// Best-selling products of the year: quantity sold (amount in the tooltip). Chart.js 2.5 options.
document.addEventListener("DOMContentLoaded", function () {
    var el = document.getElementById('topSellingChart');
    if (!el) { return; }
    var themeChart = window.ERP_CHART || { bar: 'rgba(18, 168, 82, 0.85)', barBorder: '#0e8a43' };
    var names = @json($topProducts->pluck('name')->values());
    var amounts = @json($topProducts->pluck('amount')->values());
    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
            labels: names,
            datasets: [{
                label: 'Quantity sold',
                data: @json($topProducts->pluck('qty')->values()),
                // one colour per product (green, red, light blue, black, dark blue); previous style keeps one colour
                backgroundColor: themeChart.categorical ? names.map(function (n, i) { return themeChart.categorical[i]; }) : themeChart.bar,
                borderColor: themeChart.categorical ? names.map(function (n, i) { return themeChart.categorical[i]; }) : themeChart.barBorder,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            tooltips: {
                callbacks: {
                    title: function (items) { return names[items[0].index]; },
                    label: function (item) { return 'Quantity: ' + item.yLabel + '  |  TK ' + Number(amounts[item.index]).toLocaleString('en-US', { maximumFractionDigits: 2 }); }
                }
            },
            scales: {
                yAxes: [{
                    ticks: { beginAtZero: true, callback: function (v) { return Math.floor(v) === v ? v : null; } },
                    scaleLabel: { display: true, labelString: 'Quantity sold' },
                    gridLines: { color: document.documentElement.getAttribute('data-theme') === 'dark' ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)' }
                }],
                xAxes: [{
                    barPercentage: Math.min(0.6, 0.15 * names.length), // same bar width whether 1 or 5 products
                    gridLines: { display: false },
                    ticks: { autoSkip: false, callback: function (label) { return label.length > 18 ? label.slice(0, 17) + '…' : label; } }
                }]
            }
        }
    });
});
</script>

<script>
  $(document).ready(function() {
      @if(session('ip_validation'))
          $('#ipMismatchModal').modal('show');
          {{ session()->forget('ip_validation') }}
      @endif

      $('#reason').change(function() {
          var selectedReason = $(this).val();
          var forWhomGroup = $('#forWhomGroup');
          var forWhomSelect = $('#for_whom');
          
          if (selectedReason === 'visit' || selectedReason === 'setup' || selectedReason === 'service') {
              forWhomGroup.show();
              initSelect2(selectedReason);
          } else {
              forWhomGroup.hide();
              forWhomSelect.val(null).trigger('change');
          }
      });
      
      function initSelect2(selectedReason) {
          const customerUrl = "{{ route('get_customer') }}";
          
          $('#for_whom').select2({
              tags: false,
              multiple: false,
              tokenSeparators: [',', ' '],
              minimumInputLength: 3,
              minimumResultsForSearch: 10,
              placeholder: "Select a Customer",
              ajax: {
                  url: customerUrl,
                  dataType: "json",
                  delay: 250,
                  data: function (params) {
                      return {
                          reason: selectedReason,
                          term: params.term
                      };
                  },
                  processResults: function (data) {
                      return {
                          results: $.map(data, function (item) {
                              return {
                                  text: item.qb_company_name + ' (' + item.qb_mobile + ')',
                                  id: item.qb_id
                              };
                          })
                      };
                  },
                  cache: true
              }
          });
      }
  });
</script>

<script>
    $(".alert").delay(5000).slideUp(200, function() {
        $(this).alert('close');
    });
</script>

<script>
  $('form').submit(function() {
    $(this).find("button[type='submit']").prop('disabled',true);
  });
</script>

<script>
    function showLunchInConfirmation() {
        if (confirm('Do you have any notes?')) {
            $('#notesModal').modal('show');
        } else {
            $('#notesInput').val('{{ $defaultNotes }}');
            $('#lunchMarkForm').submit();
        }
    }

    function submitLunchMarkFormWithNotes() {
        $('#notesInput').val($('#notes').val());
        $('#lunchMarkForm').submit();
    }

    function openCancelReasonModal() {
        $('#cancelReasonModal').modal('show');
    }

    function submitCancelReason() {
        var cancelReason = $('#cancel_reason').val();
        $('#lunchCancelForm').append('<input type="hidden" name="cancel_reason" value="' + cancelReason + '">');
        $('#lunchCancelForm').submit();
    }
</script>
@endsection
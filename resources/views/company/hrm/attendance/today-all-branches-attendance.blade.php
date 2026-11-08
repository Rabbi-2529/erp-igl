@php use Carbon\Carbon; @endphp
@extends('layouts.master')

@section('title', 'Today\'s All Branches Attendance')
@section('human_resource_class', 'active')
@section('attendance_class', 'active')
@section('today_attendance_class', 'active')

@section('content')
<style>
.branch-section {
    margin-bottom: 30px;
    border: 1px solid #e3e3e3;
    border-radius: 5px;
    padding: 15px;
    background: #f9f9f9;
}
.branch-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.status-late {
    color: #dc3545;
    font-weight: bold;
}
.status-ontime {
    color: #28a745;
    font-weight: bold;
}
.status-incomplete {
    color: #ffc107;
    font-weight: bold;
}
.branch-stats .badge {
    margin-left: 5px;
    font-size: 12px;
}
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
</style>

<section class="content">
    <section class="content-header">
        <!-- Success Messages -->
        @if(session()->has('msg'))
            <div class="alert alert-{{ session('type') }} alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('msg') }}
            </div>
        @endif
        
        @if(session()->has('checkout'))
            <div class="alert alert-{{ session('type') }} alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('checkout') }}
            </div>
        @endif
        
        <div class="row">
            <div class="col-md-8">
                <h1 style="margin: 0;">
                    <i class="fa fa-building"></i> Today's Attendance - All Branches
                </h1>
            </div>
            <div class="col-md-4 text-right">
                <span class="badge badge-primary" style="font-size: 14px; padding: 8px 15px;">
                    <i class="fa fa-clock-o"></i> {{ now()->format('h:i A') }}
                </span>
            </div>
        </div>
    </section>

    <div class="box box-primary">
        <div class="box-header with-border">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h4 style="margin-top: 0;"><i class="fa fa-info-circle"></i> Today's Summary - {{ $today->format('l, F d, Y') }}</h4>
                        <div class="row text-center">
                            @php
                                $totalCheckedIn = 0;
                                $totalCheckedOut = 0;
                                $totalLate = 0;
                                $totalBranches = $branches->count();
                                
                                foreach($todayAttendance as $branchData) {
                                    $totalCheckedIn += $branchData['checked_in'];
                                    $totalCheckedOut += $branchData['checked_out'];
                                    $totalLate += $branchData['late_comers'];
                                }
                            @endphp
                            <div class="col-md-3 col-sm-6">
                                <h3 style="margin: 5px 0; color: #333;">{{ $totalBranches }}</h3>
                                <p style="margin: 0;">Total Branches</p>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <h3 style="margin: 5px 0; color: #28a745;">{{ $totalCheckedIn }}</h3>
                                <p style="margin: 0;">Total Checked In</p>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <h3 style="margin: 5px 0; color: #17a2b8;">{{ $totalCheckedOut }}</h3>
                                <p style="margin: 0;">Total Checked Out</p>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <h3 style="margin: 5px 0; color: #ffc107;">{{ $totalLate }}</h3>
                                <p style="margin: 0;">Total Late Arrivals</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box-body">
            @php
                $hasAttendanceData = false;
                foreach($branches as $branch) {
                    $branchData = $todayAttendance[$branch->branch_id] ?? null;
                    if ($branchData && !$branchData['attendance']->isEmpty()) {
                        $hasAttendanceData = true;
                        break;
                    }
                }
            @endphp

            @if($hasAttendanceData)
                @foreach($branches as $branch)
                    @php
                        $branchData = $todayAttendance[$branch->branch_id] ?? null;
                        if (!$branchData || $branchData['attendance']->isEmpty()) {
                            continue;
                        }
                    @endphp

                    <div class="branch-section">
                        <div class="branch-header">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 style="margin: 0; font-size: 18px;">
                                        <i class="fa fa-building"></i> {{ $branch->branch_name }}
                                    </h3>
                                    <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 13px;">
                                        Branch ID: {{ $branch->branch_id }} | 
                                        Total Employees: {{ $branchData['attendance']->count() }} | 
                                        Checked In: {{ $branchData['checked_in'] }}
                                    </p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="branch-stats">
                                        <span class="badge badge-success">
                                            {{ $branchData['checked_in'] }} In
                                        </span>
                                        <span class="badge badge-info">
                                            {{ $branchData['checked_out'] }} Out
                                        </span>
                                        <span class="badge badge-warning">
                                            {{ $branchData['late_comers'] }} Late
                                        </span>
                                        <span class="badge badge-danger">
                                            {{ $branchData['pending_checkout'] }} Pending
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover branch-table">
                                <thead>
                                    <tr>
                                        <th width="5%">SL</th>
                                        <th width="10%">Emp ID</th>
                                        <th width="15%">User Name</th>
                                        <th width="10%">Department</th>
                                        <th width="10%">Check In</th>
                                        <th width="12%">Check In IP</th>
                                        <th width="10%">Check Out</th>
                                        <th width="12%">Check Out IP</th>
                                        <th width="8%">Work Hours</th>
                                        <th width="8%">Status</th>
                                    </tr>
                                </thead>
                               <tbody>
    @foreach($branchData['attendance'] as $key => $attendance)
        @php
            $sl = $key + 1;

            // Company default work shift
            $comWorkShift = \App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)->first();
            $au_user = \App\Models\Admin_user::where('au_id', $attendance->user_id)->first();

            if ($au_user && $au_user->au_shifts != null) {
                $auWorkshift = \App\Models\PayrollShiftManagement::where('id', $au_user->au_shifts)->first();
                $workShift = $auWorkshift ? explode(',', $auWorkshift->working_hour) : explode(',', $comWorkShift->au_work_shift);
            } else {
                $workShift = explode(',', $comWorkShift->au_work_shift);
            }

            $lateCheckIn = $workShift[0] ?? '09:00:00';
            $dbtimestamp = strtotime($lateCheckIn);
            $checkIn = strtotime($attendance->check_in);
            $checkInTime = date('H:i:s', $checkIn);
            $checkInTimestamp = strtotime($checkInTime);
            $isLate = ($checkInTimestamp - $dbtimestamp) > (60 * 60); // >1 hour late

            $hasCheckedOut = !empty($attendance->check_out);
            if ($hasCheckedOut) {
                $lateCheckOut = $workShift[1] ?? '18:00:00';
                $dbtimestampOut = strtotime($lateCheckOut);
                $checkOut = strtotime($attendance->check_out);
                $checkOutTime = date('H:i:s', $checkOut);
                $checkOutTimestamp = strtotime($checkOutTime);
                $isEarly = ($dbtimestampOut - $checkOutTimestamp) > (15 * 60); // 15 min early
            }

            if ($attendance->check_in && $attendance->check_out) {
                $in_time = date('H:i', strtotime($attendance->check_in));
                $check_out = date('H:i', strtotime($attendance->check_out));
                $date1 = Carbon::parse($in_time);
                $date2 = Carbon::parse($check_out);
                $total_time = $date1->diffInMinutes($date2);
                $working_hours = floor($total_time/60) . 'H ' . ($total_time%60) . 'M';
                $isComplete = floor($total_time/60) >= 8;
            } else {
                $working_hours = '0H 0M';
                $isComplete = false;
            }

            $ipStatus = $attendance->user_ip ? \App\Models\Admin_user::illegalLlegalEntry($attendance->user_ip) : null;
            $ipStatusOut = $attendance->user_ip_checkout ? \App\Models\Admin_user::illegalLlegalEntry($attendance->user_ip_checkout) : null;
        @endphp

        <tr>
            <td class="text-center">{{ $sl }}</td>
            <td>{{ $attendance->au_user_id ?? 'N/A' }}</td>
            <td>{{ $attendance->au_name ?? 'N/A' }}</td>
           <td>{{ $attendance->au_department ? \App\Models\Department::deptName($attendance->au_department) : 'N/A' }}</td>


            <!-- Check In -->
            <td class="text-center @if($isLate) status-late @else status-ontime @endif">
                {{ $attendance->check_in ? date('H:i A', strtotime($attendance->check_in)) : 'N/A' }}
            </td>

            <!-- Check In IP -->
            <td>
                <small>
                    {{ $attendance->user_ip ?? 'N/A' }}
                    @if($ipStatus)
                        <span class="{{ $ipStatus === 'Illegal' ? 'status-late' : 'status-ontime' }}">
                            ({{ $ipStatus }})
                        </span>
                    @endif
                </small>
            </td>

            <!-- Check Out -->
            <td class="text-center @if($hasCheckedOut && $isEarly) status-late @elseif($hasCheckedOut) status-ontime @else status-incomplete @endif">
                {{ $hasCheckedOut ? date('H:i A', strtotime($attendance->check_out)) : 'Not Checked Out' }}
            </td>

            <!-- Check Out IP -->
            <td>
                @if($hasCheckedOut)
                    <small>
                        {{ $attendance->user_ip_checkout ?? 'N/A' }}
                        @if($ipStatusOut)
                            <span class="{{ $ipStatusOut === 'Illegal' ? 'status-late' : 'status-ontime' }}">
                                ({{ $ipStatusOut }})
                            </span>
                        @endif
                    </small>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            <!-- Working Hours -->
            <td class="text-center @if(!$hasCheckedOut) status-incomplete @endif">
                {{ $working_hours }}
            </td>

            <!-- Status -->
            <td class="text-center @if(!$hasCheckedOut || !$isComplete) status-incomplete @else status-ontime @endif">
                @if(!$hasCheckedOut)
                    Incomplete
                @elseif($isComplete)
                    Complete
                @else
                    Incomplete
                @endif
            </td>
        </tr>
    @endforeach
</tbody>

                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-warning text-center">
                    <h4><i class="fa fa-warning"></i> No Attendance Data Found</h4>
                    <p>No attendance records found for today across all branches.</p>
                </div>
            @endif
        </div>

        <div class="box-footer">
            <div class="row">
                <div class="col-md-12 text-center">
                    <small class="text-muted">
                        <i class="fa fa-refresh"></i> Page auto-refreshes every 5 minutes | 
                        Last updated: {{ now()->format('h:i:s A') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Attendance Details Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="attendanceModalLabel">
                    <i class="fa fa-calendar"></i> Attendance Details
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="userData">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p>Loading attendance details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_style')
<style>
.branch-table {
    font-size: 13px;
}
.branch-table th {
    background-color: #4e73df;
    color: white;
    text-align: center;
}
.branch-table td {
    vertical-align: middle;
}
.box {
    box-shadow: 0 0 1px rgba(0,0,0,.1);
}
</style>
@endsection

@section('custom_script')
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable for each branch table
        $('.branch-table').DataTable({
            "order": [[0, "asc"]],
            "paging": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "responsive": true,
            "autoWidth": false,
            "language": {
                "search": "Search within table:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "emptyTable": "No data available in table"
            },
            "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>'
        });

        // Auto refresh every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);

        // Delete confirmation
        function deletesup() {
            return confirm("Are you sure want to delete this?");
        }

        // Load attendance details in modal
        function userAttendance(userid, year, month) {
            $.ajax({
                type: "GET",
                url: "{{ route('payroll.attendance-ajax') }}",
                data: {userid: userid, year: year, month: month},
                beforeSend: function() {
                    $('#userData').html(`
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p>Loading attendance details...</p>
                        </div>
                    `);
                },
                success: function (result) {
                    $('#userData').html(result);
                },
                error: function() {
                    $('#userData').html(`
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle"></i> 
                            Failed to load attendance details. Please try again.
                        </div>
                    `);
                }
            });
        }

        // Print attendance details
        function userAttendanceDetailsPrint(value) {
            let month = $("#month").val();
            let year = $("#year").val();
            let user = value;
            let route = "{!! route('payroll.attendance-print') !!}?month=" + month + "&year=" + year + "&user=" + user;
            window.open(route, '_blank');
        }

        // Real-time clock update
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            $('.live-time').text(timeString);
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock();
    });
</script>
@endsection
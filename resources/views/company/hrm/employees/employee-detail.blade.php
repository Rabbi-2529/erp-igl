@php use Carbon\Carbon; @endphp
@extends('layouts.master')

@section('title', 'Employee Details - ' . $employee->au_name)
@section('company_class', 'active')
@section('hrm_class', 'active')
@section('employee_list_class', 'active')

@section('content')
<style>
.info-box-icon {
    padding: 20px;
}
.detail-label {
    font-weight: 600;
    color: #555;
}
.detail-value {
    color: #333;
}
</style>

<section class="content">
    <section class="content-header">
        <h1>Employee Details</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('company.hrm.employees.list') }}">Employees</a></li>
            <li class="active">{{ $employee->au_name }}</li>
        </ol>
    </section>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <!-- Employee Information -->
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <div class="text-center">
                        <i class="fa fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h3 class="profile-username text-center">{{ $employee->au_name }}</h3>
                    <p class="text-muted text-center">
                        <span class="badge badge-{{ $employee->au_status ? 'success' : 'danger' }}">
                            {{ $employee->au_status ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="badge 
                            @if($employee->au_user_type == 4) badge-danger
                            @elseif($employee->au_user_type == 5) badge-warning
                            @else badge-info @endif">
                            @if($employee->au_user_type == 4) Admin
                            @elseif($employee->au_user_type == 5) Team Leader
                            @else User
                            @endif
                        </span>
                    </p>

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Employee ID</b>
                            <span class="pull-right">{{ $employee->au_user_id }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Email</b>
                            <span class="pull-right">{{ $employee->au_email }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Mobile</b>
                            <span class="pull-right">{{ $employee->au_mobile }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Branch</b>
                            <span class="pull-right">{{ $employee->branch->branch_name ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Department</b>
                            <span class="pull-right">{{ $employee->department->name ?? 'N/A' }}</span>
                        </li>
                        @if($employee->team_info)
                        <li class="list-group-item">
                            <b>Team</b>
                            <span class="pull-right">{{ $employee->team_info->tl_name ?? 'N/A' }}</span>
                        </li>
                        @endif
                    </ul>

                    <div class="mt-3 text-center">
                        <a href="{{ route('company.hrm.employees.edit', $employee->au_id) }}" 
                           class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit Profile
                        </a>
                        <a href="{{ route('company.hrm.employees.download-profile', $employee->au_id) }}" 
                           class="btn btn-success">
                            <i class="fa fa-download"></i> Download Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Shift Information -->
            @if($shift)
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Shift Information</h3>
                </div>
                <div class="box-body">
                    <p><strong>Shift Name:</strong> {{ $shift->name }}</p>
                    <p><strong>Start Time:</strong> {{ $shift->start_time }}</p>
                    <p><strong>End Time:</strong> {{ $shift->end_time }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Attendance & Additional Information -->
        <div class="col-md-8">
            <!-- Current Month Attendance Summary -->
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Present Days</span>
                            <span class="info-box-number">{{ $attendanceSummary->total_days ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                {{ DateTime::createFromFormat('!m', $currentMonth)->format('F') }} {{ $currentYear }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Late Days</span>
                            <span class="info-box-number">{{ $attendanceSummary->late_days ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                This Month
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box bg-blue">
                        <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Overtime</span>
                            <span class="info-box-number">{{ $attendanceSummary->overtime_days ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                This Month
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box bg-red">
                        <span class="info-box-icon"><i class="fa fa-times"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Incomplete</span>
                            <span class="info-box-number">{{ $attendanceSummary->incomplete_days ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                This Month
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Additional Information</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><span class="detail-label">Blood Group:</span> 
                                <span class="detail-value">{{ $employee->au_blood_group ?? 'Not Set' }}</span>
                            </p>
                            <p><span class="detail-label">Salary:</span> 
                                <span class="detail-value">{{ $employee->au_user_salary ? 'TK ' . number_format($employee->au_user_salary, 2) : 'Not Set' }}</span>
                            </p>
                            <p><span class="detail-label">Facilities:</span> 
                                <span class="detail-value">{{ $employee->au_user_facility ?? 'Not Set' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><span class="detail-label">Address:</span> 
                                <span class="detail-value">{{ $employee->au_address ?? 'Not Provided' }}</span>
                            </p>
                            <p><span class="detail-label">Working Hours:</span> 
                                <span class="detail-value">{{ $employee->au_working_hour ?? 'Not Set' }}</span>
                            </p>
                            <p><span class="detail-label">IP Address:</span> 
                                <span class="detail-value">{{ $employee->au_ip_address ?? 'Not Set' }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Recent Attendance (Last 5 Records)</h3>
                   
                </div>
                <div class="box-body table-responsive">
                    @if($recentAttendance->count() > 0)
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th>Working Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttendance as $record)
                                    @php
                                        $checkIn = Carbon::parse($record->check_in);
                                        $checkOut = $record->check_out ? Carbon::parse($record->check_out) : null;
                                        $workingHours = $checkOut ? $checkIn->diff($checkOut)->format('%H:%I') : 'N/A';
                                        
                                        $statusBadge = match($record->status) {
                                            1 => '<span class="badge badge-warning">Late</span>',
                                            5 => '<span class="badge badge-info">Overtime</span>',
                                            4 => '<span class="badge badge-danger">Incomplete</span>',
                                            default => '<span class="badge badge-success">Present</span>'
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $checkIn->format('Y-m-d') }}</td>
                                        <td>{{ $checkIn->format('h:i A') }}</td>
                                        <td>{{ $checkOut ? $checkOut->format('h:i A') : 'N/A' }}</td>
                                        <td>{!! $statusBadge !!}</td>
                                        <td>{{ $workingHours }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center">No attendance records found for this month.</p>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="box box-default">
                <div class="box-body text-center">
                    <div class="btn-group">
                        <form action="{{ route('company.hrm.employees.toggle-status', $employee->au_id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-{{ $employee->au_status ? 'warning' : 'success' }}"
                                    onclick="return confirm('Are you sure you want to {{ $employee->au_status ? 'deactivate' : 'activate' }} this employee?')">
                                <i class="fa fa-{{ $employee->au_status ? 'times' : 'check' }}"></i> 
                                {{ $employee->au_status ? 'Deactivate Employee' : 'Activate Employee' }}
                            </button>
                        </form>
                        <a href="{{ route('company.hrm.employees.list') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts
    $(".alert").delay(5000).slideUp(200, function() {
        $(this).alert('close');
    });
});
</script>
@endsection
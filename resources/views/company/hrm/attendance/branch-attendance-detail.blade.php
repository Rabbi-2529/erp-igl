@php use Carbon\Carbon; @endphp
@extends('layouts.master')

@section('title', 'Branch Attendance Detail')
@section('company_class', 'active')
@section('hrm_class', 'active')
@section('branches_attendance_class', 'active')

@section('content')
<style>
.employee-card {
    border: 1px solid #e3e3e3;
    border-radius: 5px;
    margin-bottom: 20px;
    background: #fff;
}
.employee-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.attendance-badge {
    font-size: 0.8rem;
    padding: 5px 10px;
}
.calendar-day {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin: 2px;
    font-size: 0.7rem;
    font-weight: bold;
    cursor: pointer;
}
.day-present {
    background-color: #28a745;
    color: white;
}
.day-absent {
    background-color: #dc3545;
    color: white;
}
.day-holiday {
    background-color: #6c757d;
    color: white;
}
.day-weekend {
    background-color: #ffc107;
    color: #212529;
}
.day-future {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}
</style>

<section class="content">
    <section class="content-header">
        <div class="row">
            <div class="col-md-8">
                <h1 style="margin: 0;">
                    <i class="fa fa-building"></i> {{ $branch->branch_name }} - Attendance Details
                </h1>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('company.hrm.attendance.branches') }}?month={{ $request_month }}&year={{ $request_year }}" 
                   class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back to All Branches
                </a>
                <a href="{{ route('company.hrm.attendance.report.branch', $branch->branch_id) }}?month={{ $request_month }}&year={{ $request_year }}" 
                   class="btn btn-success">
                    <i class="fa fa-download"></i> Download Report
                </a>
            </div>
        </div>
    </section>

    <div class="box box-primary">
        <div class="box-header with-border">
            <!-- Branch Header and Filters -->
            <div class="row">
                <div class="col-md-8">
                    <div class="alert bg-primary" style="color: white;">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 style="margin: 0 0 5px 0;">{{ $branch->branch_name }}</h4>
                                <p style="margin: 0; opacity: 0.9;">Branch ID: {{ $branch->branch_id }}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <h2 style="margin: 0;">{{ $employees->count() }}</h2>
                                <p style="margin: 0; opacity: 0.9;">Total Employees</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box">
                        <div class="box-body">
                            <form action="{{ route('company.hrm.attendance.branch.detail', $branch->branch_id) }}" method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <select name="month" class="form-control form-control-sm">
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                                {{ $request_month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                                {{ DateTime::createFromFormat('!m', $i)->format('M') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group mr-2">
                                    <select name="year" class="form-control form-control-sm">
                                        @for($year = date('Y'); $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ $request_year == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box-body">
            <!-- Employees Attendance List -->
            <div class="row">
                @foreach($employees as $employee)
                    @php
                        $data = $attendanceData[$employee->au_id] ?? null;
                        if (!$data) continue;
                        
                        $presentDays = $data['present_days'];
                        $attendanceRate = $days_in_month > 0 ? round(($presentDays / $days_in_month) * 100, 2) : 0;
                        
                        if ($attendanceRate >= 90) {
                            $statusClass = 'success';
                        } elseif ($attendanceRate >= 70) {
                            $statusClass = 'warning';
                        } else {
                            $statusClass = 'danger';
                        }
                    @endphp
                    
                    <div class="col-lg-6">
                        <div class="employee-card">
                            <div class="box-header with-border">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4 class="box-title" style="margin: 0;">
                                            <i class="fa fa-user"></i> {{ $employee->au_name }}
                                        </h4>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <span class="badge bg-{{ $statusClass }}">{{ $attendanceRate }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row text-center" style="margin-bottom: 15px;">
                                    <div class="col-3">
                                        <div class="text-success">
                                            <div style="font-size: 1.2rem; font-weight: bold;">{{ $presentDays }}</div>
                                            <small>Present</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-danger">
                                            <div style="font-size: 1.2rem; font-weight: bold;">{{ $days_in_month - $presentDays }}</div>
                                            <small>Absent</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-warning">
                                            <div style="font-size: 1.2rem; font-weight: bold;">{{ $data['late_days'] }}</div>
                                            <small>Late</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-info">
                                            <div style="font-size: 1.2rem; font-weight: bold;">{{ $data['overtime_days'] }}</div>
                                            <small>Overtime</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Monthly Calendar View -->
                                <div class="month-calendar" style="margin-bottom: 15px;">
                                    <h6 class="text-center" style="margin-bottom: 10px;">
                                        {{ DateTime::createFromFormat('!m', $request_month)->format('F Y') }}
                                    </h6>
                                    <div class="d-flex flex-wrap justify-content-center">
                                        @for($day = 1; $day <= $days_in_month; $day++)
                                            @php
                                                $currentDate = Carbon::create($request_year, $request_month, $day);
                                                $isWeekend = $currentDate->isWeekend();
                                                $isHoliday = in_array($day, $holidays);
                                                $isPresent = $data['records']->contains(function($record) use ($currentDate) {
                                                    return Carbon::parse($record->created_at)->isSameDay($currentDate);
                                                });
                                                
                                                if ($isHoliday) {
                                                    $dayClass = 'day-holiday';
                                                    $tooltip = 'Holiday';
                                                } elseif ($isWeekend) {
                                                    $dayClass = 'day-weekend';
                                                    $tooltip = 'Weekend';
                                                } elseif ($isPresent) {
                                                    $dayClass = 'day-present';
                                                    $tooltip = 'Present';
                                                } elseif ($currentDate->isFuture()) {
                                                    $dayClass = 'day-future';
                                                    $tooltip = 'Future Date';
                                                } else {
                                                    $dayClass = 'day-absent';
                                                    $tooltip = 'Absent';
                                                }
                                            @endphp
                                            <div class="calendar-day {{ $dayClass }}" 
                                                 title="{{ $tooltip }} - {{ $currentDate->format('M d, Y') }}"
                                                 data-toggle="tooltip">
                                                {{ $day }}
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="progress" style="height: 6px; margin-bottom: 10px;">
                                    <div class="progress-bar bg-{{ $statusClass }}" 
                                         style="width: {{ $attendanceRate }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <small class="text-muted">
                                    <i class="fa fa-id-card"></i> {{ $employee->au_user_id }} | 
                                    <i class="fa fa-sitemap"></i> {{ $employee->au_department ?? 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($employees->isEmpty())
                <div class="alert alert-warning text-center">
                    <h4><i class="fa fa-warning"></i> No Employees Found</h4>
                    <p>No employees found in this branch.</p>
                </div>
            @endif
        </div>

        <div class="box-footer">
            <!-- Legend -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-default">
                        <h6 style="margin-top: 0; margin-bottom: 10px;">Calendar Legend:</h6>
                        <div class="d-flex flex-wrap">
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day day-present mr-2"></div>
                                <small>Present</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day day-absent mr-2"></div>
                                <small>Absent</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day day-holiday mr-2"></div>
                                <small>Holiday</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day day-weekend mr-2"></div>
                                <small>Weekend</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day day-future mr-2"></div>
                                <small>Future</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Auto refresh every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    });
</script>
@endsection
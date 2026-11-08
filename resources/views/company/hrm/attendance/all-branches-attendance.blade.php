@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('human_resource_class', 'active')
@section('attendance_class', 'active')
@section('branches_attendance_class', 'active')
@section('title', 'All Branches Attendance')

@section('content')
<section class="content-header">
    <h1>
        All Branches Attendance
        <small>Overview of all company branches</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">All Branches Attendance</li>
    </ol>
</section>

<section class="content">

    {{-- Success Message --}}
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Filter Section --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter by Month & Year</h3>
        </div>
        <div class="box-body">
            <form method="GET" action="{{ route('company.hrm.attendance.branches') }}" class="form-inline">
                <div class="form-group">
                    <label for="month">Month:</label>
                    <select name="month" id="month" class="form-control" required>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" 
                                {{ $request_month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group ml-2">
                    <label for="year">Year:</label>
                    <select name="year" id="year" class="form-control" required>
                        @foreach(range(date('Y') - 5, date('Y')) as $y)
                            <option value="{{ $y }}" {{ $request_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary ml-2"><i class="fa fa-search"></i> Filter</button>
                <a href="{{ route('company.hrm.attendance.branches') }} " class="btn btn-default ml-2"><i class="fa fa-refresh"></i> Reset</a>
            </form>
        </div>
    </div>

    {{-- Total Overview --}}
    <div class="row">
        <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green whitecolor">
        <div class="inner">
                    <h3>{{ $totalStats['total_employees'] }}</h3>
                    <p>Total Employees</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $totalStats['total_present'] }}</h3>
                    <p>Total Present</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $totalStats['total_absent'] }}</h3>
                    <p>Total Absent</p>
                </div>
                <div class="icon">
                    <i class="fa fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $totalStats['total_late'] }}</h3>
                    <p>Total Late</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clock-o"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Overall Attendance Rate --}}
    <div class="row">
        <div class="col-md-12">
            <div class="info-box bg-light-blue">
                <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Overall Attendance Rate</span>
                    <span class="info-box-number">{{ $totalStats['total_attendance_rate'] }}%</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $totalStats['total_attendance_rate'] }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $totalStats['total_present'] }} out of {{ $totalStats['total_employees'] }} employees
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Branch-wise Attendance Summary --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-building"></i> Branch-wise Attendance Summary</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
        </div>
        <div class="box-body">
            @forelse($attendanceSummary as $summary)
                @php($branch = $summary['branch'])
                <div class="branch-attendance-card">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="branch-name">
                                <i class="fa fa-building-o"></i> {{ $branch->branch_name }}
                                <small class="text-muted">({{ $summary['employees_count'] }} employees)</small>
                            </h4>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="attendance-rate-label 
                                @if($summary['attendance_rate'] >= 90) text-success 
                                @elseif($summary['attendance_rate'] >= 70) text-warning 
                                @else text-danger @endif">
                                {{ $summary['attendance_rate'] }}% Attendance
                            </span>
                        </div>
                    </div>
                    
                    <div class="progress" style="height: 20px; margin: 10px 0;">
                        <div class="progress-bar progress-bar-success" 
                             style="width: {{ $summary['attendance_rate'] }}%">
                            <span style="font-weight: bold;">{{ $summary['attendance_rate'] }}%</span>
                        </div>
                    </div>

                    <div class="row stats-row">
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="stat-box present">
                                <i class="fa fa-check-circle"></i>
                                <div class="stat-content">
                                    <span class="stat-number">{{ $summary['present_count'] }}</span>
                                    <span class="stat-label">Present</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="stat-box absent">
                                <i class="fa fa-times-circle"></i>
                                <div class="stat-content">
                                    <span class="stat-number">{{ $summary['absent_count'] }}</span>
                                    <span class="stat-label">Absent</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="stat-box late">
                                <i class="fa fa-clock-o"></i>
                                <div class="stat-content">
                                    <span class="stat-number">{{ $summary['late_count'] }}</span>
                                    <span class="stat-label">Late</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="stat-box info">
                                <i class="fa fa-percent"></i>
                                <div class="stat-content">
                                    <span class="stat-number">{{ $summary['attendance_rate'] }}%</span>
                                    <span class="stat-label">Rate</span>
                                </div>
                                  <div class="text-center">
                            <a href="{{ route('company.hrm.attendance.branch.detail', $branch->branch_id) }}?month={{ $request_month }}&year={{ $request_year }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-eye"></i> View Details
                            </a>
                            <a href="{{ route('company.hrm.attendance.report.branch', $branch->branch_id) }}?month={{ $request_month }}&year={{ $request_year }}" class="btn btn-success btn-sm">
                                <i class="fa fa-download"></i> Download
                            </a>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if(!$loop->last)
                    <hr class="branch-divider">
                @endif
            @empty
                <div class="text-center">
                    <i class="fa fa-building fa-3x text-muted"></i>
                    <h4 class="text-muted">No branches found</h4>
                    <p class="text-muted">There are no branches available for attendance tracking.</p>
                </div>
            @endforelse
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

        // Disable submit button on form submit
        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled', true);
        });

        // Add loading state to filter button
        $('#month, #year').change(function() {
            $('form').find("button[type='submit']").prop('disabled', false);
        });
    });
</script>
@endsection

@section('styles')
<style>
.branch-attendance-card {
    background: #f9f9f9;
    border: 1px solid #e3e3e3;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.branch-attendance-card:hover {
    background: #f0f0f0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.branch-name {
    margin-top: 0;
    color: #333;
    font-weight: 600;
}

.attendance-rate-label {
    font-weight: 600;
    font-size: 14px;
}

.stats-row {
    margin-top: 15px;
}

.stat-box {
    display: flex;
    align-items: center;
    padding: 10px;
    border-radius: 6px;
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-box.present {
    border-left: 4px solid #00a65a;
}

.stat-box.absent {
    border-left: 4px solid #dd4b39;
}

.stat-box.late {
    border-left: 4px solid #f39c12;
}

.stat-box.info {
    border-left: 4px solid #00c0ef;
}

.stat-box i {
    font-size: 24px;
    margin-right: 10px;
}

.stat-box.present i { color: #00a65a; }
.stat-box.absent i { color: #dd4b39; }
.stat-box.late i { color: #f39c12; }
.stat-box.info i { color: #00c0ef; }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 18px;
    font-weight: bold;
    line-height: 1;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.branch-divider {
    margin: 20px 0;
    border-color: #e3e3e3;
}

.progress {
    background-color: #f5f5f5;
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.6s ease;
}

@media (max-width: 768px) {
    .stat-box {
        margin-bottom: 10px;
    }
    
    .branch-name {
        font-size: 16px;
    }
    
    .attendance-rate-label {
        font-size: 12px;
    }
}
</style>
@endsection
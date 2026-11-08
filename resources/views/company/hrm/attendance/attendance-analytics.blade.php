@php use Carbon\Carbon; @endphp
@extends('layouts.master')

@section('title', 'Attendance Analytics')
@section('human_resource_class', 'active')
@section('attendance_class', 'active')
@section('attendance_analytics_class', 'active')

@section('content')
<style>
.analytics-card {
    border-radius: 8px;
    margin-bottom: 20px;
    background: #fff;
    border: 1px solid #e3e3e3;
    transition: all 0.3s ease;
}
.analytics-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.stat-number {
    font-size: 2rem;
    font-weight: bold;
    line-height: 1;
}
.stat-label {
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
}
.trend-up {
    color: #28a745;
}
.trend-down {
    color: #dc3545;
}
.chart-container {
    position: relative;
    height: 300px;
    margin: 20px 0;
}
.performance-badge {
    font-size: 0.8rem;
    padding: 4px 8px;
    border-radius: 12px;
}
.late-comer-rank {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    margin-right: 10px;
}
.rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); }
.rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); }
.rank-3 { background: linear-gradient(135deg, #CD7F32, #8B4513); }
.rank-4-10 { background: #6c757d; }
.progress {
    height: 8px;
    margin-top: 5px;
}
.best-sale-title {
    display: inline-block;
    margin: 0;
}
.best-sale-seeall {
    float: right;
    margin-top: -5px;
}
.panel-heading {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e3e3e3;
}
.whitecolor {
    color: white !important;
}
</style>

<section class="content">
    <section class="content-header">
        <div class="row">
            <div class="col-md-8">
                <h1 style="margin: 0;">
                    <i class="fa fa-line-chart"></i> Attendance Analytics
                </h1>
                <small class="text-muted">Comprehensive attendance insights and trends</small>
            </div>
            <div class="col-md-4 text-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-download"></i> Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#"><i class="fa fa-file-pdf-o"></i> PDF Report</a>
                        <a class="dropdown-item" href="#"><i class="fa fa-file-excel-o"></i> Excel Report</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter Analytics</h3>
        </div>
        <div class="box-body">
            <form method="GET" action="{{ route('company.hrm.attendance.analytics') }}" class="form-inline">
                <div class="form-group mr-3">
                    <label for="month" class="mr-2">Month:</label>
                    <select name="month" id="month" class="form-control" required>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" 
                                {{ $request_month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="year" class="mr-2">Year:</label>
                    <select name="year" id="year" class="form-control" required>
                        @foreach(range(date('Y') - 2, date('Y')) as $y)
                            <option value="{{ $y }}" {{ $request_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-refresh"></i> Update Analytics
                </button>
            </form>
        </div>
    </div>

    <!-- Key Metrics - Similar to Dashboard -->
    <div class="row">
        @php
            $totalEmployees = $branchPerformance->sum('total_employees');
            $totalPresent = $branchPerformance->sum('present_employees');
            $overallAttendanceRate = $totalEmployees > 0 ? round(($totalPresent / $totalEmployees) * 100, 2) : 0;
            $avgBranchRate = $branchPerformance->avg('attendance_rate') ?? 0;
            $totalLateCount = $lateComers->sum('late_count');
        @endphp
        
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
            <div class="small-box bg-green whitecolor">
                <div class="inner">
                    <div class="stat-number">{{ $totalEmployees }}</div>
                    <p>Total Employees</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
                <a href="#" class="small-box-footer">More Info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
            <div class="small-box bg-primary whitecolor">
                <div class="inner">
                    <div class="stat-number">{{ $totalPresent }}</div>
                    <p>Present This Month</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
                <a href="#" class="small-box-footer">More Info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
            <div class="small-box bg-primary whitecolor ">
                <div class="inner">
                    <div class="stat-number">{{ $overallAttendanceRate }}%</div>
                    <p>Overall Rate</p>
                </div>
                <div class="icon">
                    <i class="fa fa-percent"></i>
                </div>
                <a href="#" class="small-box-footer">More Info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
            <div class="small-box bg-yellow whitecolor">
                <div class="inner">
                    <div class="stat-number">{{ $totalLateCount }}</div>
                    <p>Total Late Marks</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clock-o"></i>
                </div>
                <a href="#" class="small-box-footer">More Info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Charts and Detailed Analytics -->
    <div class="row">
        <!-- Monthly Trend Chart -->
        <div class="col-md-8 d-flex">
            <div class="panel panel-bd flex-fill w-100 analytics-card">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="best-sale-title">
                            <i class="fa fa-bar-chart"></i> Monthly Attendance Trend - {{ $request_year }}
                        </h4>
                    </div>
                </div>
                <div class="panel-body">
                    @if($monthlyTrend->count() > 0)
                        <div class="chart-container">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-bar-chart fa-3x mb-3"></i>
                            <p>No attendance data available for the selected period</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Branch Performance -->
        <div class="col-md-4 d-flex">
            <div class="panel panel-bd flex-fill w-100 analytics-card">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="best-sale-title">
                            <i class="fa fa-building"></i> Branch Performance
                        </h4>
                    </div>
                </div>
                <div class="panel-body">
                    @if($branchPerformance->count() > 0)
                        @foreach($branchPerformance as $branch)
                            @php
                                if ($branch->attendance_rate >= 90) {
                                    $progressClass = 'progress-bar-success';
                                    $badgeClass = 'badge-success';
                                } elseif ($branch->attendance_rate >= 70) {
                                    $progressClass = 'progress-bar-warning';
                                    $badgeClass = 'badge-warning';
                                } else {
                                    $progressClass = 'progress-bar-danger';
                                    $badgeClass = 'badge-danger';
                                }
                            @endphp
                            
                            <div class="branch-performance-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $branch->branch_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $branch->present_employees }}/{{ $branch->total_employees }} employees
                                        </small>
                                    </div>
                                    <span class="badge {{ $badgeClass }} performance-badge">
                                        {{ $branch->attendance_rate }}%
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar {{ $progressClass }}" 
                                         style="width: {{ $branch->attendance_rate }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fa fa-building fa-2x mb-2"></i>
                            <p>No branch data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Late Comers and Distribution -->
    <div class="row">
        <!-- Top Late Comers -->
        <div class="col-sm-12 col-md-6 d-flex">
            <div class="panel panel-bd flex-fill w-100 analytics-card">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="best-sale-title">
                            <i class="fa fa-exclamation-triangle"></i> Top Late Comers
                        </h4>
                        <a href="#" class="btn btn-success text-white best-sale-seeall">See All</a>
                    </div>
                </div>
                <div class="panel-body">
                    @if($lateComers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Employee</th>
                                        <th>Branch</th>
                                        <th class="text-center">Late Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lateComers as $index => $lateComer)
                                        <tr>
                                            <td>
                                                <div class="late-comer-rank 
                                                    @if($index == 0) rank-1
                                                    @elseif($index == 1) rank-2
                                                    @elseif($index == 2) rank-3
                                                    @else rank-4-10 @endif">
                                                    {{ $index + 1 }}
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $lateComer->au_name }}</strong>
                                            </td>
                                            <td>{{ $lateComer->branch_name }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-danger">{{ $lateComer->late_count }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No Late Comers</h5>
                            <p>Great! No employees were late this month.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Attendance Distribution -->
        <div class="col-sm-12 col-md-6 d-flex">
            <div class="panel panel-bd flex-fill w-100 analytics-card">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="best-sale-title">
                            <i class="fa fa-pie-chart"></i> Attendance Distribution
                        </h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="chart-container">
                        <canvas id="attendanceDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-sm-12 col-md-12 d-flex">
            <div class="panel panel-bd flex-fill w-100 analytics-card">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="best-sale-title">
                            <i class="fa fa-info-circle"></i> Summary Statistics
                        </h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-light-blue">
                                <span class="info-box-icon"><i class="fa fa-trophy"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Best Branch</span>
                                    <span class="info-box-number">
                                        @if($branchPerformance->count() > 0)
                                            {{ $branchPerformance->sortByDesc('attendance_rate')->first()->branch_name }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        @if($branchPerformance->count() > 0)
                                            {{ $branchPerformance->sortByDesc('attendance_rate')->first()->attendance_rate }}% Attendance
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-red">
                                <span class="info-box-icon"><i class="fa fa-exclamation-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Needs Improvement</span>
                                    <span class="info-box-number">
                                        @if($branchPerformance->count() > 0)
                                            {{ $branchPerformance->sortBy('attendance_rate')->first()->branch_name }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        @if($branchPerformance->count() > 0)
                                            {{ $branchPerformance->sortBy('attendance_rate')->first()->attendance_rate }}% Attendance
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg Branch Rate</span>
                                    <span class="info-box-number">{{ round($avgBranchRate, 2) }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $avgBranchRate }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Average across all branches
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-purple">
                                <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Analysis Period</span>
                                    <span class="info-box-number">
                                        {{ DateTime::createFromFormat('!m', $request_month)->format('M') }} {{ $request_year }}
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ Carbon::create($request_year, $request_month, 1)->daysInMonth }} days
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Reports -->
    <div class="row">
        <!-- Monthly Comparison -->
        <div class="col-sm-12 col-md-6 d-flex">
            <div class="panel panel-bd flex-fill w-100 analytics-card">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="best-sale-title">
                            <i class="fa fa-calendar"></i> Monthly Comparison
                        </h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Employees</th>
                                    <th>Attendance Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyTrend as $trend)
                                <tr>
                                    <td>{{ DateTime::createFromFormat('!m', $trend->month)->format('F') }}</td>
                                    <td>{{ $trend->unique_attendance }}</td>
                                    <td>
                                        @php
                                            $monthRate = $totalEmployees > 0 ? round(($trend->unique_attendance / $totalEmployees) * 100, 2) : 0;
                                        @endphp
                                        <span class="badge badge-{{ $monthRate >= 80 ? 'success' : ($monthRate >= 60 ? 'warning' : 'danger') }}">
                                            {{ $monthRate }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-sm-12 col-md-6 d-flex">
            <div class="panel panel-bd flex-fill w-100 analytics-card">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="best-sale-title">
                            <i class="fa fa-dashboard"></i> Quick Stats
                        </h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                            </tr>
                            <tr>
                                <th>Total Branches</th>
                                <td>{{ $branchPerformance->count() }}</td>
                            </tr>
                            <tr>
                                <th>Average Late per Employee</th>
                                <td>{{ $totalEmployees > 0 ? round($totalLateCount / $totalEmployees, 2) : 0 }}</td>
                            </tr>
                            <tr>
                                <th>Best Performance Rate</th>
                                <td>
                                    @if($branchPerformance->count() > 0)
                                        {{ $branchPerformance->max('attendance_rate') }}%
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Worst Performance Rate</th>
                                <td>
                                    @if($branchPerformance->count() > 0)
                                        {{ $branchPerformance->min('attendance_rate') }}%
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Monthly Trend Chart
        @if($monthlyTrend->count() > 0)
        var monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        var monthlyTrendChart = new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($monthlyTrend as $trend)
                        '{{ DateTime::createFromFormat('!m', $trend->month)->format('M') }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Unique Attendance',
                    data: [
                        @foreach($monthlyTrend as $trend)
                            {{ $trend->unique_attendance }},
                        @endforeach
                    ],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Attendance Trend {{ $request_year }}'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Employees'
                        }
                    }
                }
            }
        });
        @endif

        // Attendance Distribution Chart
        var distributionCtx = document.getElementById('attendanceDistributionChart').getContext('2d');
        var distributionChart = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [
                        {{ $totalPresent }},
                        {{ $totalEmployees - $totalPresent }},
                        {{ $totalLateCount }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#dc3545',
                        '#ffc107'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Attendance Distribution'
                    }
                }
            }
        });

        // Auto refresh every 10 minutes
        setInterval(function() {
            location.reload();
        }, 600000);
    });

    // Alert auto close
    $(".alert").delay(5000).slideUp(200, function() {
        $(this).alert('close');
    });

    // Disable submit button on form submit
    $('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
    });
</script>
@endsection
@php use Carbon\Carbon; @endphp

<div class="employee-attendance-details">
    <!-- Employee Header -->
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-md-6">
                        <h4 style="margin: 0;">{{ $user->au_name }}</h4>
                        <p style="margin: 0; font-size: 14px;">
                            <strong>ID:</strong> {{ $user->au_user_id }} | 
                            <strong>Department:</strong> {{ $user->au_department ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="col-md-6 text-right">
                        <h4 style="margin: 0;">
                            {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
                        </h4>
                        <p style="margin: 0; font-size: 14px;">
                            <strong>Branch:</strong> {{ $user->branch->branch_name ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    @php
        $totalPresent = $attendanceRecords->count();
        $totalLate = $attendanceRecords->where('status', 1)->count();
        $totalOvertime = $attendanceRecords->where('status', 5)->count();
        $totalIncomplete = $attendanceRecords->where('status', 4)->count();
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $attendanceRate = $daysInMonth > 0 ? round(($totalPresent / $daysInMonth) * 100, 2) : 0;
    @endphp

    <div class="row text-center mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Present Days</span>
                    <span class="info-box-number">{{ $totalPresent }}/{{ $daysInMonth }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ ($totalPresent/$daysInMonth)*100 }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ round(($totalPresent/$daysInMonth)*100, 2) }}% of month
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Late Days</span>
                    <span class="info-box-number">{{ $totalLate }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ ($totalLate/$daysInMonth)*100 }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ round(($totalLate/$daysInMonth)*100, 2) }}% of month
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-blue">
                <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Overtime</span>
                    <span class="info-box-number">{{ $totalOvertime }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ ($totalOvertime/$daysInMonth)*100 }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ round(($totalOvertime/$daysInMonth)*100, 2) }}% of month
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Incomplete</span>
                    <span class="info-box-number">{{ $totalIncomplete }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ ($totalIncomplete/$daysInMonth)*100 }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ round(($totalIncomplete/$daysInMonth)*100, 2) }}% of month
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Calendar View -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Monthly Calendar View</h3>
                </div>
                <div class="box-body">
                    <div class="calendar-legend mb-3">
                        <div class="d-flex flex-wrap">
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day-small day-present mr-2"></div>
                                <small>Present</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day-small day-late mr-2"></div>
                                <small>Late</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day-small day-overtime mr-2"></div>
                                <small>Overtime</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day-small day-incomplete mr-2"></div>
                                <small>Incomplete</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day-small day-absent mr-2"></div>
                                <small>Absent</small>
                            </div>
                            <div class="mr-3 mb-1 d-flex align-items-center">
                                <div class="calendar-day-small day-weekend mr-2"></div>
                                <small>Weekend</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="calendar-grid">
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $currentDate = Carbon::create($year, $month, $day);
                                $isWeekend = $currentDate->isWeekend();
                                $isFuture = $currentDate->isFuture();
                                
                                $dayAttendance = $attendanceRecords->first(function($record) use ($currentDate) {
                                    return Carbon::parse($record->created_at)->isSameDay($currentDate);
                                });
                                
                                if ($isFuture) {
                                    $dayClass = 'day-future';
                                    $tooltip = 'Future Date';
                                } elseif ($isWeekend) {
                                    $dayClass = 'day-weekend';
                                    $tooltip = 'Weekend';
                                } elseif ($dayAttendance) {
                                    switch($dayAttendance->status) {
                                        case 1: // Late
                                            $dayClass = 'day-late';
                                            $tooltip = 'Late - ' . Carbon::parse($dayAttendance->check_in)->format('h:i A');
                                            break;
                                        case 5: // Overtime
                                            $dayClass = 'day-overtime';
                                            $tooltip = 'Overtime - ' . Carbon::parse($dayAttendance->check_in)->format('h:i A');
                                            break;
                                        case 4: // Incomplete
                                            $dayClass = 'day-incomplete';
                                            $tooltip = 'Incomplete - No Checkout';
                                            break;
                                        default: // Present
                                            $dayClass = 'day-present';
                                            $tooltip = 'Present - ' . Carbon::parse($dayAttendance->check_in)->format('h:i A');
                                    }
                                } else {
                                    $dayClass = 'day-absent';
                                    $tooltip = 'Absent';
                                }
                            @endphp
                            <div class="calendar-day-small {{ $dayClass }}" 
                                 title="{{ $currentDate->format('M d, Y') }} - {{ $tooltip }}"
                                 data-toggle="tooltip">
                                {{ $day }}
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Attendance Records -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Daily Attendance Records</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead style="background: #f5f5f5;">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Working Hours</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendanceRecords as $record)
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
                                    <td>{{ $checkIn->format('l') }}</td>
                                    <td>{{ $checkIn->format('h:i A') }}</td>
                                    <td>{{ $checkOut ? $checkOut->format('h:i A') : 'N/A' }}</td>
                                    <td>{!! $statusBadge !!}</td>
                                    <td>{{ $workingHours }}</td>
                                    <td>{{ $record->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No attendance records found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-12 text-center">
            <a href="{{ route('company.hrm.attendance.employee-report', $user->au_id) }}?month={{ $month }}&year={{ $year }}" 
               class="btn btn-success" target="_blank">
                <i class="fa fa-download"></i> Download Report
            </a>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>

<style>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
    margin-top: 10px;
}

.calendar-day-small {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    cursor: pointer;
}

.day-present {
    background-color: #28a745;
    color: white;
}

.day-late {
    background-color: #ffc107;
    color: #212529;
}

.day-overtime {
    background-color: #007bff;
    color: white;
}

.day-incomplete {
    background-color: #6c757d;
    color: white;
}

.day-absent {
    background-color: #dc3545;
    color: white;
}

.day-weekend {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.day-future {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.info-box {
    margin-bottom: 0;
}

.info-box .progress {
    height: 5px;
    margin: 5px 0;
}
</style>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover'
    });
});
</script>
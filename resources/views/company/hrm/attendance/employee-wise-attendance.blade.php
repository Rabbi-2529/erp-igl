@extends('layouts.master')
@section('title', 'Employee Wise Attendance Report')

@section('human_resource_class','active')
@section('attendance_class','active')
@section('employee_attendance_class','active')

@section('content')

<style>
    .action_btn{
        border: 1px solid;
        padding: 5px;
    }
    .btn_show{
        background-color: green;
        color: white;
    }
    .btn_delete{
        background-color: red;
        color: white;
    }
    .btn_edit{
        background-color: cornflowerblue;
        color: white;
    }
    @media print {
        .print-hidden {
            display: none !important;
        }
        .table th, .table td {
            padding: 5px !important;
        }
    }
    .progress-bar {
        background-color: #28a745;
    }
    .badge-success {
        background-color: #28a745;
    }
    .badge-warning {
        background-color: #ffc107;
    }
    .badge-danger {
        background-color: #dc3545;
    }
    .badge-info {
        background-color: #17a2b8;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Employee Wise Attendance Report</h4>
                </div>
            </div>

            <div class="panel-body">

                @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session()->has('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Filter Section -->
                <div class="box print-hidden">
                    <form action="{{ route('company.hrm.attendance.employee-wise') }}" method="get">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Month & Year</label>
                                    <input type="month" name="month" class="form-control" 
                                           value="{{ $request_year }}-{{ str_pad($request_month, 2, '0', STR_PAD_LEFT) }}">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Branch</label>
                                    <select class="form-control" name="branch_id" id="branch_id">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->branch_id }}" 
                                                {{ $branch_id == $branch->branch_id ? 'selected' : '' }}>
                                                {{ $branch->branch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Employee</label>
                                    <select class="form-control select2" name="employee_id" id="employee_id">
                                        <option value="">All Employees</option>
                                        @foreach($userComId as $employee)
                                            <option value="{{ $employee->au_id }}" 
                                                {{ $employee_id == $employee->au_id ? 'selected' : '' }}>
                                                {{ $employee->au_name }} 
                                                ({{ $employee->au_user_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group" style="margin-top: 25px;">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <button type="button" class="btn btn-success" onclick="printAttendanceList()">
                                        <i class="fa fa-print"></i> Print
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                @php
                    $totalEmployees = count($attendanceDetails);
                    $totalPresent = array_sum(array_column($attendanceDetails, 'total_present'));
                    $totalLate = array_sum(array_column($attendanceDetails, 'total_late'));
                    $totalOvertime = array_sum(array_column($attendanceDetails, 'total_overtime'));
                    $totalIncomplete = array_sum(array_column($attendanceDetails, 'total_incomplete'));
                    $avgAttendanceRate = $totalEmployees > 0 ? 
                        round(array_sum(array_column($attendanceDetails, 'attendance_rate')) / $totalEmployees, 2) : 0;
                @endphp

                <div class="row print-hidden">
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Employees</span>
                                <span class="info-box-number">{{ $totalEmployees }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ DateTime::createFromFormat('!m', $request_month)->format('F') }} {{ $request_year }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Present</span>
                                <span class="info-box-number">{{ $totalPresent }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $totalEmployees > 0 ? ($totalPresent/($totalEmployees*$days_in_month))*100 : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $avgAttendanceRate }}% Avg Rate
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Late</span>
                                <span class="info-box-number">{{ $totalLate }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $totalPresent > 0 ? ($totalLate/$totalPresent)*100 : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $totalEmployees > 0 ? round($totalLate/$totalEmployees, 1) : 0 }} per employee
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Incomplete</span>
                                <span class="info-box-number">{{ $totalIncomplete }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $totalPresent > 0 ? ($totalIncomplete/$totalPresent)*100 : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $totalEmployees > 0 ? round($totalIncomplete/$totalEmployees, 1) : 0 }} per employee
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title text-center">
                            Employee Attendance Report - 
                            <span id="selectedMonthYear">
                                {{ DateTime::createFromFormat('!m', $request_month)->format('F') }} {{ $request_year }}
                            </span>
                        </h3>
                    </div>

                    <div class="box-body">
                        <table id="employeeAttendanceTable" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="print-hidden">SL</th>
                                    <th>Emp ID</th>
                                    <th>Employee Name</th>
                                    <th>Branch</th>
                                    <th>Total Present</th>
                                    <th>Total Late</th>
                                    <th>Total Overtime</th>
                                    <th>Total Incomplete</th>
                                    <th>Attendance Rate</th>
                                    <th class="text-center print-hidden">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $sl = 0; @endphp
                                @foreach($attendanceDetails as $detail)
                                <tr>
                                    <!-- Serial -->
                                    <td class="print-hidden">{{ ++$sl }}</td>
                                    
                                    <!-- Employee ID -->
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $detail['employee']->au_user_id ?? 'N/A' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Employee Name -->
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div style="width: 32px; height: 32px; margin-right: 10px;">
                                                @if($detail['employee']->photo)
                                                    <img src="{{ asset($detail['employee']->photo) }}" 
                                                         class="img-circle" width="32" height="32" alt="User Image">
                                                @else
                                                    <div class="img-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                                         style="width: 32px; height: 32px; font-size: 14px;">
                                                        {{ substr($detail['employee']->au_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <strong>{{ $detail['employee']->au_name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $detail['employee']->designation ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Branch -->
                                    <td>
                                        <span class="badge badge-light">
                                            {{ $detail['employee']->branch->branch_name ?? 'No Branch' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Total Present -->
                                    <td class="text-center">
                                        <span class="badge badge-success" style="font-size: 14px;">
                                            {{ $detail['total_present'] }}
                                        </span>
                                    </td>
                                    
                                    <!-- Total Late -->
                                    <td class="text-center">
                                        @if($detail['total_late'] > 0)
                                            <span class="badge badge-warning" style="font-size: 14px;">
                                                {{ $detail['total_late'] }}
                                            </span>
                                        @else
                                            <span class="badge badge-success" style="font-size: 14px;">
                                                {{ $detail['total_late'] }}
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Total Overtime -->
                                    <td class="text-center">
                                        @if($detail['total_overtime'] > 0)
                                            <span class="badge badge-info" style="font-size: 14px;">
                                                {{ $detail['total_overtime'] }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary" style="font-size: 14px;">
                                                {{ $detail['total_overtime'] }}
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Total Incomplete -->
                                    <td class="text-center">
                                        @if($detail['total_incomplete'] > 0)
                                            <span class="badge badge-danger" style="font-size: 14px;">
                                                {{ $detail['total_incomplete'] }}
                                            </span>
                                        @else
                                            <span class="badge badge-success" style="font-size: 14px;">
                                                {{ $detail['total_incomplete'] }}
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Attendance Rate -->
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $rate = $detail['attendance_rate'];
                                                if($rate >= 90) {
                                                    $color = 'bg-success';
                                                } elseif($rate >= 70) {
                                                    $color = 'bg-warning';
                                                } else {
                                                    $color = 'bg-danger';
                                                }
                                            @endphp
                                            <div class="progress-bar {{ $color }}" 
                                                 role="progressbar" 
                                                 style="width: {{ min($rate, 100) }}%;"
                                                 aria-valuenow="{{ $rate }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $rate }}%
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Action -->
                                    <td class="text-center print-hidden">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-info" 
                                                    onclick="viewAttendanceDetails('{{ $detail['employee']->au_id }}')"
                                                    data-toggle="tooltip" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="downloadAttendanceReport('{{ $detail['employee']->au_id }}')"
                                                    data-toggle="tooltip" title="Download Report">
                                                <i class="fa fa-download"></i>
                                            </button>
                                            @if($userType == 5) <!-- Admin only -->
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="sendAttendanceAlert('{{ $detail['employee']->au_id }}')"
                                                    data-toggle="tooltip" title="Send Alert">
                                                <i class="fa fa-bell"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                
                                @if(count($attendanceDetails) == 0)
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fa fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <h4 class="text-muted">No attendance records found</h4>
                                            <p class="text-muted">No attendance data available for the selected period and filters.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                            
                            @if(count($attendanceDetails) > 0)
                            <tfoot>
                                <tr style="background-color: #f8f9fa;">
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-center">{{ $totalEmployees }}</th>
                                    <th class="text-center">{{ $totalPresent }}</th>
                                    <th class="text-center">{{ $totalLate }}</th>
                                    <th class="text-center">{{ $totalOvertime }}</th>
                                    <th class="text-center">{{ $totalIncomplete }}</th>
                                    <th class="text-center">{{ $avgAttendanceRate }}%</th>
                                    <th class="print-hidden"></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Details Modal -->
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1" role="dialog" aria-labelledby="attendanceDetailsLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="attendanceDetailsLabel">
                    <i class="fa fa-calendar-alt"></i> Employee Attendance Details
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="attendanceDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading attendance details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printModalContent()">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#employeeAttendanceTable').DataTable({
            "order": [[0, 'asc']],
            "paging": true,
            "pageLength": 25,
            "responsive": true,
            "dom": '<"top"fl<"clear">>rt<"bottom"ip<"clear">>',
            "language": {
                "lengthMenu": "Show _MENU_ entries",
                "zeroRecords": "No matching records found",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "search": "Search:",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });

        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap'
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Auto-close alerts
        $(".alert").delay(5000).slideUp(200, function() {
            $(this).alert('close');
        });
    });

    function viewAttendanceDetails(userId) {
        // Show loading state
        $('#attendanceDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading attendance details...</p>
            </div>
        `);
        
        $('#attendanceDetailsModal').modal('show');

        $.ajax({
            type: "GET",
            url: "{{ route('company.hrm.attendance.employee.details') }}",
            data: {
                userid: userId,
                year: "{{ $request_year }}",
                month: "{{ $request_month }}"
            },
            success: function(result) {
                $('#attendanceDetailsContent').html(result);
                // Update modal title with employee info
                var employeeName = $(result).filter('.employee-name').text();
                if (employeeName) {
                    $('#attendanceDetailsLabel').html(
                        '<i class="fa fa-calendar-alt"></i> ' + employeeName + ' - Attendance Details'
                    );
                }
            },
            error: function(xhr) {
                console.error(xhr);
                $('#attendanceDetailsContent').html(`
                    <div class="text-center py-5">
                        <i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Error Loading Data</h5>
                        <p class="text-muted">Failed to load attendance details. Please try again.</p>
                        <button class="btn btn-primary mt-2" onclick="viewAttendanceDetails('${userId}')">
                            <i class="fa fa-refresh"></i> Try Again
                        </button>
                    </div>
                `);
            }
        });
    }

    function downloadAttendanceReport(userId) {
        let month = "{{ str_pad($request_month, 2, '0', STR_PAD_LEFT) }}";
        let year = "{{ $request_year }}";
        let route = "" + month + "&year=" + year + "&user=" + userId;
        window.open(route, '_blank');
    }

    function sendAttendanceAlert(userId) {
        if (confirm('Send attendance alert to this employee?')) {
            $.ajax({
                type: "POST",
                url: "",
                data: {
                    _token: "{{ csrf_token() }}",
                    user_id: userId,
                    month: "{{ $request_month }}",
                    year: "{{ $request_year }}"
                },
                success: function(response) {
                    if (response.success) {
                        alert('Alert sent successfully!');
                    } else {
                        alert('Failed to send alert.');
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Error sending alert.');
                }
            });
        }
    }

    function printAttendanceList() {
        // Hide unnecessary elements for print
        $('.print-hidden').hide();
        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate').hide();
        
        // Print
        window.print();
        
        // Restore hidden elements
        $('.print-hidden').show();
        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate').show();
    }

    function printModalContent() {
        var printContent = document.getElementById('attendanceDetailsContent').innerHTML;
        var originalContent = document.body.innerHTML;
        
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        $('#attendanceDetailsModal').modal('show');
    }
</script>
@endsection
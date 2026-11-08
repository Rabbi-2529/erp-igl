@extends('layouts.master')

@section('title', 'Employee Salary Chart')
@section('human_resource_class', 'active')
@section('payroll', 'active')
@section('salary_chart', 'active')

@section('content')
<style>
    @media print {
        .btn-primary {
            display: none;
        }
        .panel-title {
            font-size: 18px;
        }
        .print {
            display: none;
        }
        .print-summary {
            display: table-row;
        }
        /* Hide DataTables elements */
        .dataTables_wrapper,
        .dataTables_paginate,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_length {
            display: none;
        }
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="panel-title">
                         <h4>Employee Salary Chart for {{ $month }}-{{ $year }} (Approved by: {{ $approvedBy }})</h4>
                    <!-- Print Button -->
                    <div class="text-right">
                        <button class="btn btn-primary" onclick="printSalaryChart()">Print</button>
                    </div>
                </div>
            </div>

            <div class="panel panel-bd lobidrag">
                <div class="panel-body">
                                <table class="datatable table table-striped table-bordered table-hover" id="salaryTable">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Employee Name</th>
                            <th>User ID</th>
                            <th>Basic Salary</th>
                            <th class="print">Total Late Cut</th>
                            <th class="print">Total Absent Cut</th>
                            <th class="print">Total Paid Leave</th>
                            <th class="print">Total Advance</th>
                            <th class="print">Total Loan</th>
                            <th>Total Cut</th>
                            <th>Final Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $sl = 1; 
                            $totalFinalSalary = 0;
                        @endphp
                        @foreach($employees as $employee)
                            @php
                                $totalCut = $employee['total_late_cut'] + $employee['total_absent_cut'] - $employee['total_accept_days'] + $employee['salary_advance'] + $employee['salary_loan'];
                                $totalFinalSalary += $employee['final_salary'];
                            @endphp
                            <tr>
                                <td>{{ $sl++ }}</td>
                                <td>{{ $employee['name'] }}</td>
                                <td>{{ $employee['user_id'] }}</td>
                                <td>{{ number_format($employee['basic_salary'], 2) }}</td>
                                <td class="print">{{ number_format($employee['total_late_cut'], 2) }}</td>
                                <td class="print">{{ number_format($employee['total_absent_cut'], 2) }}</td>
                                <td class="print">{{ number_format($employee['total_paid_leave'], 2) }}</td>
                                <td class="print">{{ number_format($employee['salary_advance'], 2) }}</td>
                                <td class="print">{{ number_format($employee['salary_loan'], 2) }}</td>
                                <td>{{ number_format($totalCut, 2) }}</td>
                                <td>{{ number_format($employee['final_salary'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="10" class="text-end"><strong>Total  Salary:</strong></td>
                            <td><strong>{{ number_format($totalFinalSalary, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>

                </div>
            </div>
        </div>
    </div>
</div>
<script>
function printSalaryChart() {
    // Temporarily destroy DataTables
    var table = $('#salaryTable').DataTable();
    table.destroy();

    // Print the page
    window.print();

    // Reinitialize DataTables after printing
    setTimeout(function() {
        $('#salaryTable').DataTable();
    }, 1000); // Delay to ensure print dialog is closed
}
</script>
@endsection

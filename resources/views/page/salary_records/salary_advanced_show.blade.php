@extends('layouts.master')
@section('title', 'View Salary Record')

@section('human_resource_class', 'active')


@section('payroll', 'active')
@section('salary_advanced', 'active')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Salary Advanced</h4>
                </div>

                <div class="card-body">
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>Employee Name</th>
                                <td>{{ $employee->au_name }}</td>
                            </tr>
                            <tr>
                                <th>Salary Month</th>
                                <td>{{ $record->salary_month }}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>৳{{ number_format($record->amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

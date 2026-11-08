@extends('layouts.master')
@section('title', 'Edit Salary Record')

@section('human_resource_class', 'active')


@section('payroll', 'active')
@section('salary_advanced', 'active')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Edit Salary Advanced</h4>
                </div>

                <div class="panel-body">
                    {{ Form::model($record, ['route' => ['salary-records.salary_advanced_update', $record->id], 'method' => 'PUT']) }}
                    <div class="form-group">
                            {{ Form::label('employee_id', 'Employee') }}
                            {{ Form::select('employee_id', 
                                $employees->mapWithKeys(function ($employee) {
                                    return [$employee->au_id => "{$employee->au_name} ({$employee->au_user_id})"];
                                }), 
                                null, 
                                ['class' => 'form-control', 'placeholder' => 'Select Employee', 'required']
                            ) }}
                        </div>
                                            <div class="form-group">
                        {{ Form::label('salary_month', 'Salary Month') }}
                        {{ Form::text('salary_month', null, ['class' => 'form-control', 'id' => 'salary_month', 'placeholder' => 'YYYY-MM-DD', 'autocomplete' => 'off', 'required']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('amount', 'Amount') }}
                        {{ Form::number('amount', null, ['class' => 'form-control', 'placeholder' => 'Enter Amount', 'required']) }}
                    </div>
                    <div class="form-group">
                        <a href="{{ route('salary-records.salary_advanced_create') }}" class="btn btn-default">Cancel</a>
                        {{ Form::submit('Update', ['class' => 'btn btn-primary']) }}
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#salary_month').datepicker({
            dateFormat: 'yy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true
        });
    });
</script>
@endsection

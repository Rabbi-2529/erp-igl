@extends('layouts.master')
@section('title', 'Salary Records')
@section('human_resource_class', 'active')


@section('payroll', 'active')
@section('salary_advanced', 'active')
@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Salary Advanced
                    </h4>
                    <button id="addRecordBtn" class="btn btn-primary">Add Salary Advanced</button>
                </div>
            </div>

            <div class="panel-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee Name</th>
                            <th>Salary Month</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>{{ $record->id }}</td>
                                <td>{{ App\Models\Admin_user::find($record->employee_id)->au_name }}</td>
                                <td>{{ $record->salary_month }}</td>
                                <td>{{ $record->amount }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-gear"></i> Action
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li>
                                                <a href="{{ route('salary-records.salary_advanced_show', $record->id) }}" data-toggle="tooltip" data-placement="left" title="Update">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i> View
                                                </a>
                                              
                                            </li>
                                            <li>
                                                <a href="{{ route('salary-records.salary_advanced_edit', $record->id) }}" data-toggle="tooltip" data-placement="left" title="Profile">
                                                    <i class="fa fa-user" aria-hidden="true"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                            <a href="#" class="delete-record" data-url="{{ route('salary-records.destroy', $record->id) }}" data-toggle="tooltip" data-placement="left" title="Delete">
                                                <i class="fa fa-trash" aria-hidden="true"></i> Delete
                                            </a>
                                        </li>
                                        </ul>
                                    </div>
                                </td>
                              
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Advanced Salary Record Modal -->
<div class="modal fade" id="recordModal" tabindex="-1" role="dialog" aria-labelledby="recordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document"> <!-- Same dialog size as provided -->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="recordModalLabel">Add Advanced Salary Record</h4>
            </div>
            {{ Form::open(['route' => 'salary-records.salary_advanced_store', 'method' => 'POST', 'class' => 'form-horizontal']) }}
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('employee_id', 'Employee ID', ['class' => 'col-sm-4 control-label']) }}
                    <div class="col-sm-8">
                        {{ Form::select('employee_id', $employees->mapWithKeys(function ($employee) {
                            return [$employee->au_id => "{$employee->au_name} ({$employee->au_user_id})"];
                        }), null, ['class' => 'form-control', 'id' => 'employee_id', 'placeholder' => 'Select Employee', 'required']) }}
                    </div>
                </div>

                <div class="form-group">
                    {{ Form::label('salary_month', 'Salary Month', ['class' => 'col-sm-4 control-label']) }}
                    <div class="col-sm-8">
                        {{ Form::text('salary_month', null, [
                            'class' => 'form-control readonly',
                            'id' => 'salary_month',
                            'placeholder' => 'Select Salary Month',
                            'autocomplete' => 'off',
                            'readonly' => 'readonly',
                            'required'
                        ]) }}
                    </div>
                </div>

                <div class="form-group">
                    {{ Form::label('amount', 'Amount', ['class' => 'col-sm-4 control-label']) }}
                    <div class="col-sm-8">
                        {{ Form::number('amount', null, ['class' => 'form-control', 'placeholder' => 'Enter Amount', 'required']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>
                {{ Form::submit('Save Record', ['class' => 'btn btn-primary']) }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>




    <script>
        $(document).ready(function() {
            // Open modal on button click
            $('#addRecordBtn').on('click', function() {
                $('#recordModal').modal('show');
            });

            // Initialize date picker for salary_month field
            $('#salary_month').datepicker({
                dateFormat: 'yy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true
            });
        });
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to all delete links
        document.querySelectorAll('.delete-record').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default anchor behavior
                
                if (confirm('Are you sure you want to delete this record?')) {
                    // Create a form element
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.getAttribute('data-url');
                    
                    // Add CSRF token as hidden input
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                    
                    // Add method spoofing as hidden input
                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    // Append form to body and submit
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
</script>

@endsection

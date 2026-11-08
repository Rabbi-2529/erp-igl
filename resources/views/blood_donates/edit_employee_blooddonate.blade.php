@extends('layouts.master')

@section('content')
@section('title', 'Edit Blood Donation')
@section('human_resource_class','active')
@section('payroll','active')
@section('employee_blood_donation','active')

<section class="content">
    <div class="row" style="display: flex; justify-content: center;">
        <div class="col-sm-6">
            
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Edit Blood Donation</h4>
                    </div>
                </div>

                <div class="panel-body">
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <!-- Form to edit the existing blood donation record -->
                    <form action="{{ route('payroll.blood_donates.update', $bloodDonation->id) }}" method="POST">
                        @csrf
                        @method('PUT')  <!-- Use PUT method for update -->
                        
                        @if(Auth::user()->au_user_type == 4)
                            <!-- Admin User: Show employee selection dropdown -->
                            <div class="form-group">
                                <label for="employee_id">Select Employee</label>
                                <select class="form-control" id="employee_id" name="employee_id" required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->au_user_id }}" {{ $bloodDonation->employee_id == $user->au_user_id ? 'selected' : '' }}>
                                            {{ $user->au_name }} ({{ $user->au_blood_group }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <!-- Non-Admin User: Automatically assign their own ID -->
                            <input type="hidden" name="employee_id" value="{{ $user_id }}">
                            <div class="form-group">
                                <label for="employee_name">Employee Name</label>
                                <input type="text" class="form-control" id="employee_name" value="{{ Auth::user()->au_name }}" disabled>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="donate_date">Donation Date</label>
                            <input type="text" name="donate_date" class="form-control" autocomplete="off" id="donate_date" 
       value="{{ \Carbon\Carbon::parse($bloodDonation->donate_date)->format('d-m-Y') }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="for_whom">For Whom</label>
                            <input type="text" class="form-control" id="for_whom" name="for_whom" value="{{ $bloodDonation->for_whom }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Blood Donation</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('custom_script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.2.1/axios.min.js"
        integrity="sha512-zJYu9ICC+mWF3+dJ4QC34N9RA0OVS1XtPbnf6oXlvGrLGNB8egsEzu/5wgG90I61hOOKvcywoLzwNmPqGAdATA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
<script>
    var currentYear = new Date().getFullYear(); // Get the current year
    var startYear = currentYear - 100; // Start year for the range (100 years ago)

    $("#donate_date").datepicker({
        daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
        format: 'dd-mm-yyyy',  // Ensure it displays d-m-Y
        startView: 2,  // Start view in "year" mode
        minViewMode: 2,  // Allows you to pick only the year
        viewMode: 'years',  // Show the year view
        todayBtn: "linked",  // Show today's button
        changeYear: true, // Allow year change by clicking
        changeMonth: true, // Allow month change
        yearRange: startYear + ":" + currentYear,  // Show the last 100 years
    });
</script>
@endsection

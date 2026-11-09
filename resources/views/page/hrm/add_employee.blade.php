@extends('layouts.master')
@section('content')
@section('title', 'Add Employee')

@section('human_resource_class','active')
@section('hrm_class','active')
@section('add_employee_class','active')


<style>
    .erp-num-note { margin: 4px 0 0; font-size: 12px; line-height: 1.4; }
    .erp-num-note:empty { display: none; }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Add Employee</h4>
                </div>
            </div>

            <div class="panel-body">
                <form action="{{ action('UserController@user_register_insert') }}" method="post" class="form-horizontal" id="user_registration_form" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group row">
                        <label for="au_name" class="col-sm-2 col-form-div">Name<i class="text-danger">*</i></label>
                        <div class="col-sm-4">
                            <input name="au_name" class="form-control" type="text" placeholder="Enter Authorized Name" required id="au_name" value="{{ old('au_name') }}">
                        </div>
                        <label for="job_type" class="col-sm-2 col-form-div">Job Type</label>
                        <div class="col-sm-4">
                            <input type="radio" id="job_type_h" name="job_type" value="work from home">
                            <label for="job_type_h" class="mr-2">Work From Home</label>
                            <input type="radio" id="job_type_o" name="job_type" value="work from office">
                            <label for="job_type_o">Work From Office</label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="au_department[]" class="col-sm-2 col-form-div">Department<i class="text-danger">*</i></label>
                        <div class="col-sm-4">
                            <select name="au_department[]" class="form-control" required>
                                <option value="" disabled selected>Select Option</option>
                                @foreach($depts as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label for="au_mobile" class="col-sm-2 col-form-div">Mobile<i class="text-danger">*</i></label>
                        <div class="col-sm-4">
                            <input name="au_mobile" class="form-control" type="number" placeholder="Enter Mobile Number" id="phone" required value="{{ old('au_mobile') }}">
                            <p class="text-danger" id="mobile_error_message"></p>
                            <p class="erp-num-note" id="mobile_available_message" aria-live="polite"></p>
                        </div>
                    </div>

                    <div class="form-group row">
                 @if(Auth::user()->au_user_type != 3)
    <label for="au_shifts" class="col-sm-2 col-form-div">Shift<i class="text-danger">*</i></label>
    <div class="col-sm-4">
        <select name="au_shifts" class="form-control" required>
            <option value="" selected disabled>Select</option>
            @foreach($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
            @endforeach
        </select>
    </div>
@endif
                        <label for="au_user_id" class="col-sm-2 col-form-div">Employee Id<i class="text-danger">*</i></label>
                        <div class="col-sm-4">
                            <input name="au_user_id" class="form-control" type="number" placeholder="Employee Id" id="au_user_id" required value="{{ old('au_user_id') }}" required>
                            <p class="erp-num-note" id="code_message" aria-live="polite"></p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="au_email" class="col-sm-2 col-form-div">Email<i class="text-danger">*</i></label>
                        <div class="col-sm-4">
                            <input name="au_email" class="form-control" type="email" placeholder="Enter Email" id="au_email" value="{{ old('au_email') }}" required>
                        </div>
                        <label for="au_user_type" class="col-sm-2 col-form-div">User Type</label>
                        <div class="col-sm-4">
                            <input type="radio" id="au_user_type_user" name="au_user_type" onchange="show_target(this.value)" value="6">
                            <label for="au_user_type_user">User</label>
                            <input type="radio" id="au_user_type_team_leader" name="au_user_type" onchange="show_target(this.value)" value="5">
                            <label for="au_user_type_team_leader">Team Leader</label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="au_user_salary" class="col-sm-2 col-form-div">Salary</label>
                        <div class="col-sm-4">
                        <input name="au_user_salary" class="form-control" type="number" step="0.01" placeholder="Enter Salary" id="au_user_salary" value="{{ old('au_user_salary') }}" required>
                        </div>
                        <label for="au_user_facility" class="col-sm-2 col-form-div">Facility</label>
                        <div class="col-sm-4">
                            <input name="au_user_facility" class="form-control" type="text" placeholder="Enter Facility" id="au_user_facility" value="{{ old('au_user_facility') }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="au_company_img" class="col-sm-2 col-form-div">Picture</label>
                        <div class="col-sm-4">
                            <input type="file" name="au_company_img" class="form-control" placeholder="Upload Image" id="image">
                            <input type="hidden" name="au_company_img">
                        </div>
                        <label for="au_address" class="col-sm-2 col-form-div">Address</label>
                        <div class="col-sm-4">
                            <input name="au_address" class="form-control" type="text" placeholder="Put Detail Address" id="au_address" value="{{ old('au_address') }}">
                        </div>
                    </div>

                    

                    <div class="form-group row" id="user" style="display: none;">
                        <label for="au_team_id_user" class="col-sm-2 col-form-div">Team Name</label>
                        <div class="col-sm-4">
                            <select class="form-control" name="au_team_id_user">
                                <option value="" selected disabled>Select</option>
                                @foreach($teams as $team)
                                @if(\App\Models\Sds_team_name::is_team_leader_exists($team->tl_sl_id) === true)
                                <option value="{{ $team->tl_sl_id }}">{{ $team->tl_team_name }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    

                    <div class="form-group row" id="team_leader" style="display: none;">
                        <label for="au_team_id_team" class="col-sm-2 col-form-div">Team Leader</label>
                        <div class="col-sm-4">
                        <select class="form-control" name="au_team_id_team">
                            <option value="">Select</option>
                            @foreach($unassigned_teams as $team)
                                    <option value="{{ $team->tl_sl_id }}">{{ $team->tl_team_name }}</option>
                                @endforeach
                        </select>
                    </div>
                    </div>

                    <div class="form-group text-right">
                        <button type="reset" class="btn btn-primary w-md m-b-5">Reset</button>
                        <button type="submit" class="btn btn-success w-md m-b-5">Save</button>
                    </div>
                    {{ Form::close() }}
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Get the initial value of the radio button
        var userType = $("input[name='au_user_type']:checked").val();

        // Show/hide elements based on the initial value
        show_target(userType);

        // Show/hide elements on radio button change
        $("input[name='au_user_type']").change(function() {
            var userType = $(this).val();
            show_target(userType);
        });
    });

    function show_target(val) {
        if (val == 6) {
            $('#team_leader').hide();
            $('#user').show();
            $("select[name='au_team_id_user'] option").show(); // Show all options
        } else if (val == 5) {
            $('#team_leader').show();
            $('#user').hide();
            $("select[name='au_team_id_user'] option").each(function() {
                if ($(this).val() != "") {
                    $(this).hide(); // Hide options except the default option
                }
            });
        } else {
            $('#team_leader').hide();
            $('#user').hide();
            $("select[name='au_team_id_user'] option").show(); // Show all options
        }
    }

    //////// Check Number Availability/////////
    // A mobile or Employee ID of someone who left (or is turned off) can go to the new employee, who gets a new
    // record of their own; one that belongs to someone still working cannot. The server checks this again.
var error_mobile = true;
var error_code = false;
var numberCheckUrl = @json(route('hrm.employees.number-check'));
var numberCheckTimer = null;

function showNumberNote(el, result) {
    var colour = { free: 'text-success', reuse: 'text-info', inactive: 'text-warning', taken: 'text-danger' }[result.state] || 'text-success';
    $(el).removeClass('text-success text-info text-warning text-danger').addClass(colour).text(result.text || '');
}

function checkNumbers() {
    var mobile = $('#phone').val(), code = $('#au_user_id').val();
    $.ajax({
        type: "GET",
        url: numberCheckUrl,
        data: { mobile: mobile.length === 11 ? mobile : '', code: code },
        dataType: 'json',
        success: function (result) {
            if ($('#phone').val() !== mobile || $('#au_user_id').val() !== code) { return; }   // the user typed on
            error_code = result.code.state === 'taken';
            showNumberNote('#code_message', result.code);
            if (mobile.length !== 11) { return; }
            error_mobile = result.mobile.state === 'taken';
            showNumberNote('#mobile_available_message', result.mobile);
            if (result.mobile.state === 'free') { $('#mobile_available_message').text('Mobile Number Available'); }
        }
    });
}

function checkNumbersSoon() {
    clearTimeout(numberCheckTimer);
    numberCheckTimer = setTimeout(checkNumbers, 300);
}

$(document).ready(function(){
    $('#phone').on('input', function () {
        mobilenumerror();
        checkNumbersSoon();
    });
    $('#au_user_id').on('input', function () {
        error_code = false;
        $('#code_message').text('');
        checkNumbersSoon();
    });

    if ($("#phone").val().length > 0 || $('#au_user_id').val().length > 0) {
        mobilenumerror();
        checkNumbers();
    }
});

////////////number check ////////////
function mobilenumerror() {
    var mobile_num_length = $("#phone").val().length;
    if (mobile_num_length < 11 || mobile_num_length > 11) {
        error_mobile = true;
        $("#mobile_error_message").html("Invalid Mobile Number");
        $('#mobile_available_message').text('');
    } else {
        error_mobile = false;
        $("#mobile_error_message").html("");
    }
}

$("#user_registration_form").submit(function () {
    if (error_mobile == true) {
        $("#phone").focus();
        return false;
    } else if (error_code == true) {
        $("#au_user_id").focus();
        return false;
    } else {
        return true;
    }
});

</script>

@endsection

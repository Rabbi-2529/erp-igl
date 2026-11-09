

@extends('layouts.master')
@section('content')
@section('title', 'Update Employee')


@section('human_resource_class','active')
@section('hrm_class','active')
@section('manage_employee_class','active')

  
<style>
    .stepwizard-step p {
        margin-top: 10px;
    }
    .stepwizard-row {
        display: table-row;
    }
    .stepwizard {
        display: table;
        width: 50%;
        position: relative;
    }
    .stepwizard-step button[disabled] {
        opacity: 1 !important;
        filter: alpha(opacity=100) !important;
    }
    .stepwizard-row:before {
        top: 14px;
        bottom: 0;
        position: absolute;
        content: " ";
        width: 100%;
        height: 1px;
        background-color: #ccc;
        z-order: 0;
    }
    .stepwizard-step {
        display: table-cell;
        text-align: center;
        position: relative;
    }
    .btn-circle {
        width: 30px;
        height: 30px;
        text-align: center;
        padding: 6px 0;
        font-size: 12px;
        line-height: 1.428571429;
        border-radius: 15px;
    }
</style>


<div class="row"> 
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Update Employee</h4>
                </div>
            </div>

            <div class="panel-body">
                
                <div class="stepwizard col-md-offset-3">
                    <div class="stepwizard-row setup-panel">
                    <div class="stepwizard-step">
                        <a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
                        <p>Basic Info</p>
                    </div>
                    <div class="stepwizard-step">
                        <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
                        <p>Team Info</p>
                    </div>
                    <div class="stepwizard-step">
                        <a href="#step-3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</a>
                        <p>Access</p>
                    </div>
                    <div class="stepwizard-step">
                        <a href="#step-4" type="button" class="btn btn-default btn-circle" disabled="disabled">4</a>
                        <p>Documentation</p>
                    </div>
                    </div>
                </div>
            
                
                {{ Form::open([ 'action' => ['UserController@user_update', $user_edits->au_id] , 'method' => 'post' , 'class' => 'form-horizontal col-sm-12' , 'files' => true]) }}
                @csrf
                <br>
                <br>
                    <div class="row setup-content" id="step-1">
                        <div class="col-lg-9 col-md-9 col-sm-12">
                            @if( Auth::user()->au_user_type ==1 )
                                <div class="form-group row">
                                    <label for="au_company_name" class="col-sm-3 control-label">Company Name</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="au_company_name" class="form-control" id="au_company_name" placeholder="Enter Company Name" value="{{ $user_edits->au_name }}" required>
                                    </div>
                                </div>
                            @endif
                        
                            <div class="form-group row">
                                <label for="" class="col-sm-3 control-label">Employee Name</label>
                                <div class="col-sm-6">
                                @if(Auth::user()->au_user_type == 1)
`                            <input type="text" name="au_name" class="form-control" id="inputEmail3" placeholder="Enter Authorized Name" value="{{ $user_edits->au_name }}" required>
                                    @else
                                    <select class="form-control"  name="au_name">
                                        <option value="{{ $user_edits->au_name }}">{{ $user_edits->au_name }}</option>
                                        @foreach($unassigned_teams as $team)
                                            <option value="{{ $team->au_name }}">{{ $team->au_name }}</option>
                                        @endforeach
                                    </select>`
                                    @endif
                                    @include('page.hrm.employees._same_person', ['spOldName' => $user_edits->au_name, 'spSelector' => '[name="au_name"]'])
                                            </div>
                                        </div>
                        
                            <div class="form-group row">
                                <label for="au_email" class="col-sm-3 control-label">Email</label>
                                <div class="col-sm-6">
                                    <input type="email" name="au_email" class="form-control" id="au_email" placeholder="Place Employee Email." value="{{ $user_edits->au_email }}" required>
                                </div>
                            </div>
                        
                            <div class="form-group row">
                                <label for="au_mobile" class="col-sm-3 control-label">Mobile</label>
                                <div class="col-sm-6">
                                    <input type="text" name="au_mobile" class="form-control" id="au_mobile" placeholder="Enter Mobile Number" value="{{ $user_edits->au_mobile }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                            <label for="au_user_salary" class="col-sm-3 control-label">Salary</label>
                            <div class="col-sm-6">
                                <input type="text" name="au_user_salary" class="form-control" id="au_user_salary" placeholder="Enter Salary" value="{{ $user_edits->au_user_salary }}" required>
                            </div>
                        </div>

                        <!-- Facilities Input Field -->
                        <div class="form-group row">
                            <label for="au_user_facility" class="col-sm-3 control-label">Facilities</label>
                            <div class="col-sm-6">
                                <input type="text" name="au_user_facility" class="form-control" id="au_user_facility" placeholder="Enter Facilities (comma-separated)" value="{{ $user_edits->au_user_facility }}">
                            </div>
                        </div>
                        `                       
                            <div class="form-group row">
                                <label for="au_password" class="col-sm-3 control-label">Password</label>
                                <div class="col-sm-6">
                                    <input type="password" name="au_password" class="form-control" id="au_password" placeholder="Create Password...">
                                </div>
                            </div>
                        
                            <div class="form-group row">
                                <label for="au_address" class="col-sm-3 control-label">Address</label>
                                <div class="col-sm-6">
                                    <input type="text" name="au_address" class="form-control" id="au_address" placeholder="Put Detail Address.." value="{{ $user_edits->au_address }}" required>
                                </div>
                            </div>
                        
                            <div class="form-group row">
                                <label for="au_company_img" class="col-sm-3 control-label">Image</label>
                                <div class="col-sm-6">
                                    <input type="file" name="au_company_img" class="form-control" id="au_company_img" placeholder="">
                                </div>
                            </div>

                            
                        </div>

                        <div class="col-sm-3">
                            @php
                                $imagePath = asset('assets/img/icons/user.png');
                                $customImagePath = asset('public/profile/' . Auth::user()->au_company_img);
                            @endphp
                            <img id="imagePreview" src="{{ isset($customImagePath) ? $customImagePath : $imagePath }}" alt="Image Preview" class="img-thumbnail" style="max-width: 100%; max-height: 150px;">
                        </div>


                        <div class="col-sm-9">
                            <!-- Empty column to create space for the button -->
                        </div>

                        <button class="btn btn-primary w-md m-b-5 nextBtn pull-right" type="button">Next</button>
                    </div>
                        
                    <div class="setup-content" id="step-2">

                        @if( Auth::user()->au_user_type != 1 )

                        <div class="form-group row">
                            <label for="userType" class="col-sm-2 control-label">User Type</label>
                            <div class="col-sm-6">
                                <input type="radio" id="au_user_type_user" name="au_user_type"
                            onchange="show_terget(this.value)" value="6" required
                            {{ ($user_edits->au_user_type == 6)?'checked':'' }}>
                            <label for="au_user_type_user">User</label>
                                <input type="radio" id="au_user_type_team_leader" name="au_user_type" onchange="show_terget(this.value)" value="5" required
                            {{ ($user_edits->au_user_type == 5)?'checked':'' }}>
                            <label for="au_user_type_team_leader">Team Leader</label>
                            </div>
                        </div>

                        <div class="form-group row" id="user" style="display: none;">
                            <label for="inputEmail3" class="col-sm-2 control-label">Team Name</label>
                            <div class="col-sm-6">
                            <select class="form-control" name="au_team_id_user">
                                <option value="" disabled selected>Select Option</option>
                                @foreach($teams as $team)
                                    <option {{ ($user_edits->au_user_type == 6)? (($user_edits->au_team_id == $team->tl_sl_id)? 'selected':''):'' }}
                                    value="{{ $team->tl_sl_id }}">{{ $team->tl_team_name }}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>

                        <div class="form-group row" id="team_leader" style="display: none;">
                            <label for="inputEmail3" class="col-sm-2 control-label">Team Leader</label>
                            <div class="col-sm-6">
                            <select class="form-control" name="au_team_id_team">
                                <option value="" di sabled selected>Select Option</option>
                                @foreach($teams as $team)
                                    <option {{ ($user_edits->au_user_type == 5)? (($user_edits->au_team_id == $team->tl_sl_id)? 'selected':''):'' }}
                                    value="{{ $team->tl_sl_id }}">{{ $team->tl_team_name }}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="au_user_id" class="col-sm-2 control-label">User Id</label>
                            <div class="col-sm-6">
                              <input type="text" name="au_user_id" class="form-control" id="au_user_id" value="{{ $user_edits->au_user_id }}" placeholder="User id..." required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="department" class="col-sm-2 control-label">Department</label>
                            <div class="col-sm-6">
                                <select name="department" class="form-control select2" id="department" required>
                                    <option value="">Select</option>
                                    @foreach ($departments as $key)
                                        <option value="{{ $key->id }}" {{ $user_edits->au_department == $key->id ? 'selected' : '' }}>
                                            {{ $key->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>                        

                @if(Auth::user()->au_user_type != 3)
                    <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-2 control-label">Shift</label>
                        <div class="col-sm-6">
                            <select name="au_shifts" class="form-control select2" id="au_shifts_id" required>
                                <option value="">Select</option>
                                @foreach ($shifts as $key => $value)
                                    <option value="{{ $value->id }}" {{ $user_edits->au_shifts == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                        

                        @endif

                        <button class="btn btn-info w-md m-b-5 prevBtn pull-left" type="button">Previous</button>
                        <button class="btn btn-primary w-md m-b-5 nextBtn pull-right" type="button">Next</button>

                    </div>

                    <div class="setup-content text-center" id="step-3">
                        <div class="form-group row">
                            <label for="status" class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-6">
                                <input type="radio" name="status" value="1" {{ ($user_edits->au_status == 1)? 'checked':'' }}  required><b style="color: green;">Activate</b>
                                <input type="radio" name="status" value="0" {{ ($user_edits->au_status == 0)? 'checked':'' }} required><b style="color: red;">Deactivate</b>
                            </div>
                        </div>
                    
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 control-label">Flexi Permission</label>
                            <div class="col-sm-6">
                                <input type="radio" name="flexi_status" value="1" {{ ($user_edits->au_flexi_permission == 1)? 'checked':'' }} ><b style="color: green;">Activate</b>
                                <input type="radio" name="flexi_status" value="0" {{ ($user_edits->au_flexi_permission == 0)? 'checked':'' }}><b style="color: red;">Deactivate</b>
                            </div>
                        </div>
                    
                        <button class="btn btn-info w-md m-b-5 prevBtn pull-left" type="button">Previous</button>
                        <button class="btn btn-primary w-md m-b-5 nextBtn pull-right" type="button">Next</button>
                    </div>
                    

                    <div class="setup-content" id="step-4">

                        <div class="form-group row">
                            <p>There will be come Documents system.</p>
                        </div>             
                        
                        <button class="btn btn-primary w-md m-b-5 prevBtn pull-left" type="button">Previous</button>
                        <button class="btn btn-success w-md m-b-5 nextBtn pull-right" type="submit">Update</button>

                    </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var userType = $("input[name='au_user_type']:checked").val();

        show_target(userType);

        $("input[name='au_user_type']").change(function() {
            var userType = $(this).val();
            show_target(userType);
        });
    });

    $(document).ready(function () {
    var navListItems = $('div.setup-panel div a'),
            allWells = $('.setup-content'),
            allNextBtn = $('.nextBtn'),
            allPrevBtn = $('.prevBtn');

    allWells.hide();

    navListItems.click(function (e) {
        e.preventDefault();
        var $target = $($(this).attr('href')),
                $item = $(this);

        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-primary').addClass('btn-default');
            $item.addClass('btn-primary');
            // finished steps get a tick, and the line fills up to the current step (theme colours)
            var $steps = navListItems.parent();
            var at = $steps.index($item.parent());
            navListItems.each(function (i) { $(this).toggleClass('is-done', i < at); });
            var wiz = $item.closest('.stepwizard')[0], w = wiz.getBoundingClientRect(), c = this.getBoundingClientRect();
            if (w.width > 0) { wiz.style.setProperty('--erp-step-progress', ((c.left + c.width / 2 - w.left) / w.width * 100).toFixed(1) + '%'); }
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }
    });
    
    allPrevBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            prevStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a");

            prevStepWizard.removeAttr('disabled').trigger('click');
    });

    allNextBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
            curInputs = curStep.find("input[type='text'],input[type='url']"),
            isValid = true;

        $(".form-group").removeClass("has-error");
        for(var i=0; i<curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        if (isValid)
            nextStepWizard.removeAttr('disabled').trigger('click');
    });

    $('div.setup-panel div a.btn-primary').trigger('click');

    $(".nextBtn:last").on("click", function() {
        var isValid = true;

        if (isValid) {
            $("#user_registration_form").submit();
        }
    });

    $(".prevBtn").on("click", function() {
        $(".step").removeClass("active-step");
        currentStep--;
        $(".step:nth-child(" + currentStep + ")").addClass("active-step");
    });
    });

    function show_target(val) {
        if (val == 6) {
            $('#team_leader').hide();
            $('#user').show();
            $("select[name='au_team_id_user'] option").show();
        } else if (val == 5) {
            $('#team_leader').show();
            $('#user').hide();
            $("select[name='au_team_id_user'] option").each(function() {
                if ($(this).val() != "") {
                    $(this).hide();
                }
            });
        } else {
            $('#team_leader').hide();
            $('#user').hide();
            $("select[name='au_team_id_user'] option").show();
        }
    }

    var error_mobile = true;
    $(document).ready(function(){
        $('#phone').keyup(function(){
            var mobileno = $(this).val();
            $.ajax({
                type: "GET",
                url: "mobileno_exist",
                data: { au_mobile: mobileno},
                success: function (result) {
                    if (error_mobile == false){
                        if(result == "true") {
                            error_mobile = true;
                            $('#mobile_error_message').html('Mobile Number Already Exists');
                            $('#mobile_available_message').html('');
                        } else {
                            error_mobile = false;
                            $('#mobile_error_message').html('');
                            $('#mobile_available_message').html('Mobile Number Available');
                        }
                    }
                }
            });
        });

        if ($("#phone").val().length > 0) {
            mobilenumerror();
        }
    });

    function mobilenumerror() {
        var mobile_num_length = $("#phone").val().length;
        if (mobile_num_length < 11 || mobile_num_length > 11) {
            error_mobile = true;
            $("#mobile_error_message").html("Invalid Mobile Number");
            $('#mobile_available_message').html('');
        } else {
            error_mobile = false;
            $("#mobile_error_message").html("");
        }
    }

    $("#user_registration_form").submit(function () {
        if (error_mobile == true) {
            $("#phone").focus();
            return false;
        } else {
            return true;
        }
    });

    $("#phone").keyup(function () {
        mobilenumerror();
    });

    $(document).ready(function () {
        $("#au_company_img").change(function () {
            readURL(this);
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#imagePreview').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
    });

</script>

@endsection
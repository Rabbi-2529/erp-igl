@extends('layouts.master')
@section('content')
    <style>
        .alert {
            padding: 10px !important;
            margin-bottom: 10px;
            margin-top: 10px;
        }
    </style>
    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Update Profile</h3>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session()->has('msg'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('msg') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @elseif(session()->has('msg2'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('msg2') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{ Form::open(['action' => ['UserController@myInfoUpdate_1_update', $user->au_id], 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'user_registration_form', 'files' => true, 'onsubmit' => 'return validateForm()', 'name' => 'myForm']) }}

            <div class="box-body">
                <div style="border: 1px solid #ddd; padding:10px 0px; margin-bottom:10px;">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Employee Id <span style="color: red">
                                *</span></label>
                        <div class="col-sm-6">
                            <input type="text" name="au_user_id" value="{{ $user->au_user_id }}" class="form-control"
                                id="inputEmail3" placeholder="Enter Authorized Name" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Email <span style="color: red">
                                *</span></label>
                        <div class="col-sm-6">
                            <input type="email" name="au_email" value="{{ $user->au_email }}" class="form-control"
                                id="inputEmail3" placeholder="Place Email..." readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Mobile Number <span style="color: red">
                                *</span></label>
                        <div class="col-sm-6">
                            <input type="text" name="au_mobile" autocomplete="off" value="{{ $user->au_mobile }}"
                                id="mobile" class="form-control" placeholder="Enter Mobile Number" readonly>

                        </div>
                    </div>
                </div>
                <div style="border: 1px solid #ddd; padding:10px 0px; margin-bottom:10px;">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Name <span style="color: red">
                                *</span></label>
                        <div class="col-sm-5">
                            {{-- <input type="text" name="au_name"
                                value="{{ $user->au_name != null ? $user->au_name : old('au_name') }}" class="form-control"
                                id="user_name" placeholder="Enter Authorized Name"> --}}
                            <input type="text" name="au_name"
                                value="{{ $user->au_name != null ? $user->au_name : old('au_name') }}" class="form-control" placeholder="Enter Authorized Name">

                        </div>
                        {{-- <button onclick="sendOtp(event)" class="btn btn-sm btn-primary " id="otpBtn"
                            href="">Change Name</button>
                        <span id="alertSuccess" class="bg-success  alert">Name Changed Successfully!</span> --}}
                    </div>
                    {{-- <div class="form-group" id="verification_box" style="display: none">

                        <label class="col-sm-2 control-label" for="verification_code">Verification Code<span
                                style="color: red"> *</span></label>
                        <div class="col-sm-2">
                            <input name="verification_code" class="form-control" type="text" id="verification_code">
                        </div>

                        <div class="col-sm-2">
                            <button onclick="saveName(event)" class="btn btn-primary" id="otpBtn"
                                href="">Save</button>
                        </div>

                        <span id="alertMessage" style="display: none" class="bg-aqua alert w-50">Verification code send to
                            your number successfully. Please enter it!</span>
                        <span id="invalidMessage" class="bg-danger alert w-50">Invalid Verification Code! Try
                            Again..</span>

                    </div> --}}

                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Persional Mobile Number <span
                                style="color: red"> *</span></label>
                        <div class="col-sm-6">
                            <input type="text" name="persional_mobile" autocomplete="off"
                                value="{{ $user->persional_mobile != null ? $user->persional_mobile : old('persional_mobile') }}"
                                class="form-control" id="pmobileCheck" placeholder="Enter Mobile Number" required>
                            <p class="text-danger" id="mobile_error_message"></p>
                            <p class="text-danger" id="p_mobile_valid_check"></p>
                            <p class="text-success" id="mobile_available_message"></p>
                        </div>
                    </div>
                    <div class="form-group">
    <label for="blood_group" class="col-sm-2 control-label">Blood Group <span style="color: red">*</span></label>
    <div class="col-sm-6">
        <div class="custom_color" style="position: relative; padding-left: 0px; display: flex; flex-wrap: wrap;">
            <div style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" class="blood-group-checkbox" name="au_blood_group" value="A+" 
                    {{ old('au_blood_group', $user->au_blood_group) == 'A+' ? 'checked' : '' }}
                    data-toggle="toggle" data-on="A+" data-off="Off" data-size="mini">
                A+
            </div>
            <div style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" class="blood-group-checkbox" name="au_blood_group" value="A-" 
                    {{ old('au_blood_group', $user->au_blood_group) == 'A-' ? 'checked' : '' }}
                    data-toggle="toggle" data-on="A-" data-off="Off" data-size="mini">
                A-
            </div>
            <div style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" class="blood-group-checkbox" name="au_blood_group" value="B+" 
                    {{ old('au_blood_group', $user->au_blood_group) == 'B+' ? 'checked' : '' }}
                    data-toggle="toggle" data-on="B+" data-off="Off" data-size="mini">
                B+
            </div>
            <div style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" class="blood-group-checkbox" name="au_blood_group" value="B-" 
                    {{ old('au_blood_group', $user->au_blood_group) == 'B-' ? 'checked' : '' }}
                    data-toggle="toggle" data-on="B-" data-off="Off" data-size="mini">
                B-
            </div>
            <div style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" class="blood-group-checkbox" name="au_blood_group" value="AB+" 
                    {{ old('au_blood_group', $user->au_blood_group) == 'AB+' ? 'checked' : '' }}
                    data-toggle="toggle" data-on="AB+" data-off="Off" data-size="mini">
                AB+
            </div>
            <div style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" class="blood-group-checkbox" name="au_blood_group" value="AB-" 
                    {{ old('au_blood_group', $user->au_blood_group) == 'AB-' ? 'checked' : '' }}
                    data-toggle="toggle" data-on="AB-" data-off="Off" data-size="mini">
                AB-
            </div>
            <div style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" class="blood-group-checkbox" name="au_blood_group" value="O+" 
                    {{ old('au_blood_group', $user->au_blood_group) == 'O+' ? 'checked' : '' }}
                    data-toggle="toggle" data-on="O+" data-off="Off" data-size="mini">
                O+
            </div>
            <div style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" class="blood-group-checkbox" name="au_blood_group" value="O-" 
                    {{ old('au_blood_group', $user->au_blood_group) == 'O-' ? 'checked' : '' }}
                    data-toggle="toggle" data-on="O-" data-off="Off" data-size="mini">
                O-
            </div>
        </div>
    </div>
</div>


                    <div class="form-group">
                        <label for="joining_date" class="col-sm-2 control-label">Joining Date<span
                                style="color: red">*</span></label>
                        <div class="col-sm-6">
                            <input type="text" name="au_created_date"
                                value="{{ $user->au_created_date != null ? $user->au_created_date : old('au_created_date') }}"
                                class="form-control" id="joining_date" placeholder="Enter Joining Date.." required>

                        </div>
                    </div>
                </div>



                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">Image</label>
                    <div class="col-sm-6">
                        @if ($user->au_company_img != null)
                            <input type="file" name="au_company_img" class="form-control" id="inputEmail3">
                            <img src="{{ asset('public/profile/' . Auth::user()->au_company_img) }}"
                                style="height: 110px; width: 100px; margin-top: 10px;" class="">
                        @else
                            <input type="file" name="au_company_img" class="form-control" id="inputEmail3" required>
                        @endif

                    </div>
                </div>

            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" id="user_register_submit" class="btn btn-info pull-right">Update</button>
                </div>
            </div>
            <!-- /.box-footer -->
            {{ Form::close() }}
        </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Bootstrap Toggle if you're using it
    $('.blood-group-checkbox').bootstrapToggle();

    // Handle change events for the checkboxes
    $('.blood-group-checkbox').change(function() {
        // If this checkbox is checked, uncheck all others
        if ($(this).prop('checked')) {
            // Uncheck all other checkboxes and trigger their change event to update the toggle appearance
            $('.blood-group-checkbox').not(this).prop('checked', false).change();
        }
    });
});

</script>
@endsection
@section('custom_script')
<link rel="stylesheet" href="{{ asset('asset/css/bootstrap-toggle.min.css') }}">
<script>
    $(document).ready(function() {
    $('.blood-group-checkbox').change(function() {
        if ($(this).prop('checked')) {
            // Uncheck all other checkboxes
            $('.blood-group-checkbox').not(this).prop('checked', false);
        }
    });
});

</script>

<script>
        $('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
        });
</script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js">
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.2.1/axios.min.js"
        integrity="sha512-zJYu9ICC+mWF3+dJ4QC34N9RA0OVS1XtPbnf6oXlvGrLGNB8egsEzu/5wgG90I61hOOKvcywoLzwNmPqGAdATA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById("alertMessage").style.display = 'none';
            document.getElementById("verification_box").style.display = 'none';
            document.getElementById("alertSuccess").style.display = 'none';
            document.getElementById("invalidMessage").style.display = 'none';

            // Add event listeners to buttons
            document.getElementById("otpBtn").addEventListener('click', sendOtp);
            document.getElementById("saveBtn").addEventListener('click', saveName);
        });

        function sendOtp(e) {
            e.preventDefault();

            // Get values for the request
            let mobile = document.getElementById("mobile").value; // Ensure mobile is correctly defined
            let name = document.getElementById("user_name").value;

            fetch('{{ route('send-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        mobile: mobile,
                        name: name
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Show and hide elements based on response
                    document.getElementById("alertMessage").style.display = 'block';
                    document.getElementById("verification_box").style.display = 'block';
                    document.getElementById("otpBtn").disabled = true;

                    setTimeout(() => {
                        document.getElementById("otpBtn").disabled = false;
                    }, 15000);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function saveName(e) {
            e.preventDefault();

            axios.post('/change-name', {
                    verification_code: document.getElementById("verification_code").value,
                    name: document.getElementById("user_name").value
                })
                .then(response => {
                    // Show and hide elements based on response
                    if (response.data.state === '400') {
                        document.getElementById("alertSuccess").style.display = 'block';
                        document.getElementById("verification_box").style.display = 'none';

                        setTimeout(() => {
                            document.getElementById("alertSuccess").style.display = 'none';
                        }, 15000);
                    } else if (response.data.state === '500') {
                        document.getElementById("alertMessage").style.display = 'none';
                        document.getElementById("invalidMessage").style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>
    <script type="text/javascript">
        //////// Check Number Availability/////////
        var error_mobile = true;
        $(document).ready(function() {
            $('#pmobileCheck').change(function() {
                var mobileno = $(this).val();


                $.ajax({
                    type: "GET",
                    url: "mobileno_exist",
                    data: {
                        au_mobile: mobileno
                    },
                    success: function(result) {
                        if (error_mobile == false) {
                            if (result == "true") {
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

                var mobile_number_check = mobileno.substring(0, 3);
                if (mobile_number_check != '019' && mobile_number_check != '017' && mobile_number_check !=
                    '013' && mobile_number_check != '018' && mobile_number_check != '016' &&
                    mobile_number_check != '015' && mobile_number_check != '014') {
                    error_mobile = true;
                    $('#p_mobile_valid_check').html('Mobile Number is not valid!');
                    $('#mobile_available_message').html('');

                } else {
                    $('#p_mobile_valid_check').html('');
                    $('#mobile_available_message').html('');
                }


            });

            if ($("#pmobileCheck").val().length > 0) {
                p_mobilenumerror();
            }

        });
        ////////////////////////////////////
        //////////////number check ////////////
        $("#user_registration_form").submit(function() {
            if (error_mobile == true) {
                $("#pmobileCheck").focus();
                return false;
            } else {
                return true;
            }
        });


        $("#pmobileCheck").keyup(function() {
            p_mobilenumerror();
        });

        function p_mobilenumerror() {


            var mobile_num_length = $("#pmobileCheck").val().length;
            if (mobile_num_length < 11 || mobile_num_length > 11) {
                /*$("#mobile_error_message_"+sl).html("Invalid Mobile Number");*/
                error_mobile = true;

                $("#mobile_error_message").html("Mobile Number is not valid");
                $('#mobile_available_message').html('');

            } else {
                error_mobile = false;
                $("#mobile_error_message").html("");

            }


        }
    </script>
    {{-- check father mobile number  --}}
    <script type="text/javascript">
        //////// Check Number Availability/////////
        var error_mobile = true;
        $(document).ready(function() {
            $('#f_mobile').change(function() {
                var mobileno = $(this).val();

                $.ajax({
                    type: "GET",
                    url: "mobileno_exist",
                    data: {
                        au_mobile: mobileno
                    },
                    success: function(result) {
                        if (error_mobile == false) {
                            if (result == "true") {
                                error_mobile = true;
                                $('#f_mobile_error_message').html(
                                    'Mobile Number Already Exists');
                                $('#f_mobile_available_message').html('');
                            } else {
                                error_mobile = false;
                                $('#f_mobile_error_message').html('');
                                $('#f_mobile_available_message').html(
                                'Mobile Number Available');
                            }
                        }
                    }
                });

                var mobile_number_check = mobileno.substring(0, 3);
                if (mobile_number_check != '019' && mobile_number_check != '017' && mobile_number_check !=
                    '013' && mobile_number_check != '018' && mobile_number_check != '016' &&
                    mobile_number_check != '015' && mobile_number_check != '014') {
                    error_mobile = true;
                    $('#f_mobile_valid_check').html('Mobile Number is not valid!');
                    $('#f_mobile_available_message').html('');

                } else {
                    $('#f_mobile_valid_check').html('');
                }
            });

            if ($("#f_mobile").val().length > 0) {
                f_mobilenumerror();
            }

        });
        ////////////////////////////////////
        //////////////number check ////////////
        $("#user_registration_form").submit(function() {
            if (error_mobile == true) {
                $("#f_mobile").focus();
                return false;
            } else {
                return true;
            }
        });


        $("#f_mobile").keyup(function() {
            f_mobilenumerror();
        });

        function f_mobilenumerror() {
            var mobile_num_length = $("#f_mobile").val().length;
            if (mobile_num_length < 11 || mobile_num_length > 11) {
                /*$("#mobile_error_message_"+sl).html("Invalid Mobile Number");*/
                error_mobile = true;

                $("#f_mobile_error_message").html("Mobile Number is not valid");
                $('#f_mobile_available_message').html('');

            } else {
                error_mobile = false;
                $("#f_mobile_error_message").html("");

            }
        }
    </script>
    {{-- check mother mobile number  --}}
    <script type="text/javascript">
        //////// Check Number Availability/////////
        var error_mobile = true;
        $(document).ready(function() {
            $('#m_mobile').change(function() {
                var mobileno = $(this).val();

                $.ajax({
                    type: "GET",
                    url: "mobileno_exist",
                    data: {
                        au_mobile: mobileno
                    },
                    success: function(result) {
                        if (error_mobile == false) {
                            if (result == "true") {
                                error_mobile = true;
                                $('#m_mobile_error_message').html(
                                    'Mobile Number Already Exists');
                                $('#m_mobile_available_message').html('');
                            } else {
                                error_mobile = false;
                                $('#m_mobile_error_message').html('');
                                $('#m_mobile_available_message').html(
                                'Mobile Number Available');
                            }
                        }
                    }
                });

                var mobile_number_check = mobileno.substring(0, 3);
                if (mobile_number_check != '019' && mobile_number_check != '017' && mobile_number_check !=
                    '013' && mobile_number_check != '018' && mobile_number_check != '016' &&
                    mobile_number_check != '015' && mobile_number_check != '014') {
                    error_mobile = true;
                    $('#m_mobile_valid_check').html('Mobile Number is not valid!');
                    $('#m_mobile_available_message').html('');

                } else {
                    $('#m_mobile_valid_check').html('');
                }

            });

            if ($("#m_mobile").val().length > 0) {
                mobilenumerror();
            }

        });
        ////////////////////////////////////
        //////////////number check ////////////
        $("#user_registration_form").submit(function() {
            if (error_mobile == true) {
                $("#m_mobile").focus();
                return false;
            } else {
                return true;
            }
        });


        $("#m_mobile").keyup(function() {
            mobilenumerror();
        });

        function mobilenumerror() {
            var mobile_num_length = $("#m_mobile").val().length;
            if (mobile_num_length < 11 || mobile_num_length > 11) {
                /*$("#mobile_error_message_"+sl).html("Invalid Mobile Number");*/
                error_mobile = true;

                $("#m_mobile_error_message").html("Mobile Number is not valid");
                $('#m_mobile_available_message').html('');

            } else {
                error_mobile = false;
                $("#m_mobile_error_message").html("");

            }
        }
    </script>
    <script>
        $("#orginal_birthday").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            format: 'dd/mm/yyyy'
        });
        $("#cerfiticate_birthday").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            format: 'dd/mm/yyyy'
        });
        $("#joining_date").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            format: 'dd-MM-yyyy'
        });
    </script>
    <script>
        $('#m_mobile').change(function() {
            var f_mobile = $('#f_mobile').val();
            var m_mobile = $('#m_mobile').val();
            if (f_mobile == m_mobile) {
                $('#f_m_mobile_check').html('Father and Mother mobile number will not be same.')

            } else {
                $('#f_m_mobile_check').html('')
            }

        });
        $('#f_mobile').change(function() {
            var pmobileCheck = $('#pmobileCheck').val();
            var f_mobile = $('#f_mobile').val();
            if (f_mobile == pmobileCheck) {
                $('#f_p_mobile_check').html('Persional and Father mobile number will not be same.')

            } else {
                $('#f_p_mobile_check').html('')
            }

        });
        $('#m_mobile').change(function() {
            var pmobileCheck = $('#pmobileCheck').val();
            var m_mobile = $('#m_mobile').val();
            if (m_mobile == pmobileCheck) {
                $('#p_m_mobile_check').html('Persional and Mother mobile number will not be same.')

            } else {
                $('#p_m_mobile_check').html('')
            }

        });
    </script>
    <script>
        function validateForm() {
            let nid = document.getElementById('nid').value;
            let passport = document.getElementById('passport').value;
            let driving_licence = document.getElementById('driving_licence').value;
            let birth_certificate = document.getElementById('birth_certificate').value;
            if (nid == "" && passport == "" && driving_licence == "" && birth_certificate == "") {
                // alert("NID, Passport, Driving Licence, Birth Certificate  any one is required");
                document.getElementById('any_one_required').innerHTML =
                    "NID, Passport, Driving Licence, Birth Certificate  any one is required!";
                return false;
            }

        }
    </script>
    <script>
        $('#nid').keyup(function() {
            var nid_length = $("#nid").val().length;
            if (nid_length != 10 && nid_length != 17) {
                $('#nid_error_message').html('NID must be 10 or 17  digits.')
            } else {
                $('#nid_error_message').html('')
            }
        });
        $('#passport').keyup(function() {
            var passport_length = $("#passport").val().length;
            if (passport_length != 9) {
                $('#passport_error_message').html('Passport must be 9 digits.')
            } else {
                $('#passport_error_message').html('')
            }
        });
    </script>
    <script>
        $('#divison').change(function() {
            var divison_id = $(this).val();
            $.ajax({
                type: "GET",
                url: "distic_list_by_divison_id",
                data: {
                    divison_id: divison_id
                },
                success: function(response) {
                    var html = '<option selected disabled>Select a distict Name</option>';
                    $.each(response, function(key, v) {
                        html += '<option value="' + v.id + '">' + v.name + '</option>'
                    });
                    $('#district').html(html);
                }
            });

        });
        $('#district').change(function() {
            var district_id = $(this).val();
            $.ajax({
                type: "GET",
                url: "thana_list_by_district_id",
                data: {
                    district_id: district_id
                },
                success: function(response) {
                    var html = '<option selected disabled>Select a thana Name</option>';
                    $.each(response, function(key, v) {
                        html += '<option value="' + v.id + '">' + v.name + '</option>'
                    });
                    $('#thana').html(html);
                }
            });

        });
    </script>
@endsection

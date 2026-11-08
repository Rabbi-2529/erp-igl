@extends('layouts.master')
@section('content')
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
            @if(session()->has('msg'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('msg2') )
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('msg2') }}
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
            <!-- /.box-header -->
            <!-- form start -->
            {{ Form::open([ 'action' => ['UserController@myBioUpdate_1_update', $user->au_id] , 'method' => 'post' ,'class' => 'form-horizontal', 'id' => 'user_registration_form', 'files' => true, 'onsubmit'=>'return validateForm()', 'name' => 'myForm' ]) }}

              <div class="box-body">

                <div style="border: 1px solid #ddd; padding:10px 0px; margin-bottom:10px;">

                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Father Name <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="f_name" value="{{ $user->f_name !=null ? $user->f_name:old('f_name') }}" class="form-control" id="inputEmail3" placeholder="Enter Father Name" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Mobile <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text"  name="f_mobile" autocomplete="off" value="{{ $user->f_mobile !=null ? $user->f_mobile:old('f_mobile') }}" class="form-control" id="f_mobile" placeholder="Enter Father Mobile Number">
                    <p class="text-danger" id="f_p_mobile_check" style="font-size: 12px"></p>
                    <p class="text-danger" id="f_mobile_error_message"></p>
                    <p class="text-danger" id="f_mobile_valid_check"></p>
                    <p class="text-success" id="f_mobile_available_message"></p>
                  </div>
                </div>
                <div class="form-group">
                <label class="col-sm-2 control-label" for="father_late">Late</label>
                  <div class="col-sm-6">
                    <input type="checkbox" value="the father has died" name="father_late" class="form-check-input" id="father_late">
                  </div>
                </div>

              </div>
              <div style="border: 1px solid #ddd; padding:10px 0px; margin-bottom:10px;">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Spouse Name (optional) <span style="color: red"></span></label>
                    <div class="col-sm-6">
                      <input type="text" name="spouse_name" value="{{ $user->spouse !=null ? $user->spouse:old('spouse_name') }}" class="form-control" id="inputEmail3" placeholder="Enter Spouse Name">
                    </div>
                </div>
              </div>
              <div style="border: 1px solid #ddd; padding:10px 0px; margin-bottom:10px;">

                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Mother Name <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="m_name" value="{{ $user->m_name !=null ? $user->m_name:old('m_name') }}" class="form-control" id="inputEmail3" placeholder="Enter Mother Name" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Mobile (optional)</label>
                  <div class="col-sm-6">
                    <input type="text"  name="m_mobile" autocomplete="off" value="{{ $user->m_mobile !=null ? $user->m_mobile:old('m_mobile') }}" class="form-control" id="m_mobile" placeholder="Enter Mother Mobile Number">
                    <p class="text-danger" id="f_m_mobile_check" style="font-size: 12px"></p>
                    <p class="text-danger" id="p_m_mobile_check" style="font-size: 12px"></p>
                    <p class="text-danger" id="m_mobile_error_message"></p>
                    <p class="text-danger" id="m_mobile_valid_check"></p>
                    <p class="text-success" id="m_mobile_available_message"></p>
                  </div>
                </div>
                <div class="form-group">
                <label class="col-sm-2 control-label" for="father_late">Late</label>
                  <div class="col-sm-6">
                    <input type="checkbox" name="mother_late" value="Mother Has died" class="form-check-input" id="father_late">
                  </div>
                </div>

              </div>
              <div style="border: 1px solid #ddd; padding:10px 0px; margin-bottom:10px;">

                <!--div class="form-group">
                  <label for="orginal_birthday" class="col-sm-2 control-label">Birthday as Orginal(dd/mm/yyyy) <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="orginal_birthday" value="{{ $user->orginal_birthday !=null ? $user->orginal_birthday:old('orginal_birthday') }}" class="form-control" id="orginal_birthday" placeholder="dd/mm/yyyy" required>
                  </div>
                </div-->
                <div class="form-group">
                  <label for="orginal_birthday" class="col-sm-2 control-label">Birthday as Orginal<span style="color: red">*</span></label>
                  <div class="col-sm-6">
                      <input type="text" name="orginal_birthday" value="{{ $user->orginal_birthday !=null ? $user->orginal_birthday:old('orginal_birthday') }}" class="form-control" id="orginal_birthday" placeholder="Enter Orginal Birthday.." required readonly>

                  </div>
                </div>
                <!-- Certificate date Update-->

                <div class="form-group">
                  <label for="orginal_birthday" class="col-sm-2 control-label">Birthday as certificate<span style="color: red">*</span></label>
                  <div class="col-sm-6">
                      <input type="text" name="cerfiticate_birthday" value="{{ $user->cerfiticate_birthday !=null ? $user->cerfiticate_birthday:old('cerfiticate_birthday') }}" class="form-control" id="cerfiticate_birthday" placeholder="Enter certificate Birthday.." required readonly>

                  </div>
                </div>
                <!--div class="form-group">
                  <label for="cerfiticate_birthday" class="col-sm-2 control-label">Birthday as NID(dd/mm/yyyy) <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="cerfiticate_birthday" value="{{ $user->cerfiticate_birthday !=null ? $user->cerfiticate_birthday:old('cerfiticate_birthday') }}" class="form-control" id="cerfiticate_birthday" placeholder="dd/mm/yyyy" required>
                  </div>
                </div-->

              </div>
              <div style="border: 1px solid #ddd; padding:10px 0px; margin-bottom:10px;">
                <p class="text-danger" style="font-size: 12px; margin-left:20%">Any one mendatory.</p>
                <div class="form-group">
                  <label for="nid" class="col-sm-2 control-label">NID</label>
                  <div class="col-sm-6">
                    <input type="text" name="nid" value="{{ $user->nid !=null ? $user->nid:old('nid') }}" class="form-control" id="nid" placeholder="Enter NID Number..">
                    <p class="text-danger" id="nid_error_message"></p>
                  </div>
                </div>

                <div class="form-group">
                  <label for="driving_licence" class="col-sm-2 control-label">Driving License</label>
                  <div class="col-sm-6">
                    <input type="text" name="driving_licence" value="{{ $user->driving_licence !=null ? $user->driving_licence:old('driving_licence') }}" class="form-control" id="driving_licence" placeholder="Enter Driving License..">
                  </div>
                </div>
                <div class="form-group">
                  <label for="passport" class="col-sm-2 control-label">Passport Number</label>
                  <div class="col-sm-6">
                    <input type="text" name="passport	" value="{{ $user->passport !=null ? $user->passport:old('passport') }}" class="form-control" id="passport" placeholder="Enter Passport Number..">
                    <p class="text-danger" id="passport_error_message"></p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="birth_certificate" class="col-sm-2 control-label">Birth Certificate </label>
                  <div class="col-sm-6">
                    <input type="text" name="birth_certificate" value="{{ $user->birth_certificate !=null ? $user->birth_certificate:old('birth_certificate') }}" class="form-control" id="birth_certificate" placeholder="Enter Birth Certificate..">
                  </div>
                </div>
                <div class="form-group">
                  <label for="birth_certificate" class="col-sm-2 control-label"></label>
                  <div class="col-sm-6">
                    <p class="text-danger" id="any_one_required" style="font-size: 12px"></p>
                  </div>
                </div>
              </div>
              <div style="border: 1px solid #ddd; padding:10px 0px; margin-bottom:10px;">


                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Present Address <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="au_address" value="{{ $user->au_address !=null ? $user->au_address:old('au_address') }}" class="form-control" id="inputEmail3" placeholder="Put Detail Address.." required>
                  </div>
                </div>

                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Permanent Address <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="au_parmament_address" value="{{ $user->au_parmament_address !=null ? $user->au_parmament_address:old('au_parmament_address') }}" class="form-control" id="inputEmail3" placeholder="Put Detail Address.." required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Nationality <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <select name="nationality" id="" class="form-control select2">
                      @php
                          $countries = DB::table('countries')->get();
                      @endphp
                      <option value="" selected disabled>Select one</option>
                      @foreach ($countries as $item)
                      <option value="{{ $item->nationality}}" {{ ($user->nationality == $item->nationality) ? 'selected':''  }} >{{ $item->nationality}}</option>
                      @endforeach

                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Division <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <select name="divison" id="divison" class="form-control select2">
                      @php
                          $divisions = DB::table('divisions')->get();
                      @endphp
                      <option value="" selected disabled>Select one</option>
                      @foreach ($divisions as $item)
                      <option value="{{ @$item->id}}" {{ ($user->divison == @$item->id) ? 'selected':''  }} >{{ @$item->name}}</option>
                      @endforeach

                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">District <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <select name="district" id="district" class="form-control select2">
                      @php
                          $districts = DB::table('districts')->where('id',$user->district)->first();

                     @endphp

                        @foreach($districts_name as $district)
                            <option value="{{ $district->id}}" {{ ($user->district == $district->id) ? 'selected':''  }}>{{ $district->name}}</option>
                        @endforeach



{{--                      <option value="{{ @$districts->id}}" {{ ($user->district == @$districts->id) ? 'selected':''  }}>{{ @$districts->name}}</option>--}}
                      {{-- <option value="" selected disabled>Select one</option> --}}
                      {{-- @foreach ($districts as $item)
                      <option value="{{ $item->name}}" {{ ($user->district == $item->name) ? 'selected':''  }}>{{ $item->name}}</option>
                      @endforeach --}}

                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Thana <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <select name="thana" id="thana" class="form-control select2">
                      @php
                      $upazilas = DB::table('upazilas')->where('id',$user->thana)->first();

                 @endphp
                        @foreach($thana_name as $upazilas)
                            <option value="{{ $upazilas->id}}" {{ ($user->thana == $upazilas->id) ? 'selected':''  }}>{{ $upazilas->name}}</option>
                        @endforeach

                      {{-- @php
                          $thana = DB::table('thanas')->get();
                      @endphp
                      <option value="" selected disabled>Select one</option>
                      @foreach ($thana as $item)
                      <option value="{{ $item->thana}}" {{ ($user->thana == $item->thana) ? 'selected':''  }}>{{ $item->thana}}</option>
                      @endforeach --}}

                    </select>
                  </div>
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
@endsection
@section('custom_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript">
  //////// Check Number Availability/////////
  /* Azizul hakim comment
  var error_mobile = true;
    $(document).ready(function(){
      $('#pmobileCheck').change(function(){
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

        var mobile_number_check = mobileno.substring(0,3);
        if(mobile_number_check != '019' && mobile_number_check != '017' && mobile_number_check != '013' && mobile_number_check != '018' && mobile_number_check != '016' && mobile_number_check != '015' && mobile_number_check != '014' && !=""){
          error_mobile = true;
          $('#p_mobile_valid_check').html('Mobile Number is not valid!');
          $('#mobile_available_message').html('');

        }
        else{
          $('#p_mobile_valid_check').html('');
              $('#mobile_available_message').html('');
        }


      });

      if ($("#pmobileCheck").val().length > 0)  {
        p_mobilenumerror();
      }

    });
    Azizul hakim comment */
  ////////////////////////////////////
  //////////////number check ////////////
  /* Azizul Hakim Comment
    $("#user_registration_form").submit(function () {
      if(error_mobile == true) {
        $("#pmobileCheck").focus();
        return false;
      } else {
        return true;
      }
    });



    $("#pmobileCheck").keyup(function(){
      p_mobilenumerror();
    });
  Azizul hakim comment */

  /* Azizul hakim Comment
    function p_mobilenumerror(){


      var mobile_num_length = $("#pmobileCheck").val().length;
      if( mobile_num_length<11 || mobile_num_length>11 ){

        error_mobile = true;

        $("#mobile_error_message").html("Mobile Number is not valid");
        $('#mobile_available_message').html('');

      }else{
        error_mobile = false;
        $("#mobile_error_message").html("");

      }


    }

    Azizul hakim Comment*/

</script>


{{-- check father mobile number  --}}
<script type="text/javascript">
  //////// Check Number Availability/////////

    var error_mobile = true;
     $(document).ready(function(){
      $('#f_mobile').change(function(){
        var mobileno = $(this).val();

        $.ajax({
          type: "GET",
          url: "mobileno_exist",
          data: { au_mobile: mobileno},
          success: function (result) {
            if (error_mobile == false){
            if(result == "true") {
              error_mobile = true;
              $('#f_mobile_error_message').html('Mobile Number Already Exists');
              $('#f_mobile_available_message').html('');
            } else {
              error_mobile = false;
              $('#f_mobile_error_message').html('');
              $('#f_mobile_available_message').html('Mobile Number Available');
            }
          }
        }
        });

        var mobile_number_check = mobileno.substring(0,3);
      if(mobile_number_check != '019' && mobile_number_check != '017' && mobile_number_check != '013' && mobile_number_check != '018' && mobile_number_check != '016' && mobile_number_check != '015' && mobile_number_check != '014'){
        error_mobile = true;
        $('#f_mobile_valid_check').html('Mobile Number is not valid!');
        $('#f_mobile_available_message').html('');

      }
      else{
        $('#f_mobile_valid_check').html('');
      }
      });

      if ($("#f_mobile").val().length > 0)  {
        f_mobilenumerror();
      }

     });



    $("#father_late").change(function(){
      var error_mobile = true;
      if(this.checked) {

         var new_f_m = $('#f_mobile').val('');
         $('#f_mobile').attr("type", "hidden");
         console.log(new_f_m)
    }else{
      var new_f_m = $('#f_mobile').val('');
         $('#f_mobile').attr("type", "text");
         console.log(new_f_m)
    }

    })

    $("#f_mobile").keyup(function(){
      f_mobilenumerror();
    });

    function f_mobilenumerror(){
      var mobile_num_length = $("#f_mobile").val().length;
      if( mobile_num_length<11 || mobile_num_length>11 ){
        /*$("#mobile_error_message_"+sl).html("Invalid Mobile Number");*/
        error_mobile = true;

        $("#f_mobile_error_message").html("Mobile Number is not valid");
        $('#f_mobile_available_message').html('');

      }else{
        error_mobile = false;
        $("#f_mobile_error_message").html("");

      }
    }

  </script>
  {{-- check mother mobile number  --}}
<script type="text/javascript">
  //////// Check Number Availability/////////
  var error_mobile = true;
     $(document).ready(function(){
      $('#m_mobile').change(function(){
        var mobileno = $(this).val();

          $.ajax({
            type: "GET",
            url: "mobileno_exist",
            data: { au_mobile: mobileno},
            success: function (result) {
              if (error_mobile == false){
              if(result == "true") {
                error_mobile = true;
                $('#m_mobile_error_message').html('Mobile Number Already Exists');
                $('#m_mobile_available_message').html('');
              } else {
                error_mobile = false;
                $('#m_mobile_error_message').html('');
                $('#m_mobile_available_message').html('Mobile Number Available');
              }
            }
          }
          });

        var mobile_number_check = mobileno.substring(0,3);
        if(mobile_number_check != '019' && mobile_number_check != '017' && mobile_number_check != '013' && mobile_number_check != '018' && mobile_number_check != '016' && mobile_number_check != '015' && mobile_number_check != '014'){
          error_mobile = true;
          $('#m_mobile_valid_check').html('Mobile Number is not valid!');
          $('#m_mobile_available_message').html('');

        }
        else{
          $('#m_mobile_valid_check').html('');
        }

      });

      if ($("#m_mobile").val().length > 0)  {
        mobilenumerror();
      }

     });
  ////////////////////////////////
  //////////number check ////////////
  /*
     $("#user_registration_form").submit(function () {
      if(error_mobile == true) {
        $("#m_mobile").focus();
        return false;
      } else {
        return true;
      }
    });
  */

    $("#m_mobile").keyup(function(){
      mobilenumerror();
    });


    function mobilenumerror(){
      var mobile_num_length = $("#m_mobile").val().length;
      if( mobile_num_length<11 || mobile_num_length>11 ){
        /*$("#mobile_error_message_"+sl).html("Invalid Mobile Number");*/
        error_mobile = true;

        $("#m_mobile_error_message").html("Mobile Number is not valid");
        $('#m_mobile_available_message').html('');

      }else{
        error_mobile = false;
        $("#m_mobile_error_message").html("");

      }
    }

  </script>
<script>
$(document).ready(function() {
    var currentYear = new Date().getFullYear(); // Get the current year
    var startYear = currentYear - 100; // Start year for the range (100 years ago)

    $("#orginal_birthday").datepicker({
        daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
        format: 'dd-MM-yyyy',
        startView: 2,  // Start view in "year" mode
        minViewMode: 2,  // Allows you to pick only the year
        viewMode: 'years',  // Show the year view
        todayBtn: "linked",  // Show today's button
        changeYear: true, // Allow year change by clicking
        changeMonth: true, // Allow month change
        yearRange: startYear + ":" + currentYear,  // Show the last 100 years
    });

    $("#cerfiticate_birthday").datepicker({
        daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
        format: 'dd-MM-yyyy',
        startView: 2,  // Start view in "year" mode
        minViewMode: 2,  // Allows you to pick only the year
        viewMode: 'years',  // Show the year view
        todayBtn: "linked",  // Show today's button
        changeYear: true, // Allow year change by clicking
        changeMonth: true, // Allow month change
        yearRange: startYear + ":" + currentYear,  // Show the last 100 years
    });
});

</script>
<!--script>
// $('#m_mobile').change(function(){
//   var f_mobile = $('#f_mobile').val();
//   var m_mobile = $('#m_mobile').val();
//   if(f_mobile == m_mobile){
//     $('#f_m_mobile_check').html('Father and Mother mobile number will not be same.')

//   }else{
//     $('#f_m_mobile_check').html('')
//   }

// });
$('#f_mobile').change(function(){
  var pmobileCheck = $('#pmobileCheck').val();
  var f_mobile = $('#f_mobile').val();
  if(f_mobile == pmobileCheck){
    $('#f_p_mobile_check').html('Persional and Father mobile number will not be same.')

  }else{
    $('#f_p_mobile_check').html('')
  }

});
// $('#m_mobile').change(function(){
//   var pmobileCheck = $('#pmobileCheck').val();
//   var m_mobile = $('#m_mobile').val();
//   if(m_mobile == pmobileCheck){
//     $('#p_m_mobile_check').html('Persional and Mother mobile number will not be same.')

//   }else{
//     $('#p_m_mobile_check').html('')
//   }

// });


  </script-->
  <script>
    function validateForm(){
      let nid = document.getElementById('nid').value;
      let passport = document.getElementById('passport').value;
      let driving_licence = document.getElementById('driving_licence').value;
      let birth_certificate = document.getElementById('birth_certificate').value;
      if (nid == "" && passport == "" && driving_licence == "" && birth_certificate == "") {
        // alert("NID, Passport, Driving Licence, Birth Certificate  any one is required");
        document.getElementById('any_one_required').innerHTML = "NID, Passport, Driving Licence, Birth Certificate  any one is required!";
        return false;
      }

    }
  </script>
  <script>
    $('#nid').keyup(function(){
      var nid_length = $("#nid").val().length;
      if( nid_length != 10 && nid_length != 17 ){
        $('#nid_error_message').html('NID must be 10 or 17  digits.')
      }else{
        $('#nid_error_message').html('')
      }
    });
    $('#passport').keyup(function(){
      var passport_length = $("#passport").val().length;
      if( passport_length != 9 ){
        $('#passport_error_message').html('Passport must be 9 digits.')
      }else{
        $('#passport_error_message').html('')
      }
    });
  </script>
  <script>
    $('#divison').change(function(){
      var divison_id = $(this).val();
      $.ajax({
          type: "GET",
          url: "distic_list_by_divison_id",
          data: { divison_id: divison_id},
          success: function (response) {
            var html = '<option selected disabled>Select a distict Name</option>';
                $.each(response,function(key,v){
                    html += '<option value="'+v.id+'">'+v.name+'</option>'
                });
                $('#district').html(html);
        }
        });

    });
    $('#district').change(function(){
      var district_id = $(this).val();
      $.ajax({
          type: "GET",
          url: "thana_list_by_district_id",
          data: { district_id: district_id},
          success: function (response) {
            var html = '<option selected disabled>Select a thana Name</option>';
                $.each(response,function(key,v){
                    html += '<option value="'+v.id+'">'+v.name+'</option>'
                });
                $('#thana').html(html);
        }
        });

    });
  </script>
@endsection

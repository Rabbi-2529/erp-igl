@extends('layout.master')
@section('user_register_class','menu-open')
@section('user_register_user_reg','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Created Today
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">User List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Team Name</th>
                  <th>Name</th>
                  <th>Mobile</th>
                  <th>Email</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach($users as $user)
                <tr>
                  <td>{{ ++$sl }}</td>

                  <td>{{ $user->team_info['tl_team_name'] }}</td>
                  <td>{{ $user->au_name }}</td>
                  <td>{{ $user->au_mobile }}</td>
                  <td>{{ $user->au_email }}</td>
                  <td align="center"><a href="{{ url('/user_profile',['id' => $user->au_id]) }}" class="btn btn-info btn-sm">SHOW</a></td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">User Registration</h3>
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
            {{ Form::open([ 'action' => 'UserController@user_register_insert' , 'method' => 'post' ,'class' => 'form-horizontal', 'id' => 'user_registration_form', 'files' => true ]) }}

              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Name</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_name" value="{{ old('au_name') }}" class="form-control" id="inputEmail3" placeholder="Enter Authorized Name" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Email</label>
                  <div class="col-sm-6">
                    <input type="email" name="au_email" value="{{ old('au_email') }}" class="form-control" id="inputEmail3" placeholder="Place Email..." required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Mobile</label>
                  <div class="col-sm-6">
                    <input type="text"  name="au_mobile" autocomplete="off" value="{{ old('au_mobile') }}" class="form-control" id="mobilenum" placeholder="Enter Mobile Number" required>
                        <p class="text-danger" id="mobile_error_message"></p>
                        <p class="text-success" id="mobile_available_message"></p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Password</label>
                  <div class="col-sm-6">
                    <input type="password" name="au_password" class="form-control" id="inputPassword3" placeholder="Create Password..." required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="au_user_id" class="col-sm-2 control-label">Employee Id</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_user_id" class="form-control" id="au_user_id" value="{{ old('au_user_id') }}" placeholder="Employee Id..." required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Address</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_address" value="{{ old('au_address') }}" class="form-control" id="inputEmail3" placeholder="Put Detail Address.." required>
                  </div>
                </div>
                <div class="form-group" id="select_div">
                  <label for="inputEmail3" class="col-sm-2 control-label">Department</label>
                  <div class="col-sm-6">
                  <select name="au_department[]" class="form-control " id="" required multiple>
                      <option value="" disabled>Select</option>
                      @foreach($depts as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                      @endforeach
                  </select>
                </div>

              </div>
                <div class="form-group" id="select_div">
                  <label for="inputEmail3" class="col-sm-2 control-label">Shift</label>
                  <div class="col-sm-6">
                  <select name="au_shifts" class="form-control " id="" required>
                      <option value="" selected disabled>Select</option>
                      @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                      @endforeach
                  </select>
                </div>

              </div>
              <div class="form-group">
                <label for="userType" class="col-sm-2 control-label">Job Type</label>
                <div class="col-sm-6">
                  <input type="radio" id="job_type_h" name="job_type" value="work from home">
                  <label for="job_type_h" class="mr-2">Work From Home</label>
                  <input type="radio" id="job_type_o" name="job_type" value="work from office">
                  <label for="job_type_o">Work From Office</label>
                </div>
              </div>
                <div class="form-group">
                  <label for="userType" class="col-sm-2 control-label">User Type</label>
                  <div class="col-sm-6">
                    <input type="radio" id="au_user_type_user" name="au_user_type" onchange="show_terget(this.value)" value="6">
                    <label for="au_user_type_user">User</label>
                    <input type="radio" id="au_user_type_team_leader" name="au_user_type" onchange="show_terget(this.value)" value="5">
                    <label for="au_user_type_team_leader">Team Leader</label>
                    <input type="radio" id="au_user_type_team_leader" name="au_user_type" value="7">
                    <label for="au_user_type_team_leader">Head of Sales</label>
                  </div>
                </div>

                <div class="form-group" id="user" style="display: none;">
                  <label for="inputEmail3" class="col-sm-2 control-label">Team Name</label>
                  <div class="col-sm-6">
                    <select class="form-control" name="au_team_id_user">
                      <option>Select</option>
                      @foreach($teams as $team)
                        @if(\App\Models\Sds_team_name::is_team_leader_exists($team->tl_sl_id) === true)
                          <option value="{{ $team->tl_sl_id }}">{{ $team->tl_team_name }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group" id="team_leader" style="display: none;">
                  <label for="inputEmail3" class="col-sm-2 control-label">Team Leader</label>
                  <div class="col-sm-6">
                    <select class="form-control" name="au_team_id_team">
                      <option value="">Select</option>
                      @foreach($teams as $team)
                        @if(\App\Models\Sds_team_name::is_team_leader_no_exists($team->tl_sl_id) === false)
                          <option value="{{ $team->tl_slid }}">{{ $team->tl_team_name }}</option>
                        @endif

                      @endforeach
                    </select>
                  </div>
                </div>

                
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Image</label>
                  <div class="col-sm-6">
                    <input type="file" name="au_company_img" class="form-control" id="inputEmail3" placeholder="">
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" id="user_register_submit" class="btn btn-info pull-right">Create</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{ Form::close() }}
          </div>
         </section>
@endsection
@section('custom_script')
<script type="text/javascript">
//////// Check Number Availability/////////
var error_mobile = true;
   $(document).ready(function(){
    $('#mobilenum').keyup(function(){
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

    if ($("#mobilenum").val().length > 0)  {
      mobilenumerror();
    }

   });
////////////////////////////////////
//////////////number check ////////////
   $("#user_registration_form").submit(function () {
    if(error_mobile == true) {
      $("#mobilenum").focus();
      return false;
    } else {
      return true;
    }
  });


  $("#mobilenum").keyup(function(){
    mobilenumerror();
  });
  function mobilenumerror(){
    var mobile_num_length = $("#mobilenum").val().length;
    if( mobile_num_length<11 || mobile_num_length>11 ){
      /*$("#mobile_error_message_"+sl).html("Invalid Mobile Number");*/
      error_mobile = true;

      $("#mobile_error_message").html("Invalid Mobile Number");
      $('#mobile_available_message').html('');

    }else{
      error_mobile = false;
      $("#mobile_error_message").html("");

    }
  }

</script>
@endsection

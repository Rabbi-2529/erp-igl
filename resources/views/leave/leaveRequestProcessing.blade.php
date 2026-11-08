@extends('layout.master')
@section('payroll_class','menu-open')
@section('leave','menu-open')
@section('leave_request','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    @include('layout.session_message')
        @if(session()->has('update'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('update') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
          <h1>
            Leave Request Details
          </h1>
        </section>

        <!-- /.box-header -->
            <div class="row">
                <div class="col-md-8">
                    <div class="box" style="margin-top: 20px">

                    <div class="box-header">
                    <h3 class="box-title">Leave Request Processing</h3>
                    </div>
                    <div class="box-body" >
                        <table id="example1" class="table table-bordered table-striped">

                            <tbody>
                                <tr>
                                    <th style="width: 30%">User Name:</th>
                                    <td>{{ App\Models\Admin_user::attendanceUser($leaveDetails->user_id)}}</td>
                                </tr>
                                <tr>
                                    <th>User Id:</th>
                                    <td>{{ $leaveDetails->au_user_id }}</td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td>{{ @App\Models\Admin_user::userDepartment($leaveDetails->user_id)}}</td>
                                </tr>
                                <tr>
                                    <th>Title:</th>
                                    <td>{{ $leaveDetails->leave_title }}</td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td>{{ @explode(',',$leaveDetails->leave_date)[0] }} To {{ @explode(',',$leaveDetails->leave_date)[1] }}</td>
                                </tr>
                                <tr>
                                    <th>Total Day:</th>
                                    <td>{{ $leaveDetails->total_day }} Days</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if ($leaveDetails->status == 0)
                                            <span style="color: rgb(15, 154, 235)">Pending</span>
                                        @elseif($leaveDetails->status == 1)
                                            <span style="color: rgb(167, 150, 2)">Team Leader Accepted</span>
                                            @elseif($leaveDetails->status == 2)
                                            <span style="color: rgb(167, 150, 2)">Department Head Accepted</span>
                                        @elseif($leaveDetails->status == 3)
                                            <span style="color: rgb(7, 129, 2)">Accepted</span>
                                        @else
                                        <span style="color: red">Rejected</span>
                                        @endif

                                    </td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>
                                        <p>
                                            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                                              Show
                                            </button>
                                          </p>
                                          <div class="collapse" id="collapseExample">
                                            <div class="card card-body">
                                                {!! $leaveDetails->user_desc !!}
                                            </div>
                                          </div>
                                    </td>
                                </tr>
                                @if ($leaveDetails->file)
                                <tr>
                                    <th>Attached File:</th>
                                    <td>
                                        <img src="{{ asset('/asset/leavefiles/'.$leaveDetails->file) }}" width="100%" alt="">
                                    </td>
                                </tr>
                                @endif


                            </tbody>
                        </table>
                    </div>
                    <!-- /.box-body -->
                    </div>
                    <div class="box">
                        <div class="box-header">
                        <h3 class="box-title">Application accepting</h3>
                        </div>
                        <div class="box-body" >
                            {{Form::open(['route' => ['leaveAccepting',$leaveDetails->id], 'id' => 'form' , 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form', 'enctype' => 'multipart/form-data'])}}
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="inputEmail3" class="col-sm-2 control-label"> Date
                                            <b class="text-danger">*</b>
                                        </label>
                                    </div>
                                    <div class="col-md-3 col-lg-2">
                                        <label for="">Start Date*</label>
                                        <input type="text" placeholder="Start Date" name="date_1" value="{{ @explode(',',$leaveDetails->leave_date)[0] }}" class="form-control" autocomplete="off" id="startDate" data-date-format="yyyy-mm-dd" required>
                                    </div>
                                    <div class="col-md-2 col-lg-1">
                                        <label for="">Days*</label>
                                        <input type="text" name="total_day" value="{{ $leaveDetails->total_day }}" onkeyup="selectEndDate(this.value)" class="form-control" autocomplete="off" id="totalDay" placeholder="Day" data-date-format="yyyy-mm-dd" required>
                                    </div>
                                    <div class="col-sm-3 col-lg-2">
                                        <label for="">End Date*</label>
                                        <input type="text" placeholder="End Date" name="date_2" value="{{ @explode(',',$leaveDetails->leave_date)[1] }}" class="form-control" autocomplete="off" data-date-format="yyyy-mm-dd" id="endDate" readonly>
                                      </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="user_desc" class="col-sm-2 control-label"> Description
                                            <b class="text-danger">*</b>
                                        </label>
                                    </div>
                                    <div class="col-md-8 col-lg-6">
                                      <textarea name="desc" id="textEditor" class="form-control" cols="50" rows="4" required>{!! $leaveDetails->user_desc !!}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label for="userType" class="col-sm-2 control-label">Action</label>
                                </div>

                                <div class="col-sm-6">
                                  <input type="radio" id="job_type_h" name="status" value="accept">
                                  <label for="job_type_h" class="mr-2">Accept</label>
                                  <input type="radio" id="job_type_o" name="status" value="reject">
                                  <label for="job_type_o">Reject</label>
                                </div>
                            </div>
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-sm-10">

                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" id="" class="btn btn-info pull-right">Submit</button>
                                     </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box" style="margin-top: 20px">
                        @if (Auth::user()->au_user_type == 4)
                        <div class="box-header">
                            <h5 class="">Team Leader Message</h5>
                        </div>
                        <div class="box-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>T.L Name:</th>
                                        <td>{{ @App\Models\Admin_user::teamLeaderNameById($leaveDetails->user_id)}}</td>
                                    </tr>
                                    <tr>
                                        <th>T.L Id:</th>
                                        <td>{{ @App\Models\Admin_user::teamLeaderIdById($leaveDetails->user_id) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @if ($leaveDetails->desc_1)
                                 <p style="margin-top: 20px">{{ $leaveDetails->desc_1 }}</p>
                            @else
                            <p style="margin-top: 20px">No message yet.</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="box" style="margin-top: 20px">
                        @if (Auth::user()->au_user_type == 4)
                        <div class="box-header">
                            <h5 class="">Department Head Message</h5>
                        </div>
                        <div class="box-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>D.H Name:</th>
                                        <td>{{ @App\Models\Admin_user::departmentHeadNameById($leaveDetails->user_id)}}</td>
                                    </tr>
                                    <tr>
                                        <th>D.H Id:</th>
                                        <td>{{ @App\Models\Admin_user::departmentHeadIdById($leaveDetails->user_id) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @if ($leaveDetails->desc_2)
                                 <p style="margin-top: 20px">{{ $leaveDetails->desc_2 }}</p>
                            @else
                            <p style="margin-top: 20px">No message yet.</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="box" style="margin-top: 20px">
                        @if (Auth::user()->au_user_type == 4)
                        <div class="box-header">
                            <h5 class="">HR Admin Message</h5>
                        </div>
                        <div class="box-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>HR/Admin Name:</th>
                                        <td>{{ @App\Models\Admin_user::hrAdminNameById($leaveDetails->user_id)}}</td>
                                    </tr>
                                    <tr>
                                        <th>HR/Admin Id:</th>
                                        <td>{{ @App\Models\Admin_user::hrAdminIdById($leaveDetails->user_id) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @if ($leaveDetails->desc_3)
                                 <p style="margin-top: 20px">{{ $leaveDetails->desc_3 }}</p>
                            @else
                            <p style="margin-top: 20px">No message yet.</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

    </section>


@endsection
@section('custom_style')
<style>
    .action_btn{
        border: 1px solid;
        padding: 5px;
    }
    .btn_show{
        background-color: green;
        color: white;
    }
    .btn_delete{
        background-color: red;
        color: white;
    }
    .btn_edit{
        background-color: cornflowerblue;
        color: white;
    }
</style>
@endsection
@section('custom_script')
{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> --}}
{{-- <script src="http://code.jquery.com/jquery-1.9.1.js"></script> --}}
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function(){

    $( "#startDate" ).datepicker({
          daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });

    });



</script>
<script>
    function selectEndDate(value) {
      var day = $("#startDate").val();
      $.ajax({
        type: "GET",
        url: "{{ route('leave.end-date-ajax') }}",
        data: {value:value, day:day},
        success:function(response){
          $("#endDate").val(response);
          if(response == null){

          }
        }
      })
    }
  </script>
<script>
    $('#textEditor').richText();

</script>
@endsection


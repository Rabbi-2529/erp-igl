@extends('layouts.master')

@section('title', 'Process Request')

@section('human_resource_class', 'active')
@section('leave', 'active')
@section('leave_request', 'active')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Leave Request Processing</h4>
                    </div>
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

                @if(session()->has('update'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('update') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @include('layout.session_message')

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="box" style="margin-top: 20px">
                                <div class="box-body">
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
                                                <th>Requested Leave:</th>
                                                <td>{{ @explode(',',$leaveDetails->leave_date)[0] }} To {{ @explode(',',$leaveDetails->leave_date)[1] }}</td>
                                            </tr>
                                            <tr>
                                                <th>Total Leave Request:</th>
                                                <td>{{ $leaveDetails->total_day_request }} Days</td>
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
                            </div>
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Application Accepting</h3>
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
                                                <input type="text" 
                                                       placeholder="Start Date" 
                                                       name="date_1" 
                                                       value="{{ $leaveDetails->leave_date_accept_start ? $leaveDetails->leave_date_accept_start : explode(',',  $leaveDetails->leave_date)[0] }}"
                                                       class="form-control" 
                                                       autocomplete="off" 
                                                       id="startDate" 
                                                       required>
                                            </div>

                                            <div class="col-md-2 col-lg-1">
                                                <label for="">Days*</label>
                                                <input type="text" 
                                                       name="total_day_accepted" 
                                                       value="{{ $leaveDetails->total_day_accepted ? $leaveDetails->total_day_accepted : $leaveDetails->total_day_request }}" 
                                                       onkeyup="selectEndDate(this.value)" 
                                                       class="form-control" 
                                                       autocomplete="off" 
                                                       id="totalDay" 
                                                       placeholder="Day" 
                                                       required>
                                            </div>

                                            <div class="col-md-3 col-lg-2">
                                                <label for="">End Date*</label>
                                                <input type="text"
                                                       placeholder="End Date" 
                                                       name="date_2" 
                                                       value="{{ $leaveDetails->leave_date_accept_end ? $leaveDetails->leave_date_accept_end : explode(',',  $leaveDetails->leave_date)[1] }}"
                                                       class="form-control"
                                                       autocomplete="off" 
                                                       id="endDate" 
                                                       data-date-format="yyyy-mm-dd" 
                                                       required readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="user_desc" class="col-sm-2 control-label">Note</label>
                                            </div>
                                            <div class="col-md-8 col-lg-6">
                                                @if ($leaveDetails->desc_3)
                                                    <textarea name="desc" id="textEditor" class="form-control" cols="50" rows="4" required>{!! $leaveDetails->desc_3 !!}</textarea>
                                                @elseif ($leaveDetails->desc_2)
                                                    <textarea name="desc" id="textEditor" class="form-control" cols="50" rows="4" required>{!! $leaveDetails->desc_2 !!}</textarea>
                                                @elseif ($leaveDetails->desc_1)
                                                    <textarea name="desc" id="textEditor" class="form-control" cols="50" rows="4" required>{!! $leaveDetails->desc_1 !!}</textarea>
                                                @else
                                                    <textarea name="desc" id="textEditor" class="form-control" cols="50" rows="4" placeholder="Write a note." required></textarea>
                                                @endif
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
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
                                    </div>

                                    <button type="submit" id="" class="btn btn-info pull-right">Submit</button>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box" style="margin-top: 20px">
                                @if (Auth::user()->au_user_type == 4)
                                <div class="box-header">
                                    <h5 class="">Team Leader Section</h5>
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
                                            <tr>
                                                <th>T.L Note:</th>
                                                <td>
                                                    @if ($leaveDetails->desc_1)
                                                        <p>{{ $leaveDetails->desc_1 }}</p>
                                                    @else
                                                    <p>No note yet!</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                            <div class="box" style="margin-top: 20px">
                                @if (Auth::user()->au_user_type == 4)
                                <div class="box-header">
                                    <h5 class="">Department Head Section</h5>
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
                                            <tr>
                                                <th>D.H Note:</th>
                                                <td>
                                                    @if ($leaveDetails->desc_2)
                                                        <p>{{ $leaveDetails->desc_2 }}</p>
                                                    @else
                                                    <p>No note yet!</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                            <div class="box" style="margin-top: 20px">
                                @if (Auth::user()->au_user_type == 4)
                                <div class="box-header">
                                    <h5 class="">HR Admin Section</h5>
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
                                            <tr>
                                                <th>HR/Admin Note:</th>
                                                <td>
                                                    @if ($leaveDetails->desc_3)
                                                        <p>{{ $leaveDetails->desc_3 }}</p>
                                                    @else
                                                    <p>No note yet!</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .action_btn {
            border: 1px solid;
            padding: 5px;
        }

        .btn_show {
            background-color: green;
            color: white;
        }

        .btn_delete {
            background-color: red;
            color: white;
        }

        .btn_edit {
            background-color: cornflowerblue;
            color: white;
        }
    </style>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>

    

    <script>
        $(document).ready(function () {
            $("#startDate").datepicker({
                daysOfWeekHighlighted: "7",
                todayHighlight: true,
                autoclose: true
            });
        });
    </script>
    
    
    <script>
        function selectEndDate(value) {
            var day = $("#startDate").val();
            $.ajax({
                type: "GET",
                url: "{{ route('leave.end-date-ajax') }}",
                data: {value: value, day: day},
                success: function (response) {
                    $("#endDate").val(response);
                    if (response == null) {

                    }
                }
            })
        }
    </script>

    <script>
        $('#textEditor').richText();
    </script>

@endsection

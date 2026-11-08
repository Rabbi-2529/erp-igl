@extends('layout.master')
@section('payroll_class','menu-open')
@section('leave','menu-open')
@section('leave_request','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('del'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('del') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
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
          <h1>
            Leave Management
          </h1>
        </section>
        <div class="box">
          <div class="row">
            <div class="col-md-8">
              <div class="box-header">
                <h3 class="box-title">All Leave Request</h3>
              </div>
            </div>
            <div class="col-md-4">
              @if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                {{Form::open(['route' => 'leave.leaveRequestSearchByStatus', 'id' => 'form' , 'method' => 'get' , 'class' => 'form-horizontal', 'id' => 'customer_create_form', 'enctype' => 'multipart/form-data'])}}
                <div class="form-group">
                  <select name="status" id="leaveRequestSearchByStatus" class="form-control">
                    <option value="" selected disabled>Select for search</option>
                    <option value="0">Pending</option>
                    <option value="1">Team Leader Accepted</option>
                    <option value="2">Department Head Accepted</option>
                    <option value="3">Admin Accepted</option>
                    <option value="5">Rejected</option>
                  </select>
              </div>
              <button type="submit" id="" class="btn btn-info pull-right">Search</button>
              {{ Form::close() }}
              @endif
            </div>
          </div>


                <!-- /.box-header -->
                <div class="box-body">
                  <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>SL</th>
                        <th>User Name</th>
                        <th>Department</th>
                        <th>T.L Name</th>
                        <th>Request Date</th>
                        <th>Accepted Date</th>
                        <th>Total Day</th>
                        <th>Status</th>
                        @if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                        <th class="text-center">Action</th>
                        @endif



                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaveData as $key=> $item)
                        @if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5 && $item->status == 0)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ @App\Models\Admin_user::attendanceUser($item->user_id) }}</td>
                            <td>{{ @App\Models\Admin_user::userDepartment($item->user_id) }}</td>
                            <td>{{ @App\Models\Admin_user::teamLeaderNameById($item->user_id) }}</td>
                            <td>{{ @explode(',',$item->leave_date)[0] }} To {{ @explode(',',$item->leave_date)[1] }}</td>
                            <td>
                              @if ($item->leave_date_accept != null)
                              {{ @explode(',',$item->leave_date_accept)[0] }} To {{ @explode(',',$item->leave_date_accept)[1] }}
                              @else
                              Pending
                              @endif
                            </td>
                            <td>{{ $item->total_day }} Days</td>
                            <td>
                                @if ($item->status == 0)
                                    <span style="color: rgb(15, 154, 235)">Pending</span>
                                @elseif($item->status == 1)
                                    <span style="color: rgb(167, 150, 2)">Team Leader Accepted</span>
                                    @elseif($item->status == 2)
                                    <span style="color: rgb(167, 150, 2)">Department Head Accepted</span>
                                @elseif($item->status == 3)
                                    <span style="color: rgb(7, 129, 2)">Accepted</span>
                                @else
                                <span style="color: red">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('leaveRequestProcess',$item->id) }}" class="action_btn btn_edit">
                                Show
                              </a>
                            </td>

                        </tr>
                        @endif
                        @if (Auth::user()->au_user_type == 6 && Auth::user()->au_id == $item->user_id)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ @App\Models\Admin_user::attendanceUser($item->user_id) }}</td>
                            <td>{{ @App\Models\Admin_user::userDepartment($item->user_id) }}</td>
                            <td>{{ @App\Models\Admin_user::teamLeaderNameById($item->user_id) }}</td>
                            <td>{{ @explode(',',$item->leave_date)[0] }} To {{ @explode(',',$item->leave_date)[1] }}</td>
                            <td>
                              @if ($item->leave_date_accept != null)
                              {{ @explode(',',$item->leave_date_accept)[0] }} To {{ @explode(',',$item->leave_date_accept)[1] }}
                              @else
                              Pending
                              @endif
                            </td>
                            <td>{{ $item->total_day }} Days</td>
                            <td>
                                @if ($item->status == 0)
                                    <span style="color: rgb(15, 154, 235)">Pending</span>
                                @elseif($item->status == 1)
                                    <span style="color: rgb(167, 150, 2)">Team Leader Accepted</span>
                                @elseif($item->status == 3)
                                    <span style="color: rgb(7, 129, 2)">Accepted</span>
                                @else
                                <span style="color: red">Rejected</span>
                                @endif
                            </td>

                        </tr>
                        @endif
                        @endforeach

                    </tbody>
                  </table>
                </div>
                <!-- /.box-body -->
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



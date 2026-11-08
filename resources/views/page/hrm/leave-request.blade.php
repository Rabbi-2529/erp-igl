@extends('layouts.master')

@section('title', 'Leave Request')

@section('human_resource_class', 'active')
@section('leave', 'active')
@section('leave_request', 'active')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>All Leave Request</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="box">
                        <div class="box-body">
                            <div class="col-md-9">
                                {{--  --}}
                            </div>
                            <div class="col-md-3">
                                @if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                                    {{ Form::open(['route' => 'leave.leaveRequestSearchByStatus', 'id' => 'form', 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'customer_create_form', 'enctype' => 'multipart/form-data']) }}
                                    <div class="form-group d-flex mb-0">
                                        <select name="status" id="leaveRequestSearchByStatus" class="form-control">
                                            <option value="" selected disabled>Search by Status</option>
                                            <option value="0">Pending</option>
                                            <option value="1">Team Leader Accepted</option>
                                            <option value="2">Department Head Accepted</option>
                                            <option value="3">Admin Accepted</option>
                                            <option value="5">Rejected</option>
                                        </select>
                                        <button type="submit" class="btn btn-info mr-auto">Search</button>
                                    </div>
                                    {{ Form::close() }}
                                @endif
                            </div>
                        </div>
                        
                        <div class="box-body">
                            <table class="table table-bordered table-striped table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>User Name</th>
                                        <th>Department</th>
                                        <th>T.L Name</th>
                                        <th style="width: 15%">Request Date</th>
                                        <th>Requested</th>
                                        <th>Accepted Date</th>
                                        <th>Accepted</th>
                                        <th>Status</th>
                                        @if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($leaveData as $key => $item)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            {{-- User Name --}}
                                            <td>{{ @App\Models\Admin_user::attendanceUser($item->user_id) }}</td>
                                            {{-- Department --}}
                                            <td>{{ @App\Models\Admin_user::userDepartment($item->user_id) }}</td>
                                            {{-- T.L Name --}}
                                            <td>{{ @App\Models\Admin_user::teamLeaderNameById($item->user_id) }}</td>
                                            {{-- Request Date --}}
                                            <td>{{ @explode(',', $item->leave_date)[0] }} To
                                                {{ @explode(',', $item->leave_date)[1] }}</td>

                                            {{-- Request Day --}}
                                            <td>{{ $item->total_day_request }} Days</td>
                                            
                                            {{-- Accepted Date --}}
                                            <td>
                                                @if ($item->status == "3")
                                                    {{ $item->leave_date_accept_start }} To {{ $item->leave_date_accept_end }}
                                                @else
                                                    <span class="badge badge-info">Pending</span>
                                                @endif
                                            </td>                                            

                                            {{-- Accepted Day --}}

                                            <td>
                                                @if ($item->total_day_accepted != null)
                                                    {{ $item->total_day_accepted }} Days
                                                @else
                                                    <span class="badge badge-info">Pending</span>
                                                @endif
                                            </td>
                                            
                                            {{-- Action --}}
                                            <td>
                                                @if ($item->status == 0)
                                                    <span class="badge badge-info">Pending</span>
                                                @elseif($item->status == 1)
                                                    <span class="badge badge-warning">Team Leader Accepted</span>
                                                @elseif($item->status == 2)
                                                    <span class="badge badge-warning">Department Head Accepted</span>
                                                @elseif($item->status == 3)
                                                    <span class="badge badge-success">Accepted</span>
                                                @else
                                                    <span class="badge badge-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                                                    <a href="{{ route('leaveRequestProcess', $item->id) }}"
                                                        class="btn-action btn-edit">
                                                        Show
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        .panel {
            margin-top: 20px;
        }
    
        .panel-title h4 {
            margin: 0;
        }
    
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
    
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
    
        .table th {
            background-color: #f2f2f2;
        }
    
        .btn-action {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 4px;
        }
    
        .btn-show {
            background-color: #5cb85c;
            color: #fff;
        }
    
        .btn-delete {
            background-color: #d9534f;
            color: #fff;
        }
    
        .btn-edit {
            background-color: #5bc0de;
            color: #fff;
        }

    </style>


@endsection

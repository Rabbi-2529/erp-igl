@extends('layout.master')
@section('user_register_class','menu-open')
@section('user_register_agm','active')
@section('content')
<section class="content">
<section class="content-header">
    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible show" role="alert">
        <strong>{{ session('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif
    
      <h1>
        Team Leaders
      </h1>
    </section>
	    <div class="box">
          <div class="box-header">
            <h3 class="box-title">Leader List</h3>
          </div>
          <div class="box-body">
            <form action="{{ route('team_list_submit',$admin_user->au_id)}}" method="POST" class="form-horizontal">
              @csrf
              <button type="submit" class="btn btn-success btn-sm" style="float: right;">Save</button>
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>User ID</th>
                  <th>Name</th>
                  <th>Mobile</th>
                  <th>Email</th>
                  <th>Team Name</th>
                  <th>Members</th>
                  <th>Customers</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php
                ($sl=0)
                @endphp
                
                @foreach($team_leaders as $team_leader)
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $team_leader->au_user_id }}</td>
                  <td>{{ $team_leader->au_name }}</td>
                  <td>{{ $team_leader->au_mobile }}</td>
                  <td>{{ $team_leader->au_email }}</td>
                  <td>{{ $team_leader->team_info['tl_team_name'] }}</td>
                  <td align="center">
                  <a href="{{ url('/members_list',['id' => $team_leader->au_team_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ App\Admin_user::team_member($team_leader->au_team_id, $team_leader->au_company_id)->count() }}
                  </a>
                  </td>

                  <td align="center">

                  <a href ="{{ url('user/customer_list',['id' => $team_leader->au_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ App\Sds_query_book::user_customer($team_leader->au_id, $team_leader->au_company_id)->count() }}
                  </a>

                  </td>
                  <td align="center">
                    <input type="checkbox" {{ ($team_leader->au_team_id == in_array($team_leader->au_team_id,$abc))? 'checked' : '' }} data-toggle="toggle" value="{{ $team_leader->au_team_id }}" data-size="mini" name="team[]">
                  </td>
                </tr>
                
                @endforeach
                </tbody>
              </table
            </form>
          </div>
        </div>
         @endsection
         @section('custom_style')
         <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
         @endsection
         @section('custom_script')
         <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
         @endsection

@extends('layout.master')
@section('agm_class','menu-open')
@section('agm_team_list','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Team Leaders
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Leader List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
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
                    <div class="btn-group">
                      <button type="button" class="btn btn-info">Action</button>
                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                        <li>
                        <a href="{{ url('/user_profile',['id' => $team_leader->au_id]) }}">Show
                        </a>
                        </li>

                        <li class="divider"></li>

                        <li>
                        <a href="{{ url('/user_edit',['id' => $team_leader->au_id]) }}">Update
                        </a>
                        </li>

                        <li class="divider"></li>
                        @if( $team_leader->au_status == 1 )
                        <li>
                        <a href="{{ route('pages.team_leader_suspend',['id' => $team_leader->au_id]) }}">Suspend Leader
                        </a>
                        </li>
                        @elseif( $team_leader->au_status == 0 )
                        <li>
                        <a href="{{ route('pages.team_leader_active',['id' => $team_leader->au_id]) }}">Active Leader
                        </a>
                        </li>
                        @endif
                       
                      </ul>
                </div>
                  </td>
                </tr>
                @endforeach
                </tbody>
              </table>

            </div>
            <!-- /.box-body -->
          </div>
         @endsection

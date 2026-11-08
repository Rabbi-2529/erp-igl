@extends('layout.master')
@section('user_register_class','menu-open')
@section('user_register_agm','active')
@section('content')
<section class="content">
<section class="content-header">
  @if (session()->has('suspend'))
  <div class="alert alert-danger alert-dismissible show" role="alert">
    <strong>{{ session('suspend') }}</strong>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  @endif
  @if (session()->has('active'))
  <div class="alert alert-success alert-dismissible show" role="alert">
    <strong>{{ session('active') }}</strong>
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
                  {{-- <th>Team Name</th> --}}
                  {{-- <th>Members</th>
                  <th>Customers</th> --}}
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php
                ($sl=0)
                @endphp
                @foreach($agm_list as $agm)
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $agm->au_user_id }}</td>
                  <td>{{ $agm->au_name }}</td>
                  <td>{{ $agm->au_mobile }}</td>
                  <td>{{ $agm->au_email }}</td>
                  {{-- <td>{{ $agm->team_info['tl_team_name'] }}</td> --}}
                  {{-- <td align="center">
                  <a href="{{ url('/members_list',['id' => $agm->au_team_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ App\Admin_user::team_member($agm->au_team_id, $agm->au_company_id)->count() }}
                  </a>
                  </td>

                  <td align="center">

                  <a href ="{{ url('user/customer_list',['id' => $agm->au_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ App\Sds_query_book::user_customer($agm->au_id, $agm->au_company_id)->count() }}
                  </a>

                  </td> --}}
                  <td align="center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-info">Action</button>
                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                        <li>
                        <a href="{{ url('/user_profile',['id' => $agm->au_id]) }}">Show
                        </a>
                        </li>

                        <li class="divider"></li>

                        <li>
                        <a href="{{ url('/user_edit',['id' => $agm->au_id]) }}">Update
                        </a>
                        </li>

                        <li class="divider"></li>
                        @if( $agm->au_status == 1 )
                        <li>
                        <a href="{{ route('hos-suspend',['id' => $agm->au_id]) }}">Suspend AGM
                        </a>
                        </li>
                        @elseif( $agm->au_status == 0 )
                        <li>
                        <a href="{{ route('hos-active',['id' => $agm->au_id]) }}">Active AGM
                        </a>
                        </li>
                        @endif
                        <li class="divider"></li>
                        <li><a href="{{ url('/team_list',['id' => $agm->au_id])}}">Teams</a></li>
                        <li class="divider"></li>
                        <li><a href="{{ route('permission',['id' => $agm->au_id]) }}">Permission</a></li>
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

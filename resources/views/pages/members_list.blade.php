@extends('layouts.master')
@section('title', 'Manage Team')

@section('human_resource_class','active')
@section('hrm_class','active')
@section('team_manage_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Team Members
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Team Members List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Name</th>
                  <th>Mobile</th>
                  <th>Customers</th>
                  <th>Todays's Customers</th>
                  <th>Todays's Follow Up</th>
                  <th>E-mail</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach( $members as $member )
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $member->au_name }}</td>
                  <td>{{ $member->au_mobile }}</td>
                  <td align="center">
                  <a href="{{ url('user/customer_list',['id' => $member->au_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ $member->customers->count() }}
                  </a>
                  </td>
                  <td align="center">{{ App\Models\Sds_query_book::user_today_cus($member->au_id, $member->au_company_id)->count() }}</td>
                  <td align="center">{{ App\Models\Client_feedback::user_today_follow($member->au_id,$member->au_company_id)->count() }}</td>
                  <td>{{ $member->au_email }}</td>
                  <td align="center">
                    <div class="btn-group">
                          <button type="button" class="btn btn-info">Action</button>
                          <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                          </button>
                          <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                            <li>
                            <a href="{{ url('/user_profile',['id' => $member->au_id]) }}">Show
                            </a>
                            </li>

                            <li class="divider"></li>

                            <li>
                            <a href="{{ url('/user_edit',['id' => $member->au_id]) }}">Update
                            </a>
                            </li>

                            <li class="divider"></li>

                            <li>
                            <a href="#">Active
                            </a>
                            </li>
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
          </section>
          @endsection

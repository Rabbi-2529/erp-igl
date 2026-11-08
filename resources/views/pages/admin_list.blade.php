@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('create_company_class','menu-open')
@section('company_list_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Company List
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Company Table</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Company Name</th>
                  <th>Name</th>
                  <th>Mobile</th>
                  <th>E-mail</th>
                  <th>Team Leaders</th>
                  <th>Users</th>
                  <th>Customers</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach($admins as $admin)
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $admin->au_company_name }}</td>
                  <td>{{ $admin->au_name }}</td>
                  <td>{{ $admin->au_mobile }}</td>
                  <td>{{ $admin->au_email }}</td>
                  <td align="center">{{ App\Models\Admin_user::teams($admin->au_company_id)->count() }}</td>
                  <td align="center">{{ App\Models\Admin_user::users($admin->au_company_id)->count() }}</td>
                  <td align="center">{{ App\Models\Sds_query_book::total_customer($admin->au_company_id)->count() }}</td>
                  <td align="center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-info">Action</button>
                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                        <li><a href="{{ url('/user_profile',['id' => $admin->au_id]) }}">Show</a></li>
                        <li class="divider"></li>
                        <li><a href="{{ url('/user_edit',['id' => $admin->au_id]) }}">Update</a></li>
                        <li class="divider"></li>
                        @if( $admin->au_status == 1 )
                        <li><a href="{{ route('pages.admin_list',['id' => $admin->au_id]) }}">Suspend Company</a></li>
                        @elseif( $admin->au_status == 0)
                        <li><a href="{{ route('pages.admin_list_activate',['id' => $admin->au_id]) }}">Activate Company</a></li>
                        @endif
                        <li class="divider"></li>
                        <li><a href="{{ route('com-permission',['id' => $admin->au_company_id]) }}">Permission</a></li>
                      </ul>
                </div>
                  </td>
                  @endforeach
                </tr>
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          </section>
          @endsection

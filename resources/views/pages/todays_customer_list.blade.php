@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('today_customer_list_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Follow up
      </h1>
    </section>
	<div class="box">
            <h3>User List</h3>
            <!-- /.box-header -->
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>User ID</th>
                  <th>Name</th>
                  <th>Mobile</th>
                  <th>Email</th>
                  <th>Customer/Total</th>
                  <th>Customer/Monthly</th>
                  <th>Customer/Today</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach( $user_customers as $user_customer )
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $user_customer->au_user_id }}</td>
                  <td>{{ $user_customer->au_name }}</td>
                  <td>{{ $user_customer->au_mobile }}</td>
                  <td>{{ $user_customer->au_email }}</td>
                  <td align="center">
                  <a target="_blank" href="{{ url('user/customer_list',['id' => $user_customer->au_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ $user_customer->customers->count() }}
                  </a>
                  </td>
                  <td align="center"><a href="{{ url('/customer_list_monthly',['id' =>$user_customer->au_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">
                    {{ App\Models\Sds_query_book::user_monthly_cus($user_customer->au_id, $user_customer->au_company_id)->count() }}
                  </a>
                  </td>
                  <td align="center">
                    <a href="{{ url('/customer_list_today',['id' => $user_customer->au_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">
                    {{ App\Models\Sds_query_book::user_today_cus($user_customer->au_id, $user_customer->au_company_id)->count() }}
                    </a>
                  </td>
                </tr>

                @endforeach
                </tbody>


            </table>
            <!-- /.box-body -->
          </div>
          </section>
          @endsection

@extends('layouts.master')
@section('title', 'Admin Created Customers')

@section('customer_relation_class','active')
@section('customer_class','active')
@section('admins_created_class','active')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                  <h4>Admin Created Customers</h4>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                      <th>SL</th>
                      <th>Cus ID</th>
                      <th>Cus Name</th>
                      <th>Com. Name</th>
                      <th>Mobile</th>
                      <th>Location</th>
                      <th>Reason</th>
                      <th>Result</th>
                      <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php($i)
                    @php($sl=0)
                    @foreach($admin as $customer)
                    <tr>
                      <td>{{ ++$sl }}</td>
                      <td>{{ $customer->qb_serial }}</td>
                      <td>{{ $customer->qb_name }}</td>
                      <td>{{ $customer->qb_company_name }}</td>
                      <td>{{ $customer->qb_mobile }}</td>
                      <td>{{ $customer->qb_address }}</td>
                      <td>{{ $customer->reas_info['sr_reason'] ?? 'N/A' }}</td>
                      <td>
                        @for( $i=1; $i<=$customer->qb_result; ++$i )
                          <b style="color: green; font-size: 20px;">*</b>
                        @endfor
                      </td>
                      <td align="center">
                          <a href="{{ url('/client_feedback',['id' => $customer->qb_id]) }}" class="btn btn-info btn-sm">Follow Up</a>
                      </td>
                    </tr>
                    @endforeach
                    </tbody>
                  </table>
            </div>
        </div>
    </div>
</div>


@endsection
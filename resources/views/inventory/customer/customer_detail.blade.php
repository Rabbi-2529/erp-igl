@extends('layouts.master')
@section('title','active')
@section('inventory_class','Customer Details')
@section('customer_class','active')
@section('inv_customer_class','active')
@section('customer_list','active')
@section('content')
<section class="content">
<section class="content-header">
  <div class="row">
    <div class="pull-left">
      <h3 style="margin: 4px;">
        Customer
      </h3>
    </div>
  </div>

    </section>
    <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Customer Details</h4>
                </div>
            </div>

            <div class="panel-body">
            <!-- /.box-header -->
            <div class="box-body">
            <h2 class="text-center">Customer Name: {{ $cus_det->inv_cus_name }}</h2>
              <table class="table table-bordered table-striped table-hover datatable">
                <tr>
                  	<th>Company Nane</th>
                  	<td>{{ $cus_det->inv_cus_com_name }}</td>
                </tr>
                <tr>
                	<th>Mobile</th>
                	<td>{{ $cus_det->inv_cus_mobile }}</td>
                </tr>
                <tr>
                	<th>Email</th>
                	<td>{{ $cus_det->inv_cus_email }}</td>
                </tr>
                <tr>
                	<th>Address</th>
                	<td>{{ $cus_det->inv_cus_address }}</td>
                </tr>
                <tr>
                    <th>Customer Type</th>
                    <td>
                        @if($cus_det->inv_cus_type == 1)
                        Regular
                        @elseif($cus_det->inv_cus_type == 2)
                        Eregular
                        @elseif($cus_det->inv_cus_type == 3)
                        Corporate
                        @endif
                    </td>
                </tr>
                <tr>
                	<th>Website</th>
                	<td>{{ $cus_det->inv_cus_website }}</td>
                </tr>
                <tr>
                	<th>Status</th>
                	<td>
                    @if($cus_det->inv_cus_status == 1)
                    Active
                    @elseif($cus_det->inv_cus_status == 0)
                    Inactive
                    @endif
                  </td>
                </tr>
                <tr>
    <th>Created By</th>
    <td>
        @if($cus_det->cus_submit)
            {{ $cus_det->cus_submit['au_name'] }}
        @else
            Not Available
        @endif
    </td>
</tr>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection

@extends('layouts.master')
@section('title', 'Product Details')
@section('inventory_class','active')
@section('product_class','active')
@section('inv_pro_class','active')
@section('pro_list','active')
@section('content')
<section class="content">
<section class="content-header">
  <div class="row">
    <div class="pull-left">
      <h3 style="margin: 4px;">
        Product
      </h3>
    </div>
  </div>

    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Product Details</h3>
              <div class="box-tools">
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            <h2 class="text-center">Product Name: {{ $details->inv_pro_det_pro_id }}</h2>
              <table class="table table-bordered table-striped table-hover datatable">
                <tr>
                    <th>Company ID</th>
                    <td>{{ $details->inv_pro_det_com_id }}</td>
                </tr>
                <tr>
                  	<th>Model Name</th>
                  	<td>{{ $details->type_info['inv_pro_type_name'] }}</td>
                </tr>
                <tr>
                    <th>Product Description</th>
                    <td>{{ $details->inv_pro_det_pro_description }}</td>
                </tr>
                <tr>
                    <th>Submit By</th>
                    <td>{{ $details->submit_type['au_name'] }}</td>
                </tr>
                <tr>
                    <th>Supplier</th>
                    <td>
                    @if (App\Models\Inv_product_detail::suppliers_info($details->inv_pro_det_id) != null)
                    @foreach (App\Models\Inv_product_detail::suppliers_info($details->inv_pro_det_id) as $det)
                    {{ $det->inv_sup_com_name }}
                    @if (!$loop->last)
                      ,
                    @endif
                    @endforeach
                    @endif
                    </td>
                </tr>
                <tr>
                  <th>Buy Price</th>
                  <td>{{ $details->inv_pro_det_buy_price }}</td>
                </tr>
                <tr>
                  <th>Sell Price</th>
                  <td>{{ $details->inv_pro_det_sell_price }}</td>
                </tr>
                <tr>
                  <th>Short Quantity</th>
                  <td>{{ $details->inv_pro_det_short_qty }}</td>
                </tr>
                <tr>
                	<th>Status</th>
                	<td>
                    @if($details->inv_pro_det_status == 1)
                    Activate
                    @elseif($details->inv_pro_det_status == 0)
                    Deactivate
                    @endif
                  </td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection

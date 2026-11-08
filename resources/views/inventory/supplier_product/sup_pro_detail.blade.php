@extends('layouts.master')
@section('inventory_class','menu-open')
@section('supplier_class','menu-open')
@section('supplier_product','menu-open')
@section('list_sup_pro','active')
@section('content')
<section class="content">
<section class="content-header">
  <div class="row">
    <div class="pull-left">
      <h3 style="margin: 4px;">
        Supplier Product
      </h3>
    </div>
  </div>

    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Supplier Product Details</h3>
              <div class="box-tools">
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            <h2 class="text-center">Product Name: {{ $sup_pro_detail->product_info['inv_pro_det_pro_id'] }}</h2>
              <table class="table table-bordered table-striped">
                <tr>
                  	<th>Supplier Name</th>
                  	<td>{{ $sup_pro_detail->supplier_info['inv_sup_com_name'] }}</td>
                </tr>
                <tr>
                    <th>Submit By</th>
                    <td>{{ $sup_pro_detail->submit_by['au_name'] }}</td>
                  </tr>
                <tr>
                	<th>Status</th>
                	<td>
                    @if($sup_pro_detail->inv_sup_pro_status == 1)
                    Active
                    @elseif($sup_pro_detail->inv_sup_pro_status == 0)
                    Inactive
                    @endif
                  </td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection

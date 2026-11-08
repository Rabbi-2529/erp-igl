@extends('layouts.master')
@section('title','Product Invoice')
@section('inventory_class','active')
@section('pro_inv_class','active')
@section('sell_pro','active')
@section('content')
<section class="content">
        @if(session()->has('sub_err'))
        <div class="alert alert-danget alert-dismissible" role="alert">
          {{ session('sub_err') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('pro_del'))
              <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('pro_del') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
        @endif
            <section class="content-header">
                  <h1>
                    Sell Product
                  </h1>
                </section>
                <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Product Invoice</h3>
                        </div>
                    </div>

                  <div class="panel-body" >
                        <div class="col-sm-12">
                            <table class="table table-bordered table-striped table-hover datatable">
                                <tr>
                                    <th>Customer Name</th>
                                    <td>
                                        {{ $pro_cus->inv_cus_name }}({{ $pro_cus->inv_cus_com_name }})
                                    </td>
                                    <th>Invoice No:</th>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $pro_cus->inv_cus_address }}</td>
                                    <th>Mobile</th>
                                    <td>{{  $pro_cus->inv_cus_mobile }}</td>
                                </tr>
                                <tr>
                                    <th>Sold By</th>
                                    
                                    <td>{{ $pro_temps->first()->sold_by['au_name'] }}</td>
                                </tr>
                            </table>
                        </div>
                          <table class="table">
                            <thead>
                            <tr>
                              <th class="text-center">SL</th>
                              <th class="text-left">Description</th>
                              <th class="text-center">Sold Qty</th>
                              <th class="text-center">Unit Price</th>
                              <th class="text-center">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php($sl=0)
                            @php($balance=0)
                            @foreach ($pro_temps as $temp)
                            <tr>
                                <td class="text-center">{{ ++$sl }}</td>
                                <td class="text-left">
                                    {{ $temp->pro_warranty['inv_pro_det_pro_description'] }}({{ $temp->inv_pro_temp_type_name }})
                                    <br>
                                    <b>
                                        {{ implode(', ', explode(',',$temp->inv_pro_temp_slno)) }}
                                    </b><br>
                                    @if ($temp->pro_warranty['inv_pro_det_pro_warranty'] == 0)
                         
                                    @else
                                    <b>Warrenty:</b> {{ $temp->pro_warranty['inv_pro_det_pro_warranty'] }}(Days)
                                    @endif
                                </td>
                                
                                <td class="text-center">{{ $temp->inv_pro_temp_qty }}</td>
                                <td class="text-right">{{ number_format($temp->inv_pro_temp_unit_price,2) }}</td>
                                <td class="text-right">{{ number_format(($temp->inv_pro_temp_unit_price * $temp->inv_pro_temp_qty),2) }}</td>
                            </tr>
                            @php($balance = $balance + ($temp->inv_pro_temp_unit_price * $temp->inv_pro_temp_qty))

                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right">Total Amount</td>
                                    
                                    <td class="text-right">{{ number_format($balance,2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right"><span class="underline"><b>Discount</b></span></td>
                                    
                                    <td class="text-right"><span class="underline">{{ number_format($discount,2) }}</span></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right"><span class="underline"><b>Delivery Charges</b></span></td>
                                    
                                    <td class="text-right"><span class="underline">{{ number_format($delivery,2) }}</span></td>
                                </tr>
                                @php($total = ($balance +  $delivery) - $discount)
                                
                                <tr>
                                    <td colspan="4" class="text-right"><span class="underline"><b>Net Payable Amount</b></span></td>
                                    
                                    <td class="text-right"><span class="underline">{{ number_format($total,2) }}</span></td>
                                </tr>
                                
                            </tfoot>
                          </table>
                          {{ Form::open(['action' => 'Inventory\ProductSellEditController@cartSubmit','method' => 'post' , 'class' => 'form-horizontal']) }}
                          <input type="text" class="hidden" name="customer" value="{{ $pro_cus->inv_cus_id }}">
                          <input type="text" class="hidden" name="discount" value="{{ $discount }}">
                          <input type="text" class="hidden" name="delivery" value="{{ $delivery }}">
                          <button type="submit" class="btn btn-success pull-right">Confirm</button>
                          {{ Form::close() }}

                          <a href="{{ route('sell_edit.sell-pro-edit', $temp->inv_pro_temp_invoice_no) }}" class="btn btn-danger pull-right" style="margin-right: 5px;">Edit</a>

                        </div>
                        <!-- /.box-body -->
                      </div>
                     </section>
@endsection
@section('custom_style')
<style>
   .underline {
    border-bottom: 2px solid currentColor;
    }
    .underline--dotted {
        border-bottom: 3px black dashed;
    }
    .invoice{
        background-color: lightblue;
        border-radius: 12px;
    }

</style>
@endsection
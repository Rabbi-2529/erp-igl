@extends('layout.master')
@section('money_class','menu-open')
@section('curr_inv_class','menu-open')
@section('sell_curr','active')
@section('content')
<section class="content">
        @if(session()->has('det_up'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('det_up') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('sub_err'))
              <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('sub_err') }}
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
                <div class="box">
                        <div class="box-header">
                          <h3 class="box-title">Product Invoice</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                                <div class="box-header text-center">
                                    <h3 class="box-title invoice"><b>Invoice</b></h3>
                                </div>
                        <div class="col-sm-12">
                            <table class="table table-striped table-responsive">
                                <tr>
                                    <th>Customer Name</th>
                                    <td>
                                        {{ $pro_cus->name }}
                                    </td>
                                    <th>Invoice No:</th>
                                    {{-- @php($invoiceNo = App\Inv_product_inventory::getInvoice($pro_cus)) --}}
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $pro_cus->address }}</td>
                                    <th>Mobile</th>
                                    <td>{{  $pro_cus->mobile }}</td>
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
                            @php($total = 0)
                            @foreach ($pro_temps as $temp)
                            <tr>
                                <td class="text-center">{{ ++$sl }}</td>
                                <td class="text-left">
                                    {{ $temp->mex_curr_temp_curr_name }}({{ $temp->mex_curr_temp_type_name }})
                                </td>
                                
                                <td class="text-center">{{ $temp->mex_curr_temp_qty }}</td>
                                <td class="text-right">{{ number_format($temp->mex_curr_temp_unit_price,2) }}</td>
                                <td class="text-right">{{ number_format(($temp->mex_curr_temp_unit_price * $temp->mex_curr_temp_qty),2) }}</td>
                            </tr>
                            @php($balance = $balance + ($temp->mex_curr_temp_unit_price * $temp->mex_curr_temp_qty))

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
                                {{-- <tr>
                                    <td colspan="4" class="text-right"><span class="underline"><b>Delivery Charges</b></span></td>
                                    
                                    <td class="text-right"><span class="underline">{{ number_format($delivery,2) }}</span></td>
                                </tr> --}}
                                @php($total = $balance - $discount)
                                
                                <tr>
                                    <td colspan="4" class="text-right"><span class="underline"><b>Net Payable Amount</b></span></td>
                                    
                                    <td class="text-right"><span class="underline">{{ number_format($total,2) }}</span></td>
                                </tr>
                                
                            </tfoot>
                          </table>
                          {{ Form::open(['action' => 'MoneyExchange\CurrencyCartController@cartSubmit','method' => 'post' , 'class' => 'form-horizontal']) }}
                          <input type="text" class="hidden" name="customer" value="{{ $pro_cus->id }}">
                          <input type="text" class="hidden" name="discount" value="{{ $discount }}">
                          {{-- <input type="text" class="hidden" name="delivery" value="{{ $delivery }}"> --}}
                          <button type="submit" class="btn btn-success pull-right">Confirm</button>
                          {{ Form::close() }}

                          <a href="{{ route('buy.pro_sell') }}" class="btn btn-danger pull-right" style="margin-right: 5px;">Edit</a>

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
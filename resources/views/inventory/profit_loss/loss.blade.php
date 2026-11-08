@extends('layouts.master')
@section('title','Product Loss')
@section('inventory_class','active')
@section('profit_loss','active')
@section('loss_class','active')
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
                Loss
              </h1>
            </section>
            <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Product Loss</h3>
                        </div>
                    </div>

                  <div class="panel-body" ></div>

                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Date</th>
                          <th>Product Name</th>
                          <th>Product Qty</th>
                          {{-- <th>Return Qty</th> --}}
                          <th>Sell Qty</th>
                          {{-- <th>Sell Return Qty</th> --}}
                          <th>Buy Price</th>
                          <th>Sell Price</th>
                          <th class="text-center">Profit/Loss</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @php($total_profit = 0)
                       @foreach ($products as $pro)

                       @php($total_buy_qty = App\Models\Inv_product_detail::get_total_buy_qty($pro->inv_pro_det_id))
                       @php($total_buy_ret_qty = App\Models\Inv_product_detail::get_total_buy_return_qty($pro->inv_pro_det_id))
                       @php($total_ret_a_qty = $total_buy_qty - $total_buy_ret_qty)
                       @php($total_sell_qty = App\Models\Inv_product_detail::get_total_sell_qty($pro->inv_pro_det_id))
                       @php($total_a_qty = $total_buy_qty - $total_sell_qty)
                       @php($total_sell_ret_qty = App\Models\Inv_product_detail::get_total_sell_return_qty($pro->inv_pro_det_id))
                       @php($total_ret_s_qty = $total_sell_qty - $total_sell_ret_qty)
                       @php($total_buy_price = App\Models\Inv_product_detail::get_total_buy_price($pro->inv_pro_det_id))
                       @php($total_sell_price = App\Models\Inv_product_detail::get_total_sell_price($pro->inv_pro_det_id))
                       @php($total_sell_ret_price = App\Models\Inv_product_detail::get_total_sell_return_price($pro->inv_pro_det_id))
                       @php($total_buy_ret_price = App\Models\Inv_product_detail::get_total_buy_return_price($pro->inv_pro_det_id))
                       @php($sell_price = $total_sell_price - $total_sell_ret_price)
                       @php($buy_price = $total_buy_price - $total_buy_ret_price)
                       @php($total_available = $total_ret_a_qty - $total_ret_s_qty)
                       @php($total_available_price = $total_available * $pro->inv_pro_det_buy_price)
                       @php($last_buy_price = $buy_price - $total_available_price)
                       @php($profit_loss = $sell_price - $last_buy_price)
                       @php($total_profit = $total_profit + $profit_loss)
                       <tr>
                            <td>{{ ++$sl }}</td>
                            <td></td>
                            <td>{{ $pro->inv_pro_det_pro_id }}</td>
                            <td class="text-center">
                              {{ $total_buy_qty }} - {{ $total_buy_ret_qty }}<br>
                              T.B: {{ $total_ret_a_qty }}
                            </td>
                            {{-- <td class="text-center">
                                {{ number_format($total_buy_ret_qty, 2) }}
                            </td> --}}
                            <td class="text-center">
                              {{ $total_sell_qty }} - {{ $total_sell_ret_qty }}<br>
                              T.S: {{ $total_ret_s_qty }}
                            </td>
                            {{-- <td class="text-center">{{ number_format($total_sell_ret_qty, 2) }}</td> --}}
                            <td class="text-right">
                              {{ number_format($total_buy_price,2) }}<br>
                              T.B: {{ number_format($buy_price,2) }}
                            </td>
                            <td class="text-right">
                              {{ number_format($total_sell_price,2) }}<br>
                              T.S: {{ number_format($sell_price,2) }}
                            </td>
                            <td class="text-right">{{ number_format($profit_loss,2) }}</td>
                        </tr>
                       @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-right" colspan="7">Total Discount</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="7">Total Profit</td>
                                <td class="text-right">{{ number_format($total_profit,2) }}</td>
                            </tr>
                        </tfoot>
                      </table>
                    </div>
                    <!-- /.box-body -->
                  </div>
                 </section>
@endsection
@section('custom_style')
    <style>
        .action_btn{
            border: 1px solid;
            padding: 5px;
        }
        .btn_show{
            background-color: green;
            color: white;
        }
        .btn_delete{
            background-color: red;
            color: white;
        }
        .btn_edit{
            background-color: cornflowerblue;
            color: white;
        }
    </style>
    @endsection
    @section('custom_script')
<script type="text/javascript">
  function deleteprocat(){
    let clickDel = confirm("Are you sure want to delete this?");
    if (clickDel == true) {
      return true;
    }else{
      return false;
    }
  }
  $(document).ready(function() {
    var table = $('#example2').DataTable( {
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true
    } );
} );
</script>
@endsection

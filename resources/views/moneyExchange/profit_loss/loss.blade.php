@extends('layout.master')
@section('money_class','menu-open')
@section('profit_loss','menu-open')
@section('loss_class','active')
@section('content')
<section class="content">
    
        <section class="content-header">
              <h1>
                Loss
              </h1>
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Currency Loss</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                      <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Date</th>
                          <th>Currency Name</th>
                          <th>Currency Qty</th>
                          {{-- <th>Return Qty</th> --}}
                          <th>Sell Qty</th>
                          {{-- <th>Sell Return Qty</th> --}}
                          <th>Buy Price</th>
                          <th>Sell Price</th>
                          <th class="text-center">Loss</th>
                        </tr>
                        </thead>
                        <tbody>
                       
                       <tr>
                            <td>1</td>
                            <td>17-08-2020</td>
                            <td>USD</td>
                            <td class="text-center">
                              10
                            </td>
                            {{-- <td class="text-center">
                                {{ number_format($total_buy_ret_qty, 2) }}
                            </td> --}}
                            <td class="text-center">
                             5
                            </td>
                            {{-- <td class="text-center">{{ number_format($total_sell_ret_qty, 2) }}</td> --}}
                            <td class="text-right">
                              400
                            </td>
                            <td class="text-right">
                              375
                            </td>
                            <td class="text-right">25</td>
                        </tr>
                       
                        </tbody>
                        <tfoot>
                            {{-- <tr>
                                <td class="text-right" colspan="7">Total Discount</td>
                                <td></td>
                            </tr> --}}
                            <tr>
                                <td class="text-right" colspan="7">Total Loss</td>
                                <td class="text-right">25</td>
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
   
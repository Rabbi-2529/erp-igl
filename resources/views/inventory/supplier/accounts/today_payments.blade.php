@extends('layouts.master')
@section('title','Today Statements')
@section('inventory_class','active')
@section('supplier_class','active')
@section('inv_supplier_acc_class','active')
@section('supplier_today_payment','active')
@section('content')
<section class="content">
        <section class="content-header">
              @if(Session::has('errmsg'))
              <div class="alert alert-danger alert-dismissible" role="alert">
              {{ session('errmsg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
              </div>
              @endif
              @if(Session::has('msg'))
              <div class="alert alert-success alert-dismissible" role="alert">
              {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
              </div>
              @endif

              <h1>
                Today's Account Statements of Supplier
              </h1>

            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Account Statement</h3>
                    </div>

                    <!-- /.box-header -->
                    <div class="box-body">


                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Name</th>
                          <th>Debit</th>
                          <th>Credit</th>

                          <th>Balance</th>
                      </tr>
                        </thead>
                        <tbody>
                      @php

                          $sl=0;
                          $total_credit=0;
                          $total_debit=0;
                          $total_amount=0;

                        @endphp

                        @foreach($todayPayments as $todayPayment)

                         @php
                         		$total_credit+=$todayPayment->inv_abs_credit;
                         		$total_debit+=$todayPayment->inv_abs_debit;
                         		$total_amount+=$todayPayment->inv_abs_credit-$todayPayment->inv_abs_debit;
                         @endphp

                        <tr>
                          <td style="text-align: center;">{{ ++$sl }}</td>
                          <td>
                          	{{ App\Models\Inv_supplier::getSupplierNameById($todayPayment->	inv_abs_reference_id)->inv_sup_person}}
                          </td>


                          <td style="text-align: right;">
                          	{{ number_format($todayPayment->inv_abs_debit,2) }}
                          </td>
                           <td style="text-align: right;">
                            {{ number_format($todayPayment->inv_abs_credit,2) }}
                          </td>
                          <td style="text-align: right;">
                          	{{ number_format(($todayPayment->inv_abs_credit-$todayPayment->inv_abs_debit),2) }}
                          </td>

                        </tr>
                        @endforeach
                        </tbody>

                        <tfoot>
                          <tr>
                          	<td  style="text-align:center; font-weight: bolder;">#</td>
                            <td  style="text-align:right; font-weight: bolder;">Total:</td>

                            <td style="font-weight: bolder; text-align: right;">
                              {{number_format($total_debit,2)}}
                            </td>
                             <td style="font-weight: bolder; text-align: right;">
                              {{number_format($total_credit,2)}}
                            </td>
                            <td style="font-weight: bolder; text-align: right;">
                              {{number_format($total_amount,2)}}
                            </td >


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

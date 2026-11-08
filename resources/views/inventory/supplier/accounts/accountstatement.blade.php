@extends('layouts.master')
@section('title','Account Statements')
@section('inventory_class','active')
@section('supplier_class','active')
@section('inv_supplier_acc_class','active')
@section('supplier_account_statement','active')
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
                Supplier's Account Statements
              </h1>

            </section>
            <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Account Statements</h4>
                    </div>
                </div>
                <div class="panel-body">


                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Company</th>
                          <th>Name</th>
                           <th>Debit</th>
                          <th>Credit</th>

                          <th>Balance</th>

                          <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        @php

                          $sl=0;
                          $total_credit=0;
                          $total_debit=0;
                          $total_balance=0;

                        @endphp

                        @foreach($inv_pros as $inv_sup)

                        @php

                            $total_credit += App\Models\Inv_product_inventory::getSupCreditByID($inv_sup->inv_sup_id);

                            $total_debit+=App\Models\Inv_product_inventory::getSupDebitByID($inv_sup->inv_sup_id);

                            $total_balance+=App\Models\Inv_product_inventory::getSupBalanceByID($inv_sup->inv_sup_id);

                        @endphp

                        <tr>
                          <td>{{ ++$sl }}</td>
                          <td>{{ $inv_sup->inv_sup_com_name }}</td>

                          <td>{{ $inv_sup->inv_sup_person}}</td>





                          <td style="text-align: right;">{{ number_format(App\Models\Inv_product_inventory::getSupDebitByID($inv_sup->inv_sup_id) , 2) }}</td>

                           <td style="text-align: right;">{{ number_format(App\Models\Inv_product_inventory::getSupCreditByID($inv_sup->inv_sup_id) , 2) }}</td>


                          <td style="text-align: right;">{{ number_format(App\Models\Inv_product_inventory::getSupBalanceByID($inv_sup->inv_sup_id) , 2) }}</td>

                          <td style="text-align: center;">
                            <a href="{{route('inventory.supplier.accounts.account-statement-details',$inv_sup->inv_sup_id)}}">
                              View Details
                            </a>
                          </td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                          <tr>
                            <td colspan="3" style="text-align:right; font-weight: bolder;">Total:</td>

                            <td style="font-weight: bolder; text-align: right;">
                              {{ number_format($total_debit ,2) }}
                            </td>
                            <td style="font-weight: bolder; text-align: right;">
                              {{ number_format($total_credit ,2) }}
                            </td>
                            <td style="font-weight: bolder; text-align: right;">
                              {{ number_format($total_balance ,2) }}
                            </td >
                            <td style="font-weight: bolder; text-align: center;">---</td>

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

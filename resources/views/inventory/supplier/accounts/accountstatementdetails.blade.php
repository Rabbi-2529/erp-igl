@extends('layouts.master')
@section('title','Account Statement Details')
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


              <div class="box">
              <div class="box-header">

                @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif

            <form action="" method="get">
              <input type="hidden" name="_token" value="{{ csrf_token()   }}" id="_token">
             <div class="col-xs-3">
              <input type="text" name="start_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ request()->start_date }}" class="form-control" id="start_date" placeholder="Enter Start Date" >
             </div>
              <div class="col-xs-3">
              <input type="text" name="end_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ request()->end_date }}" class="form-control" id="end_date" placeholder="Enter End Date" >
             </div>
                <div class="col-xs-3">
                  <button type="submit" class="btn btn-info" name="searchbtn">Search</button>
                   <button type="submit" class="btn btn-warning" name="printdfbtn" id="download_statement_btn">Download</button>
                </div>

                </form>


                    </div>

                    <!-- /.box-header -->
                    <div class="box-body">
                 </div>
             </div>


              <h3 style="margin-top: 55px">
               Account Statement Details of {{ App\Models\Inv_supplier::getSupplierCompany(request()->supplier_id)->inv_sup_com_name }}
              </h3>

            </section>


            <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                {{ App\Models\Inv_supplier::getSupplierCompany(request()->supplier_id)->inv_sup_person }}'s
                Statement Details</h3>
                </div>
            </div>

            <div class="panel-body">
    

                    <!-- /.box-header -->
                    <div class="box-body">
                      <div class="col-xs-12">
                        <h4><b>
                           {{ App\Models\Inv_supplier::getSupplierCompany(request()->supplier_id)->inv_sup_person }}'s
                        </b> Total Discount is {{App\Models\Inv_product_inventory::getTotalDiscountBySupplierID(request()->supplier_id)}} Tk.
                      </h4>
                      </div>

                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th style="text-align:center;">SL</th>
                          <th style="text-align:center;">Date</th>
                          <th style="text-align: center;">Invoice No</th>
                          <th style="text-align:center;">Description</th>
                           <th style="text-align:center;">Debit</th>
                          <th style="text-align:center;">Credit</th>

                          <th style="text-align:center;">Balance</th>
                          <th style="text-align: center;">Action</th>

                        </tr>
                        </thead>
                        <tbody>

                        @php

                          $sl=0;
                          $total_credit=0;
                          $debit = 0;
                          $credit = 0;
                          $balance = 0;
                          $total_debit=0;
                          $total_balance=0;

                        @endphp

                        @foreach($inv_pros as $inv_sup)

                        @php
                            $debit = App\Models\Inv_product_inventory::getDebitByInvoiceNo($inv_sup->inv_pro_inv_invoice_no);
                            $credit = App\Models\Inv_product_inventory::getCreditByInvoiceNo($inv_sup->inv_pro_inv_invoice_no);
                            $balance = $balance + $credit - $debit;
                            $total_credit+=App\Models\Inv_product_inventory::getCreditByInvoiceNo($inv_sup->inv_pro_inv_invoice_no);

                            $total_debit+=App\Models\Inv_product_inventory::getDebitByInvoiceNo($inv_sup->inv_pro_inv_invoice_no);

                        @endphp

                        <tr>
                          <td style="text-align: center;">
                            {{ ++$sl }}
                          </td>
                          <td style="text-align: center;">
                            {{ $inv_sup->inv_pro_inv_issue_date }}
                          </td>
                          <td style="text-align: center;">
                            {{$inv_sup->inv_pro_inv_invoice_no}}
                          </td>
                          <td class="text-left">
                            {{$inv_sup->inv_pro_inv_tran_desc}}
                          </td>

                          <td style="text-align: right;">
                            {{ number_format($debit,2)}}
                          </td>
                          <td style="text-align: right;">
                            {{ number_format($credit,2) }}
                          </td>
                          <td style="text-align: right;">
                            {{ number_format($balance,2) }}
                          </td>
                          <td style="text-align: center;">
                              <a href="#" onclick="show_invoice_details('{{ $inv_sup->inv_pro_inv_invoice_no }}')" data-toggle="modal" data-target="#invoice_details">

                              <i class="fa fa-eye"></i>
                            </a>
                            </td>
                        </tr>
                         @endforeach

                        </tbody>
                        <tfoot>
                          <tr>
                            <td style="text-align: center;font-weight: bolder;">#</td>
                            <td colspan="3" style="text-align:right; font-weight: bolder;">Total:</td>

                            <td style="font-weight: bolder; text-align: right;">
                              {{number_format($total_debit,2)}}
                            </td>
                            <td style="font-weight: bolder; text-align: right;">
                              {{number_format($total_credit,2)}}
                            </td>
                            <td style="font-weight: bolder; text-align: right;">
                              {{number_format(($total_credit-$total_debit),2)}}
                            </td >
                           <td style="text-align: center;font-weight: bolder;">---</td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                    <!-- /.box-body -->
                  </div>
                 </section>


<!-- Modal -->
<div class="modal fade" id="showInvoiceDetailsModal" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Invoice Details</h4>
      </div>
      <div class="modal-body">
        <div class="load-details"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

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

InitializeDate();

function InitializeDate() {
    var date = new Date();
    var dd = date.getDate();
    var mm = date.getMonth() + 1;
    var yyyy = date.getFullYear();

    var ToDate = yyyy+'-'+mm+'-'+dd;
    var FromDate = yyyy+'-'+(mm-1)+'-'+dd;
    $('#start_date').datepicker('setDate', FromDate);
    $('#end_date').datepicker('setDate', ToDate);
}


$(document).ready(function(){

$( "#start_date" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});

$(document).ready(function(){

$( "#end_date" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});
 $("#download_statement_btn").click(function () {
            let start_date = $("#start_date").val();
            let end_date = $("#end_date").val();
            let _toekn=$("#_toekn").val();
            let route = "{{ route('inventory.supplier.accounts.download-supplier-account-statement-details', request()->supplier_id) }}?start_date="+ start_date +"&end_date="+end_date;
            window.open(route, '_blank');
        });

</script>
<script type="text/javascript">

  function show_invoice_details(invoice_id) {

    var requestUrl="{{route('inventory.supplier.accounts.supplier-voucher-details')}}";
    var _token=$("#_token").val();
    $.ajax({
      type: "GET",
      url: requestUrl,
      data: { invoice_id: invoice_id,_token:_token},
      success: function (result) {
       $(".load-details").html(result);
       $("#showInvoiceDetailsModal").modal("show");
      }
    });
  }
</script>
@endsection

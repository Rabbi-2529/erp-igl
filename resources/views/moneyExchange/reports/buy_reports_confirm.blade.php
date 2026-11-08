@extends('layout.master')
@section('inventory_class','menu-open')
@section('report_class','menu-open')
@section('buy_reports_confirm','active')
@section('content')
<section class="content">
    @if(session()->has('confirm'))
    <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('confirm') }}
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
          <div class="box">
              <div class="box-header">
                   
            <form action="" method="get">
              <input type="hidden" name="_token" value="" id="_token">
             <div class="col-xs-3">
              <input type="text" name="start_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="" class="form-control" id="start_date" placeholder="Enter Start Date" >
             </div>
              <div class="col-xs-3">
              <input type="text" name="end_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="" class="form-control" id="end_date" placeholder="Enter End Date" >
             </div>
                <div class="col-xs-3">
                  <button type="submit" class="btn btn-info" name="searchbtn">Search</button>
                  <a href="{{ route('reports.sell-reports-download') }}" class="btn btn-warning">Download</a>
                </div>
                </form>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                 </div>
             </div>
              <h1>
               Buy Reports of {{ Auth::user()->au_company_name }}  
              </h1>
        
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Confirm Report</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                      <table id="example1" class="table table-bordered table-striped" style="margin-top: 20px;">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Invoice No</th>
                          <th>Issue Date</th>
                          <th>Sup Name</th>
                          <th>Description</th>
                          <th>Amount</th>
                          <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                          $sl=0;
                          $balance = 0;
                        @endphp
                        @foreach ($buy_reports as $buy)
                        
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $buy->inv_pro_inv_invoice_no }}</td>
                            <td>{{ $buy->inv_pro_inv_issue_date }}</td>
                            <td>{{ $buy->getCustomerInfo['inv_cus_name'] }}</td>
                            <td>{{ $buy->inv_pro_inv_tran_desc }}</td>
                            <td class="text-right">{{ App\Inv_product_inventory::getTotalDebit($buy->inv_pro_inv_invoice_no) }}</td>
                            <td style="text-align: center;">
                              <div class="btn-group">
                                <button type="button" class="btn btn-info btn-xs">Action</button>
                                <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                  <span class="caret"></span>
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                                  <li>
                                    <a href="javascript:void(0);" onclick="sell_reports('{{ $buy->inv_pro_inv_invoice_no }}')">Details</a>
                                  </li>
                                  <li class="divider"></li>
                                  @if(Auth::user()->au_user_type == 4)
                                  <li>
                                    <a href="{{ route('buy_edit.buy-edit-pro',['invoice' => $buy->inv_pro_inv_invoice_no]) }}">Edit</a>
                                 </li>
                                <li class="divider"></li>
                                @endif
                                  <li>
                                    <a href="{{ route('reports.buy-pdf',['invoice' => $buy->inv_pro_inv_invoice_no]) }}" target="_blank">Download</a>
                                  </li>
                                </ul>
                          </div>
                                
                            </td>
                        </tr>
                        @php($balance = $balance + App\Inv_product_inventory::getTotalDebit($buy->inv_pro_inv_invoice_no))
                        @endforeach
                        </tbody>
                        <tfoot>
                          <tr>
                            <td colspan="5" style="text-align:right; font-weight: bolder;">Total:</td>
                            <td style="font-weight: bolder; text-align: right;">{{ number_format($balance,2) }}</td>
                            <td style="text-align: center;font-weight: bolder;">---</td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                    <!-- /.box-body -->
                  </div>
                 </section>


<!-- Modal -->
<div class="modal fade" id="sellReports" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Invoice Details</h4>
      </div>
      <div class="modal-body" id="sellModalDetails">
        
                
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

//InitializeDate();
/*
function InitializeDate() {
    var date = new Date();
    var dd = date.getDate();             
    var mm = date.getMonth() + 1;
    var yyyy = date.getFullYear();

    var ToDate = yyyy+'-'+mm+'-'+dd;
    var FromDate = yyyy+'-'+(mm-1)+'-'+dd;
    $('#start_date').datepicker('setDate', FromDate);
    $('#end_date').datepicker('setDate', ToDate);
}*/

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
            window.open(route, '_blank');
        });

</script>
<script type="text/javascript">

  function sell_reports(sell_id) {

    let url = "{{ route('reports.sell-reports-ajax') }}";
    var _token=$("#_token").val();
    $.ajax({  
      type: "GET",
      url: url,
      data: { sell_id: sell_id,_token:_token},
      success: function (result) {
       $("#sellModalDetails").html(result);
       $("#sellReports").modal("show");
      }
    });
  }
</script>
@endsection
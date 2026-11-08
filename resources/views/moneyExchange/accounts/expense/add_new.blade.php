@extends('layout.master')
@section('money_class','menu-open')
@section('accounts_class','menu-open')
@section('expense_class','menu-open')
@section('expense_add','active')
@section('content')
<section class="content">
        <section class="content-header">
            @if(session()->has('suc'))
            <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('suc') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>
            @endif
            @if(session()->has('err'))
            <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('err') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>
            @endif           
            </section>
                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Expense Add</h3>
                    </div>
                    <div class="col-md-6" style="margin-left: -25px;">
                   <div class="box-body" style="width: 550px;">
                <table class="table table-bordered table-striped" style="margin-top: 20px;">
                        <thead>
                        <tr>
                            <th>SL</th>
                            <th>Expense</th>
                            <th>Cus</th>
                            <th>Narration</th>
                            <th>Voucher</th>
                            <th>Cash In</th>
                            <th>Add</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td></td>
                            <td>
                                <select name="" id="" class="js-example-basic-single">
                                    <option value="">...</option>
                                </select>
                            </td>
                            <td>
                                <select name="" id="" class="form-control select2">
                                    <option value="">...</option>
                                @foreach ($customers as $cus)
                                    <option value="{{ $cus->inv_cus_id }}">{{ $cus->inv_cus_name }}</option>
                                @endforeach
                                </select>
                            </td>
                            <td><input type="text" class="form-control"></td>
                            <td><input type="text" class="form-control"></td>
                            <td><input type="text" class="form-control"></td>
                            <td style="width: 64px;">
                                <button class="btn btn-success btn-xs"><i class="fa fa-plus"></i></button>
                                <button class="btn btn-danger btn-xs"><i class="fa fa-minus"></i></button>
                            </td>
                        </tr>
                        </tbody>
                        </table>
                   </div>
                   </div>
                   <div class="col-md-6">
                        <div class="box-body" style="width: 560px;">
                     <table class="table table-bordered table-striped" style="margin-top: 20px;">
                             <thead>
                             <tr>
                                 <th>SL</th>
                                 <th>Expense</th>
                                 <th>Sup</th>
                                 <th>Narration</th>
                                 <th>Voucher</th>
                                 <th style="width: 80px;">Cash Out</th>
                                 <th>Add</th>
                             </tr>
                             </thead>
                             <tbody>
                             <tr>
                                 <td></td>
                                 <td>
                                    <select name="" id="" class="js-example-basic-single">
                                        <option value="">...</option>
                                    </select>
                                 </td>
                                 <td>
                                    <select name="" id="" class="form-control select2">
                                        <option value="">...</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->inv_sup_id }}">{{ $sup->inv_sup_person }}</option>
                                    @endforeach
                                    </select>
                                 </td>
                                 <td><input type="text" class="form-control"></td>
                                 <td><input type="text" class="form-control"></td>
                                 <td><input type="text" class="form-control"></td>
                                 <td style="width: 64px;">
                                    <button class="btn btn-success btn-xs"><i class="fa fa-plus"></i></button>
                                    <button class="btn btn-danger btn-xs"><i class="fa fa-minus"></i></button>
                                </td>
                             </tr>
                             </tbody>
                             </table>
                        </div>
                        </div>
                  </div>
                  
                 </section>
@endsection
@section('custom_script')
<script type="text/javascript">
$(document).ready(function() {
    $('.js-example-basic-single').select2();
});
$(document).ready(function() {
    var table = $('#example6').DataTable( {
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true
    } );
} );
$(document).ready(function(){

$( "#opendate" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
        endDate: "today",
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});

</script>

<script type="text/javascript">
  function loadExpense() {  
    let cat_id = $("#category").val();
    var requestUrl="{{route('accounts.ajax-load_expense')}}";
    var _token = $("#_token").val();
    $.ajax({  
      type: "GET",
      url: requestUrl,
      data: { cat_id: cat_id,_token:_token},
      success: function (result) {
       $(".expense_name_div").html(result);
      }
    });
  }

  function loadAvailableBalanceOfBank() {
    let bank_id = $("#bank_id").val();
    var requestUrl="{{route('accounts.ajax-load-bank-balance')}}";
    var _token = $("#_token").val();
    //$("#_token").val();
    $.ajax({  
      type: "GET",
      url: requestUrl,
      data: { bank_id: bank_id,_token:_token},
      success: function (result) {
       $(".bank_balance_div").html(result);
      }
    });
  }

  $("#form-id").submit(function (event) {
let btn = $("#submit-button-id");
btn.prop('disabled', true);
setTimeout(function(){
btn.prop('disabled', false);
}, 5000);
return true;
});
  
</script>
@endsection

@section('custom_style')
<style type="text/css">
  .form-control::-webkit-inner-spin-button,
  .form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
.form-control{
    height: 25px;
}
.js-example-basic-single{
    width: 84px;
}
.select2-container--open .select2-dropdown--below{
    width: 185px !important;
}
</style>
@endsection
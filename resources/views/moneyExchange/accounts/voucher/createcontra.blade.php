@extends('layout.master')
@section('money_class','menu-open')
@section('accounts_class','menu-open')
@section('voucher_class','menu-open')
@section('contra_create','active')
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
               Contra Voucher
              </h1>       
            </section>
                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Add Contra Voucher</h3>
                    </div>
                    
                    <!-- /.box-header -->
                    <!-- form start -->
                   
                    <form action="{{route('accounts.create-contra')}}" method="post" class="form-horizontal" id="form-id">
                      <input type="hidden" name="_token" value="{{csrf_token()}}" id="_token">
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Bank:</label>
                          <div class="col-sm-6">
                            <select name="bank_id" required  value="{{old('bank_id')}}"  class="form-control select2" id="bank_id" onchange="loadAvailableBalanceOfBank()">
                              <option value="">Select A Bank</option>
                              
                              @foreach($banks as $bank)
                            
                              <option value="{{$bank->inv_abi_id}}">
                                {{$bank->bank_info['bank_name'] }} ({{$bank->inv_abi_account_no}})

                              </option>
                              @endforeach
                            </select>
                         
                          </div>
                          <div class="col-sm-3">
                            @if($cash<=500)
                            <span style="color:red; font-weight: bold;">Avaible Balance in Cash is {{$cash}} Tk </span>
                            @else
                            <span style="color:green; font-weight: bold;">Avaible Balance in Cash is {{$cash}} Tk </span>
                            @endif
                            </div><br>
                          <div class="col-sm-3 bank_balance_div"></div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Descriptions:</label>
                          <div class="col-sm-6">
                            <input type="text" name="description" autocomplete="off" value="{{ old('description') }}" class="form-control" id="inputEmail3" placeholder="Write Description Here" >
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Payment Type:</label>
                          <div class="col-sm-6">
                         <div class="custom-control custom-radio">
                          <input class="custom-control-input" type="radio" id="customRadio1" name="pay_type" value="1">
                          <label for="customRadio1" class="custom-control-label">Cash To Bank</label>
                        </div>

                         <div class="custom-control custom-radio">
                          <input class="custom-control-input" type="radio" id="customRadio1" name="pay_type" value="2">
                          <label for="customRadio1" class="custom-control-label"> Bank To Cash</label>
                        </div>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Transaction Date:</label>
                          <div class="col-sm-6">
                            <input type="text" name="trans_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ old('trans_date') }}" class="form-control" id="opendate" placeholder="Select A Date" >
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Amount:</label>
                          <div class="col-sm-6">
                            <input type="number" name="amount" autocomplete="off" value="{{ old('amount') }}" class="form-control" id="inputEmail3" placeholder="Write Amount Here" min="0">
                          </div>
                        </div>

                        

                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer">
                        <div class="col-sm-3">
                            <button type="submit" id="submit-button-id" class="btn btn-info pull-right">Add</button>
                         </div>
                      </div>
                      <!-- /.box-footer -->
                  <!--   {{ Form::close() }} -->
                </form>
                  </div>

                 </section>
@endsection
@section('custom_script')
<script type="text/javascript">

$(document).ready(function(){

$( "#opendate" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
        endDate:"today",
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});

</script>
<script type="text/javascript">
 

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
</style>
@endsection
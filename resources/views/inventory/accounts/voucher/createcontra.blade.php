@extends('layouts.master')
@section('title','Contra')
@section('inventory_class','active')
@section('accounts_class','active')
@section('voucher_class','active')
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
          

                    <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Add Contra Voucher</h3>
                        </div>
                    </div>

                  <div class="panel-body" >

                    
                    <!-- /.box-header -->
                    <!-- form start -->
                   
                    <form action="{{route('accounts.create-contra')}}" method="post" class="form-horizontal" id="form-id">
                      <input type="hidden" name="_token" value="{{csrf_token()}}" id="_token">
                      <div class="box-body">
                        {{-- Payment Type first: it decides which bank fields are needed --}}
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Payment Type:</label>
                          <div class="col-sm-6">
                         <div class="custom-control custom-radio" style="display: inline-block; margin-right: 25px;">
                          <input class="custom-control-input" type="radio" id="customRadio1" name="pay_type" value="1" required {{ old('pay_type') == 1 ? 'checked' : '' }}>
                          <label for="customRadio1" class="custom-control-label">Cash To Bank</label>
                        </div>

                         <div class="custom-control custom-radio" style="display: inline-block; margin-right: 25px;">
                          <input class="custom-control-input" type="radio" id="customRadio2" name="pay_type" value="2" {{ old('pay_type') == 2 ? 'checked' : '' }}>
                          <label for="customRadio2" class="custom-control-label"> Bank To Cash</label>
                        </div>

                         <div class="custom-control custom-radio" style="display: inline-block;">
                          <input class="custom-control-input" type="radio" id="customRadio3" name="pay_type" value="3" {{ old('pay_type') == 3 ? 'checked' : '' }}>
                          <label for="customRadio3" class="custom-control-label"> Bank To Bank</label>
                        </div>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="bank_id" class="col-sm-2 control-label" id="bank_label">Bank:</label>
                          <div class="col-sm-6">
                            <select name="bank_id" required  value="{{old('bank_id')}}"  class="form-control select2" id="bank_id" onchange="loadAvailableBalanceOfBank(); refreshToBankOptions(); checkSameBank();">
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
                            <div class="bank_balance_div"></div>
                          </div>
                        </div>
                        {{-- To Bank: only for Bank To Bank --}}
                        <div class="form-group" id="to_bank_group" style="display: none;">
                          <label for="to_bank_id" class="col-sm-2 control-label">To Bank:</label>
                          <div class="col-sm-6">
                            <select name="to_bank_id" class="form-control select2" id="to_bank_id" style="width: 100%;" onchange="loadToBankBalance(); checkSameBank();">
                              <option value="">Select A Bank</option>
                              @foreach($banks as $bank)
                              <option value="{{$bank->inv_abi_id}}" {{ old('to_bank_id') == $bank->inv_abi_id ? 'selected' : '' }}>
                                {{$bank->bank_info['bank_name'] }} ({{$bank->inv_abi_account_no}})
                              </option>
                              @endforeach
                            </select>
                            <small class="text-danger" id="same_bank_msg" style="display: none;">From Bank and To Bank can't be the same.</small>
                          </div>
                          <div class="col-sm-3 to_bank_balance_div"></div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Descriptions:</label>
                          <div class="col-sm-6">
                            <input type="text" name="description" autocomplete="off" value="{{ old('description') }}" class="form-control" id="inputEmail3" placeholder="Write Description Here" >
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Transaction Date:</label>
                          <div class="col-sm-6">
                            <input type="text" name="trans_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ old('trans_date', date('Y-m-d')) }}" class="form-control" id="opendate" placeholder="Select A Date" >
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Amount:</label>
                          <div class="col-sm-6">
                            <input type="number" name="amount" autocomplete="off" value="{{ old('amount') }}" class="form-control" id="inputEmail3" placeholder="Write Amount Here" min="0">
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="service_charge" class="col-sm-2 control-label">Service Charge:</label>
                          <div class="col-sm-6">
                            <input type="number" step="0.01" name="service_charge" autocomplete="off" value="{{ old('service_charge') }}" class="form-control" id="service_charge" placeholder="Optional" min="0">
                            <small class="text-muted" id="service_charge_help">Cut from the selected bank as a separate transaction.</small>
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
       dateFormat: 'yy-mm-dd', // the default dd/mm/yy is read as mm/dd by the server
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

  // Bank To Bank: "Bank" becomes the From Bank and a To Bank is needed
  function togglePayType() {
    let bankToBank = $("input[name=pay_type]:checked").val() == 3;
    $("#bank_label").text(bankToBank ? 'From Bank:' : 'Bank:');
    $("#to_bank_group").toggle(bankToBank);
    $("#to_bank_id").prop('required', bankToBank);
    $("#service_charge_help").text(bankToBank
      ? 'Cut from the From Bank as a separate transaction.'
      : 'Cut from the selected bank as a separate transaction.');
    checkSameBank();
  }

  function checkSameBank() {
    let same = $("input[name=pay_type]:checked").val() == 3
      && $("#bank_id").val() !== '' && $("#bank_id").val() === $("#to_bank_id").val();
    $("#same_bank_msg").toggle(same);
    return !same;
  }

  // To Bank never lists the bank chosen as From Bank (copied before select2 touches the options)
  var toBankOptions = $("#to_bank_id option").clone().removeAttr('data-select2-id');

  function refreshToBankOptions() {
    let fromBank = $("#bank_id").val();
    let selected = $("#to_bank_id").val();
    $("#to_bank_id").empty().append(toBankOptions.clone().filter(function () {
      return this.value === '' || this.value !== fromBank;
    }));
    $("#to_bank_id").val(selected && selected !== fromBank ? selected : '').trigger('change.select2');
    loadToBankBalance();
  }

  function loadToBankBalance() {
    let bank_id = $("#to_bank_id").val();
    if (!bank_id) {
      $(".to_bank_balance_div").html('');
      return;
    }
    $.ajax({
      type: "GET",
      url: "{{route('accounts.ajax-load-bank-balance')}}",
      data: { bank_id: bank_id },
      success: function (result) {
        $(".to_bank_balance_div").html(result);
      }
    });
  }

  $("input[name=pay_type]").on('change', togglePayType);
  $(document).ready(function () {
    refreshToBankOptions();
    togglePayType();
  });

  $("#form-id").submit(function (event) {
if (!checkSameBank()) {
  event.preventDefault();
  return false;
}
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
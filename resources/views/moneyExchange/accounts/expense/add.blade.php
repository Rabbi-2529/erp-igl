@extends('layout.master')
@section('inventory_class','menu-open')
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
                   
                    <!-- /.box-header -->
                    <!-- form start -->
                    
                    <form action="{{route('accounts.expense-cost-add')}}" method="post" class="form-horizontal" id="form-id">
                   <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Bank :</label>
                          <div class="col-sm-6">
                            <select name="bank" required  class="form-control select2" id="bank_id" onchange="loadAvailableBalanceOfBank()">
                              <option value="">Select A Bank</option>
                              
                              @foreach($banks as $inv_bank)
                              <option  value="{{$inv_bank->inv_abi_id}}">
                                {{$inv_bank->bank_info['bank_name']}} ({{ $inv_bank->inv_abi_account_name }})
                              </option>
                              @endforeach
                            </select>
                         
                          </div>
                          <div class="col-sm-3 bank_balance_div">
                           
                          </div>
                        </div>
                    <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Expenses Category:</label>
                            <div class="col-sm-6">
                                <select name="expense_id" required class="form-control select2" id="category" onchange="loadExpense()">
                                <option value="">Select A Category</option>
                                
                                @foreach($expenses as $category)
                                <option value="{{$category->inv_acc_exp_cat_category_id}}">{{$category->inv_acc_exp_cat_category_name}}</option>
                                @endforeach
                                </select>
                            </div>
                            {{-- <div class="col-sm-3">
                                @if($cash<=500)
                                <span style="color:red; font-weight: bold;">Avaible Balance in Cash is {{$cash}} Tk </span>
                                @else
                                <span style="color:green; font-weight: bold;">Avaible Balance in Cash is {{$cash}} Tk </span>
                                @endif
                                </div> --}}
                            </div>
                            <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Expense:</label>
                            <div class="col-sm-6 expense_name_div"> 
                                <select name="expense_id" required   class="form-control select2" id="expense_id" >
                                <option value="">Choose A Category First</option>
                                </select>
                            </div>
                            </div>
                        
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Transaction Date:</label>
                          <div class="col-sm-6">
                           <input type="text" name="transaction" data-date-format="yyyy-mm-dd" autocomplete="off"  class="form-control" id="opendate" placeholder="Select A Date" >
                          </div>
                        </div>
                        {{-- <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Expenses Category:</label>
                          <div class="col-sm-6">
                            <select name="category_id" required class="form-control select2" id="category" onchange="loadExpense()">
                              <option value="">Select A Category</option>
                              
                              @foreach($categories as $category)
                              <option value="{{$category->inv_acc_exp_cat_category_id}}">{{$category->inv_acc_exp_cat_category_name}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div> --}}
                        {{-- <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Expense:</label>
                          <div class="col-sm-6 expense_name_div"> 
                             <select name="expense_id" required   class="form-control select2" id="expense_id" >
                              <option value="">Choose A Category First</option>
                            </select>
                          </div>
                        </div> --}}

                         <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Reference:</label>
                          <div class="col-sm-6">
                            <input type="text" name="reference" autocomplete="off" value="{{ old('reference') }}" class="form-control" id="inputEmail3" placeholder="Enter a Reference" required>
                          </div>
                        </div>
                         <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Amount:</label>
                          <div class="col-sm-6">
                            <input type="number" name="amount" autocomplete="off" value="{{ old('amount') }}" class="form-control" id="inputEmail3" placeholder="Enter Amount" min="0">
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
</style>
@endsection
@extends('layouts.master')
@section('title','Make Transaction')
@section('inventory_class','active')
@section('supplier_class','active')
@section('inv_supplier_acc_class','active')
@section('supplier_deposit_withdraw','active')
@section('content')
<section class="content">
        <section class="content-header">
            
              <h1>
             Supplier's Deposit/Withdraw
              </h1>       
            </section>
            <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Make Transaction</h4>
                </div>
            </div>
            <div class="panel-body">
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
                    <!-- /.box-header -->
                    <!-- form start -->

                    
                @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif
                   
                    <form action="{{route('inventory.supplier.accounts.deposit-withdraw')}}" method="post" class="form-horizontal">
                      <input type="hidden" name="_token" value="{{csrf_token()}}" id="_token">
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Supplier:</label>
                          <div class="col-sm-6">
                            <select name="supplier_id" required  value="{{old('supplier_id')}}"  class="form-control select2" id="supplier_id" onchange="loadAvailableBalanceOfSupplier()">
                              <option value="">Select A Supplier</option>
                              
                              @foreach($inv_suppliers as $inv_supplier)
                              <option value="{{$inv_supplier->inv_sup_id}}">
                                {{$inv_supplier->inv_sup_person }} 
                              ({{$inv_supplier->inv_sup_com_name}})
                              </option>
                              @endforeach
                            </select>
                         
                          </div>
                          <div class="col-sm-3 supplier_balance_div"></div>
                        </div>
                         
                     <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Transaction Type:</label>
                      <div class="col-sm-6">
                        <label class="radio-inline">
                            <input type="radio" name="trans_type" value="2" required>Credit
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="trans_type" value="3" required>Debit
                          </label>
                    </div>
                  </div>
                        
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Transaction Date:</label>
                          <div class="col-sm-6">
                            <input type="text" name="trans_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ old('trans_date') }}" class="form-control" id="opendate" placeholder="Select A Date" required>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Reference:</label>
                          <div class="col-sm-6">
                            <input type="text" name="reference" autocomplete="off" value="{{ old('reference') }}" class="form-control" id="inputEmail3" placeholder="Write Reference Here" >
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Amount:</label>
                          <div class="col-sm-6">
                            <input type="number" name="amount" autocomplete="off" value="{{ old('amount') }}" class="form-control" id="inputEmail3" placeholder="Write Amount Here" required min="0">
                          </div>
                        </div>

                        

                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer">
                        <div class="col-sm-3">
                            <button type="submit" id="" class="btn btn-info pull-right"> Submit</button>
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
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});

</script>

<script type="text/javascript">
  function loadAvailableBalanceOfSupplier() {
    let supplier_id = $("#supplier_id").val();
  
    
    var requestUrl="{{route('accounts.ajax-load-supplier-balance')}}";
  
   var _token=$("#_token").val();

    $.ajax({  
      type: "GET",
      url: requestUrl,
      data: { supplier_id: supplier_id,_token:_token},
      success: function (result) {
       $(".supplier_balance_div").html(result);
      }
    });
  }

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
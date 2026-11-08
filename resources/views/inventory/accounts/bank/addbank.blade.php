@extends('layouts.master')
@section('title','Add Bank')
@section('inventory_class','active')
@section('accounts_class','active')
@section('bank_class','active')
@section('bank_add','active')
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
               Bank Add
              </h1>       
            </section>
            <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Add Bank</h3>
                        </div>
                    </div>

                  <div class="panel-body" >
                  
                    <!-- /.box-header -->
                    <!-- form start -->
                    
                    <form action="{{route('accounts.add-bank')}}" method="post" class="form-horizontal">
                      @csrf
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Bank Name:</label>
                          <div class="col-sm-6">
                            <select name="bank_id" required  value="{{old('bank_id')}}"  class="form-control select2" id="" >
                              <option value="">Select A Bank</option>
                              
                              @foreach($banks as $inv_bank)
                              <option {{ (old('bank_id') == $inv_bank->id)?'selected':'' }} value="{{$inv_bank->id}}">{{$inv_bank->bank_name}}</option>
                              @endforeach
                            </select>
                         
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Branch Name:</label>
                          <div class="col-sm-6">
                            <input type="text" name="branch_name" autocomplete="off" value="{{ old('branch_name') }}" class="form-control" id="" placeholder="Enter Branch Name" >
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Account Name:</label>
                          <div class="col-sm-6">
                            <input type="text" name="acc_name" style="text-transform: uppercase;" autocomplete="off" value="{{ old('acc_name') }}" class="form-control" id="" placeholder="Enter Account Name" >
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Account No:</label>
                          <div class="col-sm-6">
                            <input type="text" name="acc_no" autocomplete="off" value="{{ old('acc_no') }}" class="form-control" id="" placeholder="Enter Account Number" >
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Open Date:</label>
                          <div class="col-sm-6">
                            <input type="text" name="opendate" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ old('opendate') }}" class="form-control" id="opendate" placeholder="Select A Date" >
                          </div>
                        </div>

                        <!-- <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Opening Balance:</label>
                          <div class="col-sm-6">
                            <input type="number" name="openbalance"  autocomplete="off" value="{{ old('openbalance') }}" class="form-control"  placeholder="Enter Opening Balance Here" >
                          </div>
                        </div> -->
                        

                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer">
                        <div class="col-sm-3">
                            <button type="submit" id="" class="btn btn-info pull-right">Add</button>
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
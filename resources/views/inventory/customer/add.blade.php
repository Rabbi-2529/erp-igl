@extends('layouts.master')
@section('title','Add Customer')
@section('inventory_class','active')
@section('customer_class','active')
@section('inv_customer_class','active')
@section('customer_add','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('cus_add'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('cus_add') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('cus_error'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('cus_error') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
          <h1>
            Customer
          </h1>
        </section>
        <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Add Customer</h4>
                </div>
            </div>

            <div class="panel-body">
                <!-- /.box-header -->
                <!-- form start -->
                {{Form::open(['action' => 'Inventory\InvCustomerController@customer_add_submit', 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form', 'name' => 'customer_create_form'])}}
                  <div class="box-body">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Mobile</label>
                        <div class="col-sm-6">
                          <input type="number" name="mobile" autocomplete="off" value="{{ old('mobile') }}" class="form-control" id="cus_mobile" placeholder="Enter Customer Mobile Number" onkeyup="check_mobile_existence()" required>
                          <p class="text-danger" id="cus_exist_message"></p>
                        </div>
                        <p class="text-success" id="cus_exist_message_available"></p>
                        <p class="text-danger" id="cus_exist_message_not_available"></p>
                        @if($errors->has('mobile'))
                            <p class="text-danger">{{ $errors->first('mobile') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Customer Name</label>
                      <div class="col-sm-6">
                        <input type="text" name="name" autocomplete="off" value="{{ old('name') }}" class="form-control" id="customer_name" placeholder="Enter Customer Name" required>
                      </div>
                      @if($errors->has('name'))
                          <p class="text-danger">{{ $errors->first('name') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Company Name</label>
                      <div class="col-sm-6">
                        <input type="text" name="com_name" autocomplete="off" value="{{ old('com_name') }}" class="form-control" id="com_name" placeholder="Enter Customer Company Name">
                      </div>
                      @if($errors->has('com_name'))
                          <p class="text-danger">{{ $errors->first('com_name') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">E-mail</label>
                      <div class="col-sm-6">
                        <input type="email" name="email" id="customer_email" autocomplete="off" value="{{ old('email') }}" class="form-control" placeholder="Enter Customer E-Mail" >
                      </div>
                      @if($errors->has('mobile'))
                          <p class="text-danger">{{ $errors->first('mobile') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Address</label>
                      <div class="col-sm-6">
                        <input type="text" name="address" id="customer_address" autocomplete="off" value="{{ old('address') }}" class="form-control" placeholder="Enter Customer Address">
                      </div>
                      @if($errors->has('phone'))
                          <p class="text-danger">{{ $errors->first('phone') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Website</label>
                        <div class="col-sm-6">
                          <input type="text" name="website" autocomplete="off" value="{{ old('website') }}" class="form-control" placeholder="Enter Customer Website">
                        </div>
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Customer Type</label>
                      <div class="col-sm-6">
                          <label class="radio-inline">
                              <input type="radio" {{ (old('bal_type')==1)?'checked':'' }} name="type" value="1">Regular
                          </label>
                          <label class="radio-inline">
                              <input type="radio" {{ (old('bal_type')==2)?'checked':'' }} name="type" value="2">Irregular
                          </label>
                          <label class="radio-inline">
                              <input type="radio" {{ (old('bal_type')==3)?'checked':'' }} name="type" value="3">Corporate
                          </label>
                      </div>
                  </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Customer Balance</label>
                      <div class="col-sm-3">
                        <input type="number" name="balance" autocomplete="off" value="{{ old('balance') }}" class="form-control" placeholder="Enter Customer Balance">
                      </div>
                      <div class="col-sm-3">
                        <label class="radio-inline">
                            <input type="radio" {{ (old('bal_type')==1)?'checked':'' }} name="bal_type" value="1">Debit
                          </label>
                          <label class="radio-inline">
                              <input type="radio" {{ (old('bal_type')==2)?'checked':'' }} name="bal_type" value="2">Credit
                          </label>
                      </div>
                    </div>                                     
                  </div>
                  <!-- /.box-body -->
                  <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" id="" class="btn btn-info pull-right">Create</button>
                     </div>
                  </div>
                  <!-- /.box-footer -->
                {{ Form::close() }}
              </div>
             </section>
@endsection
@section('custom_script')
<script>
    
    var error_mobile =  true;
    function check_mobile_existence() {
      var mobile_num_length = $("#cus_mobile").val().length;
      if( mobile_num_length<11 || mobile_num_length>11 ){
        $("#cus_exist_message_not_available").html("Invalid Mobile Number");
        $("#cus_exist_message_available").html("");
        error_mobile = true;
      }else{

        var link = "{{ route('customer.customer_exist') }}";
        var mobileno = $("#cus_mobile").val();
        $.ajax({
          type: "GET",
          url: link,
          data: { mobile: mobileno},
          success: function (result) {
            if(result == 200) {
              $("#cus_exist_message_not_available").html("");
              $("#cus_exist_message_available").html("Mobile Number Available");
              error_mobile = false;
            } else if(result == 405) {
              $("#cus_exist_message_not_available").html("Already Exist!");
              $("#cus_exist_message_available").html("");
              error_mobile = true;
            } else if(typeof result != "undefined") {
              $("#cus_exist_message_not_available").html("");
              $("#cus_exist_message_available").html("Mobile Number Available");
              $("#com_name").val(result.qb_company_name);
              $("#customer_name").val(result.qb_name);
              $("#customer_email").val(result.qb_email);
              $("#customer_address").val(result.qb_address);
              error_mobile = false;
            }
          }
        });
      }
    }

    $("#customer_create_form").submit(function () {
      if(error_mobile == true) {
        $("#cus_mobile").focus();
        return false;
      } else {
        return true;
      }
    });
    


</script>
@endsection

@section('custom_style')
<style>
  .form-control::-webkit-inner-spin-button,
  .form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
</style>
@endsection
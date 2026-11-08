@extends('layouts.master')
@section('title','Edit Customer')
@section('inventory_class','active')
@section('customer_class','active')
@section('inv_customer_class','active')
@section('customer_list','active')
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
          <h1>
            Customer
          </h1>
        </section>
        <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Edit Customer</h4>
                </div>
            </div>

            <div class="panel-body">
                <!-- /.box-header -->
                <!-- form start -->
                {{Form::open(['action' => ['Inventory\InvCustomerController@customer_edit',$cus_info->inv_cus_id], 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                  <div class="box-body">
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Customer Name</label>
                      <div class="col-sm-6">
                        <input type="text" name="name" autocomplete="off" value="{{ $cus_info->inv_cus_name }}" class="form-control" id="inputEmail3" placeholder="Enter Customer Name" required>
                      </div>
                      @if($errors->has('name'))
                          <p class="text-danger">{{ $errors->first('name') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Company Name</label>
                      <div class="col-sm-6">
                        <input type="text" name="com_name" autocomplete="off" value="{{ $cus_info->inv_cus_com_name }}" class="form-control" id="inputEmail3" placeholder="Enter Customer Company Name" required>
                      </div>
                      @if($errors->has('com_name'))
                          <p class="text-danger">{{ $errors->first('com_name') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Mobile</label>
                      <div class="col-sm-6">
                        <input type="number" name="mobile" autocomplete="off" value="{{ $cus_info->inv_cus_mobile }}" class="form-control" id="inputEmail3" placeholder="Enter Customer Mobile Number" required>
                      </div>
                      @if($errors->has('mobile'))
                          <p class="text-danger">{{ $errors->first('mobile') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">E-mail</label>
                      <div class="col-sm-6">
                        <input type="email" name="email" autocomplete="off" value="{{ $cus_info->inv_cus_email }}" class="form-control" id="inputEmail3" placeholder="Enter Customer E-Mail" >
                      </div>
                      @if($errors->has('mobile'))
                          <p class="text-danger">{{ $errors->first('mobile') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Address</label>
                      <div class="col-sm-6">
                        <input type="text" name="address" autocomplete="off" value="{{ $cus_info->inv_cus_address }}" class="form-control" id="inputEmail3" placeholder="Enter Customer Address">
                      </div>
                      @if($errors->has('phone'))
                          <p class="text-danger">{{ $errors->first('phone') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Website</label>
                        <div class="col-sm-6">
                          <input type="text" name="website" autocomplete="off" value="{{ $cus_info->inv_cus_website }}" class="form-control" id="inputEmail3" placeholder="Enter Customer Website">
                        </div>
                        @if($errors->has('email'))
                          <p class="text-danger">{{ $errors->first('email') }}</p>
                        @endif
                    </div>                                  
                  </div>
                  <!-- /.box-body -->
                  <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" id="" class="btn btn-info pull-right">UPDATE</button>
                     </div>
                  </div>
                  <!-- /.box-footer -->
                {{ Form::close() }}
              </div>
             </section>
@endsection
@section('custom_script')
<script>
      //Flat red color scheme for iCheck
      $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
      checkboxClass: 'icheckbox_flat-green',
      radioClass   : 'iradio_flat-green'
    })

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
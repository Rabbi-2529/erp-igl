@extends('layouts.master')
@section('title','Add Supplier')
@section('inventory_class','active')
@section('supplier_class','active')
@section('inv_supplier_class','active')
@section('supplier_add','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('add_sup'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('add_sup') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('sup_inv_err'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('sup_inv_err') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
          <h1>
            Supplier
          </h1>
        </section>
        <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Add Supplier</h4>
                </div>
            </div>
            <div class="panel-body">
                <!-- /.box-header -->
                <!-- form start -->
                {{Form::open(['action' => 'Inventory\InvSupplierController@add_supplier_submit', 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                  <div class="box-body">
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Company Name
                        <span class="text-danger">*</span>
                      </label>
                      <div class="col-sm-6">
                        <input type="text" name="company" autocomplete="off" value="{{ old('company') }}" class="form-control" id="inputEmail3" placeholder="Enter Supplier Company Name" >
                      </div>
                      @if($errors->has('company'))
                          <p class="text-danger">{{ $errors->first('company') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Address
                          <span class="text-danger">*</span>
                      </label>
                      <div class="col-sm-6">
                        <input type="text" name="address" autocomplete="off" value="{{ old('address') }}" class="form-control" id="inputEmail3" placeholder="Enter Supplier Address" >
                      </div>
                      @if($errors->has('address'))
                          <p class="text-danger">{{ $errors->first('address') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Person Name
                          <span class="text-danger">*</span>
                      </label>
                      <div class="col-sm-6">
                        <input type="text" name="person" autocomplete="off" value="{{ old('person') }}" class="form-control" id="inputEmail3" placeholder="Enter Supplier Perspn">
                      </div>
                      @if($errors->has('person'))
                          <p class="text-danger">{{ $errors->first('person') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Mobile
                          <span class="text-danger">*</span>
                      </label>
                      <div class="col-sm-6">
                        <input type="number" name="mobile" autocomplete="off" value="{{ old('mobile') }}" class="form-control" id="inputEmail3" placeholder="Enter Supplier Mobile" >
                      </div>
                      @if($errors->has('mobile'))
                          <p class="text-danger">{{ $errors->first('mobile') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Phone</label>
                      <div class="col-sm-6">
                        <input type="number" name="phone" autocomplete="off" value="{{ old('phone') }}" class="form-control" id="inputEmail3" placeholder="Enter Supplier Phone">
                      </div>
                      @if($errors->has('phone'))
                          <p class="text-danger">{{ $errors->first('phone') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">E-Mail</label>
                        <div class="col-sm-6">
                          <input type="email" name="email" autocomplete="off" value="{{ old('email') }}" class="form-control" id="inputEmail3" placeholder="Enter Supplier E-Mail">
                        </div>
                        @if($errors->has('email'))
                          <p class="text-danger">{{ $errors->first('email') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Website</label>
                        <div class="col-sm-6">
                          <input type="text" name="website" autocomplete="off" value="{{ old('website') }}" class="form-control" id="inputEmail3" placeholder="Enter Supplier Website">
                        </div>
                        @if($errors->has('website'))
                          <p class="text-danger">{{ $errors->first('website') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Complain Number</label>
                        <div class="col-sm-6">
                          <input type="number" name="complain" autocomplete="off" value="{{ old('complain') }}" class="form-control" id="inputEmail3" placeholder="Enter Complain Number">
                        </div>
                        
                    </div>
                   <!-- Material inline 1 -->
                   <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Supplier Type
                          <span class="text-danger">*</span>
                      </label>
                      <div class="col-sm-6">
                        <label class="radio-inline">
                          <input type="radio" name="type" value="1">Regular
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="type" value="2">Irregular
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="type" value="3">Importer
                        </label>
                      </div>
                      @if($errors->has('type'))
                        <p class="text-danger">{{ $errors->first('type') }}</p>
                      @endif
                  </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Open Due Balance</label>
                        <div class="col-sm-3">
                          <input type="number" name="balance" autocomplete="off" value="{{ old('balance') }}" class="form-control" id="inputEmail3" placeholder="Open Due Balance">
                        </div>
                        <div class="col-sm-3">
                            <label class="radio-inline">
                                <input type="radio" {{ (old('bal_type')==1)?'checked':'' }} name="bal_type" value="1">Debit
                              </label>
                              <label class="radio-inline">
                                  <input type="radio" {{ (old('bal_type')==2)?'checked':'' }} name="bal_type" value="2">Credit
                              </label>
                        </div>
                        @if($errors->has('balance'))
                          <p class="text-danger">{{ $errors->first('balance') }}</p>
                        @endif
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

@section('custom_style')
<style>
  .form-control::-webkit-inner-spin-button,
  .form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
</style>
@endsection
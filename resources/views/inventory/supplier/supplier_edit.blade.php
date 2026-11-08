@extends('layouts.master')
@section('title','Update Supplier')
@section('inventory_class','active')
@section('supplier_class','active')
@section('inv_supplier_class','active')
@section('supplier_list','active')
@section('content')

    <section class="content-header">
        <h1>Supplier</h1>
    </section>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Update Supplier</h4>
                    </div>
                </div>
                      <div class="panel-body">
                <!-- form start -->
                {{ Form::open(['action' => ['Inventory\InvSupplierController@sup_edit', $sup->inv_sup_id], 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'customer_create_form']) }}
                <div class="box-body">
                    <div class="form-group">
                        <label for="company" class="col-sm-2 control-label">Company Name</label>
                        <div class="col-sm-6">
                            <input type="text" name="company" autocomplete="off" value="{{ $sup->inv_sup_com_name }}" class="form-control" id="company" placeholder="Enter Supplier Company Name">
                        </div>
                        @if($errors->has('company'))
                            <p class="text-danger">{{ $errors->first('company') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="address" class="col-sm-2 control-label">Address</label>
                        <div class="col-sm-6">
                            <input type="text" name="address" autocomplete="off" value="{{ $sup->inv_sup_address }}" class="form-control" id="address" placeholder="Enter Supplier Address">
                        </div>
                        @if($errors->has('address'))
                            <p class="text-danger">{{ $errors->first('address') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="person" class="col-sm-2 control-label">Person Name</label>
                        <div class="col-sm-6">
                            <input type="text" name="person" autocomplete="off" value="{{ $sup->inv_sup_person }}" class="form-control" id="person" placeholder="Enter Supplier Person">
                        </div>
                        @if($errors->has('person'))
                            <p class="text-danger">{{ $errors->first('person') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="mobile" class="col-sm-2 control-label">Mobile</label>
                        <div class="col-sm-6">
                            <input type="number" name="mobile" autocomplete="off" value="{{ $sup->inv_sup_mobile }}" class="form-control" id="mobile" placeholder="Enter Supplier Mobile">
                        </div>
                        @if($errors->has('mobile'))
                            <p class="text-danger">{{ $errors->first('mobile') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="phone" class="col-sm-2 control-label">Phone</label>
                        <div class="col-sm-6">
                            <input type="number" name="phone" autocomplete="off" value="{{ $sup->inv_sup_phone }}" class="form-control" id="phone" placeholder="Enter Supplier Phone">
                        </div>
                        @if($errors->has('phone'))
                            <p class="text-danger">{{ $errors->first('phone') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-2 control-label">E-Mail</label>
                        <div class="col-sm-6">
                            <input type="email" name="email" autocomplete="off" value="{{ $sup->inv_sup_email }}" class="form-control" id="email" placeholder="Enter Supplier E-Mail">
                        </div>
                        @if($errors->has('email'))
                            <p class="text-danger">{{ $errors->first('email') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="website" class="col-sm-2 control-label">Website</label>
                        <div class="col-sm-6">
                            <input type="text" name="website" autocomplete="off" value="{{ $sup->inv_sup_website }}" class="form-control" id="website" placeholder="Enter Supplier Website">
                        </div>
                        @if($errors->has('website'))
                            <p class="text-danger">{{ $errors->first('website') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="complain" class="col-sm-2 control-label">Complain Number</label>
                        <div class="col-sm-6">
                            <input type="number" name="complain" autocomplete="off" value="{{ $sup->inv_sup_complain_num }}" class="form-control" id="complain" placeholder="Enter Complain Number">
                        </div>
                        @if($errors->has('complain'))
                            <p class="text-danger">{{ $errors->first('complain') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Supplier Type</label>
                        <div class="col-sm-6">
                            <label class="radio-inline">
                                <input type="radio" name="type" {{ ($sup->inv_sup_type == 1) ? 'checked' : '' }} value="1">Regular
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="type" {{ ($sup->inv_sup_type == 2) ? 'checked' : '' }} value="2">Irregular
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="type" {{ ($sup->inv_sup_type == 3) ? 'checked' : '' }} value="3">Importer
                            </label>
                        </div>
                        @if($errors->has('type'))
                            <p class="text-danger">{{ $errors->first('type') }}</p>
                        @endif
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-info pull-right">Update</button>
                    </div>
                </div>
                <!-- /.box-footer -->
                {{ Form::close() }}
            </div>
        </div>
    </div>
    </div>

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
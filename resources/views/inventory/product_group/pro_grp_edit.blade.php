@extends('layouts.master')
@section('title', 'Update Product Group')
@section('inventory_class','active')
@section('product_class','active')
@section('inv_pro_grp','active')
@section('pro_grp_list','active')
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                <h3 class="box-title">Update Product Group</h3>
                </div>
            </div>
        @if(session()->has('add_sup'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('add_sup') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
    
             
        <div class="panel-body">
                <!-- /.box-header -->
                <!-- form start -->
                {{Form::open(['action' => ['Inventory\InvProductController@pro_grp_edit',$pro_grp->inv_pro_grp_id], 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                  <div class="box-body">
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Group Name
                        <span class="text-danger">*</span>
                      </label>
                      <div class="col-sm-6">
                        <input type="text" name="type" autocomplete="off" value="{{ $pro_grp->inv_pro_grp_name }}" class="form-control" id="inputEmail3" placeholder="Enter Supplier Company Name" >
                      </div>
                      @if($errors->has('type'))
                          <p class="text-danger">{{ $errors->first('type') }}</p>
                      @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Group Status</label>
                        <div class="col-sm-6">
                          <label class="radio-inline">
                            <input type="radio" name="status" {{ ($pro_grp->inv_pro_grp_status==1)?'checked':'' }} value="1">Active
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="status" {{ ($pro_grp->inv_pro_grp_status==0)?'checked':'' }} value="0">Inactive
                            </label>
                        </div>
                        @if($errors->has('status'))
                          <p class="text-danger">{{ $errors->first('status') }}</p>
                        @endif
                    </div>
                  </div>
                  <!-- /.box-body -->
                  <!-- <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" id="" class="btn btn-info pull-right">Update</button>
                     </div>
                  </div> -->
                  <div class="d-flex">
                      <div class="col-sm-2 ">

                      </div>
                        <div class="col-sm-2 ">
                            <button type="submit" id="" class="btn btn-info">Update</button>
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
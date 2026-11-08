@extends('layouts.master')
@section('inventory_class','menu-open')
@section('supplier_class','menu-open')
@section('supplier_product','menu-open')
@section('list_sup_pro','active')
@section('content')
<section class="content">
    <section class="content-header">
          <h1>
            Supplier Product
          </h1>
        </section>
               <div class="box">
                <div class="box-header with-border">
                  <h3 class="box-title">Update Supplier Product</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                {{Form::open(['action' => ['Inventory\InvSupplierController@supplier_pro_edit',$sup_edit->inv_sup_pro_id], 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                  <div class="box-body">
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Supplier</label>
                      <div class="col-sm-6">
                          <select name="supplier" id="" class="form-control select2">
                              <option value="">Select One</option>
                              @foreach ($suppliers as $sup)
                               <option value="{{ $sup->inv_sup_id }}" {{ ($sup->inv_sup_id==$sup_edit->inv_sup_pro_sup_id)?'selected':'' }}>
                                    {{ $sup->inv_sup_com_name }}
                               </option>   
                              @endforeach
                          </select>
                      </div>
                      @if($errors->has('supplier'))
                          <p class="text-danger">{{ $errors->first('supplier') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Product</label>
                      <div class="col-sm-6">
                          <select name="product" id="" class="form-control select2">
                              <option value="">Select One</option>
                              @foreach ($products as $pro)
                              <option value="{{ $pro->inv_pro_det_id }}" {{ ($pro->inv_pro_det_id==$sup_edit->inv_sup_pro_pro_id)?'selected':'' }}>
                                {{ $pro->inv_pro_det_pro_id }}
                              </option>    
                              @endforeach
                          </select>
                      </div>
                      @if($errors->has('product'))
                          <p class="text-danger">{{ $errors->first('product') }}</p>
                      @endif
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Supplier Type</label>
                        <div class="col-sm-6">
                          <label class="radio-inline">
                            <input type="radio" name="status" {{ ($sup_edit->inv_sup_pro_status==1)?'checked':'' }} value="1">Active
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="status" {{ ($sup_edit->inv_sup_pro_status==0)?'checked':'' }} value="0">Inactive
                            </label>
                        </div>
                        @if($errors->has('status'))
                          <p class="text-danger">{{ $errors->first('status') }}</p>
                        @endif
                    </div>
                  </div>
                  <!-- /.box-body -->
                  <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" id="" class="btn btn-info pull-right">Update</button>
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
@extends('layouts.master')
@section('inventory_class','menu-open')
@section('supplier_class','menu-open')
@section('supplier_product','menu-open')
@section('add_sup_pro','active')
@section('content')
<section class="content">
        <section class="content-header">
            @if(session()->has('sup_pro'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('sup_pro') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if(session()->has('dup'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('dup') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
              <h1>
                Supplier Product
              </h1>
            </section>
                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Add Supplier Product</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open(['action' => 'Inventory\InvSupplierController@supplier_product_submit', 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Supplier</label>
                          <div class="col-sm-6">
                              <select name="supplier" id="" class="form-control select2">
                                  <option value="">Select Option</option>
                                  @foreach ($suppliers as $sup)
                                  <option value="{{ $sup->inv_sup_id }}" {{ (old('supplier')==$sup->inv_sup_id)?'selected':'' }}>
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
                                        <option value="">Select Option</option>
                                        @foreach ($products as $pro)
                                        <option value="{{ $pro->inv_pro_det_id }}" {{ (old('product')==$pro->inv_pro_det_id)?'selected':'' }}>
                                          {{ $pro->inv_pro_det_pro_id }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($errors->has('product'))
                                    <p class="text-danger">{{ $errors->first('product') }}</p>
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
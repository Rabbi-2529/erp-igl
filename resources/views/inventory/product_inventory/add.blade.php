@extends('layouts.master')
@section('inventory_class','menu-open')
@section('pro_inv_class','menu-open')
@section('inv_buy_class','menu-open')
@section('buy_add','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('pur_pro'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('pur_pro') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('invalid'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('invalid') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
          <h1>
            Purchase Product
          </h1>
        </section>
               <div class="box">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Product</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                {{Form::open(['action' => 'Inventory\ProductInventoryController@buy_product_submit', 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                  <div class="box-body">
                    <div class="col-md-8">
                        <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Supplier
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-10">
                                    <select name="supplier" id="" class="form-control select2" required>
                                        <option value="">Select One</option>
                                        @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->inv_sup_id }}">{{ $sup->inv_sup_com_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="text-center">
                                    @if($errors->has('supplier'))
                                        <p class="text-danger">{{ $errors->first('supplier') }}</p>
                                    @endif
                                </div>
                        </div>
                        <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Product
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-10">
                            <select name="product" id="" class="form-control select2" required>
                                <option value="">Select One</option>
                                @foreach ($products as $pro)
                                <option value="{{ $pro->inv_pro_det_id }}">{{ $pro->inv_pro_det_pro_id }}</option>  
                                @endforeach
                            </select>
                        </div>
                        <div class="text-center">
                            @if($errors->has('product'))
                                <p class="text-danger">{{ $errors->first('product') }}</p>
                            @endif
                        </div>
                        </div>
                        <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Issue Date
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" required data-date-format="yyyy-mm-dd" name="issue" autocomplete="off" value="{{ old('issue') }}" class="form-control" id="from" placeholder="Enter Issue Date">
                        </div>
                        <div class="text-center">
                            @if($errors->has('issue'))
                                <p class="text-danger">{{ $errors->first('issue') }}</p>
                            @endif
                        </div>
                        </div>
                        <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Quantity
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="number" required name="qty" autocomplete="off" value="{{ old('qty') }}" class="form-control" id="pro-qty" placeholder="Enter Quantity" >
                            <div class="text-center">
                                    @if($errors->has('qty'))
                                        <p class="text-danger">{{ $errors->first('qty') }}</p>
                                    @endif
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <input type="radio" id="warrenty" value="1" name="warrenty"> 
                            <label for="warrenty">Warrenty</label>
                            <input type="radio" required id="no_warrenty" value="0" name="warrenty" checked> 
                            <label for="no_warrenty">No Warrenty</label>
                        </div>
                        <div class="text-center">
                            @if($errors->has('warrenty'))
                                <p class="text-danger">{{ $errors->first('warrenty') }}</p>
                            @endif
                        </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Memo Number
                                    <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-10">
                            <input type="text" name="memo" required autocomplete="off" value="{{ old('memo') }}" class="form-control" id="" placeholder="Enter Memo Number">
                            </div>
                            <div class="text-center">
                                @if($errors->has('memo'))
                                <p class="text-danger">{{ $errors->first('memo') }}</p>
                                @endif
                            </div>
                        </div>
                    <!-- Material inline 1 -->
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Expired Date</label>
                            <div class="col-sm-10">
                            <input type="text" data-date-format="yyyy-mm-dd" name="expired" autocomplete="off" value="{{ old('expire') }}" class="form-control" id="from2" placeholder="Enter Expired Date">
                            </div>
                            <div class="text-center">
                                @if($errors->has('expired'))
                                <p class="text-danger">{{ $errors->first('expired') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group all-product-sl-no-main-wrapper" style="display:none;">
                            <label for="inputEmail3" class="col-sm-12 text-center">Product SL No</label>
                            <div class="col-sm-12">
                                <div id="all-product-sl-no">
                                        
                                </div>
                            </div>
                        </div>
                        
                    </div>
                  </div>
                  <!-- /.box-body -->
                  <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" id="" class="btn btn-info pull-right">Add</button>
                     </div>
                  </div>
                  <!-- /.box-footer -->
                {{ Form::close() }}
              </div>
             </section>


             <div class="hidden product-sl-no-input">
                    <input type="number" required name="pro_sl_no[]" autocomplete="off" value="" class="form-control product-warrenty-sl" id="" placeholder="SL No." >
             </div>
@endsection

@section('custom_style')
<style>
  .form-control::-webkit-inner-spin-button,
  .form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
    .all-product-sl-no-main-wrapper {
        border: 2px solid #ddd;
        padding: 7px;
        height: 350px;
        overflow: scroll;
        position: absolute;
    }
    .product-warrenty-sl {
        margin-top: 5px;
    }
</style>
@endsection
@section('custom_script')
<script>
    $(document).ready(function(){

    $( "#from" ).datepicker({
        daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });
    $( "#to" ).datepicker({
        daysOfWeekHighlighted: "7",
            todayHighlight: true,
        });
    });

    $(document).ready(function(){

        $( "#from2" ).datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });
        $( "#to" ).datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
        });

        function show_product_warrenty_input() {
            let product_quantity = parseInt($("#pro-qty").val());
            //all-product-sl-no-main-wrapper
            if($("#warrenty").is(':checked')) {
                if(product_quantity > 0) {
                    $(".all-product-sl-no-main-wrapper").show();
                    let product_sl_input = $(".product-sl-no-input").html();
                    $("#all-product-sl-no").html("");
                    for(let i=1; i<=product_quantity; i++) {
                        $("#all-product-sl-no").append(product_sl_input);
                    }

                } else {
                    $(".all-product-sl-no-main-wrapper").hide();
                }
            } else {
                $(".all-product-sl-no-main-wrapper").hide();
            }
        }

        $("#pro-qty").keyup(function () {
            show_product_warrenty_input();
        });
        $("#warrenty").change(function () {
            show_product_warrenty_input();
        });

        $("#no_warrenty").change(function () {
            show_product_warrenty_input();
        });
        
    });
</script>
@endsection
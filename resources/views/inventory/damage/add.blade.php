@extends('layouts.master')
@section('title','Damage Products')
@section('inventory_class','active')
@section('damage_class','active')
@section('add_damage','active')
@section('content')
<section class="content">
        <section class="content-header">
            @if(session()->has('err'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('err') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if(session()->has('damage'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('damage') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
              <h1>
                Damage
              </h1>       
            </section>
            <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Add Damage Product</h3>
                        </div>
                    </div>

                  <div class="panel-body" >
                    

                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open(['action' => 'Inventory\ProductInventoryController@damage_add_submit', 'id' => 'form' , 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                      <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"> Group</label>
                            <div class="col-sm-6">
                                <select name="" class="form-control select2" id="product_category" required>
                                    <option value="">Select</option>
                                @foreach ($pro_grp as $grp)
                                    <option value="{{ $grp->inv_pro_grp_id }}">{{ $grp->inv_pro_grp_name }}</option>
                                @endforeach   
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Type Name
                                    <b class="text-danger" >*</b>
                                </label>
                                <div class="col-sm-6 product_category_wrapper">
                                    <select name="type" class="form-control select2" id="product_model" required>
                                        <option value="">Select</option>
                                        
                                    </select>
                                </div>
                                    @if($errors->has('type'))
                                        <p class="text-danger">{{ $errors->first('type') }}</p>
                                    @endif
                        </div>
                        <div id="others-supplier"></div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label"> Product Name/Model
                              <b class="text-danger">*</b>
                          </label>
                          <div class="col-sm-6 product_name_wrapper">
                                <select name="product" class="form-control select2" id="product_name" required>
                                    <option value="">Select</option>
                                        
                                </select>
                          </div>
                            @if($errors->has('pro_name'))
                                <p class="text-danger">{{ $errors->first('pro_name') }}</p>
                            @endif
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Damage Quantity</label>
                          <div class="col-sm-6">
                            <input type="number" name="short_total" autocomplete="off" value="{{ old('pro_short') }}" class="form-control" id="" placeholder="Enter Product Damage Quantity" required min="0" step="0.01">
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
@endsection
@section('custom_style')
  <style type="text/css">

#rating > span {
  display: inline-block;
  position: relative;
  width: 1.1em;

}
#rating > span:hover:before,
#rating > span:hover ~ span:before {
   content: "\2605";
   position: absolute;
   color: orange;
}
.form-control::-webkit-inner-spin-button,
.form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
</style>
@endsection
@section('custom_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function(){
    $('#product_category').on("change",function(){
      let grp_id = $("#product_category").val();
      let link = "{{ route('inventory.product_submit_ajax') }}";
      $.ajax({
        type: "GET",
        url: link,
        data: { grp_id: grp_id},
        success: function (result) {
          $(".product_category_wrapper").html(result);
        }
      });
    });

  });


  function get_products(model_id){
      let link = "{{ route('pro_damage.pro_name') }}";
      $.ajax({
        type: "GET",
        url: link,
        data: { model_id: model_id},
        success: function (result) {
          $(".product_name_wrapper").html(result);
        }
      });
    }

$(".add_new_supplier_btn").click(function () {
  let new_supplier = $("#supplier-list-container").html();
  $("#others-supplier").append(new_supplier);
});

function remove_supplier(btn) {
  $(btn).parent().remove();
}

$(".warranty_change").change(function() {

  if(this.value == 1){
    console.log("1");
    $(".warranty_input_wrapper").show();
  } else {
    console.log("0");
    $(".warranty_input_wrapper").hide();
  }
});


</script>
@endsection
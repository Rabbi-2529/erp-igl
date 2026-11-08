@extends('layouts.master')
@section('title', 'Add Product')
@section('inventory_class','active')
@section('product_class','active')
@section('inv_pro_class','active')
@section('pro_add','active')
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
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session()->has('det_add'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('det_add') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
              <h1>
                Product Details
              </h1>       
            </section>
            <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Add Product</h3>
                        </div>
                    </div>

                  <div class="panel-body" >
                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open(['action' => 'Inventory\InvProductController@product_detail_submit', 'id' => 'form' , 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
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
                        <div class="form-group supplier_list">
                            <label for="" class="col-sm-2 control-label">Supplier
                                <b class="text-danger" ></b>
                            </label>
                            <div class="col-sm-6" id="select_div">
                                <select name="supplier[]" class="form-control select2" id="select">
                                    <option value="">Select</option>                          
                                    @foreach($suppliers as $supplier)           
                                      <option value="{{ $supplier->inv_sup_id }}">{{ $supplier->inv_sup_com_name }}</option>
                                    @endforeach
                                </select>
                                
                            </div>
                            <button type="button" class="btn btn-success btn-sm add_new_supplier_btn"><big>+</big></button>
                        </div>
                        <div id="others-supplier"></div>
                  <div class="form-group">
                            <label for="pro_name" class="col-sm-2 control-label">Product Model <b class="text-danger">*</b></label>
                            <div class="col-sm-6">
                                <input type="text" name="pro_name" autocomplete="off" value="{{ old('pro_name') }}" class="form-control" id="pro_name" placeholder="Enter Product Name" required>
                                <span class="text-danger" id="pro_name_error"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Product Buy Price
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-6">
                              <input type="number" name="pro_buy" autocomplete="off" value="{{ old('pro_buy') }}" class="form-control" id="" placeholder="Enter Product Buy Price" required>
                            </div>
                              @if($errors->has('pro_buy'))
                                  <p class="text-danger">{{ $errors->first('pro_buy') }}</p>
                              @endif
                          </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Product Sell Price
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-6">
                              <input type="number" name="pro_sell" autocomplete="off" value="{{ old('pro_sell') }}" class="form-control" placeholder="Enter Product Sell Price Name" required>
                            </div>
                              @if($errors->has('pro_sell'))
                                  <p class="text-danger">{{ $errors->first('pro_sell') }}</p>
                              @endif
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Product Warranty
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-6">
                              <div class="col-sm-3">
                              <input type="radio" id="warranty_yes" name="warranty_change" class="warranty_change" value="1"> <label for="warranty_yes">Yes</label>
                              <input type="radio" id="warranty_no" name="warranty_change" class="warranty_change" value="0" checked> <label for="warranty_no">No</label>
                            </div>
                              <div class="warranty_input_wrapper" style="display:none;">
                                <div class="col-sm-6">
                                  <input type="number" name="pro_warranty" autocomplete="off" value="{{ (old('pro_warranty') != '')? old('pro_warranty'):'0' }}" class="form-control" placeholder="Enter Product Warranty Detail" required>
                                </div>
                                <div class="col-sm-2">
                                  (Days)
                                </div>
                              </div>
                            </div>
                            
                            
                              @if($errors->has('pro_sell'))
                                  <p class="text-danger">{{ $errors->first('pro_sell') }}</p>
                              @endif
                        </div>
                        <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Product Description</label>
                                <div class="col-sm-6">
                                  <input type="text" name="pro_desc" maxlength="50" autocomplete="off" value="{{ old('pro_desc') }}" class="form-control" placeholder="Enter Product Description">
                                </div> 
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Short Quantity</label>
                          <div class="col-sm-6">
                            <input type="number" name="pro_short" autocomplete="off" value="{{ (old('pro_short') == '')? '0':old('pro_short') }}" class="form-control" id="" placeholder="Enter Product Short Quantity">
                            <p>(0 Quantity will not appear on your Quantity List)</p>
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
             


<div class="hidden" id="supplier-list-container">
  <div class="form-group supplier_list">
    <label for="inputEmail3" class="col-sm-2 control-label">Supplier
        <b class="text-danger" ></b>
    </label>
    <div class="col-sm-6 " id="select_div">
        <select name="supplier[]" class="form-control " id="" required>
            <option value="">Select</option>   
            @foreach($suppliers as $supplier)           
              <option value="{{ $supplier->inv_sup_id }}">{{ $supplier->inv_sup_com_name }}</option>
            @endforeach                                 
        </select>
        
    </div>
    <button type="button" onclick="remove_supplier(this)" class="btn btn-danger btn-sm remove_a_supplier_btn"><big>X</big></button>
  </div>
  </div>
  </div>
  </div>
</div>
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


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('pro_name').addEventListener('input', function() {
        let pro_name = this.value;
        let token = "{{ csrf_token() }}"; // Ensure CSRF token is correctly passed.

        if (pro_name.length > 0) {
            fetch("{{ route('inventory.inventory.check_unique_product') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ pro_name: pro_name })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.exists) {
                    document.getElementById('pro_name_error').innerText = 'Product model already exists.';
                    document.querySelector('button[type="submit"]').disabled = true;
                } else {
                    document.getElementById('pro_name_error').innerText = '';
                    document.querySelector('button[type="submit"]').disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('pro_name_error').innerText = 'An error occurred.';
            });
        } else {
            document.getElementById('pro_name_error').innerText = '';
            document.querySelector('button[type="submit"]').disabled = false;
        }
    });
});

</script>

@endsection
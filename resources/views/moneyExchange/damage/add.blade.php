@extends('layout.master')
@section('money_class','menu-open')
@section('damage_class','menu-open')
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
                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Add Damage Currency</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open([ 'id' => 'form' , 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                      <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Country</label>
                            <div class="col-sm-6">
                                <select name="" class="form-control select2" id="country_name" required>
                                    <option value="">Select</option>
                                @foreach ($currency as $curr)
                                    <option value="{{ $curr->id }}">
                                      {{ $curr->name }}
                                      @if($curr->currency_code != '')
                                      ({{ $curr->currency_code }})
                                      @endif
                                    </option>
                                @endforeach   
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Currencies
                                    <b class="text-danger" >*</b>
                                </label>
                                <div class="col-sm-6 country_wrapper">
                                    <select name="type" class="form-control select2" id="product_model" required>
                                        <option value="">Select</option>
                                        
                                    </select>
                                </div>
                                    @if($errors->has('type'))
                                        <p class="text-danger">{{ $errors->first('type') }}</p>
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
                            <button type="button" id="" class="btn btn-info pull-right">Add</button>
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
    $('#country_name').on("change",function(){
      let country_id = $("#country_name").val();
      let link = "{{ route('money-exchange.damage.damage-ajax') }}";
      $.ajax({
        type: "GET",
        url: link,
        data: { country_id: country_id},
        success: function (result) {
          $(".country_wrapper").html(result);
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
@endsection
@extends('layout.master')
@section('payroll_class','menu-open')
@section('shift_management','menu-open')
@section('shift_add','active')
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
            @include('layout.session_message')
            
              <h1>
                Shift Management
              </h1>       
            </section>
                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Add Shift</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open(['route' => 'payroll.shift.add-shift', 'id' => 'form' , 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                      <div class="box-body">
                        <div id="others-supplier"></div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label"> Shift Name
                              <b class="text-danger">*</b>
                          </label>
                          <div class="col-sm-6">
                            <input type="text" name="shift_name" autocomplete="off" value="{{ old('shift_name') }}" class="form-control" id="" placeholder="Enter Shift Name" required>
                          </div>
                            @if($errors->has('shift_name'))
                                <p class="text-danger">{{ $errors->first('shift_name') }}</p>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Working Hour
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-3">
                              <input type="text"  name="working_shift_begin" autocomplete="off" value="{{ old('working_shift_begin') }}" class="form-control" id="startdatetime" placeholder="Entry Time" required>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" name="working_shift_end" autocomplete="off" value="{{ old('working_shift_end') }}" class="form-control" id="enddatetime" placeholder="End Time" required>
                              </div>
                              
                          </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Shift Hour
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-2">
                              <input type="number" name="shift_h" autocomplete="off" value="{{ old('shift_h') }}" class="form-control" placeholder="Enter Shift Hour" required>
                            </div>
                        </div>
                        <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Late Entry</label>
                                <div class="col-sm-2">
                                  <input type="number" name="late" maxlength="50" autocomplete="off" value="{{ old('late') }}" class="form-control" placeholder="Late Entry" required>
                                </div> 
                                @if($errors->has('late'))
                                  <p class="text-danger">{{ $errors->first('late') }}</p>
                              @endif
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Early Leave</label>
                          <div class="col-sm-2">
                            <input type="number" name="e_leave" maxlength="50" autocomplete="off" value="{{ old('e_leave') }}" class="form-control" placeholder="Early Leave" required>
                          </div> 
                          @if($errors->has('e_leave'))
                            <p class="text-danger">{{ $errors->first('e_leave') }}</p>
                          @endif
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


<div class="hidden" id="supplier-list-container">
  <div class="form-group supplier_list">
    <label for="inputEmail3" class="col-sm-2 control-label">Supplier
        <b class="text-danger" ></b>
    </label>
    <div class="col-sm-6 " id="select_div">
        <select name="supplier[]" class="form-control " id="" required>
            <option value="">Select</option>   
                                          
        </select>
        
    </div>
    <button type="button" onclick="remove_supplier(this)" class="btn btn-danger btn-sm remove_a_supplier_btn"><big>X</big></button>
  </div>
  
</div>
@endsection

@section('custom_style')
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script>
     $('#startdatetime').datetimepicker({
            format: 'hh:mm:ss A'
        });
        $('#enddatetime').datetimepicker({
            format: 'hh:mm:ss A'
        });

        

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
@endsection
@extends('layouts.master')
@section('title', 'Add Project')

@section('projects_class', 'active')
@section('add_projects', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Project Add </h4>
                </div>
            </div>
            <div class="panel-body">
          @if(session()->has('err'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('err') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
      
     
          
                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open(['route' => 'projects.store-project', 'method' => 'post' , 'class' => 'form-horizontal'])}}
                      <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"> Customer
                            <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-6">
                                <select name="customer" class="form-control select2" id="customer_name" required>
                                    <option value="">Select</option>
                                    @foreach ($inv_cuses as $cus)
                                        <option value="{{ $cus->inv_cus_id }}" {{ ($cus->inv_cus_id == old('customer') ? 'selected' : '') }}>{{ $cus->inv_cus_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($errors->has('customer'))
                                <p class="text-danger">{{ $messages }}</p>
                            @endif
                        </div>

                        <div class="form-group">
                          <label for="" class="col-sm-2 control-label"> Customer Invoice
{{--                          <b class="text-danger">*</b>--}}
                          </label>
                          <div class="col-sm-6">
                              <select name="invoice" class="form-control select2" id="customer_invoice" >
                                  <option value="">Select Invoice</option>

                              </select>
                          </div>
                          @if($errors->has('customer'))
                              <p class="text-danger">{{ $messages }}</p>
                          @endif
                      </div>

                      <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Department
                          <b class="text-danger">*</b>
                        </label>
                        <div class="col-sm-6">
                          <select name="department" class="form-control select2" id="dept_name" required>
                              <option value="">Select</option>
                              @foreach ($departments as $key => $value)
                                  <option value="{{ $value }}">{{ App\Models\Department::deptName($value) }}</option>
                              @endforeach
                          </select>
                      </div>
                      </div>


                      <div class="form-group">
                        <label for="" class="col-sm-2 control-label"> Project Engineer
                          <b class="text-danger">*</b>
                        </label>
                        <div class="col-sm-6" id="deptUser">
                        <select class="form-control select2" name="qb_staff_id" required>
                            <option value="">Select One</option>
                            @foreach($admin_users as $user)
                                <option value="{{ $user->au_id }}">{{ $user->au_name }} ({{ $user->au_user_id }})</option>
                            @endforeach
                        </select>
                        </div>
                      </div>
                        <!-- /.form-group -->
                        <!-- /.form-group -->


                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label"> Project Name
                              <b class="text-danger">*</b>
                          </label>
                          <div class="col-sm-6">
                            <input type="text" name="project_name" autocomplete="off" value="{{ old('project_name') }}" class="form-control" id="" placeholder="Enter Project Name" required>
                          </div>
                            @if($errors->has('project_name'))
                                <p class="text-danger">{{ $messages }}</p>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Project Address
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-6">
                              <input type="text" name="project_addr" autocomplete="off" value="{{ old('project_addr') }}" class="form-control" id="" placeholder="Enter Project Address" required>
                            </div>
                              @if($errors->has('project_addr'))
                                  <p class="text-danger">{{ $messages }}</p>
                              @endif
                          </div>

                          <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Project Amount
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-6">
                              <input type="text" id="invoice_amount" name="amount" autocomplete="off" value="{{ old('amount') }}" class="form-control" placeholder="Enter Project Amount" required>
                            </div>
                              @if($errors->has('amount'))
                                  <p class="text-danger">{{ $messages }}</p>
                              @endif
                          </div>


                        <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Project Date
                                  <b class="text-danger">*</b>
                                </label>
                                <div class="col-sm-6">
                                  <input type="text" name="project_da" data-date-format="yyyy-mm-dd" id="from4" maxlength="50" autocomplete="off" value="{{ old('pro_desc') }}" class="form-control" placeholder="Enter Project Date">
                                </div>
                                @if($errors->has('project_da'))
                                  <p class="text-danger">{{ $messages }}</p>
                                @endif
                        </div>

                      </div>
                      <!-- /.box-body -->
                      <!-- <div class="box-footer">
                        <div class=" ">
                            <button type="submit" id="" class="btn btn-info">Add</button>
                         </div>
                      </div> -->
<br>
                      <div class="d-flex">
                      <div class="col-sm-2 ">

                      </div>
                        <div class="col-sm-2 ">
                            <button type="submit" id="" class="btn btn-info">Add</button>
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
<link rel="stylesheet" href="{{asset('asset/plugins/bootstrap-duallistbox.min.css')}}">
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
.select2-selection__choice{
  color: white;
  background-color: dodgerblue;
}
</style>

@endsection
@section('custom_script')
<script src="{{asset('asset/plugins/jquery.bootstrap-duallistbox.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script>
  $(document).ready(function(){

$( "#from4" ).datepicker({
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

        $(document).ready(function(){
            $('#customer_name').on("change",function(){
            let cus_id = $("#customer_name").val();

            let link = "{{ route('projects.invoice-ajax') }}";
            $.ajax({
                type: "GET",
                url: link,
                data: { cus_id: cus_id},
                success: function (result) {
                $("#customer_invoice").html(result);
                }
            });
            });


            $('#customer_invoice').on("change",function(){
                let invoice_id = $("#customer_invoice").val();

                let link = "{{ route('projects.invoice-ajax-amount') }}";
                $.ajax({
                    type: "GET",
                    url: link,
                    data: { invoice_id: invoice_id},
                    success: function (result) {
                    $("#invoice_amount").val(result);
                    }
                });
            });

            $('#dept_name').on("change",function(){
            let dept_id = $("#dept_name").val();
            let link = "{{ route('projects.department-ajax') }}";
            $.ajax({
                type: "GET",
                url: link,
                data: { dept_id: dept_id},
                success: function (result) {
                $("#deptUser").html(result);
                }
            });
            });
        });
        $('.duallistbox').bootstrapDualListbox()

</script>
@endsection

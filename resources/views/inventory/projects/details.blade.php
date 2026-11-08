@extends('layouts.master')
@section('title', 'Project Details')

@section('projects_class', 'active')
@section('details_projects', 'active')
@section('content')
<section class="content">
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Project Details</h4>
                </div>
            </div>
            @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
           
   
            <div class="panel-body">
                    <!-- /.box-header -->
                    <!-- form start -->
                    <table id="datatable" class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Name</th>
                          <th>Customer</th>
                          <th>Date</th>
                          <th>Engineers</th>
                          <th>Amount</th>
                          <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($lists as $list)
                        @php($amount = App\Models\Inv_product_inventory::projectRestAmount($list->id))
                        @php($user = explode(',',$list->project_engineer))
                        @php($userName = App\Models\Admin_user::userName($user))

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $list->project_name }}</td>
                                <td>{{ $list->cusInfo->inv_cus_name }}</td>
                                <td>{{ $list->project_date }}</td>
                                <td>
                                  {{ $userName }}
                                </td>
                                <td class="text-right">{{ $amount }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-xs">Action</button>
                                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                          <span class="caret"></span>
                                          <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                                          <li>
                                             <a href="{{ route('projects.detailsById-project',$list->id) }}">
                                                 <span class="text-success">Show</span>
                                             </a>
                                          </li>
                                          <li class="divider"></li>
                                          <li>
                                              <a href="{{ route('projects.project-update',$list->id) }}">
                                                  <span class="text-info">Update</span>
                                              </a>
                                          </li>
                                          <!--<li class="divider"></li>-->
                                          <!--<li>-->
                                          <!--    <a href="" onclick="return confirm('You Wanna Delete this project?')">-->
                                          <!--      <span class="text-danger">Delete</span>-->
                                          <!--    </a>-->
                                          <!--</li>-->


                                        </ul>
                                  </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                      </table>
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


</script>
@endsection

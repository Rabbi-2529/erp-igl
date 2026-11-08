@extends('layout.master')
@section('content')
<section class="content">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Add Experience</h3>
            </div>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if(session()->has('msg'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('msg2') )
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('msg2') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
              {{ session('error') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{ Form::open([ 'action' => ['UserController@user_update_step_3'] , 'method' => 'post' ,'class' => 'form-horizontal', 'id' => 'user_registration_form', 'files' => true ]) }}

            
              <div class="box-body">
                <div class="form-group" style="margin-top: 40px;">
                    <label for="userType" class="col-sm-2 control-label">Experience Level <span style="color: red"> *</span></label>
                    <div class="col-sm-6">
                      <input type="radio" id="ex_level_1" name="ex_level" value="Experienced" required>
                      <label for="ex_level_1" class="mr-2">Experienced</label>
                      <input type="radio" id="ex_level_2" name="ex_level" value="Fresher" required>
                      <label for="ex_level_2">Fresher</label>
                    </div>
                  </div>


              <div class="experienced_level_entry" style="display: none">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Name <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="company_name[]" class="form-control" id="inputEmail3" placeholder="Enter Company Name" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Designation <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="designation[]" class="form-control" id="inputEmail3" placeholder="Enter Designation" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Address <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text"  name="company_address[]" autocomplete="off" class="form-control" placeholder="Enter Company Address" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Duration <span style="color: red"> *</span></label>
                  <div class="col-sm-2">
                    <input type="text" placeholder="Start Date" name="start_date[]" class="form-control" autocomplete="off" id="startDate" data-date-format="yyyy-mm-dd">
                    
                  </div>
                  <div class="col-sm-2"> 
                    <input type="text" placeholder="End Date" onchange="selectEndDate()" name="end_date[]" class="form-control" autocomplete="off" id="endDate" data-date-format="yyyy-mm-dd">
                  </div>
                  <div class="col-sm-2"> 
                    <input type="text" name="" id="totalDay" class="form-control" placeholder="Total Duration" readonly>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Department Head Name <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text"  name="dep_head_name[]" autocomplete="off"  class="form-control" id="mobilenum" placeholder="Enter Department Head Name" >
                  </div>  
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Department Head Number <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text"  name="dep_head_phn[]" autocomplete="off"  class="form-control" id="mobilenum" placeholder="Enter Department Head Number" >
                  </div>  
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Leave Reason <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text"  name="leave_reason[]" autocomplete="off"  class="form-control" id="mobilenum" placeholder="Enter Leave Reason" >
                  </div>  
                  <div class="col-sm-2">
                      <a href="javascript:void(0)" id="experience_add_more" class="btn btn-info pull-right">Add More</a>
                  </div>
                </div>
              </div>
                <div class="experience_add_more">

                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" onclick="return confirm('Are you sure?. Your all information is currect.')" id="" class="btn btn-info pull-right">Update</button>
                </div>
              </div>
              <!-- /.box-footer -->
            {{ Form::close() }}
          </div>
         </section> 
@endsection
@section('custom_style')
{{-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" rel="stylesheet"> --}}
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

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>

<script type="text/javascript">
//////// Check Number Availability/////////

   $(document).ready(function(){
       //reference add more 
    $('#experience_add_more').click(function(){ 
        $('.experience_add_more').append('<div class="experience_remove_ar">\
        <h3 class="box-title">Add New Experience</h3>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Name <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text" name="company_name[]" class="form-control" id="inputEmail3" placeholder="Enter Company Name" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Designation <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text" name="designation[]" class="form-control" id="inputEmail3" placeholder="Enter Designation" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Address <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text"  name="company_address[]" autocomplete="off" class="form-control" placeholder="Enter Company Address" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Duration <span style="color: red"> *</span></label>\
                  <div class="col-sm-3">\
                    <input type="date" name="start_date[]" id="startDate">\
                  </div>\
                  <div class="col-sm-3">\
                    <input type="date" name="end_date[]" id="endDate" onchange="selectEndDate()">\
                  </div>\
                  <div class="col-sm-2">\
                    <input type="text" name="" id="totalDay" class="form-control" placeholder="Total Duration" readonly>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Department Head Name <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text"  name="dep_head_name[]" autocomplete="off"  class="form-control" id="mobilenum" placeholder="Enter Department Head Name" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Department Head Number <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text"  name="dep_head_phn[]" autocomplete="off"  class="form-control" id="mobilenum" placeholder="Enter Department Head Number" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Leave Reason <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text"  name="leave_reason[]" autocomplete="off"  class="form-control" id="mobilenum" placeholder="Enter Leave Reason" required>\
                  </div>\
                  <div class="col-sm-2">\
                      <a href="javascript:void(0)" id="experience_remove" onclick="eclick(this)" class="btn btn-danger pull-right">Remove</a>\
                  </div>\
                </div>\
                </div>'); //Add field html
    });


    //reference remove 
    $('.experience_add_more').on('click', '#experience_remove', function(e){
        e.preventDefault();
        $(this).closest('.experience_remove_ar').remove(); //Remove field html
        //$('.experience_remove_ar').remove();
    });
    // function eclick(e){ 
    //   e.remove();
    //   e.closest('.experience_remove_ar').remove(); 
    // }

    //experience leve change 
    // function ex_level(data){
       
    // }
    $('#ex_level_1').click(function(){ 
      var level = $('#ex_level_1').val();
      if(level == 'Experienced'){
        $('.experienced_level_entry').attr("style", "display:block");
      }else{
        $('.experienced_level_entry').attr("style", "display:none");
      }
    });
    $('#ex_level_2').click(function(){  
      var level = $('#ex_level_2').val();
      if(level == 'Fresher'){
        $('.experienced_level_entry').attr("style", "display:none");
        $('.experience_remove_ar').remove();
      }else{
        $('.experienced_level_entry').attr("style", "display:none");
      }
    });

   });
   



  //

</script>
<script>
  $(document).ready(function(){

  $("#startDate").datepicker({
        daysOfWeekHighlighted: "7",
          todayHighlight: true,
          autoclose: true,
  }); 
  $("#endDate").datepicker({
        daysOfWeekHighlighted: "7",
          todayHighlight: true,
          autoclose: true,
  }); 
  $("#startDate2").datepicker({
        daysOfWeekHighlighted: "7",
          todayHighlight: true,
          autoclose: true,
  }); 
  
  });

     

</script>
<script>
  function selectEndDate() { 
    var startDate = $("#startDate").val();
    var endDate = $("#endDate").val();
    $.ajax({
      type: "GET",
      url: "{{ route('experienceDayCount') }}",
      data: {endDate:endDate, startDate:startDate},
      success:function(response){
        $("#totalDay").val(response); 
        
      }
    })
  }

$("#endDate2").change(function(){
  alert('hello')
  var startDate = $("#startDate2").val();
    var endDate = $("#endDate").val();
    $.ajax({
      type: "GET",
      url: "{{ route('experienceDayCount') }}",
      data: {endDate:endDate, startDate:startDate},
      success:function(response){
        $("#totalDay").val(response); 
        
      }
    })
});
</script>
@endsection

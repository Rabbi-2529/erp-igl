@extends('layout.master')
@section('payroll_class','menu-open')
@section('leave','menu-open')
@section('leave_aplication','active')
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
                Leave Management
              </h1>       
            </section>
                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Application for Leave</h3>
                    </div>
                    <!-- form start -->
                    {{Form::open(['route' => 'leaveApplicationSubmit', 'id' => 'form' , 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form', 'enctype' => 'multipart/form-data'])}}
                      <div class="box-body">
                        <div id="others-supplier"></div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label"> Title
                              <b class="text-danger">*</b>
                          </label>
                          <div class="col-sm-8">
                            <input type="text" name="leave_title" autocomplete="off" value="{{ old('leave_title') }}" class="form-control" id="" placeholder="Enter Title" required>
                          </div>
                            @if($errors->has('leave_title'))
                                <p class="text-danger">{{ $errors->first('leave_title') }}</p>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Type
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-6">
                                <select name="leave_type_id" id="" class="form-control">
                                    <option value="">Select a Type</option>
                                    @foreach ($leaveType as $item)
                                    <option value="{{ $item->id}}">{{ $item->leave_type}}</option> 
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Date
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-3">
                              <label for="">Start Date*</label>
                              <input type="text" placeholder="Start Date" name="date_1" class="form-control" autocomplete="off" id="startDate" data-date-format="yyyy-mm-dd" required>
                            </div>
                            <div class="col-sm-2">
                              <label for="">Days*</label>
                              <input type="text" name="total_day" onkeyup="selectEndDate(this.value)" class="form-control" autocomplete="off" id="totalDay" placeholder="Day" data-date-format="yyyy-mm-dd" required>
                            </div>
                            <div class="col-sm-3"> 
                              <label for="">End Date*</label>
                              <input type="text" placeholder="End Date" name="date_2" class="form-control" autocomplete="off" data-date-format="yyyy-mm-dd" id="endDate" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user_desc" class="col-sm-2 control-label"> Description
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-8">
                              <textarea name="user_desc" id="textEditor" class="form-control" cols="30" rows="10" required></textarea>
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Attach File
                            </label>
                            <div class="col-sm-8">
                              <input type="file" name="file"  class="form-control">
                            </div>
                          </div>
                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer">
                        <div class="col-sm-3">
                            <button type="submit" id="" class="btn btn-info pull-right">Submit</button>
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
{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> --}}
{{-- <script src="http://code.jquery.com/jquery-1.9.1.js"></script> --}}
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>

<script>
    $(document).ready(function(){

    $( "#startDate" ).datepicker({
          daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });
    
    });

       

</script>
<script>
  function selectEndDate(value) {
    var day = $("#startDate").val();
    $.ajax({
      type: "GET",
      url: "{{ route('leave.end-date-ajax') }}",
      data: {value:value, day:day},
      success:function(response){
        $("#endDate").val(response);
        if(response == null){

        }
      }
    })
  }
</script>

 <script>
   $('#textEditor').richText();

 </script>

@endsection
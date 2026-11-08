@extends('layout.master')
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
      <h1>
        Customer Details
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Edit Customer Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="table-responsive">
            {{ Form::open(['action' => ['SdsController@client_feedback_edit_update',$customer_edit->qb_id] , 'method' => 'post' , 'class' => 'form-inline']) }}
            @if( Auth::user()->au_user_type == 4 )
            <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Switch User:</label>
                  <div class="col-sm-6">
                    <select class="form-control select2" name="qb_entry_by">
                    	<option value="">Select</option>
                    	@foreach( $com_users as $com_user )
                    	<option value="{{ $com_user->au_id }}"
                        @if($customer_edit->qb_staff_id == 0 || $customer_edit->qb_staff_id == '' )
                        {{ ($customer_edit->qb_entry_by==$com_user->au_id)?'selected':'' }}
                        @else
                        {{ ($customer_edit->qb_staff_id==$com_user->au_id)?'selected':'' }}
                        @endif>
                        {{ $com_user->au_name }}
                      </option>
                    	@endforeach
                    </select>
                  </div>
             </div>
             @endif
              <table class="table table-bordered table-striped">
                <thead>
	                <tr>
	                  <th>Serial</th>
	                  <td style="margin-left: 40px;">{{ $customer_edit->qb_serial }}</td>
	                  <th>Address</th>
	                  <td>
	                  		<div class="col-sm-12">
			                    		<input type="text" name="qb_address" class="form-control" value="{{  $customer_edit->qb_address  }}" id="inputEmail3" placeholder="Adress" >
			                </div>
	                  </td>
	                </tr>
	                <tr>
	                	<th>Mobile No.</th>
	                	<td>

                      @if( ($customer_edit->qb_mobile && $customer_edit->qb_mobile1 && $customer_edit->qb_mobile2) == true )
                      {{ $customer_edit->qb_mobile }},{{ $customer_edit->qb_mobile1 }}.{{ $customer_edit->qb_mobile2 }}
                      @elseif( ( $customer_edit->qb_mobile && $customer_edit->qb_mobile1 ) == true )
                      {{ $customer_edit->qb_mobile }},{{ $customer_edit->qb_mobile1 }},
                      <input type="number" name="qb_mobile2" id="mobilenum1" class="form-inline sequence qb_mobile2" value="{{ old('qb_mobile2') }}" autocomplete="off" placeholder="Enter Mobile Number" style="width: 160px; height: 40px; padding-left: 5px;">
                      <div >
                        <p class="text-success" style="width: 90px;display: inline-block;"></p>
                        <p class="text-success" style="width: 90px;display: inline-block;"></p>
                        <p class="text-danger" id="mobile_error_message_1" style="width: 160px;display: inline-block;">
                          @if(session()->has('duplicate_mobile2'))
                            {{ session()->get('duplicate_mobile2') }}
                          @endif
                        </p>
                        <p class="text-success" id="mobile_available_message_1" style="width: 160px;display: none;"></p>
                      </div>
                      @else
                      {{ $customer_edit->qb_mobile }},
                      <input type="number" name="qb_mobile1" id="mobilenum2" class="form-inline sequence qb_mobile1" value="{{ old('qb_mobile2') }}" autocomplete="off" placeholder="Enter Mobile Number" style="width: 160px; height: 40px; padding-left: 5px;" >,
                      <input type="number" name="qb_mobile2" id="mobilenum3" class="form-inline sequence qb_mobile2" value="{{ old('qb_mobile2') }}" autocomplete="off" placeholder="Enter Mobile Number" style="width: 160px; height: 40px; padding-left: 5px;" >
                      <div>
                        <p class="text-success" style="width: 90px;display: inline-block;"></p>
                        <p class="text-danger" id="mobile_error_message_2" style="width: 160px;display: inline-block;">
                          @if(session()->has('duplicate_mobile2'))
                            {{ session()->get('duplicate_mobile2') }}
                          @endif
                        </p>
                        <p class="text-success" id="mobile_available_message_2" style="width: 160px;display: none;"></p>

                        <p class="text-danger" id="mobile_error_message_3" style="width: 160px;display: inline-block;">
                          @if(session()->has('duplicate_mobile3'))
                            {{ session()->get('duplicate_mobile3') }}
                          @endif
                        </p>
                        <p class="text-success" id="mobile_available_message_3" style="width: 160px;display: none;"></p>
                      </div>

                      @endif
                      <!-- <div>
                        <p class="text-success" id="mobile_available_message_2" style="width: 90px;display: inline-block;"></p>
                        <p class="text-success" id="mobile_available_message_2" style="width: 160px;display: inline-block;">asdasdasdasdasdasdasd asdasd</p>
                        <p class="text-success" id="mobile_available_message_2" style="width: 160px;display: inline-block;">asdasdasdasdasdasdasd asdasdasdasdasdasdasd</p>
                      </div> -->


                    </td>
	                  <th>Find Us</th>
	                  <td>{{ $customer_edit->find_info['sf_howto'] }}</td>
	                </tr>
	                <tr>
	                	<th>Customer Name</th>
	                	<td >
			                  		<div class="col-sm-12">
			                    		<input type="text" name="qb_name" class="form-control" id="inputEmail3" value="{{  $customer_edit->qb_name  }}" placeholder="Customer Name">
			                  		</div>
	                	</td>
	                  	<th>Email</th>
	                  	<td>
	                  		<div class="col-sm-12">
			                    		<input type="text" name="qb_email" class="form-control" id="inputEmail3" value="{{  $customer_edit->qb_email  }}" placeholder="Enter Customer Email..">
			                </div>
			            </td>
	                </tr>
	                <tr>
	                	<th>Company Name</th>
	                	<td>
                      <div class="col-sm-12">
                        <input type="text" name="company" class="form-control" id="inputEmail3" value="{{  $customer_edit->qb_company_name  }}" placeholder="Enter Customer Email..">
                      </div>
                    </td>
	                  <th>Entry Date</th>
	                  <td>{{ $customer_edit->qb_submit_date }}</td>
	                </tr>
                  <tr>
                    <th>Birth Date</th>
                    <td>
                      <div class="col-sm-12">
                        <input type="text" name="birth" class="form-control" id="from3" value="{{  $customer_edit->qb_birth_date  }}" placeholder="Enter Birth Date..">
                      </div>
                    </td>
                    <th>Marriage Date</th>
                    <td>
                      <div class="col-sm-12">
                        <input type="text" name="marriage" class="form-control" id="from4" value="{{  $customer_edit->qb_marriage_date  }}" placeholder="Enter Marriage Date..">
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th>Customer Type</th>
                    <td>
                      <div class="col-sm-8">
                        <label class="radio-inline">
                          <input type="radio" value="1" name="cus_type" {{ ($customer_edit->qb_cus_type == 1 || $customer_edit->qb_cus_type == 0)? 'checked' : '' }}>Potential
                        </label>
                        <label class="radio-inline">
                          <input type="radio" value="2" name="cus_type" {{ ($customer_edit->qb_cus_type == 2)? 'checked' : '' }}>Pipeline
                        </label>
                        <label class="radio-inline">
                          <input type="radio" value="3" name="cus_type" {{ ($customer_edit->qb_cus_type == 3)? 'checked' : '' }}>Regular
                        </label>
                      </div>
                    </td>
                  </tr>
                </thead>
              </table>
                 <div class="col-sm-3">
                    <button type="submit" class="btn btn-warning pull-left">Update</button>
                 </div>
              {{ Form::close() }}
            </div>
            <!-- /.box-body -->
          </div>
          </section>
          @endsection
@section('custom_style')
<style media="screen">
.form-inline::-webkit-inner-spin-button,
.form-inline::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}

#mobile_available_message_3 {
  display: inline-block;
}

#mobile_available_message_2 {
  display: inline-block;
}
#mobile_error_message_1{
  display: inline-block;;
}
</style>
@endsection
          @section('custom_script')
          <script type="text/javascript">
          var error_mobile = true;
          $(document).ready(function(){
            /*$('#mobilenum1').focusout(function(){
            var mobileno = $(this).val();
            $.ajax({
              type: "GET",
              url: "mobile_num_exist",
              data: { qb_mobile: mobileno},
              success: function (result) {
                if (error_mobile == false){
                if(result == "true") {
                  error_mobile = true;
                  $('#mobile_error_message_1').html('Mobile Number Already Exists');
                  $('#mobile_available_message_1').html('');
                } else {
                  error_mobile = false;
                  $('#mobile_error_message_1').html('');
                  $('#mobile_available_message_1').html('Mobile Number Available');
                }
              }
            }
            });
          });*/



          if ($("#mobilenum1").val().length > 0)  {
            mobilenumerror();
          }

         });

         function check_mobile_existence(mobile_sl) {
           var link = "Follow Up";
           var mobileno = $("#mobilenum"+mobile_sl).val();
           var route_link = "{{ route('ajax.check_mobile_num_exists') }}";
           $.ajax({
             type: "GET",
             url: route_link,
             data: { qb_mobile: mobileno},
             success: function (result) {
               console.log(result);
               if (error_mobile == false){

                 if(result != "false") {
                   error_mobile = true;
                   // let url_show = "/client_feedback/"+ result;
                   let url_show = "{{ route('home') }}" + "/client_feedback/"+ result;
                   $('#mobile_error_message_'+mobile_sl).html("Mobile Number Already Exists <a href=\""+url_show+"\">Follow Up</a>");

                   $('#mobile_available_message_'+mobile_sl).html('');

                 } else {
                   error_mobile = false;
                   $('#mobile_error_message_'+mobile_sl).html('');
                   $('#mobile_error_message_'+mobile_sl).hide();
                   $('#mobile_available_message_'+mobile_sl).show();
                   $('#mobile_available_message_'+mobile_sl).html('Mobile Number Available');
                   // if(mobile_sl == 1) {
                   //   $(".qb_mobile1").removeAttr('disabled');
                   // } else if(mobile_sl == 2) {
                   //   $(".qb_mobile2").removeAttr('disabled');
                   // }
                 }
               }
             }
           });
         }

        /*var error_mobile1 = true;
        $(document).ready(function(){
         $('#mobilenum2').focusout(function(){
           var mobileno1 = $(this).val();
           $.ajax({
             type: "GET",
             url: "mobile_num_exist",
             data: { qb_mobile1: mobileno1},
             success: function (result) {
               if (error_mobile1 == false){
               if(result == "true") {
                 error_mobile1 = true;
                 $('#mobile_error_message_2').html('Mobile Number Already Exists');
                 $('#mobile_available_message_2').html('');
               } else {
                 error_mobile1 = false;
                 $('#mobile_error_message_2').html('');
                 $('#mobile_available_message_2').html('Mobile Number Available');
               }
             }
           }
           });
         });

         if ($("#mobilenum2").val().length > 0)  {
           mobilenumerror();
         }

        });*/

       // $("#customer_create_form").submit(function () {
       //   if(error_mobile1 == true) {
       //     return false;
       //   } else {
       //     return true;
       //   }
       // });


       $("#mobilenum1").keyup(function(){
         mobilenumerror(1);
       });
       $("#mobilenum2").keyup(function(){
         if($("#mobilenum2").val().length > 0) {
           mobilenumerror(2);
         } else {
           $("#mobile_error_message_2").html("");
         }
       });
       $("#mobilenum3").keyup(function(){
         if($("#mobilenum3").val().length > 0) {
           mobilenumerror(3);
         } else {
           $("#mobile_error_message_3").html("");
         }
       });

       function mobilenumerror(sl){
         var mobile_num_length = $("#mobilenum"+sl).val().length;
         if( mobile_num_length<11 || mobile_num_length>11 ){
           /*$("#mobile_error_message_"+sl).html("Invalid Mobile Number");*/
           error_mobile = true;

           $("#mobile_error_message_"+sl).html("Invalid Mobile Number");
           $("#mobile_error_message_"+sl).show("Invalid Mobile Number");
           $("#mobile_available_message_"+sl).html("");
           $("#mobile_available_message_"+sl).hide("");
         }else{
           error_mobile = false;
           $("#mobile_error_message_"+sl).html("");
           check_mobile_existence(sl);
         }
       }

          </script>
          <script>

          $(document).ready(function(){

          $( "#from3" ).datepicker({
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


        $('form').submit(function() {
             $(this).find("button[type='submit']").prop('disabled',true);
         });

          </script>
          @endsection

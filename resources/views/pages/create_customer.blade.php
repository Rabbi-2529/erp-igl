@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('customer_class','menu-open')
@section('create_customer_class','active')
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
          
    @if(session()->has('msgcustomer'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msgcustomer') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          @endif
      <h1>
        Follow Up
      </h1>

    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Customers List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Name</th>
                  <th>Cus ID</th>
                  <th>Company Name</th>
                  <th>Mobile</th>
                  <th>Location</th>
                  <th>Reason</th>
                  <th>Result</th>
                  <th>Type</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($i)
                @php($sl=0)
                @foreach( $customers as $customer )
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $customer->qb_name }}</td>
                  <td>{{ $customer->qb_serial }}</td>
                  <td>{{ $customer->qb_company_name }}</td>
                  <td>
                    @if( ($customer->qb_mobile && $customer->qb_mobile1 && $customer->qb_mobile2) == true )
                    {{ $customer->qb_mobile }},{{ $customer->qb_mobile1 }},{{ $customer->qb_mobile2 }}
                    @elseif( ($customer->qb_mobile && $customer->qb_mobile1) == true )
                    {{ $customer->qb_mobile }},{{ $customer->qb_mobile1 }}
                    @else
                    {{ $customer->qb_mobile }}
                    @endif
                  </td>
                  <td>{{ $customer->qb_address }}</td>
                  <td>{{ $customer->reas_info['sr_reason'] }}</td>
                  <td>
                    @for( $i = 1; $i<=$customer->qb_result; ++$i )
                      <b style="color: green; font-size: 20px;">*</b>
                    @endfor
                  </td>
                  <td>
                    @if ($customer->qb_cus_type == 1)
                        <span class="text-success">Potential</span>
                    @elseif($customer->qb_cus_type == 2)
                        <span class="text-warning">Pipeline</span>
                    @else
                        <span class="text-primary">Regular</span>
                    @endif
                  </td>
                  <td align="center"><a href="{{ url('/client_feedback',['id' => $customer->qb_id]) }}" class="btn btn-info btn-sm">Follow Up</a></td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
           <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Create Customer</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => 'SdsController@create_customer_submit', 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
              <div class="box-body">
              @if( Auth::user()->au_user_type == 4 )
              <div class="form-group" style="padding-top: 10px;">
                  <label for="inputEmail3" class="col-sm-2 control-label">Select Users <b style="color: red;font-size: 18px;">*</b></label>
                  <div class="col-sm-6">
                    <select class="form-control select2" name="qb_staff_id" required >
                      <option value="">Select One</option>
                      @foreach( $users as $user )
                      <option value="{{ $user->au_id }}">{{ $user->au_name }}</option>
                      @endforeach
                    </select>
                    @if( $errors->has('qb_result') )
                        <p class="text-danger">{{ $errors->first('qb_staff_id') }}</p>
                    @endif
                  </div>
                </div>
                @endif

                {{--  --}}
                  <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">Mobile <b style="color: red;font-size: 18px;">*</b></label>
                    <div class="col-sm-6">
                      <input type="number" name="qb_mobile" value="{{ old('qb_mobile') }}" class="form-control sequence qb_mobile" autocomplete="off" id="mobilenum1" placeholder="Enter Mobile Number" required>
                      <p class="text-danger" id="mobile_error_message_1" style="width: 170px;display: inline-block;">
                        @if(session()->has('duplicate_mobile1'))
                          {{ session()->get('duplicate_mobile1') }}
                        @endif
                      </p>
                      <p class="text-success" id="mobile_available_message_1" style="width: 170px;display: inline-block;"></p>
                    </div>
                  </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Phone</label>
                  <div class="col-sm-6">
                    <input type="text" name="qb_phone" value="{{ old('qb_phone') }}" class="form-control" id="inputEmail3" placeholder="Enter Phone Number" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Name</label>
                  <div class="col-sm-6">
                    <input type="text" name="qb_name" value="{{ old('qb_name') }}" class="form-control" id="inputEmail3" placeholder="Place Name..." >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Name</label>
                  <div class="col-sm-6">
                    <input type="text" name="qb_company_name" value="{{ old('qb_company_name') }}" class="form-control" id="inputEmail3" placeholder="Enter Company Name">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Find Us <b style="color: red;font-size: 18px;">*</b></label>
                  <div class="col-sm-6">
                    <select class="form-control select2" name="qb_find_us" required>
                      <option value="">Select one</option>
                      @foreach($finds as $find)
                      <option value="{{ $find->sf_slid }}">{{ $find->sf_howto }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Reason Category <b style="color: red;font-size: 18px;">*</b></label>
                  <div class="col-sm-6">
                    <select class="form-control select2" name="" id="qb_reason_cat" required>
                      <option value="">Select One</option>
                      @foreach( $reasons as $reason )
                      <option value="{{ $reason->sc_slid }}">{{ $reason->sc_catagory }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Reason <b style="color: red;font-size: 18px;">*</b></label>
                  <div class="col-sm-6 qb_reason_wrapper">
                    <select class="form-control" id="qb_reason" name="qb_reason" required>
                      <option value="">Select</option>

                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Result <b style="color: red;font-size: 18px;">*</b></label>
                  <div class="col-sm-6" id="rating" align="left">
                      <!-- <span>☆</span><span>☆</span><span>☆</span><span>☆</span><span>☆</span>  -->
                      <div class='starrr' id='star1'></div>
                      <input type="hidden" name="qb_result" value="{{ old('qb_result') }}" id="qb_result_inp" required>
                      @if( $errors->has('qb_result') )
                        <p class="text-danger">{{ $errors->first('qb_result') }}</p>
                      @endif
                      <p class="text-danger" style="display: none;" id="qb_result_required">This Field Must Be Requird</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Birth Date</label>
                  <div class="col-sm-6">
                    <input type="text" data-date-format="yyyy-mm-dd" name="qb_birth_date" autocomplete="off" class="form-control" id="from2" placeholder="Put Customer Birth Date" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Marriage Date</label>
                  <div class="col-sm-6">
                    <input type="text" data-date-format="yyyy-mm-dd" name="qb_marriage_date" autocomplete="off" class="form-control" id="from1" placeholder="Put Customer Marriage Date" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">E-mail</label>
                  <div class="col-sm-6">
                    <input type="email" name="qb_email" value="{{ old('qb_email') }}" class="form-control" id="inputEmail3" placeholder="Enter Email.." >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Address</label>
                  <div class="col-sm-6">
                    <input type="text" name="qb_address" value="{{ old('qb_address') }}" class="form-control" id="inputEmail3" placeholder="Enter Address">
                  </div>
                </div>
                <div class="form-group">
                  <label for="radio-inline" class="col-sm-2 control-label">Cus. Type</label>
                  <div class="col-sm-8">
                    <label class="radio-inline">
                      <input type="radio" value="1" name="cus_type">Potential
                    </label>
                    <label class="radio-inline">
                      <input type="radio" value="2" name="cus_type">Pipeline
                    </label>
                    <label class="radio-inline">
                      <input type="radio" value="3" name="cus_type">Regular
                    </label>
                    @error('cus_type')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" id="" class="btn btn-info pull-right">Create</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{ Form::close() }}
          </div>
         </section>
@endsection


@section('custom_style')
<link rel="stylesheet" type="text/css" href="{{ asset('asset/plugins/star-rating/starrr.css')}}">
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
<script type="text/javascript" src="{{ asset('asset/plugins/star-rating/starrr.js')}}"></script>


<script>

  $(document).ready(function(){

  $( "#from1" ).datepicker({
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
  });
</script>
<script type="text/javascript">
    $('#star1').starrr({
        change: function(e, value){
          if (value) {
            $("#qb_result_inp").val(value);
          } else {
            $('.your-choice-was').hide();
          }
        }
      });

      var error_mobile = true;
      $(document).ready(function(){

      if ($("#mobilenum1").val().length > 0)  {
        mobilenumerror();
      }

    });

      $("#customer_create_form").submit(function () {
        if(error_mobile == true) {
          if ($("#qb_result_inp").val() != '') {
            $('#qb_result_required').hide();
          }
          $("#mobilenum1").focus();
          return false;
        } else {
          if ($("#qb_result_inp").val() == '') {
            $('#qb_result_required').show();
            return false;
          }else{
            $('#qb_result_required').hide();
          }
          return true;
        }
      });

      function check_mobile_existence(mobile_sl) {
        var link = "Follow Up";
        var mobileno = $("#mobilenum"+mobile_sl).val();
        $.ajax({
          type: "GET",
          url: "mobile_num_exist",
          data: { qb_mobile: mobileno},
          success: function (result) {
            console.log(result);
            if (error_mobile == false){

              if(result != "false") {
                error_mobile = true;
                let url = "client_feedback/"+ result;
                $('#mobile_error_message_'+mobile_sl).html("Mobile Number Already Exists <a href=\""+url+"\">Follow Up</a>");

                $('#mobile_available_message_'+mobile_sl).html('');

              } else {
                error_mobile = false;
                $('#mobile_error_message_'+mobile_sl).html('');
                $('#mobile_error_message_'+mobile_sl).hide();
                $('#mobile_available_message_'+mobile_sl).show();
                $('#mobile_available_message_'+mobile_sl).html('Mobile Number Available');
                if(mobile_sl == 1) {
                  $(".qb_mobile1").removeAttr('disabled');
                } else if(mobile_sl == 2) {
                  $(".qb_mobile2").removeAttr('disabled');
                }
              }
            }
          }
        });
      }

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

    $(document).ready(function(){
      $('#qb_reason_cat').on("change",function(){
        let cat_id = $("#qb_reason_cat").val();
        $.ajax({
          type: "GET",
          url: "create_customer/show_reason_by_ajax",
          data: { cat_id: cat_id},
          success: function (result) {
            $(".qb_reason_wrapper").html(result);
          }
        });
      });
    });


    $(".qb_mobile").keyup(function() {
      if($(".qb_mobile").val().length == 11) {
        $(".qb_mobile1").removeAttr('disabled');
      } else {
        $(".qb_mobile1").attr('disabled', 'disabled');
      }
    });

    $(".qb_mobile1").keyup(function() {
      if($(".qb_mobile1").val().length == 11) {
        $(".qb_mobile2").removeAttr('disabled');
      } else {
        $(".qb_mobile2").attr('disabled', 'disabled');
      }
    });
  
        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled',true);
        });
</script>
@endsection

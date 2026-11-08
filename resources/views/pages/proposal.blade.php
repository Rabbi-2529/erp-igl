@extends('layout.master')
@section('proposal_class','menu-open')
@section('proposal_letter','active')
@section('content')
<section class="content">
<section class="content-header">
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
	
           <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Create Customer</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => 'SdsController@createPdf', 'method' => 'get' , 'class' => 'form-horizontal'])}}
              <div class="box-body">
                  <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">Company Name</label>
                    <div class="col-sm-6">
                      <input type="text" name="company" value="" class="form-control sequence qb_mobile" autocomplete="off" id="mobilenum1" placeholder="Enter Mobile Number">
                    </div>
                  </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Customer Name</label>
                  <div class="col-sm-6">
                    <input type="text" name="name" value="" class="form-control" id="inputEmail3" placeholder="Enter Phone Number" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Customer Designation</label>
                  <div class="col-sm-6">
                    <input type="text" name="designation" value="" class="form-control" id="inputEmail3" placeholder="Place Name..." >
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Customer Mobile</label>
                  <div class="col-sm-6">
                    <input type="text" name="mobile" value="" class="form-control" id="inputEmail3" placeholder="Enter Company Name">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Customer Email</label>
                  <div class="col-sm-6">
                    <input type="text" name="email" value="" class="form-control" id="inputEmail3" placeholder="Enter Address">
                  </div>
                </div>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">Office Address</label>
                    <div class="col-sm-6">
                      <input type="text" name="address" value="" class="form-control" id="inputEmail3" placeholder="Enter Address">
                    </div>
                  </div>
                <div class="form-group">
                  <label for="radio-inline" class="col-sm-2 control-label">What For</label>
                  <div class="col-sm-8">
                    <label class="radio-inline">
                      <input type="checkbox" value="CCTV" name="what[]"> CCTV
                    </label>
                    <label class="radio-inline">
                      <input type="checkbox" value="PABX" name="what[]"> PABX
                    </label>
                    @error('cus_type')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="form-group">
                  <label for="radio-inline" class="col-sm-2 control-label">Send</label>
                  <div class="col-sm-8">
                    <label class="radio-inline">
                      <input type="radio" value="1" name="send"> E-mail
                    </label>
                    <label class="radio-inline">
                      <input type="radio" value="2" name="send"> PDF
                    </label>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" formtarget="_blank" class="btn btn-info pull-right">Create</button>
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


  ///////////////////////
  ////////////////disable input///////////
  /*function fakeValidator(event){
    var $input = $(event.target);
    if ($input.val().length >= 3) {
      $input.addClass('valid');
    } else {
      $input.removeClass('valid');
    }
  }

  function nextInput(event){
    var $input = $(event.target);
    if($input.hasClass('valid')){
      $input.closest('.form-inline')
            .next('.form-inline')
            .find('.sequence')
            .removeAttr('disabled');
    }
  }

  $(document).ready(function(){
    $('.sequence').on('change blur keyup',fakeValidator);
    $('.sequence').on('change blur keyup',nextInput);
  });*/

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

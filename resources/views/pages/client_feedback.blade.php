@extends('layout.master')
@section('content')
<section class="content">
<section class="content-header">
  @if(session()->has('followup'))
      <div class="alert alert-warning alert-dismissible" role="alert">
          {{ session()->get('followup') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
  @endif
    @if(session()->has('message'))
    <div class="alert alert-{{ session()->get('type') }}" id="report-alert">
        <button type="button" class="close" data-dismiss="alert"><span style="font-size: 20px;">x</span></button>
        {{ session()->get('message') }}
    </div>
    @endif
    @if(session()->has('msg_cf'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('msg_cf') }}
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
              <h3 class="box-title">Company Name : {{ $customer->qb_company_name }} ( {{$customer->qb_serial }})</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table class="table table-bordered table-striped table-responsive" style="width: 100%;">

                <tr>
                  <th>Reason</th>
                  <td >{{ $customer->reas_info['sr_reason'] }}</td>
                  <th class="hidden-xs">Birth Date</th>
                  <td class="hidden-xs">{{ $customer->qb_birth_date }}</td>
                  <td rowspan="5" align="center">
                  <h3 style="margin-top: 50px;">
                    <a href="{{ url('/client_feedback_edit',['id' => $customer->qb_id]) }}" class="btn btn-danger">EDIT</a>
                  </h3>
                  <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-sms">
                    SMS
                  </button><br>
                  <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_visit" style="margin-top: 10px;">VISIT</button>
                  </td>
                </tr>
                <tr>
                	<th>Mobile Number</th>
                	<td>
                   @if( ($customer->qb_mobile && $customer->qb_mobile1 && $customer->qb_mobile2) == true )
                      <a href="tel:{{ $customer->qb_mobile }}" style="text-decoration: none;">{{ $customer->qb_mobile }}</a>,<a href="tel:{{ $customer->qb_mobile1 }}" style="text-decoration: none;">{{ $customer->qb_mobile1 }}</a>,<a href="tel:{{ $customer->qb_mobile2 }}" style="text-decoration: none;">{{ $customer->qb_mobile2 }}</a>
                      @elseif( ( $customer->qb_mobile && $customer->qb_mobile1 ) == true )
                      <a href="tel:{{ $customer->qb_mobile }}" style="text-decoration: none;">{{ $customer->qb_mobile }}</a>,<a href="tel:{{ $customer->qb_mobile1 }}" style="text-decoration: none;">{{ $customer->qb_mobile1 }}</a>
                      @else
                      <a href="tel:{{ $customer->qb_mobile }}" style="text-decoration: none;">{{ $customer->qb_mobile }}</a>
                      @endif
                  </td>
                	<th class="hidden-xs">Find Us</th>
                	<td class="hidden-xs">{{ $customer->find_info['sf_howto'] }}</td>
                </tr>
                <tr>
                	<th>Customer Name</th>
                	<td>{{ $customer->qb_name }}</td>
                	<th class="hidden-xs">Email</th>
                	<td class="hidden-xs">{{ $customer->qb_email }}</td>
                </tr>
                <tr>
                  <th>Entry By</th>
                  @if( ($customer->qb_staff_id == '') ||  ($customer->qb_staff_id == 0))
                  <td>{{ $customer->entry_info['au_name'] }}</td>
                  @else
                  <td>{{ $customer->entry_staff['au_name'] }}<br>( Admin Reference )</td>
                  @endif
                  <th class="hidden-xs">Entry Date</th>
                  <td class="hidden-xs">{{ $customer->qb_submit_date }}</td>
                </tr>
                <tr>
                  <th>Address</th>
                  <td>{{ $customer->qb_address }}</td>
                  <th class="hidden-xs">Marriage Date</th>
                  <td class="hidden-xs">{{ $customer->qb_marriage_date }}</td>
                </tr>
                <tr>
                  <th>Customer Type</th>
                  <td>
                    @if ($customer->qb_cus_type == 1 || $customer->qb_cus_type == 0)
                        <span class="text-success">Potential</span>
                    @elseif($customer->qb_cus_type == 2)
                        <span class="text-warning">Pipeline</span>
                    @elseif($customer->qb_cus_type == 3)
                        <span class="text-primary">Regular</span>
                    @endif
                  </td>
                </tr>

              </table>
            </div>
            <div class="box">
            <div class="box-header">
              <h3 class="box-title">Follow Up</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            <h4>Customer Follow Up</h4>
            <p> <b class="text-warning">NB: Followup By= Current followup given Username / Previous followup given Username</b></p>
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Followup Date</th>
                  <th>Next Date</th>
                  <th>Duration</th>
                  <th>Result</th>
                  <th>Feedback</th>
                  <th>Price</th>
                  <th>Feedback</th>
                  <th>FollowUp By</th>
                  <th>Notify</th>
                </tr>
                </thead>
                <tbody>
                @php($i)
                @php($sl=0)
                @foreach( $feedbacks as $feedback )
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $feedback->cf_date }}</td>
                  <td>{{ $feedback->cf_next_date }}</td>
                  <td>{{ $feedback->cf_call_duration }}</td>
                  <td>
                    @for( $i = 1; $i<=$feedback->cf_result; ++$i )
                    <b style="color: green; font-size: 20px;">*</b>
                    @endfor
                  </td>
                  <td>
                      {{ isset($feedback->feedbackinfo['fm_msg']) ? $feedback->feedbackinfo['fm_msg'] : '' }}
                  </td>
                  <td>{{ $feedback->cf_price }}</td>
                  <td>{{ $feedback->cf_client_message }}</td>
                  <td>{{ $feedback->feedback_info['au_name'] }} / {{ $feedback->cf_previous_followup }}</td>
                  <td align="center">
                      @if( App\Models\Client_feedback::notifycus($feedback->cf_id) == 1)
                      <b style="color: blue;">
                        <a href="javascript:void(0);" onclick="showSmsNotify({{ $feedback->cf_id }})">SMS</a>
                      </b>
                      @elseif( App\Models\Client_feedback::notifycus($feedback->cf_id) == 2)
                      <b style="color: green;">
                        <a href="javascript:void(0);" onclick="showVisitNotify({{ $feedback->cf_id }})">VISIT</a>
                      </b>
                      @else
                      <b style="color: red;">No</b>
                      @endif
                  </td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
            <!-- /.box-body -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Customer Feedback</h3>
            </div>
            	<h3 class="text-success"></h3>

            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => ['SdsController@client_feedback_submit', $customer->qb_id], 'id' => 'client_feedbaack_form','method' => 'post' , 'class' => 'form-horizontal'])}}
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Call Duration<b style="color:red; font-size:18px;">*</b></label>
                  <div class="col-sm-6">
                    <input type="text" autocomplete="off" value="{{ old('cf_call_duration') }}" name="cf_call_duration" class="form-control" id="inputEmail3" placeholder="Call Duration" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Result<b style="color:red; font-size:18px;">*</b></label>
                  <div class="col-sm-6" id="rating" align="left">
                      <!-- <span>☆</span><span>☆</span><span>☆</span><span>☆</span><span>☆</span>  -->
                      <div class='starrr' id='star1'></div>
                      <input type="hidden" value="{{ old('cf_result') }}" name="cf_result" value="" id="qb_result_inp">
                  </div>
                  <p class="text-danger cf_result_error">
                    @if($errors->has('cf_result'))
                      {{ $errors->first('cf_result') }}
                    @endif
                  </p>
                </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Feedback<b style="color:red; font-size:18px;">*</b></label>
                  <div class="col-sm-6">
                    <select class="form-control select2" name="cf_client_feedback">
                    	<option value="">Select one</option>
                      @foreach( $client_feedbacks as $feedback )
                      <option value="{{ $feedback->fm_id }}">{{ $feedback->fm_msg }}</option>
                      @endforeach
                    </select>
                  </div>
                  <p class="text-danger cf_client_feedback_error">
                    @if( $errors->has('cf_client_feedback') )
                      {{ $errors->first('cf_client_feedback') }}
                    @endif
                  </p>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Next Date<b style="color:red; font-size:18px;">*</b></label>
                  <div class="col-sm-6">
                    <input type="text" value="{{ old('cf_next_date') }}" data-date-format="yyyy-mm-dd" name="cf_next_date" class="form-control" id="from" placeholder="Next Date..." autocomplete="off" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Price</label>
                  <div class="col-sm-6">
                    <input type="number" value="{{ old('cf_price') }}" step="0.01" name="cf_price" class="form-control" id="inputEmail3" placeholder="Product Price...">
                  </div>
                   @if( $errors->has('cf_price') )
                      <p class="text-danger">{{ $errors->first('cf_price') }}</p>
                  @endif
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Customer Message</label>
                  <div class="col-sm-6">
                    <input type="text" value="{{ old('cf_client_message') }}" name="cf_client_message" autocomplete="off" class="form-control" id="inputEmail3" placeholder="Customer Message...">
                  </div>
                </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">Feedback</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{ Form::close() }}
          </div>
          <div class="modal fade" id="modal-sms">
                        <div class="modal-dialog">
                          {{Form::open(['action' => ['SdsController@send_notification', $customer->qb_id] , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title">Send Notification</h4>
                            </div>
                            <div class="modal-body">

                                <div class="box-body">
                                  <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-2 control-label">Number</label>
                                    <div class="col-sm-9">
                                      @if( ($customer->qb_mobile && $customer->qb_mobile1 && $customer->qb_mobile2) != '' )
                                      <input type="checkbox" name="sms_mobileno1" value="{{ $customer->qb_mobile }}" > {{ $customer->qb_mobile }}
                                      <input type="checkbox" name="sms_mobileno2" value="{{ $customer->qb_mobile1 }}" > {{ $customer->qb_mobile1 }}
                                      <input type="checkbox" name="sms_mobileno3" value="{{ $customer->qb_mobile2 }}" > {{ $customer->qb_mobile2 }}
                                      @elseif( ($customer->qb_mobile && $customer->qb_mobile1) != '' )
                                      <input type="checkbox" name="sms_mobileno1" value="{{ $customer->qb_mobile }}" > {{ $customer->qb_mobile }}
                                      <input type="checkbox" name="sms_mobileno2" value="{{ $customer->qb_mobile1 }}" > {{ $customer->qb_mobile1 }}
                                      @else
                                      <input type="checkbox" name="sms_mobileno1" value="{{ $customer->qb_mobile }}" > {{ $customer->qb_mobile }}
                                      @endif
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-2 control-label">Enter Number</label>
                                    <div class="col-sm-9">
                                      <input type="text" name="sms_mobileno4" value="" class="form-control" id="inputEmail3" placeholder="Enter Number...">
                                    </div>
                                  </div>
                                  <div class="form-group" id="send-sms-content">
                                    <label for="inputEmail3" class="col-sm-2 control-label">Message</label>
                                    <div class="col-sm-9">
                                      <a href="#" data-toggle="modal" data-target="#modal-use-temp"><u>Use Template</u></a><a href="http://unicodeconverter.info/avro-type.php?pgn=2.1" class="" style="margin-left: 5px" target="_blank">(বাংলা লিখতে এখানে ক্লিক করুন)</a>
                                      <textarea class="count_me form-control" rows="8" id="insert" name="sms_text" placeholder="Enter Your Text..." required></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div style="width: 373px; margin-left: 110px;"><span class="charleft contacts-count">&nbsp;</span><span
                                                        class="parts-count"></span></div>
                                        </div>
                                    </div>
                                  </div>
                              </div>
                            </div>

                            <div class="modal-footer">
                              <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                          </div>
                          {{Form::close()}}
                          <!-- /.modal-content -->
                        </div>
                <!-- /.modal-dialog -->
                </div>
                <div class="modal fade" id="modal-use-temp">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">Send Notification</h4>
                                  </div>
                                  <div class="modal-body">
                                    <table id="example1" class="table table-bordered table-striped">
                                      <thead>
                                        <tr>
                                          <th>SL</th>
                                          <th>Title</th>
                                          <th>Message</th>
                                          <th>Quatity</th>
                                          <th>Action</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @php( $sl = 0 )
                                        @foreach( $sms_temps as $temp )
                                        <tr>
                                          <td>{{ ++$sl }}</td>
                                          <td>{{ $temp->st_title }}</td>
                                          <td class="temp_msg_{{ $sl }}">{{ $temp->st_message }}</td>
                                          <td align="center">{{ $temp->st_quantity }}</td>
                                          <td align="center">
                                            <button type="button" class="btn btn-info btn-sm"  onclick='insert("{{ $sl }}")'>
                                            INSERT</button>
                                          </td>
                                        </tr>
                                      @endforeach
                                      </tbody>
                                    </table>
                                <!-- /.modal-content -->
                              </div>
                            </div>
                          </div>
                      <!-- /.modal-dialog -->
                      </div>

                      <div class="modal fade" id="smsNotify">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title">Notification Detail</h4>
                            </div>
                            <div class="modal-body">
                              <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                  <tr>
                                    <th>Feedback ID</th>
                                    <th>Mobile No</th>
                                    <th>Message</th>
                                    <th>Sending Time</th>
                                  </tr>
                                </thead>
                                <tbody>

                                  <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td ></td>
                                  </tr>

                                </tbody>
                              </table>
                              <!-- /.modal-content -->
                            </div>
                          </div>
                        </div>
                <!-- /.modal-dialog -->
                </div>

                <div class="modal fade" id="visitNotify">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Notification Detail</h4>
                      </div>
                      <div class="modal-body">
                        <table id="example1" class="table table-bordered table-striped">
                          <thead>
                            <tr>
                              <th>Feedback ID</th>
                              <th>Company Name</th>
                              <th>Text</th>
                              <th>Visiting Date</th>
                            </tr>
                          </thead>
                          <tbody>

                            <tr>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td ></td>
                            </tr>

                          </tbody>
                        </table>
                        <!-- /.modal-content -->
                      </div>
                    </div>
                  </div>
          <!-- /.modal-dialog -->
          </div>

                <div class="modal fade" id="modal_visit">
                              <div class="modal-dialog">
                                {{Form::open(['action' => ['SdsController@visit_notify', $customer->qb_id] , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">Visit Notification</h4>
                                  </div>
                                  <div class="modal-body">

                                      <div class="box-body">
                                        <div class="form-group">
                                          <label for="inputEmail3" class="col-sm-2 control-label">Company Name</label>
                                          <div class="col-sm-9">
                                            <input type="text" name="visit" value="{{ $customer->qb_company_name }}" class="form-control" id="inputEmail3" placeholder="Enter Company Name...">
                                          </div>
                                        </div>
                                        <div class="form-group">
                                          <label for="inputEmail3" class="col-sm-2 control-label">Message</label>
                                          <div class="col-sm-9">
                                            <textarea class="count_me form-control" rows="8" id="insert" name="visit_text" placeholder="Enter Your Text..." required></textarea>
                                          </div>
                                        </div>
                                    </div>
                                  </div>

                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Send</button>
                                  </div>
                                </div>
                                {{Form::close()}}
                                <!-- /.modal-content -->
                              </div>
                      <!-- /.modal-dialog -->
                      </div>
         </section>
@endsection

@section('custom_style')
<link rel="stylesheet" type="text/css" href="{{ asset('asset/plugins/star-rating/starrr.css')}}">
  <style type="text/css">

  .modal-backdrop.in {
      display: none;
    }

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
<script type="text/javascript" src="{{ asset('assets/javascript-form-validation/validation.oalid.js')}}"></script>
<script type="text/javascript" src="{{ asset('asset/plugins/star-rating/starrr.js')}}"></script>
<script src="{{ asset('asset/js/jquery.textareaCounter.plugin.js')}}"></script>
<script src="{{ asset('asset/js/text-area-counter.js')}}"></script>
<script>
		  $(document).ready(function () {
		      count_textarea('#modal-sms');

		  });
		</script>

<script type="text/javascript">
  function insert( sl ){
    let sms_text = document.getElementsByClassName("temp_msg_" + sl)[0].innerHTML;
    document.getElementById("insert").innerHTML = sms_text;
    document.getElementById('modal-use-temp').style.display = "none";
  }
</script>
<script type="text/javascript">
  $('#star1').starrr({
      change: function(e, value){
        if (value) {
          $("#qb_result_inp").val(value);
        } else {
          $('.your-choice-was').hide();
          $("#qb_result_inp").val(0);
        }
      }
    });
    let validation_rules = {
          'cf_result' : ['required'],
          'cf_client_feedback' : ['required'],
      }

      let validation_messages = {
          'cf_result.required' : 'Result field is required',
          'cf_client_feedback.required' : 'Customer Feedback is Required',
      };


      js_form_validation("client_feedbaack_form", validation_rules, validation_messages);


      function showSmsNotify(id){

        var link = "{{ route('home') }}" + "/client_feedback_modal/"+id;

        $.ajax({
          url: link,
          data: {},
          success: function(result){
            if(result == false) {
              alert('Huh!');
            } else {
              $("#smsNotify").modal('show');
              $("#smsNotify table tbody").html(result);
            }
          }
        });
      }

      function showVisitNotify(id){

        var link = "{{ route('home') }}" + "/client_feedback_modal/"+id;

        $.ajax({
          url: link,
          data: {},
          success: function(result){
            if(result == false) {
              alert('Huh!');
            } else {
              $("#visitNotify").modal('show');
              $("#visitNotify table tbody").html(result);
            }
          }
        });
      }

        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled',true);
        });

</script>
@endsection

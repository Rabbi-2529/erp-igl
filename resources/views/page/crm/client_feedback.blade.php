@extends('layouts.master')
@section('title', 'Client Feedback')

@section('customer_relation_class','active')
@section('customer_class','active')
@section('total_customer_class','active')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('asset/plugins/star-rating/starrr.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style type="text/css">

  .modal-backdrop.in {
      display: none;
  }
  
  .modal-backdrop {
      display: none !important;
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
  
  /* Fix for radio buttons */
  .radio {
      margin: 8px 0;
      padding: 5px 0;
      display: inline-block;
  }
  
  .radio label {
      cursor: pointer !important;
      font-weight: normal;
      display: inline-block;
      padding: 5px 10px;
      border-radius: 4px;
      transition: all 0.2s ease;
      pointer-events: auto !important;
  }
  
  .radio label:hover {
      background-color: #f5f5f5;
  }
  
  .radio input[type="radio"] {
      margin-right: 8px;
      cursor: pointer !important;
      vertical-align: middle;
      width: auto !important;
      height: auto !important;
      pointer-events: auto !important;
      opacity: 1 !important;
      visibility: visible !important;
      position: relative !important;
      z-index: 999 !important;
  }
  
  .form-group {
      position: relative;
      z-index: 1;
  }
  
  .box-body {
      position: relative;
      z-index: 1;
  }

</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Customer Details of {{ $customer->qb_company_name }} ( {{$customer->qb_serial }})</h4>
                </div>
            </div>

            @if(session()->has('msg_cf'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('msg_cf') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
            @endif
            @if(session()->has('followup'))
                <div class="alert alert-warning alert-dismissible" role="alert">
                    {{ session()->get('followup') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="panel-body">

                <table class="table table-bordered table-striped table-hover datatable">
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
                    @if(($customer->qb_staff_id == '') ||  ($customer->qb_staff_id == 0))
                        <td>{{ $customer->entry_info['au_name'] }} ({{ $customer->entry_info['au_user_id'] }})</td>
                    @else
                        <td>{{ $customer->entry_staff['au_name'] }} ({{ $customer->entry_staff['au_user_id'] }})<br>( Admin Reference )</td>
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
                      <th class="hidden-xs">Company Name</th>
                      <td class="hidden-xs">{{ $customer->qb_company_name }}</td>
                    </tr>
                </table>

              
                 <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Follow Up</h3>
                            <div class="my-3">
                                <button class="btn btn-primary mb-2" data-toggle="modal" data-target="#modal_conveyance">
                                    Conveyance: {{ number_format($total_debit_convence, 0, '.', ',') }} Tk
                                </button>
                                <button class="btn btn-success" data-toggle="modal" data-target="#modal_lunch">
                                    Lunch: {{ number_format($total_debit_lunch, 0, '.', ',') }} Tk
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <p>
                                <b class="text-warning">NB: Followup By= Current followup given Username / Previous followup given Username</b>
                            </p>
                            <table class="table table-bordered table-striped table-hover datatable">
                                <thead>
                                      <tr>
                                        <th>SL</th>
                                        <th>Followup Date</th>
                                        <th>Next Date</th>
                                        <th>Duration</th>
                                        <th>Result</th>
                                        <th>Emp. Feedback</th>
                                        <th>Connect Via</th>
                                        <th>Price</th>
                                        <th>Cus. Feedback</th>
                                        <th>Follow-Up By</th>
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
                                        <!-- Connect Via -->
                                        <td>
                                            @if($feedback->cf_connect_us)
                                                @if($feedback->cf_connect_us == 'WhatsApp')
                                                    <i class="fa fa-whatsapp" style="color: #25D366;"></i>
                                                @elseif($feedback->cf_connect_us == 'Phone Call')
                                                    <i class="fa fa-phone" style="color: #0078FF;"></i>
                                                @elseif($feedback->cf_connect_us == 'Email')
                                                    <i class="fa fa-envelope" style="color: #EA4335;"></i>
                                                @elseif($feedback->cf_connect_us == 'SMS')
                                                    <i class="fa fa-comment" style="color: #34B7F1;"></i>
                                                @elseif($feedback->cf_connect_us == 'Facebook')
                                                    <i class="fa fa-facebook" style="color: #1877F2;"></i>
                                                @elseif($feedback->cf_connect_us == 'In Person')
                                                    <i class="fa fa-users"></i>
                                                @else
                                                    <i class="fa fa-ellipsis-h"></i>
                                                @endif
                                                {{ $feedback->cf_connect_us }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <!-- Price -->
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
                    </div>

                <div class="box box-info">
                        
                        <div class="box-header with-border">
                            <h3 class="box-title">Customer Feedback</h3>
                        </div>
                          <h3 class="text-success"></h3>

                        <div class="box-body">
                            {{Form::open(['action' => ['SdsController@client_feedback_submit', $customer->qb_id], 'id' => 'client_feedbaack_form','method' => 'post' , 'class' => 'form-horizontal'])}}
                             <div class="form-group">
                            <label class="col-sm-2 control-label">How did you connect?</label>
                            <div class="col-sm-6">
                                <div>
                                    <label class="radio-inline" style="margin-right: 15px; cursor: pointer;">
                                        <input type="radio" name="cf_connect_us" value="WhatsApp" {{ old('cf_connect_us') == 'WhatsApp' ? 'checked' : '' }}> WhatsApp
                                    </label>
                                    <label class="radio-inline" style="margin-right: 15px; cursor: pointer;">
                                        <input type="radio" name="cf_connect_us" value="Phone Call" {{ old('cf_connect_us') == 'Phone Call' ? 'checked' : '' }}> Phone Call
                                    </label>
 
                                    <label class="radio-inline" style="margin-right: 15px; cursor: pointer;">
                                        <input type="radio" name="cf_connect_us" value="Facebook" {{ old('cf_connect_us') == 'Facebook' ? 'checked' : '' }}> Messanger
                                    </label>
                                    <label class="radio-inline" style="margin-right: 15px; cursor: pointer;">
                                        <input type="radio" name="cf_connect_us" value="In Person" {{ old('cf_connect_us') == 'In Person' ? 'checked' : '' }}> In Person
                                    </label>
                                    <label class="radio-inline" style="cursor: pointer;">
                                        <input type="radio" name="cf_connect_us" value="Other" {{ old('cf_connect_us') == 'Other' ? 'checked' : '' }}> Other
                                    </label>
                                </div>
                            </div>
                        </div>
                            <div class="form-group">
                               <label for="callDuration" class="col-sm-2 control-label">Call Duration
        <b style="color:red; font-size:18px;">*</b>
    </label>
    <div class="col-sm-6">
        <input type="text" autocomplete="off" id="callDuration" value="{{ old('cf_call_duration') }}" 
               name="cf_call_duration" class="form-control" placeholder="MM:SS" required>
    </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Result<b style="color:red; font-size:18px;">*</b></label>
                                <div class="col-sm-6" id="rating" align="left">
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
                                    <select class="form-control select2" name="cf_client_feedback" id="cf_client_feedback">
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

                            <div class="form-group" id="reasonGroup" style="display: none;">
                                <label for="cf_reason" class="col-sm-2 control-label">Reason<b style="color:red; font-size:18px;">*</b></label>
                                <div class="col-sm-6">
                                    <select class="form-control select2" name="cf_reason" id="cf_reason">
                                        <option value="">Select one</option>
                                        @foreach( $reason as $rsn )
                                        <option value="{{ $rsn->sr_slid }}">{{ $rsn->sr_reason }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group" id="nextDateGroup">
                                <label for="inputEmail3" class="col-sm-2 control-label">Next Date<b style="color:red; font-size:18px;">*</b></label>
                                <div class="col-sm-6">
                                    <input type="text" value="{{ old('cf_next_date') }}" data-date-format="yyyy-mm-dd" name="cf_next_date" class="form-control" placeholder="Next Date..." id="nextdate" autocomplete="off">
                                </div>
                            </div>
                            
                              <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Price <b id="price-star" style="color:red; font-size:18px; display:none;">*</b></label>
    <div class="col-sm-6">
        <input type="text" value="{{ old('cf_price') }}" name="cf_price" class="form-control amount" id="inputEmail3" placeholder="Product Price...">
    </div>
    @if($errors->has('cf_price'))
        <p class="text-danger">{{ $errors->first('cf_price') }}</p>
    @endif
</div>
                            
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Customer Message</label>
                                <div class="col-sm-6">
                                <input type="text" value="{{ old('cf_client_message') }}" name="cf_client_message" autocomplete="off" class="form-control" id="inputEmail3" placeholder="Customer Message...">
                                </div>
                            </div>
                                    <!-- HOW DID YOU CONNECT? RADIO BUTTONS - FIXED VERSION -->
                       
                        <!-- END OF HOW DID YOU CONNECT? SECTION -->
                            <div class="box-footer">
                            <div class="col-sm-3">
                            <button type="submit" id="submitBtn" class="btn btn-info pull-right">Feedback</button>
                                </div>
                            </div>
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
                                        <table class="table table-bordered table-striped table-hover datatable">
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
                                        <table class="table table-bordered table-striped table-hover datatable">
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
                                    <table class="table table-bordered table-striped table-hover datatable">
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
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal_conveyance">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Conveyance Details</h4>
            </div>
            <div class="modal-body">
                <div class="box-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Staff ID</th>
                                <th>Bill Date</th>
                                <th>From</th>
                                <th>TO</th>
                                <th>Reason</th>
                                <th>Debit</th>
                                <th>Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($convence_entries as $entry)
                                <tr>
                                    <td>{{ optional($entry->employee_info)->au_name ?? '-' }}</td>
                                    <td>{{ optional($entry->employee_info)->au_user_id ?? '-' }}</td>
                                    <td>{{ $entry->oms_bill_date }}</td>
                                    <td>{{ $entry->oms_dfrom }}</td>
                                    <td>{{ $entry->oms_dto }}</td>
                                    <td>{{ optional($entry->reason)->name ?? '-' }}</td>

                                    <td>{{ number_format($entry->oms_debit, 0, '.', ',') }} Tk</td>
                                    <td>{{ optional($entry->mode)->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No conveyance records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modal_lunch">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Lunch Details</h4>
            </div>
            <div class="modal-body">
                <div class="box-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Staff ID</th>
                                <th>Bill Date</th>
                                <th>From</th>
                                <th>TO</th>
                                <th>Reason</th>
                                <th>Debit</th>
                                <th>Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lunch_entries as $entry)
                                <tr>
                                    <td>{{ optional($entry->employee_info)->au_name ?? '-' }}</td>
                                    <td>{{ optional($entry->employee_info)->au_user_id ?? '-' }}</td>
                                    <td>{{ $entry->oms_bill_date }}</td>
                                    <td>{{ $entry->oms_dfrom }}</td>
                                    <td>{{ $entry->oms_dto }}</td>
                                    <td>{{ optional($entry->reason)->name ?? '-' }}</td>
                                    <td>{{ number_format($entry->oms_debit, 0, '.', ',') }} Tk</td>
                                    <td>{{ optional($entry->mode)->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8">No lunch records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('assets/javascript-form-validation/validation.oalid.js')}}"></script>
<script type="text/javascript" src="{{ asset('asset/plugins/star-rating/starrr.js')}}"></script>
<script src="{{ asset('asset/js/jquery.textareaCounter.plugin.js')}}"></script>
<script src="{{ asset('asset/js/text-area-counter.js')}}"></script>
<script>
    // Global functions - moved outside document ready for onclick access
    function showSmsNotify(id) {
        var link = "{{ route('home') }}" + "/client_feedback_modal/" + id;

        $.ajax({
            url: link,
            type: 'GET',
            data: {},
            success: function(result) {
                if (result == false) {
                    alert('Huh!');
                } else {
                    $("#smsNotify").modal('show');
                    $("#smsNotify table tbody").html(result);
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error: ", error);
                alert('Error loading SMS notification details');
            }
        });
    }

    function showVisitNotify(id) {
        var link = "{{ route('home') }}" + "/client_feedback_modal/" + id;

        $.ajax({
            url: link,
            type: 'GET',
            data: {},
            success: function(result) {
                if (result == false) {
                    alert('Huh!');
                } else {
                    $("#visitNotify").modal('show');
                    $("#visitNotify table tbody").html(result);
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error: ", error);
                alert('Error loading Visit notification details');
            }
        });
    }

    $(document).ready(function() {
        // Handle input change for call duration
        $('#callDuration').on('input', function() {
            let value = $(this).val();

            // Allow only numbers and colon
            value = value.replace(/[^0-9:]/g, '');

            // Auto-add colon when typing two numbers (e.g., "45" → "45:")
            if (value.length === 2 && value.indexOf(':') === -1) {
                value += ":";
            }

            // Ensure valid format MM:SS, where minutes and seconds are 2 digits
            let regex = /^([0-5]?[0-9]):([0-5]?[0-9])$/;

            // Set red border if the value doesn't match the format
            if (!regex.test(value) && value.length > 0) {
                $(this).css('border', '2px solid red');
            } else {
                $(this).css('border', '');
            }

            // Limit input to max length of 5 (MM:SS)
            if (value.length > 5) {
                value = value.substring(0, 5);
            }

            // Set the formatted value back to the input
            $(this).val(value);
        });

        // Restrict input to numbers and ":" only
        $('#callDuration').keypress(function(event) {
            let charCode = event.which ? event.which : event.keyCode;
            if (charCode !== 58 && (charCode < 48 || charCode > 57)) {
                return false;
            }
        });

        // Validate and format on blur
        $('#callDuration').blur(function() {
            let value = $(this).val();
            let parts = value.split(":");

            // Ensure the input is in MM:SS format
            if (parts.length === 2) {
                let minutes = parts[0].padStart(2, '0');
                let seconds = parts[1].padStart(2, '0');

                // Validate minutes and seconds
                if (parseInt(minutes) > 59 || parseInt(seconds) > 59) {
                    alert("Invalid format! Please enter a value up to 59:59.");
                    $(this).val("00:00");
                } else {
                    $(this).val(`${minutes}:${seconds}`);
                }
            } else if (value.length > 0) {
                // Handle incomplete input
                if (value.indexOf(':') === -1) {
                    let minutes = value.padStart(2, '0');
                    $(this).val(`${minutes}:00`);
                } else {
                    let minutes = parts[0].padStart(2, '0');
                    let seconds = (parts[1] || '').padStart(2, '0');
                    $(this).val(`${minutes}:${seconds}`);
                }
            } else {
                $(this).val("00:00");
            }
        });

        // Initialize textarea counter
        count_textarea('#modal-sms');

        // Datepicker initialization
        $("#nextdate").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            minDate: 0,
            changeMonth: true,
            changeYear: true
        });

        // Handle price star visibility
        $('#cf_client_feedback').change(function() {
            var selectedValue = $(this).val();

            if (selectedValue === '6') {
                $('#price-star').show();
            } else {
                $('#price-star').hide();
            }
        });

        // Handle next date group visibility
        $('#cf_client_feedback').change(function() {
            var selectedValue = $(this).val();

            if (selectedValue === '5') {
                $('#nextDateGroup').hide();
                $('input[name="cf_next_date"]').removeAttr('required').val('');
            } else {
                $('#nextDateGroup').show();
                $('input[name="cf_next_date"]').attr('required', 'required');
            }
        });

        // Handle reason group visibility
        $('#cf_client_feedback').change(function() {
            var selectedValue = $(this).val();

            if (selectedValue === '4') {
                $('#reasonGroup').show();
                $('#cf_reason').prop('required', true);
            } else {
                $('#reasonGroup').hide();
                $('#cf_reason').prop('required', false);
            }
        });

        // Amount formatting
        $('.amount').on('input', function() {
            let input = $(this).val().replace(/[^0-9.]/g, '');
            input = parseFloat(input).toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            $(this).val(input);
        });

        // Star rating initialization
        $('#star1').starrr({
            change: function(e, value) {
                if (value) {
                    $("#qb_result_inp").val(value);
                } else {
                    $('.your-choice-was').hide();
                    $("#qb_result_inp").val(0);
                }
            }
        });

        // Form validation rules
        let validation_rules = {
            'cf_result': ['required'],
            'cf_client_feedback': ['required'],
        }

        let validation_messages = {
            'cf_result.required': 'Result field is required',
            'cf_client_feedback.required': 'Customer Feedback is Required',
        };

        js_form_validation("client_feedbaack_form", validation_rules, validation_messages);

        // Disable submit button on form submit
        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled', true);
        });
    });
</script>

<script>
    // Separate script for AJAX form submission
    $(document).ready(function() {
        $('#client_feedbaack_form').submit(function(e) {
            e.preventDefault();
            
            $('#submitBtn').attr('disabled', true);
            
            var formData = $(this).serialize();
            
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                success: function(response) {
                    $('#feedbackMessage').text('Form submitted successfully!');
                    $('#client_feedbaack_form')[0].reset();
                    $('#submitBtn').attr('disabled', false);
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    $('#feedbackMessage').text('Form submission failed: ' + error);
                    $('#submitBtn').attr('disabled', false);
                }
            });
        });
    });
</script>

<script>
    // Function for inserting template text
    function insert(sl) {
        let sms_text = document.getElementsByClassName("temp_msg_" + sl)[0].innerHTML;
        document.getElementById("insert").innerHTML = sms_text;
        $('#modal-use-temp').modal('hide');
    }
</script>

@endsection



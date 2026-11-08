@extends('layouts.master')
@section('sms_class','menu-open')
@section('sms_campaign_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        SMS Campaign
      </h1>
    </section>
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">SMS Campaign Form</h3>
            </div>
            @if(session()->has('msg'))
              <h3 class="text-success">{{ session('msg') }}</h3>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => 'SmsController@sms_campaign_send','method' => 'post','class' => 'form-horizontal'])}}
              <div class="box-body">
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Sender ID</label>
                  <div class="col-sm-6">
                    <select class="form-control select2">
                    	<option value="">Select</option>
                        @foreach( $sender_id as $sender )
                        <option value="{{ $sender->au_sender_id }}">{{ $sender->au_sender_id }}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Team Leader ID</label>
                  <div class="col-sm-6">
                    <select class="form-control select2">
                    	<option>Select</option>
                      @foreach( $tl_name as $tl )
                      <option value="{{ $tl->au_team_id }}">{{ $tl->au_name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Customer Reason</label>
                  <div class="col-sm-6">
                    <select class="form-control select2">
                    	<option>Select</option>
                      @foreach( $sms_reason as $reason )
                      <option value="{{ $reason->src_slid }}">{{ $reason->getReasonName['sr_reason'] }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group" id="campaign_text">
                  <label for="inputEmail3" class="col-sm-2 control-label">Campaign SMS</label>
                  <div class="col-sm-6">
                    <textarea rows="8" class="count_me form-control"></textarea>
                  </div>
                  <div class="row">
                      <div class="col-md-7">
                          <div style="width: 400px; margin-left: 200px;"><span class="charleft contacts-count">&nbsp;</span><span
                                      class="parts-count"></span></div>
                      </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">Send</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{Form::close()}}
          </div>
         </section>
@endsection
@section('custom_script')
<script src="{{ asset('asset/js/jquery.textareaCounter.plugin.js')}}"></script>
<script src="{{ asset('asset/js/text-area-counter.js')}}"></script>
<script>
		  $(document).ready(function () {
		      count_textarea('#campaign_text');

		  });
		</script>
@endsection

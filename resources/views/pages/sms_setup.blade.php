@extends('layouts.master')
@section('sms_class','menu-open')
@section('sms_setup_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        SMS Setup
      </h1>
    </section>
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Change API Key</h3>
              @if( $available_bal == 0 )
              <div class="alert alert-danger text-center" role="alert">
                  Available Balance is : {{ $available_bal }}
              </div>
              @else
              <div class="alert alert-success text-center" role="alert">
                  Available Balance is : {{ $available_bal }}
              </div>
              @endif
            </div>
            @if(session()->has('msg_sms_setup'))
            <div class="alert alert-success alert-dismissible show" role="alert">
              {{ session()->get('msg_sms_setup') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => ['SmsController@sms_setup_submit'],'method' => 'post','class' => 'form-horizontal'])}}
              <div class="box-body">
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">API Key</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_api_key" value="{{ $sms_setup->au_api_key }}" class="form-control" id="inputEmail3" placeholder="Enter API Key">
                  </div>
                  @if( $errors->has('au_api_key') )
                    <p class="text-warning">{{ $errors->first('au_api_key') }}</p>
                  @endif
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Sender ID</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_sender_id" value="{{ $sms_setup->au_sender_id }}" class="form-control" id="inputEmail3" placeholder="Enter Sender ID">
                  </div>
                  @if( $errors->has('au_sender_id') )
                    <p class="text-warning">{{ $errors->first('au_sender_id') }}</p>
                  @endif
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">Update</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{Form::close()}}
          </div>
         </section>
@endsection

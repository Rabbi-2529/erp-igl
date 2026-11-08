@extends('layouts.master')
@section('content')
	<section class="content">
<section class="content-header">
      <h1>
        Template
      </h1>
    </section>
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Edit Template</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => ['SmsController@sms_temp_modify_edit',$template->st_sl_id], 'method' => 'post' , 'class' => 'form-horizontal'])}}
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Title</label>
                  <div class="col-sm-6">
                    <input type="text" value="{{ $template->st_title }}" name="st_title" class="form-control" placeholder="Enter Template Title" >
                  </div>
									@if( $errors->has('st_title') )
									<p class="text-warning"> {{ $errors->first('st_title') }} </p>
									@endif
                </div>
                <div class="form-group">
                   <label for="inputEmail3" class="col-sm-2 control-label">Message</label>
                     <div class="col-sm-6">
                      <textarea class="form-control" rows="8" name="st_message" placeholder="Enter Message...">{{ $template->st_message }}</textarea>
                     </div>
										 @if( $errors->has('st_message') )
										 <p class="text-warning"> {{ $errors->first('st_message') }} </p>
										 @endif
                </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">UPDATE</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{ Form::close() }}
          </div>
         </section>
@endsection

@extends('layouts.master')
@section('sms_class','menu-open')
@section('template_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Template Modify
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
          @if(Auth::user()->au_user_type == 3)
    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-default">
        Create Template
    </button>
@endif
                    <div class="modal fade" id="modal-default">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title">Create Template</h4>
                            </div>
                            <div class="modal-body">
                              {{Form::open(['action' => 'SmsController@create_template' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                                <div class="box-body">
                                  <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-2 control-label">Title</label>
                                    <div class="col-sm-9">
                                      <input type="text" name="st_title" class="form-control" id="inputEmail3" placeholder="Enter Message Title...">
                                    </div>
                                  </div>
                                  <div class="form-group" id="temp_text">
                                    <label for="inputEmail3" class="col-sm-2 control-label">Message</label>
                                    <div class="col-sm-9">
                                      <textarea class="count_me form-control" rows="8" name="st_message" placeholder="Enter Your Text..."></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div style="width: 400px; margin-left: 115px;"><span class="charleft contacts-count">&nbsp;</span><span
                                                        class="parts-count"></span></div>
                                        </div>
                                    </div>
                                  </div>

                              </div>
                            </div>

                            <div class="modal-footer">
                              <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                          </div>
                          {{Form::close()}}
                          <!-- /.modal-content -->
                        </div>
                <!-- /.modal-dialog -->
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped table-hover datatable">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Title</th>
                  <th>Message</th>
                  <th>Quantity</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach($templates as $template)
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $template->st_title }}</td>
                  <td>{{ $template->st_message }}</td>
                  <td align="center">{{ $template->st_quantity }}</td>
                  <td align="center">
                    <a href="{{ url('/sms_temp_modify',['id' => $template->st_sl_id]) }}" style="color:green;">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a> |
                    <a href="{{ route('pages.sms_temp_edit_del',['id' => $template->st_sl_id]) }}" onclick="return deleteTemp()" style="color: red;">
                      <span class="glyphicon glyphicon-remove"></span>
                    </a>
                  </td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
@section('custom_script')
<script src="{{ asset('asset/js/jquery.textareaCounter.plugin.js')}}"></script>
<script src="{{ asset('asset/js/text-area-counter.js')}}"></script>
<script>
		  $(document).ready(function () {
		      count_textarea('#temp_text');

		  });
		</script>
  <script type="text/javascript">
    function deleteTemp(){
      let clickDel = confirm("Are you sure want to delete this?");
      if (clickDel == true) {
        return true;
      }else{
        return false;
      }
    }
  </script>
@endsection

@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('add_reason_class','active')
@section('content')
<section class="content">
<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Add Reason/Service</h3>
            </div>
            @if(session()->has('reason_edit'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('reason_edit') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('reason_delete') )
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('reason_delete') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('msg')  )
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{ Form::open(['action' => 'SdsController@add_reason_insert' , 'method' => 'post' , 'class' => 'form-horizontal']) }}
              <div class="box-body">
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Category</label>
                  <div class="col-sm-6">
                    <select class="form-control select2" id="catagory" name="sr_catagory" required>
                      <option value="">Select</option>
                    @foreach($reasons as $reason)
                      <option value="{{ $reason->sc_slid }}">{{ $reason->sc_catagory }}</option>
                    @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Reason/Service</label>
                  <div class="col-sm-6">
                    <input type="text" name="sr_reason" class="form-control" id="inputEmail3" placeholder="Enter Reason" required>
                  </div>
                </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3" style="right:20px;">
                    <button type="submit" class="btn btn-info pull-right">Add</button>
                 </div>
              </div>
              <!-- /.box-footer -->
              </div>
            {{ Form::close() }}
              <p id="all_list"></p>
          </div>
          </section>



          <div class="modal fade" id="reason_edit_modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create Template</h4>
                  </div>
                  <div class="modal-body">
                    {{Form::open(['action' => 'SdsController@reason_edit' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Title</label>
                          <div class="col-sm-9">
                            <input type="hidden" name="id" class="form-control" id="reason_edit_id" value="">
                            <input type="text" name="edit" class="form-control" id="reason_edit_text" value="">
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
          @endsection
          @section('custom_script')
            <script type="text/javascript">
                $(document).ready(function(){
                  $('#catagory').on("change",function(){
                    let cat_id = $("#catagory").val();
                    let link = '{{ route('show_reason_list') }}';
                    $.ajax({
                      type: "GET",
                      url: link,
                      data: { cat_id: cat_id},
                      success: function (result) {
                        $("#all_list").html(result);
                      }
                    });
                  });
                });

                function open_reason_edit_modal(id, text) {
                  $("#reason_edit_id").val(id);
                  $("#reason_edit_text").val(text);
                  $("#reason_edit_modal").modal('show');
                }
                
        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled',true);
        });

            </script>
          @endsection

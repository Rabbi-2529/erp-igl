@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('find_us_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Find Us
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Add Find Option</h3>
            </div>
            @if(session()->has('find_edit'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('find_edit') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('find_up_del') )
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('find_up_del') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('msgfind')  )
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msgfind') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            <!-- /.box-header -->
            <!-- /.box-body -->
          </div>
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => 'SdsController@find_us_submit', 'method' => 'post' , 'class' => 'form-horizontal'])}}
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Find Option</label>
                  <div class="col-sm-6">
                    <input type="text" name="sf_howto" class="form-control" id="inputEmail3" placeholder="Add Find...." required>
                  </div>
                </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">Create</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{ Form::close() }}
            <div class="row" style="width: 500px; margin-left: 210px;" >
              <table id="all_list" class="table table-bordered table-striped" >
                <thead>
                  <tr>
                    <th>SL</th>
                    <th class="text-center">Category Name</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php( $sl = 0 )
                  @foreach( $find_us as $find )
                  <tr>
                    <td>{{ ++$sl }}</td>
                    <td class="text-center">{{ $find->sf_howto }}</td>
                    <td class="text-center">
                      <a href="javascript:void(0);" onclick="open_find_edit_modal('{{ $find->sf_slid }}', '{{ $find->sf_howto }}')" style="color:green;">
                      <span class="glyphicon glyphicon-edit"></span>
                      </a> |
                      <a href="{{ route('find_delete',$find->sf_slid) }}" onclick="return deleteTemp()" style="color: red;">
                        <span class="glyphicon glyphicon-remove"></span>
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>


          <div class="modal fade" id="find_edit_modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Edit Find Us</h4>
                  </div>
                  <div class="modal-body">
                    {{Form::open(['action' => 'SdsController@find_us_edit' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Category</label>
                          <div class="col-sm-9">
                            <input type="hidden" name="id" class="form-control" id="find_edit_id" value="">
                            <input type="text" name="how" class="form-control" id="find_edit_text" value="">
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
         </section>
@endsection
@section('custom_script')
<script type="text/javascript">
function deleteTemp(){
  let clickDel = confirm("Are you sure want to delete this?");
  if (clickDel == true) {
    return true;
  }else{
    return false;
  }
}

function open_find_edit_modal(id, text) {
  $("#find_edit_id").val(id);
  $("#find_edit_text").val(text);
  $("#find_edit_modal").modal('show');
}

        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled',true);
        });
</script>
@endsection

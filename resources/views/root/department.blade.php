@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('department_class','menu-open')
@section('department_add_class','active')
@section('content')
<section class="content">
<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Add Department</h3>
            </div>
            @if(session()->has('delete'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('delete') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('update') )
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('update') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('success')  )
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('exist')  )
            <div class="alert alert-warning alert-dismissible" role="alert">
                {{ session('exist') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{ Form::open(['route' => 'department-store' , 'method' => 'post' , 'class' => 'form-horizontal']) }}
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Name</label>
                  <div class="col-sm-6">
                    <input type="text" name="dept" class="form-control" id="inputEmail3" placeholder="Enter Name" required>
                  </div>
                  @error('dept')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror
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
            <div class="row" style="width: 500px; margin-left: 210px;" >
                <table id="all_list" class="table table-bordered table-striped" >
                  <thead>
                    <tr>
                      <th>SL</th>
                      <th class="text-center">Department Name</th>
                      <th class="text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php( $sl = 0 )
                    @foreach( $depts as $dept )
                    <tr>
                      <td>{{ ++$sl }}</td>
                      <td class="text-center">{{ $dept->name }}</td>
                      <td class="text-center">
                        <a href="javascript:void(0);" onclick="departmentEdit('{{ $dept->id }}', '{{ $dept->name }}')" style="color:green;">
                        <span class="glyphicon glyphicon-edit"></span>
                        </a> |
                        <a href="{{ route('department-delete',$dept->id) }}" onclick="return confirm('Are you sure to delete Department?')" style="color: red;">
                          <span class="glyphicon glyphicon-remove"></span>
                        </a>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
          </div>
          </section>



          <div class="modal fade" id="department_edit_modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Department Edit</h4>
                  </div>
                  <div class="modal-body">
                    {{Form::open(['route' => 'department-update' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Title</label>
                          <div class="col-sm-9">
                            <input type="hidden" name="id" class="form-control" id="dept_edit_id" value="">
                            <input type="text" name="dept_name" class="form-control" id="dept_edit_text" value="">
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

                function departmentEdit(id, text) {
                  $("#dept_edit_id").val(id);
                  $("#dept_edit_text").val(text);
                  $("#department_edit_modal").modal('show');
                }
                
        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled',true);
        });

            </script>
          @endsection

@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('add_category_class','active')
@section('content')
<section class="content">
<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Add Reason/Service Category</h3>
            </div>
            @if(session()->has('catagory_edit'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('catagory_edit') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('catagory_delete') )
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('catagory_delete') }}
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
            <div class="box-body">
            {{ Form::open(['action' => 'SdsController@add_category_insert' , 'method' => 'post' , 'class' => 'form-horizontal']) }}
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Category</label>
                  <div class="col-sm-6">
                    <input type="text" name="sc_catagory" class="form-control" id="inputEmail3" placeholder="Enter Category" required>
                  </div>
                </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3" style="right:20px;">
                    <button type="submit" class="btn btn-info pull-right">Add</button>
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
                  @foreach( $cat as $category )
                  <tr>
                    <td>{{ ++$sl }}</td>
                    <td class="text-center">{{ $category->sc_catagory }}</td>
                    <td class="text-center">
                      <a href="javascript:void(0);" onclick="open_catagory_edit_modal('{{ $category->sc_slid }}', '{{ $category->sc_catagory }}')" style="color:green;">
                      <span class="glyphicon glyphicon-edit"></span>
                      </a> |
                      <a href="{{ route('catagory_delete',$category->sc_slid) }}" onclick="return deleteTemp()" style="color: red;">
                        <span class="glyphicon glyphicon-remove"></span>
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            </div>
          </div>


          <div class="modal fade" id="catagory_edit_modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Edit Category</h4>
                  </div>
                  <div class="modal-body">
                    {{Form::open(['action' => 'SdsController@catagory_edit' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                      <div class="box-body">
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Category</label>
                          <div class="col-sm-9">
                            <input type="hidden" name="id" class="form-control" id="catagory_edit_id" value="">
                            <input type="text" name="catagory_edit" class="form-control" id="catagory_edit_text" value="">
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

              function open_catagory_edit_modal(id, text) {
                $("#catagory_edit_id").val(id);
                $("#catagory_edit_text").val(text);
                $("#catagory_edit_modal").modal('show');
              }
              
                $('form').submit(function() {
                        $(this).find("button[type='submit']").prop('disabled',true);
                      });
              </script>
          @endsection

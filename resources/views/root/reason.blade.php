@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_reason','active')
@section('content')
    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Manage Reason</h3>
                <button class="btn btn-sm btn-primary " style="float: right" data-toggle="modal" data-target="#reason_add_modal">ADD</button>
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
            <div class="row" style="width: 500px; margin-left: 210px;" >
                <table id="all_list" class="table table-bordered table-striped" >
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th class="text-center">Reason Name</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php( $sl = 0 )
                    @foreach( $reasons as $reason )
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td class="text-center">{{ $reason->name }}</td>
                            <td class="text-center">
                                <a href="javascript:void(0);" onclick="reasonEdit('{{ $reason->id }}', '{{ $reason->name }}')" style="color:green;">
                                    <span class="glyphicon glyphicon-edit"></span>
                                </a> |
                                <a href="{{ url('root/reason/delete',$reason->id) }}" onclick="return confirm('Are you sure to delete Department?')" style="color: red;">
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

    <div class="modal fade" id="reason_add_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Reason</h4>
                </div>
                <div class="modal-body">
                    {{Form::open(['url' => 'root/reason/store' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Title</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="id" class="form-control"  value="">
                                <input type="text" name="name" class="form-control"  value="">
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

    <div class="modal fade" id="reason_edit_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Reason Edit</h4>
                </div>
                <div class="modal-body">
                    {{Form::open(['url' => 'root/reason/update' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Title</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="id" class="form-control" id="reason_edit_id" value="">
                                <input type="text" name="name" class="form-control" id="reason_edit_text" value="">
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

        });
        function reasonEdit(id, text) {
            $("#reason_edit_id").val(id);
            $("#reason_edit_text").val(text);
            $("#reason_edit_modal").modal('show');
        }
        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled',true);
        });

    </script>
@endsection

@extends('layouts.master')

@section('title', 'Add Shift')

@section('human_resource_class', 'active')
@section('payroll', 'active')
@section('shift_management', 'active')
@section('list_class', 'active')


@section('content')



<section class="content">
        @if(session()->has('det_up'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('det_up') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('pro_del'))
              <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('pro_del') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
        @endif
            <section class="content-header">
                  <h1>
                    Shift
                  </h1>
                </section>
                <div class="box">
                        <div class="box-header">
                          <h3 class="box-title">Shift List</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                          <table id="example1"  class="table table-bordered table-striped table-hover datatable">
                            <thead>
                            <tr>
                              <th>SL</th>
                              <th>Shift Name</th>
                              <th>Working Hour</th>
                              <th>Shift Hour</th>
                              <th>Late Entry(in minute)</th>
                              <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php($sl=0)
                            @foreach ($shifts as $shift)
                            <tr>
                                <td>{{ ++$sl }}</td>
                                <td>{{ $shift->name }}</td>
                                <td>{{ $shift->working_hour }}</td>
                                <td class="text-center">{{ number_format($shift->shift_hour/60,2) }}</td>
                                <td class="text-right">{{ $shift->late_entry }}</td>
                                <td align="center">
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-xs">Action</button>
                                    <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                      <span class="caret"></span>
                                      <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                                      <li><a href="{{ route('payroll.shift.editShift', $shift->id) }}">Update</a></li>
                                      <li class="divider"></li>
                                      <li><a href="">Assign</a></li>
              
                                      
                                    </ul>
                              </div>
                                </td>
                              </tr>
                            @endforeach
                            </tbody>
                          </table>
                        </div>
                        <!-- /.box-body -->
                      </div>
                     </section>

                     <div class="modal fade" id="shiftModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Shift Update</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body" id="shiftModalData">
                            ...
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
@endsection
@section('custom_style')
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
        <style>
            .action_btn{
                border: 1px solid;
                padding: 5px;
            }
            .btn_show{
                background-color: green;
                color: white;
            }
            .btn_delete{
                background-color: red;
                color: white;
            }
            .btn_edit{
                background-color: cornflowerblue;
                color: white;
            }
        </style>
        @endsection
        @section('custom_script')
        {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> --}}
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
            
    <script type="text/javascript">
  

       function shiftUpdate(value){
        // console.log(value);
        //  $.ajax({
        //   type: "GET",
        //   url: "{{ route('payroll.shift.shift-ajax') }}",
        //   data: {value:value},
        //   success:function(result){
        //     $("#shiftModalData").html(result);
        //     $("#shiftModal").modal("show");
        //   }
        //  })

         $.ajax({
          type: "GET",
          url: "{{ route('payroll.shift.shift-ajax') }}",
          data: {value:value},
          success:function(result){
            $('#shiftModalData').html(result);
            $('#shiftModal').modal('show');
          }
        })
       }


      function deleteprocat(){
        let clickDel = confirm("Are you sure want to delete this?");
        if (clickDel == true) {
          return true;
        }else{
          return false;
        }
      }
    </script>
    @endsection
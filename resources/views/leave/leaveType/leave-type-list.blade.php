@extends('layout.master')
@section('payroll_class','menu-open')
@section('leave_management','menu-open')
@section('leave_list','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('del'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('del') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('update'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('update') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
          <h1>
            Leave Type
          </h1>
        </section>
        <div class="box">
                <div class="box-header">
                  <h3 class="box-title">All Leave Type</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>SL</th>
                        <th>Leave Type</th>
                        <th>Leave Days</th> 
                        <th class="text-center">Action</th>
                  
                      
                    </tr>
                    </thead>
                    <tbody> 
                        @foreach ($leaveType as $key=> $item)
                        <tr>  
                            <td>{{ $key+1 }}</td>
                            <td>{{ $item->leave_type }}</td>
                            <td>{{ $item->l_day }}</td>
                            <td>
                                <a href="{{ route('leave.leaveTypeDelete',$item->id) }}" onclick="return confirm('Are you sure?')" class="action_btn btn_delete">
                                    <i class="fa fa-trash"></i>
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
@section('custom_style')
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


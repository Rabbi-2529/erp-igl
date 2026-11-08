@extends('layouts.master')
@section('title', 'Category List')
@section('inventory_class','active')
@section('product_class','active')
@section('inv_pro_type','active')
@section('type_list','active')
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                <h3 class="box-title">Category List</h3>
                </div>
            </div>
        @if(session()->has('mod_up'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('mod_up') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('delete_msg'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('delete_msg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    
                        <!-- /.box-header -->
                        <div class="panel-body">
                          <table id="example1" class="table table-bordered table-striped table-hover datatable">
                            <thead>
                            <tr>
                              <th>SL</th>
                              <th>Category Name</th>
                              <th>type Name</th>
                              <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php($sl=0)
                            @foreach ($pro_type as $type)
                            <tr>
                                <td>{{ ++$sl }}</td>
                                <td>{{ $type->type_category['inv_pro_grp_name'] }}</td>
                                <td>{{ $type->inv_pro_type_name }}</td>
                                <td align="center">
                                    <a href="{{ route('inventory.type_show',$type->inv_pro_type_id) }}" class="action_btn btn_show">
                                      <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.type_delete',$type->inv_pro_type_id) }}" onclick="return deleteprocat()" class="action_btn btn_delete">
                                      <i class="fa fa-trash"></i>
                                    </a>
                                    <a href="{{ route('inventory.type_edit_page',$type->inv_pro_type_id) }}" class="action_btn btn_edit">
                                      <i class="fa fa-edit"></i>
                                    </a>
                                </td>
                              </tr>
                            @endforeach
                            </tbody>
                          </table>
                        </div>
                        <!-- /.box-body -->
                      </div>
                    </div>
                    </div>
                   
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
    @section('custom_script')
    <script type="text/javascript">
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
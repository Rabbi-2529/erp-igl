@extends('layouts.master')
@section('title','Supplier List')
@section('inventory_class','active')
@section('supplier_class','active')
@section('inv_supplier_class','active')
@section('supplier_list','active')
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
            Supplier
          </h1>
        </section>
        <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Supplier List</h4>
                </div>
            </div>

            <div class="panel-body">
                <!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                      <th>SL</th>
                      <th>Supplier Name</th>
                      <th>Person</th>
                      <th>Mobile</th>
                      <th>Complain Number</th>
                      <th>Address</th>
                      <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php($sl=0)
                    @foreach ($suppliers as $sup)
                    <tr>
                        <td>{{ ++$sl }}</td>
                        <td>{{ $sup->inv_sup_com_name }}</td>
                        <td>{{ $sup->inv_sup_person }}</td>
                        <td>{{ $sup->inv_sup_mobile }}</td>
                        <td>{{ $sup->inv_sup_complain_num }}</td>
                        <td>{{ $sup->inv_sup_address }}</td>
                        <td align="center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-xs">Action</button>
                                <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                  <span class="caret"></span>
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                                  <li>
                                    <a href="{{ route('inventory.list_show',['id' => $sup->inv_sup_id]) }}">Show</a>
                                  </li>
                                  <li class="divider"></li>
                                  {{-- <li>
                                    <a href="{{ route('inventory.supplier_delete',['id' => $sup->inv_sup_id]) }}" onclick="return deletesup()">Delete</a>
                                  </li> 
                                  <li class="divider"></li> --}}
                                  <li>
                                    <a href="{{ route('inventory.supplier_show',['id' => $sup->inv_sup_id]) }}">Edit</a>
                                  </li>
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
  function deletesup(){
    let clickDel = confirm("Are you sure want to delete this?");
    if (clickDel == true) {
      return true;
    }else{
      return false;
    }
  }
</script>
@endsection
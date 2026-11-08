@extends('layouts.master')
@section('inventory_class','menu-open')
@section('product_class','menu-open')
@section('list_sup_pro','active')
@section('content')
<section class="content">
        <section class="content-header">
            @if(session()->has('sup_pro_del'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('sup_pro_del') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            @if(session()->has('sup_pro_edit'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('sup_pro_edit') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
              <h1>
                Supplier Product
              </h1>
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Supplier Product List</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Supplier Name</th>
                          <th>Supplier Product</th>
                          <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @foreach ($sup_pro as $product)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $product->supplier_info['inv_sup_com_name'] }}</td>
                            <td>{{ $product->product_info['inv_pro_det_pro_id'] }}</td>
                            <td align="center">
                                <a href="{{ route('inventory.sup_pro_show',['id' => $product->inv_sup_pro_id]) }}" class="action_btn btn_show">
                                  <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('inventory.sup_pro_del',['id' => $product->inv_sup_pro_id]) }}" class="action_btn btn_delete" onclick="return deletesup()">
                                  <i class="fa fa-trash"></i>
                                </a>
                                <a href="{{ route('inventory.sup_pro_edit',['id' => $product->inv_sup_pro_id]) }}" class="action_btn btn_edit">
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
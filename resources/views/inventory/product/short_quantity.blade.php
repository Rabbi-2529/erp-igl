@extends('layouts.master')
@section('title','Short Quantity')
@section('inventory_class','active')
@section('short_quantity','active')
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
                Product
              </h1>
            </section>
            <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Product List</h3>
                        </div>
                    </div>

                  <div class="panel-body" ></div>

                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Type Name</th>
                          <th>Product Name</th>
                          <th>Available Stock</th>
                          <th>Short Qty</th>
                          <th>Buy Price</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @foreach ($pro_det as $detail)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $detail->type_info['inv_pro_type_name'] }}</td>
                              <td>{{ $detail->product->inv_pro_name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $detail->inv_pro_det_available_qty }}</td>
                            <td class="text-center">
                                @if ( $detail->inv_pro_det_short_qty == 0)
                                    N/A
                                @else
                                {{ $detail->inv_pro_det_short_qty }}
                                @endif
                            </td>
                            <td class="text-right">{{ $detail->inv_pro_det_buy_price }}</td>
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

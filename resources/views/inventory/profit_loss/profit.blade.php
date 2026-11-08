@extends('layouts.master')
@section('title','Product Profit')
@section('inventory_class','active')
@section('profit_loss','active')
@section('profit_class','active')
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
                Profit
              </h1>
            </section>
            <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Product Profit</h3>
                        </div>
                    </div>

                  <div class="panel-body" ></div>

                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Date</th>
                          <th>Product Name</th>
                          <th>Product Qty</th>
                          <th>Return Qty</th>
                          <th>Sell Qty</th>
                          <th>Buy Price</th>
                          <th>Sell Price</th>
                          <th class="text-center">Profit</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        
                        @foreach ($profits as $profit)
                        
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td></td>
                            <td>{{ $profit->getProductInfo['inv_pro_det_pro_id'] }}</td>
                            <td class="text-center">{{ $profit->inv_pro_inv_qty }}</td>
                            <td class="text-center">{{ $profit->inv_pro_inv_invoice_no }}</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-right"></td>
                            <td align="center"></td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-right" colspan="8">Total Discount</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="8">Total Profit</td>
                                <td></td>
                            </tr>
                        </tfoot>
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
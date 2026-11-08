@extends('layouts.master')
@section('title','Product List')
@section('inventory_class','active')
@section('warranty_claim_class','active')
@section('warranty_claim','active')
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
          <div class="box">
            <div class="box-body">
              <div class="col-md-3">
                <input type="text" class="form-control" onkeyup="productSerial(this.value)">
              </div>
            </div>
          </div>
              <h1 style="margin-top: 0px;">
                Product
              </h1>
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Product List</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body" id="products">
                      
                      {{-- <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Product Name</th>
                          <th>Serial No</th>
                          <th>Invoice</th>
                          <th>Buy Price</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @foreach ($products as $detail)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $detail->getProductName->inv_pro_det_pro_name }}</td>
                            <td>{{ $detail->inv_pro_invdet_slno }}</td>
                            <td>{{ $detail->getProductName->getInvoiceNo->inv_pro_inv_invoice_no }}</td>
                            <td class="text-right">{{ $detail->inv_pro_det_buy_price }}</td>
                          </tr>
                        @endforeach
                        </tbody>
                      </table> --}}
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

  function productSerial(proSlNo){
    $.ajax({
      type: "GET",
      url: "{{ route('inventory.warranty-claim-ajax') }}",
      data: {proSlNo:proSlNo},
      success:function(result){
        $('#products').html(result);
      }
    });
  }
</script>
@endsection

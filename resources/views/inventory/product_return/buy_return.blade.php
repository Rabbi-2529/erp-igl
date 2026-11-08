@extends('layouts.master')
@section('title','Buy Return')
@section('inventory_class','active')
@section('return_class','active')
@section('buy_return','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('sub_success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('sub_success') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('sub_err'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('sub_err') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
          <h1>
            Return
          </h1>
        </section>
                <!-- /.box-header -->
                <!-- form start -->
                {{ Form::open(['action' => 'Inventory\ProductBuyReturnController@invTemporaryProduct' , 'method' => 'get' , 'class' => 'form-inline']) }}
                <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Buy Return</h3>
                        </div>
                    </div>
                    <div class="panel-body" >
                 <!-- /.box-header -->
                 <!-- form start -->
                <div class="form-group" style="margin-left:200px;">
                    <label for="inputEmail3" class="col-sm-3 text-right control-label">Invoice :</label>
                    <div class="col-sm-8">
                        <select id="inv_no" class="form-control select2" style="width: 299px;" required>
                            <option value="">Select One</option>
                            @foreach ($buy_return as $return)
                            <option value="{{ $return->inv_pro_inv_invoice_no }}" {{ ($added_invoice_no == $return->inv_pro_inv_invoice_no)? 'selected': '' }}>
                                {{ $return->inv_pro_inv_invoice_no }} 
                            </option>  
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4">
                        <button type="button" id="pro_search" name="search" class="btn btn-info" onclick="show_invoice_products()">Search</button>
                    </div>
                </div>
            <div class="box-body">
            <div class="col-sm-12 product_wrapper">
                    <table id="sell_product_list_table" class="table table-bordered table-striped table-hover datatable">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Invoice</th>
                                <th>Product Name</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Amount</th>
                                <th>Buy</th>
                            </tr>
                            </thead>
                            <tbody id="product_table_body">
                            
                            </tbody>
                        </table>
                    </div>


                  </div>
                  <div class="box-body">

                    <div class="col-sm-12">
                                <div class="box shopping_cart">
                                        <div class="box-header with-border">
                                                <h3 class="box-title"><i class="fa fa-shopping-cart"></i>
                                                    Sales Invoice
                                                </h3>
                                                </div>   
                                    <table class="table table-bordered table-striped table-hover datatable">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>Return Qty</th>
                                                <th>Unit Price</th>
                                                <th>Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="show-cart-conten">
                                            
                                        </tbody>
                                    </table>
                                </div>
                                <button class="btn btn-success btn-sm pull-right">Submit</button>
                            {{ Form::close() }}
                        </div>
                        </div>
              </div>
             </section>

             @include('pages.ajax.warrenty_product_get_sl_no');
@endsection
@section('custom_style')
<style>
  .form-control::-webkit-inner-spin-button,
  .form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
    .all-product-sl-no-main-wrapper {
        border: 2px solid #ddd;
        padding: 7px;
        height: 350px;
        overflow: scroll;
        position: absolute;
    }
    .product-warrenty-sl {
        margin-top: 5px;
    }
    .table>tbody>tr>td{
        padding: 2px;
    }
    .shopping_cart{
        height: 300px;
        border: 1px solid grey;
        overflow: scroll;
    }
    input {
        height: 27px !important;
    }
    .btn-sm {
        padding: 3px 8px;
    }
</style>
@endsection
@section('custom_script')
<script>
$(document).ready(function(){
    getCartProduct();
    let inv_no = $("#inv_no").val();
    if(inv_no != '') {
        show_invoice_products();
    }
    $( "#from2" ).datepicker({
        daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
    });
    $( "#to" ).datepicker({
        daysOfWeekHighlighted: "7",
        todayHighlight: true,
    });

    @if(session()->has('print_invoice'))
        let sell_print = "{!! route('reports.buy-return-print', session()->get('print_invoice')) !!}";
        window.open(sell_print, '_blank');
    @endif

    // Initialize DataTable
    initializeDataTable();

    $('#pro_search').on("click",function(){
        // Additional code for search functionality if needed
    });
});

function initializeDataTable() {
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#sell_product_list_table')) {
        $('#sell_product_list_table').DataTable().destroy();
    }

    $('#sell_product_list_table').DataTable({
        pageLength: 5,
        lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']],
        responsive: true
    });
}

function show_invoice_products() {
    let inv_no = $("#inv_no").val();
    let link = "{{ route('product_return.buy-return-ajax') }}";
    $.ajax({
        type: "GET",
        url: link,
        data: {inv_no: inv_no},
        success: function (result) {
            $("#product_table_body").html(result);
            getCartProduct();
            initializeDataTable(); // Reinitialize DataTable after updating content
        }
    });
}

function addtocart(inv_id) {
    let pro_qty = parseFloat($("#pro_qty_"+inv_id).val());
    if(isNaN(pro_qty)) {
        alert("quantity field can't be empty");
    } else if(pro_qty < 1) {
        alert("minimum quantity at least 1");
    } else {
        let route_url = "{{ route('product_return.buy.add-to-cart') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: { inv_id: inv_id, pro_qty: pro_qty },
            success: function (result) {
                if(result.status == 400) {
                    alert("Stock has been cross its limit");
                } else {
                    getCartProduct();
                }
            }
        });
    }
}

function getCartProduct() {
    let route_url = "{{ route('product_return.buy.get-cart') }}";
    $.ajax({
        type: "GET",
        url: route_url,
        data: {},
        success: function (result) {
            $("#show-cart-conten").html(result);
        }
    });
}

function remove_cart(content_id) {
    let clickDel = confirm("Are you sure want to delete this?");
    if (clickDel == true) {
        let route_url = "{{ route('product_return.buy.remove-cart') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: {content_id:content_id},
            success: function (result) {
                getCartProduct();
            }
        });
    }
}

function addWarrentyProduct(pro_det_id) {
    let pro_price = parseFloat($("#pro_price_"+pro_det_id).val());
    let route_url = "{{ route('buy.add-to-cart-warrenty-product') }}";
    $.ajax({
        type: "GET",
        url: route_url,
        data: { pro_id: pro_det_id, pro_price: pro_price},
        success: function (result) {
            if(result.status == 404) {
                alert("Invalid Warranty Product");
            } else if(result.status == 406) {
                alert("Invalid Warranty Product");
            } else {
                $("#warrenty_product_get_sl_no .modal-content").html(result);
                $("#warrenty_product_get_sl_no").modal('show');
            }
        }
    });
}

function check_sl_no(value, pro_det_id) {
    let sl_no = value;
    let route_url = "{{ route('buy.add-warrenty-product-sl-no') }}";
    $.ajax({
        type: "GET",
        url: route_url,
        data: { pro_id: pro_det_id, sl_no: sl_no},
        success: function (result) {
            if(result.status == 404) {
                alert("Invalid Warranty Product");
            } else if(result.status == 402) {
                alert("Already Added");
            } else {
                $("#all-added-warrenty-product-id ul").html(result);
                $("#w_product_sl_scan_inp").val("");
                getCartProduct();
            }
        }
    });
}

function remove_product_sl(product_id, sl_no) {
    let route_url = "{{ route('buy.remove-warrenty-product-sl') }}";
    $.ajax({
        type: "GET",
        url: route_url,
        data: { pro_id: product_id, sl_no: sl_no},
        success: function (result) {
            if(result.status == 404) {
                alert("Invalid Warranty Product");
            } else if(result.status == 406) {
                alert("Invalid Warranty Product");
            } else {
                $("#all-added-warrenty-product-id ul").html(result);
                getCartProduct();
            }
        }
    });
}
</script>
@endsection
@extends('layouts.master')
@section('inventory_class','menu-open')
@section('pro_inv_class','menu-open')
@section('sell_pro','active')
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
            Sell
          </h1>
        </section>
                <!-- /.box-header -->
                <!-- form start -->
                {{ Form::open(['action' => 'Inventory\InventoryCartController@invTemporaryProduct' , 'method' => 'get' , 'class' => 'form-horizontal']) }}
                <div class="box">
                 <div class="box-header with-border">
                   <h3 class="box-title">Add Product</h3>
                 </div>
                 <!-- /.box-header -->
                 <!-- form start -->
                 <div class="row">
                     <div class="col-sm-4">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-3 text-right control-label">Customer :</label>
                            <div class="col-sm-6">
                                <select name="customer" id="" class="form-control select2" style="width: 299px;" required>
                                    <option value="">Select One</option>
                                    @foreach ($customers as $customer)
                                    <option value="{{ $customer->inv_cus_id }}">
                                        {{ $customer->inv_cus_name }} ({{ $customer->inv_cus_com_name }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                     </div>
                     
                     <!-- ADD DATE FIELD HERE -->
                     <div class="col-sm-4">
                        <div class="form-group">
                            <label for="date" class="col-sm-3 text-right control-label">Date :</label>
                            <div class="col-sm-6">
                                <?php
                                    $date = date('Y-m-d');
                                    $escapedDate = htmlspecialchars($date, ENT_QUOTES, 'UTF-8');
                                ?>
                                <input class="datepicker form-control" type="text" name="inv_pro_temp_created_at" id="date" required value="<?php echo $escapedDate; ?>" style="width: 200px;" />
                            </div>
                        </div>
                     </div>
                     <!-- END DATE FIELD -->
                     
                     @if (Auth::user()->au_user_type == 4)
                     <div class="col-sm-4">
                         <div class="form-group">
                            <label for="inputEmail3" class="col-sm-3 text-right control-label">Employee :</label>
                            <div class="col-sm-6">
                                <select name="employee" id="" class="form-control select2" style="width: 299px;" required>
                                    <option value="">Select One</option>
                                    @foreach ($employees as $emp)
                                    <option value="{{ $emp->au_id }}">
                                        {{ $emp->au_name }} ({{ $emp->au_mobile }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                     </div>
                     @else
                         <input type="hidden" name="employee" value="{{Auth::user()->au_id}}">
                     @endif
                 </div>
                <div class="box-body">
                <div class="col-sm-12 text-center">

                <div class="col-sm-5">
                        <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Group:</label>
                                <div class="col-sm-5">
                                    <select name="group" style="width:200px;" id="product_category" class="form-control select2">
                                        <option value="">Select Group</option>
                                        @foreach ($groups as $group)
                                        <option value="{{ $group->inv_pro_grp_id }}">
                                            {{ $group->inv_pro_grp_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                        </div>
                </div>
                <div class="col-sm-5">
                        <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">Type:</label>
                                <div class="col-sm-5 product_category_wrapper">
                                    <select name="type" style="width:200px;" id="product_model" class="form-control select2">
                                        <option value="">Select Type First</option>

                                    </select>
                                </div>
                        </div>
                </div>
                <div class="col-xs-2">
                    <button type="button" id="pro_search"  class="btn btn-info" name="searchbtn">Search</button>
                </div>
                </div>
                </div>
            <div class="box-body">
            <div class="col-sm-12 product_wrapper">
                    <table id="sell_product_list_table" class="table table-bordered table-striped table-hover datatable">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>A. Stock</th>
                                <th>Price</th>
                                <th style="width: 110px; text-align: center;">Sell</th>
                            </tr>
                            </thead>
                            <tbody id="product_table_body">
                            @php($sl=0)
                            @foreach ($sell_pro as $sell)
                                <tr>
                                    <td class="text-center">{{ ++$sl }}</td>
                                    <td>{{ optional($sell->product)->inv_pro_name }}</td>
                                    <td>
                                        {{ $sell->inv_pro_det_pro_description }}
                                        ({{ $sell->type_info['inv_pro_type_name'] }})
                                    </td>
                                    <td align="center">{{ $sell->inv_pro_det_available_qty }}</td>
                                    <td class="text-center"><input type="text" autocomplete="off" class="form-control" id="pro_price_{{ $sell->inv_pro_det_id }}" style="width: 100px;" value="{{ $sell->inv_pro_det_sell_price }}"></td>
                                    <td class="text-center">
                                        @if($sell->inv_pro_det_pro_warranty == 0)
                                        <input type="text" autocomplete="off" class="form-control" style="width: 50px;" id="pro_qty_{{ $sell->inv_pro_det_id }}" placeholder="Qty">
                                        <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $sell->inv_pro_det_id }}')">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                        @else
                                        <input type="text" autocomplete="off" class="form-control" style="width: 50px;" placeholder="N/A" disabled>
                                        <button type="button" class="btn btn-success btn-sm" onclick="addWarrentyProduct('{{ $sell->inv_pro_det_id }}')">
                                                <i class="fa fa-plus"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td class="text-center service">#</td>
                                <td colspan="3" class="text-right service">
                                    <b>Service Charges & Qty : </b>
                                </td>
                                <td class="text-center service" >
                                    <input type="text" id="service" autocomplete="off" style="width:100px;" name="service" class="form-control" placeholder="Service">
                                </td>
                                <td class="text-center service">
                                    <input type="text" id="qty" autocomplete="off" style="width:50px;" name="service" class="form-control" placeholder="Qty">
                                    <button type="button" class="btn btn-success btn-sm" onclick="addServiceCharges()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    

                  </div>
                  <div class="box-body">

                    <div class="col-sm-12">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-shopping-cart"></i>
                                        Sales Invoice
                                    </h3>
                                </div>
                                <div class="box shopping_cart">

                                    <table class="table table-bordered table-striped table-hover datatable">
                                        <thead>
                                            <tr>
                                                <th>Sl</th>
                                                <th>Description</th>
                                                <th>Qty</th>
                                                <th>Unit Price</th>
                                                <th style="width:140px;">Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="show-cart-conten">

                                        </tbody>
                                    </table>
                                </div>
                                <div class="form-group" style="float: right;">
                                    <label for="inputEmail3" class="col-sm-2 control-label">Total: </label>
                                    <div class="col-sm-6">
                                        <input type="text" class="text-right" disabled autocomplete="off" value="" class="form-control" id="total_sell">
                                    </div>
                                <button class="btn btn-success btn-sm pull-right">Continue</button>
                            </div>

                            {{ Form::close() }}
                        </div>
                        </div>
              </div>
             </section>
             <button hidden id="open_sell_print_invoice_btn"></button>
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
    .service{
        padding: 2px 0 !important;

    }

</style>
@endsection
@section('custom_script')
<script>
    // Initialize datepicker
    $(document).ready(function(){
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    });

    $(document).ready(function(){
        $('#product_category').on("change",function(){
        let grp_id = $("#product_category").val();
        let link = "{{ route('buy.type_submit_ajax') }}";
        $.ajax({
            type: "GET",
            url: link,
            data: { grp_id: grp_id},
            success: function (result) {
            $(".product_category_wrapper").html(result);
            }
        });
        });

        $('#pro_search').on("click",function(){
            let type_id = $("#product_model").val();
            let link = "{{ route('buy.product-search') }}";
            $.ajax({
                type: "GET",
                url: link,
                data: { type_id: type_id},
                success: function (result) {
                $(".product_wrapper").html(result);
                }
            });
        });
    });


$(document).ready(function(){

@if(session()->has('print_invoice'))
    let route = "{!! route('reports.sell-print', session()->get('print_invoice')) !!}?print=1";
    window.open(route, '_blank');
@endif

$( "#from" ).datepicker({
    daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
    });
$( "#to" ).datepicker({
    daysOfWeekHighlighted: "7",
        todayHighlight: true,
    });

    var table = $('#sell_product_list_table').DataTable({
        pageLength : 5,
        lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']]
    } );
});



    function formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") {
    try {
        decimalCount = Math.abs(decimalCount);
        decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

        const negativeSign = amount < 0 ? "-" : "";

        let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
        let j = (i.length > 3) ? i.length % 3 : 0;

        return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
    } catch (e) {
        console.log(e)
    }
    }


function total_sell_amount(){
    let amount = 0;
    $('.temp_cart').each(function(i, obj) {
        amount = amount + parseFloat(obj.innerText.split(',').join(''));
    });
    let discount = parseFloat($(".temp_cart_discount").val());
    let delivery = parseFloat($(".temp_cart_delivery").val());
    if (isNaN(discount)) {
       discount = 0;
    }
    if (isNaN(delivery)) {
        delivery = 0;
    }
    amount = amount + delivery - discount;
    $('#total_sell').val(formatMoney(amount));
}

$(document).ready(function(){
    getCartProduct();
    $( "#from2" ).datepicker({
        daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
    });
    $( "#to" ).datepicker({
        daysOfWeekHighlighted: "7",
        todayHighlight: true,
    });

});

function addtocart(pro_det_id) {
    let pro_qty = parseFloat($("#pro_qty_"+pro_det_id).val());
    let pro_price = parseFloat($("#pro_price_"+pro_det_id).val());
    let service = parseInt($("#service").val());
    let qty = parseFloat($("#qty").val());
    if(isNaN(pro_qty) || isNaN(pro_price)) {
        alert("quantity field can\'t be empty");
    } else if(pro_qty < 1) {
        alert("minimum quantity at least 1");
    } else {

        let route_url = "{{ route('buy.add-to-cart') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: { pro_id: pro_det_id, pro_qty: pro_qty, pro_price: pro_price},
            success: function (result) {
                if(result.status == 400) {
                    alert("Stock has been cross it's limit");
                } else {
                    getCartProduct();
                }
            }
        });
    }
}

function addServiceCharges() {
    let service = parseInt($("#service").val());
    let qty = parseFloat($("#qty").val());
    if(isNaN(qty) || isNaN(service)) {
        alert("quantity field can\'t be empty");
    } else if(qty < 1) {
        alert("minimum quantity at least 1");
    } else {

        let route_url = "{{ route('buy.add-to-cart-service-charge') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: { qty: qty, service: service},
            success: function (result) {
                if(result.status == 400) {
                    alert("Stock has been cross it's limit");
                } else {
                    getCartProduct();
                }
            }
        });
    }
}

function getCartProduct() {
    let route_url = "{{ route('buy.get-cart') }}";
    $.ajax({
        type: "GET",
        url: route_url,
        data: {},
        success: function (result) {

            $("#show-cart-conten").html(result);
            total_sell_amount();
        }
    });
}

function update_cart_quantity(content_id) {
    let pro_quantity = parseFloat($("#update_qty_"+content_id).val());
    if(pro_quantity < 1) {
        alert("Minimum Quantity is 1");
        return false;
    }
    let route_url = "{{ route('buy.update-cart') }}";
    $.ajax({
        type: "GET",
        url: route_url,
        data: {content_id:content_id, pro_qty:pro_quantity},
        success: function (result) {
            getCartProduct();
        }
    });
}

function remove_cart(content_id) {
    let clickDel = confirm("Are you sure want to delete this?");
    if (clickDel == true) {

    }else{
        return false;
    }
    let route_url = "{{ route('buy.remove-cart') }}";
    $.ajax({
        type: "GET",
        url: route_url,
        data: {content_id:content_id},
        success: function (result) {
            getCartProduct();
        }
    });
}

function addWarrentyProduct (pro_det_id) {
    let pro_price = parseFloat($("#pro_price_"+pro_det_id).val());

    let route_url = "{{ route('buy.add-to-cart-warrenty-product') }}";
    $.ajax({
        type: "GET",
        url: route_url,
        data: { pro_id: pro_det_id, pro_price: pro_price},
        success: function (result) {
            if(result.status == 404) {
                alert("Invalid Warrenty Product");
            } else if(result.status == 406) {
                alert("Invalid Warrenty Product");
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
                alert("Invalid Warrenty Product");
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
                alert("Invalid Warrenty Product");
            } else if(result.status == 406) {
                alert("Invalid Warrenty Product");
            } else {
                $("#all-added-warrenty-product-id ul").html(result);
                getCartProduct();
            }
        }
    });
}

</script>
@endsection
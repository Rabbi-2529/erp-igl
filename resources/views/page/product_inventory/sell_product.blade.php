@extends('layouts.master')

@section('content')

@section('title', 'Sell Product')

@section('inventory_class','active')
@section('pro_inv_class','active')
@section('sell_pro','active')

<style>
    .project-class {
        display: flex;
        align-items: center;
    }
    .project-class > .radio {
        margin-left: 5px;
    }
    .project-class > label {
        margin-left: 5px;
    }
</style>

<br>

<!-- Add Invoice -->
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <span>New Sale</span>
                    <span class="padding-lefttitle">
                        <a href="" class="btn btn-info m-b-5 m-r-2">
                            <i class="ti-align-justify"> Another Button</i>
                        </a>
                        <a href="" class="btn btn-primary m-b-5 m-r-2">
                            <i class="ti-align-justify"> Manage Sale</i>
                        </a>
                    </span>                    
                </div>
            </div>

            <div class="panel-body">
                {{ Form::open(['action' => 'Inventory\InventoryCartController@invTemporaryProduct', 'method' => 'get', 'class' => 'form-horizontal']) }}

                <!-- Customer, Sales Person, and Date -->
                <div class="row">
                    <!-- Customer -->
                    <div class="col-sm-4" id="payment_from_1">
                        <div class="form-group row">
                            <label for="customer_name" class="col-sm-3 col-form-label">Customer :<i class="text-danger">*</i></label>
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
                            <div class="col-sm-3">
                                <a href="#" class="client-add-btn btn btn-success" aria-hidden="true" data-toggle="modal" data-target="#cust_info">
                                    <i class="ti-plus m-r-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                       
                    <!-- Sales Person -->
                    <div class="col-sm-4">
                        <div class="form-group row">
                            <label for="employee" class="col-sm-3 col-form-label">Sales Person :<i class="text-danger">*</i></label>
                            <div class="col-sm-6">
                                <select name="employee" class="form-control basic-single" required>
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

                    <!-- Date -->
                    <div class="col-sm-4">
                        <div class="form-group row">
                            <label for="date" class="col-sm-3 col-form-label">Date :<i class="text-danger">*</i></label>
                            <div class="col-sm-6">
                                <?php
                                    $date = isset($invoiceDate) ? $invoiceDate : date('Y-m-d');
                                    $escapedDate = htmlspecialchars($date, ENT_QUOTES, 'UTF-8');
                                ?>
                                <input class="datepicker form-control" type="text" size="50" name="inv_pro_temp_created_at" id="date" required value="<?php echo $escapedDate; ?>" tabindex="4" />
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <!-- Product Search Section -->
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group row">
                            <label for="group" class="col-sm-3 col-form-label">Group :</label>
                            <div class="col-sm-6">
                                <select name="group" id="product_category" class="form-control basic-single">
                                    <option value="">Select One</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->inv_pro_grp_id }}">
                                            {{ $group->inv_pro_grp_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group row">
                            <label for="type" class="col-sm-3 col-form-label">Type :</label>
                            <div class="col-sm-6 product_category_wrapper">
                                <select name="type" id="product_model" class="form-control basic-single">
                                    <option value="">Select Type First</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group row">
                            <div class="col-sm-6">
                                <button type="button" id="pro_search" class="btn btn-info" name="searchbtn">Search</button>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <!-- Product List Table -->
                <div class="row">
                    <div class="col-sm-12 product_wrapper">
                        <table id="sell_product_list_table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>A. Stock</th>
                                    <th>Price</th>
                                    <th>Sell</th>
                                </tr>
                            </thead>
                            <tbody id="product_table_body">
                                @php($sl=0)
                                @foreach ($sell_pro as $sell)
                                    <tr>
                                        <td class="text-center">{{ ++$sl }}</td>
                                        <td>{{ $sell->product->inv_pro_name ?? 'N/A' }}</td>
                                        <td>
                                            {{ $sell->inv_pro_det_pro_description }}
                                            (
                                            @if($sell->type_info)
                                                {{ $sell->type_info['inv_pro_type_name'] }}
                                            @else
                                                No Data
                                            @endif
                                            )
                                        </td>
                                        <td align="center">{{ $sell->inv_pro_det_available_qty }}</td>
                                        <td class="text-center">
                                            <input type="text" autocomplete="off" class="form-control" id="pro_price_{{ $sell->inv_pro_det_id }}" style="width: 100px;" value="{{ $sell->inv_pro_det_sell_price }}">
                                        </td>
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

                <br>

                <!-- Sales Invoice Section -->
                <div class="table-responsive">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Sales Invoice</h3>
                    </div>
                    <table class="table table-bordered table-hover" id="normalinvoice">
                        <thead>
                            <tr>
                                <th class="text-center invoice_fields">SL</th>
                                <th class="text-center product_field">Item Information</th>
                                <th class="text-center product_field">Qnty.</th>
                                <th class="text-center product_field">Unit Price</th>
                                <th class="text-center invoice_fields">Discount</th>
                                <th class="text-center invoice_fields">Dis Val</th>
                                <th class="text-center invoice_fields">Vat %</th>
                                <th class="text-center invoice_fields">Vat Val</th>
                                <th class="text-center invoice_fields">Total</th>
                                <th class="text-center" style="width: 5px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cart-content">
                            
                        </tbody>
                    </table>

                    <p hidden id="old-amount">0</p>
                    <p hidden id="pay-amount"></p>
                    <p hidden id="change-amount"></p>

                    <!-- Payment Section -->
                    <div class="col-sm-6 table-bordered p-20">
                        <div class="row no-gutters">
                            <div class="form-group col-md-6" style="margin-left: 5px;">
                                <label for="payments" class="col-form-label">Payment Type:</label>
                                <select class="form-control basic-single">
                                    <option></option>
                                </select>
                            </div>
                            <div class="form-group col-md-6" style="margin-left: 5px;">
                                <label for="4digit" class="col-form-label">Paid Amount:</label>
                                <input type="text" id="pamount_by_method" class="form-control number pay" name="pamount_by_method[]" value="" onkeyup="changedueamount()" placeholder="0" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group row text-right">
                    <div class="col-sm-12 p-20">
                        <input type="submit" id="add_invoice" class="btn btn-success" name="add-invoice" value="Continue" tabindex="17" />
                    </div>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>

    <button hidden id="open_sell_print_invoice_btn"></button>
    @include('pages.ajax.warrenty_product_get_sl_no');
</div>

<!-- Modal for adding new customer -->
{{Form::open(['action' => 'Inventory\InvCustomerController@customer_add_submit', 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form', 'name' => 'customer_create_form'])}}

<div class="modal fade modal-success" id="cust_info" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <a href="#" class="close" data-dismiss="modal">&times;</a>
                <h3 class="modal-title">Add New Customer</h3>
            </div>

            <div class="modal-body">
                <div id="customeMessage" class="alert hide"></div>

                <div class="panel-body">
                    <div class="form-group row">
                        <label for="m_customer_name" class="col-sm-4 col-form-label">Customer Name <i class="text-danger">*</i></label>
                        <div class="col-sm-6">
                            <input class="form-control" name="name" id="m_customer_name" type="text" placeholder="Customer Name" tabindex="1" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="com_name" class="col-sm-4 col-form-label">Company Name</label>
                        <div class="col-sm-6">
                            <input class="form-control" name="com_name" id="com_name" type="text" placeholder="Company Name" tabindex="2" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="email" class="col-sm-4 col-form-label">Customer Email</label>
                        <div class="col-sm-6">
                            <input class="form-control" name="email" id="email" type="email" placeholder="Customer Email" tabindex="3" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="mobile" class="col-sm-4 col-form-label">Customer Mobile <i class="text-danger">*</i></label>
                        <div class="col-sm-6">
                            <input class="form-control" name="mobile" id="cus_mobile" type="number" placeholder="Customer Mobile" min="0" tabindex="4" autocomplete="off" value="{{ old('mobile') }}" onkeyup="check_mobile_existence()" required>
                            <p class="text-danger" id="cus_exist_message"></p>
                            <p class="text-success" id="cus_exist_message_available"></p>
                            <p class="text-danger" id="cus_exist_message_not_available"></p>
                            @if($errors->has('mobile'))
                                <p class="text-danger">{{ $errors->first('mobile') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="address" class="col-sm-4 col-form-label">Customer Address</label>
                        <div class="col-sm-6">
                            <textarea class="form-control" name="address" id="address" rows="3" placeholder="Customer Address" tabindex="5"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="customer_type" class="col-sm-4 col-form-label">Customer Type <i class="text-danger">*</i></label>
                        <div class="col-sm-8" tabindex="6">
                            <label class="radio-inline">
                                <input type="radio" {{ (old('bal_type')==1)?'checked':'' }} name="type" value="1">Regular
                            </label>
                            <label class="radio-inline">
                                <input type="radio" {{ (old('bal_type')==2)?'checked':'' }} name="type" value="2">Irregular
                            </label>
                            <label class="radio-inline">
                                <input type="radio" {{ (old('bal_type')==3)?'checked':'' }} name="type" value="3">Corporate
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <a href="#" class="btn btn-danger" tabindex="5" data-dismiss="modal">Close</a>
                <input type="submit" tabindex="6" class="btn btn-success" value="Submit">
            </div>
        </div>
    </div>
</div>
{{ Form::close() }}
<!-- Modal for adding new customer END -->

<script>
    $(document).ready( function () {
        var table = $('#sell_product_list_table').DataTable({
            pageLength : 5,
            lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']]
        });

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

        // Initialize datepicker
        $('#date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
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
    });

    function addtocart(pro_det_id) {
        let pro_qty = parseFloat($("#pro_qty_"+pro_det_id).val());
        let pro_price = parseFloat($("#pro_price_"+pro_det_id).val());
        if(isNaN(pro_qty) || isNaN(pro_price)) {
            alert("Oops... Quantity field can\'t be empty!");
        } else if(pro_qty < 1) {
            alert("Minimum quantity at least 1.");
        } else {
            let route_url = "{{ route('buy.add-to-cart') }}";
            $.ajax({
                type: "GET",
                url: route_url,
                data: { pro_id: pro_det_id, pro_qty: pro_qty, pro_price: pro_price},
                success: function (result) {
                    if(result.status == 400) {
                        alert("Please try adding a quantity less than the available stock.");
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
                $("#cart-content").html(result);
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
            let route_url = "{{ route('buy.remove-cart') }}";
            $.ajax({
                type: "GET",
                url: route_url,
                data: {content_id:content_id},
                success: function (result) {
                    getCartProduct();
                }
            });
        } else {
            return false;
        }
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

    var error_mobile =  true;
    function check_mobile_existence() {
        var mobile_num_length = $("#cus_mobile").val().length;
        if( mobile_num_length<11 || mobile_num_length>11 ){
            $("#cus_exist_message_not_available").html("Invalid Mobile Number");
            $("#cus_exist_message_available").html("");
            error_mobile = true;
        } else {
            var link = "{{ route('customer.customer_exist') }}";
            var mobileno = $("#cus_mobile").val();
            $.ajax({
                type: "GET",
                url: link,
                data: { mobile: mobileno},
                success: function (result) {
                    if(result == 200) {
                        $("#cus_exist_message_not_available").html("");
                        $("#cus_exist_message_available").html("Mobile Number Available");
                        error_mobile = false;
                    } else if(result == 405) {
                        $("#cus_exist_message_not_available").html("Already Exist!");
                        $("#cus_exist_message_available").html("");
                        error_mobile = true;
                    } else if(typeof result != "undefined") {
                        $("#cus_exist_message_not_available").html("");
                        $("#cus_exist_message_available").html("Mobile Number Available");
                        $("#com_name").val(result.qb_company_name);
                        $("#m_customer_name").val(result.qb_name);
                        $("#customer_email").val(result.qb_email);
                        $("#customer_address").val(result.qb_address);
                        error_mobile = false;
                    }
                }
            });
        }
    }

    $("#customer_create_form").submit(function () {
        if(error_mobile == true) {
            $("#cus_mobile").focus();
            return false;
        } else {
            return true;
        }
    });
</script>

@endsection
@extends('layouts.master')
@section('inventory_class','menu-open')
@section('pro_inv_class','menu-open')
@section('inv_buy_class','menu-open')
@section('buy_add','active')

@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('msg'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('msg') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
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

        <h1>Purchase Product</h1>
    </section>

    {{ Form::open(['action' => 'Inventory\InventoryPurchaseCartController@invTemporaryBuy', 'method' => 'get', 'class' => 'form-inline']) }}

    <div class="box" id="total">
        <div class="box-header with-border">
            <h3 class="box-title">Add Product</h3>
        </div>

        <div class="box-body">
            <div class="col-sm-12">
                {{-- SUPPLIER --}}
                <div class="form-group">
                    <label class="col-sm-2 control-label" style="width: 101px;">Supplier <span class="text-danger">*</span>:</label>
                    <div class="col-sm-6">
                        <select name="supplier" style="width: 170px;" class="form-control select2" required>
                            <option value="">Select One</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->inv_sup_id }}">{{ $sup->inv_sup_com_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-center">
                        @if($errors->has('supplier'))
                            <p class="text-danger">{{ $errors->first('supplier') }}</p>
                        @endif
                    </div>
                </div>

                {{-- ISSUE DATE --}}
                <div class="form-group">
                    <label class="col-sm-2 control-label" style="width: 115px;">Issue Date <span class="text-danger">*</span>:</label>
                    <div class="col-sm-6">
                        <input type="text" autocomplete="off" data-date-format="yyyy-mm-dd"
                               class="form-control" placeholder="Enter Issue Date"
                               id="issue" name="issue_date" required>
                    </div>
                </div>

                {{-- MEMO --}}
                <div class="form-group">
                    <label class="col-sm-2 control-label" style="width: 142px;">Memo Number <span class="text-danger">*</span>:</label>
                    <div class="col-sm-6">
                        <input type="text" name="memo" required autocomplete="off"
                               value="{{ old('memo') }}" class="form-control"
                               placeholder="Enter Memo Number">
                    </div>
                    <div class="text-center">
                        @if($errors->has('memo'))
                            <p class="text-danger">{{ $errors->first('memo') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- PRODUCT TABLE --}}
        <div class="box-body">
            <div class="col-sm-12">
                <table id="purchase_product_list_table"
                       class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>A. S.</th>
                            <th>Exp Date</th>
                            <th>Buy Price</th>
                            <th>Short</th>
                            <th>Remarks</th>
                            <th>Purchase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($sl = 0)
                        @foreach ($sell_pro as $sell)
                            <tr>
                                <td class="text-center">{{ ++$sl }}</td>
                                <td>{{ $sell->inv_pro_det_pro_id }}</td>
                                <td>{{ $sell->type_info['inv_pro_type_name'] }}</td>
                                <td align="center">{{ $sell->inv_pro_det_available_qty }}</td>

                                <td class="text-center">
                                    <input type="text" autocomplete="off" data-date-format="yyyy-mm-dd"
                                           class="form-control from"
                                           id="exp_date_{{ $sell->inv_pro_det_id }}"
                                           style="width: 100px;" placeholder="Expire Date">
                                </td>

                                <td class="text-center">
                                    <input type="text" class="form-control"
                                           id="pro_price_{{ $sell->inv_pro_det_id }}"
                                           style="width: 100px;"
                                           value="{{ $sell->inv_pro_det_buy_price }}">
                                </td>

                                <td>
                                    @if($sell->inv_pro_det_pro_warranty == 0)
                                        <input type="text" autocomplete="off" class="form-control"
                                               name="short_qty"
                                               id="short_qty_{{ $sell->inv_pro_det_id }}"
                                               style="width: 70px;" placeholder="Qty">
                                    @else
                                        <input type="text" autocomplete="off" class="form-control"
                                               name="short_qty" style="width: 70px;"
                                               placeholder="N/A" readonly disabled>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <input type="text" name="remarks" class="form-control"
                                           id="short_remarks_{{ $sell->inv_pro_det_id }}"
                                           placeholder="Reason of short quatity..">
                                </td>

                                <td class="text-center">
                                    @if($sell->inv_pro_det_pro_warranty == 0)
                                        <input type="text" autocomplete="off" class="form-control"
                                               style="width: 50px;"
                                               id="pro_qty_{{ $sell->inv_pro_det_id }}"
                                               placeholder="Qty">
                                        <button type="button" class="btn btn-success btn-sm"
                                                onclick="addtocart('{{ $sell->inv_pro_det_id }}')">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    @else
                                        <input type="text" class="form-control warranty"
                                               style="width: 50px;" placeholder="N/A" readonly disabled>
                                        <button type="button" class="btn btn-success btn-sm"
                                                onclick="addWarrentyProduct('{{ $sell->inv_pro_det_id }}')">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- CART --}}
        <div class="box-body">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Buy Invoice</h3>
            </div>
            <div class="col-sm-12">
                <div class="box shopping_cart">
                    <table class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Buy Qty</th>
                                <th>Expire date</th>
                                <th>Unit Price</th>
                                <th>Amount</th>
                                <th>Short</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="show-cart-conten"></tbody>
                    </table>
                </div>
                <div class="form-group" style="float: right;">
                    <label class="col-sm-2 control-label">Total: </label>
                    <div class="col-sm-6">
                        <input type="text" class="text-right" disabled
                               value="" class="form-control" id="total_buy">
                    </div>
                    <button class="btn btn-success btn-sm pull-right">Continue</button>
                </div>
            </div>
        </div>
    </div>

    {{ Form::close() }}
</section>

@include('pages.ajax.warrenty_product_get_sl_no')

@endsection

@section('custom_style')
<style>
    .form-control::-webkit-inner-spin-button,
    .form-control::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }
    .warranty { background-color: black; }
    .all-product-sl-no-main-wrapper {
        border: 2px solid #ddd;
        padding: 7px;
        height: 350px;
        position: absolute;
    }
    .product-warrenty-sl { margin-top: 5px; }
    .table > tbody > tr > td { padding: 2px; }
    .shopping_cart {
        min-height: 300px;
        border: 1px solid grey;
        padding: 10px;
    }
    input { height: 27px !important; }
    .btn-sm { padding: 3px 8px; }
</style>
@endsection

@section('custom_script')
<script>
    function formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") {
        try {
            decimalCount = Math.abs(decimalCount);
            decimalCount = isNaN(decimalCount) ? 2 : decimalCount;
            const negativeSign = amount < 0 ? "-" : "";
            let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
            let j = (i.length > 3) ? i.length % 3 : 0;
            return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
        } catch (e) {
            console.log(e);
        }
    }

    function total_buy_amount() {
        let amount = 0;
        $('.temp_cart').each(function (i, obj) {
            amount = amount + parseFloat(obj.innerText.split(',').join(''));
        });
        if (isNaN(amount)) amount = 0;
        $('#total_buy').val(formatMoney(amount));
    }

    $(document).ready(function () {
        @if(session()->has('print_buy_invoice'))
            let route = "{!! route('reports.buy-print', session()->get('print_buy_invoice')) !!}";
            window.open(route, '_blank');
        @endif

        var date = new Date();
        date.setDate(date.getDate());
        $("#issue").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
        });
        $(".from").datepicker({
            daysOfWeekHighlighted: "7",
            startDate: date,
            todayHighlight: true,
            autoclose: true,
        });
    });

    $(document).ready(function () {
        getCartProduct();
        $("#expire").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });
        $("#to").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
        });

        var table = $('#purchase_product_list_table').DataTable({
            pageLength: 5,
            lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']]
        });
    });

    // ---------------- NON-WARRANTY PRODUCT ----------------
    function addtocart(pro_det_id) {
        let pro_qty = parseFloat($("#pro_qty_" + pro_det_id).val());
        let short_qty = parseFloat($("#short_qty_" + pro_det_id).val());
        let pro_price = parseFloat($("#pro_price_" + pro_det_id).val());
        let remarks = $("#short_remarks_" + pro_det_id).val();
        let exp_date = $("#exp_date_" + pro_det_id).val();

        if (isNaN(pro_qty) || isNaN(pro_price)) {
            alert("quantity field can't be empty");
        } else if (pro_qty < 1) {
            alert("minimum quantity at least 1");
        } else {
            let route_url = "{{ route('buy.buy-add-to-cart') }}";
            $.ajax({
                type: "GET",
                url: route_url,
                data: {
                    pro_id: pro_det_id,
                    pro_qty: pro_qty,
                    pro_price: pro_price,
                    exp_date: exp_date,
                    short_qty: short_qty,
                    remarks: remarks
                },
                success: function (result) {
                    console.log('Add-to-cart response:', result);
                    if (result.status == 400) {
                        alert("Stock has been cross it's limit");
                    } else {
                        getCartProduct();
                    }
                }
            });
        }
    }

    // ---------------- WARRANTY PRODUCT ----------------
    function addWarrentyProduct(pro_det_id) {
        let pro_price = parseFloat($("#pro_price_" + pro_det_id).val());
        let exp_date = $("#exp_date_" + pro_det_id).val();

        let route_url = "{{ route('buy.buy-add-to-cart-warrenty-product') }}";

        $.ajax({
            type: "GET",
            url: route_url,
            data: { pro_id: pro_det_id, pro_price: pro_price, exp_date: exp_date },
            success: function (result) {
                console.log('Warranty response:', result);

                if (result.status == 404 || result.status == 406) {
                    let reason = result.reason || 'Unknown reason';
                    alert("Invalid Warranty Product\n\nReason: " + reason);
                } else {
                    $("#warrenty_product_get_sl_no .modal-content").html(result);
                    $("#warrenty_product_get_sl_no").modal('show');
                }
            },
            error: function (xhr) {
                alert("Server error: " + xhr.status + " — " + xhr.statusText);
            }
        });
    }

    // ---------------- SERIAL NUMBER CHECK ----------------
    function check_sl_no(value, pro_det_id) {
        let sl_no = value;
        if (sl_no == '') {
            alert("sl no is required");
            return false;
        }
        let route_url = "{{ route('buy.buy-add-warrenty-product-sl-no') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: { pro_id: pro_det_id, sl_no: sl_no },
            success: function (result) {
                console.log('Check SL response:', result);
                if (result.status == 404 || result.status == 402) {
                    let reason = result.reason || (result.status == 402 ? 'Already Added' : 'Unknown reason');
                    alert("Warranty Product Error\n\nReason: " + reason);
                } else {
                    $("#all-added-warrenty-product-id .show-added-list").html(result);
                    $("#pur_warrenty_pro_sl_no").val("");
                    getCartProduct();
                }
            }
        });
    }

    // ---------------- REMOVE SERIAL NUMBER ----------------
    function remove_product_sl(product_id, sl_no) {
        let route_url = "{{ route('buy.buy-remove-warrenty-product-sl') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: { pro_id: product_id, sl_no: sl_no },
            success: function (result) {
                console.log('Remove SL response:', result);
                if (result.status == 404 || result.status == 406) {
                    let reason = result.reason || 'Unknown reason';
                    alert("Warranty Product Error\n\nReason: " + reason);
                } else {
                    $("#all-added-warrenty-product-id .show-added-list").html(result);
                    getCartProduct();
                }
            }
        });
    }

    // ---------------- CART ----------------
    function getCartProduct() {
        let route_url = "{{ route('buy.buy-get-cart') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: {},
            success: function (result) {
                $("#show-cart-conten").html(result);
                total_buy_amount();
            }
        });
    }

    function update_cart_quantity(content_id) {
        let pro_quantity = parseFloat($("#update_qty_" + content_id).val());
        if (pro_quantity < 1) {
            alert("Minimum Quantity is 1");
            return false;
        }
        let route_url = "{{ route('buy.buy-update-cart') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: { content_id: content_id, pro_qty: pro_quantity },
            success: function (result) {
                getCartProduct();
            }
        });
    }

    function remove_cart(content_id) {
        let clickDel = confirm("Are you sure want to delete this?");
        if (clickDel == true) {
            let route_url = "{{ route('buy.buy-remove-cart') }}";
            $.ajax({
                type: "GET",
                url: route_url,
                data: { content_id: content_id },
                success: function (result) {
                    getCartProduct();
                }
            });
        } else {
            return false;
        }
    }
</script>
@endsection
@extends('layouts.master')
@section('title', 'Create Quotation')
@section('customer_relation_class', 'active')
@section('quotation_class', 'active')
@section('quotations_create', 'active')

<style>
    .form-control::-webkit-inner-spin-button,
    .form-control::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }
    .shopping_cart {
        min-height: 300px;
        border: 1px solid grey;
        padding: 10px;
    }
    input {
        height: 27px !important;
    }
    .btn-sm {
        padding: 3px 8px;
    }
    .action-column {
        width: 80px;
    }
</style>

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Create Quotation</h4>
                </div>
            </div>

            <div class="panel-body">
                <form id="quotationForm" action="{{ route('quotations.store') }}" method="POST">
                    @csrf
                    <div class="box" id="total">
                        <!-- Customer and Date Fields -->
                        <div class="col-sm-12">
                            <div class="form-group col-sm-4">
                                <label for="customer" class="col-sm-6 control-label">Customer <span class="text-danger">*</span>:</label>
                                <div class="col-sm-6">
                                    <select name="customer_id" id="customer_id" required class="form-control select2">
                                        <option value="">Select One</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->qb_id }}">{{ $customer->qb_company_name }} ({{ $customer->qb_name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="qdate" class="col-sm-6 control-label">Quotation Date <span class="text-danger">*</span>:</label>
                                <div class="col-sm-6">
                                    <input type="text" required name="qdate" class="form-control datepicker" id="qdate" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="expiry_date" class="col-sm-6 control-label">Expiry Date <span class="text-danger">*</span>:</label>
                                <div class="col-sm-6">
                                    <input type="text" required name="expiry_date" class="form-control datepicker" id="expiry_date" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Product Table -->
                        <div class="col-sm-12">
                            <br>
                            <table id="quotation_product_list_table" class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Product Name</th>
                                        <th>Available Stock</th>
                                        <th>Rate</th>
                                        <th>Quantity</th>
                                        <th class="action-column">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php($sl = 1)
                                    @foreach($products as $product)
                                        <tr id="product_row_{{ $product->inv_pro_det_id }}">
                                            <td>{{ $sl++ }}</td>
                                            <td>{{ $product->product->inv_pro_name ?? 'N/A' }}</td>
                                            <td>{{ $product->inv_pro_det_available_qty }}</td>
                                            <td>
                                                <input type="number" class="form-control text-right" id="rate_{{ $product->inv_pro_det_id }}" value="{{ $product->inv_pro_det_sell_price }}" />
                                            </td>
                                            <td>
                                                <input type="number" class="form-control text-right" id="quantity_{{ $product->inv_pro_det_id }}" placeholder="0.00" value="1" />
                                            </td>
                                            <td class="action-column">
                                                <button type="button" class="btn btn-success btn-sm" onclick="addToQuotation({{ $product->inv_pro_det_id }})">
                                                    <i class="fa fa-plus"></i> Add
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Quotation Cart -->
                        <div class="col-sm-12">
                            <div class="box shopping_cart">
                                <h3>Quotation Cart</h3>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Rate</th>
                                            <th>Total</th>
                                            <th class="action-column">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="quotation_cart"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                            <td><input type="text" class="form-control text-right" id="subtotal" value="0.00" readonly /></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Discount:</strong></td>
                                            <td><input type="number" class="form-control text-right" id="discount" name="discount" placeholder="0.00" oninput="calculateGrandTotal();" /></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                                            <td><input type="text" class="form-control text-right" id="grandTotal" value="0.00" readonly /></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="form-group" style="float: right;">
                                <button type="submit" class="btn btn-success btn-sm pull-right">Save Quotation</button>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Field for Product Details -->
                    <input type="hidden" name="product_details" id="product_details" value="[]" />
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let quotationCart = [];

    function addToQuotation(productId) {
        const productName = document.querySelector(`#product_row_${productId} td:nth-child(2)`).innerText;
        const quantity = parseFloat(document.getElementById(`quantity_${productId}`).value) || 0;
        const rate = parseFloat(document.getElementById(`rate_${productId}`).value) || 0;
        const buyPrice = parseFloat(document.getElementById(`rate_${productId}`).getAttribute("min")) || 0;

        if (quantity <= 0) {
            alert("Quantity must be greater than 0.");
            return;
        }

        if (rate < buyPrice) {
            alert(`Rate cannot be less than the buying price (${buyPrice.toFixed(2)}).`);
            return;
        }

        const total = quantity * rate;
        quotationCart.push({ id: productId, name: productName, quantity: quantity, rate: rate, total: total });
        updateQuotationCart();
    }

    function updateQuotationCart() {
        let subtotal = 0;
        document.getElementById("quotation_cart").innerHTML = quotationCart.map((item, index) => {
            subtotal += item.total;
            return ` 
            <tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${item.rate.toFixed(2)}</td>
                <td>${item.total.toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeFromQuotation(${index})"><i class="fa fa-trash"></i></button></td>
            </tr>`;
        }).join("");
        document.getElementById("subtotal").value = subtotal.toFixed(2);
        calculateGrandTotal();
    }

    function removeFromQuotation(index) {
        quotationCart.splice(index, 1);
        updateQuotationCart();
    }

    function calculateGrandTotal() {
        const subtotal = parseFloat(document.getElementById("subtotal").value) || 0;
        const discount = parseFloat(document.getElementById("discount").value) || 0;
        const grandTotal = subtotal - discount;
        document.getElementById("grandTotal").value = grandTotal.toFixed(2);
    }

    // Before form submission, serialize the cart items to a hidden field
    document.getElementById("quotationForm").addEventListener("submit", function(event) {
        // Serialize the quotationCart to JSON and set it to the hidden field
        document.getElementById("product_details").value = JSON.stringify(quotationCart);
    });
</script>
@endsection

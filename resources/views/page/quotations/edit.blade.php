@extends('layouts.master')
@section('title', 'Edit Quotation')

@section('quotations_class', 'active')
@section('quotations_edit', 'active')

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
    .btn-success[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Edit Quotation</h4>
                </div>
            </div>

            <div class="panel-body">
                <form id="quotationForm" action="{{ route('quotations.update', $quotation->qt_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box" id="total">
                        <!-- Customer and Date Fields -->
                        <div class="col-sm-12">
                            <div class="form-group col-sm-4">
                                <label for="customer" class="col-sm-6 control-label">Customer <span class="text-danger">*</span>:</label>
                                <div class="col-sm-6">
                                    <select name="customer_id" id="customer_id" required class="form-control select2">
                                        <option value="">Select One</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->qb_id }}" {{ $quotation->qt_customer_id == $customer->qb_id ? 'selected' : '' }}>
                                                {{ $customer->qb_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="qdate" class="col-sm-6 control-label">Quotation Date <span class="text-danger">*</span>:</label>
                                <div class="col-sm-6">
                                    <input type="text" required name="qdate" class="form-control datepicker" id="qdate" value="{{ $quotation->qt_date }}">
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="expiry_date" class="col-sm-6 control-label">Expiry Date <span class="text-danger">*</span>:</label>
                                <div class="col-sm-6">
                                    <input type="text" required name="expiry_date" class="form-control datepicker" id="expiry_date" value="{{ $quotation->qt_expiry_date }}">
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
                                            <td>{{ $product->inv_pro_det_pro_id }}</td>
                                            <td>{{ $product->inv_pro_det_available_qty }}</td>
                                            <td>
                                                <input type="text" class="form-control text-right" id="rate_{{ $product->inv_pro_det_id }}" value="{{ $product->inv_pro_det_sell_price }}" readonly />
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
                                    <tbody id="quotation_cart">
                                        @foreach(json_decode($quotation->qt_product_details, true) as $item)
                                            <tr id="cart_item_{{ $item['product_id'] }}">
                                                <td>{{ $products->firstWhere('inv_pro_det_id', $item['product_id'])->inv_pro_det_pro_id }}</td>
                                                <td>{{ $item['quantity'] }}</td>
                                                <td>{{ $products->firstWhere('inv_pro_det_id', $item['product_id'])->inv_pro_det_sell_price }}</td>
                                                <td>{{ $item['quantity'] * $products->firstWhere('inv_pro_det_id', $item['product_id'])->inv_pro_det_sell_price }}</td>
                                                <td class="action-column">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFromQuotation({{ $item['product_id'] }})">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                            <td>
                                                <input type="text" class="form-control text-right" id="subtotal" value="{{ number_format($subtotal, 2) }}" readonly />
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Discount:</strong></td>
                                            <td>
                                                <input type="number" class="form-control text-right" id="discount" name="discount" placeholder="0.00" value="{{ $quotation->qt_discount }}" oninput="calculateGrandTotal();" />
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                                            <td>
                                                <input type="text" class="form-control text-right" id="grandTotal" value="{{ number_format($grandTotal, 2) }}" readonly />
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="form-group" style="float: right;">
                                <button type="submit" class="btn btn-success btn-sm pull-right">Update Quotation</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let quotationCart = @json($quotation->qt_product_details ? json_decode($quotation->qt_product_details, true) : []);

    function addToQuotation(productId) {
        const existingProduct = quotationCart.find(item => item.product_id === productId);
        if (existingProduct) {
            alert("This product is already in the quotation cart.");
            return;
        }

        const productName = document.querySelector(`#product_row_${productId} td:nth-child(2)`).innerText;
        const quantity = parseFloat(document.getElementById(`quantity_${productId}`).value) || 0;
        const rate = parseFloat(document.getElementById(`rate_${productId}`).value) || 0;

        if (quantity <= 0) {
            alert("Quantity must be greater than 0.");
            return;
        }

        const total = quantity * rate;

        const product = {
            product_id: productId,
            name: productName,
            quantity: quantity,
            rate: rate,
            total: total,
        };
        quotationCart.push(product);
        updateQuotationCart();
        document.getElementById(`quantity_${productId}`).value = ''; // Reset quantity input
    }

    function updateQuotationCart() {
        const cartBody = document.getElementById("quotation_cart");
        cartBody.innerHTML = "";

        let subtotal = 0;

        quotationCart.forEach((item, index) => {
            cartBody.innerHTML += `
                <tr id="cart_item_${item.product_id}">
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>${item.rate.toFixed(2)}</td>
                    <td>${item.total.toFixed(2)}</td>
                    <td class="action-column">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeFromQuotation(${index})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            subtotal += item.total;

            // Disable the "Add" button for products in the cart
            const addButton = document.querySelector(`#product_row_${item.product_id} .btn-success`);
            if (addButton) {
                addButton.disabled = true;
            }
        });

        document.getElementById("subtotal").value = subtotal.toFixed(2);
        calculateGrandTotal();
    }

    function removeFromQuotation(index) {
        const removedProduct = quotationCart.splice(index, 1)[0];
        updateQuotationCart();

        // Re-enable the "Add" button for the removed product
        const addButton = document.querySelector(`#product_row_${removedProduct.product_id} .btn-success`);
        if (addButton) {
            addButton.disabled = false;
        }
    }

    function calculateGrandTotal() {
        const subtotal = parseFloat(document.getElementById("subtotal").value) || 0;
        const discount = parseFloat(document.getElementById("discount").value) || 0;
        document.getElementById("grandTotal").value = (subtotal - discount).toFixed(2);
    }

    // Append product details before submitting
    document.getElementById('quotationForm').addEventListener('submit', function(event) {
        const productDetails = quotationCart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            rate: item.rate,
        }));

        const productDetailsInput = document.createElement('input');
        productDetailsInput.type = 'hidden';
        productDetailsInput.name = 'product_details';
        productDetailsInput.value = JSON.stringify(productDetails);
        this.appendChild(productDetailsInput);

        const discountInput = document.createElement('input');
        discountInput.type = 'hidden';
        discountInput.name = 'discount';
        discountInput.value = document.getElementById('discount').value || 0;
        this.appendChild(discountInput);
    });
</script>
@endsection
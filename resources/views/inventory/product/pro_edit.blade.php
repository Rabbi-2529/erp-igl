@extends('layouts.master')
@section('title', 'Update Product Details')
@section('inventory_class','active')
@section('product_class','active')
@section('inv_pro_class','active')
@section('pro_list','active')

@section('content')
<section class="content">
    <section class="content-header">
        <h1>Product Details</h1>
    </section>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Update Product Details</h3>
        </div>

        {{-- ✅ FIXED: action uses detail ID, not product ID --}}
        {{ Form::open([
            'action' => ['Inventory\InvProductController@pro_edit', $product_details->inv_pro_det_id],
            'method' => 'post',
            'class'  => 'form-horizontal',
            'id'     => 'customer_create_form'
        ]) }}

        <div class="box-body">

            {{-- GROUP --}}
            <div class="form-group">
                <label class="col-sm-2 control-label">Group</label>
                <div class="col-sm-6">
                    <select name="group" class="form-control select2" id="product_category" required>
                        <option value="">Select</option>
                        @foreach ($pro_det_grp as $grp)
                            <option {{ ($grp->inv_pro_grp_id == $product->type_info['inv_pro_type_grp_id']) ? 'selected' : '' }}
                                    value="{{ $grp->inv_pro_grp_id }}">
                                {{ $grp->inv_pro_grp_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- TYPE --}}
            <div class="form-group">
                <label class="col-sm-2 control-label">Type Name <b class="text-danger">*</b></label>
                <div class="col-sm-6 product_category_wrapper">
                    <select name="type" class="form-control select2" id="product_model" required>
                        <option value="">Select</option>
                        @foreach ($types as $type)
                            <option {{ ($type->inv_pro_type_id == $product->inv_pro_type_id) ? 'selected' : '' }}
                                    value="{{ $type->inv_pro_type_id }}">
                                {{ $type->inv_pro_type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('type'))
                    <p class="text-danger">{{ $errors->first('type') }}</p>
                @endif
            </div>

            {{-- SUPPLIER --}}
            @if (App\Models\Inv_product_detail::suppliers_info($product_details->inv_pro_det_id) == null)
                <div class="form-group supplier_list">
                    <label class="col-sm-2 control-label">Supplier</label>
                    <div class="col-sm-6" id="select_div">
                        <select name="supplier[]" class="form-control select2" id="select">
                            <option value="">Select</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->inv_sup_id }}">{{ $supplier->inv_sup_com_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-success btn-sm add_new_supplier_btn"><big>+</big></button>
                </div>
            @else
                @foreach (App\Models\Inv_product_detail::suppliers_info($product_details->inv_pro_det_id) as $si)
                    <div class="form-group supplier_list">
                        <label class="col-sm-2 control-label">Supplier</label>
                        <div class="col-sm-6" id="select_div">
                            <select name="supplier[]" class="form-control select2" id="select" required>
                                <option value="">Select</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->inv_sup_id }}"
                                        {{ ($supplier->inv_sup_id == $si->inv_sup_id) ? 'selected' : '' }}>
                                        {{ $supplier->inv_sup_com_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if ($loop->first)
                            <button type="button" class="btn btn-success btn-sm add_new_supplier_btn"><big>+</big></button>
                        @else
                            <button type="button" onclick="remove_supplier(this)" class="btn btn-danger btn-sm remove_a_supplier_btn"><big>X</big></button>
                        @endif
                    </div>
                @endforeach
            @endif

            <div id="others-supplier"></div>

            {{-- PRODUCT NAME --}}
            <div class="form-group">
                <label class="col-sm-2 control-label">Product Name <b class="text-danger">*</b></label>
                <div class="col-sm-6">
                    <input type="text" name="pro_name" autocomplete="off"
                           value="{{ $product->inv_pro_name }}" class="form-control" required>
                </div>
                @if($errors->has('pro_name'))
                    <p class="text-danger">{{ $errors->first('pro_name') }}</p>
                @endif
            </div>

            {{-- BUY PRICE --}}
            <div class="form-group">
                <label class="col-sm-2 control-label">Product Buy Price <b class="text-danger">*</b></label>
                <div class="col-sm-6">
                    <input type="text" name="pro_buy" autocomplete="off"
                           value="{{ $product->inv_pro_buy_price }}" class="form-control" required>
                </div>
                @if($errors->has('pro_buy'))
                    <p class="text-danger">{{ $errors->first('pro_buy') }}</p>
                @endif
            </div>

            {{-- SELL PRICE --}}
            <div class="form-group">
                <label class="col-sm-2 control-label">Product Sell Price <b class="text-danger">*</b></label>
                <div class="col-sm-6">
                    <input type="text" name="pro_sell" autocomplete="off"
                           value="{{ $product->inv_pro_sell_price }}" class="form-control" required>
                </div>
                @if($errors->has('pro_sell'))
                    <p class="text-danger">{{ $errors->first('pro_sell') }}</p>
                @endif
            </div>

            {{-- WARRANTY --}}
            <div class="form-group">
                <label class="col-sm-2 control-label">Product Warranty <b class="text-danger">*</b></label>
                <div class="col-sm-6">
                    <div class="col-sm-3">
                        <input type="radio" id="warranty_yes" name="has_warranty" class="warranty_change"
                               value="1" {{ ($product->inv_pro_warranty != 0) ? 'checked' : '' }}>
                        <label for="warranty_yes">Yes</label>

                        <input type="radio" id="warranty_no" name="has_warranty" class="warranty_change"
                               value="0" {{ ($product->inv_pro_warranty == 0) ? 'checked' : '' }}>
                        <label for="warranty_no">No</label>
                    </div>

                    {{-- ✅ FIXED: hide on load if no warranty --}}
                    <div class="warranty_input_wrapper" style="{{ $product->inv_pro_warranty == 0 ? 'display:none;' : '' }}">
                        <div class="col-sm-6">
                            <input type="number" name="warranty_days" autocomplete="off"
                                   value="{{ $product->inv_pro_warranty ?? 0 }}"
                                   class="form-control" placeholder="Enter Product Warranty Detail">
                        </div>
                        <div class="col-sm-2">(Days)</div>
                    </div>

                    @if($errors->has('warranty_days'))
                        <p class="text-danger">{{ $errors->first('warranty_days') }}</p>
                    @endif
                </div>
            </div>

            {{-- DESCRIPTION --}}
            <div class="form-group">
                <label class="col-sm-2 control-label">Product Description</label>
                <div class="col-sm-6">
                    <input type="text" name="pro_desc" autocomplete="off"
                           value="{{ $product->inv_pro_description }}" class="form-control">
                </div>
            </div>

            {{-- SHORT QUANTITY --}}
            <div class="form-group">
                <label class="col-sm-2 control-label">Short Quantity</label>
                <div class="col-sm-6">
                    {{-- ✅ FIXED: read from detail, not product --}}
                    <input type="text" name="pro_short" autocomplete="off"
                           value="{{ $product_details->inv_pro_det_short_qty ?? '' }}"
                           class="form-control" placeholder="Enter Product Short Quantity">
                </div>
            </div>

        </div>

        <div class="box-footer">
            <div class="col-sm-3">
                <button type="submit" class="btn btn-info pull-right">UPDATE</button>
            </div>
        </div>

        {{ Form::close() }}
    </div>

    {{-- HIDDEN SUPPLIER TEMPLATE --}}
    <div class="hidden" id="supplier-list-container">
        <div class="form-group supplier_list">
            <label class="col-sm-2 control-label">Supplier</label>
            <div class="col-sm-6" id="select_div">
                <select name="supplier[]" class="form-control" required>
                    <option value="">Select</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->inv_sup_id }}">{{ $supplier->inv_sup_com_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" onclick="remove_supplier(this)" class="btn btn-danger btn-sm remove_a_supplier_btn"><big>X</big></button>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
<style>
    .form-control::-webkit-inner-spin-button,
    .form-control::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }
</style>
@endsection

@section('custom_script')
<script>
    $(document).ready(function () {
        $('#product_category').on("change", function () {
            let grp_id = $("#product_category").val();
            let link = "{{ route('inventory.product_submit_ajax') }}";
            $.ajax({
                type: "GET",
                url: link,
                data: { grp_id: grp_id },
                success: function (result) {
                    $(".product_category_wrapper").html(result);
                }
            });
        });
    });

    $(".add_new_supplier_btn").click(function () {
        let new_supplier = $("#supplier-list-container").html();
        $("#others-supplier").append(new_supplier);
    });

    function remove_supplier(btn) {
        $(btn).parent().remove();
    }

    $(".warranty_change").change(function () {
        if (this.value == 1) {
            $(".warranty_input_wrapper").show();
        } else {
            $(".warranty_input_wrapper").hide();
        }
    });
</script>
@endsection
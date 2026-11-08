@extends('layouts.master')
@section('title', 'Edit Purchase')

@section('inventory_class','active')
@section('pro_inv_class','active')
@section('inv_buy_class','active')
@section('purchase_list','active')

@section('content')
<section class="content">
    @if(Session::has('errmsg'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('errmsg') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Edit Purchase - Memo No: {{ $purchase->inv_pro_inv_invoice_no }}</h4>
                    </div>
                </div>

                <div class="panel-body">
                    <form action="{{ route('buy.purchase.update', $purchase->inv_pro_inv_id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-sm-4">
                                <label>Supplier <span class="text-danger">*</span></label>
                                <select name="supplier" class="form-control select2" style="width: 100%;" required>
                                    <option value="">Select One</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->inv_sup_id }}" {{ old('supplier', $purchase->inv_pro_inv_party_id) == $sup->inv_sup_id ? 'selected' : '' }}>
                                            {{ $sup->inv_sup_com_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-4">
                                <label>Issue Date <span class="text-danger">*</span></label>
                                <input type="text" name="issue" id="issue" autocomplete="off" data-date-format="yyyy-mm-dd" class="form-control" required
                                       value="{{ old('issue', $purchase->inv_pro_inv_issue_date ? \Carbon\Carbon::parse($purchase->inv_pro_inv_issue_date)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="form-group col-sm-4">
                                <label>Memo Number</label>
                                <input type="text" class="form-control" value="{{ $purchase->inv_pro_inv_invoice_no }}" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label>Product <span class="text-danger">*</span></label>
                                <select name="product" id="product" class="form-control select2" style="width: 100%;" required>
                                    <option value="">Select One</option>
                                    @foreach ($products as $pro)
                                        <option value="{{ $pro->inv_pro_det_id }}"
                                                data-warranty="{{ $pro->inv_pro_det_pro_warranty }}"
                                                {{ old('product', $purchase->inv_pro_inv_project_id) == $pro->inv_pro_det_id ? 'selected' : '' }}>
                                            {{ $pro->product->inv_pro_name ?? $pro->inv_pro_det_pro_description ?? $pro->inv_pro_det_id }}
                                            ({{ $pro->type_info->inv_pro_type_name ?? 'N/A' }}) - Stock: {{ $pro->inv_pro_det_available_qty }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-2">
                                <label>Unit Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="unit_price" id="unit_price" class="form-control" required
                                       value="{{ old('unit_price', $purchase->inv_pro_inv_unit_price) }}">
                            </div>

                            <div class="form-group col-sm-2">
                                <label>Total Qty <span class="text-danger">*</span></label>
                                <input type="number" step="1" min="1" name="qty" id="qty" class="form-control" required
                                       value="{{ old('qty', (int) ($purchase->inv_pro_inv_total_qty ?: $purchase->inv_pro_inv_qty)) }}">
                            </div>

                            <div class="form-group col-sm-2">
                                <label>Short Qty</label>
                                <input type="number" step="1" min="0" name="short_qty" id="short_qty" class="form-control"
                                       value="{{ old('short_qty', (int) $purchase->inv_pro_inv_short_qty) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-4">
                                <label>Short Remarks</label>
                                <input type="text" name="short_remarks" class="form-control"
                                       value="{{ old('short_remarks', $purchase->inv_pro_inv_short_remarks) }}">
                            </div>

                            <div class="form-group col-sm-4">
                                <label>Expire Date</label>
                                <input type="text" name="expired" id="expire" autocomplete="off" data-date-format="yyyy-mm-dd" class="form-control"
                                       value="{{ old('expired', $purchase->inv_pro_inv_exp_date) }}">
                            </div>

                            <div class="form-group col-sm-4">
                                <label>Total Amount</label>
                                <input type="text" id="line_total" class="form-control text-right" readonly>
                            </div>
                        </div>

                        <div class="row" id="serial_box">
                            <div class="form-group col-sm-12">
                                <label>Serial Numbers <small class="text-muted">(one per line or comma separated - must match Total Qty)</small></label>
                                <textarea name="serials" rows="4" class="form-control">{{ old('serials', implode("\n", array_filter($serial_numbers))) }}</textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update Purchase</button>
                                <a href="{{ route('buy.purchase.list') }}" class="btn btn-default">Cancel</a>
                                <a href="{{ route('reports.buy-pdf', ['invoice' => $purchase->inv_pro_inv_invoice_no]) }}?view=1" target="_blank" class="btn btn-info">
                                    <i class="fa fa-file-text-o"></i> View Invoice
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script>
    $(document).ready(function () {
        $("#issue, #expire").datepicker({
            todayHighlight: true,
            autoclose: true,
            format: 'yyyy-mm-dd'
        });

        function refresh() {
            var qty = parseFloat($('#qty').val()) || 0;
            var price = parseFloat($('#unit_price').val()) || 0;
            $('#line_total').val((qty * price).toFixed(2));

            var warranty = parseInt($('#product option:selected').data('warranty')) || 0;
            $('#serial_box').toggle(warranty > 0);
        }

        $('#qty, #unit_price').on('input', refresh);
        $('#product').on('change', refresh);
        refresh();
    });
</script>
@endsection

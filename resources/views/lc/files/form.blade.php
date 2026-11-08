@extends('layouts.master')
@section('title', $file->exists ? 'Edit LC File' : 'Add LC File')
@section('lc_class','active')
@section('lc_file_class','active')
@section($file->exists ? 'lc_file_list_class' : 'lc_file_add_class','active')
@section('content')
<style>
    #item_rows td { vertical-align: middle; }
    #item_rows .row-sl { width: 40px; text-align: center; }
    #item_rows input.num { text-align: right; }
</style>
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>{{ $file->exists ? 'Edit File ' . $file->lcf_file_no : 'File Opening' }}</h1>
    </section>

    @php
        $oldDetails = old('pro_det_id', $file->exists ? $file->items->pluck('lcfi_pro_det_id')->all() : ['']);
        $oldQty     = old('qty', $file->exists ? $file->items->pluck('lcfi_qty')->map(function ($q) { return $q + 0; })->all() : []);
        $oldPrice   = old('unit_price', $file->exists ? $file->items->pluck('lcfi_unit_price_fc')->map(function ($p) { return $p + 0; })->all() : []);
        $currencyRates = $currencies->pluck('lcc_rate', 'lcc_code');
    @endphp

    <form action="{{ $file->exists ? route('lc.files.update', $file->lcf_id) : route('lc.files.store') }}" method="post" id="file_form">
        @csrf
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3 class="box-title">
                        Import File (Proforma Invoice)
                        <a href="{{ route('lc.files.index') }}" class="btn btn-default btn-sm pull-right"><i class="fa fa-list"></i> File Opening Details</a>
                    </h3>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="file_no">File No <span class="text-danger">*</span></label>
                            <input type="text" name="file_no" id="file_no" class="form-control" maxlength="50" required
                                   value="{{ old('file_no', $file->lcf_file_no) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="file_date">File Date <span class="text-danger">*</span></label>
                            <input type="text" name="file_date" id="file_date" class="form-control datepicker" autocomplete="off" required
                                   value="{{ old('file_date', $file->lcf_file_date) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_id">Supplier (Exporter) <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplier_id" class="form-control" style="width: 100%;" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->inv_sup_id }}" {{ (string) old('supplier_id', $file->lcf_supplier_id) === (string) $supplier->inv_sup_id ? 'selected' : '' }}>
                                    {{ $supplier->inv_sup_com_name }} ({{ $supplier->inv_sup_person }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pi_no">PI No</label>
                            <input type="text" name="pi_no" id="pi_no" class="form-control" maxlength="100" value="{{ old('pi_no', $file->lcf_pi_no) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pi_date">PI Date</label>
                            <input type="text" name="pi_date" id="pi_date" class="form-control datepicker" autocomplete="off" value="{{ old('pi_date', $file->lcf_pi_date) }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="currency">Currency <span class="text-danger">*</span></label>
                            <select name="currency" id="currency" class="form-control dont-select-me" required>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->lcc_code }}" data-rate="{{ $currency->lcc_rate + 0 }}"
                                    {{ old('currency', $file->lcf_currency) === $currency->lcc_code ? 'selected' : '' }}>{{ $currency->lcc_code }}</option>
                                @endforeach
                            </select>
                            <small><a href="{{ route('lc.currencies') }}">Currency settings</a></small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="rate">Rate to BDT <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" min="0.0001" name="rate" id="rate" class="form-control" required
                                   value="{{ old('rate', $file->exists ? $file->lcf_rate + 0 : ($currencyRates[old('currency', $file->lcf_currency)] ?? 1) + 0) }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control" maxlength="255" value="{{ old('remarks', $file->lcf_remarks) }}">
                        </div>
                    </div>
                </div>

                <table class="table table-bordered" id="item_rows">
                    <thead>
                        <tr>
                            <th class="row-sl">SL</th>
                            <th>Product <span class="text-danger">*</span></th>
                            <th width="13%">Quantity <span class="text-danger">*</span></th>
                            <th width="16%">Unit Price (<span class="cur-code">{{ old('currency', $file->lcf_currency) }}</span>) <span class="text-danger">*</span></th>
                            <th width="16%" class="text-right">Total (<span class="cur-code">{{ old('currency', $file->lcf_currency) }}</span>)</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($oldDetails as $i => $oldDetail)
                        <tr class="item-row">
                            <td class="row-sl">{{ $i + 1 }}</td>
                            <td>
                                <select name="pro_det_id[]" class="form-control" style="width: 100%;" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ (string) $oldDetail === (string) $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}{{ $product->warranty ? ' - warranty' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" step="0.01" min="0.01" name="qty[]" class="form-control num item-qty" value="{{ $oldQty[$i] ?? '' }}" required></td>
                            <td><input type="number" step="0.0001" min="0" name="unit_price[]" class="form-control num item-price" value="{{ $oldPrice[$i] ?? '' }}" required></td>
                            <td class="text-right item-total">0.00</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row" title="Remove"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <button type="button" class="btn btn-info btn-sm" id="add_row"><i class="fa fa-plus"></i> Add Product</button>
                                <span class="pull-right"><b>PI Value (<span class="cur-code">{{ old('currency', $file->lcf_currency) }}</span>)</b></span>
                            </td>
                            <td class="text-right"><b id="total_fc">0.00</b></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4"><span class="pull-right">PI Value in BDT</span></td>
                            <td class="text-right"><b id="total_bdt">0.00</b></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="text-right">
                    <a href="{{ $file->exists ? route('lc.files.show', $file->lcf_id) : route('lc.files.index') }}" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ $file->exists ? 'Update File' : 'Open File' }}</button>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('custom_script')
<script type="text/javascript">
$(document).ready(function () {
    function money(value) {
        return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function refresh() {
        var total = 0;
        $('.item-row').each(function (i) {
            var line = (parseFloat($(this).find('.item-qty').val()) || 0) * (parseFloat($(this).find('.item-price').val()) || 0);
            line = Math.round(line * 100) / 100;
            total += line;
            $(this).find('.row-sl').text(i + 1);
            $(this).find('.item-total').text(money(line));
        });
        var rate = parseFloat($('#rate').val()) || 0;
        $('#total_fc').text(money(total));
        $('#total_bdt').text(money(Math.round(total * rate * 100) / 100));
        $('.remove-row').prop('disabled', $('.item-row').length === 1);
    }

    // Picking a currency fills in its default rate (BDT is always 1)
    $('#currency').on('change', function () {
        var code = $(this).val();
        $('.cur-code').text(code);
        $('#rate').val($(this).find(':selected').data('rate')).prop('readonly', code === 'BDT');
        refresh();
    });
    $('#rate').prop('readonly', $('#currency').val() === 'BDT');

    $('#add_row').on('click', function () {
        var row = $('.item-row:first').clone();

        // custom.js turns the select into select2; strip the copied widget and start a fresh one
        row.find('.select2-container').remove();
        var select = row.find('select')
            .removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id aria-hidden tabindex');
        select.find('option').removeAttr('data-select2-id selected');
        select.val('');
        row.find('input').val('');

        $('#item_rows tbody').append(row);
        if ($.fn.select2) {
            select.select2({ placeholder: 'Select option', allowClear: true });
        }
        refresh();
    });

    $('#item_rows').on('click', '.remove-row', function () {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            refresh();
        }
    });

    $('#item_rows').on('input change', '.item-qty, .item-price', refresh);
    $('#rate').on('input change', refresh);
    refresh();
});
</script>
@endsection

@extends('layouts.master')
@section('title','LC Opening')
@section('lc_class','active')
@section('lcm_class','active')
@section('lc_opening_class','active')
@section('content')
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>LC Opening</h1>
    </section>

    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title"><h3 class="box-title">Open an LC against an import file</h3></div>
        </div>
        <div class="panel-body">
            @if($files->isEmpty())
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> There is no open file waiting for an LC.
                    <a href="{{ route('lc.files.create') }}">Add a file</a> first.
                </div>
            @else
            <form action="{{ route('lc.opening.store') }}" method="post" id="lc_form">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="file_id">Import File <span class="text-danger">*</span></label>
                            <select name="file_id" id="file_id" class="form-control" style="width: 100%;" required>
                                <option value="">Select File</option>
                                @foreach($files as $f)
                                <option value="{{ $f->lcf_id }}" data-currency="{{ $f->lcf_currency }}" data-rate="{{ $f->lcf_rate + 0 }}" data-value="{{ $f->lcf_total_fc + 0 }}"
                                    {{ (string) old('file_id', $selectedFile->lcf_id ?? '') === (string) $f->lcf_id ? 'selected' : '' }}>
                                    {{ $f->lcf_file_no }} - {{ $f->supplier->inv_sup_com_name ?? '' }} ({{ $f->lcf_currency }} {{ number_format($f->lcf_total_fc, 2) }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lc_no">LC No <span class="text-danger">*</span></label>
                            <input type="text" name="lc_no" id="lc_no" class="form-control" maxlength="100" value="{{ old('lc_no') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lc_date">LC Date <span class="text-danger">*</span></label>
                            <input type="text" name="lc_date" id="lc_date" class="form-control datepicker" autocomplete="off" value="{{ old('lc_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bank_id">Issuing Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id" class="form-control" style="width: 100%;" required>
                                <option value="">Select Bank</option>
                                @foreach($issuingBanks as $id => $label)
                                <option value="{{ $id }}" {{ (string) old('bank_id') === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lc_value_fc">LC Value (<span class="cur-code">-</span>) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="lc_value_fc" id="lc_value_fc" class="form-control text-right" value="{{ old('lc_value_fc') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lc_rate">Rate to BDT <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" min="0.0001" name="lc_rate" id="lc_rate" class="form-control text-right" value="{{ old('lc_rate') }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>LC Value in BDT</label>
                            <p class="form-control-static"><b id="value_bdt">0.00</b></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="margin_percent">Margin % <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" name="margin_percent" id="margin_percent" class="form-control text-right" value="{{ old('margin_percent', 0) }}" required>
                            <small class="text-muted">Margin amount: <b id="margin_amount">0.00</b> BDT</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="text" name="expiry_date" id="expiry_date" class="form-control datepicker" autocomplete="off" value="{{ old('expiry_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="last_ship_date">Last Shipment Date</label>
                            <input type="text" name="last_ship_date" id="last_ship_date" class="form-control datepicker" autocomplete="off" value="{{ old('last_ship_date') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control" maxlength="255" value="{{ old('remarks') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="pay_margin" id="pay_margin" value="1" {{ old('pay_margin') ? 'checked' : '' }}>
                                Pay the margin now
                            </label>
                            <select name="margin_bank_id" id="margin_bank_id" class="form-control" style="width: 100%;">
                                <option value="">Pay from (bank / cash)</option>
                                @foreach($payBanks as $id => $label)
                                <option value="{{ $id }}" {{ (string) old('margin_bank_id') === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Recorded on the LC Statement and taken out of that account. You can also pay it later from the LC Statement.</small>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Open this LC?');"><i class="fa fa-university"></i> Open LC</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script type="text/javascript">
$(document).ready(function () {
    function money(value) {
        return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function refresh() {
        var bdt = Math.round((parseFloat($('#lc_value_fc').val()) || 0) * (parseFloat($('#lc_rate').val()) || 0) * 100) / 100;
        var margin = Math.round(bdt * (parseFloat($('#margin_percent').val()) || 0)) / 100;
        $('#value_bdt').text(money(bdt));
        $('#margin_amount').text(money(margin));
    }

    // Picking a file fills in its PI value, currency and rate
    function fillFromFile(keepTyped) {
        var opt = $('#file_id').find(':selected');
        if (!opt.val()) {
            $('.cur-code').text('-');
            return;
        }
        $('.cur-code').text(opt.data('currency'));
        if (!keepTyped || !$('#lc_value_fc').val()) {
            $('#lc_value_fc').val(opt.data('value'));
        }
        if (!keepTyped || !$('#lc_rate').val()) {
            $('#lc_rate').val(opt.data('rate'));
        }
        $('#lc_rate').prop('readonly', opt.data('currency') === 'BDT');
        refresh();
    }

    // The margin is paid from the issuing bank unless another account is chosen
    $('#bank_id').on('change', function () {
        if (!$('#margin_bank_id').val()) {
            $('#margin_bank_id').val($(this).val()).trigger('change.select2');
        }
    });

    $('#file_id').on('change', function () { fillFromFile(false); });
    $('#lc_value_fc, #lc_rate, #margin_percent').on('input change', refresh);
    fillFromFile(true);
});
</script>
@endsection

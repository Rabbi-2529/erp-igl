@extends('layouts.master')
@section('title', 'New Journal Voucher')
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false])
@include('inventory.accounts.journal._nav')
@include('lc._alerts')
@php
    $oldAccounts = old('account', []);
    $rowsCount = max(4, count($oldAccounts));
    // the account list, used for every line (and for lines added in the page)
    $optionsHtml = '<option value="">Choose an account</option>';
    foreach ($groups as $label => $list) {
        $optionsHtml .= '<optgroup label="' . e($label) . '">';
        foreach ($list as $a) {
            $optionsHtml .= '<option value="' . e($a->key) . '">' . e(trim($a->code . ' ' . $a->name)) . '</option>';
        }
        $optionsHtml .= '</optgroup>';
    }
@endphp
<style>
    .jv-lines td { vertical-align: middle !important; }
    .jv-lines .form-control { height: 34px; }
    .jv-lines .num-in { text-align: right; }
    .jv-total td { font-weight: 700; }
    .jv-state { font-weight: 700; }
    .jv-state.ok { color: #0b7a3b; }
    .jv-state.bad { color: #c4161f; }
    html[data-theme="dark"] .jv-state.ok { color: #6fd79a; }
    html[data-theme="dark"] .jv-state.bad { color: #f58b90; }
</style>

<div class="rep-head">
    <div>
        <h3>New Journal Voucher</h3>
        <p class="rep-sub">Each line goes to one account as a debit or a credit. The voucher can be saved when debits equal credits.</p>
    </div>
    <a href="{{ route('accounts.journal.vouchers') }}" class="btn btn-default"><i class="fa fa-list"></i> All vouchers</a>
</div>

<form method="POST" action="{{ route('accounts.journal.vouchers.store') }}" id="jvForm">
    @csrf
    <div class="panel panel-bd">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-3 form-group">
                    <label for="jv_date">Date</label>
                    <input type="text" class="form-control datepicker" id="jv_date" name="date" value="{{ old('date', now()->toDateString()) }}" autocomplete="off" required>
                </div>
                <div class="col-sm-9 form-group">
                    <label for="jv_narration">Narration (what this voucher is for)</label>
                    <input type="text" class="form-control" id="jv_narration" name="narration" maxlength="255" value="{{ old('narration') }}" placeholder="e.g. Office rent of November 2026, not paid yet" required>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered jv-lines">
                    <thead><tr><th style="width: 40%;">Account</th><th style="width: 15%;" class="num">Debit</th><th style="width: 15%;" class="num">Credit</th><th>Line note (optional)</th><th style="width: 44px;"></th></tr></thead>
                    <tbody id="jvRows">
                        @for($i = 0; $i < $rowsCount; $i++)
                            <tr class="jv-row">
                                <td><select class="form-control jv-account" name="account[]" style="width: 100%;" aria-label="Account of line {{ $i + 1 }}">{!! str_replace('value="' . e($oldAccounts[$i] ?? '') . '"', 'value="' . e($oldAccounts[$i] ?? '') . '" selected', $optionsHtml) !!}</select></td>
                                <td><input type="number" class="form-control num-in jv-dr" name="debit[]" min="0" step="0.01" value="{{ old('debit.' . $i) }}" aria-label="Debit of line {{ $i + 1 }}"></td>
                                <td><input type="number" class="form-control num-in jv-cr" name="credit[]" min="0" step="0.01" value="{{ old('credit.' . $i) }}" aria-label="Credit of line {{ $i + 1 }}"></td>
                                <td><input type="text" class="form-control" name="note[]" maxlength="255" value="{{ old('note.' . $i) }}" aria-label="Note of line {{ $i + 1 }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-default btn-xs jv-remove" title="Remove line" aria-label="Remove line"><i class="fa fa-times"></i></button></td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot>
                        <tr class="jv-total">
                            <td class="text-right">Total</td>
                            <td class="num" id="jvDr">0.00</td>
                            <td class="num" id="jvCr">0.00</td>
                            <td colspan="2"><span class="jv-state" id="jvState"></span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" class="btn btn-default btn-sm" id="jvAdd"><i class="fa fa-plus"></i> Add a line</button>

            <div style="margin-top: 16px;">
                <button type="submit" class="btn btn-success" id="jvSave"><i class="fa fa-save"></i> Save voucher</button>
                <a href="{{ route('accounts.journal.vouchers') }}" class="btn btn-default">Cancel</a>
            </div>
            @if($needsApproval)
                <p class="rep-note" style="font-weight: 700;"><i class="fa fa-info-circle"></i> This voucher will wait for a company admin's approval before it counts in the books.</p>
            @endif
            @if($lockedUntil)
                <p class="rep-note"><i class="fa fa-lock"></i> The books are closed up to {{ $lockedUntil->format('d M Y') }}: use a later date.</p>
            @endif
            <p class="rep-note">
                Not in this list: {{ $notAllowed }}. They change only through their own pages (payments, invoices, Accounts &gt; Bank),
                so their balances stay the same everywhere. Add your own accounts in the Chart of Accounts.
                A saved voucher cannot be edited; it can be cancelled with a reason.
            </p>
        </div>
    </div>
</form>

<script type="text/template" id="jvRowTemplate">
    <tr class="jv-row">
        <td><select class="form-control jv-account dont-select-me" name="account[]" style="width: 100%;" aria-label="Account">{!! $optionsHtml !!}</select></td>
        <td><input type="number" class="form-control num-in jv-dr" name="debit[]" min="0" step="0.01" aria-label="Debit"></td>
        <td><input type="number" class="form-control num-in jv-cr" name="credit[]" min="0" step="0.01" aria-label="Credit"></td>
        <td><input type="text" class="form-control" name="note[]" maxlength="255" aria-label="Note"></td>
        <td class="text-center"><button type="button" class="btn btn-default btn-xs jv-remove" title="Remove line" aria-label="Remove line"><i class="fa fa-times"></i></button></td>
    </tr>
</script>
@endsection

@section('custom_script')
<script>
(function ($) {
    function fmt(n) { return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function totals() {
        var dr = 0, cr = 0, lines = 0;
        $('#jvRows .jv-row').each(function () {
            var d = parseFloat($(this).find('.jv-dr').val()) || 0, c = parseFloat($(this).find('.jv-cr').val()) || 0;
            dr += d; cr += c;
            if ((d || c) && $(this).find('.jv-account').val()) { lines++; }
        });
        dr = Math.round(dr * 100) / 100; cr = Math.round(cr * 100) / 100;
        $('#jvDr').text(fmt(dr)); $('#jvCr').text(fmt(cr));
        var diff = Math.round((dr - cr) * 100) / 100, ok = dr > 0 && diff === 0 && lines >= 2;
        $('#jvState').text(ok ? 'Balanced' : (dr === 0 && cr === 0 ? 'Enter the amounts' : (diff !== 0 ? 'Difference ' + fmt(Math.abs(diff)) : 'Choose an account on each line')))
            .toggleClass('ok', ok).toggleClass('bad', !ok && (dr > 0 || cr > 0));
        $('#jvSave').prop('disabled', !ok);
    }
    // a line is a debit or a credit
    $(document).on('input', '.jv-dr', function () { if (this.value) { $(this).closest('tr').find('.jv-cr').val(''); } totals(); });
    $(document).on('input', '.jv-cr', function () { if (this.value) { $(this).closest('tr').find('.jv-dr').val(''); } totals(); });
    $(document).on('change', '.jv-account', totals);
    $('#jvAdd').on('click', function () {
        var row = $($('#jvRowTemplate').html().trim());
        $('#jvRows').append(row);
        if ($.fn.select2) { row.find('select').select2({ width: '100%' }); }
        // the next line suggests the amount that balances the voucher
        var dr = 0, cr = 0;
        $('#jvRows .jv-row').each(function () { dr += parseFloat($(this).find('.jv-dr').val()) || 0; cr += parseFloat($(this).find('.jv-cr').val()) || 0; });
        var diff = Math.round((dr - cr) * 100) / 100;
        if (diff > 0) { row.find('.jv-cr').val(diff); } else if (diff < 0) { row.find('.jv-dr').val(-diff); }
        totals();
    });
    $(document).on('click', '.jv-remove', function () {
        if ($('#jvRows .jv-row').length > 2) { $(this).closest('tr').remove(); } else { $(this).closest('tr').find('input').val(''); $(this).closest('tr').find('select').val('').trigger('change'); }
        totals();
    });
    $('#jvForm').on('submit', function () { return confirm('Save this journal voucher? It cannot be edited after saving.'); });
    totals();
})(jQuery);
</script>
@endsection

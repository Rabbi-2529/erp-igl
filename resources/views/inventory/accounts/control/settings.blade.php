@extends('layouts.master')
@section('title', 'Accounts Settings')
@section('accounts_class', 'active')
@section('accounts_control_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false])
@include('inventory.accounts.journal._nav', ['navGroup' => 'control'])
@include('lc._alerts')
<style>
    .set-check { display: flex; gap: 10px; align-items: flex-start; font-weight: 400; cursor: pointer; margin: 4px 0 12px; }
    .set-check input { margin-top: 3px; }
    .set-check b { display: block; }
</style>

<div class="rep-head">
    <div>
        <h3>Accounts Settings</h3>
        <p class="rep-sub">VAT and journal voucher approval for the whole company.@unless($isAdmin) Only a company admin can change them.@endunless</p>
    </div>
</div>

<form method="POST" action="{{ route('accounts.control.settings.save') }}">
    @csrf
    <fieldset @unless($isAdmin) disabled @endunless>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-bd">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-percent"></i> VAT</h4></div></div>
                <div class="panel-body">
                    <label class="set-check">
                        <input type="hidden" name="vat_on" value="0">
                        <input type="checkbox" name="vat_on" value="1" {{ old('vat_on', $settings['vat_on']) ? 'checked' : '' }}>
                        <span><b>The business is VAT registered</b>
                            <span class="text-muted">Sale and purchase prices include VAT. The ERP takes the VAT out of each invoice for VAT Payable, the VAT report and Profit &amp; Loss.</span></span>
                    </label>
                    <div class="row">
                        <div class="col-sm-6 form-group"><label for="vat_bin">BIN (Business Identification Number)</label><input type="text" class="form-control" id="vat_bin" name="vat_bin" maxlength="30" value="{{ old('vat_bin', $settings['vat_bin']) }}"></div>
                        <div class="col-sm-6 form-group"><label for="vat_from">VAT counts on invoices from</label><input type="text" class="form-control datepicker" id="vat_from" name="vat_from" value="{{ old('vat_from', $settings['vat_from']) }}" autocomplete="off" placeholder="from the first invoice"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group"><label for="vat_rate">VAT on sales (%)</label><input type="number" class="form-control" id="vat_rate" name="vat_rate" min="0" max="100" step="0.01" value="{{ old('vat_rate', $settings['vat_rate']) }}" required></div>
                        <div class="col-sm-6 form-group"><label for="vat_rebate_rate">VAT rebate on purchases (%)</label><input type="number" class="form-control" id="vat_rebate_rate" name="vat_rebate_rate" min="0" max="100" step="0.01" value="{{ old('vat_rebate_rate', $settings['vat_rebate_rate']) }}" required></div>
                    </div>
                    <p class="rep-note">
                        Example at 15%: a sale of 1,150 holds 150 VAT (1,150 &times; 15 &divide; 115). The rebate is the VAT inside purchase prices that you can set off (0 = none).
                        Sales income in Profit &amp; Loss is shown without VAT. VAT payments are made on Accounts &gt; VAT.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-bd">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-check-square-o"></i> Journal vouchers</h4></div></div>
                <div class="panel-body">
                    <label class="set-check">
                        <input type="hidden" name="approval_on" value="0">
                        <input type="checkbox" name="approval_on" value="1" {{ old('approval_on', $settings['approval_on']) ? 'checked' : '' }}>
                        <span><b>Vouchers need an admin's approval</b>
                            <span class="text-muted">A voucher made by a staff user waits as "pending" and does not count in the books until a company admin approves it. Vouchers made by an admin count at once.</span></span>
                    </label>
                </div>
            </div>
            <div class="panel panel-bd">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-calendar-check-o"></i> Fiscal year</h4></div></div>
                <div class="panel-body">
                    <p class="rep-note" style="margin-top: 0;">The fiscal year runs 1 July to 30 June. Close a year on <a href="{{ route('accounts.control.fiscal-years') }}">Fiscal Years</a>.</p>
                </div>
            </div>
        </div>
    </div>
    @if($isAdmin)
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save settings</button>
    @endif
    </fieldset>
</form>
@endsection

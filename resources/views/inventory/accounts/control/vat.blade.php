@extends('layouts.master')
@section('title', 'VAT')
@section('accounts_class', 'active')
@section('accounts_control_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'VAT Summary',
    'printInfo' => ['Fiscal year' => \App\Services\AccountsJournal::fyLabel($fyStart), 'BIN' => $settings['vat_bin'] ?: '-', 'VAT rate' => $settings['vat_rate'] . '% on sales, ' . $settings['vat_rebate_rate'] . '% rebate']])
@include('inventory.accounts.journal._nav', ['navGroup' => 'control'])
@include('lc._alerts')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
@endphp

<div class="rep-head">
    <div>
        <h3>VAT</h3>
        <p class="rep-sub">VAT inside your sales and purchase prices, month by month, and what is paid to the government. Fiscal year {{ \App\Services\AccountsJournal::fyLabel($fyStart) }}.</p>
    </div>
    <form method="GET" class="rep-filter">
        <div>
            <label for="fy">Fiscal year</label>
            <select class="form-control dont-select-me" id="fy" name="fy">
                @foreach($years as $y)<option value="{{ $y->year }}" @selected($y->eq($fyStart))>{{ \App\Services\AccountsJournal::fyLabel($y) }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

@if(!$data)
    <div class="panel panel-bd"><div class="panel-body">
        <p class="rep-empty">VAT is off. If the business is VAT registered, turn it on in <a href="{{ route('accounts.control.settings') }}">Accounts Settings</a>.</p>
    </div></div>
@else
    @php
        $m = collect($data['months']);
        $last = $m->last();
        $due = $m->filter(function ($x) { return $x->month->lte(now()); })->last();
    @endphp
    <div class="rep-cards">
        <div class="rep-card green"><b>{{ $money($m->sum('output')) }}</b><span>VAT on sales ({{ $settings['vat_rate'] }}%)</span></div>
        <div class="rep-card blue"><b>{{ $money($m->sum('input')) }}</b><span>VAT rebate on purchases</span></div>
        <div class="rep-card black"><b>{{ $money($m->sum('paid')) }}</b><span>VAT paid this year</span></div>
        @php
            $nowBalance = $due ? $due->balance : $data['opening'];
        @endphp
        <div class="rep-card red"><b class="{{ $nowBalance > 0 ? 'rep-neg' : '' }}">{{ $money(abs($nowBalance)) }}</b><span>{{ $nowBalance >= 0 ? 'VAT payable now' : 'VAT credit to carry forward (rebate more than VAT on sales)' }}</span></div>
    </div>

    <div class="panel panel-bd">
        <div class="panel-body">
            <table class="table table-bordered table-striped rep-table">
                <thead>
                    <tr><th>Month</th><th class="num">Sales (with VAT)</th><th class="num">VAT on sales</th><th class="num">Purchases (with VAT)</th><th class="num">VAT rebate</th>
                        <th class="num">Net VAT</th><th class="num">Paid</th><th class="num">Payable at month end</th></tr>
                </thead>
                <tbody>
                    <tr class="rep-sub-total"><td colspan="7">Payable at the start of the year</td><td class="num">{{ $money($data['opening']) }}</td></tr>
                    @foreach($data['months'] as $x)
                        <tr @if($x->month->gt(now())) class="text-muted" @endif>
                            <td style="white-space: nowrap;">{{ $x->month->format('F Y') }}</td>
                            <td class="num">{{ $x->sales ? $money($x->sales) : '' }}</td>
                            <td class="num">{{ $x->output ? $money($x->output) : '' }}</td>
                            <td class="num">{{ $x->purchases ? $money($x->purchases) : '' }}</td>
                            <td class="num">{{ $x->input ? $money($x->input) : '' }}</td>
                            <td class="num">{{ $x->net ? $money($x->net) : '' }}</td>
                            <td class="num">{{ $x->paid ? $money($x->paid) : '' }}</td>
                            <td class="num {{ $x->balance < 0 ? 'rep-neg' : '' }}">{{ $x->month->gt(now()) ? '' : $money($x->balance) }}</td>
                        </tr>
                    @endforeach
                    <tr class="rep-grand"><td>Total</td><td class="num">{{ $money($m->sum('sales')) }}</td><td class="num">{{ $money($m->sum('output')) }}</td>
                        <td class="num">{{ $money($m->sum('purchases')) }}</td><td class="num">{{ $money($m->sum('input')) }}</td><td class="num">{{ $money($m->sum('net')) }}</td>
                        <td class="num">{{ $money($m->sum('paid')) }}</td><td></td></tr>
                </tbody>
            </table>
            <p class="rep-note">
                Sales are product sales, service and delivery charges less returns and discounts; purchases are less purchase returns. Prices include VAT, so
                VAT = amount &times; {{ $settings['vat_rate'] }} &divide; {{ 100 + $settings['vat_rate'] }}@if($settings['vat_rebate_rate'] > 0) (rebate: &times; {{ $settings['vat_rebate_rate'] }} &divide; {{ 100 + $settings['vat_rebate_rate'] }})@endif.
                @if($settings['vat_from']) VAT counts on invoices from {{ \Carbon\Carbon::parse($settings['vat_from'])->format('d M Y') }}. @endif
                Use these figures for the monthly return (Mushak 9.1).
            </p>
        </div>
    </div>

    <div class="row rep-noprint">
        <div class="col-md-5">
            <div class="panel panel-bd">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-money"></i> Pay VAT</h4></div></div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('accounts.control.vat.pay') }}" onsubmit="return confirm('Save this VAT payment?');">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="period">For the month</label>
                                <select class="form-control dont-select-me" id="period" name="period">
                                    @foreach($data['months'] as $x)
                                        @if($x->month->lte(now()))<option value="{{ $x->month->format('Y-m') }}" @selected(old('period', optional($due)->month ? $due->month->copy()->subMonth()->format('Y-m') : '') === $x->month->format('Y-m'))>{{ $x->month->format('F Y') }}</option>@endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 form-group"><label for="amount">Amount</label><input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required></div>
                        </div>
                        <div class="form-group">
                            <label for="account_id">Pay from</label>
                            <select class="form-control" id="account_id" name="account_id" required style="width: 100%;">
                                <option value="">Choose a bank or cash account</option>
                                @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('account_id') == $a->id)>{{ $a->is_cash ? 'Cash: ' : '' }}{{ $a->label }} ({{ $money($a->closing) }})</option>@endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group"><label for="date">Payment date</label><input type="text" class="form-control datepicker" id="date" name="date" value="{{ old('date', now()->toDateString()) }}" autocomplete="off" required></div>
                            <div class="col-sm-6 form-group"><label for="note">Challan / note</label><input type="text" class="form-control" id="note" name="note" maxlength="150" value="{{ old('note') }}"></div>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save payment</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="panel panel-bd">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-history"></i> VAT payments this year</h4></div></div>
                <div class="panel-body">
                    @if($payments->isEmpty())
                        <p class="rep-empty">No VAT payments in {{ \App\Services\AccountsJournal::fyLabel($fyStart) }}.</p>
                    @else
                        <table class="table table-bordered table-striped rep-table">
                            <thead><tr><th>Date</th><th>For</th><th>From</th><th>Voucher</th><th class="num">Amount</th></tr></thead>
                            <tbody>
                                @foreach($payments as $p)
                                    <tr><td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($p->inv_abs_transaction_date)->format('d M Y') }}</td>
                                        <td>{{ $p->inv_abs_description }}</td><td>{{ $accountNames[$p->inv_abs_bank_id] ?? ('#' . $p->inv_abs_bank_id) }}</td>
                                        <td>{{ $p->inv_abs_voucher_no }}</td><td class="num">{{ $money($p->inv_abs_debit - $p->inv_abs_credit) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

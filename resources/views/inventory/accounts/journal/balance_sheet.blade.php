@extends('layouts.master')
@section('title', 'Balance Sheet')
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Balance Sheet',
    'printInfo' => ['As of' => $asOf->format('d M Y'), 'Fiscal year from' => $bs['fyStart']->format('d M Y')]])
@include('inventory.accounts.journal._nav')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $show = function ($lines) { return array_values(array_filter($lines, function ($l) { return abs($l->amount) > 0.004 || ($l->group && abs($l->total) > 0.004); })); };
    $balanced = abs($bs['difference']) < 0.005;
    $titles = ['asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity'];
    $sectionTotal = ['asset' => $bs['assets'], 'liability' => $bs['liabilities'], 'equity' => $bs['equity']];
@endphp

<div class="rep-head">
    <div>
        <h3>Balance Sheet</h3>
        <p class="rep-sub">What the business has and owes on {{ $asOf->format('d M Y') }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div><label for="as_of">As of</label><input type="text" class="form-control datepicker" id="as_of" name="as_of" value="{{ $asOf->toDateString() }}" autocomplete="off"></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="rep-cards">
    <div class="rep-card green"><b>{{ $money($bs['assets']) }}</b><span>Total assets</span></div>
    <div class="rep-card red"><b>{{ $money($bs['liabilities']) }}</b><span>Total liabilities</span></div>
    <div class="rep-card black"><b>{{ $money($bs['equity']) }}</b><span>Equity (owner's share)</span></div>
    <div class="rep-card {{ $balanced ? 'green' : 'red' }}"><b class="{{ $balanced ? 'rep-pos' : 'rep-neg' }}">{{ $balanced ? 'Balanced' : $money($bs['difference']) }}</b><span>{{ $balanced ? 'Assets = liabilities + equity' : 'Difference' }}</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <table class="table rep-table">
            <tbody>
                @foreach($titles as $type => $title)
                    <tr class="rep-group"><th colspan="2">{{ $title }}</th></tr>
                    @php
                        $lines = $show($bs['sections'][$type]);
                    @endphp
                    @foreach($lines as $l)
                        <tr @if($l->group) style="font-weight: 700;" @endif>
                            <td style="padding-left: {{ 24 + $l->depth * 22 }}px !important;">
                                <a href="{{ route('accounts.journal.ledger', ['account' => $l->account->key, 'from' => $bs['fyStart']->toDateString(), 'to' => $asOf->toDateString()]) }}">{{ $l->account->name }}</a>
                            </td>
                            <td class="num {{ ($l->group ? $l->total : $l->amount) < 0 ? 'rep-neg' : '' }}">{{ $money($l->group ? $l->total : $l->amount) }}</td>
                        </tr>
                    @endforeach
                    @if($type === 'equity')
                        <tr><td class="rep-indent">Profit / loss up to {{ $bs['fyStart']->copy()->subDay()->format('d M Y') }}</td><td class="num {{ $bs['profitBefore'] < 0 ? 'rep-neg' : '' }}">{{ $money($bs['profitBefore']) }}</td></tr>
                        <tr><td class="rep-indent">Profit / loss this year ({{ $bs['fyStart']->format('d M Y') }} to {{ $asOf->format('d M Y') }})</td><td class="num {{ $bs['profitYear'] < 0 ? 'rep-neg' : '' }}">{{ $money($bs['profitYear']) }}</td></tr>
                    @elseif(!count($lines))
                        <tr><td class="rep-indent rep-note" colspan="2">None.</td></tr>
                    @endif
                    <tr class="rep-sub-total"><td>Total {{ strtolower($title) }}</td><td class="num">{{ $money($sectionTotal[$type]) }}</td></tr>
                @endforeach
                <tr class="rep-grand"><td>Total liabilities and equity</td><td class="num">{{ $money($bs['liabilities'] + $bs['equity']) }}</td></tr>
                @unless($balanced)
                    <tr><td class="rep-neg">Difference (assets less liabilities and equity)</td><td class="num rep-neg">{{ $money($bs['difference']) }}</td></tr>
                @endunless
            </tbody>
        </table>
        <p class="rep-note">
            Cash and bank, customers and suppliers are the same balances the ERP shows on their own pages. Stock is purchases at cost less the cost of goods sold.
            Profit is the same as in Profit &amp; Loss (with payroll salaries). The fiscal year runs July to June.
            A "to match" line, if any, should be checked: a payment is in a customer / supplier ledger but not in bank or cash, or the other way round.
        </p>
    </div>
</div>
@endsection

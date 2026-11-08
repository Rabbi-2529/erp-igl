@extends('layouts.master')
@section('title', 'Cash Flow')
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Cash Flow Statement', 'printInfo' => ['Period' => $from->format('d M Y') . ' to ' . $to->format('d M Y')]])
@include('inventory.accounts.journal._nav')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $titles = ['operating' => 'Operating activities (day-to-day business)', 'investing' => 'Investing activities', 'financing' => 'Financing activities (loans and owner)'];
    $balanced = abs($cf['difference']) < 0.005;
@endphp

<div class="rep-head">
    <div>
        <h3>Cash Flow</h3>
        <p class="rep-sub">Where the money in bank, mobile banking and cash came from and went to: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div><label for="from">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ $from->toDateString() }}" autocomplete="off"></div>
        <div><label for="to">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ $to->toDateString() }}" autocomplete="off"></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="rep-cards">
    <div class="rep-card black"><b>{{ $money($cf['opening']) }}</b><span>Money at the start ({{ $from->format('d M Y') }})</span></div>
    <div class="rep-card {{ $cf['totals']['operating'] >= 0 ? 'green' : 'red' }}"><b class="{{ $cf['totals']['operating'] < 0 ? 'rep-neg' : '' }}">{{ $money($cf['totals']['operating']) }}</b><span>From operating activities</span></div>
    <div class="rep-card blue"><b class="{{ $cf['totals']['financing'] < 0 ? 'rep-neg' : '' }}">{{ $money($cf['totals']['financing']) }}</b><span>From loans and owner</span></div>
    <div class="rep-card green"><b>{{ $money($cf['closing']) }}</b><span>Money at the end ({{ $to->format('d M Y') }})</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <table class="table rep-table">
            <tbody>
                <tr class="rep-sub-total"><td>Money in bank, mobile banking and cash at the start</td><td class="num">{{ $money($cf['opening']) }}</td></tr>
                @foreach($titles as $key => $title)
                    <tr class="rep-group"><th colspan="2">{{ $title }}</th></tr>
                    @forelse($cf['sections'][$key] as $label => $amount)
                        <tr><td class="rep-indent">{{ $label }}</td><td class="num {{ $amount < 0 ? 'rep-neg' : '' }}">{{ $money($amount) }}</td></tr>
                    @empty
                        <tr><td class="rep-indent rep-note" colspan="2">{{ $key === 'investing' ? 'No fixed asset payments recorded in bank or cash (Fixed Assets are added on their own page).' : 'None in this period.' }}</td></tr>
                    @endforelse
                    <tr class="rep-sub-total"><td>Net cash from {{ strtolower(strtok($title, ' (')) }} activities</td><td class="num {{ $cf['totals'][$key] < 0 ? 'rep-neg' : '' }}">{{ $money($cf['totals'][$key]) }}</td></tr>
                @endforeach
                @if($cf['sections']['transfers'])
                    <tr class="rep-group"><th colspan="2">Between your own accounts (should be zero)</th></tr>
                    @foreach($cf['sections']['transfers'] as $label => $amount)
                        <tr><td class="rep-indent">{{ $label }} (net)</td><td class="num rep-neg">{{ $money($amount) }}</td></tr>
                    @endforeach
                @endif
                <tr class="rep-sub-total"><td>Net change in money</td><td class="num {{ $cf['net'] < 0 ? 'rep-neg' : '' }}">{{ $money($cf['net']) }}</td></tr>
                <tr class="rep-grand"><td>Money in bank, mobile banking and cash at the end</td><td class="num">{{ $money($cf['closing']) }}</td></tr>
                @unless($balanced)
                    <tr><td class="rep-neg">Difference</td><td class="num rep-neg">{{ $money($cf['difference']) }}</td></tr>
                @endunless
            </tbody>
        </table>

        <h4 class="rep-day">By account</h4>
        <table class="table table-bordered table-striped rep-table">
            <thead><tr><th>Account</th><th class="num">Start</th><th class="num">Money in</th><th class="num">Money out</th><th class="num">End</th></tr></thead>
            <tbody>
                @foreach($cf['accounts'] as $a)
                    @if(abs($a->opening) > 0.004 || $a->in || $a->out || abs($a->closing) > 0.004)
                        <tr><td>{{ $a->is_cash ? 'Cash: ' : '' }}{{ $a->label }}</td><td class="num">{{ $money($a->opening) }}</td><td class="num">{{ $a->in ? $money($a->in) : '' }}</td>
                            <td class="num">{{ $a->out ? $money($a->out) : '' }}</td><td class="num {{ $a->closing < 0 ? 'rep-neg' : '' }}">{{ $money($a->closing) }}</td></tr>
                    @endif
                @endforeach
                <tr class="rep-grand"><td>Total</td><td class="num">{{ $money($cf['opening']) }}</td><td class="num">{{ $money($cf['accounts']->sum('in')) }}</td>
                    <td class="num">{{ $money($cf['accounts']->sum('out')) }}</td><td class="num">{{ $money($cf['closing']) }}</td></tr>
            </tbody>
        </table>
        <p class="rep-note">Every bank, mobile banking and cash movement is counted once, by what it was for. Transfers between your own accounts (contra, petty cash, loan transfers) cancel out. The default period is this fiscal year.</p>
    </div>
</div>
@endsection

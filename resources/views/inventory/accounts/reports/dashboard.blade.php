@extends('layouts.master')
@section('title', 'Accounts Dashboard')
@section('accounts_class', 'active')
@section('accounts_reports_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['printTitle' => 'Accounts Summary', 'printInfo' => ['Date' => $today->format('d M Y')]])

<div class="rep-head">
    <div>
        <h3>Accounts Dashboard</h3>
        <p class="rep-sub">Today, {{ $today->format('d M Y') }}. Money of this branch, what customers owe you and what you owe suppliers.</p>
    </div>
    <button type="button" class="btn btn-default rep-noprint" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
</div>

<div class="rep-cards">
    <div class="rep-card green"><b>{{ number_format($cash, 2) }}</b><span>Cash in hand</span></div>
    <div class="rep-card blue"><b>{{ number_format($bank, 2) }}</b><span>In banks and mobile banking</span></div>
    <div class="rep-card black"><b>{{ number_format($receivable['totals']['total'], 2) }}</b><span>Customers owe you</span> <a href="{{ route('accounts.reports.aging') }}">details</a></div>
    <div class="rep-card red"><b>{{ number_format($payable['totals']['total'], 2) }}</b><span>You owe suppliers</span> <a href="{{ route('accounts.reports.aging', ['type' => 'supplier']) }}">details</a></div>
</div>

<div class="rep-cards">
    <div class="rep-card green"><b>{{ number_format($month['totalIncome'], 2) }}</b><span>Income this month (sales, service, other)</span></div>
    <div class="rep-card red"><b>{{ number_format($month['cogs'] + $month['operating'], 2) }}</b><span>Costs this month (goods sold and expenses)</span></div>
    <div class="rep-card {{ $month['net'] >= 0 ? 'green' : 'red' }}"><b class="{{ $month['net'] >= 0 ? 'rep-pos' : 'rep-neg' }}">{{ number_format($month['net'], 2) }}</b><span>{{ $month['net'] >= 0 ? 'Net profit' : 'Net loss' }} this month</span> <a href="{{ route('accounts.reports.profit-loss') }}">details</a></div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-university"></i> Balances now</h4></div></div>
            <div class="panel-body">
                <table class="table table-bordered table-striped rep-table">
                    <thead><tr><th>Account</th><th class="num">Balance</th></tr></thead>
                    <tbody>
                        @foreach($accounts as $a)
                            <tr>
                                <td>@if($a->is_cash)<i class="fa fa-money"></i> @endif<a href="{{ route('accounts.reports.cash-book', ['account' => $a->id]) }}">{{ $a->label }}</a></td>
                                <td class="num {{ $a->closing < 0 ? 'rep-neg' : '' }}">{{ number_format($a->closing, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="rep-grand"><td>Total</td><td class="num">{{ number_format($cash + $bank, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-hourglass-half"></i> Dues by age</h4></div></div>
            <div class="panel-body">
                <table class="table table-bordered rep-table">
                    <thead><tr><th></th><th class="num">0-30 days</th><th class="num">31-60</th><th class="num">61-90</th><th class="num">Over 90</th><th class="num">Total</th></tr></thead>
                    <tbody>
                        @foreach(['Customers owe you' => $receivable['totals'], 'You owe suppliers' => $payable['totals']] as $label => $t)
                            <tr>
                                <th>{{ $label }}</th>
                                <td class="num">{{ number_format($t['b0'], 2) }}</td>
                                <td class="num">{{ number_format($t['b30'], 2) }}</td>
                                <td class="num">{{ number_format($t['b60'], 2) }}</td>
                                <td class="num {{ $t['b90'] > 0 ? 'rep-neg' : '' }}">{{ number_format($t['b90'], 2) }}</td>
                                <td class="num"><b>{{ number_format($t['total'], 2) }}</b></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="rep-note">Over 90 days is money to chase first.</p>
            </div>
        </div>

        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-exchange"></i> Today's money in and out</h4></div></div>
            <div class="panel-body">
                @if($todayMoves->isEmpty())
                    <p class="rep-empty">No money moved today yet.</p>
                @else
                    <table class="table table-bordered table-striped rep-table">
                        <thead><tr><th>What</th><th>Account</th><th class="num">In</th><th class="num">Out</th></tr></thead>
                        <tbody>
                            @foreach($todayMoves->take(8) as $m)
                                <tr><td>{{ $m->type_label }}{{ $m->party ? ' - ' . $m->party : '' }}</td><td>{{ $m->account }}</td>
                                    <td class="num">{{ $m->in ? number_format($m->in, 2) : '' }}</td><td class="num">{{ $m->out ? number_format($m->out, 2) : '' }}</td></tr>
                            @endforeach
                            <tr class="rep-sub-total"><td colspan="2">Total today ({{ $todayMoves->count() }})</td><td class="num">{{ number_format($todayMoves->sum('in'), 2) }}</td><td class="num">{{ number_format($todayMoves->sum('out'), 2) }}</td></tr>
                        </tbody>
                    </table>
                    <p class="rep-note"><a href="{{ route('accounts.reports.day-book') }}">Open the Day Book</a> for every line.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

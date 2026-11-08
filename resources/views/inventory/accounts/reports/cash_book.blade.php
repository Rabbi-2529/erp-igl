@extends('layouts.master')
@section('title', 'Cash & Bank Book')
@section('accounts_class', 'active')
@section('accounts_reports_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['printTitle' => 'Cash & Bank Book', 'printInfo' => ['Account' => $account ? $account->label : 'All accounts', 'Period' => $from->format('d M Y') . ' to ' . $to->format('d M Y')]])

<div class="rep-head">
    <div>
        <h3>Cash &amp; Bank Book</h3>
        <p class="rep-sub">{{ $account ? $account->label : 'All accounts' }}: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div>
            <label for="account">Account</label>
            <select class="form-control dont-select-me" id="account" name="account" style="min-width: 220px;">
                <option value="">All accounts (summary)</option>
                @foreach($summary as $a)
                    <option value="{{ $a->id }}" @selected($account && $account->id == $a->id)>{{ $a->is_cash ? 'Cash: ' : '' }}{{ $a->label }}</option>
                @endforeach
            </select>
        </div>
        <div><label for="from">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ $from->toDateString() }}" autocomplete="off"></div>
        <div><label for="to">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ $to->toDateString() }}" autocomplete="off"></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

@if(!$account)
    <div class="panel panel-bd">
        <div class="panel-body">
            <table class="table table-bordered table-striped rep-table">
                <thead><tr><th>Account</th><th class="num">Opening</th><th class="num">Money in</th><th class="num">Money out</th><th class="num">Closing</th></tr></thead>
                <tbody>
                    @foreach([['Cash', $summary->where('is_cash', true)], ['Banks and mobile banking', $summary->where('is_cash', false)]] as [$groupName, $group])
                        @if($group->count())
                            <tr class="rep-group"><th colspan="5">{{ $groupName }}</th></tr>
                            @foreach($group as $a)
                                <tr>
                                    <td class="rep-indent"><a href="{{ request()->fullUrlWithQuery(['account' => $a->id]) }}">{{ $a->label }}</a></td>
                                    <td class="num">{{ number_format($a->opening, 2) }}</td>
                                    <td class="num rep-pos">{{ number_format($a->in, 2) }}</td>
                                    <td class="num rep-neg">{{ number_format($a->out, 2) }}</td>
                                    <td class="num {{ $a->closing < 0 ? 'rep-neg' : '' }}"><b>{{ number_format($a->closing, 2) }}</b></td>
                                </tr>
                            @endforeach
                            <tr class="rep-sub-total">
                                <td>{{ $groupName }} total</td>
                                <td class="num">{{ number_format($group->sum('opening'), 2) }}</td>
                                <td class="num">{{ number_format($group->sum('in'), 2) }}</td>
                                <td class="num">{{ number_format($group->sum('out'), 2) }}</td>
                                <td class="num">{{ number_format($group->sum('closing'), 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="rep-grand">
                        <td>All accounts</td>
                        <td class="num">{{ number_format($summary->sum('opening'), 2) }}</td>
                        <td class="num">{{ number_format($summary->sum('in'), 2) }}</td>
                        <td class="num">{{ number_format($summary->sum('out'), 2) }}</td>
                        <td class="num">{{ number_format($summary->sum('closing'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
            <p class="rep-note">Click an account to see every line with a running balance. Transfers between your own accounts show as money out of one and money in to the other.</p>
        </div>
    </div>
@else
    <div class="rep-cards">
        <div class="rep-card black"><b>{{ number_format($account->opening, 2) }}</b><span>Opening on {{ $from->format('d M Y') }}</span></div>
        <div class="rep-card green"><b>{{ number_format($account->in, 2) }}</b><span>Money in</span></div>
        <div class="rep-card red"><b>{{ number_format($account->out, 2) }}</b><span>Money out</span></div>
        <div class="rep-card blue"><b>{{ number_format($account->closing, 2) }}</b><span>Closing on {{ $to->format('d M Y') }}</span></div>
    </div>
    <div class="panel panel-bd">
        <div class="panel-body">
            <table class="table table-bordered table-striped rep-table">
                <thead><tr><th>Date</th><th>Voucher</th><th>What</th><th>Customer / Supplier</th><th>Note</th><th class="num">In</th><th class="num">Out</th><th class="num">Balance</th></tr></thead>
                <tbody>
                    <tr class="rep-sub-total"><td colspan="7">Opening balance</td><td class="num">{{ number_format($account->opening, 2) }}</td></tr>
                    @forelse($rows as $m)
                        <tr>
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($m->inv_abs_transaction_date)->format('d M Y') }}</td>
                            <td>{{ $m->inv_abs_voucher_no }}</td>
                            <td>{{ $m->type_label }}</td>
                            <td>{{ $m->party }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($m->inv_abs_description, 50) }}</td>
                            <td class="num rep-pos">{{ $m->in ? number_format($m->in, 2) : '' }}</td>
                            <td class="num rep-neg">{{ $m->out ? number_format($m->out, 2) : '' }}</td>
                            <td class="num {{ $m->balance < 0 ? 'rep-neg' : '' }}">{{ number_format($m->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="rep-empty">No money moved in this period.</td></tr>
                    @endforelse
                    <tr class="rep-grand"><td colspan="5">Closing balance</td><td class="num">{{ number_format($account->in, 2) }}</td><td class="num">{{ number_format($account->out, 2) }}</td><td class="num">{{ number_format($account->closing, 2) }}</td></tr>
                </tbody>
            </table>
            <p class="rep-note"><a href="{{ request()->fullUrlWithQuery(['account' => null]) }}">Back to all accounts</a></p>
        </div>
    </div>
@endif
@endsection

@extends('layouts.master')
@section('title', 'Trial Balance')
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Trial Balance', 'printInfo' => ['As of' => $asOf->format('d M Y')]])
@include('inventory.accounts.journal._nav')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $types = \App\Services\AccountsJournal::TYPES;
    $balanced = abs($tb['dr'] - $tb['cr']) < 0.005;
@endphp

<div class="rep-head">
    <div>
        <h3>Trial Balance</h3>
        <p class="rep-sub">Every account's balance on {{ $asOf->format('d M Y') }}. Debits equal credits when the books are in balance.</p>
    </div>
    <form method="GET" class="rep-filter">
        <div><label for="as_of">As of</label><input type="text" class="form-control datepicker" id="as_of" name="as_of" value="{{ $asOf->toDateString() }}" autocomplete="off"></div>
        <label class="rep-check"><input type="checkbox" name="all" value="1" {{ $showZero ? 'checked' : '' }}> Show accounts with no balance</label>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="rep-cards">
    <div class="rep-card green"><b>{{ $money($tb['dr']) }}</b><span>Total debits</span></div>
    <div class="rep-card red"><b>{{ $money($tb['cr']) }}</b><span>Total credits</span></div>
    <div class="rep-card {{ $balanced ? 'green' : 'red' }}"><b class="{{ $balanced ? 'rep-pos' : 'rep-neg' }}">{{ $balanced ? 'Balanced' : $money($tb['dr'] - $tb['cr']) }}</b><span>{{ $balanced ? 'Debits equal credits' : 'Difference' }}</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <table class="table table-bordered rep-table">
            <thead><tr><th style="width: 110px;">Code</th><th>Account</th><th class="num" style="width: 160px;">Debit</th><th class="num" style="width: 160px;">Credit</th></tr></thead>
            <tbody>
                @foreach($types as $type => $label)
                    @php
                        $inType = array_values(array_filter($tb['rows'], function ($r) use ($type) { return $r->account->type === $type; }));
                    @endphp
                    @if(count($inType))
                        <tr class="rep-group"><th colspan="4">{{ $label }}</th></tr>
                        @foreach($inType as $r)
                            <tr @if($r->group) style="font-weight: 700;" @endif>
                                <td style="white-space: nowrap;">{{ $r->account->code }}</td>
                                <td style="padding-left: {{ 8 + $r->depth * 22 }}px !important;">
                                    @if($r->group && abs($r->own) < 0.005)
                                        {{ $r->account->name }} <small class="text-muted">({{ $money(abs($r->total)) }} {{ $r->total >= 0 ? 'Dr' : 'Cr' }})</small>
                                    @else
                                        <a href="{{ route('accounts.journal.ledger', ['account' => $r->account->key, 'from' => $asOf->copy()->startOfMonth()->toDateString(), 'to' => $asOf->toDateString()]) }}">{{ $r->account->name }}</a>
                                    @endif
                                </td>
                                <td class="num">{{ $r->dr > 0 ? $money($r->dr) : '' }}</td>
                                <td class="num">{{ $r->cr > 0 ? $money($r->cr) : '' }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
                <tr class="rep-grand"><td colspan="2">Total</td><td class="num">{{ $money($tb['dr']) }}</td><td class="num">{{ $money($tb['cr']) }}</td></tr>
            </tbody>
        </table>
        <p class="rep-note">
            Income and expenses are counted from the first entry up to this date (year closing comes in the next phase).
            The "to match" accounts should be zero; a balance there means a payment is in the customer / supplier ledger but not in bank or cash, or the other way round.
            Click an account to see its entries.
        </p>
    </div>
</div>
@endsection

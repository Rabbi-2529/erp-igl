@extends('layouts.master')
@section('title', 'Account Ledger')
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Account Ledger',
    'printInfo' => ['Account' => $account ? trim($account->code . ' ' . $account->name) : '-', 'Period' => $from->format('d M Y') . ' to ' . $to->format('d M Y')]])
@include('inventory.accounts.journal._nav')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $types = \App\Services\AccountsJournal::TYPES;
    $debitSide = $account && in_array($account->type, \App\Services\AccountsJournal::DEBIT_NORMAL, true);
@endphp

<div class="rep-head">
    <div>
        <h3>Account Ledger</h3>
        <p class="rep-sub">{{ $account ? trim($account->code . ' ' . $account->name) . ' (' . ($types[$account->type] ?? '') . ')' : 'Choose an account' }}: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div>
            <label for="account">Account</label>
            <select class="form-control" id="account" name="account" style="min-width: 300px;">
                <option value="">Choose an account</option>
                @foreach($types as $type => $label)
                    <optgroup label="{{ $label }}">
                        @foreach($choices as [$a, $depth])
                            @if($a->type === $type)
                                <option value="{{ $a->key }}" @selected($account && $account->key === $a->key)>{{ str_repeat('- ', $depth) }}{{ trim($a->code . ' ' . $a->name) }}</option>
                            @endif
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div><label for="from">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ $from->toDateString() }}" autocomplete="off"></div>
        <div><label for="to">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ $to->toDateString() }}" autocomplete="off"></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

@if($account)
<div class="rep-cards">
    <div class="rep-card black"><b>{{ $money($opening) }}</b><span>Opening balance ({{ $from->format('d M Y') }})</span></div>
    <div class="rep-card green"><b>{{ $money($rows->sum('dr')) }}</b><span>Debits</span></div>
    <div class="rep-card red"><b>{{ $money($rows->sum('cr')) }}</b><span>Credits</span></div>
    <div class="rep-card blue"><b>{{ $money($rows->count() ? $rows->last()->balance : $opening) }}</b><span>Closing balance ({{ $to->format('d M Y') }})</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <table class="table table-bordered table-striped rep-table">
            <thead><tr><th style="width: 100px;">Date</th><th>What</th><th>Against</th><th>Ref</th><th class="num">Debit</th><th class="num">Credit</th><th class="num">Balance</th></tr></thead>
            <tbody>
                <tr class="rep-sub-total"><td colspan="6">Opening balance</td><td class="num">{{ $money($opening) }}</td></tr>
                @forelse($rows as $r)
                    <tr>
                        <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->entry->date)->format('d M Y') }}</td>
                        <td>{{ $r->entry->what }}@if($r->entry->party)<br><small class="text-muted">{{ $r->entry->party }}</small>@endif
                            @if($r->entry->narr)<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($r->entry->narr, 70) }}</small>@endif</td>
                        <td>{{ \Illuminate\Support\Str::limit($r->against, 60) }}</td>
                        <td>@if($r->entry->kind === 'JV' && preg_match('/^j(\d+)$/', $r->entry->eid, $m))<a href="{{ route('accounts.journal.vouchers.show', $m[1]) }}">{{ $r->entry->ref }}</a>@else{{ $r->entry->ref }}@endif</td>
                        <td class="num">{{ $r->dr > 0 ? $money($r->dr) : '' }}</td>
                        <td class="num">{{ $r->cr > 0 ? $money($r->cr) : '' }}</td>
                        <td class="num {{ $r->balance < 0 ? 'rep-neg' : '' }}">{{ $money($r->balance) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="rep-empty">No entries in this period.</td></tr>
                @endforelse
                <tr class="rep-grand"><td colspan="4">Closing balance</td><td class="num">{{ $money($rows->sum('dr')) }}</td><td class="num">{{ $money($rows->sum('cr')) }}</td>
                    <td class="num">{{ $money($rows->count() ? $rows->last()->balance : $opening) }}</td></tr>
            </tbody>
        </table>
        <p class="rep-note">
            The balance is shown as {{ $debitSide ? 'debits less credits (assets and expenses)' : 'credits less debits (liabilities, equity and income)' }}.
            @if($account->parent === null && !$account->dynamic && $rows->isEmpty()) If this is a group, open the accounts under it. @endif
            Up to one year at a time.
        </p>
    </div>
</div>
@else
    <div class="panel panel-bd"><div class="panel-body"><p class="rep-empty">Choose an account to see its entries.</p></div></div>
@endif
@endsection

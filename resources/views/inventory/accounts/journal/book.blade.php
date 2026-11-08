@extends('layouts.master')
@section('title', 'Journal Book')
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Journal Book',
    'printInfo' => ['Period' => $from->format('d M Y') . ' to ' . $to->format('d M Y'), 'Showing' => $sources[$source] . ' (' . $entries->count() . ' entries)']])
@include('inventory.accounts.journal._nav')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $allLines = $entries->pluck('lines')->flatten(1);
@endphp
<style>
    .jb-entry td { vertical-align: top; }
    .jb-entry .jb-first td { border-top: 2px solid var(--erp-border) !important; }
    .jb-cr td.jb-acc { padding-left: 34px !important; }
    @media print { .jb-entry .jb-first td { border-top: 1px solid #1a1a1a !important; } }
</style>

<div class="rep-head">
    <div>
        <h3>Journal Book</h3>
        <p class="rep-sub">Every entry in double entry, day by day: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div><label for="from">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ $from->toDateString() }}" autocomplete="off"></div>
        <div><label for="to">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ $to->toDateString() }}" autocomplete="off"></div>
        <div>
            <label for="source">Show</label>
            <select class="form-control dont-select-me" id="source" name="source">
                @foreach($sources as $k => $label)<option value="{{ $k }}" @selected($source === $k)>{{ $label }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        @if($entries->isEmpty())
            <p class="rep-empty">No entries in this period.</p>
        @else
            <table class="table table-bordered rep-table">
                <thead><tr><th style="width: 100px;">Date</th><th>Particulars</th><th style="width: 90px;">Code</th><th class="num" style="width: 130px;">Debit</th><th class="num" style="width: 130px;">Credit</th></tr></thead>
                @foreach($entries as $e)
                    <tbody class="jb-entry">
                        @foreach($e->lines as $l)
                            <tr class="{{ $loop->first ? 'jb-first' : '' }} {{ $l->cr > 0 ? 'jb-cr' : '' }}">
                                @if($loop->first)
                                    <td rowspan="{{ $e->lines->count() + 1 }}" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($e->date)->format('d M Y') }}</td>
                                @endif
                                <td class="jb-acc">
                                    <a href="{{ route('accounts.journal.ledger', ['account' => $l->key, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}">{{ $l->cr > 0 ? 'To ' : '' }}{{ $l->name }}</a>
                                </td>
                                <td>{{ $l->code }}</td>
                                <td class="num">{{ $l->dr > 0 ? $money($l->dr) : '' }}</td>
                                <td class="num">{{ $l->cr > 0 ? $money($l->cr) : '' }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" class="text-muted" style="font-size: 12px;">
                                <b>{{ $e->what }}</b>@if($e->party) &middot; {{ $e->party }}@endif @if($e->ref) &middot; {{ $e->ref }}@endif
                                @if($e->narr) &middot; {{ \Illuminate\Support\Str::limit($e->narr, 90) }}@endif
                            </td>
                        </tr>
                    </tbody>
                @endforeach
                <tbody>
                    <tr class="rep-grand"><td colspan="3">Total ({{ $entries->count() }} entries)</td><td class="num">{{ $money($allLines->sum('dr')) }}</td><td class="num">{{ $money($allLines->sum('cr')) }}</td></tr>
                </tbody>
            </table>
        @endif
        <p class="rep-note">
            Entries come from where they were made: bank and cash movements, sales and purchase invoices, customer and supplier ledgers,
            the cost of each sale, payroll, fixed assets and journal vouchers. Debits are listed first, then credits ("To ...").
            Click an account for its ledger. Up to 31 days at a time.
        </p>
    </div>
</div>
@endsection

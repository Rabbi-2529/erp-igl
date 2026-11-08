@extends('layouts.master')
@section('title', 'Journal Vouchers')
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Journal Vouchers', 'printInfo' => ['Period' => $from->format('d M Y') . ' to ' . $to->format('d M Y'), 'Showing' => ucfirst($status === 'all' ? 'all' : $status) . ' vouchers (' . $list->count() . ')']])
@include('inventory.accounts.journal._nav')
@include('lc._alerts')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
@endphp

<div class="rep-head">
    <div>
        <h3>Journal Vouchers</h3>
        <p class="rep-sub">Entries made by hand: adjustments, accruals, opening balances, owner's money, corrections. Debits always equal credits.</p>
    </div>
    <div>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <a href="{{ route('accounts.journal.vouchers.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> New journal voucher</a>
    </div>
</div>

@if($pending)
    <div class="alert alert-warning rep-noprint">
        <b>{{ $pending }} journal voucher{{ $pending > 1 ? 's' : '' }} waiting for approval.</b>
        They do not count in the books until {{ $isAdmin ? 'you approve them' : 'a company admin approves them' }}.
        <a href="{{ route('accounts.journal.vouchers', ['status' => 'pending', 'from' => '2000-01-01', 'to' => now()->toDateString()]) }}">Show them</a>
    </div>
@endif

<div class="panel panel-bd">
    <div class="panel-body">
        <form method="GET" class="rep-filter" style="margin-bottom: 12px;">
            <div><label for="from">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ $from->toDateString() }}" autocomplete="off"></div>
            <div><label for="to">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ $to->toDateString() }}" autocomplete="off"></div>
            <div>
                <label for="status">Status</label>
                <select class="form-control dont-select-me" id="status" name="status">
                    @foreach(['posted' => 'Posted', 'pending' => 'Waiting for approval', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'all' => 'All'] as $k => $v)<option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        </form>

        @if($list->isEmpty())
            <p class="rep-empty">No journal vouchers in this period. <a href="{{ route('accounts.journal.vouchers.create') }}">Make the first one</a>.</p>
        @else
            <table class="table table-bordered table-striped rep-table">
                <thead><tr><th>Date</th><th>Voucher</th><th>Narration</th><th>Accounts</th><th class="num">Amount</th><th>Status</th><th>By</th></tr></thead>
                <tbody>
                    @foreach($list as $v)
                        <tr>
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($v->aj_date)->format('d M Y') }}</td>
                            <td style="white-space: nowrap;"><a href="{{ route('accounts.journal.vouchers.show', $v->aj_id) }}">{{ $v->aj_voucher_no }}</a></td>
                            <td>{{ $v->aj_narration }}</td>
                            <td>
                                @foreach(($lines[$v->aj_id] ?? collect()) as $l)
                                    <div>{{ $l->ajl_debit > 0 ? 'Dr' : 'Cr' }} {{ $l->ajl_account_name }} <span class="text-muted">{{ $money($l->ajl_debit > 0 ? $l->ajl_debit : $l->ajl_credit) }}</span></div>
                                @endforeach
                            </td>
                            <td class="num">{{ $money($v->aj_total) }}</td>
                            <td>
                                @if($v->aj_status === 'posted')<span class="label label-success">Posted</span>
                                @elseif($v->aj_status === 'pending')<span class="label label-warning">Waiting for approval</span>
                                @elseif($v->aj_status === 'rejected')<span class="label label-danger">Rejected</span>
                                @else<span class="label label-default">Cancelled</span>@endif
                            </td>
                            <td>{{ $v->au_name }}</td>
                        </tr>
                    @endforeach
                    <tr class="rep-grand"><td colspan="4">Total ({{ $list->count() }})</td><td class="num">{{ $money($list->sum('aj_total')) }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        @endif
        <p class="rep-note">A saved voucher is never edited or deleted, so the books keep their history: cancel it with a reason and enter a new one.
            Only posted vouchers count in the books.</p>
    </div>
</div>
@endsection

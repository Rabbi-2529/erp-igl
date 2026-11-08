@extends('layouts.master')
@section('title', 'Journal Voucher ' . $v->aj_voucher_no)
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Journal Voucher', 'printPreparedBy' => $v->created_name,
    'printInfo' => ['Voucher no' => $v->aj_voucher_no, 'Date' => \Carbon\Carbon::parse($v->aj_date)->format('d M Y'),
        'Status' => ['posted' => 'Posted', 'pending' => 'Waiting for approval', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'][$v->aj_status] ?? $v->aj_status,
        'Approved by' => $approvedBy]])
@include('inventory.accounts.journal._nav')
@include('lc._alerts')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $dr = $lines->sum('ajl_debit');
    $cr = $lines->sum('ajl_credit');
    $words = class_exists('App\Models\NumberConverter') ? \App\Models\NumberConverter::number_to_text($dr) : number_format($dr, 2) . ' Taka only';
@endphp

<div class="rep-head">
    <div>
        <h3>Journal Voucher {{ $v->aj_voucher_no }}</h3>
        <p class="rep-sub">{{ \Carbon\Carbon::parse($v->aj_date)->format('d M Y') }} &middot; made by {{ $v->created_name ?: '-' }} on {{ \Carbon\Carbon::parse($v->aj_created_at)->format('d M Y, h:i A') }}</p>
    </div>
    <div>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <a href="{{ route('accounts.journal.vouchers.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> New voucher</a>
    </div>
</div>

@if($v->aj_status === 'cancelled')
    <div class="alert alert-danger">
        <b>Cancelled</b> by {{ $v->cancelled_name ?: '-' }} on {{ $v->aj_cancelled_at ? \Carbon\Carbon::parse($v->aj_cancelled_at)->format('d M Y, h:i A') : '' }}:
        {{ $v->aj_cancel_reason }}. It does not count in the books.
    </div>
    <p class="rep-print-only" style="font-weight: bold; color: #c0392b;">CANCELLED: {{ $v->aj_cancel_reason }}</p>
@elseif($v->aj_status === 'rejected')
    <div class="alert alert-danger"><b>Rejected:</b> {{ $v->aj_cancel_reason }}. It does not count in the books.</div>
    <p class="rep-print-only" style="font-weight: bold; color: #c0392b;">REJECTED: {{ $v->aj_cancel_reason }}</p>
@elseif($v->aj_status === 'pending')
    <div class="alert alert-warning"><b>Waiting for approval.</b> It does not count in the books until a company admin approves it.</div>
    <p class="rep-print-only" style="font-weight: bold; color: #c0392b;">NOT APPROVED YET</p>
@endif

<div class="panel panel-bd">
    <div class="panel-body">
        <p style="margin-bottom: 10px;"><b>Narration:</b> {{ $v->aj_narration }}</p>
        <table class="table table-bordered rep-table">
            <thead><tr><th style="width: 40px;">#</th><th style="width: 110px;">Code</th><th>Account</th><th>Note</th><th class="num" style="width: 140px;">Debit</th><th class="num" style="width: 140px;">Credit</th></tr></thead>
            <tbody>
                @foreach($lines as $l)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $l->code }}</td>
                        <td>{{ $l->ajl_account_name }}</td>
                        <td>{{ $l->ajl_note }}</td>
                        <td class="num">{{ $l->ajl_debit > 0 ? $money($l->ajl_debit) : '' }}</td>
                        <td class="num">{{ $l->ajl_credit > 0 ? $money($l->ajl_credit) : '' }}</td>
                    </tr>
                @endforeach
                <tr class="rep-grand"><td colspan="4">Total</td><td class="num">{{ $money($dr) }}</td><td class="num">{{ $money($cr) }}</td></tr>
            </tbody>
        </table>
        <p class="rep-note"><b>Amount in words:</b> {{ $words }}</p>
    </div>
</div>

@if($v->aj_status === 'pending' && $isAdmin)
    <div class="panel panel-bd rep-noprint">
        <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-check-square-o"></i> Approve or reject</h4></div></div>
        <div class="panel-body">
            <div class="rep-filter">
                <form method="POST" action="{{ route('accounts.journal.vouchers.approve', $v->aj_id) }}" onsubmit="return confirm('Approve journal voucher {{ $v->aj_voucher_no }}? It will count in the books.');">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Approve</button>
                </form>
                <form method="POST" action="{{ route('accounts.journal.vouchers.reject', $v->aj_id) }}" class="rep-filter" style="flex: 1 1 320px;">
                    @csrf
                    <div style="flex: 1 1 240px;"><label for="reject_reason">Reason to reject</label><input type="text" class="form-control" id="reject_reason" name="reason" maxlength="255" required></div>
                    <button type="submit" class="btn btn-danger"><i class="fa fa-times"></i> Reject</button>
                </form>
            </div>
        </div>
    </div>
@endif

@if($history->count())
    <div class="panel panel-bd rep-noprint">
        <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-history"></i> Approval history</h4></div></div>
        <div class="panel-body">
            <table class="table table-bordered rep-table">
                <thead><tr><th>When</th><th>Who</th><th>What</th><th>Reason</th></tr></thead>
                <tbody>
                    @foreach($history as $h)
                        <tr><td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($h->aja_created_at)->format('d M Y, h:i A') }}</td><td>{{ $h->au_name }}</td><td>{{ ucfirst($h->aja_action) }}</td><td>{{ $h->aja_reason }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if(in_array($v->aj_status, ['posted', 'pending'], true))
    <div class="panel panel-bd rep-noprint">
        <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-ban"></i> Cancel this voucher</h4></div></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('accounts.journal.vouchers.cancel', $v->aj_id) }}" class="rep-filter" onsubmit="return confirm('Cancel journal voucher {{ $v->aj_voucher_no }}? It will no longer count in the books.');">
                @csrf
                <div style="flex: 1 1 320px;"><label for="reason">Reason</label><input type="text" class="form-control" id="reason" name="reason" maxlength="255" required placeholder="e.g. Wrong amount, entered again as JV-..."></div>
                <button type="submit" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel voucher</button>
            </form>
            <p class="rep-note">Vouchers are never edited or deleted. A cancelled voucher stays on record with its reason.</p>
        </div>
    </div>
@endif
@endsection

@extends('layouts.master')
@section('title', 'Fiscal Years')
@section('accounts_class', 'active')
@section('accounts_control_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Fiscal Years', 'printInfo' => ['Books closed up to' => $lockedUntil ? $lockedUntil->format('d M Y') : 'not closed']])
@include('inventory.accounts.journal._nav', ['navGroup' => 'control'])
@include('lc._alerts')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
@endphp

<div class="rep-head">
    <div>
        <h3>Fiscal Years</h3>
        <p class="rep-sub">1 July to 30 June. Closing a year moves its profit to retained earnings and locks the books up to its last day.</p>
    </div>
    <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
</div>

@foreach($changes as $c)
    <div class="alert alert-danger">
        <b>Fiscal year {{ $c->label }} changed after it was closed:</b> profit was {{ $money($c->closed) }} when closed, it is {{ $money($c->now) }} now
        (difference {{ $money($c->now - $c->closed) }}). An entry dated in that year was added or changed on another page. Retained earnings show the new figure; check the year, or reopen and close it again.
    </div>
@endforeach

<div class="panel panel-bd">
    <div class="panel-body">
        <table class="table table-bordered table-striped rep-table">
            <thead><tr><th>Fiscal year</th><th>Period</th><th class="num">Profit / loss</th><th>Status</th><th class="rep-noprint" style="width: 330px;"></th></tr></thead>
            <tbody>
                @foreach($years as $y)
                    <tr>
                        <td><b>{{ $y->label }}</b></td>
                        <td style="white-space: nowrap;">{{ $y->start->format('d M Y') }} to {{ $y->end->format('d M Y') }}</td>
                        <td class="num {{ $y->profit < 0 ? 'rep-neg' : '' }}">{{ $money($y->profit) }}</td>
                        <td>
                            @if($y->closed)
                                <span class="label label-success">Closed</span><br><small class="text-muted">by {{ $y->rec->closed_name ?: '-' }}, {{ \Carbon\Carbon::parse($y->rec->afy_closed_at)->format('d M Y') }}@if($y->rec->afy_note) &middot; {{ $y->rec->afy_note }}@endif</small>
                            @elseif($y->rec && $y->rec->afy_status === 'reopened')
                                <span class="label label-warning">Reopened</span><br><small class="text-muted">by {{ $y->rec->reopened_name ?: '-' }}: {{ $y->rec->afy_reopen_reason }}</small>
                            @elseif(!$y->ended)
                                <span class="label label-default">Running</span>
                            @else
                                <span class="label label-default">Open</span>
                            @endif
                        </td>
                        <td class="rep-noprint">
                            @if($isAdmin && $y->canClose)
                                <form method="POST" action="{{ route('accounts.control.fiscal-years.close') }}" class="rep-filter" onsubmit="return confirm('Close fiscal year {{ $y->label }}? Its profit moves to retained earnings and the books are locked up to {{ $y->end->format('d M Y') }}.');">
                                    @csrf
                                    <input type="hidden" name="start" value="{{ $y->start->toDateString() }}">
                                    <input type="text" class="form-control input-sm" name="note" maxlength="255" placeholder="Note (optional)" aria-label="Note" style="height: 30px; min-width: 150px;">
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-lock"></i> Close year</button>
                                </form>
                            @elseif($isAdmin && $y->canReopen)
                                <form method="POST" action="{{ route('accounts.control.fiscal-years.reopen') }}" class="rep-filter" onsubmit="return confirm('Reopen fiscal year {{ $y->label }}?');">
                                    @csrf
                                    <input type="hidden" name="start" value="{{ $y->start->toDateString() }}">
                                    <input type="text" class="form-control input-sm" name="reason" maxlength="255" placeholder="Reason" required aria-label="Reason" style="height: 30px; min-width: 150px;">
                                    <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-unlock"></i> Reopen</button>
                                </form>
                            @elseif(!$y->ended)
                                <small class="text-muted">Can be closed after {{ $y->end->format('d M Y') }}</small>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="rep-note">
            Years close in order, oldest first, and reopen from the newest. When a year is closed: its income and expenses move to Retained Earnings on the Trial Balance and Balance Sheet,
            and journal vouchers, salary payments, petty cash, cheque clearing and VAT payments dated in it are refused.
            Other pages (sales, purchases, payments) still accept old dates; if they change a closed year, it shows here in red.
            @unless($isAdmin) Only a company admin can close or reopen a year. @endunless
        </p>
    </div>
</div>
@endsection

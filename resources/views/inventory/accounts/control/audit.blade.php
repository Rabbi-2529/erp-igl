@extends('layouts.master')
@section('title', 'Audit Trail')
@section('accounts_class', 'active')
@section('accounts_control_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Audit Trail',
    'printInfo' => ['Period' => $from->format('d M Y') . ' to ' . $to->format('d M Y'), 'User' => $user ? ($users[$user] ?? '-') : 'Everyone']])
@include('inventory.accounts.journal._nav', ['navGroup' => 'control'])
@php
    $money = function ($v) { return number_format((float) $v, 2); };
@endphp

<div class="rep-head">
    <div>
        <h3>Audit Trail</h3>
        <p class="rep-sub">Who did what in Accounts, and when: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div><label for="from">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ $from->toDateString() }}" autocomplete="off"></div>
        <div><label for="to">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ $to->toDateString() }}" autocomplete="off"></div>
        <div>
            <label for="user">User</label>
            <select class="form-control dont-select-me" id="user" name="user">
                <option value="">Everyone</option>
                @foreach($users as $id => $name)<option value="{{ $id }}" @selected($user === (int) $id)>{{ $name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="source">Show</label>
            <select class="form-control dont-select-me" id="source" name="source">
                @foreach(['all' => 'Everything', 'actions' => 'Accounts actions', 'money' => 'Money movements entered'] as $k => $v)<option value="{{ $k }}" @selected($source === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        @if($rows->isEmpty())
            <p class="rep-empty">Nothing recorded in this period.</p>
        @else
            <table class="table table-bordered table-striped rep-table">
                <thead><tr><th style="width: 150px;">When</th><th>Who</th><th>What</th><th>Details</th><th class="num">Amount</th></tr></thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->at)->format('d M Y, h:i A') }}</td>
                            <td>{{ $r->who ?: '-' }}</td>
                            <td>{{ $r->kind === 'action' ? ucwords(str_replace(['.', '_'], ' ', $r->action)) : $r->action }}
                                <br><small class="text-muted">{{ $r->kind === 'action' ? 'Accounts action' : 'Money movement' }}</small></td>
                            <td>{{ \Illuminate\Support\Str::limit($r->summary, 140) }}@if($r->ip)<br><small class="text-muted">IP {{ $r->ip }}</small>@endif</td>
                            <td class="num {{ $r->amount !== null && $r->amount < 0 ? 'rep-neg' : '' }}">{{ $r->amount !== null ? $money($r->amount) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <p class="rep-note">
            Accounts actions: settings, chart of accounts, journal vouchers (made, approved, rejected, cancelled), salary payments, petty cash, cheques,
            bank reconciliation, VAT payments, budgets and year closing. Money movements: every bank, mobile banking and cash entry with the user who entered it.
            Up to three months at a time.
        </p>
    </div>
</div>
@endsection

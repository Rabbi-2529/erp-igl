@extends('layouts.master')
@section('title', $kind === 'supplier' ? 'Supplier Due Aging' : 'Customer Due Aging')
@section('accounts_class', 'active')
@section('accounts_reports_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['printTitle' => ($kind === 'supplier' ? 'Supplier' : 'Customer') . ' Due Aging', 'printInfo' => ['As of' => $asOf->format('d M Y'), 'Rule' => 'Payments set against the oldest bills first']])

<div class="rep-head">
    <div>
        <h3>{{ $kind === 'supplier' ? 'What you owe suppliers' : 'What customers owe you' }}, by age</h3>
        <p class="rep-sub">As of {{ $asOf->format('d M Y') }}. Payments are set against the oldest bills first, so the age is how long the unpaid part has been waiting.</p>
    </div>
    <form method="GET" class="rep-filter">
        <div>
            <label for="type">Show</label>
            <select class="form-control dont-select-me" id="type" name="type">
                <option value="customer" @selected($kind === 'customer')>Customers (receivable)</option>
                <option value="supplier" @selected($kind === 'supplier')>Suppliers (payable)</option>
            </select>
        </div>
        <div><label for="as_of">As of</label><input type="text" class="form-control datepicker" id="as_of" name="as_of" value="{{ $asOf->toDateString() }}" autocomplete="off"></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

@php($t = $data['totals'])
<div class="rep-cards">
    <div class="rep-card green"><b>{{ number_format($t['b0'], 2) }}</b><span>0-30 days</span></div>
    <div class="rep-card blue"><b>{{ number_format($t['b30'], 2) }}</b><span>31-60 days</span></div>
    <div class="rep-card black"><b>{{ number_format($t['b60'], 2) }}</b><span>61-90 days</span></div>
    <div class="rep-card red"><b class="{{ $t['b90'] > 0 ? 'rep-neg' : '' }}">{{ number_format($t['b90'], 2) }}</b><span>Over 90 days</span></div>
    <div class="rep-card black"><b>{{ number_format($t['total'], 2) }}</b><span>Total due ({{ count(array_filter($data['rows'], function ($r) { return $r->total > 0.004; })) }} {{ $kind === 'supplier' ? 'suppliers' : 'customers' }})</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <table class="table table-bordered table-striped rep-table">
            <thead>
                <tr><th>#</th><th>{{ $kind === 'supplier' ? 'Supplier' : 'Customer' }}</th><th>Mobile</th>
                    <th class="num">0-30</th><th class="num">31-60</th><th class="num">61-90</th><th class="num">Over 90</th><th class="num">Total due</th><th class="num">Oldest</th>
                    <th class="num">{{ $kind === 'supplier' ? 'Paid in advance' : 'Advance' }}</th></tr>
            </thead>
            <tbody>
                @forelse($data['rows'] as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            @if($kind === 'supplier')
                                <a href="{{ route('inventory.supplier.accounts.account-statement-details', $p->party) }}">{{ $p->name }}</a>
                            @else
                                <a href="{{ route('customer.accounts.account-statement-details', $p->party) }}">{{ $p->name }}</a>
                            @endif
                        </td>
                        <td>{{ $p->mobile }}</td>
                        <td class="num">{{ $p->b0 ? number_format($p->b0, 2) : '' }}</td>
                        <td class="num">{{ $p->b30 ? number_format($p->b30, 2) : '' }}</td>
                        <td class="num">{{ $p->b60 ? number_format($p->b60, 2) : '' }}</td>
                        <td class="num rep-neg">{{ $p->b90 ? number_format($p->b90, 2) : '' }}</td>
                        <td class="num"><b>{{ number_format($p->total, 2) }}</b></td>
                        <td class="num">{{ $p->oldest_days !== null ? $p->oldest_days . ' days' : '' }}</td>
                        <td class="num">{{ $p->advance > 0.004 ? number_format($p->advance, 2) : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="rep-empty">Nothing is due.</td></tr>
                @endforelse
                @if(count($data['rows']))
                    <tr class="rep-grand"><td colspan="3">Total</td>
                        <td class="num">{{ number_format($t['b0'], 2) }}</td><td class="num">{{ number_format($t['b30'], 2) }}</td>
                        <td class="num">{{ number_format($t['b60'], 2) }}</td><td class="num">{{ number_format($t['b90'], 2) }}</td>
                        <td class="num">{{ number_format($t['total'], 2) }}</td><td></td><td class="num">{{ number_format($t['advance'], 2) }}</td></tr>
                @endif
            </tbody>
        </table>
        <p class="rep-note">"Advance" is money paid beyond the bills so far. Click a name for the full statement.</p>
    </div>
</div>
@endsection

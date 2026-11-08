@extends('layouts.master')
@section('title', 'Day Book')
@section('accounts_class', 'active')
@section('accounts_reports_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['printTitle' => 'Day Book', 'printInfo' => ['Period' => $from->format('d M Y') . ($to->ne($from) ? ' to ' . $to->format('d M Y') : '')]])

<div class="rep-head">
    <div>
        <h3>Day Book</h3>
        <p class="rep-sub">Every money movement and every invoice, day by day: {{ $from->format('d M Y') }}{{ $to->ne($from) ? ' to ' . $to->format('d M Y') : '' }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div><label for="from">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ $from->toDateString() }}" autocomplete="off"></div>
        <div><label for="to">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ $to->toDateString() }}" autocomplete="off"></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="rep-cards">
    <div class="rep-card green"><b>{{ number_format($moves->sum('in'), 2) }}</b><span>Money in</span></div>
    <div class="rep-card red"><b>{{ number_format($moves->sum('out'), 2) }}</b><span>Money out</span></div>
    <div class="rep-card blue"><b>{{ number_format($invoices->where('kind', 'Sale')->sum('amount'), 2) }}</b><span>Sales invoices ({{ $invoices->where('kind', 'Sale')->count() }})</span></div>
    <div class="rep-card black"><b>{{ number_format($invoices->where('kind', 'Purchase')->sum('amount'), 2) }}</b><span>Purchase invoices ({{ $invoices->where('kind', 'Purchase')->count() }})</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        @forelse($dates as $date)
            <div class="rep-day"><i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</div>
            @php($dayMoves = $days->get($date, collect()))
            @php($dayInvoices = $invoiceDays->get($date, collect()))
            @if($dayMoves->count())
                <table class="table table-bordered table-striped rep-table">
                    <thead><tr><th>Voucher</th><th>What</th><th>Customer / Supplier</th><th>Account</th><th>Note</th><th class="num">Money in</th><th class="num">Money out</th></tr></thead>
                    <tbody>
                        @foreach($dayMoves as $m)
                            <tr>
                                <td>{{ $m->inv_abs_voucher_no }}</td>
                                <td>{{ $m->type_label }}</td>
                                <td>{{ $m->party }}</td>
                                <td>{{ $m->account }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($m->inv_abs_description, 60) }}</td>
                                <td class="num rep-pos">{{ $m->in ? number_format($m->in, 2) : '' }}</td>
                                <td class="num rep-neg">{{ $m->out ? number_format($m->out, 2) : '' }}</td>
                            </tr>
                        @endforeach
                        <tr class="rep-sub-total"><td colspan="5">Day total</td><td class="num">{{ number_format($dayMoves->sum('in'), 2) }}</td><td class="num">{{ number_format($dayMoves->sum('out'), 2) }}</td></tr>
                    </tbody>
                </table>
            @endif
            @if($dayInvoices->count())
                <table class="table table-bordered rep-table" style="margin-top: 8px;">
                    <thead><tr><th>Invoice</th><th>Type</th><th>Customer / Supplier</th><th class="num">Amount</th></tr></thead>
                    <tbody>
                        @foreach($dayInvoices as $inv)
                            <tr><td>{{ $inv->invoice }}</td><td>{{ $inv->kind }}</td><td>{{ $inv->party_name }}</td><td class="num">{{ number_format($inv->amount, 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @empty
            <p class="rep-empty">Nothing was recorded in this period.</p>
        @endforelse

        @if($dates->count() > 1)
            <table class="table rep-table" style="margin-top: 16px;">
                <tr class="rep-grand"><td>Total for the period</td><td class="num rep-pos">In {{ number_format($moves->sum('in'), 2) }}</td><td class="num rep-neg">Out {{ number_format($moves->sum('out'), 2) }}</td></tr>
            </table>
        @endif
        <p class="rep-note">Money in and out covers every bank, mobile banking and cash account. Invoices change what customers and suppliers owe; money only moves when they pay. Up to three months at a time.</p>
    </div>
</div>
@endsection

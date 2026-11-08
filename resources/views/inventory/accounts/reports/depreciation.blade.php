@extends('layouts.master')
@section('title', 'Depreciation')
@section('accounts_class', 'active')
@section('accounts_reports_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['printTitle' => 'Depreciation Schedule', 'printInfo' => ['Up to' => $asOf->copy()->endOfMonth()->format('d M Y'), 'Assets' => $assets->count()]])
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $methods = ['straight_line' => 'Straight line', 'declining_balance' => 'Declining balance', 'units_of_production' => 'Units of production'];
    $statuses = [0 => 'Inactive', 1 => 'Active', 2 => 'Under maintenance', 3 => 'Disposed'];
@endphp

<div class="rep-head">
    <div>
        <h3>Depreciation of Fixed Assets</h3>
        <p class="rep-sub">Up to the end of {{ $asOf->format('F Y') }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div><label for="as_of">As of</label><input type="text" class="form-control datepicker" id="as_of" name="as_of" value="{{ $asOf->toDateString() }}" autocomplete="off"></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="rep-cards">
    <div class="rep-card black"><b>{{ $money($totals['price']) }}</b><span>Price of assets in use</span></div>
    <div class="rep-card red"><b>{{ $money($totals['this_year']) }}</b><span>Depreciation in {{ $asOf->year }}</span></div>
    <div class="rep-card blue"><b>{{ $money($totals['monthly']) }}</b><span>Depreciation of {{ $asOf->format('F') }}</span></div>
    <div class="rep-card green"><b>{{ $money($totals['book']) }}</b><span>Value now (price less depreciation)</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        @if($assets->isEmpty())
            <p class="rep-empty">No fixed assets in this branch yet. Add them in Fixed Assets.</p>
        @else
            <table class="table table-bordered table-striped rep-table">
                <thead>
                    <tr><th>Asset</th><th>Category</th><th>Bought</th><th>Method</th><th class="num">Rate / year</th><th class="num">Price</th>
                        <th class="num">{{ $asOf->year }}</th><th class="num">Depreciation so far</th><th class="num">Value now</th></tr>
                </thead>
                <tbody>
                    @foreach($assets as $a)
                        <tr @if($a->disposed) class="text-muted" @endif>
                            <td><a href="{{ route('assets.show', $a->asset_id) }}">{{ $a->asset_name }}</a>
                                @if($a->asset_code)<small class="text-muted">{{ $a->asset_code }}</small>@endif
                                @if((int) $a->asset_status !== 1)<br><small class="text-muted">{{ $statuses[(int) $a->asset_status] ?? '' }}{{ $a->disposed ? ' (left out of the totals)' : '' }}</small>@endif
                            </td>
                            <td>{{ $categories[$a->asset_category_id] ?? '' }}</td>
                            <td style="white-space: nowrap;">{{ $a->asset_purchase_date ? \Carbon\Carbon::parse($a->asset_purchase_date)->format('d M Y') : '' }}</td>
                            <td>{{ $methods[$a->asset_depreciation_method] ?? 'Straight line' }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format((float) $a->asset_depreciation_rate, 2), '0'), '.') }}%</td>
                            <td class="num">{{ $money($a->asset_purchase_price) }}</td>
                            @if($a->calculated)
                                <td class="num">{{ $money($a->this_year) }}</td>
                                <td class="num">{{ $money($a->so_far) }}</td>
                                <td class="num">{{ $money($a->book_value) }}</td>
                            @else
                                <td colspan="3" class="rep-note">Units of production needs usage figures, so it is not worked out here.</td>
                            @endif
                        </tr>
                    @endforeach
                    <tr class="rep-grand">
                        <td colspan="5">Total (assets in use)</td>
                        <td class="num">{{ $money($totals['price']) }}</td>
                        <td class="num">{{ $money($totals['this_year']) }}</td>
                        <td class="num">{{ $money($totals['so_far']) }}</td>
                        <td class="num">{{ $money($totals['book']) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
        <p class="rep-note">
            Straight line: price &times; rate &divide; 12 each month from the month it was bought, until the price is used up.
            Declining balance: what is left &times; rate &divide; 12 each month. Depreciation is a cost but not cash, so it shows in
            Profit &amp; Loss and does not change any bank or cash balance. Disposed assets are left out of the totals.
        </p>
    </div>
</div>
@endsection

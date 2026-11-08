@extends('layouts.master')
@section('title', 'Profit & Loss')
@section('accounts_class', 'active')
@section('accounts_reports_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['printTitle' => 'Profit & Loss', 'printInfo' => ['Period' => $from->format('d M Y') . ' to ' . $to->format('d M Y'), 'Salaries' => $withSalary ? 'Included (from payroll)' : 'Not included']])
@php
    $money = function ($v) { return number_format((float) $v, 2); };
@endphp

<div class="rep-head">
    <div>
        <h3>Profit &amp; Loss</h3>
        <p class="rep-sub">{{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>
    </div>
    <form method="GET" class="rep-filter">
        <div><label for="from">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ $from->toDateString() }}" autocomplete="off"></div>
        <div><label for="to">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ $to->toDateString() }}" autocomplete="off"></div>
        <label class="rep-check"><input type="checkbox" name="salary" value="1" {{ $withSalary ? 'checked' : '' }}> Include payroll salaries</label>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="rep-cards">
    <div class="rep-card green"><b>{{ $money($pl['totalIncome']) }}</b><span>Total income</span></div>
    <div class="rep-card black"><b>{{ $money($pl['gross']) }}</b><span>Gross profit (after cost of goods)</span></div>
    <div class="rep-card red"><b>{{ $money($pl['operating']) }}</b><span>Expenses</span></div>
    <div class="rep-card {{ $pl['net'] >= 0 ? 'green' : 'red' }}"><b class="{{ $pl['net'] >= 0 ? 'rep-pos' : 'rep-neg' }}">{{ $money($pl['net']) }}</b><span>{{ $pl['net'] >= 0 ? 'Net profit' : 'Net loss' }}</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <table class="table rep-table">
            <tbody>
                <tr class="rep-group"><th colspan="2">Income</th></tr>
                <tr><td class="rep-indent">Product sales</td><td class="num">{{ $money($pl['sales']) }}</td></tr>
                @if($pl['returns'])<tr><td class="rep-indent">Less: sales returns</td><td class="num rep-neg">-{{ $money($pl['returns']) }}</td></tr>@endif
                @if($pl['discounts'])<tr><td class="rep-indent">Less: discounts given</td><td class="num rep-neg">-{{ $money($pl['discounts']) }}</td></tr>@endif
                <tr><td class="rep-indent">Service / installation charges</td><td class="num">{{ $money($pl['service']) }}</td></tr>
                @if($pl['delivery'])<tr><td class="rep-indent">Delivery charges</td><td class="num">{{ $money($pl['delivery']) }}</td></tr>@endif
                @if($pl['vatOnSales'])<tr><td class="rep-indent">Less: VAT on sales <small class="text-muted">(inside the prices)</small></td><td class="num rep-neg">-{{ $money($pl['vatOnSales']) }}</td></tr>@endif
                <tr class="rep-sub-total"><td>Sales income</td><td class="num">{{ $money($pl['revenue']) }}</td></tr>

                <tr class="rep-group"><th colspan="2">Cost of goods sold</th></tr>
                <tr><td class="rep-indent">Buying price of the products sold</td><td class="num rep-neg">-{{ $money($pl['cogs']) }}</td></tr>
                <tr class="rep-sub-total"><td>Gross profit</td><td class="num {{ $pl['gross'] < 0 ? 'rep-neg' : '' }}">{{ $money($pl['gross']) }}</td></tr>

                <tr class="rep-group"><th colspan="2">Other income</th></tr>
                <tr><td class="rep-indent">Other income (Ledger cash in)</td><td class="num">{{ $money($pl['otherIncome']) }}</td></tr>
                @if($pl['purchaseDiscount'])<tr><td class="rep-indent">Discount received from suppliers</td><td class="num">{{ $money($pl['purchaseDiscount']) }}</td></tr>@endif
                @if($pl['vatRebate'])<tr><td class="rep-indent">VAT rebate on purchases</td><td class="num">{{ $money($pl['vatRebate']) }}</td></tr>@endif
                @foreach($pl['jvIncome'] as $head => $amount)
                    <tr><td class="rep-indent">{{ $head }} <small class="text-muted">(journal voucher)</small></td><td class="num">{{ $money($amount) }}</td></tr>
                @endforeach

                <tr class="rep-group"><th colspan="2">Expenses</th></tr>
                @foreach($pl['heads'] as $head => $amount)
                    <tr><td class="rep-indent">{{ $head }}</td><td class="num">{{ $money($amount) }}</td></tr>
                @endforeach
                @if($pl['omsBills'])<tr><td class="rep-indent">Office bills (OMS)</td><td class="num">{{ $money($pl['omsBills']) }}</td></tr>@endif
                @if($pl['bankCharges'])<tr><td class="rep-indent">Bank service charges</td><td class="num">{{ $money($pl['bankCharges']) }}</td></tr>@endif
                @if($pl['loanInterest'])<tr><td class="rep-indent">Loan interest</td><td class="num">{{ $money($pl['loanInterest']) }}</td></tr>@endif
                @if($withSalary)<tr><td class="rep-indent">Salaries (from payroll)</td><td class="num">{{ $money($pl['salary']) }}</td></tr>@endif
                @if($pl['depreciation'])<tr><td class="rep-indent">Depreciation of fixed assets <small class="text-muted">(not cash)</small></td><td class="num">{{ $money($pl['depreciation']) }}</td></tr>@endif
                @if(!$pl['heads']->count() && !$pl['omsBills'] && !$pl['bankCharges'] && !$pl['loanInterest'] && !$pl['salary'] && !$pl['depreciation'])
                    <tr><td class="rep-indent rep-note" colspan="2">No expenses in this period.</td></tr>
                @endif
                <tr class="rep-sub-total"><td>Total expenses</td><td class="num">{{ $money($pl['operating']) }}</td></tr>

                <tr class="rep-grand">
                    <td>{{ $pl['net'] >= 0 ? 'Net profit' : 'Net loss' }}</td>
                    <td class="num {{ $pl['net'] >= 0 ? 'rep-pos' : 'rep-neg' }}">{{ $money($pl['net']) }}</td>
                </tr>
            </tbody>
        </table>
        <p class="rep-note">
            Sales are counted on the invoice date (paid or not). The cost of goods sold uses each product's buying price.
            Expenses come from Add Expense, Expense Vouchers, Ledger cash out and journal vouchers. Loan principal, supplier payments and
            transfers between accounts are not costs, so they are left out. Depreciation is worked out from each fixed
            asset's price and rate (see Accounts &gt; Reports &gt; Depreciation).
            @if($withSalary) If you also enter salaries as an expense, untick "Include payroll salaries" so they are not counted twice. @endif
        </p>
    </div>
</div>
@endsection

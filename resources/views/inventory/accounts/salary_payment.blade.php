@extends('layouts.master')
@section('title', 'Salary Payment')
@section('accounts_class', 'active')
@section('expense_class', 'active')
@section('salary_payment_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Salary Sheet', 'printInfo' => ['Month' => $monthName, 'Employees' => $salaries->count()]])
@include('lc._alerts')

<div class="rep-head">
    <div>
        <h3>Salary Payment</h3>
        <p class="rep-sub">Pay approved salaries of {{ $monthName }} from a bank or cash account. Each payment is saved as money out of that account.</p>
    </div>
    <form method="GET" class="rep-filter">
        <div>
            <label for="month">Month</label>
            <select class="form-control dont-select-me" id="month" name="month">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($m === $month)>{{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="year">Year</label>
            <select class="form-control dont-select-me" id="year" name="year">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="rep-cards">
    <div class="rep-card black"><b>{{ number_format($salaries->sum('final_salary'), 2) }}</b><span>Salaries of {{ $monthName }} ({{ $salaries->count() }})</span></div>
    <div class="rep-card green"><b>{{ number_format($salaries->sum('paid'), 2) }}</b><span>Paid</span></div>
    <div class="rep-card red"><b>{{ number_format($salaries->sum('due'), 2) }}</b><span>Still to pay</span></div>
    <div class="rep-card blue"><b>{{ $salaries->where('approved', false)->count() }}</b><span>Not approved yet</span></div>
</div>

@if($salaries->isEmpty())
    <div class="panel panel-bd"><div class="panel-body">
        <p class="rep-empty">No salaries generated for {{ $monthName }}. Generate and approve them first in Payroll &gt; Salary Generate.</p>
    </div></div>
@else
<form method="POST" action="{{ route('accounts.salary-payment.pay') }}" id="salaryForm">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">
    <div class="panel panel-bd">
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped rep-table">
                    <thead>
                        <tr><th class="rep-noprint"><input type="checkbox" id="payAll" title="Pay everyone in full" checked></th><th class="rep-print-only">SL</th><th>Employee</th><th>ID</th><th class="num">Salary</th><th class="num">Paid</th><th class="num">Due</th><th class="rep-noprint" style="width: 150px;">Pay now</th><th class="rep-print-only" style="width: 22%;">Signature</th></tr>
                    </thead>
                    <tbody>
                        @foreach($salaries as $s)
                            <tr>
                                <td class="rep-noprint">@if($s->approved && $s->due > 0)<input type="checkbox" class="pay-tick" data-id="{{ $s->id }}" data-due="{{ $s->due }}" checked>@endif</td>
                                <td class="rep-print-only" style="text-align: center;">{{ $loop->iteration }}</td>
                                <td>{{ $s->au_name }}@if(!$s->approved)<span class="rep-print-only text-muted"> (not approved)</span>@endif</td>
                                <td>{{ $s->au_user_id }}</td>
                                <td class="num">{{ number_format($s->final_salary, 2) }}</td>
                                <td class="num">{{ $s->paid ? number_format($s->paid, 2) : '' }}</td>
                                <td class="num">{{ number_format($s->due, 2) }}</td>
                                <td class="rep-noprint">
                                    @if(!$s->approved)
                                        <span class="text-muted">Not approved</span>
                                    @elseif($s->due <= 0)
                                        <span class="rep-pos"><i class="fa fa-check"></i> Paid</span>
                                    @else
                                        <input type="number" class="form-control input-sm pay-amount" name="amount[{{ $s->id }}]" id="amount{{ $s->id }}" min="0" max="{{ $s->due }}" step="0.01" value="{{ old('amount.' . $s->id, $s->due) }}">
                                    @endif
                                </td>
                                <td class="rep-print-only rep-sign-cell"></td>
                            </tr>
                        @endforeach
                        <tr class="rep-grand rep-noprint"><td colspan="6">Total to pay now</td><td class="num" id="payTotal">0.00</td></tr>
                        <tr class="rep-grand rep-print-only"><td colspan="3">Total ({{ $salaries->count() }})</td><td class="num">{{ number_format($salaries->sum('final_salary'), 2) }}</td>
                            <td class="num">{{ number_format($salaries->sum('paid'), 2) }}</td><td class="num">{{ number_format($salaries->sum('due'), 2) }}</td><td></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="rep-filter" style="margin-top: 14px;">
                <div>
                    <label for="account_id">Pay from</label>
                    <select class="form-control" id="account_id" name="account_id" required style="min-width: 300px;">
                        <option value="">Choose a bank or cash account</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}" @selected(old('account_id') == $a->id)>{{ $a->is_cash ? 'Cash: ' : '' }}{{ $a->label }} (balance {{ number_format($a->closing, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div><label for="pay_date">Payment date</label><input type="text" class="form-control datepicker" id="pay_date" name="pay_date" value="{{ old('pay_date', now()->toDateString()) }}" autocomplete="off" required></div>
                <button type="submit" class="btn btn-success"><i class="fa fa-money"></i> Pay salaries</button>
            </div>
            <p class="rep-note rep-noprint">Only approved salaries can be paid. You can pay part of a salary now and the rest later. Salaries already count as an expense in Profit &amp; Loss (from payroll), so these payments are not counted twice.</p>
        </div>
    </div>
</form>
@endif

@if($payments->count())
    <div class="panel panel-bd">
        <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-history"></i> Payments for {{ $monthName }}</h4></div></div>
        <div class="panel-body">
            <table class="table table-bordered table-striped rep-table">
                <thead><tr><th>Date</th><th>Voucher</th><th>For</th><th>From</th><th class="num">Amount</th></tr></thead>
                <tbody>
                    @foreach($payments as $p)
                        <tr><td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($p->inv_abs_transaction_date)->format('d M Y') }}</td><td>{{ $p->inv_abs_voucher_no }}</td><td>{{ $p->inv_abs_description }}</td>
                            <td>{{ $accountNames[$p->inv_abs_bank_id] ?? ('#' . $p->inv_abs_bank_id) }}</td><td class="num">{{ number_format($p->inv_abs_debit - $p->inv_abs_credit, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@section('custom_script')
<script>
(function ($) {
    function total() {
        var t = 0;
        $('.pay-amount').each(function () { t += parseFloat(this.value) || 0; });
        $('#payTotal').text(t.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }
    $(document).on('change', '.pay-tick', function () {
        var input = $('#amount' + $(this).data('id'));
        input.val(this.checked ? $(this).data('due') : 0);
        total();
    });
    $('#payAll').on('change', function () { $('.pay-tick').prop('checked', this.checked).trigger('change'); });
    $(document).on('input', '.pay-amount', total);
    $('#salaryForm').on('submit', function () {
        var t = $('#payTotal').text();
        return confirm('Pay salaries of ' + t + ' from the chosen account?');
    });
    total();
})(jQuery);
</script>
@endsection

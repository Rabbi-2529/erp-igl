@extends('layouts.master')
@section('title', 'Budgets')
@section('accounts_class', 'active')
@section('accounts_control_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Budget vs Actual',
    'printInfo' => ['Fiscal year' => \App\Services\AccountsJournal::fyLabel($fyStart), 'Actual up to' => $asOf->format('d M Y') . ' (' . $monthsGone . ' of 12 months)']])
@include('inventory.accounts.journal._nav', ['navGroup' => 'control'])
@include('lc._alerts')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $income = collect($rows)->filter(function ($r) { return $r->account->type === 'income' && !$r->group; });
    $expense = collect($rows)->filter(function ($r) { return $r->account->type === 'expense' && !$r->group; });
    $sum = function ($list, $field) { return round($list->sum($field), 2); };
@endphp
<style>
    .bud-input { width: 140px; height: 30px; text-align: right; margin-left: auto; }
    .bud-over { color: #c4161f; font-weight: 700; }
    html[data-theme="dark"] .bud-over { color: #f58b90; }
</style>

<div class="rep-head">
    <div>
        <h3>Budgets</h3>
        <p class="rep-sub">A yearly amount for each income and expense account, against what really happened. Fiscal year {{ \App\Services\AccountsJournal::fyLabel($fyStart) }} ({{ $fyStart->format('d M Y') }} to {{ $fyEnd->format('d M Y') }}).</p>
    </div>
    <form method="GET" class="rep-filter">
        <div>
            <label for="fy">Fiscal year</label>
            <select class="form-control dont-select-me" id="fy" name="fy">
                @foreach($years as $y)<option value="{{ $y->year }}" @selected($y->eq($fyStart))>{{ \App\Services\AccountsJournal::fyLabel($y) }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<div class="rep-cards">
    <div class="rep-card green"><b>{{ $money($sum($income, 'budget')) }}</b><span>Income budget for the year</span></div>
    <div class="rep-card red"><b>{{ $money($sum($expense, 'budget')) }}</b><span>Expense budget for the year</span></div>
    <div class="rep-card blue"><b>{{ $money($sum($income, 'budget') - $sum($expense, 'budget')) }}</b><span>Budgeted profit</span></div>
    <div class="rep-card black"><b class="{{ ($sum($income, 'actual') - $sum($expense, 'actual')) < 0 ? 'rep-neg' : '' }}">{{ $money($sum($income, 'actual') - $sum($expense, 'actual')) }}</b><span>Actual profit so far ({{ $monthsGone }} of 12 months)</span></div>
</div>

<form method="POST" action="{{ route('accounts.control.budgets.save') }}">
    @csrf
    <input type="hidden" name="fy" value="{{ $fyStart->year }}">
    <div class="panel panel-bd">
        <div class="panel-body">
            <table class="table table-bordered rep-table">
                <thead>
                    <tr><th>Account</th><th class="num" style="width: 170px;">Budget for the year</th><th class="num">Budget to date</th><th class="num">Actual to date</th><th class="num">Difference</th><th class="num">Used</th></tr>
                </thead>
                <tbody>
                    @foreach(['income' => 'Income', 'expense' => 'Expenses'] as $type => $title)
                        <tr class="rep-group"><th colspan="6">{{ $title }}</th></tr>
                        @foreach(collect($rows)->filter(function ($r) use ($type) { return $r->account->type === $type; }) as $r)
                            @php
                                // over budget: income below plan, or expenses above plan
                                $bad = $r->budget > 0 && ($type === 'expense' ? $r->actual > $r->toDate + 0.004 : $r->actual < $r->toDate - 0.004);
                            @endphp
                            <tr @if($r->group) style="font-weight: 700;" @endif>
                                <td style="padding-left: {{ 8 + $r->depth * 22 }}px !important;">{{ trim($r->account->code . ' ' . $r->account->name) }}</td>
                                <td class="num">
                                    @if($r->group)
                                    @elseif($isAdmin)
                                        <input type="number" class="form-control bud-input rep-noprint" name="budget[{{ $r->account->key }}]" min="0" step="0.01" value="{{ $r->budget ? $r->budget : '' }}" aria-label="Budget for {{ $r->account->name }}">
                                        <span class="rep-print-only">{{ $r->budget ? $money($r->budget) : '' }}</span>
                                    @else
                                        {{ $r->budget ? $money($r->budget) : '' }}
                                    @endif
                                </td>
                                <td class="num">{{ !$r->group && $r->budget ? $money($r->toDate) : '' }}</td>
                                <td class="num">{{ !$r->group && abs($r->actual) > 0.004 ? $money($r->actual) : '' }}</td>
                                <td class="num {{ $bad ? 'bud-over' : '' }}">{{ !$r->group && $r->budget ? $money($r->diff) : '' }}</td>
                                <td class="num {{ $bad ? 'bud-over' : '' }}">{{ !$r->group && $r->used !== null ? $r->used . '%' : '' }}</td>
                            </tr>
                        @endforeach
                        @php
                            $list = $type === 'income' ? $income : $expense;
                        @endphp
                        <tr class="rep-sub-total"><td>Total {{ strtolower($title) }}</td><td class="num">{{ $money($sum($list, 'budget')) }}</td><td class="num">{{ $money($sum($list, 'toDate')) }}</td>
                            <td class="num">{{ $money($sum($list, 'actual')) }}</td><td class="num">{{ $money($sum($list, 'actual') - $sum($list, 'toDate')) }}</td><td></td></tr>
                    @endforeach
                </tbody>
            </table>
            @if($isAdmin)
                <div style="margin-top: 12px;" class="rep-noprint"><button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save budget</button></div>
            @endif
            <p class="rep-note">
                Budget to date is the yearly budget &times; months gone &divide; 12. Red: an expense above its budget, or income below it.
                Actual comes from the journal (the same figures as Profit &amp; Loss with payroll salaries). Leave a budget empty for none.
                @unless($isAdmin) Only a company admin can change budgets. @endunless
            </p>
        </div>
    </div>
</form>
@endsection

@extends('layouts.master')
@section('title', 'Expense Analysis')
@section('accounts_class', 'active')
@section('accounts_reports_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['printTitle' => 'Expense Analysis', 'printInfo' => ['Year' => $year, 'Total expenses' => number_format($data['total'], 2)]])

<div class="rep-head">
    <div>
        <h3>Expense Analysis {{ $year }}</h3>
        <p class="rep-sub">Every expense head, month by month: {{ number_format($data['total'], 2) }} in total.</p>
    </div>
    <form method="GET" class="rep-filter">
        <div>
            <label for="year">Year</label>
            <select class="form-control dont-select-me" id="year" name="year">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

@if(empty($data['rows']))
    <div class="panel panel-bd"><div class="panel-body"><p class="rep-empty">No expenses in {{ $year }}.</p></div></div>
@else
    <div class="panel panel-bd">
        <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-bar-chart"></i> Where the money went in {{ $year }}</h4></div></div>
        <div class="panel-body">
            <div style="position: relative; height: {{ max(160, count($data['rows']) * 34 + 40) }}px;"><canvas id="expenseChart"></canvas></div>
        </div>
    </div>

    <div class="panel panel-bd">
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped rep-table">
                    <thead>
                        <tr><th>Expense head</th>
                            @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $mName)<th class="num">{{ $mName }}</th>@endforeach
                            <th class="num">Total</th><th class="num">Share</th></tr>
                    </thead>
                    <tbody>
                        @foreach($data['rows'] as $row)
                            <tr>
                                <td>{{ $row->head }}</td>
                                @for($m = 1; $m <= 12; $m++)<td class="num">{{ $row->months[$m] ? number_format($row->months[$m], 0) : '' }}</td>@endfor
                                <td class="num"><b>{{ number_format($row->total, 2) }}</b></td>
                                <td class="num">{{ $data['total'] > 0 ? number_format($row->total / $data['total'] * 100, 1) . '%' : '' }}</td>
                            </tr>
                        @endforeach
                        <tr class="rep-grand"><td>Total</td>
                            @for($m = 1; $m <= 12; $m++)<td class="num">{{ $data['monthTotals'][$m] ? number_format($data['monthTotals'][$m], 0) : '' }}</td>@endfor
                            <td class="num">{{ number_format($data['total'], 2) }}</td><td class="num">100%</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="rep-note">From Add Expense, Expense Vouchers and Ledger cash out, plus bank service charges, office (OMS) bills and loan interest. Months show whole taka; totals show paisa.</p>
        </div>
    </div>
@endif
@endsection

@section('custom_script')
@if(!empty($data['rows']))
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('expenseChart');
    if (!el || !window.Chart) { return; }
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    var rows = @json($chart);
    new Chart(el.getContext('2d'), {
        type: 'horizontalBar',
        data: {
            labels: rows.map(function (r) { return r.head; }),
            datasets: [{ label: 'Spent ({{ $year }})', data: rows.map(function (r) { return r.total; }), backgroundColor: dark ? '#4fb3e8' : '#0b7a3b', borderWidth: 0 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            tooltips: { callbacks: { label: function (item) { return 'TK ' + Number(item.xLabel).toLocaleString('en-US', { maximumFractionDigits: 2 }); } } },
            scales: {
                xAxes: [{ ticks: { beginAtZero: true, callback: function (v) { return Number(v).toLocaleString('en-US'); } }, gridLines: { color: dark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)' } }],
                yAxes: [{ barPercentage: 0.6, gridLines: { display: false } }]
            }
        }
    });
});
</script>
@endif
@endsection

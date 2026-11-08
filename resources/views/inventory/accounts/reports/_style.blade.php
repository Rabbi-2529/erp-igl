{{-- Shared look of the Accounts reports --}}
<style>
    .rep-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
    .rep-head h3 { margin: 0; font-size: 20px; color: var(--erp-text); }
    .rep-head .rep-sub { margin: 2px 0 0; color: var(--erp-muted); }
    .rep-filter { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 8px; }
    .rep-filter label { display: block; margin: 0 0 3px; font-size: 12px; color: var(--erp-muted); font-weight: 700; }
    .rep-filter .form-control { height: 34px; min-width: 130px; }
    .rep-filter .rep-check { align-self: center; margin: 18px 4px 0 0; font-weight: 400; }
    .rep-cards { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
    .rep-card { flex: 1 1 170px; padding: 12px 14px; border: 1px solid var(--erp-border); border-left-width: 4px; border-radius: 6px; background: var(--erp-white); }
    .rep-card b { display: block; font-size: 22px; line-height: 1.15; color: var(--erp-text); }
    .rep-card span { color: var(--erp-muted); font-size: 13px; }
    .rep-card.green { border-left-color: #0b7a3b; }
    .rep-card.red { border-left-color: #c4161f; }
    .rep-card.blue { border-left-color: #4fb3e8; }
    .rep-card.black { border-left-color: #2b2f2c; }
    .rep-card a { font-size: 12px; }
    .rep-table { margin-bottom: 0; }
    .rep-table td.num, .rep-table th.num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .rep-table tr.rep-sub-total td, .rep-table tr.rep-sub-total th { font-weight: 700; background: var(--erp-green-tint); }
    .rep-table tr.rep-grand td, .rep-table tr.rep-grand th { font-weight: 700; font-size: 15px; border-top: 2px solid var(--erp-text) !important; }
    .rep-table tr.rep-group th { background: transparent; color: var(--erp-muted); font-size: 12px; text-transform: uppercase; letter-spacing: .4px; border-bottom: 0 !important; padding-top: 14px !important; }
    .rep-table .rep-indent { padding-left: 24px !important; }
    .rep-pos { color: #0b7a3b; }
    .rep-neg { color: #c4161f; }
    html[data-theme="dark"] .rep-pos { color: #6fd79a; }
    html[data-theme="dark"] .rep-neg { color: #f58b90; }
    .rep-note { color: var(--erp-muted); font-size: 13px; margin: 10px 0 0; }
    /* small grey notes stay readable in every theme (the old style's grey is too light on white) */
    .content .text-muted { color: var(--erp-muted) !important; }
    .rep-table .label-success { background-color: #0b7a3b; }
    .rep-table .label-warning { background-color: #9a5b00; }
    .rep-table .label-default { background-color: #5f6b66; color: #ffffff; }
    html:not([data-theme="dark"]) .content .alert-warning { background: #fff4d6; border-color: #f0c36d; color: #5c4000; }
    html:not([data-theme="dark"]) .content .alert-warning a { color: #5c4000; text-decoration: underline; }
    .rep-day { margin: 18px 0 6px; font-size: 15px; font-weight: 700; color: var(--erp-text); }
    .rep-empty { padding: 26px 0; text-align: center; color: var(--erp-muted); }
    .rep-links { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
    .rep-links a.active { color: var(--erp-on-primary); background: var(--erp-green-btn); border-color: var(--erp-green-btn); }
</style>

{{-- Printed look (same design as the sales invoice) --}}
@include('inventory.accounts.reports._print')

{{-- Links between the reports (left out on the working pages: salary payment, petty cash, cheques, reconciliation) --}}
@if($reportLinks ?? true)
<div class="rep-links rep-noprint">
    @foreach([
        ['accounts.reports.dashboard', 'fa-tachometer', 'Dashboard'],
        ['accounts.reports.profit-loss', 'fa-line-chart', 'Profit & Loss'],
        ['accounts.reports.day-book', 'fa-book', 'Day Book'],
        ['accounts.reports.cash-book', 'fa-university', 'Cash & Bank Book'],
        ['accounts.reports.aging', 'fa-hourglass-half', 'Due Aging'],
        ['accounts.reports.expenses', 'fa-pie-chart', 'Expense Analysis'],
        ['accounts.reports.depreciation', 'fa-line-chart fa-flip-vertical', 'Depreciation'],
        ['accounts.journal.trial-balance', 'fa-balance-scale', 'Trial Balance'],
        ['accounts.journal.balance-sheet', 'fa-columns', 'Balance Sheet'],
        ['accounts.control.cash-flow', 'fa-tint', 'Cash Flow'],
    ] as [$routeName, $icon, $label])
        <a href="{{ route($routeName) }}" class="btn btn-default btn-sm {{ request()->routeIs($routeName) ? 'active' : '' }}"><i class="fa {{ $icon }}"></i> {{ $label }}</a>
    @endforeach
</div>
@endif

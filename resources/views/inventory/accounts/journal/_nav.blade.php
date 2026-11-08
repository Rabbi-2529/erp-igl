{{-- Links between the journal pages (needs reports._style). $navGroup = 'control' shows the tax & year-end pages --}}
@php
    $navLinks = ($navGroup ?? 'books') === 'control' ? [
        ['accounts.control.vat', 'fa-percent', 'VAT'],
        ['accounts.control.budgets', 'fa-bullseye', 'Budgets'],
        ['accounts.control.fiscal-years', 'fa-calendar-check-o', 'Fiscal Years'],
        ['accounts.control.audit', 'fa-history', 'Audit Trail'],
        ['accounts.control.settings', 'fa-cog', 'Accounts Settings'],
    ] : [
        ['accounts.journal.coa', 'fa-sitemap', 'Chart of Accounts'],
        ['accounts.journal.vouchers', 'fa-pencil-square-o', 'Journal Vouchers'],
        ['accounts.journal.book', 'fa-book', 'Journal Book'],
        ['accounts.journal.ledger', 'fa-list-alt', 'Account Ledger'],
        ['accounts.journal.trial-balance', 'fa-balance-scale', 'Trial Balance'],
        ['accounts.journal.balance-sheet', 'fa-columns', 'Balance Sheet'],
        ['accounts.reports.profit-loss', 'fa-line-chart', 'Profit & Loss'],
        ['accounts.control.cash-flow', 'fa-tint', 'Cash Flow'],
    ];
@endphp
<div class="rep-links rep-noprint">
    @foreach($navLinks as [$routeName, $icon, $label])
        <a href="{{ route($routeName) }}" class="btn btn-default btn-sm {{ request()->routeIs($routeName) || request()->routeIs($routeName . '.*') ? 'active' : '' }}"><i class="fa {{ $icon }}"></i> {{ $label }}</a>
    @endforeach
</div>

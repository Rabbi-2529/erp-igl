<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\AccountsReports;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Accounts > Reports (Phase 1): Accounts dashboard, Profit & Loss, Day Book, Cash & Bank Book,
 * Due Aging and Expense Analysis. Read-only: these pages never write to the database.
 */
class AccountsReportController extends Controller
{
    private function reports()
    {
        return new AccountsReports(Auth::user()->au_company_id, Auth::user()->au_branch_id);
    }

    /** A date from the filter (yyyy-mm-dd or dd/mm/yyyy), or the default */
    private function date($value, Carbon $default)
    {
        if (!$value) {
            return $default;
        }
        try {
            return strpos($value, '/') !== false ? Carbon::createFromFormat('d/m/Y', $value)->startOfDay() : Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /** [from, to] from the filter; to is never before from */
    private function period(Request $request, Carbon $defaultFrom, Carbon $defaultTo)
    {
        $from = $this->date($request->input('from'), $defaultFrom);
        $to = $this->date($request->input('to'), $defaultTo);
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        return [$from, $to];
    }

    public function dashboard()
    {
        $r = $this->reports();
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $accounts = $r->accountSummary($today, $today);
        $cash = $accounts->where('is_cash', true)->sum('closing');
        $bank = $accounts->where('is_cash', false)->sum('closing');
        $receivable = $r->aging(2, $today);
        $payable = $r->aging(1, $today);
        $month = $r->profitLoss($monthStart, $today);
        $todayMoves = $r->movements($today, $today);
        return view('inventory.accounts.reports.dashboard', compact('accounts', 'cash', 'bank', 'receivable', 'payable', 'month', 'todayMoves', 'today', 'monthStart'));
    }

    public function profitLoss(Request $request)
    {
        [$from, $to] = $this->period($request, Carbon::today()->startOfMonth(), Carbon::today());
        $withSalary = !$request->has('from') || $request->boolean('salary');
        $pl = $this->reports()->profitLoss($from, $to, $withSalary);
        return view('inventory.accounts.reports.profit_loss', compact('pl', 'from', 'to', 'withSalary'));
    }

    public function dayBook(Request $request)
    {
        [$from, $to] = $this->period($request, Carbon::today(), Carbon::today());
        if ($from->diffInDays($to) > 92) {
            $to = $from->copy()->addDays(92);   // keep the page quick: up to three months at a time
        }
        $r = $this->reports();
        $moves = $r->movements($from, $to);
        $invoices = $r->invoices($from, $to);
        $days = $moves->groupBy('inv_abs_transaction_date');
        $invoiceDays = $invoices->groupBy('d');
        $dates = collect($days->keys())->merge($invoiceDays->keys())->unique()->sort()->values();
        return view('inventory.accounts.reports.day_book', compact('from', 'to', 'moves', 'invoices', 'days', 'invoiceDays', 'dates'));
    }

    public function cashBook(Request $request)
    {
        [$from, $to] = $this->period($request, Carbon::today()->startOfMonth(), Carbon::today());
        $r = $this->reports();
        $summary = $r->accountSummary($from, $to);
        $accountId = (int) $request->input('account');
        $account = $accountId ? $summary->firstWhere('id', $accountId) : null;
        $rows = collect();
        if ($account) {
            $balance = $account->opening;
            $rows = $r->movements($from, $to, $account->id)->map(function ($m) use (&$balance) {
                $balance += $m->in - $m->out;
                $m->balance = $balance;
                return $m;
            });
        }
        return view('inventory.accounts.reports.cash_book', compact('from', 'to', 'summary', 'account', 'rows'));
    }

    public function aging(Request $request)
    {
        $kind = $request->input('type') === 'supplier' ? 'supplier' : 'customer';
        $asOf = $this->date($request->input('as_of'), Carbon::today());
        $data = $this->reports()->aging($kind === 'supplier' ? 1 : 2, $asOf);
        return view('inventory.accounts.reports.aging', compact('kind', 'asOf', 'data'));
    }

    public function expenses(Request $request)
    {
        $year = (int) $request->input('year', Carbon::today()->year);
        if ($year < 2000 || $year > Carbon::today()->year + 1) {
            $year = Carbon::today()->year;
        }
        $data = $this->reports()->expenseYear($year);
        $chart = array_map(function ($r) { return ['head' => $r->head, 'total' => round($r->total, 2)]; }, $data['rows']);
        return view('inventory.accounts.reports.expenses', compact('year', 'data', 'chart'));
    }

    /** Depreciation schedule of the fixed assets (calculated from price, rate and method; nothing is saved) */
    public function depreciation(Request $request)
    {
        $asOf = $this->date($request->input('as_of'), Carbon::today());
        $assets = $this->reports()->depreciationSchedule($asOf);
        $categories = \Illuminate\Support\Facades\DB::table('inv_asset_category')
            ->whereIn('category_id', $assets->pluck('asset_category_id')->filter()->all() ?: [0])->pluck('category_name', 'category_id');
        $live = $assets->where('disposed', false);
        $totals = [
            'price'     => $live->sum('asset_purchase_price'),
            'this_year' => $live->sum('this_year'),
            'so_far'    => $live->sum('so_far'),
            'book'      => $live->sum('book_value'),
            'monthly'   => $live->sum('monthly_now'),
        ];
        return view('inventory.accounts.reports.depreciation', compact('asOf', 'assets', 'categories', 'totals'));
    }
}

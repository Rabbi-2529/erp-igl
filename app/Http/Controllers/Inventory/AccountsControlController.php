<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inv_acc_bank_statement;
use App\Services\AccountsJournal;
use App\Services\AccountsReports;
use App\Services\AccountsSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accounts Phase 3b: accounts settings, VAT, cash flow, fiscal year closing, budgets and the audit trail.
 * Writes only to the new tables (acc_settings, acc_fiscal_years, acc_budgets, acc_audit_log) and,
 * for a VAT payment, one new bank / cash movement row (type 15), like every other payment.
 */
class AccountsControlController extends Controller
{
    private function company() { return (int) Auth::user()->au_company_id; }
    private function branch() { return (int) Auth::user()->au_branch_id; }
    private function journal() { return new AccountsJournal($this->company(), $this->branch()); }

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

    /** Fiscal year start from ?fy=2026 (the year it starts in), or the current one */
    private function fyStart(Request $request)
    {
        $current = AccountsJournal::fiscalYearStart(Carbon::today());
        $y = (int) $request->input('fy');
        if ($y >= 2000 && $y <= $current->year + 1) {
            return Carbon::create($y, AccountsJournal::FY_START_MONTH, 1)->startOfDay();
        }
        return $current;
    }

    /** Fiscal years from the first entry of the branch to the current one, newest first */
    private function fiscalYearList()
    {
        $first = collect([
            DB::table('inv_acc_bank_statements')->where('inv_abs_company_id', $this->company())->where('inv_abs_branch_id', $this->branch())->where('inv_abs_status', 1)->min('inv_abs_transaction_date'),
            DB::table('inv_product_inventories')->where('inv_pro_inv_com_id', $this->company())->where('inv_pro_inv_branch_id', $this->branch())->where('inv_pro_inv_status', 1)->whereIn('inv_pro_inv_deal_type', [1, 2])->min('inv_pro_inv_issue_date'),
        ])->filter()->min();
        $current = AccountsJournal::fiscalYearStart(Carbon::today());
        $start = $first ? AccountsJournal::fiscalYearStart(Carbon::parse($first)) : $current;
        $years = [];
        for ($y = $current->copy(); $y->gte($start); $y->subYear()) {
            $years[] = $y->copy();
        }
        return $years;
    }

    private function adminOnly()
    {
        abort_unless(AccountsSettings::isAdmin(), 403, 'Only a company admin can do this.');
    }

    // ------------------------------------------------------------------ settings

    public function settings()
    {
        $settings = AccountsSettings::get($this->company());
        $isAdmin = AccountsSettings::isAdmin();
        return view('inventory.accounts.control.settings', compact('settings', 'isAdmin'));
    }

    public function settingsSave(Request $request)
    {
        $this->adminOnly();
        $data = $request->validate([
            'vat_on'          => 'nullable|in:0,1',
            'vat_bin'         => 'nullable|string|max:30',
            'vat_rate'        => 'required|numeric|min:0|max:100',
            'vat_rebate_rate' => 'required|numeric|min:0|max:100',
            'vat_from'        => 'nullable|date_format:Y-m-d',
            'approval_on'     => 'nullable|in:0,1',
        ], [], ['vat_rate' => 'VAT rate', 'vat_rebate_rate' => 'VAT rebate rate', 'vat_from' => 'VAT start date']);
        $before = AccountsSettings::get($this->company());
        $s = AccountsSettings::save($this->company(), [
            'vat_on' => (bool) ($data['vat_on'] ?? false), 'vat_bin' => trim((string) ($data['vat_bin'] ?? '')),
            'vat_rate' => round((float) $data['vat_rate'], 2), 'vat_rebate_rate' => round((float) $data['vat_rebate_rate'], 2),
            'vat_from' => $data['vat_from'] ?? null, 'approval_on' => (bool) ($data['approval_on'] ?? false),
        ]);
        $changed = array_keys(array_filter($s, function ($v, $k) use ($before) { return $before[$k] !== $v; }, ARRAY_FILTER_USE_BOTH));
        AccountsSettings::log('settings.update', 'Accounts settings saved' . ($changed ? ': ' . implode(', ', $changed) : ''), 'settings', $this->company(), null, ['before' => $before, 'after' => $s]);
        return redirect()->route('accounts.control.settings')->with('msg', 'Accounts settings saved.');
    }

    // ------------------------------------------------------------------ VAT

    public function vat(Request $request)
    {
        $fyStart = $this->fyStart($request);
        $data = $this->journal()->vatMonths($fyStart);
        $years = $this->fiscalYearList();
        $accounts = (new AccountsReports($this->company(), $this->branch()))->accountSummary(Carbon::today(), Carbon::today());
        $payments = DB::table('inv_acc_bank_statements as s')->leftJoin('admin_user as u', 'u.au_id', '=', 's.inv_abs_submit_by')
            ->where('s.inv_abs_company_id', $this->company())->where('s.inv_abs_branch_id', $this->branch())->where('s.inv_abs_status', 1)
            ->where('s.inv_abs_reference_type', 15)
            ->whereBetween('s.inv_abs_transaction_date', [$fyStart->toDateString(), $fyStart->copy()->addYear()->subDay()->toDateString()])
            ->orderByDesc('s.inv_abs_transaction_date')->orderByDesc('s.inv_abs_id')->get(['s.*', 'u.au_name']);
        $accountNames = $accounts->pluck('label', 'id');
        $settings = AccountsSettings::get($this->company());
        return view('inventory.accounts.control.vat', compact('data', 'fyStart', 'years', 'accounts', 'payments', 'accountNames', 'settings'));
    }

    public function vatPay(Request $request)
    {
        $data = $request->validate([
            'period'     => 'required|date_format:Y-m',
            'amount'     => 'required|numeric|min:0.01',
            'account_id' => 'required|integer',
            'date'       => 'required|date_format:Y-m-d',
            'note'       => 'nullable|string|max:150',
        ], [], ['period' => 'VAT month', 'account_id' => 'pay from', 'date' => 'payment date']);
        $period = Carbon::createFromFormat('Y-m', $data['period'])->startOfMonth();
        $back = redirect()->route('accounts.control.vat', ['fy' => AccountsJournal::fiscalYearStart($period)->year]);
        if (!AccountsSettings::vat($this->company())) {
            return $back->with('errmsg', 'VAT is off. Turn it on in Accounts > Settings first.');
        }
        if ($err = AccountsSettings::lockError($data['date'], $this->company(), $this->branch())) {
            return $back->withInput()->with('errmsg', $err);
        }
        $account = DB::table('inv_acc_bank_infos')->where('inv_abi_id', $data['account_id'])->where('inv_abi_company_id', $this->company())
            ->where('inv_abi_branch_id', $this->branch())->where('inv_abi_status', 1)->first();
        if (!$account) {
            return $back->withInput()->with('errmsg', 'Choose a bank or cash account of this branch.');
        }
        $amount = round((float) $data['amount'], 2);
        $balance = (float) Inv_acc_bank_statement::getAvailableBalanceByBankId($account->inv_abi_id);
        if ((int) $account->inv_abi_account_type === 2 && $amount > $balance + 0.005) {
            return $back->withInput()->with('errmsg', 'Not enough cash in ' . $account->inv_abi_account_name . ': ' . number_format($balance, 2) . '.');
        }
        $voucher = 'VAT-' . Carbon::now()->format('YmdHis');
        $st = new Inv_acc_bank_statement();
        $st->inv_abs_company_id = $this->company();
        $st->inv_abs_branch_id = $this->branch();
        $st->inv_abs_bank_id = $account->inv_abi_id;
        $st->inv_abs_reference_id = (int) $period->format('Ym');        // the VAT month, e.g. 202611
        $st->inv_abs_reference_type = 15;                               // 15 = VAT payment
        $st->inv_abs_debit = $amount;
        $st->inv_abs_credit = 0;
        $st->inv_abs_transaction_date = $data['date'];
        $st->inv_abs_voucher_no = $voucher;
        $st->inv_abs_description = mb_substr('VAT for ' . $period->format('F Y') . (!empty($data['note']) ? ' - ' . $data['note'] : ''), 0, 255);
        $st->inv_abs_status = 1;
        $st->inv_abs_submit_by = Auth::user()->au_id;
        $st->inv_abs_submit_at = Carbon::now();
        $st->save();
        AccountsSettings::log('vat.pay', 'VAT for ' . $period->format('F Y') . ' paid from ' . $account->inv_abi_account_name, 'statement', $st->inv_abs_id, $amount);
        return $back->with('msg', 'VAT payment of ' . number_format($amount, 2) . ' for ' . $period->format('F Y') . ' saved (voucher ' . $voucher . ').');
    }

    // ------------------------------------------------------------------ cash flow

    public function cashFlow(Request $request)
    {
        $from = $this->date($request->input('from'), AccountsJournal::fiscalYearStart(Carbon::today()));
        $to = $this->date($request->input('to'), Carbon::today());
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        $cf = $this->journal()->cashFlow($from, $to);
        return view('inventory.accounts.control.cash_flow', compact('cf', 'from', 'to'));
    }

    // ------------------------------------------------------------------ fiscal years

    public function fiscalYears()
    {
        $j = $this->journal();
        $rows = DB::table('acc_fiscal_years as f')->leftJoin('admin_user as c', 'c.au_id', '=', 'f.afy_closed_by')->leftJoin('admin_user as r', 'r.au_id', '=', 'f.afy_reopened_by')
            ->where('f.afy_company_id', $this->company())->where('f.afy_branch_id', $this->branch())
            ->get(['f.*', 'c.au_name as closed_name', 'r.au_name as reopened_name'])->keyBy('afy_start');
        $today = Carbon::today();
        $years = [];
        $closedEnds = $j->closedYears()->pluck('afy_end')->all();
        $latestClosed = $closedEnds ? max($closedEnds) : null;
        foreach ($this->fiscalYearList() as $start) {
            $end = $start->copy()->addYear()->subDay();
            $rec = $rows->get($start->toDateString());
            $closed = $rec && $rec->afy_status === 'closed';
            $years[] = (object) [
                'start' => $start, 'end' => $end, 'label' => AccountsJournal::fyLabel($start), 'rec' => $rec, 'closed' => $closed,
                'profit' => $j->profit($start, $end), 'ended' => $end->lt($today),
                'canReopen' => $closed && $latestClosed && $rec->afy_end === $latestClosed,
            ];
        }
        // only the oldest open year that has ended can be closed (years close in order)
        $oldestOpen = collect($years)->reverse()->first(function ($y) { return !$y->closed; });
        foreach ($years as $y) {
            $y->canClose = !$y->closed && $y->ended && $oldestOpen && $y->start->eq($oldestOpen->start);
        }
        $changes = $j->closedYearChanges();
        $isAdmin = AccountsSettings::isAdmin();
        $lockedUntil = AccountsSettings::lockedUntil($this->company(), $this->branch());
        return view('inventory.accounts.control.fiscal_years', compact('years', 'changes', 'isAdmin', 'lockedUntil'));
    }

    public function closeYear(Request $request)
    {
        $this->adminOnly();
        $data = $request->validate(['start' => 'required|date_format:Y-m-d', 'note' => 'nullable|string|max:255']);
        $back = redirect()->route('accounts.control.fiscal-years');
        $start = Carbon::parse($data['start'])->startOfDay();
        if ($start->month !== AccountsJournal::FY_START_MONTH || $start->day !== 1) {
            return $back->with('errmsg', 'A fiscal year starts on 1 July.');
        }
        $end = $start->copy()->addYear()->subDay();
        if (!$end->lt(Carbon::today())) {
            return $back->with('errmsg', 'Fiscal year ' . AccountsJournal::fyLabel($start) . ' has not ended yet.');
        }
        $j = $this->journal();
        $open = DB::table('acc_fiscal_years')->where('afy_company_id', $this->company())->where('afy_branch_id', $this->branch())
            ->where('afy_status', 'closed')->where('afy_end', '>=', $end->toDateString())->exists();
        if ($open) {
            return $back->with('errmsg', 'This year or a later one is already closed.');
        }
        $earlierOpen = collect($this->fiscalYearList())->first(function ($s) use ($start, $j) {
            return $s->lt($start) && !$j->closedYears()->contains(function ($y) use ($s) { return $y->afy_start === $s->toDateString(); });
        });
        if ($earlierOpen) {
            return $back->with('errmsg', 'Close fiscal year ' . AccountsJournal::fyLabel($earlierOpen) . ' first: years close in order.');
        }
        $prev = $j->closedYears()->last();
        $profit = $j->profit($prev ? Carbon::parse($prev->afy_end)->addDay() : null, $end);
        DB::table('acc_fiscal_years')->updateOrInsert(
            ['afy_company_id' => $this->company(), 'afy_branch_id' => $this->branch(), 'afy_start' => $start->toDateString()],
            ['afy_end' => $end->toDateString(), 'afy_status' => 'closed', 'afy_profit' => $profit, 'afy_note' => $data['note'] ?? null,
             'afy_closed_by' => Auth::user()->au_id, 'afy_closed_at' => Carbon::now()]
        );
        AccountsSettings::log('year.close', 'Fiscal year ' . AccountsJournal::fyLabel($start) . ' closed, profit moved to retained earnings', 'fiscal_year', $start->toDateString(), $profit);
        return $back->with('msg', 'Fiscal year ' . AccountsJournal::fyLabel($start) . ' closed. Profit ' . number_format($profit, 2) . ' moved to retained earnings; the books are locked up to ' . $end->format('d M Y') . '.');
    }

    public function reopenYear(Request $request)
    {
        $this->adminOnly();
        $data = $request->validate(['start' => 'required|date_format:Y-m-d', 'reason' => 'required|string|max:255'], [], ['reason' => 'reason']);
        $back = redirect()->route('accounts.control.fiscal-years');
        $rec = DB::table('acc_fiscal_years')->where('afy_company_id', $this->company())->where('afy_branch_id', $this->branch())
            ->where('afy_start', $data['start'])->where('afy_status', 'closed')->first();
        if (!$rec) {
            return $back->with('errmsg', 'That year is not closed.');
        }
        $later = DB::table('acc_fiscal_years')->where('afy_company_id', $this->company())->where('afy_branch_id', $this->branch())
            ->where('afy_status', 'closed')->where('afy_end', '>', $rec->afy_end)->exists();
        if ($later) {
            return $back->with('errmsg', 'Reopen the later closed year first: years reopen from the newest.');
        }
        DB::table('acc_fiscal_years')->where('afy_id', $rec->afy_id)->update([
            'afy_status' => 'reopened', 'afy_reopened_by' => Auth::user()->au_id, 'afy_reopened_at' => Carbon::now(), 'afy_reopen_reason' => trim($data['reason']),
        ]);
        $label = AccountsJournal::fyLabel(Carbon::parse($rec->afy_start));
        AccountsSettings::log('year.reopen', 'Fiscal year ' . $label . ' reopened: ' . trim($data['reason']), 'fiscal_year', $rec->afy_start);
        return $back->with('msg', 'Fiscal year ' . $label . ' reopened. Its entries can be changed again; close it again when done.');
    }

    // ------------------------------------------------------------------ budgets

    public function budgets(Request $request)
    {
        $j = $this->journal();
        $fyStart = $this->fyStart($request);
        $fyEnd = $fyStart->copy()->addYear()->subDay();
        $today = Carbon::today();
        $asOf = $today->lt($fyEnd) ? ($today->lt($fyStart) ? $fyStart->copy()->subDay() : $today) : $fyEnd;
        $monthsGone = $asOf->lt($fyStart) ? 0 : min(12, ($asOf->year - $fyStart->year) * 12 + $asOf->month - $fyStart->month + 1);
        $totals = $asOf->lt($fyStart) ? [] : $j->totals($fyStart, $asOf);
        $saved = DB::table('acc_budgets')->where('ab_company_id', $this->company())->where('ab_branch_id', $this->branch())
            ->where('ab_fy_year', $fyStart->year)->pluck('ab_amount', 'ab_account_key');
        $accounts = $j->accounts();
        $rows = [];
        foreach ($j->ordered($accounts) as [$a, $depth]) {
            if (!in_array($a->type, ['income', 'expense'], true)) {
                continue;
            }
            $hasKids = $accounts->contains(function ($x) use ($a) { return $x->parent === $a->key; });
            $t = $totals[$a->key] ?? ['dr' => 0, 'cr' => 0];
            $actual = round(AccountsJournal::natural($a->type, $t['dr'], $t['cr']), 2);
            $budget = (float) ($saved[$a->key] ?? 0);
            if (!$a->active && !$budget && abs($actual) < 0.005) {
                continue;
            }
            $toDate = round($budget * $monthsGone / 12, 2);
            $rows[] = (object) ['account' => $a, 'depth' => $depth, 'group' => $hasKids, 'budget' => $budget, 'toDate' => $toDate, 'actual' => $actual,
                'diff' => round($actual - $toDate, 2), 'used' => $budget > 0 ? round($actual / $budget * 100, 1) : null];
        }
        $years = $this->fiscalYearList();
        if (!collect($years)->contains(function ($y) use ($fyStart) { return $y->eq($fyStart); })) {
            array_unshift($years, $fyStart->copy());
        }
        $next = AccountsJournal::fiscalYearStart($today)->addYear();
        if (!collect($years)->contains(function ($y) use ($next) { return $y->eq($next); })) {
            array_unshift($years, $next);
        }
        $isAdmin = AccountsSettings::isAdmin();
        return view('inventory.accounts.control.budgets', compact('rows', 'fyStart', 'fyEnd', 'asOf', 'monthsGone', 'years', 'isAdmin'));
    }

    public function budgetsSave(Request $request)
    {
        $this->adminOnly();
        $data = $request->validate(['fy' => 'required|integer|min:2000|max:2100', 'budget' => 'nullable|array', 'budget.*' => 'nullable|numeric|min:0|max:99999999999']);
        $keys = $this->journal()->accounts()->filter(function ($a) { return in_array($a->type, ['income', 'expense'], true); })->keys()->all();
        $now = Carbon::now();
        $n = 0;
        $total = 0;
        DB::transaction(function () use ($data, $keys, $now, &$n, &$total) {
            foreach ((array) ($data['budget'] ?? []) as $key => $amount) {
                if (!in_array($key, $keys, true)) {
                    continue;
                }
                $amount = round((float) $amount, 2);
                $where = ['ab_company_id' => $this->company(), 'ab_branch_id' => $this->branch(), 'ab_fy_year' => (int) $data['fy'], 'ab_account_key' => $key];
                if ($amount > 0) {
                    DB::table('acc_budgets')->updateOrInsert($where, ['ab_amount' => $amount, 'ab_updated_by' => Auth::user()->au_id, 'ab_updated_at' => $now]);
                    $n++;
                    $total += $amount;
                } else {
                    DB::table('acc_budgets')->where($where)->delete();
                }
            }
        });
        $label = AccountsJournal::fyLabel(Carbon::create((int) $data['fy'], AccountsJournal::FY_START_MONTH, 1));
        AccountsSettings::log('budget.save', 'Budget for ' . $label . ' saved (' . $n . ' accounts)', 'budget', $data['fy'], $total);
        return redirect()->route('accounts.control.budgets', ['fy' => $data['fy']])->with('msg', 'Budget for ' . $label . ' saved: ' . $n . ' accounts.');
    }

    // ------------------------------------------------------------------ audit trail

    public function audit(Request $request)
    {
        $from = $this->date($request->input('from'), Carbon::today()->subDays(6));
        $to = $this->date($request->input('to'), Carbon::today());
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->diffInDays($to) > 92) {
            $from = $to->copy()->subDays(92);
        }
        $user = (int) $request->input('user');
        $source = in_array($request->input('source'), ['actions', 'money'], true) ? $request->input('source') : 'all';
        $range = [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
        $rows = collect();
        if ($source !== 'money' && AccountsSettings::hasTable('acc_audit_log')) {
            $rows = $rows->concat(DB::table('acc_audit_log as a')->leftJoin('admin_user as u', 'u.au_id', '=', 'a.aal_user_id')
                ->where('a.aal_company_id', $this->company())->where(function ($q) { $q->where('a.aal_branch_id', $this->branch())->orWhereNull('a.aal_branch_id'); })
                ->whereBetween('a.aal_created_at', $range)->when($user, function ($q) use ($user) { $q->where('a.aal_user_id', $user); })
                ->get(['a.aal_created_at as at', 'u.au_name as who', 'a.aal_action as action', 'a.aal_summary as summary', 'a.aal_amount as amount', 'a.aal_ip as ip'])
                ->map(function ($r) { $r->kind = 'action'; return $r; }));
        }
        if ($source !== 'actions') {
            $stmts = DB::table('inv_acc_bank_statements as s')->leftJoin('admin_user as u', 'u.au_id', '=', 's.inv_abs_submit_by')
                ->where('s.inv_abs_company_id', $this->company())->where('s.inv_abs_branch_id', $this->branch())
                ->whereBetween('s.inv_abs_submit_at', $range)->when($user, function ($q) use ($user) { $q->where('s.inv_abs_submit_by', $user); })
                ->orderByDesc('s.inv_abs_submit_at')->limit(1000)
                ->get(['s.*', 'u.au_name as who']);
            $names = $this->journal()->accounts();
            $rows = $rows->concat($stmts->map(function ($s) use ($names) {
                $in = (float) $s->inv_abs_credit > 0;
                return (object) ['at' => $s->inv_abs_submit_at, 'who' => $s->who, 'kind' => 'money',
                    'action' => Inv_acc_bank_statement::getReferenceTypeLabel($s) . ((int) $s->inv_abs_status === 1 ? '' : ' (removed)'),
                    'summary' => trim(($in ? 'Into ' : 'Out of ') . optional($names->get('bank:' . $s->inv_abs_bank_id))->name . ' · ' . $s->inv_abs_transaction_date
                        . ($s->inv_abs_voucher_no ? ' · ' . $s->inv_abs_voucher_no : '') . ($s->inv_abs_description ? ' · ' . $s->inv_abs_description : '')),
                    'amount' => $in ? (float) $s->inv_abs_credit : -(float) $s->inv_abs_debit, 'ip' => null];
            }));
        }
        $rows = $rows->sortByDesc('at')->values();
        $users = DB::table('admin_user')->where('au_company_id', $this->company())->where('au_branch_id', $this->branch())->orderBy('au_name')->pluck('au_name', 'au_id');
        return view('inventory.accounts.control.audit', compact('rows', 'from', 'to', 'user', 'users', 'source'));
    }
}

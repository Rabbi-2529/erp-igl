<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\AccountsJournal;
use App\Services\AccountsSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accounts > Journal (Phase 3a): chart of accounts, journal vouchers, journal book, account ledger,
 * trial balance and balance sheet. Writes only to the new tables acc_coa, acc_journals and acc_journal_lines.
 */
class JournalController extends Controller
{
    private function company() { return (int) Auth::user()->au_company_id; }
    private function branch() { return (int) Auth::user()->au_branch_id; }

    private function journal()
    {
        return new AccountsJournal($this->company(), $this->branch());
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

    private function period(Request $request, Carbon $from, Carbon $to, $maxDays)
    {
        $from = $this->date($request->input('from'), $from);
        $to = $this->date($request->input('to'), $to);
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->diffInDays($to) > $maxDays) {
            $from = $to->copy()->subDays($maxDays);
        }
        return [$from, $to];
    }

    // ------------------------------------------------------------------ chart of accounts

    public function coa()
    {
        $j = $this->journal();
        $accounts = $j->accounts();
        $totals = $j->totals(null, Carbon::today(), true);
        $rows = collect($j->ordered($accounts))->map(function ($x) use ($totals) {
            [$a, $depth] = $x;
            $t = $totals[$a->key] ?? ['dr' => 0, 'cr' => 0];
            return (object) ['account' => $a, 'depth' => $depth, 'balance' => round(AccountsJournal::natural($a->type, $t['dr'], $t['cr']), 2)];
        });
        // groups a new account can go under: chart accounts (not banks / heads)
        $parents = $accounts->filter(function ($a) { return !$a->dynamic; })->sortBy('code');
        return view('inventory.accounts.journal.coa', compact('rows', 'parents'));
    }

    private function coaRules($id = null)
    {
        return [
            'code'   => 'required|string|max:20|regex:/^[A-Za-z0-9.\-]+$/',
            'name'   => 'required|string|max:150',
            'type'   => $id ? 'nullable' : 'required|in:' . implode(',', array_keys(AccountsJournal::TYPES)),
            'parent' => 'nullable|string|max:40',
            'manual' => 'nullable|in:0,1',
            'status' => 'nullable|in:0,1',
            'note'   => 'nullable|string|max:255',
        ];
    }

    /** coa_id of a parent account key, checked to be a chart account of the same type */
    private function parentId($key, $type, $selfKey = null)
    {
        if (!$key) {
            return [null, null];
        }
        $p = $this->journal()->accounts()->get($key);
        if (!$p || $p->dynamic || !$p->coa_id) {
            return [null, 'Choose a group from the chart of accounts.'];
        }
        if ($p->type !== $type) {
            return [null, 'The group must be of the same type (' . AccountsJournal::TYPES[$type] . ').'];
        }
        if ($selfKey && ($p->key === $selfKey || $this->isUnder($p->key, $selfKey))) {
            return [null, 'An account cannot go under itself.'];
        }
        return [$p->coa_id, null];
    }

    private function isUnder($key, $ancestor)
    {
        $all = $this->journal()->accounts();
        for ($k = $all[$key]->parent ?? null, $n = 0; $k && $n < 20; $k = $all[$k]->parent ?? null, $n++) {
            if ($k === $ancestor) {
                return true;
            }
        }
        return false;
    }

    public function coaStore(Request $request)
    {
        $data = $request->validate($this->coaRules(), ['code.regex' => 'The code can have letters, numbers, dots and dashes.']);
        $back = redirect()->route('accounts.journal.coa');
        $this->journal()->ensureSystemAccounts();
        if (DB::table('acc_coa')->where('coa_company_id', $this->company())->where('coa_code', $data['code'])->exists()) {
            return $back->withInput()->with('errmsg', 'Code ' . $data['code'] . ' is already used.');
        }
        [$parentId, $err] = $this->parentId($data['parent'] ?? null, $data['type']);
        if ($err) {
            return $back->withInput()->with('errmsg', $err);
        }
        DB::table('acc_coa')->insert([
            'coa_company_id' => $this->company(), 'coa_code' => $data['code'], 'coa_name' => trim($data['name']), 'coa_type' => $data['type'],
            'coa_parent_id' => $parentId, 'coa_manual' => (int) ($data['manual'] ?? 1), 'coa_status' => 1, 'coa_note' => $data['note'] ?? null,
            'coa_created_by' => Auth::user()->au_id, 'coa_created_at' => Carbon::now(),
        ]);
        AccountsSettings::log('coa.add', 'Account ' . $data['code'] . ' ' . trim($data['name']) . ' added (' . $data['type'] . ')', 'coa', $data['code']);
        return $back->with('msg', 'Account ' . $data['code'] . ' ' . trim($data['name']) . ' added.');
    }

    public function coaUpdate(Request $request, $id)
    {
        $data = $request->validate($this->coaRules($id), ['code.regex' => 'The code can have letters, numbers, dots and dashes.']);
        $back = redirect()->route('accounts.journal.coa');
        $row = DB::table('acc_coa')->where('coa_company_id', $this->company())->where('coa_id', (int) $id)->first();
        if (!$row) {
            return $back->with('errmsg', 'Account not found.');
        }
        if (DB::table('acc_coa')->where('coa_company_id', $this->company())->where('coa_code', $data['code'])->where('coa_id', '<>', $row->coa_id)->exists()) {
            return $back->withInput()->with('errmsg', 'Code ' . $data['code'] . ' is already used.');
        }
        $update = ['coa_code' => $data['code'], 'coa_name' => trim($data['name']), 'coa_note' => $data['note'] ?? null,
            'coa_updated_by' => Auth::user()->au_id, 'coa_updated_at' => Carbon::now()];
        if (!$row->coa_system_key) {     // system accounts keep their place, use and status
            $selfKey = 'coa:' . $row->coa_id;
            [$parentId, $err] = $this->parentId($data['parent'] ?? null, $row->coa_type, $selfKey);
            if ($err) {
                return $back->withInput()->with('errmsg', $err);
            }
            $update += ['coa_parent_id' => $parentId, 'coa_manual' => (int) ($data['manual'] ?? 1), 'coa_status' => (int) ($data['status'] ?? 1)];
        }
        DB::table('acc_coa')->where('coa_id', $row->coa_id)->update($update);
        AccountsSettings::log('coa.edit', 'Account ' . $row->coa_code . ' ' . $row->coa_name . ' changed to ' . $data['code'] . ' ' . trim($data['name']), 'coa', $row->coa_id);
        return $back->with('msg', 'Account ' . $data['code'] . ' ' . trim($data['name']) . ' saved.');
    }

    // ------------------------------------------------------------------ journal vouchers

    public function vouchers(Request $request)
    {
        [$from, $to] = $this->period($request, Carbon::today()->startOfMonth(), Carbon::today(), 366);
        $status = in_array($request->input('status'), ['posted', 'pending', 'rejected', 'cancelled', 'all'], true) ? $request->input('status') : 'posted';
        $list = DB::table('acc_journals as j')->leftJoin('admin_user as u', 'u.au_id', '=', 'j.aj_created_by')
            ->where('j.aj_company_id', $this->company())->where('j.aj_branch_id', $this->branch())
            ->whereBetween('j.aj_date', [$from->toDateString(), $to->toDateString()])
            ->when($status !== 'all', function ($q) use ($status) { $q->where('j.aj_status', $status); })
            ->orderByDesc('j.aj_date')->orderByDesc('j.aj_id')->get(['j.*', 'u.au_name']);
        $lines = DB::table('acc_journal_lines')->whereIn('ajl_journal_id', $list->pluck('aj_id')->all() ?: [0])
            ->orderBy('ajl_sort')->get()->groupBy('ajl_journal_id');
        $pending = DB::table('acc_journals')->where('aj_company_id', $this->company())->where('aj_branch_id', $this->branch())->where('aj_status', 'pending')->count();
        $isAdmin = AccountsSettings::isAdmin();
        return view('inventory.accounts.journal.vouchers', compact('list', 'lines', 'from', 'to', 'status', 'pending', 'isAdmin'));
    }

    public function voucherCreate()
    {
        $j = $this->journal();
        $accounts = $j->voucherAccounts();
        $groups = [];
        foreach ($j->ordered($accounts) as [$a, $depth]) {
            $groups[AccountsJournal::TYPES[$a->type]][] = $a;
        }
        $notAllowed = AccountsJournal::NOT_IN_VOUCHERS;
        $needsApproval = AccountsSettings::get($this->company())['approval_on'] && !AccountsSettings::isAdmin();
        $lockedUntil = AccountsSettings::lockedUntil($this->company(), $this->branch());
        return view('inventory.accounts.journal.voucher_form', compact('groups', 'notAllowed', 'needsApproval', 'lockedUntil'));
    }

    public function voucherStore(Request $request)
    {
        $data = $request->validate([
            'date'      => 'required|date_format:Y-m-d',
            'narration' => 'required|string|max:255',
            'account'   => 'required|array|max:50',
            'account.*' => 'nullable|string|max:40',
            'debit.*'   => 'nullable|numeric|min:0|max:9999999999',
            'credit.*'  => 'nullable|numeric|min:0|max:9999999999',
            'note.*'    => 'nullable|string|max:255',
        ], [], ['narration' => 'narration (what it is for)']);
        $back = redirect()->route('accounts.journal.vouchers.create')->withInput();
        if ($err = AccountsSettings::lockError($data['date'], $this->company(), $this->branch())) {
            return $back->with('errmsg', $err);
        }
        $allowed = $this->journal()->voucherAccounts();

        $lines = [];
        foreach ($data['account'] as $i => $key) {
            $dr = round((float) ($request->input("debit.$i") ?: 0), 2);
            $cr = round((float) ($request->input("credit.$i") ?: 0), 2);
            if (!$key && !$dr && !$cr) {
                continue;
            }
            $n = count($lines) + 1;
            if (!$key || !$allowed->has($key)) {
                return $back->with('errmsg', "Line $n: choose an account from the list.");
            }
            if ($dr > 0 && $cr > 0) {
                return $back->with('errmsg', "Line $n: enter a debit or a credit, not both.");
            }
            if (!$dr && !$cr) {
                return $back->with('errmsg', "Line $n: enter the amount.");
            }
            $lines[] = ['key' => $key, 'name' => $allowed[$key]->name, 'dr' => $dr, 'cr' => $cr, 'note' => trim((string) $request->input("note.$i")) ?: null];
        }
        if (count($lines) < 2) {
            return $back->with('errmsg', 'A journal voucher needs at least two lines: one debit and one credit.');
        }
        $dr = round(array_sum(array_column($lines, 'dr')), 2);
        $cr = round(array_sum(array_column($lines, 'cr')), 2);
        if ($dr <= 0 || abs($dr - $cr) > 0.004) {
            return $back->with('errmsg', 'Debits (' . number_format($dr, 2) . ') and credits (' . number_format($cr, 2) . ') must be equal.');
        }

        // with approval on, a staff user's voucher waits for an admin; an admin's voucher counts at once
        $status = AccountsSettings::get($this->company())['approval_on'] && !AccountsSettings::isAdmin() ? 'pending' : 'posted';
        $id = DB::transaction(function () use ($data, $lines, $dr, $status) {
            $year = substr($data['date'], 0, 4);
            $prefix = 'JV-' . $year . '-';
            $last = DB::table('acc_journals')->where('aj_company_id', $this->company())->where('aj_branch_id', $this->branch())
                ->where('aj_voucher_no', 'like', $prefix . '%')->lockForUpdate()->pluck('aj_voucher_no')
                ->map(function ($v) use ($prefix) { return (int) substr($v, strlen($prefix)); })->max();
            $voucher = $prefix . str_pad(($last ?: 0) + 1, 4, '0', STR_PAD_LEFT);
            $id = DB::table('acc_journals')->insertGetId([
                'aj_company_id' => $this->company(), 'aj_branch_id' => $this->branch(), 'aj_voucher_no' => $voucher,
                'aj_date' => $data['date'], 'aj_narration' => trim($data['narration']), 'aj_total' => $dr, 'aj_status' => $status,
                'aj_created_by' => Auth::user()->au_id, 'aj_created_at' => Carbon::now(),
            ]);
            foreach ($lines as $i => $l) {
                DB::table('acc_journal_lines')->insert([
                    'ajl_journal_id' => $id, 'ajl_account_key' => $l['key'], 'ajl_account_name' => mb_substr($l['name'], 0, 150),
                    'ajl_debit' => $l['dr'], 'ajl_credit' => $l['cr'], 'ajl_note' => $l['note'] ? mb_substr($l['note'], 0, 255) : null, 'ajl_sort' => $i,
                ]);
            }
            if ($status === 'pending' && AccountsSettings::hasTable('acc_journal_approvals')) {
                DB::table('acc_journal_approvals')->insert(['aja_journal_id' => $id, 'aja_action' => 'submitted', 'aja_user_id' => Auth::user()->au_id, 'aja_created_at' => Carbon::now()]);
            }
            return $id;
        });
        $v = DB::table('acc_journals')->where('aj_id', $id)->value('aj_voucher_no');
        AccountsSettings::log('voucher.' . ($status === 'pending' ? 'submit' : 'post'), 'Journal voucher ' . $v . ': ' . trim($data['narration']), 'journal', $id, $dr);
        return redirect()->route('accounts.journal.vouchers.show', $id)->with('msg', 'Journal voucher ' . $v . ' saved (' . number_format($dr, 2) . ')'
            . ($status === 'pending' ? '. It waits for an admin\'s approval and does not count in the books yet.' : '.'));
    }

    private function voucher($id)
    {
        return DB::table('acc_journals as j')->leftJoin('admin_user as u', 'u.au_id', '=', 'j.aj_created_by')
            ->leftJoin('admin_user as x', 'x.au_id', '=', 'j.aj_cancelled_by')
            ->where('j.aj_company_id', $this->company())->where('j.aj_branch_id', $this->branch())->where('j.aj_id', (int) $id)
            ->first(['j.*', 'u.au_name as created_name', 'x.au_name as cancelled_name']);
    }

    public function voucherShow($id)
    {
        $v = $this->voucher($id);
        abort_if(!$v, 404);
        $j = $this->journal();
        $lines = DB::table('acc_journal_lines')->where('ajl_journal_id', $v->aj_id)->orderBy('ajl_sort')->get()
            ->map(function ($l) use ($j) { $l->code = $j->account($l->ajl_account_key)->code; return $l; });
        $history = AccountsSettings::hasTable('acc_journal_approvals')
            ? DB::table('acc_journal_approvals as a')->leftJoin('admin_user as u', 'u.au_id', '=', 'a.aja_user_id')
                ->where('a.aja_journal_id', $v->aj_id)->orderBy('a.aja_id')->get(['a.*', 'u.au_name'])
            : collect();
        $isAdmin = AccountsSettings::isAdmin();
        $approvedBy = optional($history->where('aja_action', 'approved')->last())->au_name;
        return view('inventory.accounts.journal.voucher_show', compact('v', 'lines', 'history', 'isAdmin', 'approvedBy'));
    }

    /** A posted voucher is never edited or deleted: it is cancelled with a reason and stays on record */
    public function voucherCancel(Request $request, $id)
    {
        $data = $request->validate(['reason' => 'required|string|max:255'], [], ['reason' => 'reason']);
        $v = $this->voucher($id);
        abort_if(!$v, 404);
        if (!in_array($v->aj_status, ['posted', 'pending'], true)) {
            return redirect()->route('accounts.journal.vouchers.show', $v->aj_id)->with('errmsg', 'This voucher is already ' . $v->aj_status . '.');
        }
        if ($v->aj_status === 'posted' && ($err = AccountsSettings::lockError($v->aj_date, $this->company(), $this->branch()))) {
            return redirect()->route('accounts.journal.vouchers.show', $v->aj_id)->with('errmsg', $err);
        }
        DB::table('acc_journals')->where('aj_id', $v->aj_id)->update([
            'aj_status' => 'cancelled', 'aj_cancel_reason' => trim($data['reason']),
            'aj_cancelled_by' => Auth::user()->au_id, 'aj_cancelled_at' => Carbon::now(),
        ]);
        AccountsSettings::log('voucher.cancel', 'Journal voucher ' . $v->aj_voucher_no . ' cancelled: ' . trim($data['reason']), 'journal', $v->aj_id, $v->aj_total);
        return redirect()->route('accounts.journal.vouchers.show', $v->aj_id)->with('msg', 'Journal voucher ' . $v->aj_voucher_no . ' cancelled. It no longer counts in the books.');
    }

    /** An admin approves a pending voucher: from now on it counts in the books */
    public function voucherApprove($id)
    {
        abort_unless(AccountsSettings::isAdmin(), 403, 'Only a company admin can approve vouchers.');
        $v = $this->voucher($id);
        abort_if(!$v, 404);
        $back = redirect()->route('accounts.journal.vouchers.show', $v->aj_id);
        if ($v->aj_status !== 'pending') {
            return $back->with('errmsg', 'Only a pending voucher can be approved.');
        }
        if ($err = AccountsSettings::lockError($v->aj_date, $this->company(), $this->branch())) {
            return $back->with('errmsg', $err);
        }
        DB::transaction(function () use ($v) {
            DB::table('acc_journals')->where('aj_id', $v->aj_id)->update(['aj_status' => 'posted']);
            DB::table('acc_journal_approvals')->insert(['aja_journal_id' => $v->aj_id, 'aja_action' => 'approved', 'aja_user_id' => Auth::user()->au_id, 'aja_created_at' => Carbon::now()]);
        });
        AccountsSettings::log('voucher.approve', 'Journal voucher ' . $v->aj_voucher_no . ' approved', 'journal', $v->aj_id, $v->aj_total);
        return $back->with('msg', 'Journal voucher ' . $v->aj_voucher_no . ' approved. It now counts in the books.');
    }

    /** An admin rejects a pending voucher, with a reason; it stays on record */
    public function voucherReject(Request $request, $id)
    {
        abort_unless(AccountsSettings::isAdmin(), 403, 'Only a company admin can reject vouchers.');
        $data = $request->validate(['reason' => 'required|string|max:255'], [], ['reason' => 'reason']);
        $v = $this->voucher($id);
        abort_if(!$v, 404);
        $back = redirect()->route('accounts.journal.vouchers.show', $v->aj_id);
        if ($v->aj_status !== 'pending') {
            return $back->with('errmsg', 'Only a pending voucher can be rejected.');
        }
        DB::transaction(function () use ($v, $data) {
            DB::table('acc_journals')->where('aj_id', $v->aj_id)->update(['aj_status' => 'rejected', 'aj_cancel_reason' => trim($data['reason'])]);
            DB::table('acc_journal_approvals')->insert(['aja_journal_id' => $v->aj_id, 'aja_action' => 'rejected', 'aja_user_id' => Auth::user()->au_id,
                'aja_reason' => trim($data['reason']), 'aja_created_at' => Carbon::now()]);
        });
        AccountsSettings::log('voucher.reject', 'Journal voucher ' . $v->aj_voucher_no . ' rejected: ' . trim($data['reason']), 'journal', $v->aj_id, $v->aj_total);
        return $back->with('msg', 'Journal voucher ' . $v->aj_voucher_no . ' rejected.');
    }

    // ------------------------------------------------------------------ books and statements

    const BOOK_SOURCES = [
        'all'      => 'Everything',
        'money'    => 'Bank & cash movements',
        'parties'  => 'Sales, purchases, customers & suppliers',
        'cost'     => 'Cost of goods sold',
        'payroll'  => 'Payroll, assets & depreciation',
        'vouchers' => 'Journal vouchers',
    ];

    public function book(Request $request)
    {
        [$from, $to] = $this->period($request, Carbon::today()->startOfMonth(), Carbon::today(), 31);
        $source = array_key_exists($request->input('source'), self::BOOK_SOURCES) ? $request->input('source') : 'all';
        $entries = $this->journal()->entries($from, $to)->filter(function ($e) use ($source) {
            $k = $e->kind;
            switch ($source) {
                case 'money': return $k[0] === 'S';
                case 'parties': return $k[0] === 'I';
                case 'cost': return $k === 'COGS';
                case 'payroll': return in_array($k, ['PAY', 'FA', 'DEP'], true);
                case 'vouchers': return $k === 'JV';
                default: return true;
            }
        })->values();
        $sources = self::BOOK_SOURCES;
        return view('inventory.accounts.journal.book', compact('entries', 'from', 'to', 'source', 'sources'));
    }

    public function trialBalance(Request $request)
    {
        $asOf = $this->date($request->input('as_of'), Carbon::today());
        $showZero = $request->boolean('all');
        $tb = $this->journal()->trialBalance($asOf, $showZero);
        return view('inventory.accounts.journal.trial_balance', compact('tb', 'asOf', 'showZero'));
    }

    public function ledger(Request $request)
    {
        $j = $this->journal();
        $accounts = $j->accounts();
        [$from, $to] = $this->period($request, Carbon::today()->startOfMonth(), Carbon::today(), 366);
        $key = (string) $request->input('account');
        $account = $key !== '' ? $j->account($key) : null;
        $rows = collect();
        $opening = 0.0;
        if ($account) {
            $before = $j->totals(null, $from->copy()->subDay(), true)[$key] ?? ['dr' => 0, 'cr' => 0];
            $opening = round(AccountsJournal::natural($account->type, $before['dr'], $before['cr']), 2);
            $balance = $opening;
            foreach ($j->entries($from, $to, $key) as $e) {
                $own = $e->lines->where('key', $key);
                $dr = round($own->sum('dr'), 2);
                $cr = round($own->sum('cr'), 2);
                $balance += AccountsJournal::natural($account->type, $dr, $cr);
                $against = $e->lines->where('key', '<>', $key)->pluck('name')->unique()->implode(', ');
                $rows->push((object) ['entry' => $e, 'dr' => $dr, 'cr' => $cr, 'balance' => round($balance, 2), 'against' => $against]);
            }
        }
        $choices = collect($j->ordered($accounts));
        return view('inventory.accounts.journal.ledger', compact('account', 'rows', 'opening', 'from', 'to', 'choices'));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $this->date($request->input('as_of'), Carbon::today());
        $bs = $this->journal()->balanceSheet($asOf);
        return view('inventory.accounts.journal.balance_sheet', compact('bs', 'asOf'));
    }
}

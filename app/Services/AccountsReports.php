<?php

namespace App\Services;

use App\Models\Inv_acc_bank_statement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Accounts reports (Phase 1): read-only. Nothing here writes to the database.
 *
 * Where the money is (this ERP):
 *  - Bank and cash accounts: inv_acc_bank_infos (account type 2 = cash, otherwise bank).
 *  - Every money movement of those accounts: inv_acc_bank_statements (credit = money in, debit = money out),
 *    labelled by inv_abs_reference_type (1 customer, 2 supplier, 3/4/9 expense, 5 contra, 6 deposit,
 *    7 loan received / withdraw, 8 other income / loan payment, 10 OMS bill, 11 bank charge, 12 loan transfer, 13 LC).
 *  - Customer and supplier ledgers: inv_product_inventories (deal 2 customer, deal 1 supplier; debit = charge,
 *    credit = payment / return / discount). Sales rows carry the product and quantity, used for the cost of goods.
 */
class AccountsReports
{
    public $company;
    public $branch;

    const EXPENSE_TYPES = [3, 4, 9];

    public function __construct($companyId, $branchId)
    {
        $this->company = (int) $companyId;
        $this->branch = (int) $branchId;
    }

    // ------------------------------------------------------------------ building blocks

    /** Money movements of this branch (bank and cash) */
    public function statements()
    {
        return DB::table('inv_acc_bank_statements as s')
            ->where('s.inv_abs_company_id', $this->company)
            ->where('s.inv_abs_branch_id', $this->branch)
            ->where('s.inv_abs_status', 1);
    }

    /** Customer (deal 2) or supplier (deal 1) ledger rows; rows saved without a branch (opening balances) count too */
    public function ledger($deal)
    {
        return DB::table('inv_product_inventories as i')
            ->where('i.inv_pro_inv_com_id', $this->company)
            ->where(function ($q) {
                $q->where('i.inv_pro_inv_branch_id', $this->branch)->orWhereNull('i.inv_pro_inv_branch_id');
            })
            ->where('i.inv_pro_inv_status', 1)
            ->where('i.inv_pro_inv_deal_type', $deal);
    }

    /** Bank and cash accounts of the branch: id, name, kind (cash / bank) */
    public function accounts()
    {
        return DB::table('inv_acc_bank_infos as a')
            ->leftJoin('inv_banks as b', 'b.id', '=', 'a.inv_abi_bank_id')
            ->where('a.inv_abi_company_id', $this->company)
            ->where('a.inv_abi_branch_id', $this->branch)
            ->where('a.inv_abi_status', 1)
            ->orderBy('a.inv_abi_account_type', 'desc')->orderBy('a.inv_abi_id')
            ->get(['a.inv_abi_id as id', 'a.inv_abi_account_type as type', 'a.inv_abi_account_name as account_name',
                   'a.inv_abi_account_no as account_no', 'b.bank_name as bank_name'])
            ->map(function ($a) {
                $a->is_cash = (int) $a->type === 2;
                $a->label = $a->is_cash
                    ? ($a->account_name ?: 'Cash')
                    : trim(($a->bank_name ? $a->bank_name . ' - ' : '') . ($a->account_name ?: '') . ($a->account_no ? ' (' . $a->account_no . ')' : ''));
                return $a;
            });
    }

    /** Opening, money in, money out and closing of every account for a period */
    public function accountSummary(Carbon $from, Carbon $to)
    {
        $before = $this->statements()->where('s.inv_abs_transaction_date', '<', $from->toDateString())
            ->groupBy('s.inv_abs_bank_id')->selectRaw('s.inv_abs_bank_id id, SUM(s.inv_abs_credit) - SUM(s.inv_abs_debit) bal')->pluck('bal', 'id');
        $during = $this->statements()->whereBetween('s.inv_abs_transaction_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('s.inv_abs_bank_id')->selectRaw('s.inv_abs_bank_id id, SUM(s.inv_abs_credit) cin, SUM(s.inv_abs_debit) cout')->get()->keyBy('id');
        return $this->accounts()->map(function ($a) use ($before, $during) {
            $a->opening = (float) ($before[$a->id] ?? 0);
            $a->in = (float) optional($during->get($a->id))->cin;
            $a->out = (float) optional($during->get($a->id))->cout;
            $a->closing = $a->opening + $a->in - $a->out;
            return $a;
        });
    }

    /** Names for customers, suppliers and accounts used in a list of movements */
    private function names($rows)
    {
        $customers = DB::table('inv_customers')->whereIn('inv_cus_id', $rows->where('inv_abs_reference_type', 1)->pluck('inv_abs_reference_id')->filter()->unique()->all())->pluck('inv_cus_name', 'inv_cus_id');
        $suppliers = DB::table('inv_suppliers')->whereIn('inv_sup_id', $rows->whereIn('inv_abs_reference_type', [2, 13])->pluck('inv_abs_reference_id')->filter()->unique()->all())
            ->selectRaw("inv_sup_id, COALESCE(NULLIF(inv_sup_com_name, ''), inv_sup_person) n")->pluck('n', 'inv_sup_id');
        return [$customers, $suppliers];
    }

    /** Money movements of a period (optionally one account), with type label, party and account name */
    public function movements(Carbon $from, Carbon $to, $accountId = null)
    {
        $q = $this->statements()->whereBetween('s.inv_abs_transaction_date', [$from->toDateString(), $to->toDateString()]);
        if ($accountId) {
            $q->where('s.inv_abs_bank_id', $accountId);
        }
        $rows = $q->orderBy('s.inv_abs_transaction_date')->orderBy('s.inv_abs_id')->get();
        [$customers, $suppliers] = $this->names($rows);
        $accounts = $this->accounts()->keyBy('id');
        return $rows->map(function ($r) use ($customers, $suppliers, $accounts) {
            $r->type_label = Inv_acc_bank_statement::getReferenceTypeLabel($r);
            $r->party = (int) $r->inv_abs_reference_type === 1 ? ($customers[$r->inv_abs_reference_id] ?? '')
                : (in_array((int) $r->inv_abs_reference_type, [2, 13], true) ? ($suppliers[$r->inv_abs_reference_id] ?? '') : '');
            $r->account = optional($accounts->get($r->inv_abs_bank_id))->label ?: ('#' . $r->inv_abs_bank_id);
            $r->in = (float) $r->inv_abs_credit;
            $r->out = (float) $r->inv_abs_debit;
            return $r;
        });
    }

    /** Sales and purchase invoices of a period (they change dues, not cash) */
    public function invoices(Carbon $from, Carbon $to)
    {
        $range = [$from->toDateString(), $to->toDateString()];
        $sales = $this->ledger(2)->whereIn('i.inv_pro_inv_tran_type', [1, 10, 11, 12])->whereBetween('i.inv_pro_inv_issue_date', $range)
            ->whereNotNull('i.inv_pro_inv_invoice_no')->groupBy('i.inv_pro_inv_invoice_no')
            ->selectRaw("'Sale' kind, i.inv_pro_inv_invoice_no invoice, MIN(i.inv_pro_inv_issue_date) d, MAX(i.inv_pro_inv_party_id) party,
                SUM(i.inv_pro_inv_debit) - SUM(CASE WHEN i.inv_pro_inv_tran_type = 12 THEN i.inv_pro_inv_credit ELSE 0 END) amount")->get();
        $buys = $this->ledger(1)->where('i.inv_pro_inv_tran_type', 1)->whereBetween('i.inv_pro_inv_issue_date', $range)
            ->whereNotNull('i.inv_pro_inv_invoice_no')->groupBy('i.inv_pro_inv_invoice_no')
            ->selectRaw("'Purchase' kind, i.inv_pro_inv_invoice_no invoice, MIN(i.inv_pro_inv_issue_date) d, MAX(i.inv_pro_inv_party_id) party, SUM(i.inv_pro_inv_debit) amount")->get();
        $cus = DB::table('inv_customers')->whereIn('inv_cus_id', $sales->pluck('party')->filter()->all())->pluck('inv_cus_name', 'inv_cus_id');
        $sup = DB::table('inv_suppliers')->whereIn('inv_sup_id', $buys->pluck('party')->filter()->all())
            ->selectRaw("inv_sup_id, COALESCE(NULLIF(inv_sup_com_name, ''), inv_sup_person) n")->pluck('n', 'inv_sup_id');
        return $sales->map(function ($r) use ($cus) { $r->party_name = $cus[$r->party] ?? 'Walk-in customer'; $r->amount = (float) $r->amount; return $r; })
            ->concat($buys->map(function ($r) use ($sup) { $r->party_name = $sup[$r->party] ?? ''; $r->amount = (float) $r->amount; return $r; }))
            ->sortBy(function ($r) { return $r->d . ' ' . $r->invoice; })->values();
    }

    // ------------------------------------------------------------------ depreciation (calculated, not cash)

    /** Fixed assets of the branch */
    public function assets()
    {
        return DB::table('inv_asset_fixed')->where('asset_com_id', $this->company)->where('asset_branch_id', $this->branch)
            ->orderBy('asset_purchase_date')->orderBy('asset_id')->get();
    }

    /**
     * Depreciation of one asset per month ['Ym' => amount] from its purchase month up to $until.
     * Straight line: price x rate / 12 every month until the price is used up.
     * Declining balance: what is left x rate / 12 every month. Units of production needs usage figures: null.
     */
    public function assetDepreciationByMonth($a, Carbon $until)
    {
        $cost = (float) $a->asset_purchase_price;
        $rate = (float) $a->asset_depreciation_rate;
        $method = $a->asset_depreciation_method ?: 'straight_line';
        if ($method === 'units_of_production') {
            return null;
        }
        if ($cost <= 0 || $rate <= 0 || !$a->asset_purchase_date || $a->asset_purchase_date === '0000-00-00') {
            return [];
        }
        $month = Carbon::parse($a->asset_purchase_date)->startOfMonth();
        $book = $cost;
        $out = [];
        for ($i = 0; $i < 600 && $month->lte($until) && $book > 0.005; $i++, $month->addMonth()) {
            $dep = $method === 'declining_balance' ? $book * $rate / 1200 : min($book, $cost * $rate / 1200);
            $dep = round($dep, 2);
            $book -= $dep;
            $out[$month->format('Ym')] = $dep;
        }
        return $out;
    }

    /** Total depreciation of the branch's assets for the months a period touches (disposed assets left out) */
    public function depreciation(Carbon $from, Carbon $to, $byMonth = false)
    {
        $first = (int) $from->format('Ym');
        $last = (int) $to->format('Ym');
        $months = [];
        foreach ($this->assets() as $a) {
            if ((int) $a->asset_status === 3) {
                continue;
            }
            foreach ((array) $this->assetDepreciationByMonth($a, $to->copy()->endOfMonth()) as $ym => $dep) {
                if ((int) $ym >= $first && (int) $ym <= $last) {
                    $months[(int) $ym] = ($months[(int) $ym] ?? 0) + $dep;
                }
            }
        }
        return $byMonth ? $months : round(array_sum($months), 2);
    }

    /** Depreciation schedule: each asset's price, this year's depreciation, depreciation so far and value now */
    public function depreciationSchedule(Carbon $asOf)
    {
        $yearStart = (int) $asOf->copy()->startOfYear()->format('Ym');
        return $this->assets()->map(function ($a) use ($asOf, $yearStart) {
            $plan = $this->assetDepreciationByMonth($a, $asOf->copy()->endOfMonth());
            $a->calculated = $plan !== null;
            $a->so_far = $plan ? round(array_sum($plan), 2) : 0.0;
            $a->this_year = $plan ? round(array_sum(array_filter($plan, function ($v, $k) use ($yearStart) { return (int) $k >= $yearStart; }, ARRAY_FILTER_USE_BOTH)), 2) : 0.0;
            $a->monthly_now = $plan ? (float) (end($plan) ?: 0) : 0.0;
            $a->book_value = round((float) $a->asset_purchase_price - $a->so_far, 2);
            $a->disposed = (int) $a->asset_status === 3;
            return $a;
        });
    }

    // ------------------------------------------------------------------ profit and loss

    /**
     * Income and expense lines of journal vouchers (Accounts > Journal Vouchers) in a period:
     * type (income / expense), head (account name), m (month), amount (income: credits less debits, expense: debits less credits).
     */
    public function journalVoucherLines(Carbon $from, Carbon $to)
    {
        static $hasTables = null;
        if ($hasTables === null) {
            $hasTables = \Illuminate\Support\Facades\Schema::hasTable('acc_journal_lines') && \Illuminate\Support\Facades\Schema::hasTable('acc_journals');
        }
        if (!$hasTables) {          // Phase 3a tables not made yet: nothing to add
            return collect();
        }
        $rows = DB::table('acc_journal_lines as l')->join('acc_journals as j', 'j.aj_id', '=', 'l.ajl_journal_id')
            ->where('j.aj_company_id', $this->company)->where('j.aj_branch_id', $this->branch)->where('j.aj_status', 'posted')
            ->whereBetween('j.aj_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('l.ajl_account_key', 'm')
            ->selectRaw('l.ajl_account_key k, MONTH(j.aj_date) m, SUM(l.ajl_debit) dr, SUM(l.ajl_credit) cr')->get();
        if ($rows->isEmpty()) {
            return collect();
        }
        $journal = new AccountsJournal($this->company, $this->branch);
        return $rows->map(function ($r) use ($journal) {
            $a = $journal->account($r->k);
            if (!in_array($a->type, ['income', 'expense'], true)) {
                return null;
            }
            $head = preg_replace('/ \(ledger\)$/', '', $a->name);       // ledger heads are named as elsewhere in the reports
            $amount = $a->type === 'income' ? (float) $r->cr - (float) $r->dr : (float) $r->dr - (float) $r->cr;
            return (object) ['type' => $a->type, 'head' => $head, 'm' => (int) $r->m, 'amount' => $amount];
        })->filter()->values();
    }

    /** Expense lines of a period grouped by expense head (expense entries, vouchers and ledger cash-outs) */
    public function expenseLines(Carbon $from, Carbon $to)
    {
        $rows = $this->statements()->whereIn('s.inv_abs_reference_type', self::EXPENSE_TYPES)
            ->whereBetween('s.inv_abs_transaction_date', [$from->toDateString(), $to->toDateString()])
            ->leftJoin('inv_acc_expenses as e', function ($j) {
                $j->on('e.inv_acc_exp_id', '=', 's.inv_abs_reference_id')->whereIn('s.inv_abs_reference_type', [3, 4]);
            })
            ->leftJoin('inv_acc_expense_categories as c', 'c.inv_acc_exp_cat_category_id', '=', 'e.inv_acc_exp_category_id')
            ->leftJoin('inv_product_inventories as li', function ($j) {
                $j->on('li.inv_pro_inv_id', '=', 's.inv_abs_inventory_id')->where('s.inv_abs_reference_type', 9);
            })
            ->leftJoin('inv_ledgers as l', 'l.inv_ledg_id', '=', 'li.inv_pro_inv_exp_id')
            // a ledger cash out to a supplier pays that supplier (it lowers what you owe), so it is not an expense
            ->where(function ($q) { $q->whereNull('li.inv_pro_inv_deal_type')->orWhere('li.inv_pro_inv_deal_type', '<>', 1); })
            ->selectRaw("COALESCE(c.inv_acc_exp_cat_category_name, l.inv_ledg_ledger_name, 'Other expenses') head,
                MONTH(s.inv_abs_transaction_date) m, SUM(s.inv_abs_debit) - SUM(s.inv_abs_credit) amount")
            ->groupBy('head', 'm')->get();
        // expenses entered with journal vouchers (accruals, adjustments) count under their account
        $journal = $this->journalVoucherLines($from, $to)->where('type', 'expense')
            ->map(function ($r) { return (object) ['head' => $r->head, 'm' => $r->m, 'amount' => $r->amount]; });
        return $rows->concat($journal)->values();
    }

    public function profitLoss(Carbon $from, Carbon $to, $withSalary = true)
    {
        $range = [$from->toDateString(), $to->toDateString()];
        $sum = function ($deal, $types, $col) use ($range) {
            return (float) $this->ledger($deal)->whereIn('i.inv_pro_inv_tran_type', $types)->whereBetween('i.inv_pro_inv_issue_date', $range)->sum('i.' . $col);
        };

        $sales = $sum(2, [1], 'inv_pro_inv_debit');
        $returns = $sum(2, [2], 'inv_pro_inv_credit');
        $discounts = $sum(2, [12, 6], 'inv_pro_inv_credit');       // invoice discounts and discounts given on payments
        $service = $sum(2, [10], 'inv_pro_inv_debit');
        $delivery = $sum(2, [11], 'inv_pro_inv_debit');

        // cost of goods sold: quantity sold x buying price of that product (returns put the cost back)
        $cost = function ($tranType) use ($range) {
            return (float) $this->ledger(2)->where('i.inv_pro_inv_tran_type', $tranType)->whereBetween('i.inv_pro_inv_issue_date', $range)
                ->join('inv_product_details as d', 'd.inv_pro_det_id', '=', 'i.inv_pro_inv_project_id')
                ->sum(DB::raw('COALESCE(i.inv_pro_inv_qty, 0) * COALESCE(d.inv_pro_det_buy_price, 0)'));
        };
        $cogs = $cost(1) - $cost(2);

        $statementSum = function ($types, $col) use ($range) {
            return (float) $this->statements()->whereIn('s.inv_abs_reference_type', $types)->whereBetween('s.inv_abs_transaction_date', $range)->sum('s.' . $col);
        };
        // ledger "cash in" (type 8 money out is a loan payment); cash in from a customer is that customer paying, not income
        $otherIncome = (float) $this->statements()->where('s.inv_abs_reference_type', 8)->whereBetween('s.inv_abs_transaction_date', $range)
            ->leftJoin('inv_product_inventories as li', 'li.inv_pro_inv_id', '=', 's.inv_abs_inventory_id')
            ->where(function ($q) { $q->whereNull('li.inv_pro_inv_deal_type')->orWhere('li.inv_pro_inv_deal_type', '<>', 2); })
            ->sum('s.inv_abs_credit');
        // discounts taken on supplier payments (supplier ledger "discount" rows that are not a ledger cash out to the supplier)
        $purchaseDiscount = (float) $this->ledger(1)->where('i.inv_pro_inv_tran_type', 6)->whereBetween('i.inv_pro_inv_issue_date', $range)
            ->whereRaw('COALESCE(i.inv_pro_inv_exp_id, 0) = 0')->sum(DB::raw('COALESCE(i.inv_pro_inv_credit, 0) - COALESCE(i.inv_pro_inv_debit, 0)'));
        $bankCharges = $statementSum([11], 'inv_abs_debit');
        $omsBills = $statementSum([10], 'inv_abs_debit');
        $loanInterest = (float) DB::table('inv_acc_bank_loan_payments')->where('inv_ablp_company_id', $this->company)
            ->where('inv_ablp_branch_id', $this->branch)->where('inv_ablp_status', 1)
            ->whereBetween('inv_ablp_payment_date', $range)->sum('inv_ablp_interest_amount');
        $salary = $withSalary ? (float) DB::table('emp_salary_generates')->where('company_id', $this->company)->where('branch_id', $this->branch)
            ->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [(int) $from->format('Ym'), (int) $to->format('Ym')])->sum('final_salary') : 0.0;

        $depreciation = $this->depreciation($from, $to);
        $heads = $this->expenseLines($from, $to)->groupBy('head')->map(function ($g) { return (float) $g->sum('amount'); })->filter(function ($v) { return abs($v) > 0.004; })->sortDesc();

        // income entered with journal vouchers, by account
        $jvIncome = $this->journalVoucherLines($from, $to)->where('type', 'income')->groupBy('head')
            ->map(function ($g) { return (float) $g->sum('amount'); })->filter(function ($v) { return abs($v) > 0.004; });
        $jvIncomeTotal = (float) $jvIncome->sum();

        // VAT inside sales and purchase prices, when VAT is on (the same row-by-row amounts as the journal)
        $vatOnSales = 0.0;
        $vatRebate = 0.0;
        if ($vat = AccountsSettings::vat($this->company)) {
            $vatSum = function ($deal, $trans, $rate) use ($range, $vat) {
                return round((float) $this->ledger($deal)->whereIn('i.inv_pro_inv_tran_type', $trans)->whereBetween('i.inv_pro_inv_issue_date', $range)
                    ->when($vat['vat_from'], function ($q) use ($vat) { $q->where('i.inv_pro_inv_issue_date', '>=', $vat['vat_from']); })
                    ->sum(DB::raw(AccountsJournal::vatSql($rate))), 2);
            };
            $vatOnSales = $vatSum(2, AccountsJournal::VAT_SALE_TRANS, $vat['vat_rate']);
            $vatRebate = $vat['vat_rebate_rate'] > 0 ? $vatSum(1, AccountsJournal::VAT_BUY_TRANS, $vat['vat_rebate_rate']) : 0.0;
        }

        $netSales = $sales - $returns - $discounts;
        $revenue = $netSales + $service + $delivery - $vatOnSales;
        $gross = $revenue - $cogs;
        $operating = $heads->sum() + $bankCharges + $omsBills + $loanInterest + $salary + $depreciation;
        $net = $gross + $otherIncome + $purchaseDiscount + $jvIncomeTotal + $vatRebate - $operating;
        $totalIncome = $revenue + $otherIncome + $purchaseDiscount + $jvIncomeTotal + $vatRebate;

        return compact('sales', 'returns', 'discounts', 'netSales', 'service', 'delivery', 'revenue', 'cogs', 'gross',
            'otherIncome', 'purchaseDiscount', 'jvIncome', 'jvIncomeTotal', 'vatOnSales', 'vatRebate', 'totalIncome', 'heads', 'bankCharges', 'omsBills', 'loanInterest', 'salary', 'depreciation', 'operating', 'net');
    }

    // ------------------------------------------------------------------ dues by age

    /**
     * Customer (deal 2) or supplier (deal 1) dues as of a date, split by age. Payments, returns and discounts are
     * set against the oldest bills first, so what is left is the unpaid part of the newest bills.
     */
    public function aging($deal, Carbon $asOf)
    {
        $rows = $this->ledger($deal)->where('i.inv_pro_inv_issue_date', '<=', $asOf->toDateString())
            ->whereNotNull('i.inv_pro_inv_party_id')
            ->orderBy('i.inv_pro_inv_issue_date')->orderBy('i.inv_pro_inv_id')
            ->get(['i.inv_pro_inv_party_id as party', 'i.inv_pro_inv_issue_date as d', 'i.inv_pro_inv_debit as dr', 'i.inv_pro_inv_credit as cr']);

        $parties = [];
        foreach ($rows->groupBy('party') as $party => $list) {
            $bills = [];
            $paid = 0.0;
            foreach ($list as $r) {
                if ((float) $r->dr > 0) { $bills[] = ['d' => $r->d, 'left' => (float) $r->dr]; }
                $paid += (float) $r->cr;
            }
            foreach ($bills as &$b) {               // oldest bills are paid first
                $use = min($b['left'], $paid);
                $b['left'] -= $use;
                $paid -= $use;
            }
            unset($b);
            $buckets = ['b0' => 0.0, 'b30' => 0.0, 'b60' => 0.0, 'b90' => 0.0];
            $oldest = null;
            foreach ($bills as $b) {
                if ($b['left'] <= 0.004) { continue; }
                $days = Carbon::parse($b['d'])->diffInDays($asOf);
                $key = $days <= 30 ? 'b0' : ($days <= 60 ? 'b30' : ($days <= 90 ? 'b60' : 'b90'));
                $buckets[$key] += $b['left'];
                $oldest = $oldest === null ? $days : max($oldest, $days);
            }
            $total = array_sum($buckets);
            if ($total > 0.004 || $paid > 0.004) {
                $parties[] = (object) array_merge(['party' => (int) $party, 'total' => $total, 'advance' => $paid, 'oldest_days' => $oldest], $buckets);
            }
        }

        $ids = array_column($parties, 'party');
        $names = $deal == 2
            ? DB::table('inv_customers')->whereIn('inv_cus_id', $ids)->selectRaw("inv_cus_id id, inv_cus_name n, inv_cus_mobile m")->get()->keyBy('id')
            : DB::table('inv_suppliers')->whereIn('inv_sup_id', $ids)->selectRaw("inv_sup_id id, COALESCE(NULLIF(inv_sup_com_name, ''), inv_sup_person) n, inv_sup_mobile m")->get()->keyBy('id');
        foreach ($parties as $p) {
            $p->name = optional($names->get($p->party))->n ?: ('#' . $p->party);
            $p->mobile = optional($names->get($p->party))->m;
        }
        usort($parties, function ($a, $b) { return $b->total <=> $a->total; });
        $totals = ['b0' => 0.0, 'b30' => 0.0, 'b60' => 0.0, 'b90' => 0.0, 'total' => 0.0, 'advance' => 0.0];
        foreach ($parties as $p) {
            foreach ($totals as $k => $v) { $totals[$k] += $p->$k; }
        }
        return ['rows' => $parties, 'totals' => $totals];
    }

    // ------------------------------------------------------------------ expense analysis

    /** Expense heads x months of a year, plus bank charges, office (OMS) bills and loan interest */
    public function expenseYear($year)
    {
        $from = Carbon::create($year, 1, 1);
        $to = Carbon::create($year, 12, 31);
        $grid = [];
        foreach ($this->expenseLines($from, $to) as $r) {
            $grid[$r->head][(int) $r->m] = ($grid[$r->head][(int) $r->m] ?? 0) + (float) $r->amount;
        }
        $monthly = function ($query, $dateCol, $col) {
            return $query->selectRaw("MONTH($dateCol) m, SUM($col) v")->groupBy('m')->pluck('v', 'm')->map(function ($v) { return (float) $v; })->all();
        };
        $range = [$from->toDateString(), $to->toDateString()];
        $extra = [
            'Bank service charges' => $monthly($this->statements()->where('s.inv_abs_reference_type', 11)->whereBetween('s.inv_abs_transaction_date', $range), 's.inv_abs_transaction_date', 's.inv_abs_debit'),
            'Office bills (OMS)'   => $monthly($this->statements()->where('s.inv_abs_reference_type', 10)->whereBetween('s.inv_abs_transaction_date', $range), 's.inv_abs_transaction_date', 's.inv_abs_debit'),
            'Loan interest'        => $monthly(DB::table('inv_acc_bank_loan_payments')->where('inv_ablp_company_id', $this->company)->where('inv_ablp_branch_id', $this->branch)
                ->where('inv_ablp_status', 1)->whereBetween('inv_ablp_payment_date', $range), 'inv_ablp_payment_date', 'inv_ablp_interest_amount'),
        ];
        $dep = [];
        foreach ($this->depreciation($from, $to, true) as $ym => $v) {
            $dep[(int) substr((string) $ym, 4, 2)] = $v;
        }
        $extra['Depreciation (not cash)'] = $dep;
        foreach ($extra as $head => $months) {
            if (array_sum($months) > 0.004) { $grid[$head] = $months; }
        }
        $rows = [];
        foreach ($grid as $head => $months) {
            $line = [];
            for ($m = 1; $m <= 12; $m++) { $line[$m] = (float) ($months[$m] ?? 0); }
            $rows[] = (object) ['head' => $head, 'months' => $line, 'total' => array_sum($line)];
        }
        usort($rows, function ($a, $b) { return $b->total <=> $a->total; });
        $monthTotals = [];
        for ($m = 1; $m <= 12; $m++) { $monthTotals[$m] = array_sum(array_map(function ($r) use ($m) { return $r->months[$m]; }, $rows)); }
        return ['rows' => $rows, 'monthTotals' => $monthTotals, 'total' => array_sum($monthTotals)];
    }
}

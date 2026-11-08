<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accounts Phase 3a: the double-entry journal of a branch.
 *
 * Nothing that exists is copied or changed. Every record the ERP already keeps is read as a balanced journal entry:
 *  - each bank / cash movement (inv_acc_bank_statements): the bank or cash account against what it was for;
 *  - each customer / supplier ledger row (inv_product_inventories, deal 2 / 1): the customer or supplier against
 *    sales, purchases, returns, discounts or opening balances;
 *  - the cost of each sale (quantity x buying price): cost of goods sold against stock;
 *  - payroll (emp_salary_generates): salaries against salary payable, at the end of the month;
 *  - fixed assets and their depreciation (inv_asset_fixed);
 *  - journal vouchers entered by hand (acc_journals / acc_journal_lines).
 * A payment kept both in the customer / supplier ledger and in bank / cash meets in a "to match" account, which is
 * zero when both sides agree, so customer, supplier, bank and cash balances are exactly the ones the ERP shows elsewhere.
 *
 * Accounts are named by key: a system key ('ar', 'sales' ...), 'coa:<id>' for accounts added to the chart of accounts,
 * 'bank:<id>' for each bank / cash account, 'exp:cat:<id>' / 'exp:ledg:<id>' / 'exp:other' for expense heads and
 * 'inc:ledg:<id>' for ledger income heads.
 */
class AccountsJournal
{
    public $company;
    public $branch;

    const TYPES = ['asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity', 'income' => 'Income', 'expense' => 'Expenses'];
    const DEBIT_NORMAL = ['asset', 'expense'];
    const FY_START_MONTH = 7;      // Bangladesh: July to June

    /** System accounts: key => [code, name, type, can be used in a journal voucher, what fills it] */
    const SYSTEM = [
        'cash_bank'         => ['1100', 'Cash & Bank', 'asset', false, 'Each bank, mobile banking and cash account (Accounts > Bank)'],
        'ar'                => ['1200', 'Customers (Accounts Receivable)', 'asset', false, 'Sales, service and delivery charges less payments, returns and discounts'],
        'stock'             => ['1300', 'Stock (Inventory)', 'asset', true, 'Purchases at cost less the cost of goods sold'],
        'lc_costs'          => ['1350', 'LC Import Costs', 'asset', true, 'LC payments other than margin and retirement (duty, insurance, charges)'],
        'fixed_assets'      => ['1400', 'Fixed Assets (at cost)', 'asset', true, 'Fixed Assets register (disposed assets left out)'],
        'acc_dep'           => ['1450', 'Less: Accumulated Depreciation', 'asset', true, 'Depreciation of the fixed assets so far'],
        'clr_customer'      => ['1900', 'Customer Payments to Match', 'asset', false, 'Zero when every customer payment is both in the customer ledger and in bank / cash'],
        'clr_supplier'      => ['1910', 'Supplier Payments to Match', 'asset', false, 'Zero when every supplier payment is both in the supplier ledger and in bank / cash'],
        'transfers'         => ['1920', 'Transfers between Accounts', 'asset', false, 'Zero when each contra / loan transfer left one account and reached another'],
        'suspense'          => ['1990', 'Suspense', 'asset', true, 'Movements the ERP could not place'],
        'ap'                => ['2100', 'Suppliers (Accounts Payable)', 'liability', false, 'Purchases less payments, returns and discounts'],
        'salary_payable'    => ['2200', 'Salary Payable', 'liability', true, 'Payroll salaries less salary payments'],
        'loans'             => ['2300', 'Bank Loans', 'liability', true, 'Loans received less principal repaid'],
        'vat_payable'       => ['2500', 'VAT Payable', 'liability', true, 'VAT on sales less rebate on purchases, less VAT paid (Accounts > VAT)'],
        'capital'           => ['3100', "Owner's Capital (Bank Deposits)", 'equity', true, 'Money deposited from outside (Accounts > Bank > Deposit)'],
        'capital_assets'    => ['3150', 'Capital: Fixed Assets Brought In', 'equity', true, 'Cost of the assets in the Fixed Assets register'],
        'drawings'          => ['3200', "Owner's Drawings (Bank Withdrawals)", 'equity', true, 'Money taken out (Accounts > Bank > Withdraw)'],
        'obe'               => ['3300', 'Opening Balances & Adjustments', 'equity', true, 'Opening balances of customers and suppliers, and balance adjustments'],
        'retained'          => ['3900', 'Retained Earnings', 'equity', false, 'Profit of the closed fiscal years (Accounts > Fiscal Years)'],
        'sales'             => ['4100', 'Product Sales', 'income', false, 'Sales invoices'],
        'sales_returns'     => ['4110', 'Less: Sales Returns', 'income', false, 'Sales returns'],
        'sales_discount'    => ['4120', 'Less: Discounts Given', 'income', false, 'Invoice discounts and discounts given on payments'],
        'vat_on_sales'      => ['4130', 'Less: VAT on Sales', 'income', false, 'VAT inside sales prices, when VAT is on (Accounts > VAT)'],
        'service_income'    => ['4200', 'Service / Installation Income', 'income', false, 'Service charges on invoices'],
        'delivery_income'   => ['4300', 'Delivery Charges', 'income', false, 'Delivery charges on invoices'],
        'other_income'      => ['4500', 'Other Income', 'income', true, 'Ledger cash in'],
        'purchase_discount' => ['4600', 'Discount Received from Suppliers', 'income', true, 'Discounts taken on supplier payments'],
        'vat_rebate'        => ['4650', 'VAT Rebate on Purchases', 'income', false, 'VAT inside purchase prices that is set off, when VAT is on'],
        'cogs'              => ['5100', 'Cost of Goods Sold', 'expense', false, 'Quantity sold x buying price'],
        'opex'              => ['6100', 'Operating Expenses', 'expense', false, 'Expense heads (Add Expense, Expense Vouchers) and ledger cash out'],
        'salary_exp'        => ['6200', 'Salaries', 'expense', true, 'Payroll (Salary Generate)'],
        'oms'               => ['6300', 'Office Bills (OMS)', 'expense', true, 'OMS bill payments'],
        'bank_charges'      => ['6400', 'Bank Service Charges', 'expense', true, 'Bank service charges'],
        'interest'          => ['6500', 'Loan Interest', 'expense', true, 'Interest part of loan payments'],
        'depreciation'      => ['6600', 'Depreciation', 'expense', true, 'Fixed assets, each month'],
    ];

    /** Customer ledger rows that carry VAT (sales, service, delivery; returns and discounts take it back) and supplier rows (purchases, returns) */
    const VAT_SALE_TRANS = [1, 10, 11, 2, 12, 6];
    const VAT_BUY_TRANS = [1, 2];

    /** VAT inside an amount that includes VAT, per ledger row, in paisa: amount x rate / (100 + rate) */
    public static function vatSql($rate, $alias = 'i')
    {
        $r = rtrim(rtrim(number_format((float) $rate, 4, '.', ''), '0'), '.');
        return "ROUND((COALESCE($alias.inv_pro_inv_debit, 0) - COALESCE($alias.inv_pro_inv_credit, 0)) * $r / (100 + $r), 2)";
    }

    /** Accounts that only change through their own pages, so the balances shown everywhere else stay the same */
    const NOT_IN_VOUCHERS = 'Cash & bank, customers, suppliers, the "to match" accounts, sales and cost of goods sold';

    private $catalogue;

    public function __construct($companyId, $branchId)
    {
        $this->company = (int) $companyId;
        $this->branch = (int) $branchId;
    }

    // ------------------------------------------------------------------ chart of accounts

    /** Adds the system accounts the company does not have yet (new rows in acc_coa only) */
    public function ensureSystemAccounts()
    {
        $have = DB::table('acc_coa')->where('coa_company_id', $this->company)->whereNotNull('coa_system_key')->pluck('coa_system_key')->all();
        $missing = array_diff(array_keys(self::SYSTEM), $have);
        if (!$missing) {
            return;
        }
        $codes = DB::table('acc_coa')->where('coa_company_id', $this->company)->pluck('coa_code')->map(function ($c) { return strtolower($c); })->all();
        $now = Carbon::now();
        foreach ($missing as $key) {
            [$code, $name, $type, $manual, $note] = self::SYSTEM[$key];
            $use = $code;
            for ($n = 1; in_array(strtolower($use), $codes, true); $n++) {
                $use = $code . '-S' . $n;
            }
            $codes[] = strtolower($use);
            DB::table('acc_coa')->insertOrIgnore([
                'coa_company_id' => $this->company, 'coa_code' => $use, 'coa_name' => $name, 'coa_type' => $type,
                'coa_system_key' => $key, 'coa_manual' => $manual ? 1 : 0, 'coa_status' => 1, 'coa_note' => $note,
                'coa_created_by' => optional(Auth::user())->au_id, 'coa_created_at' => $now,
            ]);
        }
        $this->catalogue = null;
    }

    /**
     * Every account the journal can use, keyed by account key:
     * key, code, name, type, parent (key), system, manual, active, note, coa_id, depth.
     */
    public function accounts()
    {
        if ($this->catalogue) {
            return $this->catalogue;
        }
        $this->ensureSystemAccounts();
        $rows = DB::table('acc_coa')->where('coa_company_id', $this->company)->orderBy('coa_code')->get();
        $keyOf = [];
        foreach ($rows as $r) {
            $keyOf[$r->coa_id] = $r->coa_system_key ?: 'coa:' . $r->coa_id;
        }
        $list = [];
        foreach ($rows as $r) {
            $key = $keyOf[$r->coa_id];
            $list[$key] = (object) [
                'key' => $key, 'code' => $r->coa_code, 'name' => $r->coa_name, 'type' => $r->coa_type,
                'parent' => $r->coa_parent_id ? ($keyOf[$r->coa_parent_id] ?? null) : null,
                'system' => (bool) $r->coa_system_key, 'manual' => (bool) $r->coa_manual, 'active' => (int) $r->coa_status === 1,
                'note' => $r->coa_note, 'coa_id' => $r->coa_id, 'dynamic' => false,
            ];
        }
        $add = function ($key, $parent, $name, $manual, $active = true) use (&$list) {
            $p = $list[$parent] ?? null;
            $list[$key] = (object) [
                'key' => $key, 'code' => '', 'name' => $name, 'type' => $p ? $p->type : 'asset', 'parent' => $parent,
                'system' => true, 'manual' => $manual, 'active' => $active, 'note' => null, 'coa_id' => null, 'dynamic' => true,
            ];
        };

        // each bank / cash account
        $banks = DB::table('inv_acc_bank_infos as a')->leftJoin('inv_banks as b', 'b.id', '=', 'a.inv_abi_bank_id')
            ->where('a.inv_abi_company_id', $this->company)->where('a.inv_abi_branch_id', $this->branch)
            ->orderBy('a.inv_abi_account_type', 'desc')->orderBy('a.inv_abi_id')
            ->get(['a.inv_abi_id', 'a.inv_abi_account_type', 'a.inv_abi_account_name', 'a.inv_abi_account_no', 'a.inv_abi_status', 'b.bank_name']);
        foreach ($banks as $a) {
            $name = (int) $a->inv_abi_account_type === 2
                ? 'Cash: ' . ($a->inv_abi_account_name ?: 'Cash')
                : trim(($a->bank_name ? $a->bank_name . ' - ' : '') . $a->inv_abi_account_name . ($a->inv_abi_account_no ? ' (' . $a->inv_abi_account_no . ')' : ''));
            $add('bank:' . $a->inv_abi_id, 'cash_bank', $name, false, (int) $a->inv_abi_status === 1);
        }
        // expense heads and ledger heads
        $cats = DB::table('inv_acc_expense_categories')->where('inv_acc_exp_cat_company_id', $this->company)
            ->where('inv_acc_exp_cat_branch_id', $this->branch)->orderBy('inv_acc_exp_cat_category_name')
            ->get(['inv_acc_exp_cat_category_id as id', 'inv_acc_exp_cat_category_name as name', 'inv_acc_exp_cat_status as status']);
        foreach ($cats as $c) {
            $add('exp:cat:' . $c->id, 'opex', $c->name, true, (int) $c->status === 1);
        }
        $ledgers = DB::table('inv_ledgers')->where('inv_ledg_company_id', $this->company)->orderBy('inv_ledg_ledger_name')
            ->get(['inv_ledg_id as id', 'inv_ledg_ledger_name as name', 'inv_ledg_status as status']);
        foreach ($ledgers as $l) {
            $add('exp:ledg:' . $l->id, 'opex', $l->name . ' (ledger)', true, (int) $l->status === 1);
            $add('inc:ledg:' . $l->id, 'other_income', $l->name . ' (ledger)', true, (int) $l->status === 1);
        }
        $add('exp:other', 'opex', 'Other expenses', true);

        // codes for the accounts under a group: group code + number
        $count = [];
        foreach ($list as $a) {
            if ($a->dynamic && isset($list[$a->parent])) {
                $count[$a->parent] = ($count[$a->parent] ?? 0) + 1;
                $a->code = $list[$a->parent]->code . '-' . str_pad($count[$a->parent], 2, '0', STR_PAD_LEFT);
            }
        }
        return $this->catalogue = collect($list);
    }

    /** The account of a key, also for one that no longer exists */
    public function account($key)
    {
        return $this->accounts()->get($key) ?: (object) [
            'key' => $key, 'code' => '', 'name' => $key, 'type' => 'asset', 'parent' => null, 'system' => false,
            'manual' => false, 'active' => false, 'note' => null, 'coa_id' => null, 'dynamic' => true,
        ];
    }

    /** Accounts a journal voucher can use */
    public function voucherAccounts()
    {
        $all = $this->accounts();
        return $all->filter(function ($a) use ($all) {
            if (!$a->manual || !$a->active) {
                return false;
            }
            return !$a->parent || !isset($all[$a->parent]) || $a->dynamic || $all[$a->parent]->active;
        });
    }

    // ------------------------------------------------------------------ postings (every journal line)

    private function str($expr)
    {
        return "CONVERT($expr USING utf8mb4) COLLATE utf8mb4_unicode_ci";
    }

    private function cols($d, $eid, $kind, $ref, $narr, $party, $akey, $dr, $cr)
    {
        return "$d AS d, " . $this->str($eid) . " AS eid, " . $this->str($kind) . " AS kind, " . $this->str($ref) . " AS ref, "
            . $this->str($narr) . " AS narr, $party AS party, " . $this->str($akey) . " AS akey, $dr AS dr, $cr AS cr";
    }

    /**
     * All journal lines from the database between two dates (from = null: since the start):
     * d (date), eid (entry), kind (what made it), ref, narr, party, akey (account), dr, cr.
     */
    public function postingsQuery($from, $to)
    {
        $c = $this->company;
        $b = $this->branch;
        $from = $from ? Carbon::parse($from)->toDateString() : null;
        $to = Carbon::parse($to)->toDateString();

        // -- bank and cash movements
        $st = function () use ($c, $b, $from, $to) {
            return DB::table('inv_acc_bank_statements as s')->where('s.inv_abs_company_id', $c)->where('s.inv_abs_branch_id', $b)
                ->where('s.inv_abs_status', 1)
                ->whereRaw('(COALESCE(s.inv_abs_credit, 0) <> 0 OR COALESCE(s.inv_abs_debit, 0) <> 0)')
                ->when($from, function ($q) use ($from) { $q->where('s.inv_abs_transaction_date', '>=', $from); })
                ->where('s.inv_abs_transaction_date', '<=', $to);
        };
        $loanPay = DB::table('inv_acc_bank_loan_payments')->where('inv_ablp_company_id', $c)->where('inv_ablp_status', 1)
            ->whereNotNull('inv_ablp_bank_statement_id')->groupBy('inv_ablp_bank_statement_id')
            ->selectRaw('inv_ablp_bank_statement_id AS sid, SUM(inv_ablp_interest_amount) AS interest');
        $sCols = function ($akey, $dr, $cr) {
            return $this->cols('s.inv_abs_transaction_date', "CONCAT('s', s.inv_abs_id)", "CONCAT('S', s.inv_abs_reference_type)",
                's.inv_abs_voucher_no', 's.inv_abs_description', 's.inv_abs_reference_id', $akey, $dr, $cr);
        };
        $q = $st()->selectRaw($sCols("CONCAT('bank:', s.inv_abs_bank_id)", 'COALESCE(s.inv_abs_credit, 0)', 'COALESCE(s.inv_abs_debit, 0)'));

        $expCat = "IF(COALESCE(e.inv_acc_exp_category_id, 0) = 0, 'exp:other', CONCAT('exp:cat:', e.inv_acc_exp_category_id))";
        $lcGoods = "EXISTS (SELECT 1 FROM inv_product_inventories g WHERE g.inv_pro_inv_com_id = s.inv_abs_company_id AND g.inv_pro_inv_deal_type = 1
                    AND g.inv_pro_inv_tran_type = 4 AND g.inv_pro_inv_status = 1 AND g.inv_pro_inv_invoice_no = s.inv_abs_voucher_no)";
        $counter = "CASE s.inv_abs_reference_type
            WHEN 1 THEN 'clr_customer'
            WHEN 2 THEN 'clr_supplier'
            WHEN 3 THEN $expCat
            WHEN 4 THEN $expCat
            WHEN 5 THEN 'transfers'
            WHEN 12 THEN 'transfers'
            WHEN 6 THEN 'capital'
            WHEN 7 THEN IF(COALESCE(s.inv_abs_credit, 0) > 0, 'loans', 'drawings')
            WHEN 8 THEN IF(COALESCE(s.inv_abs_debit, 0) > 0, 'loans',
                        IF(li.inv_pro_inv_deal_type = 2, 'clr_customer',
                        IF(COALESCE(li.inv_pro_inv_exp_id, 0) > 0, CONCAT('inc:ledg:', li.inv_pro_inv_exp_id), 'other_income')))
            WHEN 9 THEN IF(li.inv_pro_inv_deal_type = 1, 'clr_supplier',
                        IF(COALESCE(li.inv_pro_inv_exp_id, 0) > 0, CONCAT('exp:ledg:', li.inv_pro_inv_exp_id), 'exp:other'))
            WHEN 10 THEN 'oms'
            WHEN 11 THEN 'bank_charges'
            WHEN 13 THEN IF($lcGoods, 'clr_supplier', 'lc_costs')
            WHEN 14 THEN 'salary_payable'
            WHEN 15 THEN 'vat_payable'
            ELSE 'suspense' END";
        $interest = "IF(s.inv_abs_reference_type = 8 AND COALESCE(s.inv_abs_debit, 0) > 0, COALESCE(lp.interest, 0), 0)";
        $q->unionAll($st()
            ->leftJoin('inv_product_inventories as li', 'li.inv_pro_inv_id', '=', 's.inv_abs_inventory_id')
            ->leftJoin('inv_acc_expenses as e', function ($j) {
                $j->on('e.inv_acc_exp_id', '=', 's.inv_abs_reference_id')->whereIn('s.inv_abs_reference_type', [3, 4]);
            })
            ->leftJoinSub($loanPay, 'lp', 'lp.sid', '=', 's.inv_abs_id')
            ->selectRaw($sCols($counter, "COALESCE(s.inv_abs_debit, 0) - $interest", 'COALESCE(s.inv_abs_credit, 0)')));
        // the interest part of a loan payment
        $q->unionAll($st()->where('s.inv_abs_reference_type', 8)->where('s.inv_abs_debit', '>', 0)
            ->joinSub($loanPay, 'lp', 'lp.sid', '=', 's.inv_abs_id')->where('lp.interest', '<>', 0)
            ->selectRaw($sCols("'interest'", 'lp.interest', '0')));

        // -- customer and supplier ledgers (opening balance rows without a branch count too, as on every other page)
        $inv = function ($deals) use ($c, $b, $from, $to) {
            return DB::table('inv_product_inventories as i')->where('i.inv_pro_inv_com_id', $c)
                ->where(function ($w) use ($b) { $w->where('i.inv_pro_inv_branch_id', $b)->orWhereNull('i.inv_pro_inv_branch_id'); })
                ->where('i.inv_pro_inv_status', 1)->whereIn('i.inv_pro_inv_deal_type', $deals)
                ->when($from, function ($q) use ($from) { $q->where('i.inv_pro_inv_issue_date', '>=', $from); })
                ->where('i.inv_pro_inv_issue_date', '<=', $to);
        };
        $iCols = function ($eid, $kind, $akey, $dr, $cr) {
            return $this->cols('i.inv_pro_inv_issue_date', $eid, $kind, 'i.inv_pro_inv_invoice_no', 'i.inv_pro_inv_tran_desc', 'i.inv_pro_inv_party_id', $akey, $dr, $cr);
        };
        $idr = 'COALESCE(i.inv_pro_inv_debit, 0)';
        $icr = 'COALESCE(i.inv_pro_inv_credit, 0)';
        $party = function () use ($inv, $idr, $icr) {
            return $inv([1, 2])->whereRaw("($idr <> 0 OR $icr <> 0)");
        };
        $iEid = "CONCAT('i', i.inv_pro_inv_id)";
        $iKind = "CONCAT('I', i.inv_pro_inv_deal_type, '-', i.inv_pro_inv_tran_type)";
        // customer: a ledger debit is owed to you (asset up); supplier: a ledger debit is owed by you (liability up)
        $q->unionAll($party()->selectRaw($iCols($iEid, $iKind, "IF(i.inv_pro_inv_deal_type = 2, 'ar', 'ap')",
            "IF(i.inv_pro_inv_deal_type = 2, $idr, $icr)", "IF(i.inv_pro_inv_deal_type = 2, $icr, $idr)")));
        $other = "CASE WHEN i.inv_pro_inv_deal_type = 2 THEN
                CASE i.inv_pro_inv_tran_type WHEN 1 THEN 'sales' WHEN 10 THEN 'service_income' WHEN 11 THEN 'delivery_income'
                    WHEN 12 THEN 'sales_discount' WHEN 6 THEN 'sales_discount' WHEN 2 THEN 'sales_returns' WHEN 3 THEN 'obe'
                    WHEN 4 THEN 'clr_customer' WHEN 5 THEN 'clr_customer' ELSE 'suspense' END
            ELSE
                CASE i.inv_pro_inv_tran_type WHEN 1 THEN 'stock' WHEN 2 THEN 'stock' WHEN 3 THEN 'obe'
                    WHEN 4 THEN 'clr_supplier' WHEN 5 THEN 'clr_supplier'
                    WHEN 6 THEN IF(COALESCE(i.inv_pro_inv_exp_id, 0) > 0, 'clr_supplier', 'purchase_discount') ELSE 'suspense' END
            END";
        $q->unionAll($party()->selectRaw($iCols($iEid, $iKind, $other,
            "IF(i.inv_pro_inv_deal_type = 2, $icr, $idr)", "IF(i.inv_pro_inv_deal_type = 2, $idr, $icr)")));

        // -- cost of goods sold: quantity x buying price (returns put it back), as in Profit & Loss
        $cost = function () use ($inv) {
            return $inv([2])->whereIn('i.inv_pro_inv_tran_type', [1, 2])
                ->join('inv_product_details as pd', 'pd.inv_pro_det_id', '=', 'i.inv_pro_inv_project_id')
                ->whereRaw('COALESCE(i.inv_pro_inv_qty, 0) * COALESCE(pd.inv_pro_det_buy_price, 0) <> 0');
        };
        $amt = 'COALESCE(i.inv_pro_inv_qty, 0) * COALESCE(pd.inv_pro_det_buy_price, 0)';
        $cEid = "CONCAT('c', i.inv_pro_inv_id)";
        $q->unionAll($cost()->selectRaw($iCols($cEid, "'COGS'", "'cogs'", "IF(i.inv_pro_inv_tran_type = 1, $amt, 0)", "IF(i.inv_pro_inv_tran_type = 2, $amt, 0)")));
        $q->unionAll($cost()->selectRaw($iCols($cEid, "'COGS'", "'stock'", "IF(i.inv_pro_inv_tran_type = 2, $amt, 0)", "IF(i.inv_pro_inv_tran_type = 1, $amt, 0)")));

        // -- payroll, at the end of the salary month
        $payDate = "LAST_DAY(STR_TO_DATE(CONCAT(y.year, '-', y.month, '-01'), '%Y-%c-%d'))";
        $pay = function () use ($c, $b, $from, $to, $payDate) {
            return DB::table('emp_salary_generates as y')->where('y.company_id', $c)->where('y.branch_id', $b)
                ->whereRaw('COALESCE(y.final_salary, 0) <> 0')
                ->when($from, function ($q) use ($from, $payDate) { $q->whereRaw("$payDate >= ?", [$from]); })
                ->whereRaw("$payDate <= ?", [$to]);
        };
        $pCols = function ($akey, $dr, $cr) use ($payDate) {
            return $this->cols($payDate, "CONCAT('y', y.id)", "'PAY'", "CONCAT('SAL-', y.year, '-', LPAD(y.month, 2, '0'))",
                "CONCAT('Salary ', y.year, '-', LPAD(y.month, 2, '0'))", 'y.user_id', $akey, $dr, $cr);
        };
        $q->unionAll($pay()->selectRaw($pCols("'salary_exp'", 'y.final_salary', '0')));
        $q->unionAll($pay()->selectRaw($pCols("'salary_payable'", '0', 'y.final_salary')));

        // -- VAT, when it is on (Accounts > Settings): the VAT inside each sale and purchase, row by row
        $vat = AccountsSettings::vat($c);
        if ($vat) {
            $vf = $vat['vat_from'] ?: null;
            $vatRows = function ($deal, $trans) use ($inv, $vf) {
                return $inv([$deal])->whereIn('i.inv_pro_inv_tran_type', $trans)
                    ->when($vf, function ($q) use ($vf) { $q->where('i.inv_pro_inv_issue_date', '>=', $vf); });
            };
            $vEid = "CONCAT('v', i.inv_pro_inv_id)";
            $sale = self::vatSql($vat['vat_rate']);
            $q->unionAll($vatRows(2, self::VAT_SALE_TRANS)->whereRaw("$sale <> 0")
                ->selectRaw($iCols($vEid, "'VAT'", "'vat_on_sales'", "GREATEST($sale, 0)", "GREATEST(-($sale), 0)")));
            $q->unionAll($vatRows(2, self::VAT_SALE_TRANS)->whereRaw("$sale <> 0")
                ->selectRaw($iCols($vEid, "'VAT'", "'vat_payable'", "GREATEST(-($sale), 0)", "GREATEST($sale, 0)")));
            if ($vat['vat_rebate_rate'] > 0) {
                $buy = self::vatSql($vat['vat_rebate_rate']);
                $q->unionAll($vatRows(1, self::VAT_BUY_TRANS)->whereRaw("$buy <> 0")
                    ->selectRaw($iCols($vEid, "'VAT'", "'vat_payable'", "GREATEST($buy, 0)", "GREATEST(-($buy), 0)")));
                $q->unionAll($vatRows(1, self::VAT_BUY_TRANS)->whereRaw("$buy <> 0")
                    ->selectRaw($iCols($vEid, "'VAT'", "'vat_rebate'", "GREATEST(-($buy), 0)", "GREATEST($buy, 0)")));
            }
        }

        // -- journal vouchers
        $q->unionAll(DB::table('acc_journal_lines as l')->join('acc_journals as j', 'j.aj_id', '=', 'l.ajl_journal_id')
            ->where('j.aj_company_id', $c)->where('j.aj_branch_id', $b)->where('j.aj_status', 'posted')
            ->when($from, function ($q) use ($from) { $q->where('j.aj_date', '>=', $from); })
            ->where('j.aj_date', '<=', $to)
            ->selectRaw($this->cols('j.aj_date', "CONCAT('j', j.aj_id)", "'JV'", 'j.aj_voucher_no',
                "COALESCE(NULLIF(l.ajl_note, ''), j.aj_narration)", 'NULL', 'l.ajl_account_key', 'l.ajl_debit', 'l.ajl_credit')));

        return $q;
    }

    /** Fixed assets and their monthly depreciation, worked out like the Depreciation report (disposed assets left out) */
    public function assetPostings($from, $to)
    {
        $from = $from ? Carbon::parse($from)->startOfDay() : null;
        $to = Carbon::parse($to)->startOfDay();
        $reports = new AccountsReports($this->company, $this->branch);
        $out = [];
        foreach ($reports->assets() as $a) {
            if ((int) $a->asset_status === 3 || !$a->asset_purchase_date || $a->asset_purchase_date === '0000-00-00') {
                continue;
            }
            $cost = round((float) $a->asset_purchase_price, 2);
            $bought = Carbon::parse($a->asset_purchase_date)->startOfDay();
            $name = 'Fixed asset: ' . $a->asset_name . ($a->asset_code ? ' (' . $a->asset_code . ')' : '');
            if ($cost != 0 && $bought->lte($to) && (!$from || $bought->gte($from))) {
                foreach ([['fixed_assets', $cost, 0], ['capital_assets', 0, $cost]] as [$key, $dr, $cr]) {
                    $out[] = (object) ['d' => $bought->toDateString(), 'eid' => 'a' . $a->asset_id, 'kind' => 'FA', 'ref' => $a->asset_code,
                        'narr' => $name, 'party' => null, 'akey' => $key, 'dr' => $dr, 'cr' => $cr];
                }
            }
            foreach ((array) $reports->assetDepreciationByMonth($a, $to->copy()->endOfMonth()) as $ym => $dep) {
                $day = Carbon::createFromFormat('Ym', (string) $ym)->startOfMonth()->endOfMonth()->startOfDay();
                if ($dep == 0 || $day->gt($to) || ($from && $day->lt($from))) {
                    continue;
                }
                foreach ([['depreciation', $dep, 0], ['acc_dep', 0, $dep]] as [$key, $dr, $cr]) {
                    $out[] = (object) ['d' => $day->toDateString(), 'eid' => 'd' . $a->asset_id . '-' . $ym, 'kind' => 'DEP', 'ref' => $a->asset_code,
                        'narr' => 'Depreciation ' . $day->format('F Y') . ': ' . $a->asset_name, 'party' => null, 'akey' => $key, 'dr' => $dr, 'cr' => $cr];
                }
            }
        }
        return collect($out);
    }

    // ------------------------------------------------------------------ fiscal years

    /** "2025-26" for the fiscal year that starts on a date */
    public static function fyLabel(Carbon $start)
    {
        return $start->year . '-' . substr((string) ($start->year + 1), -2);
    }

    /** Closed fiscal years of the branch, oldest first */
    public function closedYears()
    {
        if (!AccountsSettings::hasTable('acc_fiscal_years')) {
            return collect();
        }
        return DB::table('acc_fiscal_years')->where('afy_company_id', $this->company)->where('afy_branch_id', $this->branch)
            ->where('afy_status', 'closed')->orderBy('afy_end')->get();
    }

    /** What a closed year moves to retained earnings: everything after the previous closed year up to its end */
    public function closingRange($year)
    {
        $prev = $this->closedYears()->filter(function ($y) use ($year) { return $y->afy_end < $year->afy_end; })->last();
        return [$prev ? Carbon::parse($prev->afy_end)->addDay()->startOfDay() : null, Carbon::parse($year->afy_end)->startOfDay()];
    }

    /** Year-end closing entries: the income and expense balances of each closed year moved to retained earnings */
    public function closingPostings($from, $to)
    {
        $from = $from ? Carbon::parse($from)->startOfDay() : null;
        $to = Carbon::parse($to)->startOfDay();
        $out = [];
        foreach ($this->closedYears() as $y) {
            [$rangeFrom, $end] = $this->closingRange($y);
            if ($end->gt($to) || ($from && $end->lt($from))) {
                continue;
            }
            $label = self::fyLabel(Carbon::parse($y->afy_start));
            $profit = 0.0;
            foreach ($this->totals($rangeFrom, $end) as $key => $t) {
                if (!in_array($this->account($key)->type, ['income', 'expense'], true)) {
                    continue;
                }
                $net = round($t['dr'] - $t['cr'], 2);
                if (abs($net) < 0.005) {
                    continue;
                }
                $out[] = (object) ['d' => $end->toDateString(), 'eid' => 'z' . $y->afy_id, 'kind' => 'CLOSE', 'ref' => 'FY ' . $label,
                    'narr' => 'Closing of fiscal year ' . $label . ' to retained earnings', 'party' => null, 'akey' => $key,
                    'dr' => $net < 0 ? -$net : 0.0, 'cr' => $net > 0 ? $net : 0.0];
                $profit -= $net;
            }
            if (abs($profit) >= 0.005) {
                $out[] = (object) ['d' => $end->toDateString(), 'eid' => 'z' . $y->afy_id, 'kind' => 'CLOSE', 'ref' => 'FY ' . $label,
                    'narr' => 'Closing of fiscal year ' . $label . ' to retained earnings', 'party' => null, 'akey' => 'retained',
                    'dr' => $profit < 0 ? round(-$profit, 2) : 0.0, 'cr' => $profit > 0 ? round($profit, 2) : 0.0];
            }
        }
        return collect($out);
    }

    /** Closed years whose profit changed after closing (entries added or changed later on other pages) */
    public function closedYearChanges()
    {
        $out = [];
        foreach ($this->closedYears() as $y) {
            [$rangeFrom, $end] = $this->closingRange($y);
            $now = $this->profit($rangeFrom, $end);
            if (abs($now - (float) $y->afy_profit) >= 0.005) {
                $out[] = (object) ['year' => $y, 'label' => self::fyLabel(Carbon::parse($y->afy_start)), 'closed' => (float) $y->afy_profit, 'now' => $now];
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------ balances, entries, statements

    /** Debit and credit totals of every account between two dates (from = null: since the start), keyed by account */
    public function totals($from, $to, $withClosing = false)
    {
        $rows = DB::query()->fromSub($this->postingsQuery($from, $to), 'p')
            ->groupBy('p.akey')->selectRaw('p.akey, SUM(p.dr) AS dr, SUM(p.cr) AS cr')->get();
        $out = [];
        foreach ($rows as $r) {
            $out[$r->akey] = ['dr' => (float) $r->dr, 'cr' => (float) $r->cr];
        }
        $extra = $this->assetPostings($from, $to);
        if ($withClosing) {
            $extra = $extra->concat($this->closingPostings($from, $to));
        }
        foreach ($extra as $p) {
            $out[$p->akey]['dr'] = ($out[$p->akey]['dr'] ?? 0) + $p->dr;
            $out[$p->akey]['cr'] = ($out[$p->akey]['cr'] ?? 0) + $p->cr;
        }
        foreach ($out as $k => $v) {
            $out[$k] = ['dr' => round($v['dr'], 2), 'cr' => round($v['cr'], 2)];
        }
        return $out;
    }

    /** Balance in the account's own direction (debit for assets and expenses, credit for the rest) */
    public static function natural($type, $dr, $cr)
    {
        return in_array($type, self::DEBIT_NORMAL, true) ? $dr - $cr : $cr - $dr;
    }

    /**
     * Trial balance up to a date: every account with its debit or credit balance, in chart order,
     * group accounts carrying the sum of the accounts under them.
     */
    public function trialBalance(Carbon $asOf, $showZero = false)
    {
        $totals = $this->totals(null, $asOf, true);
        $accounts = $this->accounts();
        foreach (array_keys($totals) as $k) {          // a key in the books that the chart does not know
            if (!$accounts->has($k)) {
                $accounts->put($k, $this->account($k));
            }
        }
        $rows = [];
        foreach ($this->ordered($accounts) as [$a, $depth]) {
            $t = $totals[$a->key] ?? ['dr' => 0, 'cr' => 0];
            $net = round($t['dr'] - $t['cr'], 2);
            $rows[] = (object) ['account' => $a, 'depth' => $depth, 'dr' => $net > 0 ? $net : 0.0, 'cr' => $net < 0 ? -$net : 0.0,
                'own' => $net, 'group' => $accounts->contains(function ($x) use ($a) { return $x->parent === $a->key; })];
        }
        // group totals: own balance + everything under it
        $byKey = [];
        foreach ($rows as $r) {
            $byKey[$r->account->key] = $r;
        }
        $sum = function ($key) use (&$sum, $byKey, $accounts) {
            $total = $byKey[$key]->own ?? 0;
            foreach ($accounts as $x) {
                if ($x->parent === $key) {
                    $total += $sum($x->key);
                }
            }
            return round($total, 2);
        };
        foreach ($rows as $r) {
            $r->total = $r->group ? $sum($r->account->key) : $r->own;
        }
        if (!$showZero) {
            $rows = array_values(array_filter($rows, function ($r) { return abs($r->own) > 0.004 || ($r->group && abs($r->total) > 0.004); }));
        }
        $dr = round(array_sum(array_map(function ($r) { return $r->dr; }, $rows)), 2);
        $cr = round(array_sum(array_map(function ($r) { return $r->cr; }, $rows)), 2);
        return ['rows' => $rows, 'dr' => $dr, 'cr' => $cr, 'totals' => $totals];
    }

    /** Accounts in chart order: by type, then code, each followed by the accounts under it */
    public function ordered($accounts)
    {
        $out = [];
        $walk = function ($parent, $depth) use (&$walk, &$out, $accounts) {
            $kids = $accounts->filter(function ($a) use ($parent) { return $a->parent === $parent; })
                ->sortBy(function ($a) { return array_search($a->type, array_keys(self::TYPES)) . '|' . $a->code . '|' . $a->name; });
            foreach ($kids as $a) {
                $out[] = [$a, $depth];
                $walk($a->key, $depth + 1);
            }
        };
        // top level: no parent, or a parent the chart does not have
        $walk(null, 0);
        foreach ($accounts as $a) {
            if ($a->parent !== null && !$accounts->has($a->parent)) {
                $out[] = [$a, 0];
                $walk($a->key, 1);
            }
        }
        return $out;
    }

    /** Profit (income less expenses) between two dates (from = null: since the start) */
    public function profit($from, $to, $totals = null)
    {
        $totals = $totals ?? $this->totals($from, $to);
        $accounts = $this->accounts();
        $profit = 0.0;
        foreach ($totals as $key => $t) {
            $type = $this->account($key)->type;
            if ($type === 'income') {
                $profit += $t['cr'] - $t['dr'];
            } elseif ($type === 'expense') {
                $profit -= $t['dr'] - $t['cr'];
            }
        }
        return round($profit, 2);
    }

    /** First day of the fiscal year (July to June) a date is in */
    public static function fiscalYearStart(Carbon $date)
    {
        $year = $date->month >= self::FY_START_MONTH ? $date->year : $date->year - 1;
        return Carbon::create($year, self::FY_START_MONTH, 1)->startOfDay();
    }

    /** Balance sheet on a date */
    public function balanceSheet(Carbon $asOf)
    {
        $totals = $this->totals(null, $asOf, true);
        $accounts = $this->accounts();
        $fyStart = self::fiscalYearStart($asOf);
        $before = $this->totals(null, $fyStart->copy()->subDay(), true);
        $profitBefore = $this->profit(null, null, $before);
        $profitAll = $this->profit(null, null, $totals);
        $sections = [];
        foreach (['asset', 'liability', 'equity'] as $type) {
            $lines = [];
            foreach ($this->ordered($accounts) as [$a, $depth]) {
                if ($a->type !== $type) {
                    continue;
                }
                $t = $totals[$a->key] ?? null;
                $amount = $t ? round(self::natural($type, $t['dr'], $t['cr']), 2) : 0.0;
                $lines[] = (object) ['account' => $a, 'depth' => $depth, 'amount' => $amount];
            }
            foreach ($totals as $key => $t) {           // keys the chart does not know
                if (!$accounts->has($key) && $this->account($key)->type === $type) {
                    $lines[] = (object) ['account' => $this->account($key), 'depth' => 0, 'amount' => round(self::natural($type, $t['dr'], $t['cr']), 2)];
                }
            }
            // a group shows the total of the accounts under it
            $amountOf = [];
            foreach ($lines as $l) {
                $amountOf[$l->account->key] = $l->amount;
            }
            $sumUnder = function ($key) use (&$sumUnder, $accounts, $amountOf) {
                $total = $amountOf[$key] ?? 0;
                foreach ($accounts as $x) {
                    if ($x->parent === $key) {
                        $total += $sumUnder($x->key);
                    }
                }
                return $total;
            };
            foreach ($lines as $l) {
                $l->group = $accounts->contains(function ($x) use ($l) { return $x->parent === $l->account->key; });
                $l->total = $l->group ? round($sumUnder($l->account->key), 2) : $l->amount;
            }
            $sections[$type] = $lines;
        }
        $sum = function ($lines) { return round(array_sum(array_map(function ($l) { return $l->amount; }, $lines)), 2); };
        $assets = $sum($sections['asset']);
        $liabilities = $sum($sections['liability']);
        $equity = $sum($sections['equity']) + $profitAll;
        return [
            'sections' => $sections, 'assets' => $assets, 'liabilities' => $liabilities, 'equity' => round($equity, 2),
            'profitBefore' => $profitBefore, 'profitYear' => round($profitAll - $profitBefore, 2), 'fyStart' => $fyStart,
            'difference' => round($assets - $liabilities - $equity, 2),
        ];
    }

    // ------------------------------------------------------------------ cash flow and VAT

    /** What each bank / cash movement was for, in the cash flow: account key => [activity, label for money in, label for money out] */
    private static function cashFlowLine($key)
    {
        $map = [
            'clr_customer'   => ['operating', 'Received from customers', 'Refunds to customers'],
            'clr_supplier'   => ['operating', 'Refunds from suppliers', 'Paid to suppliers'],
            'oms'            => ['operating', 'Office bill refunds', 'Office bills (OMS) paid'],
            'bank_charges'   => ['operating', 'Bank charges refunded', 'Bank service charges'],
            'salary_payable' => ['operating', 'Salary refunds', 'Salaries paid'],
            'other_income'   => ['operating', 'Other income received', 'Other income refunded'],
            'lc_costs'       => ['operating', 'LC cost refunds', 'LC import costs paid'],
            'interest'       => ['operating', 'Interest received', 'Loan interest paid'],
            'vat_payable'    => ['operating', 'VAT refunds', 'VAT paid'],
            'loans'          => ['financing', 'Loans received', 'Loan principal repaid'],
            'capital'        => ['financing', "Owner's deposits", "Owner's deposits returned"],
            'drawings'       => ['financing', "Owner's withdrawals returned", "Owner's withdrawals"],
            'transfers'      => ['transfers', 'Transfers between own accounts', 'Transfers between own accounts'],
        ];
        if (isset($map[$key])) {
            return $map[$key];
        }
        if (strpos($key, 'exp:') === 0) {
            return ['operating', 'Expense refunds', 'Expenses paid'];
        }
        if (strpos($key, 'inc:') === 0) {
            return ['operating', 'Other income received', 'Other income refunded'];
        }
        return ['operating', 'Other money in', 'Other money out'];
    }

    /** Cash flow (direct method) of the bank, mobile banking and cash accounts for a period */
    public function cashFlow(Carbon $from, Carbon $to)
    {
        $rows = DB::query()->fromSub($this->postingsQuery($from, $to), 'p')
            ->where('p.eid', 'like', 's%')->where('p.akey', 'not like', 'bank:%')
            ->groupBy('p.akey')->selectRaw('p.akey, SUM(p.cr) AS cin, SUM(p.dr) AS cout')->get();
        $sections = ['operating' => [], 'investing' => [], 'financing' => [], 'transfers' => []];
        foreach ($rows as $r) {
            [$activity, $inLabel, $outLabel] = self::cashFlowLine($r->akey);
            if ($activity === 'transfers') {
                $sections['transfers'][$inLabel] = ($sections['transfers'][$inLabel] ?? 0) + (float) $r->cin - (float) $r->cout;
                continue;
            }
            if ((float) $r->cin) {
                $sections[$activity][$inLabel] = ($sections[$activity][$inLabel] ?? 0) + (float) $r->cin;
            }
            if ((float) $r->cout) {
                $sections[$activity][$outLabel] = ($sections[$activity][$outLabel] ?? 0) - (float) $r->cout;
            }
        }
        $totals = [];
        foreach ($sections as $k => $lines) {
            $lines = array_filter(array_map(function ($v) { return round($v, 2); }, $lines), function ($v) { return abs($v) >= 0.005; });
            // money in first, then money out, largest first
            uksort($lines, function ($a, $b) use ($lines) { return ($lines[$b] <=> 0) <=> ($lines[$a] <=> 0) ?: abs($lines[$b]) <=> abs($lines[$a]); });
            $sections[$k] = $lines;
            $totals[$k] = round(array_sum($lines), 2);
        }
        $accounts = (new AccountsReports($this->company, $this->branch))->accountSummary($from, $to);
        $opening = round($accounts->sum('opening'), 2);
        $closing = round($accounts->sum('closing'), 2);
        $net = round(array_sum($totals), 2);
        return compact('sections', 'totals', 'accounts', 'opening', 'closing', 'net') + ['difference' => round($opening + $net - $closing, 2)];
    }

    /**
     * VAT of each month of a fiscal year: sales with VAT, VAT on sales, purchases with VAT, VAT rebate, VAT paid
     * and the VAT payable at the end of the month. Null when VAT is off.
     */
    public function vatMonths(Carbon $fyStart)
    {
        $vat = AccountsSettings::vat($this->company);
        if (!$vat) {
            return null;
        }
        $fyEnd = $fyStart->copy()->addYear()->subDay();
        $rows = function ($deal, $trans) use ($vat, $fyStart, $fyEnd) {
            return DB::table('inv_product_inventories as i')->where('i.inv_pro_inv_com_id', $this->company)
                ->where(function ($w) { $w->where('i.inv_pro_inv_branch_id', $this->branch)->orWhereNull('i.inv_pro_inv_branch_id'); })
                ->where('i.inv_pro_inv_status', 1)->where('i.inv_pro_inv_deal_type', $deal)->whereIn('i.inv_pro_inv_tran_type', $trans)
                ->whereBetween('i.inv_pro_inv_issue_date', [$fyStart->toDateString(), $fyEnd->toDateString()])
                ->when($vat['vat_from'], function ($q) use ($vat) { $q->where('i.inv_pro_inv_issue_date', '>=', $vat['vat_from']); })
                ->groupBy('ym');
        };
        $base = 'COALESCE(i.inv_pro_inv_debit, 0) - COALESCE(i.inv_pro_inv_credit, 0)';
        $sales = $rows(2, self::VAT_SALE_TRANS)->selectRaw("DATE_FORMAT(i.inv_pro_inv_issue_date, '%Y%m') ym, SUM($base) amount, SUM(" . self::vatSql($vat['vat_rate']) . ') vat')->get()->keyBy('ym');
        $buys = $vat['vat_rebate_rate'] > 0
            ? $rows(1, self::VAT_BUY_TRANS)->selectRaw("DATE_FORMAT(i.inv_pro_inv_issue_date, '%Y%m') ym, SUM($base) amount, SUM(" . self::vatSql($vat['vat_rebate_rate']) . ') vat')->get()->keyBy('ym')
            : collect();
        $paid = DB::table('inv_acc_bank_statements')->where('inv_abs_company_id', $this->company)->where('inv_abs_branch_id', $this->branch)
            ->where('inv_abs_status', 1)->where('inv_abs_reference_type', 15)
            ->whereBetween('inv_abs_transaction_date', [$fyStart->toDateString(), $fyEnd->toDateString()])
            ->groupBy('ym')->selectRaw("DATE_FORMAT(inv_abs_transaction_date, '%Y%m') ym, SUM(inv_abs_debit) - SUM(inv_abs_credit) v")->pluck('v', 'ym');
        $before = $this->totals(null, $fyStart->copy()->subDay())['vat_payable'] ?? ['dr' => 0, 'cr' => 0];
        $balance = round($before['cr'] - $before['dr'], 2);
        $opening = $balance;
        $months = [];
        for ($m = $fyStart->copy(); $m->lte($fyEnd); $m->addMonth()) {
            $ym = $m->format('Ym');
            $s = $sales->get($ym);
            $b = $buys->get($ym);
            $line = (object) [
                'month' => $m->copy(), 'sales' => round((float) optional($s)->amount, 2), 'output' => round((float) optional($s)->vat, 2),
                'purchases' => round((float) optional($b)->amount, 2), 'input' => round((float) optional($b)->vat, 2), 'paid' => round((float) ($paid[$ym] ?? 0), 2),
            ];
            $line->net = round($line->output - $line->input, 2);
            $balance = round($balance + $line->net - $line->paid, 2);
            $line->balance = $balance;
            $months[] = $line;
        }
        return ['settings' => $vat, 'months' => $months, 'opening' => $opening, 'fyStart' => $fyStart, 'fyEnd' => $fyEnd];
    }

    /** Journal entries between two dates, optionally of one account: date, entry id, what, ref, narration, party, lines */
    public function entries(Carbon $from, Carbon $to, $accountKey = null)
    {
        $q = DB::query()->fromSub($this->postingsQuery($from, $to), 'p');
        if ($accountKey) {
            $q->whereIn('p.eid', DB::query()->fromSub($this->postingsQuery($from, $to), 'k')->where('k.akey', $accountKey)->select('k.eid'));
        }
        $rows = $q->orderBy('p.d')->orderBy('p.eid')->get();
        $assets = $this->assetPostings($from, $to)->concat($this->closingPostings($from, $to));
        if ($accountKey) {
            $ids = $assets->where('akey', $accountKey)->pluck('eid')->all();
            $assets = $assets->whereIn('eid', $ids);
        }
        $rows = $rows->concat($assets)->sortBy(function ($r) { return $r->d . ' ' . $r->eid; })->values();

        $customers = DB::table('inv_customers')->whereIn('inv_cus_id', $rows->filter(function ($r) { return in_array($r->kind, ['S1', 'S8']) || strpos($r->kind, 'I2-') === 0; })->pluck('party')->filter()->unique()->all())
            ->pluck('inv_cus_name', 'inv_cus_id');
        $suppliers = DB::table('inv_suppliers')->whereIn('inv_sup_id', $rows->filter(function ($r) { return in_array($r->kind, ['S2', 'S9']) || strpos($r->kind, 'I1-') === 0; })->pluck('party')->filter()->unique()->all())
            ->selectRaw("inv_sup_id, COALESCE(NULLIF(inv_sup_com_name, ''), inv_sup_person) n")->pluck('n', 'inv_sup_id');
        $employees = DB::table('admin_user')->whereIn('au_id', $rows->where('kind', 'PAY')->pluck('party')->filter()->unique()->all())->pluck('au_name', 'au_id');

        return $rows->groupBy('eid')->map(function ($lines) use ($customers, $suppliers, $employees) {
            $f = $lines->first();
            $partyName = '';
            if ($f->kind === 'PAY') {
                $partyName = $employees[$f->party] ?? '';
            } elseif (in_array($f->kind, ['S1', 'S8']) || strpos($f->kind, 'I2-') === 0) {
                $partyName = $customers[$f->party] ?? '';
            } elseif (in_array($f->kind, ['S2', 'S9']) || strpos($f->kind, 'I1-') === 0) {
                $partyName = $suppliers[$f->party] ?? '';
            }
            return (object) [
                'date' => $f->d, 'eid' => $f->eid, 'kind' => $f->kind, 'what' => self::kindLabel($f->kind, $lines),
                'ref' => $f->ref, 'narr' => $f->narr, 'party' => $partyName,
                'lines' => $lines->map(function ($l) {
                    $a = $this->account($l->akey);
                    return (object) ['key' => $l->akey, 'code' => $a->code, 'name' => $a->name, 'dr' => round((float) $l->dr, 2), 'cr' => round((float) $l->cr, 2), 'note' => $l->kind === 'JV' ? $l->narr : null];
                })->sortByDesc('dr')->values(),
            ];
        })->sortBy(function ($e) { return $e->date . ' ' . $e->eid; })->values();
    }

    /** What made an entry */
    public static function kindLabel($kind, $lines = null)
    {
        $moneyIn = $lines ? $lines->contains(function ($l) { return strpos($l->akey, 'bank:') === 0 && $l->dr > 0; }) : true;
        $labels = [
            'S1' => $moneyIn ? 'Customer payment' : 'Refund to customer', 'S2' => $moneyIn ? 'Refund from supplier' : 'Supplier payment',
            'S3' => 'Expense', 'S4' => 'Expense voucher', 'S5' => 'Contra (transfer)', 'S6' => 'Bank deposit',
            'S7' => $moneyIn ? 'Loan received' : 'Bank withdraw', 'S8' => $moneyIn ? 'Ledger cash in' : 'Loan payment', 'S9' => 'Ledger cash out',
            'S10' => 'OMS bill payment', 'S11' => 'Bank service charge', 'S12' => 'Loan transfer', 'S13' => 'LC payment',
            'S14' => $moneyIn ? 'Salary refund' : 'Salary payment', 'S15' => $moneyIn ? 'VAT refund' : 'VAT payment',
            'I2-1' => 'Sales invoice', 'I2-10' => 'Service charge', 'I2-11' => 'Delivery charge', 'I2-12' => 'Invoice discount',
            'I2-6' => 'Discount to customer', 'I2-2' => 'Sales return', 'I2-3' => 'Customer opening balance / adjustment',
            'I2-4' => 'Customer payment (customer ledger)', 'I2-5' => 'Customer refund (customer ledger)',
            'I1-1' => 'Purchase', 'I1-2' => 'Purchase return', 'I1-3' => 'Supplier opening balance / adjustment',
            'I1-4' => 'Supplier payment (supplier ledger)', 'I1-5' => 'Supplier refund (supplier ledger)', 'I1-6' => 'Supplier discount / ledger payment',
            'COGS' => 'Cost of goods sold', 'PAY' => 'Payroll salary', 'JV' => 'Journal voucher', 'FA' => 'Fixed asset added', 'DEP' => 'Depreciation',
            'VAT' => 'VAT on sale / purchase', 'CLOSE' => 'Year-end closing',
        ];
        return $labels[$kind] ?? ('Other (' . $kind . ')');
    }
}

<?php

namespace App\Services;

use App\Models\Admin_user;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard numbers, scoped by the user's role:
 *  - company admin (type 4, also 1 and 3): the whole branch, including purchases, expenses, salary and supplier due
 *  - team leader (5) and AGM (7): sales entered by their team
 *  - everyone else: their own sales
 *
 * Ledger used (inv_product_inventories): deal 2 = customer, deal 1 = supplier.
 * Customer tran types: 1 sale, 10 service charge, 11 delivery charge, 12 discount (credit), 4/5 payment (credit).
 * Supplier tran 1 = purchase. Expenses are bank statement rows of reference type 3; salary is emp_salary_generates.
 */
class DashboardStats
{
    public $company;
    public $branch;
    public $userId;
    public $scope;        // all | team | self
    public $scopeLabel;
    public $memberIds;    // null = everyone in the branch
    public $canSeeCosts;  // purchases, expenses, salary, supplier due

    public function __construct($user)
    {
        $this->company = $user->au_company_id;
        $this->branch = $user->au_branch_id;
        $this->userId = $user->au_id;
        $type = (int) $user->au_user_type;

        if (in_array($type, [1, 3, 4], true)) {
            $this->scope = 'all';
            $this->scopeLabel = 'Whole branch';
            $this->memberIds = null;
            $this->canSeeCosts = true;
        } elseif (in_array($type, [5, 7], true)) {
            $teams = $type === 5 ? [$user->au_team_id] : array_filter(explode('-', (string) $user->agm_permission));
            $ids = Admin_user::where('au_company_id', $this->company)
                ->whereIn('au_team_id', $teams ?: [-1])
                ->pluck('au_id')->all();
            $this->scope = 'team';
            $this->scopeLabel = 'Your team';
            $this->memberIds = array_values(array_unique(array_merge([$this->userId], $ids)));
            $this->canSeeCosts = false;
        } else {
            $this->scope = 'self';
            $this->scopeLabel = 'Your own sales';
            $this->memberIds = [$this->userId];
            $this->canSeeCosts = false;
        }
    }

    /** Ledger rows of this company and branch (rows saved without a branch count too), limited to the role's people */
    private function ledger($deal, $scoped = true)
    {
        $q = DB::table('inv_product_inventories')
            ->where('inv_pro_inv_com_id', $this->company)
            ->where(function ($q) {
                $q->where('inv_pro_inv_branch_id', $this->branch)->orWhereNull('inv_pro_inv_branch_id');
            })
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_deal_type', $deal);
        if ($scoped && $this->memberIds !== null) {
            $q->whereIn('inv_pro_inv_submit_by', $this->memberIds);
        }
        return $q;
    }

    private static function between($q, $column, $from, $to)
    {
        return $q->whereBetween($column, [$from->format('Y-m-d'), $to->format('Y-m-d')]);
    }

    /** Totals for a date range */
    public function totals(Carbon $from, Carbon $to)
    {
        $sum = function ($deal, $types, $col) use ($from, $to) {
            return (float) self::between($this->ledger($deal), 'inv_pro_inv_issue_date', $from, $to)
                ->whereIn('inv_pro_inv_tran_type', $types)->sum($col);
        };

        $t = [
            'sales'       => $sum(2, [1, 11], 'inv_pro_inv_debit') - $sum(2, [12], 'inv_pro_inv_credit'),
            'service'     => $sum(2, [10], 'inv_pro_inv_debit'),
            'collections' => $sum(2, [4, 5], 'inv_pro_inv_credit'),
            'invoices'    => self::between($this->ledger(2), 'inv_pro_inv_issue_date', $from, $to)
                                ->whereIn('inv_pro_inv_tran_type', [1, 10])->distinct()->count('inv_pro_inv_invoice_no'),
            'purchases'   => null,
            'expenses'    => null,
            'salary'      => null,
        ];

        if ($this->canSeeCosts) {
            $t['purchases'] = $sum(1, [1], 'inv_pro_inv_debit');
            $t['expenses'] = (float) self::between(DB::table('inv_acc_bank_statements'), 'inv_abs_transaction_date', $from, $to)
                ->where('inv_abs_company_id', $this->company)
                ->where('inv_abs_branch_id', $this->branch)
                ->where('inv_abs_status', 1)
                ->where('inv_abs_reference_type', 3)
                ->sum('inv_abs_debit');
            $t['salary'] = (float) DB::table('emp_salary_generates')
                ->where('company_id', $this->company)
                ->where('branch_id', $this->branch)
                ->where(function ($q) use ($from, $to) {
                    // every month the range touches
                    $q->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [(int) $from->format('Ym'), (int) $to->format('Ym')]);
                })
                ->sum('final_salary');
        }
        return $t;
    }

    /** Month-by-month sales (and purchases for the admin) of one year, in Tk */
    public function yearly($year)
    {
        $byMonth = function ($deal, $types, $col, $sign = 1) use ($year) {
            return $this->ledger($deal)
                ->whereYear('inv_pro_inv_issue_date', $year)
                ->whereIn('inv_pro_inv_tran_type', $types)
                ->selectRaw('MONTH(inv_pro_inv_issue_date) m, SUM(' . $col . ') total')
                ->groupBy('m')->pluck('total', 'm')
                ->map(function ($v) use ($sign) { return $sign * (float) $v; });
        };
        $fill = function ($rows) {
            $out = array_fill(0, 12, 0);
            foreach ($rows as $m => $v) {
                $out[$m - 1] += round($v, 2);
            }
            return $out;
        };

        $sales = $fill($byMonth(2, [1, 10, 11], 'inv_pro_inv_debit'));
        $discount = $fill($byMonth(2, [12], 'inv_pro_inv_credit'));
        foreach ($sales as $i => $v) {
            $sales[$i] = round($v - $discount[$i], 2);
        }

        return [
            'sales'     => $sales,
            'purchases' => $this->canSeeCosts ? $fill($byMonth(1, [1], 'inv_pro_inv_debit')) : null,
        ];
    }

    /** Best-selling products of a year: name, quantity, amount */
    public function topProducts($year, $limit = 5)
    {
        return $this->ledger(2)
            ->leftJoin('inv_product_details as d', 'd.inv_pro_det_id', '=', 'inv_product_inventories.inv_pro_inv_project_id')
            ->leftJoin('inv_product as p', 'p.inv_pro_id', '=', 'd.inv_pro_det_pro_id')
            ->leftJoin('inv_product as p2', 'p2.inv_pro_id', '=', 'inv_product_inventories.inv_pro_inv_project_id')
            ->where('inv_pro_inv_tran_type', 1)
            ->whereNotNull('inv_pro_inv_project_id')
            ->whereYear('inv_pro_inv_issue_date', $year)
            ->groupBy('inv_pro_inv_project_id')
            ->selectRaw('inv_pro_inv_project_id, MAX(COALESCE(p.inv_pro_name, p2.inv_pro_name)) as name, SUM(inv_pro_inv_qty) as qty, SUM(inv_pro_inv_debit) as amount')
            ->orderByDesc('qty')->orderByDesc('amount')
            ->limit($limit)
            ->get()
            ->map(function ($r) {
                $r->name = $r->name ?: 'Product #' . $r->inv_pro_inv_project_id;
                $r->qty = (float) $r->qty;
                $r->amount = (float) $r->amount;
                return $r;
            });
    }

    /** Invoices of one day: customer, invoice, sold by, amount */
    public function salesOn(Carbon $day, $limit = 10)
    {
        $rows = $this->ledger(2)
            ->where('inv_pro_inv_issue_date', $day->format('Y-m-d'))
            ->whereIn('inv_pro_inv_tran_type', [1, 10, 11, 12])
            ->whereNotNull('inv_pro_inv_invoice_no')
            ->groupBy('inv_pro_inv_invoice_no')
            ->selectRaw('inv_pro_inv_invoice_no as invoice, MAX(inv_pro_inv_party_id) as party, MAX(inv_pro_inv_submit_by) as seller,
                SUM(inv_pro_inv_debit) - SUM(CASE WHEN inv_pro_inv_tran_type = 12 THEN inv_pro_inv_credit ELSE 0 END) as amount,
                MAX(inv_pro_inv_id) as last_id')
            ->orderByDesc('last_id')
            ->get();

        $customers = DB::table('inv_customers')->whereIn('inv_cus_id', $rows->pluck('party')->filter()->all())->pluck('inv_cus_name', 'inv_cus_id');
        $sellers = DB::table('admin_user')->whereIn('au_id', $rows->pluck('seller')->filter()->all())->pluck('au_name', 'au_id');

        return [
            'rows' => $rows->take($limit)->map(function ($r) use ($customers, $sellers) {
                $r->customer = $customers[$r->party] ?? 'Walk-in customer';
                $r->seller_name = $sellers[$r->seller] ?? '';
                $r->amount = (float) $r->amount;
                return $r;
            }),
            'count' => $rows->count(),
            'total' => (float) $rows->sum('amount'),
        ];
    }

    /** Most recent invoice in the role's scope: invoice, date, customer, amount */
    public function lastSale()
    {
        $last = $this->ledger(2)->whereIn('inv_pro_inv_tran_type', [1, 10])->whereNotNull('inv_pro_inv_invoice_no')
            ->orderByDesc('inv_pro_inv_id')->first();
        if (!$last) {
            return null;
        }
        $rows = $this->ledger(2, false)->where('inv_pro_inv_invoice_no', $last->inv_pro_inv_invoice_no)->whereIn('inv_pro_inv_tran_type', [1, 10, 11, 12])->get();
        return (object) [
            'invoice'  => $last->inv_pro_inv_invoice_no,
            'date'     => $last->inv_pro_inv_issue_date,
            'customer' => DB::table('inv_customers')->where('inv_cus_id', $last->inv_pro_inv_party_id)->value('inv_cus_name') ?: 'Walk-in customer',
            'amount'   => (float) $rows->sum('inv_pro_inv_debit') - (float) $rows->where('inv_pro_inv_tran_type', 12)->sum('inv_pro_inv_credit'),
        ];
    }

    /** Customers who still owe money (the role's customers; their full balance) */
    public function customerDue($limit = 5)
    {
        $parties = null;
        if ($this->memberIds !== null) {
            $parties = $this->ledger(2)->whereIn('inv_pro_inv_tran_type', [1, 10, 11])->distinct()->pluck('inv_pro_inv_party_id')->filter()->all();
            if (!$parties) {
                return ['rows' => collect(), 'total' => 0.0, 'count' => 0];
            }
        }
        $q = $this->ledger(2, false)->whereNotNull('inv_pro_inv_party_id');
        if ($parties !== null) {
            $q->whereIn('inv_pro_inv_party_id', $parties);
        }
        $all = $q->groupBy('inv_pro_inv_party_id')
            ->selectRaw('inv_pro_inv_party_id as party, SUM(inv_pro_inv_debit) - SUM(inv_pro_inv_credit) as due, MAX(inv_pro_inv_issue_date) as last_date')
            ->havingRaw('due > 0.009')
            ->orderByDesc('due')
            ->get();

        $names = DB::table('inv_customers')->whereIn('inv_cus_id', $all->take($limit)->pluck('party')->all())->pluck('inv_cus_name', 'inv_cus_id');
        return [
            'rows' => $all->take($limit)->map(function ($r) use ($names) {
                $r->name = $names[$r->party] ?? ('Customer #' . $r->party);
                $r->due = (float) $r->due;
                return $r;
            }),
            'total' => (float) $all->sum('due'),
            'count' => $all->count(),
        ];
    }

    /** Suppliers the company still owes (admin only) */
    public function supplierDue($limit = 5)
    {
        if (!$this->canSeeCosts) {
            return null;
        }
        $all = $this->ledger(1, false)->whereNotNull('inv_pro_inv_party_id')
            ->groupBy('inv_pro_inv_party_id')
            ->selectRaw('inv_pro_inv_party_id as party, SUM(inv_pro_inv_debit) - SUM(inv_pro_inv_credit) as due, MAX(inv_pro_inv_issue_date) as last_date')
            ->havingRaw('due > 0.009')
            ->orderByDesc('due')
            ->get();

        $names = DB::table('inv_suppliers')->whereIn('inv_sup_id', $all->take($limit)->pluck('party')->all())
            ->selectRaw("inv_sup_id, COALESCE(NULLIF(inv_sup_com_name, ''), inv_sup_person) as name")->pluck('name', 'inv_sup_id');
        return [
            'rows' => $all->take($limit)->map(function ($r) use ($names) {
                $r->name = $names[$r->party] ?? ('Supplier #' . $r->party);
                $r->due = (float) $r->due;
                return $r;
            }),
            'total' => (float) $all->sum('due'),
            'count' => $all->count(),
        ];
    }
}

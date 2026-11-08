<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inv_acc_bank_statement;
use App\Services\AccountsReports;
use App\Services\AccountsSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accounts > Salary Payment: pays approved salaries (Payroll > Generate / Approve) from a bank or cash account.
 * Each payment is one new bank / cash movement of type 14 "Salary Payment", linked to the salary row
 * (emp_salary_generates id in inv_abs_reference_id). Nothing existing is changed.
 */
class SalaryPaymentController extends Controller
{
    const TYPE = 14;

    private function company() { return (int) Auth::user()->au_company_id; }
    private function branch() { return (int) Auth::user()->au_branch_id; }

    /** Salary rows of a month with what is paid already and what is still due */
    private function salaries($year, $month)
    {
        $rows = DB::table('emp_salary_generates as s')
            ->join('admin_user as u', 'u.au_id', '=', 's.user_id')
            ->where('s.company_id', $this->company())->where('s.branch_id', $this->branch())
            ->where('s.year', $year)->where('s.month', $month)
            ->orderBy('u.au_name')
            ->get(['s.id', 's.user_id', 's.final_salary', 's.status', 'u.au_name', 'u.au_user_id', 'u.au_mobile']);
        $paid = DB::table('inv_acc_bank_statements')
            ->where('inv_abs_company_id', $this->company())->where('inv_abs_branch_id', $this->branch())
            ->where('inv_abs_status', 1)->where('inv_abs_reference_type', self::TYPE)
            ->whereIn('inv_abs_reference_id', $rows->pluck('id')->all() ?: [0])
            ->groupBy('inv_abs_reference_id')
            ->selectRaw('inv_abs_reference_id id, SUM(inv_abs_debit) - SUM(inv_abs_credit) paid')->pluck('paid', 'id');
        return $rows->map(function ($r) use ($paid) {
            $r->final_salary = (float) $r->final_salary;
            $r->paid = (float) ($paid[$r->id] ?? 0);
            $r->due = round(max(0, $r->final_salary - $r->paid), 2);
            $r->approved = (int) $r->status === 1;
            return $r;
        });
    }

    public function index(Request $request)
    {
        $now = Carbon::today();
        $month = (int) $request->input('month', $now->month);
        $year = (int) $request->input('year', $now->year);
        if ($month < 1 || $month > 12) { $month = $now->month; }
        if ($year < 2000 || $year > $now->year + 1) { $year = $now->year; }

        $salaries = $this->salaries($year, $month);
        $accounts = (new AccountsReports($this->company(), $this->branch()))->accountSummary($now, $now);
        $payments = DB::table('inv_acc_bank_statements')
            ->where('inv_abs_company_id', $this->company())->where('inv_abs_branch_id', $this->branch())
            ->where('inv_abs_status', 1)->where('inv_abs_reference_type', self::TYPE)
            ->whereIn('inv_abs_reference_id', $salaries->pluck('id')->all() ?: [0])
            ->orderByDesc('inv_abs_id')->get();
        $accountNames = $accounts->pluck('label', 'id');
        $monthName = Carbon::create($year, $month, 1)->format('F Y');
        return view('inventory.accounts.salary_payment', compact('salaries', 'accounts', 'payments', 'accountNames', 'month', 'year', 'monthName'));
    }

    public function pay(Request $request)
    {
        $data = $request->validate([
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2000',
            'account_id' => 'required|integer',
            'pay_date'   => 'required|date_format:Y-m-d',
            'amount'     => 'required|array',
            'amount.*'   => 'nullable|numeric|min:0',
        ], [], ['account_id' => 'pay from', 'pay_date' => 'payment date']);

        $back = redirect()->route('accounts.salary-payment', ['month' => $data['month'], 'year' => $data['year']]);
        if ($err = AccountsSettings::lockError($data['pay_date'], $this->company(), $this->branch())) {
            return $back->withInput()->with('errmsg', $err);
        }
        $account = DB::table('inv_acc_bank_infos')->where('inv_abi_id', $data['account_id'])
            ->where('inv_abi_company_id', $this->company())->where('inv_abi_branch_id', $this->branch())->where('inv_abi_status', 1)->first();
        if (!$account) {
            return $back->withInput()->with('errmsg', 'Choose a bank or cash account of this branch.');
        }

        $salaries = $this->salaries($data['year'], $data['month'])->keyBy('id');
        $lines = [];
        foreach ($data['amount'] as $salaryId => $amount) {
            $amount = round((float) $amount, 2);
            if ($amount <= 0) { continue; }
            $row = $salaries->get((int) $salaryId);
            if (!$row) {
                return $back->withInput()->with('errmsg', 'One of the salaries is not of this branch and month.');
            }
            if (!$row->approved) {
                return $back->withInput()->with('errmsg', $row->au_name . "'s salary is not approved yet (Payroll > Salary Generate > Approve).");
            }
            if ($amount > $row->due + 0.005) {
                return $back->withInput()->with('errmsg', $row->au_name . ': ' . number_format($amount, 2) . ' is more than the ' . number_format($row->due, 2) . ' still due.');
            }
            $lines[] = [$row, $amount];
        }
        if (!$lines) {
            return $back->withInput()->with('errmsg', 'Enter at least one amount to pay.');
        }

        $total = array_sum(array_column($lines, 1));
        $balance = (float) DB::table('inv_acc_bank_statements')->where('inv_abs_bank_id', $account->inv_abi_id)
            ->where('inv_abs_company_id', $this->company())->where('inv_abs_branch_id', $this->branch())->where('inv_abs_status', 1)
            ->selectRaw('COALESCE(SUM(inv_abs_credit) - SUM(inv_abs_debit), 0) b')->value('b');
        if ((int) $account->inv_abi_account_type === 2 && $total > $balance + 0.005) {
            return $back->withInput()->with('errmsg', 'Not enough cash: ' . number_format($balance, 2) . ' in ' . $account->inv_abi_account_name . ', salaries ' . number_format($total, 2) . '.');
        }

        $voucher = 'SAL-' . Carbon::now()->format('YmdHis');
        $monthName = Carbon::create($data['year'], $data['month'], 1)->format('F Y');
        DB::transaction(function () use ($lines, $account, $data, $voucher, $monthName) {
            foreach ($lines as [$row, $amount]) {
                $st = new Inv_acc_bank_statement();
                $st->inv_abs_company_id = $this->company();
                $st->inv_abs_branch_id = $this->branch();
                $st->inv_abs_bank_id = $account->inv_abi_id;
                $st->inv_abs_reference_id = $row->id;             // emp_salary_generates id
                $st->inv_abs_reference_type = self::TYPE;          // 14 = salary payment
                $st->inv_abs_debit = $amount;
                $st->inv_abs_credit = 0;
                $st->inv_abs_transaction_date = $data['pay_date'];
                $st->inv_abs_voucher_no = $voucher;
                $st->inv_abs_description = 'Salary ' . $monthName . ': ' . $row->au_name;
                $st->inv_abs_status = 1;
                $st->inv_abs_submit_by = Auth::user()->au_id;
                $st->inv_abs_submit_at = Carbon::now();
                $st->save();
            }
        });

        AccountsSettings::log('salary.pay', count($lines) . ' salary payment(s) for ' . $monthName . ' from ' . $account->inv_abi_account_name . ' (' . $voucher . ')',
            'salary', $voucher, $total, ['salaries' => array_map(function ($l) { return ['id' => $l[0]->id, 'name' => $l[0]->au_name, 'amount' => $l[1]]; }, $lines)]);
        return $back->with('msg', count($lines) . ' salary payment(s) saved: ' . number_format($total, 2) . ' from ' . $account->inv_abi_account_name . ' (voucher ' . $voucher . ').');
    }
}

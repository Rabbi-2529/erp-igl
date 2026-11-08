<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_acc_bank_statement;
use App\Services\AccountsReports;
use App\Services\AccountsSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accounts > Petty Cash: small cash boxes for daily spending.
 * A petty cash box is a cash-type account (inv_acc_bank_infos, type 2, branch "Petty Cash"), so it can pay
 * expenses on Add Expense like any account. Top-ups and returns are transfers (contra, type 5) between accounts.
 * Only new rows are added; the main cash account stays the branch's oldest cash account.
 */
class PettyCashController extends Controller
{
    const MARK = 'Petty Cash';   // stored in inv_abi_branch_name to tell petty cash boxes apart

    private function company() { return (int) Auth::user()->au_company_id; }
    private function branch() { return (int) Auth::user()->au_branch_id; }

    private function boxes()
    {
        return DB::table('inv_acc_bank_infos')->where('inv_abi_company_id', $this->company())->where('inv_abi_branch_id', $this->branch())
            ->where('inv_abi_account_type', 2)->where('inv_abi_branch_name', self::MARK)->where('inv_abi_status', 1)->orderBy('inv_abi_id')->get();
    }

    private function balance($accountId)
    {
        return (float) DB::table('inv_acc_bank_statements')->where('inv_abs_bank_id', $accountId)
            ->where('inv_abs_company_id', $this->company())->where('inv_abs_branch_id', $this->branch())->where('inv_abs_status', 1)
            ->selectRaw('COALESCE(SUM(inv_abs_credit) - SUM(inv_abs_debit), 0) b')->value('b');
    }

    public function index()
    {
        $today = Carbon::today();
        $reports = new AccountsReports($this->company(), $this->branch());
        $accounts = $reports->accountSummary($today, $today);
        $boxIds = $this->boxes()->pluck('inv_abi_id')->all();
        $boxes = $accounts->whereIn('id', $boxIds)->values();
        $monthStart = $today->copy()->startOfMonth();
        $spent = DB::table('inv_acc_bank_statements')->whereIn('inv_abs_bank_id', $boxIds ?: [0])
            ->where('inv_abs_status', 1)->whereIn('inv_abs_reference_type', AccountsReports::EXPENSE_TYPES)
            ->whereBetween('inv_abs_transaction_date', [$monthStart->toDateString(), $today->toDateString()])
            ->groupBy('inv_abs_bank_id')->selectRaw('inv_abs_bank_id id, SUM(inv_abs_debit) v')->pluck('v', 'id');
        $recent = $boxIds ? $reports->movements($today->copy()->subMonths(3), $today)->whereIn('inv_abs_bank_id', $boxIds)->reverse()->take(15) : collect();
        $others = $accounts->whereNotIn('id', $boxIds)->values();
        return view('inventory.accounts.petty_cash', compact('boxes', 'spent', 'recent', 'others'));
    }

    public function create(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:60'], [], ['name' => 'name']);
        $data['name'] = trim($data['name']);
        if ($this->boxes()->contains(function ($b) use ($data) { return mb_strtolower($b->inv_abi_account_name) === mb_strtolower($data['name']); })) {
            return redirect()->route('accounts.petty-cash')->withInput()->with('errmsg', 'There is already a petty cash box called "' . $data['name'] . '".');
        }
        $main = DB::table('inv_acc_bank_infos')->where('inv_abi_company_id', $this->company())->where('inv_abi_branch_id', $this->branch())
            ->where('inv_abi_account_type', 2)->orderBy('inv_abi_id')->first();
        $box = new Inv_acc_bank_info();
        $box->inv_abi_company_id = $this->company();
        $box->inv_abi_branch_id = $this->branch();
        $box->inv_abi_bank_id = $main ? $main->inv_abi_bank_id : null;        // the same "Cash" bank as the main cash account
        $box->inv_abi_branch_name = self::MARK;
        $box->inv_abi_account_name = $data['name'];
        $box->inv_abi_account_no = $data['name'];                             // shown in account lists, e.g. "Cash (Office petty cash)"
        $box->inv_abi_open_date = Carbon::today()->toDateString();
        $box->inv_abi_account_type = 2;                                       // cash
        $box->inv_abi_status = 1;
        $box->inv_abi_submit_by = Auth::user()->au_id;
        $box->inv_abi_submit_at = Carbon::now();
        $box->save();
        AccountsSettings::log('petty.create', 'Petty cash box "' . $data['name'] . '" made', 'account', $box->inv_abi_id);
        return redirect()->route('accounts.petty-cash')->with('msg', 'Petty cash box "' . $data['name'] . '" made. Top it up to start using it.');
    }

    /** Top up (money into the box) or return (money out of the box back to an account) */
    public function move(Request $request)
    {
        $data = $request->validate([
            'direction' => 'required|in:topup,return',
            'box_id'    => 'required|integer',
            'other_id'  => 'required|integer|different:box_id',
            'amount'    => 'required|numeric|min:0.01',
            'date'      => 'required|date_format:Y-m-d',
            'note'      => 'nullable|string|max:150',
        ], [], ['other_id' => 'account', 'box_id' => 'petty cash box']);
        $back = redirect()->route('accounts.petty-cash');
        if ($err = AccountsSettings::lockError($data['date'], $this->company(), $this->branch())) {
            return $back->withInput()->with('errmsg', $err);
        }

        $box = $this->boxes()->firstWhere('inv_abi_id', (int) $data['box_id']);
        $other = DB::table('inv_acc_bank_infos')->where('inv_abi_id', $data['other_id'])->where('inv_abi_company_id', $this->company())
            ->where('inv_abi_branch_id', $this->branch())->where('inv_abi_status', 1)->first();
        if (!$box || !$other) {
            return $back->withInput()->with('errmsg', 'Choose a petty cash box and an account of this branch.');
        }
        [$from, $to] = $data['direction'] === 'topup' ? [$other, $box] : [$box, $other];
        $amount = round((float) $data['amount'], 2);
        $fromBalance = $this->balance($from->inv_abi_id);
        if ((int) $from->inv_abi_account_type === 2 && $amount > $fromBalance + 0.005) {
            return $back->withInput()->with('errmsg', 'Not enough money in ' . $from->inv_abi_account_name . ': ' . number_format($fromBalance, 2) . '.');
        }

        $voucher = 'PETTY-' . Carbon::now()->format('YmdHis');
        $what = $data['direction'] === 'topup' ? 'Petty cash top-up' : 'Petty cash return';
        $note = $what . ': ' . $from->inv_abi_account_name . ' to ' . $to->inv_abi_account_name . (!empty($data['note']) ? ' - ' . $data['note'] : '');
        DB::transaction(function () use ($from, $to, $amount, $data, $voucher, $note) {
            foreach ([[$from, $amount, 0], [$to, 0, $amount]] as [$acc, $debit, $credit]) {
                $st = new Inv_acc_bank_statement();
                $st->inv_abs_company_id = $this->company();
                $st->inv_abs_branch_id = $this->branch();
                $st->inv_abs_bank_id = $acc->inv_abi_id;
                $st->inv_abs_reference_id = $acc === $from ? $to->inv_abi_id : $from->inv_abi_id;
                $st->inv_abs_reference_type = 5;     // 5 = contra (transfer between own accounts)
                $st->inv_abs_debit = $debit;
                $st->inv_abs_credit = $credit;
                $st->inv_abs_transaction_date = $data['date'];
                $st->inv_abs_voucher_no = $voucher;
                $st->inv_abs_description = $note;
                $st->inv_abs_status = 1;
                $st->inv_abs_submit_by = Auth::user()->au_id;
                $st->inv_abs_submit_at = Carbon::now();
                $st->save();
            }
        });
        AccountsSettings::log('petty.' . $data['direction'], $note . ' (' . $voucher . ')', 'account', $box->inv_abi_id, $amount);
        return $back->with('msg', $what . ' of ' . number_format($amount, 2) . ' saved (voucher ' . $voucher . ').');
    }
}

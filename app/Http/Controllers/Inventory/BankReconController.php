<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\AccountsReports;
use App\Services\AccountsSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accounts > Bank Reconciliation (new table acc_bank_recon_marks).
 * Tick each ERP bank movement that also shows on the real bank statement. The ticked movements add up to what
 * the bank should show; the difference with the bank statement balance is what is missing or wrong.
 * Ticks are the only thing saved: no bank movement is changed.
 */
class BankReconController extends Controller
{
    private function company() { return (int) Auth::user()->au_company_id; }
    private function branch() { return (int) Auth::user()->au_branch_id; }

    private function date($value, Carbon $default)
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public function index(Request $request)
    {
        $today = Carbon::today();
        $asOf = $this->date($request->input('as_of'), $today);
        $reports = new AccountsReports($this->company(), $this->branch());
        $all = $reports->accountSummary($asOf, $asOf);
        $banks = $all->where('is_cash', false)->values();
        $accountId = (int) $request->input('account', optional($banks->first())->id);
        $account = $all->firstWhere('id', $accountId);
        $bankBalance = $request->filled('bank_balance') && is_numeric($request->input('bank_balance')) ? round((float) $request->input('bank_balance'), 2) : null;

        $open = collect();
        $ticked = collect();
        $sum = ['erp' => 0.0, 'ticked' => 0.0, 'open_in' => 0.0, 'open_out' => 0.0];
        if ($account) {
            $rows = $reports->movements(Carbon::create(1970, 1, 1), $asOf, $account->id);
            $marks = DB::table('acc_bank_recon_marks')->where('abr_company_id', $this->company())->where('abr_branch_id', $this->branch())
                ->where('abr_account_id', $account->id)->whereIn('abr_statement_id', $rows->pluck('inv_abs_id')->all() ?: [0])
                ->pluck('abr_cleared_date', 'abr_statement_id');
            foreach ($rows as $r) {
                $sum['erp'] += $r->in - $r->out;
                if ($marks->has($r->inv_abs_id)) {
                    $r->cleared_date = $marks[$r->inv_abs_id];
                    $sum['ticked'] += $r->in - $r->out;
                    $ticked->push($r);
                } else {
                    $sum['open_in'] += $r->in;
                    $sum['open_out'] += $r->out;
                    $open->push($r);
                }
            }
            $ticked = $ticked->sortByDesc(function ($r) { return ($r->cleared_date ?: $r->inv_abs_transaction_date) . sprintf('%012d', $r->inv_abs_id); })->take(100)->values();
        }
        $sum = array_map(function ($v) { return round($v, 2); }, $sum);
        $difference = $bankBalance === null ? null : round($bankBalance - $sum['ticked'], 2);
        return view('inventory.accounts.bank_reconciliation', compact('banks', 'all', 'account', 'asOf', 'bankBalance', 'open', 'ticked', 'sum', 'difference'));
    }

    /** Tick (on the bank statement) or untick movements of one account */
    public function mark(Request $request)
    {
        $data = $request->validate([
            'account'      => 'required|integer',
            'action'       => 'required|in:tick,untick,tick_until',
            'ids'          => 'nullable|array',
            'ids.*'        => 'integer',
            'cleared_date' => 'nullable|date_format:Y-m-d',
            'until'        => 'nullable|date_format:Y-m-d',
            'as_of'        => 'nullable|date_format:Y-m-d',
            'bank_balance' => 'nullable|numeric',
        ], [], ['cleared_date' => 'bank date']);
        $back = redirect()->route('accounts.bank-reconciliation', array_filter([
            'account' => $data['account'], 'as_of' => $data['as_of'] ?? null, 'bank_balance' => $data['bank_balance'] ?? null,
        ], function ($v) { return $v !== null && $v !== ''; }));

        $account = DB::table('inv_acc_bank_infos')->where('inv_abi_id', $data['account'])->where('inv_abi_company_id', $this->company())
            ->where('inv_abi_branch_id', $this->branch())->first();
        if (!$account) {
            return $back->with('errmsg', 'Choose an account of this branch.');
        }
        $rows = DB::table('inv_acc_bank_statements')->where('inv_abs_bank_id', $account->inv_abi_id)
            ->where('inv_abs_company_id', $this->company())->where('inv_abs_branch_id', $this->branch())->where('inv_abs_status', 1);

        if ($data['action'] === 'untick') {
            $ids = array_map('intval', $data['ids'] ?? []);
            if (!$ids) { return $back->with('errmsg', 'Choose the movements to untick.'); }
            $n = DB::table('acc_bank_recon_marks')->where('abr_company_id', $this->company())->where('abr_branch_id', $this->branch())
                ->where('abr_account_id', $account->inv_abi_id)->whereIn('abr_statement_id', $ids)->delete();
            AccountsSettings::log('recon.untick', $n . ' movement(s) of ' . $account->inv_abi_account_name . ' unticked', 'account', $account->inv_abi_id);
            return $back->with('msg', $n . ' movement(s) unticked.');
        }

        if ($data['action'] === 'tick_until') {
            if (empty($data['until'])) { return $back->with('errmsg', 'Choose the date to tick up to.'); }
            $rows->where('inv_abs_transaction_date', '<=', $data['until']);
        } else {
            $ids = array_map('intval', $data['ids'] ?? []);
            if (!$ids) { return $back->with('errmsg', 'Tick the movements that are on the bank statement first.'); }
            $rows->whereIn('inv_abs_id', $ids);
        }
        $found = $rows->get(['inv_abs_id', 'inv_abs_transaction_date']);
        $now = Carbon::now();
        $insert = $found->map(function ($r) use ($account, $data, $now) {
            return [
                'abr_company_id' => $this->company(), 'abr_branch_id' => $this->branch(), 'abr_account_id' => $account->inv_abi_id,
                'abr_statement_id' => $r->inv_abs_id,
                'abr_cleared_date' => !empty($data['cleared_date']) ? $data['cleared_date'] : $r->inv_abs_transaction_date,
                'abr_marked_by' => Auth::user()->au_id, 'abr_marked_at' => $now,
            ];
        })->all();
        $n = 0;
        foreach (array_chunk($insert, 500) as $chunk) {
            $n += DB::table('acc_bank_recon_marks')->insertOrIgnore($chunk);
        }
        AccountsSettings::log('recon.tick', $n . ' movement(s) of ' . $account->inv_abi_account_name . ' ticked as on the bank statement', 'account', $account->inv_abi_id);
        return $back->with('msg', $n . ' movement(s) ticked as on the bank statement.');
    }
}

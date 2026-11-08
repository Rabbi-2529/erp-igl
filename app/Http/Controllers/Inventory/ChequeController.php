<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inv_acc_bank_statement;
use App\Models\Inv_product_inventory;
use App\Services\AccountsReports;
use App\Services\AccountsSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accounts > Cheque Register (new table acc_cheques).
 * A cheque is saved as "pending" and moves no money. When it clears, the payment is saved exactly like the
 * existing pages do it: Customer Payment (received from a customer), Supplier Payment (issued to a supplier),
 * or Bank Deposit / Withdraw (other). Bounced and cancelled cheques move no money.
 */
class ChequeController extends Controller
{
    const STATUSES = ['pending', 'cleared', 'bounced', 'cancelled'];

    private function company() { return (int) Auth::user()->au_company_id; }
    private function branch() { return (int) Auth::user()->au_branch_id; }

    private function cheques()
    {
        return DB::table('acc_cheques')->where('chq_company_id', $this->company())->where('chq_branch_id', $this->branch());
    }

    private function customers()
    {
        return DB::table('inv_customers')->where('inv_cus_com_id', $this->company())->where('inv_cus_branch_id', $this->branch())
            ->where('inv_cus_status', 1)->orderBy('inv_cus_name')->get(['inv_cus_id', 'inv_cus_name', 'inv_cus_mobile']);
    }

    private function suppliers()
    {
        return DB::table('inv_suppliers')->where('inv_sup_com_id', $this->company())->where('inv_sup_status', 1)
            ->selectRaw("inv_sup_id, COALESCE(NULLIF(inv_sup_com_name, ''), inv_sup_person) name")->orderBy('name')->get();
    }

    private function account($id)
    {
        return DB::table('inv_acc_bank_infos')->where('inv_abi_id', (int) $id)->where('inv_abi_company_id', $this->company())
            ->where('inv_abi_branch_id', $this->branch())->where('inv_abi_status', 1)->first();
    }

    public function index(Request $request)
    {
        $today = Carbon::today();
        $status = in_array($request->input('status'), array_merge(self::STATUSES, ['all']), true) ? $request->input('status') : 'pending';
        $direction = in_array($request->input('direction'), ['received', 'issued'], true) ? $request->input('direction') : '';

        $list = $this->cheques()
            ->when($status !== 'all', function ($q) use ($status) { $q->where('chq_status', $status); })
            ->when($direction, function ($q) use ($direction) { $q->where('chq_direction', $direction); })
            ->orderBy($status === 'pending' ? 'chq_date' : 'chq_id', $status === 'pending' ? 'asc' : 'desc')
            ->limit(300)->get();

        $pending = $this->cheques()->where('chq_status', 'pending')->get();
        $soon = $today->copy()->addDays(7)->toDateString();
        $cards = [
            'received'  => $pending->where('chq_direction', 'received')->sum('chq_amount'),
            'issued'    => $pending->where('chq_direction', 'issued')->sum('chq_amount'),
            'due'       => $pending->filter(function ($c) use ($soon) { return $c->chq_date <= $soon; })->count(),
            'bounced'   => $this->cheques()->where('chq_status', 'bounced')->where('chq_status_date', '>=', $today->copy()->startOfMonth()->toDateString())->count(),
        ];
        $accounts = (new AccountsReports($this->company(), $this->branch()))->accountSummary($today, $today);
        $accountNames = $accounts->pluck('label', 'id');
        $customers = $this->customers();
        $suppliers = $this->suppliers();
        return view('inventory.accounts.cheques', compact('list', 'status', 'direction', 'cards', 'accounts', 'accountNames', 'customers', 'suppliers', 'today', 'soon'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'direction'   => 'required|in:received,issued',
            'party_type'  => 'required|in:customer,supplier,other',
            'customer_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer',
            'party_name'  => 'nullable|string|max:120',
            'number'      => 'required|string|max:40',
            'bank_name'   => 'nullable|string|max:120',
            'chq_date'    => 'required|date_format:Y-m-d',
            'amount'      => 'required|numeric|min:0.01',
            'account_id'  => 'nullable|integer',
            'note'        => 'nullable|string|max:255',
        ], [], ['chq_date' => 'cheque date', 'number' => 'cheque number', 'account_id' => 'account']);
        $back = redirect()->route('accounts.cheques');

        $partyId = null;
        $partyName = trim((string) ($data['party_name'] ?? ''));
        if ($data['direction'] === 'received' && $data['party_type'] === 'supplier' || $data['direction'] === 'issued' && $data['party_type'] === 'customer') {
            return $back->withInput()->with('errmsg', 'A received cheque comes from a customer (or other); an issued cheque goes to a supplier (or other).');
        }
        if ($data['party_type'] === 'customer') {
            $c = $this->customers()->firstWhere('inv_cus_id', (int) ($data['customer_id'] ?? 0));
            if (!$c) { return $back->withInput()->with('errmsg', 'Choose the customer.'); }
            [$partyId, $partyName] = [$c->inv_cus_id, $c->inv_cus_name];
        } elseif ($data['party_type'] === 'supplier') {
            $s = $this->suppliers()->firstWhere('inv_sup_id', (int) ($data['supplier_id'] ?? 0));
            if (!$s) { return $back->withInput()->with('errmsg', 'Choose the supplier.'); }
            [$partyId, $partyName] = [$s->inv_sup_id, $s->name];
        } elseif ($partyName === '') {
            return $back->withInput()->with('errmsg', 'Write who the cheque is from / to.');
        }
        if (!empty($data['account_id']) && !$this->account($data['account_id'])) {
            return $back->withInput()->with('errmsg', 'Choose an account of this branch.');
        }
        $duplicate = $this->cheques()->where('chq_direction', $data['direction'])->where('chq_number', $data['number'])
            ->whereIn('chq_status', ['pending', 'cleared'])->when($data['bank_name'] ?? null, function ($q, $b) { $q->where('chq_bank_name', $b); })->exists();
        if ($duplicate) {
            return $back->withInput()->with('errmsg', 'Cheque ' . $data['number'] . ' is already in the register.');
        }

        DB::table('acc_cheques')->insert([
            'chq_company_id' => $this->company(),
            'chq_branch_id'  => $this->branch(),
            'chq_direction'  => $data['direction'],
            'chq_party_type' => $data['party_type'],
            'chq_party_id'   => $partyId,
            'chq_party_name' => $partyName,
            'chq_number'     => $data['number'],
            'chq_bank_name'  => $data['bank_name'] ?? null,
            'chq_date'       => $data['chq_date'],
            'chq_amount'     => round((float) $data['amount'], 2),
            'chq_account_id' => !empty($data['account_id']) ? (int) $data['account_id'] : null,
            'chq_status'     => 'pending',
            'chq_note'       => $data['note'] ?? null,
            'chq_created_by' => Auth::user()->au_id,
            'chq_created_at' => Carbon::now(),
        ]);
        AccountsSettings::log('cheque.add', ucfirst($data['direction']) . ' cheque ' . $data['number'] . ' (' . $partyName . ') saved as pending', 'cheque', $data['number'], $data['amount']);
        return $back->with('msg', 'Cheque ' . $data['number'] . ' of ' . number_format($data['amount'], 2) . ' saved as pending. Mark it cleared when the bank pays it.');
    }

    /** Cleared: the money moves now, saved the same way as the payment pages */
    public function clear(Request $request, $id)
    {
        $data = $request->validate([
            'account_id' => 'required|integer',
            'date'       => 'required|date_format:Y-m-d',
        ], [], ['account_id' => 'account', 'date' => 'clearing date']);
        $back = redirect()->route('accounts.cheques');
        $chq = $this->cheques()->where('chq_id', (int) $id)->first();
        if (!$chq || $chq->chq_status !== 'pending') {
            return $back->with('errmsg', 'Only a pending cheque can be marked cleared.');
        }
        $account = $this->account($data['account_id']);
        if (!$account) {
            return $back->with('errmsg', 'Choose an account of this branch.');
        }
        if ($err = AccountsSettings::lockError($data['date'], $this->company(), $this->branch())) {
            return $back->with('errmsg', $err);
        }
        $amount = (float) $chq->chq_amount;
        $out = $chq->chq_direction === 'issued';
        if ($out) {
            $balance = (float) Inv_acc_bank_statement::getAvailableBalanceByBankId($account->inv_abi_id);
            if ($balance + 0.005 < $amount) {
                return $back->with('errmsg', 'Not enough money in ' . $account->inv_abi_account_name . ' (' . number_format($balance, 2) . ') to pay cheque ' . $chq->chq_number . '.');
            }
        }

        $now = Carbon::now();
        $voucher = $now->format('YmdHis');
        $desc = 'Cheque ' . $chq->chq_number . ($chq->chq_bank_name ? ' (' . $chq->chq_bank_name . ')' : '') . ' - ' . $chq->chq_party_name;
        DB::transaction(function () use ($chq, $account, $data, $amount, $out, $now, $voucher, $desc) {
            $inventoryId = null;
            if ($chq->chq_party_type !== 'other') {
                // Customer / supplier ledger: the same row Customer Payment / Supplier Payment saves
                $inv = new Inv_product_inventory();
                $inv->inv_pro_inv_com_id = $this->company();
                $inv->inv_pro_inv_branch_id = $this->branch();
                $inv->inv_pro_inv_party_id = $chq->chq_party_id;
                $inv->inv_pro_inv_debit = 0;
                $inv->inv_pro_inv_credit = $amount;
                $inv->inv_pro_inv_deal_type = $chq->chq_party_type === 'customer' ? 2 : 1;   // 2 customer, 1 supplier
                $inv->inv_pro_inv_tran_type = 4;                                              // 4 payment
                $inv->inv_pro_inv_tran_desc = $desc;
                $inv->inv_pro_inv_issue_date = $data['date'];
                $inv->inv_pro_inv_invoice_no = $voucher;
                $inv->inv_pro_inv_status = 1;
                $inv->inv_pro_inv_submit_by = Auth::user()->au_id;
                $inv->inv_pro_inv_submit_at = $now;
                $inv->save();
                $inventoryId = $inv->inv_pro_inv_id;
            }

            $st = new Inv_acc_bank_statement();
            $st->inv_abs_company_id = $this->company();
            $st->inv_abs_branch_id = $this->branch();
            $st->inv_abs_bank_id = $account->inv_abi_id;
            $st->inv_abs_reference_id = $chq->chq_party_id;
            $st->inv_abs_inventory_id = $inventoryId;
            // 1 customer payment, 2 supplier payment, 6 bank deposit, 7 bank withdraw
            $st->inv_abs_reference_type = $chq->chq_party_type === 'customer' ? 1 : ($chq->chq_party_type === 'supplier' ? 2 : ($out ? 7 : 6));
            $st->inv_abs_debit = $out ? $amount : 0;
            $st->inv_abs_credit = $out ? 0 : $amount;
            $st->inv_abs_transaction_date = $data['date'];
            $st->inv_abs_description = $desc;
            $st->inv_abs_voucher_no = $voucher;
            $st->inv_abs_status = 1;
            $st->inv_abs_submit_by = Auth::user()->au_id;
            $st->inv_abs_submit_at = $now;
            $st->save();

            DB::table('acc_cheques')->where('chq_id', $chq->chq_id)->update([
                'chq_status' => 'cleared', 'chq_status_date' => $data['date'], 'chq_account_id' => $account->inv_abi_id,
                'chq_statement_id' => $st->inv_abs_id, 'chq_inventory_id' => $inventoryId,
                'chq_updated_by' => Auth::user()->au_id, 'chq_updated_at' => $now,
            ]);
        });
        AccountsSettings::log('cheque.clear', 'Cheque ' . $chq->chq_number . ' (' . $chq->chq_party_name . ') cleared ' . ($out ? 'from ' : 'into ') . $account->inv_abi_account_name, 'cheque', $chq->chq_id, $amount);
        return $back->with('msg', 'Cheque ' . $chq->chq_number . ' cleared: ' . number_format($amount, 2) . ($out ? ' paid from ' : ' added to ') . $account->inv_abi_account_name . '.');
    }

    public function bounce(Request $request, $id)
    {
        return $this->close($request, $id, 'bounced');
    }

    public function cancel(Request $request, $id)
    {
        return $this->close($request, $id, 'cancelled');
    }

    /** Bounced / cancelled: only the cheque's status changes, no money moves */
    private function close(Request $request, $id, $status)
    {
        $reason = trim((string) $request->input('reason'));
        $back = redirect()->route('accounts.cheques');
        $chq = $this->cheques()->where('chq_id', (int) $id)->first();
        if (!$chq || $chq->chq_status !== 'pending') {
            return $back->with('errmsg', 'Only a pending cheque can be marked ' . $status . '.');
        }
        $note = trim(($chq->chq_note ? $chq->chq_note . ' | ' : '') . ucfirst($status) . ($reason !== '' ? ': ' . mb_substr($reason, 0, 120) : ''));
        DB::table('acc_cheques')->where('chq_id', $chq->chq_id)->update([
            'chq_status' => $status, 'chq_status_date' => Carbon::today()->toDateString(), 'chq_note' => mb_substr($note, 0, 255),
            'chq_updated_by' => Auth::user()->au_id, 'chq_updated_at' => Carbon::now(),
        ]);
        AccountsSettings::log('cheque.' . ($status === 'bounced' ? 'bounce' : 'cancel'), 'Cheque ' . $chq->chq_number . ' (' . $chq->chq_party_name . ') marked ' . $status . ($reason !== '' ? ': ' . $reason : ''), 'cheque', $chq->chq_id, $chq->chq_amount);
        return $back->with('msg', 'Cheque ' . $chq->chq_number . ' marked ' . $status . '. No money was moved.');
    }
}

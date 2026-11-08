<?php

namespace App\Http\Controllers\Lc;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_acc_bank_statement;
use App\Models\Inv_product_inventory;
use App\Models\Lc_cost;
use App\Models\Lc_file;
use App\Models\Lc_letter;
use Carbon\Carbon;
use Auth;
use DB;

/**
 * LC opening, LC statement (payments and import costs), LC summary and payable per supplier.
 */
class LcController extends Controller
{
    use LcHelpers;

    // ============================================================
    //                      LC OPENING
    // ============================================================
    public function openForm(Request $request)
    {
        $files = Lc_file::with('supplier')
            ->where('lcf_company_id', $this->com())
            ->where('lcf_branch_id', $this->branch())
            ->where('lcf_status', 1)
            ->orderBy('lcf_id', 'desc')
            ->get();
        $selectedFile = $files->firstWhere('lcf_id', (int) $request->file);
        $issuingBanks = $this->bankOptions(true);
        $payBanks = $this->bankOptions();

        return view('lc.opening', compact('files', 'selectedFile', 'issuingBanks', 'payBanks'));
    }

    public function openStore(Request $request)
    {
        $request->validate([
            'file_id'        => 'required|integer',
            'lc_no'          => 'required|string|max:100',
            'lc_date'        => 'required|date_format:Y-m-d',
            'bank_id'        => 'required|integer',
            'lc_value_fc'    => 'required|numeric|gt:0',
            'lc_rate'        => 'required|numeric|gt:0',
            'margin_percent' => 'required|numeric|min:0|max:100',
            'expiry_date'    => 'nullable|date_format:Y-m-d',
            'last_ship_date' => 'nullable|date_format:Y-m-d',
            'remarks'        => 'nullable|string|max:255',
            'pay_margin'     => 'nullable|in:1',
            'margin_bank_id' => 'required_with:pay_margin|nullable|integer',
        ]);

        $file = Lc_file::where('lcf_id', $request->file_id)
            ->where('lcf_company_id', $this->com())
            ->where('lcf_branch_id', $this->branch())
            ->where('lcf_status', 1)
            ->first();
        if (!$file) {
            return redirect()->back()->withInput()->with('errmsg', 'Select an open file (an LC is not opened for it yet).');
        }
        if (!$this->bankOptions(true)->has($request->bank_id)) {
            return redirect()->back()->withInput()->with('errmsg', 'Select the issuing bank.');
        }
        if (Lc_letter::where('lc_company_id', $this->com())->where('lc_no', $request->lc_no)->exists()) {
            return redirect()->back()->withInput()->with('errmsg', 'LC no ' . $request->lc_no . ' is already used.');
        }

        $rate = $file->lcf_currency === 'BDT' ? 1 : $request->lc_rate;
        $valueBdt = round($request->lc_value_fc * $rate, 2);
        $marginAmount = round($valueBdt * $request->margin_percent / 100, 2);

        try {
            $lc = DB::transaction(function () use ($request, $file, $rate, $valueBdt, $marginAmount) {
                $lc = Lc_letter::create([
                    'lc_company_id'     => $this->com(),
                    'lc_branch_id'      => $this->branch(),
                    'lc_file_id'        => $file->lcf_id,
                    'lc_no'             => $request->lc_no,
                    'lc_date'           => $request->lc_date,
                    'lc_bank_id'        => $request->bank_id,
                    'lc_value_fc'       => $request->lc_value_fc,
                    'lc_rate'           => $rate,
                    'lc_value_bdt'      => $valueBdt,
                    'lc_margin_percent' => $request->margin_percent,
                    'lc_expiry_date'    => $request->expiry_date,
                    'lc_last_ship_date' => $request->last_ship_date,
                    'lc_remarks'        => $request->remarks,
                    'lc_status'         => 1,
                    'lc_submit_by'      => Auth::user()->au_id,
                    'lc_submit_at'      => Carbon::now(),
                ]);

                $file->lcf_status = 2;
                $file->save();

                // Optional: pay the margin now
                if ($request->pay_margin && $marginAmount > 0) {
                    $lc->setRelation('file', $file);
                    $this->postCost($lc, 1, $marginAmount, $request->lc_date, $request->margin_bank_id, $request->margin_percent . '% margin');
                }

                return $lc;
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('errmsg', $e->getMessage());
        }

        return redirect()->route('lc.statement', ['lc' => $lc->lc_id])->with('msg', 'LC ' . $lc->lc_no . ' opened.');
    }

    // ============================================================
    //                      LC STATEMENT
    // ============================================================
    public function statement(Request $request)
    {
        $lcs = $this->lcList();
        $lc = $request->lc ? $this->findLc($request->lc) : null;
        if ($lc) {
            $lc->load(['costs.bank.bank_info', 'shipments.items']);
        }
        $payBanks = $this->bankOptions();
        $heads = Lc_letter::HEADS;

        return view('lc.statement', compact('lcs', 'lc', 'payBanks', 'heads'));
    }

    public function storeCost(Request $request, $lcId)
    {
        $lc = $this->findLc($lcId);
        $request->validate([
            'head'        => 'required|integer|in:' . implode(',', array_keys(Lc_letter::HEADS)),
            'amount'      => 'required|numeric|gt:0',
            'cost_date'   => 'required|date_format:Y-m-d',
            'bank_id'     => 'required|integer',
            'description' => 'nullable|string|max:150',
        ]);

        try {
            DB::transaction(function () use ($request, $lc) {
                $this->postCost($lc, (int) $request->head, $request->amount, $request->cost_date, $request->bank_id, $request->description);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('errmsg', $e->getMessage());
        }

        return redirect()->route('lc.statement', ['lc' => $lc->lc_id])
            ->with('msg', Lc_letter::HEADS[$request->head] . ' of ' . number_format($request->amount, 2) . ' recorded.');
    }

    /**
     * Records an LC payment: the LC cost, a bank statement row (type 13, money out of the paying account)
     * and, for margin / retirement, a supplier payment in the supplier ledger.
     */
    private function postCost(Lc_letter $lc, $head, $amount, $date, $bankId, $description = null)
    {
        $amount = round($amount, 2);
        $bank = Inv_acc_bank_info::where('inv_abi_id', $bankId)
            ->where('inv_abi_company_id', $this->com())
            ->where('inv_abi_branch_id', $this->branch())
            ->where('inv_abi_status', 1)
            ->first();
        if (!$bank) {
            throw new \RuntimeException('Select the bank / cash account the payment is made from.');
        }

        $balance = Inv_acc_bank_statement::getAvailableBalanceByBankId($bank->inv_abi_id);
        if ($balance < $amount) {
            throw new \RuntimeException('Insufficient balance: ' . number_format($amount, 2) . ' is more than the available ' . number_format($balance, 2) . ' in the selected account.');
        }

        $headLabel = Lc_letter::HEADS[$head];
        $voucherNo = 'LCP-' . Carbon::now()->format('YmdHis') . '-' . random_int(10, 99);
        $text = mb_substr('LC ' . $lc->lc_no . ': ' . $headLabel . ($description ? ' - ' . $description : ''), 0, 255);

        $statement = new Inv_acc_bank_statement();
        $statement->inv_abs_company_id = $this->com();
        $statement->inv_abs_branch_id = $this->branch();
        $statement->inv_abs_reference_id = $lc->lc_id;
        $statement->inv_abs_reference_type = 13; //13= LC Payment
        $statement->inv_abs_bank_id = $bank->inv_abi_id;
        $statement->inv_abs_debit = $amount;
        $statement->inv_abs_credit = 0;
        $statement->inv_abs_transaction_date = $date;
        $statement->inv_abs_voucher_no = $voucherNo;
        $statement->inv_abs_description = $text;
        $statement->inv_abs_status = 1;
        $statement->inv_abs_submit_by = Auth::user()->au_id;
        $statement->inv_abs_submit_at = Carbon::now();
        $statement->save();

        // Margin and retirement pay the supplier for the goods
        $supplierRow = null;
        if (in_array($head, Lc_letter::GOODS_HEADS)) {
            $supplierRow = new Inv_product_inventory();
            $supplierRow->inv_pro_inv_com_id = $this->com();
            $supplierRow->inv_pro_inv_branch_id = $this->branch();
            $supplierRow->inv_pro_inv_party_id = $lc->file->lcf_supplier_id;
            $supplierRow->inv_pro_inv_debit = 0;
            $supplierRow->inv_pro_inv_credit = $amount;
            $supplierRow->inv_pro_inv_tran_type = 4; //4= supplier payment
            $supplierRow->inv_pro_inv_deal_type = 1; //1= supplier
            $supplierRow->inv_pro_inv_tran_desc = $text;
            $supplierRow->inv_pro_inv_issue_date = $date;
            $supplierRow->inv_pro_inv_invoice_no = $voucherNo;
            $supplierRow->inv_pro_inv_status = 1;
            $supplierRow->inv_pro_inv_submit_by = Auth::user()->au_id;
            $supplierRow->inv_pro_inv_submit_at = Carbon::now();
            $supplierRow->save();
        }

        return Lc_cost::create([
            'lcx_company_id'        => $this->com(),
            'lcx_branch_id'         => $this->branch(),
            'lcx_lc_id'             => $lc->lc_id,
            'lcx_date'              => $date,
            'lcx_head'              => $head,
            'lcx_amount'            => $amount,
            'lcx_bank_id'           => $bank->inv_abi_id,
            'lcx_bank_statement_id' => $statement->inv_abs_id,
            'lcx_supplier_inv_id'   => $supplierRow ? $supplierRow->inv_pro_inv_id : null,
            'lcx_voucher_no'        => $voucherNo,
            'lcx_description'       => $description,
            'lcx_status'            => 1,
            'lcx_submit_by'         => Auth::user()->au_id,
            'lcx_submit_at'         => Carbon::now(),
        ]);
    }

    /**
     * Removes a wrongly entered payment: the bank and supplier ledger rows go, the LC cost is kept as deleted.
     */
    public function deleteCost($id)
    {
        $cost = Lc_cost::where('lcx_id', $id)
            ->where('lcx_company_id', $this->com())
            ->where('lcx_branch_id', $this->branch())
            ->where('lcx_status', 1)
            ->firstOrFail();

        DB::transaction(function () use ($cost) {
            if ($cost->lcx_bank_statement_id) {
                Inv_acc_bank_statement::where('inv_abs_id', $cost->lcx_bank_statement_id)->delete();
            }
            if ($cost->lcx_supplier_inv_id) {
                Inv_product_inventory::where('inv_pro_inv_id', $cost->lcx_supplier_inv_id)->delete();
            }
            $cost->lcx_status = 0;
            $cost->save();
        });

        return redirect()->route('lc.statement', ['lc' => $cost->lcx_lc_id])
            ->with('msg', $cost->head_label . ' of ' . number_format($cost->lcx_amount, 2) . ' deleted and taken back out of the bank statement.');
    }

    // ============================================================
    //                      LC SUMMARY
    // ============================================================
    public function summary()
    {
        $lcs = Lc_letter::with(['file.supplier', 'file.items', 'bank.bank_info', 'costs', 'shipments'])
            ->where('lc_company_id', $this->com())
            ->where('lc_branch_id', $this->branch())
            ->orderBy('lc_id', 'desc')
            ->get();

        return view('lc.summary', compact('lcs'));
    }

    // ============================================================
    //               DEBITORS BALANCE (LC payable per supplier)
    // ============================================================
    public function debitors()
    {
        $lcs = Lc_letter::with(['file.supplier', 'costs'])
            ->where('lc_company_id', $this->com())
            ->where('lc_branch_id', $this->branch())
            ->orderBy('lc_id', 'desc')
            ->get();

        $suppliers = $lcs->groupBy(function ($lc) {
            return $lc->file->lcf_supplier_id;
        })->map(function ($supplierLcs) {
            $supplier = $supplierLcs->first()->file->supplier;
            return (object) [
                'name'      => $supplier->inv_sup_person ?? 'N/A',
                'company'   => $supplier->inv_sup_com_name ?? '',
                'lcs'       => $supplierLcs,
                'value'     => $supplierLcs->sum('lc_value_bdt'),
                'paid'      => $supplierLcs->sum(function ($lc) { return $lc->goodsPaid(); }),
                'payable'   => $supplierLcs->sum(function ($lc) { return $lc->payable(); }),
            ];
        })->sortByDesc('payable')->values();

        return view('lc.debitors', compact('suppliers'));
    }
}

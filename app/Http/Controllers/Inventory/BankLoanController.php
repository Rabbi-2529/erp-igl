<?php

namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_acc_bank_loan;
use App\Models\Inv_acc_bank_loan_payment;
use App\Models\Inv_acc_bank_statement;
use Session;
use DB;
use Exception;

class BankLoanController extends Controller
{
    /**
     * Loan Type = Frequency mapping
     *   1 = Monthly      → 1 month
     *   2 = Quarterly    → 3 months
     *   3 = Half-Yearly  → 6 months
     *   4 = Yearly       → 12 months
     */
    private $loanTypeMap = [
        1 => 1,
        2 => 3,
        3 => 6,
        4 => 12,
    ];

    // ============================================================
    //                          INDEX
    // ============================================================
    public function index()
    {
        $companyId = Auth::user()->au_company_id;
        $branchId  = Auth::user()->au_branch_id;

        $loans = Inv_acc_bank_loan::where('inv_abl_company_id', $companyId)
            ->where('inv_abl_branch_id', $branchId)
            ->orderBy('inv_abl_id', 'desc')
            ->get();

        $totalOutstanding = Inv_acc_bank_loan::where('inv_abl_company_id', $companyId)
            ->where('inv_abl_branch_id', $branchId)
            ->where('inv_abl_status', 1)
            ->sum('inv_abl_outstanding_balance');

        $totalMonthlyEMI = Inv_acc_bank_loan::where('inv_abl_company_id', $companyId)
            ->where('inv_abl_branch_id', $branchId)
            ->where('inv_abl_status', 1)
            ->sum('inv_abl_emi_amount');

        $activeLoansCount = Inv_acc_bank_loan::where('inv_abl_company_id', $companyId)
            ->where('inv_abl_branch_id', $branchId)
            ->where('inv_abl_status', 1)
            ->count();

        return view('inventory.accounts.bank.loan-list', compact(
            'loans',
            'totalOutstanding',
            'totalMonthlyEMI',
            'activeLoansCount'
        ));
    }

    // ============================================================
    //                          CREATE
    // ============================================================
    public function create()
    {
        $banks = $this->getAvailableBanks();

        return view('inventory.accounts.bank.loan-add', compact('banks'));
    }

    // ============================================================
    //                          STORE
    // ============================================================
    public function store(Request $request)
    {
        $request->validate([
            'bank_id'           => 'required|integer',
            'loan_name'         => 'required|string|max:255',
            'loan_type'         => 'required|integer|in:1,2,3,4',
            'principal_amount'  => 'required|numeric|min:0',
            'interest_rate'     => 'required|numeric|min:0',
            'tenure_months'     => 'required|integer|min:1',
            'disbursement_date' => 'required',
        ]);

        DB::beginTransaction();
        try {
            // Verify bank
            $bank = Inv_acc_bank_info::where('inv_abi_id', $request->bank_id)
                ->where('inv_abi_company_id', Auth::user()->au_company_id)
                ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
                ->first();

            if (!$bank) {
                return redirect()->back()->withInput()
                    ->with('errmsg', 'The selected bank does not belong to your company.');
            }

            // Parse date
            $disbursementDate = $this->parseDate($request->disbursement_date);
            if (!$disbursementDate) {
                return redirect()->back()->withInput()
                    ->with('errmsg', 'Invalid disbursement date format: ' . $request->disbursement_date);
            }

            // Derived values
            $loanType        = (int) $request->loan_type;
            $monthsPerPeriod = $this->loanTypeMap[$loanType];
            $firstEmiDate    = $disbursementDate->copy()->addMonths($monthsPerPeriod);
            $maturityDate    = $disbursementDate->copy()->addMonths((int) $request->tenure_months);

            $emi = Inv_acc_bank_loan::calculateEMI(
                (float) $request->principal_amount,
                (float) $request->interest_rate,
                (int)   $request->tenure_months,
                $loanType
            );

            // Save loan
            $loan = new Inv_acc_bank_loan();
            $loan->inv_abl_company_id          = Auth::user()->au_company_id;
            $loan->inv_abl_branch_id           = Auth::user()->au_branch_id;
            $loan->inv_abl_bank_id             = $request->bank_id;
            $loan->inv_abl_loan_name           = $request->loan_name;
            $loan->inv_abl_loan_type           = $loanType;
            $loan->inv_abl_loan_account_no     = $request->loan_account_no;
            $loan->inv_abl_principal_amount    = $request->principal_amount;
            $loan->inv_abl_interest_rate       = $request->interest_rate;
            $loan->inv_abl_interest_type       = $request->interest_type ?? 1;
            $loan->inv_abl_tenure_months       = $request->tenure_months;
            $loan->inv_abl_emi_amount          = $emi;
            $loan->inv_abl_disbursement_date   = $disbursementDate->format('Y-m-d');
            $loan->inv_abl_first_emi_date      = $firstEmiDate->format('Y-m-d');
            $loan->inv_abl_maturity_date       = $maturityDate->format('Y-m-d');
            $loan->inv_abl_outstanding_balance = $request->principal_amount;
            $loan->inv_abl_total_paid          = 0;
            $loan->inv_abl_total_interest_paid = 0;
            $loan->inv_abl_status              = 1;
            $loan->inv_abl_remarks             = $request->remarks;
            $loan->inv_abl_submit_by           = Auth::user()->au_id;
            $loan->inv_abl_submit_at           = Carbon::now();
            $loan->save();

            // Bank statement
            $statement = new Inv_acc_bank_statement();
            $statement->inv_abs_company_id       = Auth::user()->au_company_id;
            $statement->inv_abs_branch_id        = Auth::user()->au_branch_id;
            $statement->inv_abs_reference_id     = $loan->inv_abl_id;
            $statement->inv_abs_reference_type   = 7;
            $statement->inv_abs_bank_id          = $request->bank_id;
            $statement->inv_abs_credit           = $request->principal_amount;
            $statement->inv_abs_debit            = 0;
            $statement->inv_abs_transaction_date = $disbursementDate->format('Y-m-d');
            $statement->inv_abs_voucher_no       = 'LOAN-' . Carbon::now()->format('YmdHis');
            $statement->inv_abs_description      = 'Loan disbursement: ' . $request->loan_name;
            $statement->inv_abs_status           = 1;
            $statement->inv_abs_submit_by        = Auth::user()->au_id;
            $statement->inv_abs_submit_at        = Carbon::now();
            $statement->save();

            DB::commit();

            Session::flash('msg', 'Bank Loan Created Successfully.');
            return redirect()->route('accounts.bank-loan.list');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('errmsg', 'Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
        }
    }

    // ============================================================
    //                          SHOW (Loan Details)
    // ============================================================
    public function show($id)
    {
        $loan = Inv_acc_bank_loan::where('inv_abl_id', $id)
            ->where('inv_abl_company_id', Auth::user()->au_company_id)
            ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
            ->firstOrFail();

        $schedule = $loan->getAmortizationSchedule();
        $payments = $loan->payments()
            ->orderBy('inv_ablp_payment_date', 'desc')
            ->get();

        return view('inventory.accounts.bank.loan-details', compact(
            'loan',
            'schedule',
            'payments'
        ));
    }

    // ============================================================
    //                          EDIT
    // ============================================================
    public function edit($id)
    {
        $loan = Inv_acc_bank_loan::where('inv_abl_id', $id)
            ->where('inv_abl_company_id', Auth::user()->au_company_id)
            ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
            ->firstOrFail();

        $banks = $this->getAvailableBanks();

        return view('inventory.accounts.bank.loan-edit', compact('loan', 'banks'));
    }

    // ============================================================
    //                          UPDATE
    // ============================================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'bank_id'           => 'required|integer',
            'loan_name'         => 'required|string|max:255',
            'loan_type'         => 'required|integer|in:1,2,3,4',
            'principal_amount'  => 'required|numeric|min:0',
            'interest_rate'     => 'required|numeric|min:0',
            'tenure_months'     => 'required|integer|min:1',
            'disbursement_date' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $loan = Inv_acc_bank_loan::where('inv_abl_id', $id)
                ->where('inv_abl_company_id', Auth::user()->au_company_id)
                ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
                ->firstOrFail();

            $disbursementDate = $this->parseDate($request->disbursement_date);
            if (!$disbursementDate) {
                return redirect()->back()->withInput()
                    ->with('errmsg', 'Invalid disbursement date format.');
            }

            // Money already transferred can never be more than the loan taken
            $transferred = $this->getTransferredAmount($loan);
            if ($request->principal_amount < $transferred) {
                DB::rollBack();
                return redirect()->back()->withInput()
                    ->with('errmsg', 'Principal amount cannot be less than the ' . number_format($transferred, 2) . ' already transferred from this loan.');
            }

            $loanType        = (int) $request->loan_type;
            $monthsPerPeriod = $this->loanTypeMap[$loanType];
            $firstEmiDate    = $disbursementDate->copy()->addMonths($monthsPerPeriod);
            $maturityDate    = $disbursementDate->copy()->addMonths((int) $request->tenure_months);

            $emi = Inv_acc_bank_loan::calculateEMI(
                (float) $request->principal_amount,
                (float) $request->interest_rate,
                (int)   $request->tenure_months,
                $loanType
            );

            $principalDifference = $request->principal_amount - $loan->inv_abl_principal_amount;
            $newOutstanding      = max($loan->inv_abl_outstanding_balance + $principalDifference, 0);

            $loan->inv_abl_bank_id             = $request->bank_id;
            $loan->inv_abl_loan_name           = $request->loan_name;
            $loan->inv_abl_loan_type           = $loanType;
            $loan->inv_abl_loan_account_no     = $request->loan_account_no;
            $loan->inv_abl_principal_amount    = $request->principal_amount;
            $loan->inv_abl_interest_rate       = $request->interest_rate;
            $loan->inv_abl_interest_type       = $request->interest_type ?? 1;
            $loan->inv_abl_tenure_months       = $request->tenure_months;
            $loan->inv_abl_emi_amount          = $emi;
            $loan->inv_abl_disbursement_date   = $disbursementDate->format('Y-m-d');
            $loan->inv_abl_first_emi_date      = $firstEmiDate->format('Y-m-d');
            $loan->inv_abl_maturity_date       = $maturityDate->format('Y-m-d');
            $loan->inv_abl_outstanding_balance = $newOutstanding;
            $loan->inv_abl_remarks             = $request->remarks;
            $loan->inv_abl_updated_by          = Auth::user()->au_id;
            $loan->inv_abl_updated_at          = Carbon::now();
            $loan->save();

            DB::commit();

            Session::flash('msg', 'Bank Loan Updated Successfully.');
            return redirect()->route('accounts.bank-loan.list');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('errmsg', 'Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
        }
    }

    // ============================================================
    //                          DESTROY
    // ============================================================
    public function destroy($id)
    {
        try {
            $loan = Inv_acc_bank_loan::where('inv_abl_id', $id)
                ->where('inv_abl_company_id', Auth::user()->au_company_id)
                ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
                ->firstOrFail();

            $loan->inv_abl_status     = 0;
            $loan->inv_abl_updated_by = Auth::user()->au_id;
            $loan->inv_abl_updated_at = Carbon::now();
            $loan->save();

            return redirect()->back()->with('msg', 'Bank Loan Deleted Successfully.');

        } catch (Exception $e) {
            return redirect()->back()->with('errmsg', 'Something went wrong! ' . $e->getMessage());
        }
    }

    // ============================================================
    //                          PAYMENT FORM
    // ============================================================
    public function showPaymentForm($id)
    {
        $loan = Inv_acc_bank_loan::where('inv_abl_id', $id)
            ->where('inv_abl_company_id', Auth::user()->au_company_id)
            ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abl_status', 1)
            ->firstOrFail();

        $monthsPerPeriod    = $this->loanTypeMap[$loan->inv_abl_loan_type] ?? 1;
        $ratePerPeriod      = ($loan->inv_abl_interest_rate / 100) / (12 / $monthsPerPeriod);
        $interestComponent  = round($loan->inv_abl_outstanding_balance * $ratePerPeriod, 2);
        $principalComponent = round($loan->inv_abl_emi_amount - $interestComponent, 2);

        if ($principalComponent > $loan->inv_abl_outstanding_balance) {
            $principalComponent = $loan->inv_abl_outstanding_balance;
        }

        return view('inventory.accounts.bank.loan-payment', compact(
            'loan',
            'interestComponent',
            'principalComponent'
        ));
    }

    // ============================================================
    //                          STORE PAYMENT
    // ============================================================
    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'payment_date' => 'required',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $loan = Inv_acc_bank_loan::where('inv_abl_id', $id)
                ->where('inv_abl_company_id', Auth::user()->au_company_id)
                ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
                ->where('inv_abl_status', 1)
                ->firstOrFail();

            $paymentDate = $this->parseDate($request->payment_date);
            if (!$paymentDate) {
                return redirect()->back()->withInput()
                    ->with('errmsg', 'Invalid payment date format.');
            }

            $totalAmount = (float) $request->total_amount;

            $monthsPerPeriod   = $this->loanTypeMap[$loan->inv_abl_loan_type] ?? 1;
            $ratePerPeriod     = ($loan->inv_abl_interest_rate / 100) / (12 / $monthsPerPeriod);
            $interestComponent = round($loan->inv_abl_outstanding_balance * $ratePerPeriod, 2);

            if ($totalAmount <= $interestComponent) {
                $principalComponent = 0;
                $interestComponent  = $totalAmount;
            } else {
                $principalComponent = round($totalAmount - $interestComponent, 2);
                if ($principalComponent > $loan->inv_abl_outstanding_balance) {
                    $principalComponent = $loan->inv_abl_outstanding_balance;
                    $interestComponent  = $totalAmount - $principalComponent;
                }
            }

            $statement = new Inv_acc_bank_statement();
            $statement->inv_abs_company_id       = Auth::user()->au_company_id;
            $statement->inv_abs_branch_id        = Auth::user()->au_branch_id;
            $statement->inv_abs_reference_id     = $loan->inv_abl_id;
            $statement->inv_abs_reference_type   = 8;
            $statement->inv_abs_bank_id          = $loan->inv_abl_bank_id;
            $statement->inv_abs_credit           = 0;
            $statement->inv_abs_debit            = $totalAmount;
            $statement->inv_abs_transaction_date = $paymentDate->format('Y-m-d');
            $statement->inv_abs_voucher_no       = 'LOANPAY-' . Carbon::now()->format('YmdHis');
            $statement->inv_abs_description      = 'Loan payment: ' . $loan->inv_abl_loan_name;
            $statement->inv_abs_status           = 1;
            $statement->inv_abs_submit_by        = Auth::user()->au_id;
            $statement->inv_abs_submit_at        = Carbon::now();
            $statement->save();

            $payment = new Inv_acc_bank_loan_payment();
            $payment->inv_ablp_loan_id           = $loan->inv_abl_id;
            $payment->inv_ablp_company_id        = Auth::user()->au_company_id;
            $payment->inv_ablp_branch_id         = Auth::user()->au_branch_id;
            $payment->inv_ablp_bank_statement_id = $statement->inv_abs_id;
            $payment->inv_ablp_payment_type      = $request->payment_type ?? 1;
            $payment->inv_ablp_principal_amount  = $principalComponent;
            $payment->inv_ablp_interest_amount   = $interestComponent;
            $payment->inv_ablp_total_amount      = $totalAmount;
            $payment->inv_ablp_payment_date      = $paymentDate->format('Y-m-d');
            $payment->inv_ablp_voucher_no        = $statement->inv_abs_voucher_no;
            $payment->inv_ablp_description       = $request->description;
            $payment->inv_ablp_status            = 1;
            $payment->inv_ablp_submit_by         = Auth::user()->au_id;
            $payment->inv_ablp_submit_at         = Carbon::now();
            $payment->save();

            $loan->updateAfterPayment($principalComponent, $interestComponent);

            DB::commit();

            Session::flash('msg', 'Loan Payment Recorded Successfully.');
            return redirect()->route('accounts.bank-loan.details', $loan->inv_abl_id);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('errmsg', 'Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
        }
    }

    // ============================================================
    //                    TRANSFER FORM (split loan amount)
    // ============================================================
    public function showTransferForm($id)
    {
        $loan = Inv_acc_bank_loan::where('inv_abl_id', $id)
            ->where('inv_abl_company_id', Auth::user()->au_company_id)
            ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abl_status', 1)
            ->firstOrFail();

        $transferred = $this->getTransferredAmount($loan);
        $available   = max($loan->inv_abl_principal_amount - $transferred, 0);
        $bankBalance = Inv_acc_bank_statement::getAvailableBalanceByBankId($loan->inv_abl_bank_id);

        $allAccounts = Inv_acc_bank_info::with('bank_info')
            ->where('inv_abi_company_id', Auth::user()->au_company_id)
            ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
            ->get()
            ->keyBy('inv_abi_id');

        $accountLabels = $allAccounts->map(function ($account) {
            return $this->accountLabel($account);
        });

        // Any other bank or cash account can receive the money
        $accounts = $allAccounts->filter(function ($account) use ($loan) {
            return $account->inv_abi_status == 1 && $account->inv_abi_id != $loan->inv_abl_bank_id;
        });

        // Previous transfers (the money-in side of each pair)
        $transfers = Inv_acc_bank_statement::where('inv_abs_company_id', Auth::user()->au_company_id)
            ->where('inv_abs_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abs_reference_type', 12)
            ->where('inv_abs_reference_id', $loan->inv_abl_id)
            ->where('inv_abs_credit', '>', 0)
            ->where('inv_abs_status', 1)
            ->orderBy('inv_abs_id', 'desc')
            ->get();

        return view('inventory.accounts.bank.loan-transfer', compact(
            'loan',
            'transferred',
            'available',
            'bankBalance',
            'accounts',
            'accountLabels',
            'transfers'
        ));
    }

    // ============================================================
    //                    STORE TRANSFER
    // ============================================================
    public function storeTransfer(Request $request, $id)
    {
        $request->validate([
            'transfer_date' => 'required',
            'to_bank_id'    => 'required|array|min:1',
            'to_bank_id.*'  => 'required|integer',
            'amount'        => 'required|array|size:' . count((array) $request->to_bank_id),
            'amount.*'      => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $loan = Inv_acc_bank_loan::where('inv_abl_id', $id)
                ->where('inv_abl_company_id', Auth::user()->au_company_id)
                ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
                ->where('inv_abl_status', 1)
                ->firstOrFail();

            $transferDate = $this->parseDate($request->transfer_date);
            if (!$transferDate) {
                DB::rollBack();
                return redirect()->back()->withInput()
                    ->with('errmsg', 'Invalid transfer date format.');
            }

            $fromBank = Inv_acc_bank_info::with('bank_info')
                ->where('inv_abi_id', $loan->inv_abl_bank_id)
                ->first();

            // Check every destination before saving anything
            $rows = [];
            foreach ($request->to_bank_id as $i => $toBankId) {
                $toBank = Inv_acc_bank_info::with('bank_info')
                    ->where('inv_abi_id', $toBankId)
                    ->where('inv_abi_company_id', Auth::user()->au_company_id)
                    ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
                    ->where('inv_abi_status', 1)
                    ->where('inv_abi_id', '!=', $loan->inv_abl_bank_id)
                    ->first();

                if (!$toBank) {
                    DB::rollBack();
                    return redirect()->back()->withInput()
                        ->with('errmsg', 'Row ' . ($i + 1) . ': select a bank or cash account other than the loan bank.');
                }

                $rows[] = [$toBank, round((float) $request->amount[$i], 2)];
            }

            $total     = round(array_sum(array_column($rows, 1)), 2);
            $available = round($loan->inv_abl_principal_amount - $this->getTransferredAmount($loan), 2);

            if ($total > $available) {
                DB::rollBack();
                return redirect()->back()->withInput()
                    ->with('errmsg', 'Total transfer ' . number_format($total, 2) . ' is more than the loan amount left to transfer (' . number_format($available, 2) . ').');
            }

            $voucherNo = 'LOANTRF-' . Carbon::now()->format('YmdHis');
            $note      = $request->note ? ' - ' . $request->note : '';

            foreach ($rows as [$toBank, $amount]) {
                // Money out of the loan bank
                $out = new Inv_acc_bank_statement();
                $out->inv_abs_company_id       = Auth::user()->au_company_id;
                $out->inv_abs_branch_id        = Auth::user()->au_branch_id;
                $out->inv_abs_reference_id     = $loan->inv_abl_id;
                $out->inv_abs_reference_type   = 12; //12= Loan Transfer
                $out->inv_abs_bank_id          = $loan->inv_abl_bank_id;
                $out->inv_abs_credit           = 0;
                $out->inv_abs_debit            = $amount;
                $out->inv_abs_transaction_date = $transferDate->format('Y-m-d');
                $out->inv_abs_voucher_no       = $voucherNo;
                $out->inv_abs_description      = mb_substr('Loan transfer: ' . $loan->inv_abl_loan_name . ' to ' . $this->accountLabel($toBank) . $note, 0, 255);
                $out->inv_abs_status           = 1;
                $out->inv_abs_submit_by        = Auth::user()->au_id;
                $out->inv_abs_submit_at        = Carbon::now();
                $out->save();

                // Money into the selected bank / cash account
                $in = new Inv_acc_bank_statement();
                $in->inv_abs_company_id            = Auth::user()->au_company_id;
                $in->inv_abs_branch_id             = Auth::user()->au_branch_id;
                $in->inv_abs_reference_id          = $loan->inv_abl_id;
                $in->inv_abs_reference_type        = 12; //12= Loan Transfer
                $in->inv_abs_bank_id               = $toBank->inv_abi_id;
                $in->inv_abs_credit                = $amount;
                $in->inv_abs_debit                 = 0;
                $in->inv_abs_transaction_date      = $transferDate->format('Y-m-d');
                $in->inv_abs_voucher_no            = $voucherNo;
                $in->inv_abs_description           = mb_substr('Loan transfer: ' . $loan->inv_abl_loan_name . ' from ' . ($fromBank ? $this->accountLabel($fromBank) : 'loan bank') . $note, 0, 255);
                $in->inv_abs_contra_transaction_id = $out->inv_abs_id;
                $in->inv_abs_status                = 1;
                $in->inv_abs_submit_by             = Auth::user()->au_id;
                $in->inv_abs_submit_at             = Carbon::now();
                $in->save();

                $out->inv_abs_contra_transaction_id = $in->inv_abs_id;
                $out->save();
            }

            DB::commit();

            Session::flash('msg', 'Loan amount ' . number_format($total, 2) . ' transferred successfully.');
            return redirect()->route('accounts.bank-loan.transfer-form', $loan->inv_abl_id);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('errmsg', 'Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
        }
    }

    // ============================================================
    //                          AJAX: Calculate EMI
    // ============================================================
    public function ajaxCalculateEMI(Request $request)
    {
        $principal = (float) ($request->principal ?? 0);
        $rate      = (float) ($request->interest_rate ?? 0);
        $tenure    = (int)   ($request->tenure_months ?? 0);
        $loanType  = (int)   ($request->loan_type ?? 1);

        if ($tenure <= 0 || !isset($this->loanTypeMap[$loanType])) {
            return response()->json([
                'emi'                => '0.00',
                'total_installments' => 0,
                'total'              => '0.00',
                'total_interest'     => '0.00',
            ]);
        }

        $emi               = Inv_acc_bank_loan::calculateEMI($principal, $rate, $tenure, $loanType);
        $monthsPerPeriod   = $this->loanTypeMap[$loanType];
        $totalInstallments = (int) ceil($tenure / $monthsPerPeriod);
        $total             = $emi * $totalInstallments;
        $totalInterest     = $total - $principal;

        return response()->json([
            'emi'                => number_format($emi, 2, '.', ''),
            'total_installments' => $totalInstallments,
            'total'              => number_format($total, 2, '.', ''),
            'total_interest'     => number_format($totalInterest, 2, '.', ''),
        ]);
    }

    // ============================================================
    //                          REPORT
    // ============================================================
    public function report()
    {
        $companyId = Auth::user()->au_company_id;
        $branchId  = Auth::user()->au_branch_id;

        $loans = Inv_acc_bank_loan::where('inv_abl_company_id', $companyId)
            ->where('inv_abl_branch_id', $branchId)
            ->orderBy('inv_abl_id', 'desc')
            ->get();

        $totalPrincipal   = $loans->sum('inv_abl_principal_amount');
        $totalOutstanding = $loans->sum('inv_abl_outstanding_balance');
        $totalPaid        = $loans->sum('inv_abl_total_paid');
        $totalInterest    = $loans->sum('inv_abl_total_interest_paid');

        return view('inventory.accounts.bank.loan-report', compact(
            'loans',
            'totalPrincipal',
            'totalOutstanding',
            'totalPaid',
            'totalInterest'
        ));
    }

    // ============================================================
    //                        PRIVATE HELPERS
    // ============================================================

    /**
     * Get available banks for current company & branch.
     */
    private function getAvailableBanks()
    {
        return Inv_acc_bank_info::where('inv_abi_company_id', Auth::user()->au_company_id)
            ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abi_status', 1)
            ->where(function ($q) {
                $q->where('inv_abi_account_type', 1)
                  ->orWhereNull('inv_abi_account_type')
                  ->orWhere('inv_abi_account_type', 0);
            })
            ->get();
    }

    /**
     * Loan amount already moved out of the loan bank by transfers.
     */
    private function getTransferredAmount($loan)
    {
        return Inv_acc_bank_statement::where('inv_abs_company_id', $loan->inv_abl_company_id)
            ->where('inv_abs_branch_id', $loan->inv_abl_branch_id)
            ->where('inv_abs_reference_type', 12)
            ->where('inv_abs_reference_id', $loan->inv_abl_id)
            ->where('inv_abs_status', 1)
            ->sum('inv_abs_debit');
    }

    /**
     * "Bank name (account no)" for a bank / cash account.
     */
    private function accountLabel($account)
    {
        return ($account->bank_info->bank_name ?? $account->inv_abi_account_name) . ' (' . $account->inv_abi_account_no . ')';
    }

    /**
     * Robust date parser.
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value);
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value);
            }
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                return Carbon::createFromFormat('d-m-Y', $value);
            }

            return Carbon::parse($value);

        } catch (Exception $e) {
            return null;
        }
    }
}
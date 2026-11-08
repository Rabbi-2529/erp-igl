<?php
namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use Illuminate\cusport\Facades\Input;
use App\Http\Controllers\Controller;
use App\Models\Inv_customer;
use App\Models\Inv_product_inventory;
use Carbon\Carbon;
use Session;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_acc_bank_statement;
use PDF;
use Dompdf\Dompdf;
use Exception; // Added proper Exception import

class CustomerAccountsController extends Controller
{
    public function depositWithdrawForm()
    {
        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        $inv_customers = Inv_customer::where('inv_cus_com_id', $companyId)
                        ->where('inv_cus_branch_id', $branchId)
                        ->where('inv_cus_status', 1)
                        ->get();

        return view('inventory.customer.accounts.depositwithdraw', compact('inv_customers'));
    }

    public function depositWithdrawStore(Request $request)
    {
        try {
            $request->validate([
                'trans_date' => 'required|date_format:Y-m-d',
                'trans_type' => 'required',
                'customer_id' => 'required',
                'amount' => 'required|numeric|min:0',
            ]);

            // Parse and validate date
            $transDate = Carbon::createFromFormat('Y-m-d', $request->trans_date)->format('Y-m-d');

            $inv_Pro_Invt = new Inv_product_inventory();
            $inv_Pro_Invt->inv_pro_inv_com_id = Auth::user()->au_company_id;
            $inv_Pro_Invt->inv_pro_inv_branch_id = Auth::user()->au_branch_id;
            $inv_Pro_Invt->inv_pro_inv_party_id = $request->customer_id;
            $inv_Pro_Invt->inv_pro_inv_invoice_no = Carbon::now()->format('YmdHis');
            $inv_Pro_Invt->inv_pro_inv_tran_desc = $request->reference;
            $inv_Pro_Invt->inv_pro_inv_issue_date = $transDate; // Use parsed date
            $inv_Pro_Invt->inv_pro_inv_deal_type = 2; //2 for customer dealing
            $inv_Pro_Invt->inv_pro_inv_tran_type = 3; // 3 for opening,deposit and withdraw balance
            $inv_Pro_Invt->inv_pro_inv_submit_by = Auth::user()->au_id;
            $inv_Pro_Invt->inv_pro_inv_submit_at = Carbon::now();

            if($request->trans_type == 2) {
                //credit
                $inv_Pro_Invt->inv_pro_inv_debit = 0;
                $inv_Pro_Invt->inv_pro_inv_credit = $request->amount;
            } else if($request->trans_type == 3) {
                //Debit
                $inv_Pro_Invt->inv_pro_inv_debit = $request->amount;
                $inv_Pro_Invt->inv_pro_inv_credit = 0;
            }

            if($inv_Pro_Invt->save()) {
                Session::flash('msg','Transaction Successful.');
                return redirect()->back();
            } else {
                Session::flash('errmsg','Transaction Failed. Please Try Again.');
                return redirect()->back();
            }
        } catch(Exception $err) {
            Session::flash('errmsg','Something Went Wrong: ' . $err->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function customerPaymentForm()
    {
        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        $banks = Inv_acc_bank_info::where('inv_abi_company_id', $companyId)
                    ->where('inv_abi_branch_id', $branchId)
                    ->where('inv_abi_status', 1)
                    ->get();

        $inv_customers = Inv_customer::where('inv_cus_com_id', $companyId)
                        ->where('inv_cus_branch_id', $branchId)
                        ->where('inv_cus_status', 1)
                        ->get();

        return view('inventory.customer.accounts.payment', compact('banks', 'inv_customers'));
    }

    public function customerPaymentStore(Request $request)
    {
        try {
            $request->validate([
                'trans_date' => 'required|date_format:Y-m-d',
                'customer_id' => 'required',
                'amount' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'service_charge' => 'nullable|numeric|min:0',
            ]);

            // Parse and validate date
            $transDate = Carbon::createFromFormat('Y-m-d', $request->trans_date)->format('Y-m-d');

            $inv_Pro_Invt = new Inv_product_inventory();
            $invoiceNo = Carbon::now()->format('YmdHis');
            $inv_Pro_Invt->inv_pro_inv_com_id = Auth::user()->au_company_id;
            $inv_Pro_Invt->inv_pro_inv_branch_id = Auth::user()->au_branch_id;
            $inv_Pro_Invt->inv_pro_inv_party_id = $request->customer_id;
            $inv_Pro_Invt->inv_pro_inv_debit = 0;
            $inv_Pro_Invt->inv_pro_inv_credit = $request->amount;
            $inv_Pro_Invt->inv_pro_inv_deal_type = 2; // 2 for customer
            $inv_Pro_Invt->inv_pro_inv_tran_type = 4; // 4 for customer payment collection
            $inv_Pro_Invt->inv_pro_inv_tran_desc = $request->reference;
            $inv_Pro_Invt->inv_pro_inv_issue_date = $transDate; // Use parsed date
            $inv_Pro_Invt->inv_pro_inv_invoice_no = $invoiceNo;
            $inv_Pro_Invt->inv_pro_inv_submit_by = Auth::user()->au_id;
            $inv_Pro_Invt->inv_pro_inv_submit_at = Carbon::now();
            $inv_Pro_Invt->save();

            $inv_Acc_Bank_Statement = new Inv_acc_bank_statement();
            $inv_Acc_Bank_Statement->inv_abs_company_id = Auth::user()->au_company_id;
            $inv_Acc_Bank_Statement->inv_abs_branch_id = Auth::user()->au_branch_id;
            $inv_Acc_Bank_Statement->inv_abs_reference_id = $request->customer_id;
            $inv_Acc_Bank_Statement->inv_abs_bank_id = $request->bank_id;
            $inv_Acc_Bank_Statement->inv_abs_inventory_id = $inv_Pro_Invt->inv_pro_inv_id;
            $inv_Acc_Bank_Statement->inv_abs_debit = 0;
            $inv_Acc_Bank_Statement->inv_abs_credit = $request->amount;
            $inv_Acc_Bank_Statement->inv_abs_transaction_date = $transDate; // Use parsed date
            $inv_Acc_Bank_Statement->inv_abs_description = $request->reference;
            $inv_Acc_Bank_Statement->inv_abs_voucher_no = $invoiceNo;
            $inv_Acc_Bank_Statement->inv_abs_status = 1;
            $inv_Acc_Bank_Statement->inv_abs_reference_type = 1; //1= Customer Payment Collection
            $inv_Acc_Bank_Statement->inv_abs_submit_at = Carbon::now();
            $inv_Acc_Bank_Statement->inv_abs_submit_by = Auth::user()->au_id;
            $inv_Acc_Bank_Statement->save();

            // Optional service charge is cut from the same bank as its own transaction
            if($request->service_charge > 0) {
                $inv_Acc_Bank_Charge = new Inv_acc_bank_statement();
                $inv_Acc_Bank_Charge->inv_abs_company_id = Auth::user()->au_company_id;
                $inv_Acc_Bank_Charge->inv_abs_branch_id = Auth::user()->au_branch_id;
                $inv_Acc_Bank_Charge->inv_abs_reference_id = $request->customer_id;
                $inv_Acc_Bank_Charge->inv_abs_bank_id = $request->bank_id;
                $inv_Acc_Bank_Charge->inv_abs_inventory_id = $inv_Pro_Invt->inv_pro_inv_id;
                $inv_Acc_Bank_Charge->inv_abs_debit = $request->service_charge;
                $inv_Acc_Bank_Charge->inv_abs_credit = 0;
                $inv_Acc_Bank_Charge->inv_abs_service_charge = $request->service_charge;
                $inv_Acc_Bank_Charge->inv_abs_transaction_date = $transDate;
                $inv_Acc_Bank_Charge->inv_abs_description = 'Service charge'.($request->reference ? ': '.$request->reference : '');
                $inv_Acc_Bank_Charge->inv_abs_voucher_no = $invoiceNo;
                $inv_Acc_Bank_Charge->inv_abs_status = 1;
                $inv_Acc_Bank_Charge->inv_abs_reference_type = 11; //11= Bank Service Charge
                $inv_Acc_Bank_Charge->inv_abs_submit_at = Carbon::now();
                $inv_Acc_Bank_Charge->inv_abs_submit_by = Auth::user()->au_id;
                $inv_Acc_Bank_Charge->save();
            }

            if($request->discount > 0) {
                $inv_Pro_Invt1 = new Inv_product_inventory();
                $inv_Pro_Invt1->inv_pro_inv_com_id = Auth::user()->au_company_id;
                $inv_Pro_Invt1->inv_pro_inv_branch_id = Auth::user()->au_branch_id;
                $inv_Pro_Invt1->inv_pro_inv_party_id = $request->customer_id;
                $inv_Pro_Invt1->inv_pro_inv_debit = 0;
                $inv_Pro_Invt1->inv_pro_inv_credit = $request->discount;
                $inv_Pro_Invt1->inv_pro_inv_deal_type = 2; // 2 for customer
                $inv_Pro_Invt1->inv_pro_inv_tran_type = 6; // 6 Customer or Supplier Discount
                $inv_Pro_Invt1->inv_pro_inv_tran_desc = $request->reference.' Customer Discount - '.$request->discount;
                $inv_Pro_Invt1->inv_pro_inv_issue_date = $transDate; // Use parsed date
                $inv_Pro_Invt1->inv_pro_inv_invoice_no = $invoiceNo;
                $inv_Pro_Invt1->inv_pro_inv_submit_by = Auth::user()->au_id;
                $inv_Pro_Invt1->inv_pro_inv_submit_at = Carbon::now();
                $inv_Pro_Invt1->save();
            }

            Session::flash('msg','Payment Successful.');
            return redirect()->back();

        } catch(Exception $err) {
            Session::flash('errmsg','Something Went Wrong: ' . $err->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function customerPaymentRedfundForm()
    {
        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        $banks = Inv_acc_bank_info::where('inv_abi_company_id', $companyId)
                    ->where('inv_abi_branch_id', $branchId)
                    ->where('inv_abi_status', 1)
                    ->get();

        $inv_customers = Inv_customer::where('inv_cus_com_id', $companyId)
                        ->where('inv_cus_branch_id', $branchId)
                        ->where('inv_cus_status', 1)
                        ->get();

        return view('inventory.customer.accounts.paymentrefund', compact('banks', 'inv_customers'));
    }

    public function customerPaymentRedfundStore(Request $request)
    {
        try {
            $request->validate([
                'trans_date' => 'required|date_format:Y-m-d',
                'amount' => 'required|numeric|min:0',
                'customer_id' => 'required',
            ]);

            // Parse and validate date
            $transDate = Carbon::createFromFormat('Y-m-d', $request->trans_date)->format('Y-m-d');

            $balance = Inv_acc_bank_statement::getAvailableBalanceByBankId($request->bank_id);
            
            if($balance >= $request->amount) {
                $inv_Pro_Invt = new Inv_product_inventory();
                $invoiceNo = Carbon::now()->format('YmdHis');
                $inv_Pro_Invt->inv_pro_inv_com_id = Auth::user()->au_company_id;
                $inv_Pro_Invt->inv_pro_inv_branch_id = Auth::user()->au_branch_id;
                $inv_Pro_Invt->inv_pro_inv_party_id = $request->customer_id;
                $inv_Pro_Invt->inv_pro_inv_debit = $request->amount;
                $inv_Pro_Invt->inv_pro_inv_credit = 0;
                $inv_Pro_Invt->inv_pro_inv_tran_type = 5; //5=customer payment refunds
                $inv_Pro_Invt->inv_pro_inv_invoice_no = $invoiceNo;
                $inv_Pro_Invt->inv_pro_inv_tran_desc = $request->reference;
                $inv_Pro_Invt->inv_pro_inv_issue_date = $transDate; // Use parsed date
                $inv_Pro_Invt->inv_pro_inv_deal_type = 2;// 2 for customer
                $inv_Pro_Invt->inv_pro_inv_submit_by = Auth::user()->au_id;
                $inv_Pro_Invt->inv_pro_inv_submit_at = Carbon::now();
                $inv_Pro_Invt->save();

                $inv_Acc_Bank_Statement = new Inv_acc_bank_statement();
                $inv_Acc_Bank_Statement->inv_abs_company_id = Auth::user()->au_company_id;
                $inv_Acc_Bank_Statement->inv_abs_branch_id = Auth::user()->au_branch_id;
                $inv_Acc_Bank_Statement->inv_abs_reference_id = $request->customer_id;
                $inv_Acc_Bank_Statement->inv_abs_bank_id = $request->bank_id;
                $inv_Acc_Bank_Statement->inv_abs_inventory_id = $inv_Pro_Invt->inv_pro_inv_id;
                $inv_Acc_Bank_Statement->inv_abs_debit = $request->amount;
                $inv_Acc_Bank_Statement->inv_abs_credit = 0;
                $inv_Acc_Bank_Statement->inv_abs_transaction_date = $transDate; // Use parsed date
                $inv_Acc_Bank_Statement->inv_abs_description = $request->reference;
                $inv_Acc_Bank_Statement->inv_abs_voucher_no = $invoiceNo;
                $inv_Acc_Bank_Statement->inv_abs_status = 1;
                $inv_Acc_Bank_Statement->inv_abs_reference_type = 1; //1= Customer Payment Collection
                $inv_Acc_Bank_Statement->inv_abs_submit_at = Carbon::now();
                $inv_Acc_Bank_Statement->inv_abs_submit_by = Auth::user()->au_id;
                $inv_Acc_Bank_Statement->save();

                Session::flash('msg','Payment Successful.');
                return redirect()->back();
            } else {
                Session::flash('errmsg','Insufficient Balance Found.');
                return redirect()->back()->withInput();
            }
        } catch(Exception $err) {
            Session::flash('errmsg','Something Went Wrong: ' . $err->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function showAccountStatement()
    {
        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        $inv_cus_invts = Inv_customer::where('inv_cus_com_id', $companyId)
                        ->where('inv_cus_branch_id', $branchId)
                        ->where('inv_cus_status', 1)
                        ->get();

        return view('inventory.customer.accounts.accountstatement', compact('inv_cus_invts'));
    }

    public function showAccountStatementDetails(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        // Validate dates if provided
        if ($request->has('searchbtn')) {
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d',
                'customer_id' => 'required',
            ]);

            $inv_custs = Inv_product_inventory::where('inv_pro_inv_party_id', $request->customer_id)
                ->where('inv_pro_inv_com_id', $companyId)
                ->where('inv_pro_inv_branch_id', $branchId)
                ->where('inv_pro_inv_issue_date', '>=', $request->start_date)
                ->where('inv_pro_inv_issue_date', '<=', $request->end_date)
                ->where('inv_pro_inv_deal_type', 2)
                ->where('inv_pro_inv_status', 1)
                ->groupBy('inv_pro_inv_invoice_no')
                ->get();
        } else {
            $inv_custs = Inv_product_inventory::where('inv_pro_inv_party_id', $request->customer_id)
                ->where('inv_pro_inv_com_id', $companyId)
                ->where('inv_pro_inv_branch_id', $branchId)
                ->where('inv_pro_inv_deal_type', 2)
                ->where('inv_pro_inv_status', 1)
                ->groupBy('inv_pro_inv_invoice_no')
                ->get();
        }

        // ===== ADDED THIS ONE LINE TO SORT BY DATE PROPERLY =====
        $inv_custs = $inv_custs->sortBy('inv_pro_inv_issue_date');

        return view('inventory.customer.accounts.accountstatementdetails', compact('inv_custs'));
    }
    public function downloadAccountStatementDetails(Request $request)
    {
        if($request->start_date != null && $request->end_date != null) {
            $inv_custs = Inv_product_inventory::where('inv_pro_inv_party_id', $request->customer_id)
                            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
                            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id)
                            ->where('inv_pro_inv_issue_date', '>=', $request->start_date)
                            ->where('inv_pro_inv_issue_date', '<=', $request->end_date)
                            ->where('inv_pro_inv_status', 1)
                            ->where('inv_pro_inv_deal_type', 2)
                            ->groupBy('inv_pro_inv_invoice_no')
                            ->get();
        } else {
            $inv_custs = Inv_product_inventory::where('inv_pro_inv_party_id', $request->customer_id)
                            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
                            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id)
                            ->where('inv_pro_inv_status', 1)
                            ->where('inv_pro_inv_deal_type', 2)
                            ->groupBy('inv_pro_inv_invoice_no')
                            ->get();
        }

        $pdf = PDF::loadView('inventory.customer.accounts.accountstatementdetailsdownload', compact('inv_custs'));
        return $pdf->download('account_statements.pdf');
    }





/**
 * Download Account Statement as PDF
 */
public function downloadAccountStatementPdf()
{
    try {
        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        $inv_cus_invts = Inv_customer::where('inv_cus_com_id', $companyId)
                        ->where('inv_cus_branch_id', $branchId)
                        ->where('inv_cus_status', 1)
                        ->get();

        // Calculate totals
        $total_debit = 0;
        $total_credit = 0;
        $total_balance = 0;
        
        foreach ($inv_cus_invts as $customer) {
            $debit = Inv_product_inventory::getCusDebitByID($customer->inv_cus_id);
            $credit = Inv_product_inventory::getCusCreditByID($customer->inv_cus_id);
            $balance = $credit - $debit;
            
            $customer->debit = $debit;
            $customer->credit = $credit;
            $customer->balance = $balance;
            
            $total_debit += $debit;
            $total_credit += $credit;
            $total_balance += $balance;
        }

        $pdf = PDF::loadView('inventory.customer.accounts.accountstatementpdf', compact(
            'inv_cus_invts', 
            'total_debit', 
            'total_credit', 
            'total_balance'
        ));
        
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('account_statement_' . date('Y-m-d') . '.pdf');

    } catch (Exception $e) {
        Session::flash('errmsg', 'Error: ' . $e->getMessage());
        return redirect()->back();
    }
}


    public function loadAjaxInvoiceDetails(Request $request)
    {
        $isProductTrans = Inv_product_inventory::where('inv_pro_inv_invoice_no', $request->invoice_id)
                                            ->where('inv_pro_inv_tran_type', 1)
                                            ->orWhere('inv_pro_inv_tran_type', 2)
                                            ->count();

        if($isProductTrans > 0) {
            $isProductTrans = 1;
        }
        
        $invoiceInfos = Inv_product_inventory::where('inv_pro_inv_invoice_no', $request->invoice_id)
                                            ->where('inv_pro_inv_deal_type', 2)
                                            ->where('inv_pro_inv_status', 1)
                                            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
                                            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id)
                                            ->get();
        
        return view('pages.ajax.invoice_details', compact('invoiceInfos', 'isProductTrans'));
    }

    // ==================== 18-11-19 =================
    public function customerTodayPaymentReport()
    {
        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        $todayPayments = Inv_acc_bank_statement::where('inv_abs_company_id', $companyId)
            ->where('inv_abs_branch_id', $branchId)
            ->where('inv_abs_transaction_date', Carbon::now()->format('Y-m-d'))
            ->where('inv_abs_reference_type', 1)
            ->where('inv_abs_status', 1)
            ->orderBy('inv_abs_id', 'asc')
            ->get();

        return view('inventory.customer.accounts.today_payments', compact('todayPayments'));
    }
}
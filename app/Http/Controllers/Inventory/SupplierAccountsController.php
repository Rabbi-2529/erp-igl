<?php
namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use App\Models\Inv_supplier;
use App\Models\Inv_product_inventory;
use Carbon\Carbon;
use Session;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_acc_bank_statement;
use PDF;

class SupplierAccountsController extends Controller
{
	public function depositWithdrawForm()
	{
		$inv_suppliers=Inv_supplier::where('inv_sup_com_id',Auth::user()->au_company_id)
						->where('inv_sup_status',1)->get();
		return view('inventory.supplier.accounts.depositwithdraw',compact('inv_suppliers'));

	}
	
	public function depositWithdrawStore(Request $request)
	{
		try{
			if (!empty($request->trans_date)) {
				$request->merge([
					'trans_date' => Carbon::createFromFormat('d/m/Y', $request->trans_date)->format('Y-m-d')
				]);
			}
			$request->validate([
				'trans_date' => 'required|date',
				'amount'=>'required|numeric|min:0',
				'supplier_id'=>'required',
			]);

			$inv_Pro_Invt=new Inv_product_inventory();
			$inv_Pro_Invt->inv_pro_inv_com_id=Auth::user()->au_company_id;
			$inv_Pro_Invt->inv_pro_inv_branch_id=Auth::user()->au_branch_id; // ADDED BRANCH ID
			$inv_Pro_Invt->inv_pro_inv_party_id=$request->supplier_id;
			$inv_Pro_Invt->inv_pro_inv_tran_type=3; // for opening,deposit and withdraw balance
			$inv_Pro_Invt->inv_pro_inv_invoice_no=Carbon::now()->format('YmdHis');
			$inv_Pro_Invt->inv_pro_inv_deal_type=1; // for supplier
			$inv_Pro_Invt->inv_pro_inv_tran_desc=$request->reference;
			$inv_Pro_Invt->inv_pro_inv_issue_date=$request->trans_date;
			$inv_Pro_Invt->inv_pro_inv_status=1;
			$inv_Pro_Invt->inv_pro_inv_submit_by=Auth::user()->au_id;
			$inv_Pro_Invt->inv_pro_inv_submit_at=Carbon::now();

			if($request->trans_type==2)
			{
				//credit
				$inv_Pro_Invt->inv_pro_inv_debit=0;
				$inv_Pro_Invt->inv_pro_inv_credit=$request->amount;
			}
			else if($request->trans_type==3)
			{
				//Debit
				$inv_Pro_Invt->inv_pro_inv_debit=$request->amount;
				$inv_Pro_Invt->inv_pro_inv_credit=0;
			}

			if($inv_Pro_Invt->save())
			{
				Session::flash('msg','Transaction Successfull.');
				return redirect()->back();
			}
			else
			{
				Session::flash('errmsg','Transaction Failed.Please Try Again.');
			}
		}
		catch(Exception $err)
		{
			Session::flash('errmsg','Something Went Wrong.');
			return redirect()->back();
		}
	}
	
	public function supplierPaymentForm()
	{
		$banks=Inv_acc_bank_info::where('inv_abi_company_id',Auth::user()->au_company_id)
				->where('inv_abi_status',1)->get();
		$inv_suppliers=Inv_supplier::where('inv_sup_com_id',Auth::user()->au_company_id)
						->where('inv_sup_status',1)->get();
		return view('inventory.supplier.accounts.payment',compact('banks','inv_suppliers'));
	}
	
	public function supplierPaymentStore(Request $request)
	{
		try{
			$request->validate([
				'trans_date' => 'required|date',
				'amount'=>'required|numeric|min:0',
				'supplier_id'=>'required',
				'discount'=>'nullable|numeric|min:0',
			]);

			$balance=Inv_acc_bank_statement::getAvailableBalanceByBankId($request->bank_id);
			
			if($balance >= $request->amount){
				$inv_Pro_Invt=new Inv_product_inventory();
				$invoiceNo=Carbon::now()->format('YmdHis');
				$inv_Pro_Invt->inv_pro_inv_com_id=Auth::user()->au_company_id;
				$inv_Pro_Invt->inv_pro_inv_branch_id=Auth::user()->au_branch_id; // ADDED BRANCH ID
				$inv_Pro_Invt->inv_pro_inv_party_id=$request->supplier_id;
				$inv_Pro_Invt->inv_pro_inv_debit=0;
				$inv_Pro_Invt->inv_pro_inv_credit=$request->amount;
				$inv_Pro_Invt->inv_pro_inv_tran_type=4; //for suppliers payments
				$inv_Pro_Invt->inv_pro_inv_deal_type=1; // for supplier
				$inv_Pro_Invt->inv_pro_inv_tran_desc=$request->reference;
				$inv_Pro_Invt->inv_pro_inv_issue_date=$request->trans_date;
				$inv_Pro_Invt->inv_pro_inv_invoice_no=$invoiceNo;
				$inv_Pro_Invt->inv_pro_inv_status=1;
				$inv_Pro_Invt->inv_pro_inv_submit_by=Auth::user()->au_id;
				$inv_Pro_Invt->inv_pro_inv_submit_at=Carbon::now();
				$inv_Pro_Invt->save();

				$inv_Acc_Bank_Statement = new Inv_acc_bank_statement();
				$inv_Acc_Bank_Statement->inv_abs_company_id = Auth::user()->au_company_id;
				$inv_Acc_Bank_Statement->inv_abs_branch_id = Auth::user()->au_branch_id; // ADDED BRANCH ID
				$inv_Acc_Bank_Statement->inv_abs_reference_id = $request->supplier_id;
				$inv_Acc_Bank_Statement->inv_abs_bank_id = $request->bank_id;
				$inv_Acc_Bank_Statement->inv_abs_inventory_id=$inv_Pro_Invt->inv_pro_inv_id;
				$inv_Acc_Bank_Statement->inv_abs_debit = $request->amount;
				$inv_Acc_Bank_Statement->inv_abs_credit = 0;
				$inv_Acc_Bank_Statement->inv_abs_transaction_date = $request->trans_date;
				$inv_Acc_Bank_Statement->inv_abs_description= $request->reference;
				$inv_Acc_Bank_Statement->inv_abs_voucher_no=Carbon::now()->format('YmdHis');
				$inv_Acc_Bank_Statement->inv_abs_status = 1;
				$inv_Acc_Bank_Statement->inv_abs_reference_type = 2; //2= Supplier Payment
				$inv_Acc_Bank_Statement->inv_abs_submit_at = Carbon::now();
				$inv_Acc_Bank_Statement->inv_abs_submit_by = Auth::user()->au_id;
				$inv_Acc_Bank_Statement->save();

				if($request->discount>0)
				{
					$inv_Pro_Invt1=new Inv_product_inventory();
					$inv_Pro_Invt1->inv_pro_inv_com_id=Auth::user()->au_company_id;
					$inv_Pro_Invt1->inv_pro_inv_branch_id=Auth::user()->au_branch_id; // ADDED BRANCH ID
					$inv_Pro_Invt1->inv_pro_inv_party_id=$request->supplier_id;
					$inv_Pro_Invt1->inv_pro_inv_debit=0;
					$inv_Pro_Invt1->inv_pro_inv_credit=$request->discount;
					$inv_Pro_Invt1->inv_pro_inv_deal_type=1; // 1 for supplier
					$inv_Pro_Invt1->inv_pro_inv_tran_type=6; // 6 Customer or Supplier Discount
					$inv_Pro_Invt1->inv_pro_inv_tran_desc=$request->reference.' Supplier Discount - '.$request->discount;
					$inv_Pro_Invt1->inv_pro_inv_issue_date=$request->trans_date;
					$inv_Pro_Invt1->inv_pro_inv_invoice_no=$invoiceNo;
					$inv_Pro_Invt1->inv_pro_inv_submit_by=Auth::user()->au_id;
					$inv_Pro_Invt1->inv_pro_inv_submit_at=Carbon::now();
					$inv_Pro_Invt1->save();
				}

				Session::flash('msg','Payment Successfull.');
				return redirect()->back();
			}
			else
			{
				Session::flash('errmsg','Insufficent Balance Found.');
				return redirect()->back()->withInput();
			}
		}
		catch(Exception $err)
		{
			Session::flash('errmsg','Something Went Wrong.');
			return redirect()->back();
		}
	}
	
	public function supplierPaymentCollectionForm()
	{
		$banks=Inv_acc_bank_info::where('inv_abi_company_id',Auth::user()->au_company_id)
				->where('inv_abi_status',1)->get();
		$inv_suppliers=Inv_supplier::where('inv_sup_com_id',Auth::user()->au_company_id)
						->where('inv_sup_status',1)->get();
		return view('inventory.supplier.accounts.paymentcollection',compact('banks','inv_suppliers'));
	}

	public function supplierPaymentCollectionStore(Request $request)
	{
		try{
			$request->validate([
				'trans_date' => 'required|date',
				'amount'=>'required|numeric|min:0',
				'supplier_id'=>'required',
			]);

			$inv_Pro_Invt=new Inv_product_inventory();
			$inv_Pro_Invt->inv_pro_inv_com_id=Auth::user()->au_company_id;
			$inv_Pro_Invt->inv_pro_inv_branch_id=Auth::user()->au_branch_id; // ADDED BRANCH ID
			$inv_Pro_Invt->inv_pro_inv_party_id=$request->supplier_id;
			$inv_Pro_Invt->inv_pro_inv_debit=$request->amount;
			$inv_Pro_Invt->inv_pro_inv_credit=0;
			$inv_Pro_Invt->inv_pro_inv_tran_type=5; // 5=payment collection
			$inv_Pro_Invt->inv_pro_inv_deal_type=1; // for supplier
			$inv_Pro_Invt->inv_pro_inv_invoice_no=Carbon::now()->format('YmdHis');
			$inv_Pro_Invt->inv_pro_inv_tran_desc=$request->reference;
			$inv_Pro_Invt->inv_pro_inv_issue_date=$request->trans_date;
			$inv_Pro_Invt->inv_pro_inv_status=1;
			$inv_Pro_Invt->inv_pro_inv_submit_by=Auth::user()->au_id;
			$inv_Pro_Invt->inv_pro_inv_submit_at=Carbon::now();
			$inv_Pro_Invt->save();

			$inv_Acc_Bank_Statement = new Inv_acc_bank_statement();
			$inv_Acc_Bank_Statement->inv_abs_company_id = Auth::user()->au_company_id;
			$inv_Acc_Bank_Statement->inv_abs_branch_id = Auth::user()->au_branch_id; // ADDED BRANCH ID
			$inv_Acc_Bank_Statement->inv_abs_reference_id = $request->supplier_id;
			$inv_Acc_Bank_Statement->inv_abs_bank_id = $request->bank_id;
			$inv_Acc_Bank_Statement->inv_abs_inventory_id=$inv_Pro_Invt->inv_pro_inv_id;
			$inv_Acc_Bank_Statement->inv_abs_debit = 0;
			$inv_Acc_Bank_Statement->inv_abs_credit = $request->amount;
			$inv_Acc_Bank_Statement->inv_abs_transaction_date = $request->trans_date;
			$inv_Acc_Bank_Statement->inv_abs_description= $request->reference;
			$inv_Acc_Bank_Statement->inv_abs_voucher_no=Carbon::now()->format('YmdHis');
			$inv_Acc_Bank_Statement->inv_abs_status = 1;
			$inv_Acc_Bank_Statement->inv_abs_reference_type = 2; //2= Supplier Payment
			$inv_Acc_Bank_Statement->inv_abs_submit_at = Carbon::now();
			$inv_Acc_Bank_Statement->inv_abs_submit_by = Auth::user()->au_id;
			$inv_Acc_Bank_Statement->save();

			Session::flash('msg','Payment Successfull.');
			return redirect()->back();
		}
		catch(Exception $err)
		{
			Session::flash('errmsg','Something Went Wrong.');
			return redirect()->back();
		}
	}
	
	public function showAccountStatement()
	{
		$inv_pros=Inv_supplier::where('inv_sup_com_id',Auth::user()->au_company_id)
					->where('inv_sup_status',1)
					->get();
		return view('inventory.supplier.accounts.accountstatement',compact('inv_pros'));
	}
	
	public function showAccountStatementDetails(Request $request)
	{
		if ($request->has('searchbtn')) {
			$request->validate([
				'start_date' => 'required',
				'end_date' => 'required',
			]);
			$inv_pros=Inv_product_inventory::where('inv_pro_inv_party_id',$request->supplier_id)
						->where('inv_pro_inv_com_id',Auth::user()->au_company_id)
						->where('inv_pro_inv_branch_id',Auth::user()->au_branch_id) // ADDED BRANCH ID
						->where('inv_pro_inv_issue_date','>=',$request->start_date)
						->where('inv_pro_inv_issue_date','<=',$request->end_date)
						->where('inv_pro_inv_status',1)
						->where('inv_pro_inv_deal_type',1)
						->orderBy('inv_pro_inv_issue_date','desc')
						->orderBy('inv_pro_inv_id','desc')
						->get();
		}
		else
		{
			$inv_pros=Inv_product_inventory::where('inv_pro_inv_party_id',$request->supplier_id)
						->where('inv_pro_inv_com_id',Auth::user()->au_company_id)
						->where('inv_pro_inv_branch_id',Auth::user()->au_branch_id) // ADDED BRANCH ID
						->where('inv_pro_inv_deal_type',1)
						->where('inv_pro_inv_status',1)
						->orderBy('inv_pro_inv_issue_date','desc')
						->orderBy('inv_pro_inv_id','desc')
						->get();
		}
		return view('inventory.supplier.accounts.accountstatementdetails',compact('inv_pros'));
	}
	
	public function downloadAccountStatementDetails(Request $request)
	{
		if($request->start_date!=null && $request->end_date!=null)
		{
			$inv_pros=Inv_product_inventory::where('inv_pro_inv_party_id',$request->supplier_id)
							->where('inv_pro_inv_com_id',Auth::user()->au_company_id)
							->where('inv_pro_inv_branch_id',Auth::user()->au_branch_id) // ADDED BRANCH ID
							->where('inv_pro_inv_issue_date','>=',$request->start_date)
							->where('inv_pro_inv_issue_date','<=',$request->end_date)
							->where('inv_pro_inv_deal_type',1)
							->where('inv_pro_inv_status',1)->groupBy('inv_pro_inv_invoice_no')->get();
		}
		else
		{
			$inv_pros=Inv_product_inventory::where('inv_pro_inv_party_id',$request->supplier_id)
							->where('inv_pro_inv_com_id',Auth::user()->au_company_id)
							->where('inv_pro_inv_branch_id',Auth::user()->au_branch_id) // ADDED BRANCH ID
							->where('inv_pro_inv_deal_type',1)
							->where('inv_pro_inv_status',1)->groupBy('inv_pro_inv_invoice_no')->get();
		}
		$pdf = PDF::loadView('inventory.supplier.accounts.accountstatementdetailsdownload',compact('inv_pros'));
		return $pdf->download('account_statements.pdf');
	}
	
public function loadAjaxInvoiceDetails(Request $request)
{
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    // Fixed OR condition
    $isProductTrans = Inv_product_inventory::where('inv_pro_inv_invoice_no', $request->invoice_id)
        ->where(function($q) {
            $q->where('inv_pro_inv_tran_type', 1)
              ->orWhere('inv_pro_inv_tran_type', 2);
        })
        ->count();

    if ($isProductTrans > 0) {
        $isProductTrans = 1;
    }
        
    $invoiceInfos = Inv_product_inventory::where('inv_pro_inv_invoice_no', $request->invoice_id)
        ->where('inv_pro_inv_deal_type', 1)
        ->where('inv_pro_inv_status', 1)
        ->where('inv_pro_inv_com_id', $com)
        ->where('inv_pro_inv_branch_id', $branch)
        // Eager load the DETAIL row, and the PRODUCT inside the detail
        ->with(['getProductInfo.product']) 
        ->get();

    return view('pages.ajax.invoice_details', compact('invoiceInfos', 'isProductTrans'));
}

	// =================== 18-11-19 ===============
	public function supplierTodayPaymentReport()
	{
		$todayPayments=Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)
						->where('inv_abs_branch_id',Auth::user()->au_branch_id) // ADDED BRANCH ID
						->where('inv_abs_transaction_date',Carbon::now()->format('Y-m-d'))
						->where('inv_abs_reference_type',2)
						->where('inv_abs_status',1)
						->orderBy('inv_abs_id','asc')
						->get();
		return view('inventory.supplier.accounts.today_payments',compact('todayPayments'));
	}
	
    /**
     * AJAX: Load Supplier Balance
     */
    public function ajaxLoadSupplierBalance(Request $request)
    {
        $supplier_id = $request->supplier_id;
        if (empty($supplier_id)) return '';

        $totalDebit = Inv_product_inventory::where('inv_pro_inv_party_id', $supplier_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADDED BRANCH ID
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_status', 1)
            ->sum('inv_pro_inv_debit');

        $totalCredit = Inv_product_inventory::where('inv_pro_inv_party_id', $supplier_id)
            ->where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_branch_id', Auth::user()->au_branch_id) // ADDED BRANCH ID
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_status', 1)
            ->sum('inv_pro_inv_credit');

        $balance = $totalCredit - $totalDebit;

        return '<span style="color: ' . ($balance > 0 ? 'green' : 'red') . '; font-weight: bold;">Balance: ' . number_format($balance, 2) . '</span>';
    }

    /**
     * AJAX: Load Bank Balance
     */
    public function ajaxLoadBankBalance(Request $request)
    {
        $bank_id = $request->bank_id;
        
        if (empty($bank_id)) {
            return '<span class="text-danger">Select a Bank</span>';
        }

        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        // ===== STEP 1: Calculate Total Credit (Money going INTO the bank) =====
        $totalCredit = Inv_acc_bank_statement::where('inv_abs_bank_id', $bank_id)
                        ->where('inv_abs_company_id', $companyId)
                        ->where('inv_abs_branch_id', $branchId) // ADDED BRANCH ID
                        ->where('inv_abs_status', 1)
                        ->sum('inv_abs_credit');

        // ===== STEP 2: Calculate Total Debit (Money going OUT of the bank) =====
        $totalDebit = Inv_acc_bank_statement::where('inv_abs_bank_id', $bank_id)
                        ->where('inv_abs_company_id', $companyId)
                        ->where('inv_abs_branch_id', $branchId) // ADDED BRANCH ID
                        ->where('inv_abs_status', 1)
                        ->sum('inv_abs_debit');

        // ===== STEP 3: Calculate the Balance =====
        $balance = $totalCredit - $totalDebit;

        // ===== STEP 4: Format nicely and return =====
        $balanceFormatted = number_format($balance, 2);

        return '<span style="color: ' . ($balance >= 0 ? 'green' : 'red') . '; font-weight: bold;">Balance: ' . $balanceFormatted . '</span>';
    }
}
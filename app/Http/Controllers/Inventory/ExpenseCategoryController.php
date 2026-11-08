<?php
namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Inv_acc_expense_category;
use Session;
use App\Models\Inv_acc_expense;
use App\Models\Inv_supplier;
use App\Models\Inv_customer;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_acc_bank_statement;
use DB;


class ExpenseCategoryController extends Controller
{
public function showExpenseCategories()
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $categories = Inv_acc_expense_category::where('inv_acc_exp_cat_status', 1)
        ->where('inv_acc_exp_cat_company_id', $companyId)
        ->where('inv_acc_exp_cat_branch_id', $branchId)
        ->orderBy('inv_acc_exp_cat_category_name')
        ->get();

    return view('inventory.accounts.expense.expensecategories', compact('categories'));
}


public function expense_category_edit(Request $request, $id)
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $categories = Inv_acc_expense_category::where('inv_acc_exp_cat_status', 1)
        ->where('inv_acc_exp_cat_company_id', $companyId)
        ->where('inv_acc_exp_cat_branch_id', $branchId)
        ->orderBy('inv_acc_exp_cat_category_name')
        ->get();

    $cat_edit = Inv_acc_expense_category::where('inv_acc_exp_cat_category_id', $id)
        ->where('inv_acc_exp_cat_company_id', $companyId)
        ->where('inv_acc_exp_cat_branch_id', $branchId)
        ->first();

    return view('inventory.accounts.expense.expense_category_edit', compact('cat_edit', 'categories'));
}


	public function expense_category_update(Request $request,$id){

		$cat_update = Inv_acc_expense_category::find($id);
		$cat_update->inv_acc_exp_cat_category_name = $request->exp_category;
		$cat_update->save();

		return redirect()->route('accounts.expense-categories')->with(['exp_up' => 'Expense Categories Updated Successfully']);
	}
public function showExpenses()
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $categories = Inv_acc_expense_category::where('inv_acc_exp_cat_status', 1)
        ->where('inv_acc_exp_cat_company_id', $companyId)
        ->where('inv_acc_exp_cat_branch_id', $branchId)
        ->orderBy('inv_acc_exp_cat_category_name')
        ->get();

    $expenses = Inv_acc_expense::where('inv_acc_exp_status', 1)
        ->where('inv_acc_exp_company_id', $companyId)
        ->where('inv_acc_exp_branch_id', $branchId)
        ->orderBy('inv_acc_exp_expense_name')
        ->get();

    return view('inventory.accounts.expense.expenses', compact('expenses', 'categories'));
}

	public function storeExpenseCategory(Request $request)
	{
		$inv_Acc_Cat=new Inv_acc_expense_category();
		$inv_Acc_Cat->inv_acc_exp_cat_company_id=Auth::user()->au_company_id;
		$inv_Acc_Cat->inv_acc_exp_cat_branch_id=Auth::user()->au_branch_id;
		$inv_Acc_Cat->inv_acc_exp_cat_category_name=$request->exp_category;
		$inv_Acc_Cat->inv_acc_exp_cat_created_by=Auth::user()->au_id;
		$inv_Acc_Cat->created_at=Carbon::now();
		$inv_Acc_Cat->save();
		Session::flash('msg','Category Successfully Added.');
		return redirect()->back();
	}
	public function storeExpenses(Request $request)
	{
		$inv_Acc_Exp=new Inv_acc_expense();

		$inv_Acc_Exp->inv_acc_exp_company_id=Auth::user()->au_company_id;
			$inv_Acc_Exp->inv_acc_exp_branch_id=Auth::user()->au_branch_id;
		$inv_Acc_Exp->inv_acc_exp_category_id=$request->exp_category_id;
		$inv_Acc_Exp->inv_acc_exp_expense_name=$request->exp_name;
		$inv_Acc_Exp->inv_acc_exp_created_by=Auth::user()->au_id;
		$inv_Acc_Exp->created_at=Carbon::now();
		$inv_Acc_Exp->save();

		Session::flash('msg',"Expense's Added Successfully.");
		return redirect()->back();
	}
	public function ajaxLoadExpense(Request $request)
	{
		$expenses=Inv_acc_expense::where('inv_acc_exp_category_id',$request->cat_id)->get();
		return view('pages.ajax.expense_list',compact('expenses'));
	}

public function add_expense_form() {
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    $exp_categories = Inv_acc_expense_category::where('inv_acc_exp_cat_company_id', $com)
                                     ->where('inv_acc_exp_cat_branch_id', $branch)
                                     ->where('inv_acc_exp_cat_status', 1)
                                     ->orderBy('inv_acc_exp_cat_category_name')
                                     ->get();

    // Expense heads grouped under their category in the dropdown
    $expenses = Inv_acc_expense::where('inv_acc_exp_company_id', $com)
                                     ->where('inv_acc_exp_branch_id', $branch)
                                     ->where('inv_acc_exp_status', 1)
                                     ->orderBy('inv_acc_exp_expense_name')
                                     ->get()
                                     ->groupBy('inv_acc_exp_category_id');

    // Any bank or cash account can pay the expense
    $banks = Inv_acc_bank_info::with('bank_info')
                                     ->where('inv_abi_company_id', $com)
                                     ->where('inv_abi_branch_id', $branch)
                                     ->where('inv_abi_status', 1)
                                     ->get();

    return view('inventory.accounts.expense.add_new', compact('exp_categories', 'expenses', 'banks'));
}

	public function add_expense_cost(Request $request)
	{
		$request->validate([
			'trans_date'   => 'required|date_format:d/m/Y',
			'bank_id'      => 'required|integer',
			'expense_id'   => 'required|array|min:1',
			'expense_id.*' => 'required|integer',
			'amount'       => 'required|array|size:' . count((array) $request->expense_id),
			'amount.*'     => 'required|numeric|min:0.01',
			'note.*'       => 'nullable|string|max:255',
			'voucher_no'   => 'nullable|string|max:128',
		]);

		$com = Auth::user()->au_company_id;
		$branch = Auth::user()->au_branch_id;

		$bank = Inv_acc_bank_info::where('inv_abi_id', $request->bank_id)
			->where('inv_abi_company_id', $com)
			->where('inv_abi_branch_id', $branch)
			->where('inv_abi_status', 1)
			->first();

		if (empty($bank)) {
			return redirect()->back()->withInput()->with(['err' => 'The selected bank / cash account does not belong to your company.']);
		}

		$expenseIds = array_map('intval', $request->expense_id);
		$validExpenses = Inv_acc_expense::where('inv_acc_exp_company_id', $com)
			->where('inv_acc_exp_branch_id', $branch)
			->where('inv_acc_exp_status', 1)
			->whereIn('inv_acc_exp_id', $expenseIds)
			->pluck('inv_acc_exp_expense_name', 'inv_acc_exp_id');

		foreach ($expenseIds as $i => $expenseId) {
			if (!isset($validExpenses[$expenseId])) {
				return redirect()->back()->withInput()->with(['err' => 'Row ' . ($i + 1) . ': select a valid expense.']);
			}
		}

		$total = round(array_sum(array_map('floatval', $request->amount)), 2);
		$balance = Inv_acc_bank_statement::getAvailableBalanceByBankId($bank->inv_abi_id);

		if ($balance < $total) {
			return redirect()->back()->withInput()->with(['err' => 'Insufficient balance. Total expense ' . number_format($total, 2) . ' is more than the available balance ' . number_format($balance, 2) . '.']);
		}

		$transDate = Carbon::createFromFormat('d/m/Y', $request->trans_date)->format('Y-m-d');
		$voucherNo = $request->voucher_no ?: Carbon::now()->format('YmdHis');
		$notes = (array) $request->note;

		DB::transaction(function () use ($request, $expenseIds, $validExpenses, $notes, $bank, $transDate, $voucherNo, $com, $branch) {
			foreach ($expenseIds as $i => $expenseId) {
				$expense_cost = new Inv_acc_bank_statement();
				$expense_cost->inv_abs_company_id = $com;
				$expense_cost->inv_abs_branch_id = $branch;
				$expense_cost->inv_abs_reference_id = $expenseId;
				$expense_cost->inv_abs_reference_type = 3; // 3 = expense (money out)
				$expense_cost->inv_abs_bank_id = $bank->inv_abi_id;
				$expense_cost->inv_abs_debit = $request->amount[$i];
				$expense_cost->inv_abs_credit = 0;
				$expense_cost->inv_abs_transaction_date = $transDate;
				$expense_cost->inv_abs_voucher_no = $voucherNo;
				$expense_cost->inv_abs_description = !empty($notes[$i]) ? $notes[$i] : $validExpenses[$expenseId];
				$expense_cost->inv_abs_status = 1;
				$expense_cost->inv_abs_submit_by = Auth::user()->au_id;
				$expense_cost->inv_abs_submit_at = Carbon::now();
				$expense_cost->save();
			}
		});

		return redirect()->back()->with(['suc' => count($expenseIds) . ' expense(s) totalling ' . number_format($total, 2) . ' added successfully. Voucher: ' . $voucherNo]);
	}
	// ================= 16-11-19 ============

public function showAjaxLoadedExpensesCategory(Request $request)
{
    $expenses = Inv_acc_expense::where('inv_acc_exp_category_id', $request->cat_id)
        ->where('inv_acc_exp_company_id', Auth::user()->au_company_id)
        ->where('inv_acc_exp_branch_id', Auth::user()->au_branch_id)
        ->where('inv_acc_exp_status', 1)
        ->get();

    return view('pages.ajax.expenses_by_categories', compact('expenses'));
}
public function showAjaxLoadedExpenses(Request $request)
{
    $expenses = Inv_acc_bank_statement::where('inv_abs_reference_id', $request->exp_id)
        ->where('inv_abs_company_id', Auth::user()->au_company_id)
        ->where('inv_abs_branch_id', Auth::user()->au_branch_id)
        ->where('inv_abs_status', 1)
        ->where(function ($query) {
            $query->where('inv_abs_reference_type', 3)
                  ->orWhere('inv_abs_reference_type', 4);
        })
        ->get();

    return view('pages.ajax.load_expenses_by_ajax', compact('expenses'));
}

}

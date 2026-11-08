<?php

namespace App\Http\Controllers\MoneyExchange;

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


class ExpenseCategoryController extends Controller
{
	public function showExpenseCategories()
	{
		$categories = Inv_acc_expense_category::where('inv_acc_exp_cat_status', 1)
			->where('inv_acc_exp_cat_company_id',Auth::user()->au_company_id)
            ->orderBy('inv_acc_exp_cat_category_name')
            ->get();
        return view('moneyExchange.accounts.expense.expensecategories',compact('categories'));
	}

	public function expense_category_edit(Request $request,$id){
		$categories = Inv_acc_expense_category::where('inv_acc_exp_cat_status', 1)
			->where('inv_acc_exp_cat_company_id',Auth::user()->au_company_id)
            ->orderBy('inv_acc_exp_cat_category_name')
            ->get();
		$cat_edit = Inv_acc_expense_category::where('inv_acc_exp_cat_category_id',$id)
											->where('inv_acc_exp_cat_company_id',Auth::user()->au_company_id)
											->first();
		return view('inventory.accounts.expense.expense_category_edit',compact('cat_edit','categories'));
	}

	public function expense_category_update(Request $request,$id){

		$cat_update = Inv_acc_expense_category::find($id);
		$cat_update->inv_acc_exp_cat_category_name = $request->exp_category;
		$cat_update->save();

		return redirect()->route('accounts.expense-categories')->with(['exp_up' => 'Expense Categories Updated Successfully']);
	}
	public function showExpenses()
	{
		$categories = Inv_acc_expense_category::where('inv_acc_exp_cat_status', 1)
            ->where('inv_acc_exp_cat_company_id',Auth::user()->au_company_id)
            ->orderBy('inv_acc_exp_cat_category_name')
            ->get();

		$expenses = Inv_acc_expense::where('inv_acc_exp_status', 1)
			->where('inv_acc_exp_company_id',Auth::user()->au_company_id)
            ->orderBy('inv_acc_exp_expense_name')
            ->get();
        return view('moneyExchange.accounts.expense.expenses',compact('expenses','categories'));
	}
	public function storeExpenseCategory(Request $request)
	{
		$inv_Acc_Cat=new Inv_acc_expense_category();
		$inv_Acc_Cat->inv_acc_exp_cat_company_id=Auth::user()->au_company_id;
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

	public function add_expense_form(){
		$com = Auth::user()->au_company_id;
		$suppliers = Inv_supplier::where('inv_sup_com_id',$com)
								 ->where('inv_sup_status',1)
								 ->get();
		$customers = Inv_customer::where('inv_cus_com_id',$com)
								  ->where('inv_cus_status',1)
								  ->get();
		$exp_categories = Inv_acc_expense::where('inv_acc_exp_company_id',$com)
										->where('inv_acc_exp_status',1)
										->get();
		// $banks=Inv_acc_bank_info::where('inv_abi_company_id',$com)->get();
		// $expenses = Inv_acc_expense_category::where('inv_acc_exp_cat_company_id',$com)
		// 							->where('inv_acc_exp_cat_status',1)
		// 							->get();
		return view('moneyExchange.accounts.expense.add_new',compact('suppliers','customers','exp_categories'));
	}

	// public function add_expense_cost(Request $request){
	// 	$com = Auth::user()->au_company_id;
	// 	$submit_by = Auth::user()->au_id;
	// 	$submit_at = Carbon::now()->format('Y-m-d H:i:s');
	// 	try {
	// 		$expense_cost = new Inv_acc_bank_statement();
	// 		$expense_cost->inv_abs_company_id = $com;
	// 		$expense_cost->inv_abs_reference_type = 8;
	// 		$expense_cost->inv_abs_reference_id = $request->expense_id;
	// 		$expense_cost->inv_abs_bank_id = $request->bank;
	// 		$expense_cost->inv_abs_transaction_date = $request->transaction;
	// 		$expense_cost->inv_abs_description = $request->reference;
	// 		$expense_cost->inv_abs_credit = $request->amount;
	// 		$expense_cost->inv_abs_submit_by = $submit_by;
	// 		$expense_cost->inv_abs_submit_at = $submit_at;
	// 		$expense_cost->save();
	// 		return redirect()->back()->with(['suc' => 'Expense Cost Added Successfully']);
	// 	} catch (\Exception $e) {
	// 		return redirect()->back()->with(['err' => $e->getMessage()]);
	// 	}
	// }
	// ================= 16-11-19 ============

	public function showAjaxLoadedExpensesCategory(Request $request)
	{
		 $expenses=Inv_acc_expense::where('inv_acc_exp_category_id',$request->cat_id)->where('inv_acc_exp_company_id',Auth::user()->au_company_id)->where('inv_acc_exp_status',1)->get();
        return view('pages.ajax.expenses_by_categories',compact('expenses'));
	}
	public function showAjaxLoadedExpenses(Request $request)
	{
		$expenses=Inv_acc_bank_statement::where('inv_abs_reference_id',$request->exp_id)->where('inv_abs_status',1)->where('inv_abs_reference_type',3)->orWhere('inv_abs_reference_type',4)->where('inv_abs_reference_id',Auth::user()->au_company_id)->get();
        return view('pages.ajax.load_expenses_by_ajax',compact('expenses'));
	}
}

<?php

namespace App\Http\Controllers\MoneyExchange;

use Auth;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inv_ledger_category;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_product_inventory;
use App\Models\Inv_acc_bank_statement;
use App\Models\Inv_ledger;
use App\Models\Inv_customer;
use App\Models\Inv_supplier;
use Carbon\Carbon;

class CurrencyLedgerController extends Controller
{
    public function ledger_insert_form(){
        $com = Auth::user()->au_company_id;
        $categories = Inv_ledger_category::where('inv_ledg_cat_company_id',$com)
                                        ->where('inv_ledg_cat_status',1)
                                        ->get();
        $show_cat = Inv_ledger::where('inv_ledg_company_id',$com)
                              ->where('inv_ledg_status',1)
                              ->get();
                            //   dd($show_cat);
        return view('moneyExchange.accounts.ledger.ledger',compact('categories','show_cat'));
    }

    public function ledger_insert(Request $request){
        $com = Auth::user()->au_company_id;
        $user = Auth::user()->au_id;
        $time = Carbon::now()->format('Y-m-d H:i:s');
        try {
            $ledger = new Inv_ledger();
            $ledger->inv_ledg_company_id = $com;
            $ledger->inv_ledg_category_id = $request->category_id;
            $ledger->inv_ledg_ledger_name = $request->name;
            $ledger->inv_ledg_created_by = $user;
            $ledger->inv_ledg_created_at = $time;

            $ledger->save();
            return redirect()->back()->with(['success' => 'Ledger Entitled Successfully']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['err' => $e->getMessage()]);
        }
    }

    public function ledger_categories(){
        $com = Auth::user()->au_company_id;
        $categories = Inv_ledger_category::where('inv_ledg_cat_company_id',$com)
                                        ->orderBy('inv_ledg_cat_category_name')
                                        ->get();
        return view('moneyExchange.accounts.ledger.ledger_categories',compact('categories'));
    }

    public function ledger_category_edit_ajax(Request $request){
        $com = Auth::user()->au_company_id;
        $ledg_cat_edit = Inv_ledger_category::where('inv_ledg_cat_company_id',$com)
                                            ->where('inv_ledg_cat_cat_id',$request->ledg_cat_id)
                                            ->first();
        return view('pages.ajax.ledger.ledger_cat_update',compact('ledg_cat_edit'));
    }

    public function ledger_category_update(Request $request){
        $com = Auth::user()->au_company_id;
        $update_at = Carbon::now()->format('Y-m-d H:i:s');
        $update_by = Auth::user()->au_id;
        $message = [
            'cat_name.required' => 'Category Name Field Have To be Fill!',
        ];
        $request->validate([
            'cat_name' => 'required',
        ],$message);
        try {
            $ledger_cat_update = Inv_ledger_category::where('inv_ledg_cat_company_id',$com)
                                                    ->where('inv_ledg_cat_cat_id',$request->cat_id)
                                                    ->first();
            $ledger_cat_update->inv_ledg_cat_category_name = $request->cat_name;
            $ledger_cat_update->inv_ledg_cat_status = $request->status;
            $ledger_cat_update->inv_ledg_cat_updated_at = $update_at;
            $ledger_cat_update->inv_ledg_cat_updated_by = $update_by;
            $ledger_cat_update->save();
        } catch (\Exception $e) {
            return redirect()->back()->with(['err_up' => $e->getMessage()]);
        }

        return redirect()->back()->with(['up_suc' => 'Ledger Category Updated Successfully']);
    }

    public function insert_ledger_category(Request $request){
        $com = Auth::user()->au_company_id;
        $user = Auth::user()->au_id;
        $time = Carbon::now()->format('Y-m-d H:i:s');
        try {
            $request->validate([
                'ledg_category' => 'required|unique:inv_ledger_categories,inv_ledg_cat_category_name',
            ]);
            $ledger_category = new Inv_ledger_category();
            $ledger_category->inv_ledg_cat_company_id = $com;
            $ledger_category->inv_ledg_cat_category_name = $request->ledg_category;
            $ledger_category->inv_ledg_cat_created_by = $user;
            $ledger_category->inv_ledg_cat_created_at = $time;
            $ledger_category->save();
            return redirect()->back()->with(['suc' => 'Ledger Category Entitled Successfully']);
        } catch (\Exception $e) {
           return redirect()->back()->with(['err' => $e->getMessage()]);
        }
    }

    public function showAjaxLoadedLedgerCategory(Request $request)
	{
         $ledgers=Inv_ledger::where('inv_ledg_category_id',$request->cat_id)
                            ->where('inv_ledg_company_id',Auth::user()->au_company_id)
                            ->where('inv_ledg_status',1)
                            ->get();
        return view('pages.ajax.ledger_by_categories',compact('ledgers'));
    }

    public function ledger_add(Request $request){
        $com = Auth::user()->au_company_id;
        $total = '';
        $old_date_value_income = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                               ->where('inv_pro_inv_issue_date','=' , $request->dateinput)
                                               ->where('inv_pro_inv_status',1)
                                               ->whereIn('inv_pro_inv_tran_type', [4,15])
                                               ->get();
        $old_date_value_expense = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                                    ->where('inv_pro_inv_issue_date','=' , $request->dateinput)
                                                    ->where('inv_pro_inv_status',1)
                                                    ->whereIn('inv_pro_inv_tran_type', [6,16])
                                                    ->get();
        $customers_n = Inv_customer::where('inv_cus_com_id',$com)
                        ->where('inv_cus_status',1)
                        ->first();

        $ledger_category_n = Inv_ledger::where('inv_ledg_company_id',$com)
                                ->where('inv_ledg_status',1)
                                ->get();
                                            //    dd($old_date_value_expense);
        $total_bal_cd = '';
        if ($request->has('dateinput')) {
        $bank_id = Inv_acc_bank_info::where('inv_abi_company_id',$com)
                                    ->where('inv_abi_account_type',2)
                                    ->where('inv_abi_status',1)
                                    ->first();

        $debit = Inv_acc_bank_statement::where('inv_abs_company_id',$com)
                                        ->where('inv_abs_bank_id',$bank_id->inv_abi_id)
                                        ->where('inv_abs_transaction_date','<',$request->dateinput)
                                        ->sum('inv_abs_debit');
                                        // dd($debit);

        $credit = Inv_acc_bank_statement::where('inv_abs_company_id',$com)
                                        ->where('inv_abs_bank_id',$bank_id->inv_abi_id)
                                        ->where('inv_abs_transaction_date','<',$request->dateinput)
                                        ->sum('inv_abs_credit');
                                        // dd($credit);
        $total_bal_bd = $credit - $debit;

        $customers = Inv_customer::where('inv_cus_com_id',$com)
                                ->where('inv_cus_status',1)
                                ->get();
        $suppliers = Inv_supplier::where('inv_sup_com_id',$com)
                                ->where('inv_sup_status',1)
                                ->get();
        $ledger_category = Inv_ledger_category::where('inv_ledg_cat_company_id',$com)
                                              ->where('inv_ledg_cat_status',1)
                                              ->get();
        return view('moneyExchange.accounts.ledger.add_new',compact('ledger_category','customers','customers_n','suppliers','total_bal_bd','old_date_value_income','old_date_value_expense','ledger_category_n'));
        }else {
            return view('moneyExchange.accounts.ledger.add_new');
    }
    }

    public function insert_ledger_data(Request $request){

        $com = Auth::user()->au_company_id;
        $time = Carbon::now()->format('Y-m-d H:i:s');
        $user = Auth::user()->au_id;
        $issue_date = $request->issue_date;
        $bank_id = Inv_acc_bank_info::where('inv_abi_company_id',$com)
                                    ->where('inv_abi_account_type',2)
                                    ->where('inv_abi_status',1)
                                    ->first();
        // $invoice = Carbon::now()->format('YmdHis');
        if ($request->income) {
            $request->validate([
                'income' => 'required',
                'first_narration' => 'required',
                'cash_in' => 'required',

            ]);
        }else{
        $request->validate([
            'expense' => 'required',
            'second_narration' => 'required',
            'cash_out' => 'required'
        ]);
        }
        DB::beginTransaction();
        try {

                if (isset($request->income) && (count($request->income) > 0) ) {
                    for ($i=0; $i <count($request->income); $i++) {
                        $last_pro_inv = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->whereIn('inv_pro_inv_tran_type', [6,4,15,16])
                                        ->orderBy('inv_pro_inv_id', 'DESC')
                                        ->first();
                        if(!empty($last_pro_inv)) {
                            $last_pro_inv_memo_no = $last_pro_inv->inv_pro_inv_invoice_no;
                            $last_data = substr($last_pro_inv_memo_no, 8);
                            if(is_numeric($last_data)) {
                                $last_number = $last_data + 1;
                                $last_number_length = strlen($last_number);
                                if ($last_number_length < 2) {
                                    $less_number = 2-$last_number_length;
                                    $sl_prefix = "";
                                    for ($x=0; $x <$less_number ; $x++) {
                                        $sl_prefix = $sl_prefix . "0";
                                    }
                                    $last_number = $sl_prefix . $last_number;
                                }

                                $new_memo_no = "MR".$com.($last_number);
                            } else {
                                $new_memo_no = "MR".$com."0001";
                            }
                        } else {
                            $new_memo_no = "MR".$com."0001";
                        }
                        $ledge_inventory = new Inv_product_inventory();
                        $ledge_inventory->inv_pro_inv_com_id = $com;
                        $ledge_inventory->inv_pro_inv_party_id = $request->customer[$i];
                        $ledge_inventory->inv_pro_inv_exp_id = $request->income[$i];
                        $ledge_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                        $ledge_inventory->inv_pro_inv_debit = 0;
                        $ledge_inventory->inv_pro_inv_credit = $request->cash_in[$i];
                        $ledge_inventory->inv_pro_inv_issue_date = $issue_date;
                        $ledge_inventory->inv_pro_inv_tran_desc = $request->first_narration[$i];
                        if ($request->customer[$i] != '') {
                            $ledge_inventory->inv_pro_inv_deal_type = 2;
                            $ledge_inventory->inv_pro_inv_tran_type = 4;
                        }else {
                            $ledge_inventory->inv_pro_inv_deal_type = 3;//ledger-income
                            $ledge_inventory->inv_pro_inv_tran_type = 15;//cash-in
                        }
                        $ledge_inventory->inv_pro_inv_status = 1;
                        $ledge_inventory->inv_pro_inv_submit_at = $time;
                        $ledge_inventory->inv_pro_inv_submit_by = $user;
                        $ledge_inventory->save();

                        $ledger_add = new Inv_acc_bank_statement();
                        $ledger_add->inv_abs_company_id = $com;
                        $ledger_add->inv_abs_inventory_id = $ledge_inventory->inv_pro_inv_id;
                        $ledger_add->inv_abs_reference_id = $request->customer[$i];
                        $ledger_add->inv_abs_reference_type = 8; //income-add
                        $ledger_add->inv_abs_bank_id = $bank_id->inv_abi_id;
                        $ledger_add->inv_abs_debit = 0;
                        $ledger_add->inv_abs_credit = $request->cash_in[$i];
                        $ledger_add->inv_abs_transaction_date = $issue_date;
                        $ledger_add->inv_abs_voucher_no = $new_memo_no;
                        $ledger_add->inv_abs_description = $request->first_narration[$i];
                        $ledger_add->inv_abs_status = 1;
                        $ledger_add->inv_abs_submit_by = $user;
                        $ledger_add->inv_abs_submit_at = $time;
                        $ledger_add->save();
                    }
                }
                if (isset($request->expense) && (count($request->expense)) > 0) {
                    for ($i=0; $i <count($request->expense); $i++) {
                        $last_pro_inv = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                                        ->whereIn('inv_pro_inv_tran_type', [6,4,15,16])
                                                        ->orderBy('inv_pro_inv_id', 'DESC')
                                                        ->first();
                        if(!empty($last_pro_inv)) {
                            $last_pro_inv_memo_no = $last_pro_inv->inv_pro_inv_invoice_no;
                            $last_data = substr($last_pro_inv_memo_no, 8);
                            if(is_numeric($last_data)) {
                                $last_number = $last_data + 1;
                                $last_number_length = strlen($last_number);
                                if ($last_number_length < 2) {
                                    $less_number = 2-$last_number_length;
                                    $sl_prefix = "";
                                    for ($x=0; $x <$less_number ; $x++) {
                                        $sl_prefix = $sl_prefix . "0";
                                    }
                                    $last_number = $sl_prefix . $last_number;
                                }

                                $new_memo_no = "MR".$com.($last_number);
                            } else {
                                $new_memo_no = "MR".$com."0001";
                            }
                        } else {
                            $new_memo_no = "MR".$com."0001";
                        }
                        $ledge_inventory = new Inv_product_inventory();
                        $ledge_inventory->inv_pro_inv_com_id = $com;
                        $ledge_inventory->inv_pro_inv_party_id = $request->supplier[$i];
                        $ledge_inventory->inv_pro_inv_exp_id = $request->expense[$i];
                        $ledge_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                        $ledge_inventory->inv_pro_inv_debit = 0;
                        $ledge_inventory->inv_pro_inv_credit = $request->cash_out[$i];
                        $ledge_inventory->inv_pro_inv_issue_date = $issue_date;
                        $ledge_inventory->inv_pro_inv_tran_desc = $request->second_narration[$i];
                        if ($request->supplier[$i] != '') {
                            $ledge_inventory->inv_pro_inv_deal_type = 1;
                            $ledge_inventory->inv_pro_inv_tran_type = 6;
                        }else {
                            $ledge_inventory->inv_pro_inv_deal_type = 4;//ledger-expense
                            $ledge_inventory->inv_pro_inv_tran_type = 16;//cash-out
                        }
                        $ledge_inventory->inv_pro_inv_status = 1;
                        $ledge_inventory->inv_pro_inv_submit_at = $time;
                        $ledge_inventory->inv_pro_inv_submit_by = $user;
                        $ledge_inventory->save();

                        $ledger_add = new Inv_acc_bank_statement();
                        $ledger_add->inv_abs_company_id = $com;
                        $ledger_add->inv_abs_inventory_id = $ledge_inventory->inv_pro_inv_id;
                        $ledger_add->inv_abs_reference_id = $request->supplier[$i];
                        $ledger_add->inv_abs_reference_type = 9; //expense-add
                        $ledger_add->inv_abs_bank_id = $bank_id->inv_abi_id;
                        $ledger_add->inv_abs_debit = $request->cash_out[$i];
                        $ledger_add->inv_abs_credit = 0;
                        $ledger_add->inv_abs_transaction_date = $issue_date;
                        $ledger_add->inv_abs_voucher_no = $new_memo_no;
                        $ledger_add->inv_abs_description = $request->second_narration[$i];
                        $ledger_add->inv_abs_status = 1;
                        $ledger_add->inv_abs_submit_by = $user;
                        $ledger_add->inv_abs_submit_at = $time;
                        $ledger_add->save();
                    }
                }
             } catch (\Exception $e) {
                    DB::rollback();
                    return redirect()->back()->with(['err' => $e->getMessage()])->withInput();
             }
             DB::commit();
             return redirect()->back()->with(['suc' => 'Added Successfully']);

    }

    public function update_ledger_data(Request $request){

        $com = Auth::user()->au_company_id;
        $time = Carbon::now()->format('Y-m-d H:i:s');
        $user = Auth::user()->au_id;

        try {
                        $ledge_inventory_update = Inv_product_inventory::find($request->cash_in_id);
                        $ledge_inventory_update->inv_pro_inv_party_id = $request->customer;
                        $ledge_inventory_update->inv_pro_inv_exp_id = $request->income;
                        $ledge_inventory_update->inv_pro_inv_credit = $request->cash_in;
                        $ledge_inventory_update->inv_pro_inv_tran_desc = $request->desc;
                        $ledge_inventory_update->inv_pro_inv_update_at = $time;
                        $ledge_inventory_update->inv_pro_inv_update_by = $user;
                        $ledge_inventory_update->save();

                        $ledger_update = Inv_acc_bank_statement::where('inv_abs_company_id',$com)
                                                                ->where('inv_abs_inventory_id',$request->cash_in_id)
                                                                ->where('inv_abs_reference_type',8)
                                                                ->first();
                        $ledger_update->inv_abs_reference_id = $request->customer;
                        $ledger_update->inv_abs_credit = $request->cash_in;
                        $ledger_update->inv_abs_description = $request->desc;
                        $ledger_update->inv_abs_updated_by = $user;
                        $ledger_update->inv_abs_updated_at = $time;
                        $ledger_update->save();
             } catch (\Exception $e) {
                    return redirect()->back()->with(['errup' => $e->getMessage()])->withInput();
             }
             return redirect()->back()->with(['sucup' => 'Updated Successfully']);

    }

    public function update_ledger_cash_out_data(Request $request){

        $com = Auth::user()->au_company_id;
        $time = Carbon::now()->format('Y-m-d H:i:s');
        $user = Auth::user()->au_id;

        try {
                        $ledge_inventory_update_cash_out = Inv_product_inventory::find($request->cash_out_id);
                        $ledge_inventory_update_cash_out->inv_pro_inv_party_id = $request->supplier;
                        $ledge_inventory_update_cash_out->inv_pro_inv_exp_id = $request->expense;
                        $ledge_inventory_update_cash_out->inv_pro_inv_credit = $request->exp_cash_out;
                        $ledge_inventory_update_cash_out->inv_pro_inv_tran_desc = $request->exp_desc;
                        $ledge_inventory_update_cash_out->inv_pro_inv_update_at = $time;
                        $ledge_inventory_update_cash_out->inv_pro_inv_update_by = $user;
                        $ledge_inventory_update_cash_out->save();

                        $ledger_update_cash_out = Inv_acc_bank_statement::where('inv_abs_company_id',$com)
                                                                ->where('inv_abs_inventory_id',$request->cash_out_id)
                                                                ->where('inv_abs_reference_type',9)
                                                                ->first();
                        $ledger_update_cash_out->inv_abs_reference_id = $request->supplier;
                        $ledger_update_cash_out->inv_abs_debit = $request->exp_cash_out;
                        $ledger_update_cash_out->inv_abs_description = $request->exp_desc;
                        $ledger_update_cash_out->inv_abs_updated_by = $user;
                        $ledger_update_cash_out->inv_abs_updated_at = $time;
                        $ledger_update_cash_out->save();
             } catch (\Exception $e) {
                    return redirect()->back()->with(['errup' => $e->getMessage()])->withInput();
             }
             return redirect()->back()->with(['sucup' => 'Updated Successfully']);

    }

    public function ledger_cash_in_ajax(Request $request){
        $com = Auth::user()->au_company_id;
        $customers = Inv_customer::where('inv_cus_com_id',$com)
                                ->where('inv_cus_status',1)
                                ->get();
        $ledger_category = Inv_ledger_category::where('inv_ledg_cat_company_id',$com)
                                              ->where('inv_ledg_cat_status',1)
                                              ->get();
        $cash_in_ajax = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                             ->where('inv_pro_inv_id',$request->cash_id)
                                             ->first();
        return view('pages.ajax.ledger.cash_in_ajax',compact('customers','ledger_category','cash_in_ajax'));
    }

    public function ledger_cash_out_ajax(Request $request){
        $com = Auth::user()->au_company_id;
        $suppliers = Inv_supplier::where('inv_sup_com_id',$com)
                                ->where('inv_sup_status',1)
                                ->get();
        $ledger_category = Inv_ledger_category::where('inv_ledg_cat_company_id',$com)
                                              ->where('inv_ledg_cat_status',1)
                                              ->get();
        $cash_out_ajax = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                             ->where('inv_pro_inv_id',$request->cash_out_id)
                                             ->first();
        return view('pages.ajax.ledger.cash_out_ajax',compact('suppliers','ledger_category','cash_out_ajax'));
    }

    public function bank_ledger(Request $request){
        $com = Auth::user()->au_company_id;
        $bank_bal_debit = Inv_acc_bank_statement::where('inv_abs_company_id',$com)
                                          ->where('inv_abs_bank_id',$request->bank)
                                          ->sum('inv_abs_debit');


        $bank_infos = Inv_acc_bank_info::where('inv_abi_company_id',$com)
                                        ->where('inv_abi_account_type',1)
                                        ->where('inv_abi_status',1)
                                        ->get();
        $customers = Inv_customer::where('inv_cus_com_id',$com)
                                    ->where('inv_cus_status',1)
                                    ->get();
        $suppliers = Inv_supplier::where('inv_sup_com_id',$com)
                                ->where('inv_sup_status',1)
                                ->get();
        return view('inventory.accounts.ledger.bank',compact('bank_infos','customers','suppliers','bank_bal_debit'));
    }

    public function ajaxBankBalance(Request $request)
	{
        $total_bal = '';
        $com = Auth::user()->au_company_id;
        $credit= Inv_acc_bank_statement::where('inv_abs_bank_id',$request->bank_id)
                                            ->where('inv_abs_company_id',$com)
                                            ->sum('inv_abs_credit')-
        $debit = Inv_acc_bank_statement::where('inv_abs_bank_id',$request->bank_id)
                                        ->where('inv_abs_company_id',$com)
                                        ->sum('inv_abs_debit');
        $total_bal = $credit - $debit;
			return view('pages.ajax.ledger.bank_balance',compact('total_bal'));
    }

    public function bank_ledger_submit(Request $request){

        $com = Auth::user()->au_company_id;
        $time = Carbon::now()->format('Y-m-d H:i:s');
        $user = Auth::user()->au_id;
        $issue_date = $request->issue_date;
        $bank_id = Inv_acc_bank_info::where('inv_abi_company_id',$com)
                                    ->where('inv_abi_account_type',1)
                                    ->where('inv_abi_status',1)
                                    ->first();
        // $invoice = Carbon::now()->format('YmdHis');
        if ($request->income) {
            $request->validate([
                'income' => 'required',
                'first_narration' => 'required',
                'cash_in' => 'required',

            ]);
        }else{
        $request->validate([
            'expense' => 'required',
            'second_narration' => 'required',
            'cash_out' => 'required'
        ]);
        }
        DB::beginTransaction();
        try {

                if (isset($request->income) && (count($request->income) > 0) ) {
                    for ($i=0; $i <count($request->income); $i++) {
                        $last_pro_inv = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->whereIn('inv_pro_inv_tran_type', [6,4,15,16])
                                        ->orderBy('inv_pro_inv_id', 'DESC')
                                        ->first();
                        if(!empty($last_pro_inv)) {
                            $last_pro_inv_memo_no = $last_pro_inv->inv_pro_inv_invoice_no;
                            $last_data = substr($last_pro_inv_memo_no, 8);
                            if(is_numeric($last_data)) {
                                $last_number = $last_data + 1;
                                $last_number_length = strlen($last_number);
                                if ($last_number_length < 2) {
                                    $less_number = 2-$last_number_length;
                                    $sl_prefix = "";
                                    for ($x=0; $x <$less_number ; $x++) {
                                        $sl_prefix = $sl_prefix . "0";
                                    }
                                    $last_number = $sl_prefix . $last_number;
                                }

                                $new_memo_no = "MR".$com.($last_number);
                            } else {
                                $new_memo_no = "MR".$com."0001";
                            }
                        } else {
                            $new_memo_no = "MR".$com."0001";
                        }
                        $ledge_inventory = new Inv_product_inventory();
                        $ledge_inventory->inv_pro_inv_com_id = $com;
                        $ledge_inventory->inv_pro_inv_party_id = $request->customer[$i];
                        $ledge_inventory->inv_pro_inv_exp_id = $request->income[$i];
                        $ledge_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                        $ledge_inventory->inv_pro_inv_debit = 0;
                        $ledge_inventory->inv_pro_inv_credit = $request->cash_in[$i];
                        $ledge_inventory->inv_pro_inv_issue_date = $issue_date;
                        $ledge_inventory->inv_pro_inv_tran_desc = $request->first_narration[$i];
                        if ($request->customer[$i] != '') {
                            $ledge_inventory->inv_pro_inv_deal_type = 2;
                            $ledge_inventory->inv_pro_inv_tran_type = 4;
                        }else {
                            $ledge_inventory->inv_pro_inv_deal_type = 3;//ledger-income
                            $ledge_inventory->inv_pro_inv_tran_type = 15;//cash-in
                        }
                        $ledge_inventory->inv_pro_inv_status = 1;
                        $ledge_inventory->inv_pro_inv_submit_at = $time;
                        $ledge_inventory->inv_pro_inv_submit_by = $user;
                        $ledge_inventory->save();

                        $ledger_add = new Inv_acc_bank_statement();
                        $ledger_add->inv_abs_company_id = $com;
                        $ledger_add->inv_abs_inventory_id = $ledge_inventory->inv_pro_inv_id;
                        $ledger_add->inv_abs_reference_id = $request->customer[$i];
                        $ledger_add->inv_abs_reference_type = 8; //income-add
                        $ledger_add->inv_abs_bank_id = $bank_id->inv_abi_id;
                        $ledger_add->inv_abs_debit = 0;
                        $ledger_add->inv_abs_credit = $request->cash_in[$i];
                        $ledger_add->inv_abs_transaction_date = $issue_date;
                        $ledger_add->inv_abs_voucher_no = $new_memo_no;
                        $ledger_add->inv_abs_description = $request->first_narration[$i];
                        $ledger_add->inv_abs_status = 1;
                        $ledger_add->inv_abs_submit_by = $user;
                        $ledger_add->inv_abs_submit_at = $time;
                        $ledger_add->save();
                    }
                }
                if (isset($request->expense) && (count($request->expense)) > 0) {
                    for ($i=0; $i <count($request->expense); $i++) {
                        $last_pro_inv = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                                        ->whereIn('inv_pro_inv_tran_type', [6,4,15,16])
                                                        ->orderBy('inv_pro_inv_id', 'DESC')
                                                        ->first();
                        if(!empty($last_pro_inv)) {
                            $last_pro_inv_memo_no = $last_pro_inv->inv_pro_inv_invoice_no;
                            $last_data = substr($last_pro_inv_memo_no, 8);
                            if(is_numeric($last_data)) {
                                $last_number = $last_data + 1;
                                $last_number_length = strlen($last_number);
                                if ($last_number_length < 2) {
                                    $less_number = 2-$last_number_length;
                                    $sl_prefix = "";
                                    for ($x=0; $x <$less_number ; $x++) {
                                        $sl_prefix = $sl_prefix . "0";
                                    }
                                    $last_number = $sl_prefix . $last_number;
                                }

                                $new_memo_no = "MR".$com.($last_number);
                            } else {
                                $new_memo_no = "MR".$com."0001";
                            }
                        } else {
                            $new_memo_no = "MR".$com."0001";
                        }
                        $ledge_inventory = new Inv_product_inventory();
                        $ledge_inventory->inv_pro_inv_com_id = $com;
                        $ledge_inventory->inv_pro_inv_party_id = $request->supplier[$i];
                        $ledge_inventory->inv_pro_inv_exp_id = $request->expense[$i];
                        $ledge_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                        $ledge_inventory->inv_pro_inv_debit = 0;
                        $ledge_inventory->inv_pro_inv_credit = $request->cash_out[$i];
                        $ledge_inventory->inv_pro_inv_issue_date = $issue_date;
                        $ledge_inventory->inv_pro_inv_tran_desc = $request->second_narration[$i];
                        if ($request->supplier[$i] != '') {
                            $ledge_inventory->inv_pro_inv_deal_type = 1;
                            $ledge_inventory->inv_pro_inv_tran_type = 6;
                        }else {
                            $ledge_inventory->inv_pro_inv_deal_type = 4;//ledger-expense
                            $ledge_inventory->inv_pro_inv_tran_type = 16;//cash-out
                        }
                        $ledge_inventory->inv_pro_inv_status = 1;
                        $ledge_inventory->inv_pro_inv_submit_at = $time;
                        $ledge_inventory->inv_pro_inv_submit_by = $user;
                        $ledge_inventory->save();

                        $ledger_add = new Inv_acc_bank_statement();
                        $ledger_add->inv_abs_company_id = $com;
                        $ledger_add->inv_abs_inventory_id = $ledge_inventory->inv_pro_inv_id;
                        $ledger_add->inv_abs_reference_id = $request->supplier[$i];
                        $ledger_add->inv_abs_reference_type = 9; //expense-add
                        $ledger_add->inv_abs_bank_id = $bank_id->inv_abi_id;
                        $ledger_add->inv_abs_debit = $request->cash_out[$i];
                        $ledger_add->inv_abs_credit = 0;
                        $ledger_add->inv_abs_transaction_date = $issue_date;
                        $ledger_add->inv_abs_voucher_no = $new_memo_no;
                        $ledger_add->inv_abs_description = $request->second_narration[$i];
                        $ledger_add->inv_abs_status = 1;
                        $ledger_add->inv_abs_submit_by = $user;
                        $ledger_add->inv_abs_submit_at = $time;
                        $ledger_add->save();
                    }
                }
             } catch (\Exception $e) {
                    DB::rollback();
                    return redirect()->back()->with(['err' => $e->getMessage()])->withInput();
             }
             DB::commit();
             return redirect()->back()->with(['suc' => 'Added Successfully']);

    }
}

<?php

namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Inv_customer;
use App\Models\Inv_product_inventory;
use App\Models\Inv_customer_inventory;
use App\Models\Inv_supplier;
use App\Models\Sds_query_book;

class InvCustomerController extends Controller
{
    public function customer_add_page(){
        return view('inventory.customer.add');
    }

    public function customer_add_submit(Request $request){

        $com = Auth::user()->au_company_id;
         $branch = Auth::user()->au_branch_id;
        $submit_by = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');
        DB::beginTransaction();
        try{
            $inv_cus = new Inv_customer;
            $request->validate([
                'name' => 'required',
                'mobile' => 'required',
            ]);
            $inv_cus->inv_cus_com_id = $com;
                $inv_cus->inv_cus_branch_id = $branch;
            $inv_cus->inv_cus_name = Input::get('name');
            $inv_cus->inv_cus_com_name = Input::get('com_name');
            $inv_cus->inv_cus_mobile = Input::get('mobile');
            $inv_cus->inv_cus_email = Input::get('email');
            $inv_cus->inv_cus_address = Input::get('address');
            $inv_cus->inv_cus_customer_type = Input::get('type');
            $inv_cus->inv_cus_website = Input::get('website');
            $inv_cus->inv_cus_status = 1;
            $inv_cus->inv_cus_submit_by = $submit_by;
            $inv_cus->inv_cus_submit_at = $submit_at;
            $inv_cus->save();

            if (Input::get('balance') > 0) {
                if (Input::get('bal_type') == 1) {
                    $credit_amount = 0;
                    $debit_amount = Input::get('balance');
                } else {
                    $credit_amount = Input::get('balance');
                    $debit_amount = 0;
                }
                

                $new_memo_no = Inv_customer::getNewCustomerMemoNo();

                $inv_cus_inv = new Inv_product_inventory;
                $inv_cus_inv->inv_pro_inv_com_id  = $com;
                     $inv_cus_inv->inv_pro_inv_branch_id  = $branch;
                $inv_cus_inv->inv_pro_inv_party_id = $inv_cus->inv_cus_id;
                $inv_cus_inv->inv_pro_inv_invoice_no = $new_memo_no;
                $inv_cus_inv->inv_pro_inv_unit_price = 0;
                $inv_cus_inv->inv_pro_inv_debit = $debit_amount;
                $inv_cus_inv->inv_pro_inv_credit = $credit_amount;
                $inv_cus_inv->inv_pro_inv_issue_date = Carbon::now();
                $inv_cus_inv->inv_pro_inv_tran_desc = "Opening Balance";
                $inv_cus_inv->inv_pro_inv_deal_type = 2;//2=customer
                $inv_cus_inv->inv_pro_inv_tran_type = 3;//opening balance/ deposit withdraw
                $inv_cus_inv->inv_pro_inv_status = 1;
                $inv_cus_inv->inv_pro_inv_submit_at = $submit_at;
                $inv_cus_inv->inv_pro_inv_submit_by = $submit_by;
                $inv_cus_inv->save();

            }
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['cus_error' => 'Something Went Wrong'.$e->getMessage()]);
        }
        DB::commit();
        return redirect()->back()->with(['cus_add' => 'Customer Added Successfully']);
    }


    public function customer_exist(Request $request)
    {
        $com = Auth::user()->au_company_id;
         $branch = Auth::user()->au_branch_id;
        $exist = Inv_customer::where('inv_cus_mobile', $request->mobile)
            ->where('inv_cus_com_id', $com)
                   ->where('inv_cus_branch_id', $branch)
            ->first();

        if (!empty($exist)) {
            return 405;
        } else {
            $exist_cus = Sds_query_book::where('qb_mobile', $request->mobile)
                ->orWhere('qb_mobile1', $request->mobile)
                ->orWhere('qb_mobile2', $request->mobile)
                ->first();

            if (!empty($exist_cus)) {
                return $exist_cus;
            } else {
                return 200;
            }
        }
    }


    public function customer_list(){
        $com = Auth::user()->au_company_id;
         $branch = Auth::user()->au_branch_id;
        $inv_cuses = Inv_customer::where('inv_cus_com_id',$com)
                               ->where('inv_cus_branch_id',$branch)
                               ->where('inv_cus_status',1)
                               ->get();
        return view('inventory.customer.list',compact('inv_cuses'));
    }

    public function customer_detail($id){
        $cus_det = Inv_customer::where('inv_cus_id',$id)->first();

        return view('inventory.customer.customer_detail',compact('cus_det'));
    }

    public function customer_delete(Request $request,$id){
        $cus_del = Inv_customer::find($id);
        $cus_del->inv_cus_status = 0;
        $cus_del->save();
        return redirect()->back()->with(['cus_del' => 'Customer Deleted Successfully']);
    }

    public function customer_edit_page($id){
        $cus_info = Inv_customer::where('inv_cus_id',$id)->first();
        return view('inventory.customer.customer_edit',compact('cus_info'));
    }

    public function customer_edit(Request $request,$id){
        $update_by = Auth::user()->au_id;
        $update_at = Carbon::now()->format('Y-m-d H:i:s');
        $cus_edit = Inv_customer::find($id);
        $request->validate([
            'name' => 'required',
            'com_name' => 'required',
            'mobile' => 'required',
        ]);
        $cus_edit->inv_cus_name = $request->name;
        $cus_edit->inv_cus_com_name = $request->com_name;
        $cus_edit->inv_cus_mobile = $request->mobile;
        $cus_edit->inv_cus_email = $request->email;
        $cus_edit->inv_cus_address = $request->address;
        $cus_edit->inv_cus_website = $request->website;
        $cus_edit->inv_cus_update_by = $update_by;
        $cus_edit->inv_cus_update_at = $update_at;
        $cus_edit->save();
        return redirect()->route('customer.customer_list')->with(['cus_up' => 'Customer Updated Successfully']);
    }
}

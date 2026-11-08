<?php

namespace App\Http\Controllers\Inventory;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inv_product_inventory;
use App\Models\Inv_product_temporary;

class ProductReturnController extends Controller
{
    public function sale_return(){
        $com = Auth::user()->au_company_id;
        $today = Carbon::now()->format('Y-m-d');
        $subDays = Carbon::now()->subDays(15)->format('Y-m-d');
        // dd($subDays);
        $sale_return = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                            ->where('inv_pro_inv_deal_type',2)
                                            ->where('inv_pro_inv_tran_type',1)
                                            ->where('inv_pro_inv_issue_date','<=',$today)
                                            ->where('inv_pro_inv_issue_date','>=',$subDays)
                                            ->where('inv_pro_inv_status',1)
                                            ->groupBy('inv_pro_inv_invoice_no')
                                            ->orderBy('inv_pro_inv_invoice_no')
                                            ->get();
        $available_return_added_cart = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type', 6)
            ->first();
        if(!empty($available_return_added_cart)) {
            $added_invoice_no = $available_return_added_cart->inv_pro_temp_invoice_no;
        } else {
            $added_invoice_no = '';
        }

        return view('inventory.product_return.sell_return',compact('sale_return', 'added_invoice_no'));
    }

    public function sale_product_ajax(Request $request){
        $com = Auth::user()->au_company_id;

        Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_invoice_no', '!=', $request->inv_no)
            ->where('inv_pro_temp_deal_type', 6)
            ->delete();

        $sale_pro = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                        ->where('inv_pro_inv_deal_type',2)
                                        ->where('inv_pro_inv_tran_type',1)
                                        ->where('inv_pro_inv_status',1)
                                        ->where('inv_pro_inv_invoice_no',$request->inv_no)
                                        ->get();

        return view('pages.ajax.return.sale_return_ajax',compact('sale_pro'));
    }

    public function buy_return(){
        $com = Auth::user()->au_company_id;
        $buy_return = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                            ->where('inv_pro_inv_deal_type',1)
                                            ->where('inv_pro_inv_tran_type',1)
                                            ->where('inv_pro_inv_status',1)
                                            ->groupBy('inv_pro_inv_invoice_no')
                                            ->orderBy('inv_pro_inv_invoice_no')
                                            ->get();
        $available_return_added_cart = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type', 5)
            ->first();
        if(!empty($available_return_added_cart)) {
            $added_invoice_no = $available_return_added_cart->inv_pro_temp_invoice_no;
        } else {
            $added_invoice_no = '';
        }
        return view('inventory.product_return.buy_return',compact('buy_return', 'added_invoice_no'));
    }

    public function buy_product_ajax(Request $request){
        $com = Auth::user()->au_company_id;
        Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_invoice_no', '!=', $request->inv_no)
            ->where('inv_pro_temp_deal_type', 5)
            ->delete();

        $buy_pro = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                        ->where('inv_pro_inv_deal_type',1)
                                        ->where('inv_pro_inv_tran_type',1)
                                        ->where('inv_pro_inv_status',1)
                                        ->where('inv_pro_inv_invoice_no',$request->inv_no)
                                        ->get();

        return view('pages.ajax.return.buy_return_ajax',compact('buy_pro'));
    }
}

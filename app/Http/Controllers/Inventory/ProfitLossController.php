<?php

namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inv_product_inventory;
use App\Models\Inv_product_detail;

class ProfitLossController extends Controller
{
    public function profit_statement(){
        $com = Auth::user()->au_company_id;
        $pro_ret = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                        ->where('inv_pro_inv_deal_type',2)
                                        ->where('inv_pro_inv_tran_type',2)
                                        ->get();
        $pro_buy = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                        ->where('inv_pro_inv_deal_type',1)
                                        ->where('inv_pro_inv_tran_type',1)
                                        ->get();
        $pro_buy_ret = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                        ->where('inv_pro_inv_deal_type',1)
                                        ->where('inv_pro_inv_tran_type',2)
                                        ->get();

        $profits = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                    ->where('inv_pro_inv_deal_type',2)
                                    ->where('inv_pro_inv_tran_type',1)
                                    ->get();

        return view('inventory.profit_loss.profit',compact('profits','pro_ret'));
    }

    public function loss_statement(Request $request){
        $com = Auth::user()->au_company_id;

        $products = Inv_product_detail::where('inv_pro_det_com_id', $com)->get();



            // ModelName::whereRaw('IF (`inv_pro_inv_deal_type` = 1, `inv_pro_inv_tran_type` = 1)')->get();
        return view('inventory.profit_loss.loss',compact('products'));
    }
}

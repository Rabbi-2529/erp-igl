<?php

namespace App\Http\Controllers\MoneyExchange;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inv_supplier;
use App\Models\Inv_product_detail;
use App\Models\Inv_customer;
use App\Models\MexCustomer;
use App\Models\MexCurrency;
use App\Models\MexCountry;
use App\Models\Models\MexCurrencyDetail;
use Auth;

class CurrencyInventoryController extends Controller
{
    public function buy_product_add(){
        $com = Auth::user()->au_company_id;
        $suppliers = Inv_supplier::where('inv_sup_com_id',$com)
                                 ->where('inv_sup_status',1)
                                 ->orderBy('inv_sup_com_name','ASC')
                                 ->get();
        $products = Inv_product_detail::where('inv_pro_det_com_id',$com)
                                            ->where('inv_pro_det_status',1)
                                            ->orderBy('inv_pro_det_pro_id','ASC')
                                            ->get();
        return view('inventory.product_inventory.add',compact('suppliers','products'));
    }

    public function buy_currency_new(){
        $com = Auth::user()->au_company_id;
        $suppliers = Inv_supplier::where('inv_sup_com_id',$com)
                                 ->where('inv_sup_status',1)
                                 ->orderBy('inv_sup_com_name','ASC')
                                 ->get();
        $sell_pro = MexCurrency::all();
        $customers = MexCustomer::where('com_id',$com)
                                  ->where('status',1)
                                  ->get();
        return view('moneyExchange.currency_inventory.add_new',compact('sell_pro','customers'));
    }

    public function buy_product_submit(Request $request){
        $com = Auth::user()->au_company_id;
        $submit_by = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');

        $request->validate([
            'supplier' => 'required',
            'product' => 'required',
            'issue' => 'required',
            'qty' => 'required|integer|min:1',
            'memo' => 'required|max:15',
            'warrenty' => 'required',
        ]);
        $pro_det = Inv_product_detail::where('inv_pro_det_com_id',$com)
                                    ->where('inv_pro_det_id',Input::get('product'))
                                    ->first();
        if (!empty($pro_det)) {
            $pro_det->inv_pro_det_available_qty = $pro_det->inv_pro_det_available_qty + Input::get('qty');
            $pro_det->save();
            $pro_buy_price = $pro_det->inv_pro_det_buy_price;
            $total_purchase_price = $pro_buy_price * Input::get('qty');

            $pro_inv = new Inv_product_inventory;
            $pro_inv->inv_pro_inv_com_id = $com;
            $pro_inv->inv_pro_inv_prodet_id = Input::get('product');
            $pro_inv->inv_pro_inv_ref_id = Input::get('supplier');
            $pro_inv->inv_pro_inv_ref_type = 1; //1=suppliers
            $pro_inv->inv_pro_inv_issue_date = Input::get('issue');
            $pro_inv->inv_pro_inv_qty = Input::get('qty');
            $pro_inv->inv_pro_inv_pur_price = $total_purchase_price;
            $pro_inv->inv_pro_inv_memo_no = Input::get('memo');
            $pro_inv->inv_pro_inv_exp_date = Input::get('expired');
            $pro_inv->inv_pro_inv_submit_by = $submit_by;
            $pro_inv->inv_pro_inv_submit_at = $submit_at;
            $pro_inv->save();

            $pro_sup_inv = new Inv_supplier_inventory;
            $pro_sup_inv->inv_sup_inv_com_id = $com;
            $pro_sup_inv->inv_sup_inv_sup_id = Input::get('supplier');
            $pro_sup_inv->inv_sup_inv_proinv_id = $pro_inv->inv_pro_inv_id;
            $pro_sup_inv->inv_sup_inv_debit = 0;
            $pro_sup_inv->inv_sup_inv_credit = $total_purchase_price;
            $pro_sup_inv->inv_sup_inv_tran_type = 1;//1=buy product
            $pro_sup_inv->inv_sup_inv_issue_date = Input::get('issue');
            $pro_sup_inv->inv_sup_inv_status = 1;
            $pro_sup_inv->inv_sup_inv_submit_by = $submit_by;
            $pro_sup_inv->inv_sup_inv_submit_at = $submit_at;
            $pro_sup_inv->save();

            // if (Input::get('warrenty') == 1) {
            for ($i=0; $i < Input::get('qty'); $i++) {
                $pro_inv_det = new Inv_product_inventory_detail;
                $pro_inv_det->inv_pro_invdet_com_id = $com;
                $pro_inv_det->inv_pro_invdet_proinv_id = $pro_inv->inv_pro_inv_id;
                $pro_inv_det->inv_pro_invdet_slno = @Input::get('pro_sl_no')[$i];
                $pro_inv_det->inv_pro_invdet_sell_status = 0;
                $pro_inv_det->inv_pro_invdet_status = 1;
                $pro_inv_det->inv_pro_invdet_submit_by = $submit_by;
                $pro_inv_det->inv_pro_invdet_submit_at = $submit_at;
                $pro_inv_det->save();
            }
            // }else {
            //         $pro_inv_det = new Inv_product_inventory_detail;
            //         $pro_inv_det->inv_pro_invdet_com_id = $com;
            //         $pro_inv_det->inv_pro_invdet_proinv_id = Input::get('product');
            //         $pro_inv_det->inv_pro_invdet_slno = Input::get('pro_sl_no');
            //         $pro_inv_det->inv_pro_invdet_sell_status = 0;
            //         $pro_inv_det->inv_pro_invdet_status = 1;
            //         $pro_inv_det->inv_pro_invdet_submit_by = $submit_by;
            //         $pro_inv_det->inv_pro_invdet_submit_at = $submit_at;
            //         $pro_inv_det->save();
            // }
        }else {
            return redirect()->back()->with(['invalid' => 'Invalid Product']);
        }


        return redirect()->back()->with(['pur_pro' => 'Product Purchases Successfully']);
    }

    public function available_list(){
        $com = Auth::user()->au_company_id;
        $available = Inv_product_inventory_detail::where('inv_pro_invdet_com_id',$com)
                                                 ->get();
        return view('inventory.product_inventory.available');
    }

    public function sell_product(Request $request){

        $com = Auth::user()->au_company_id;

        $customers = Inv_customer::where('inv_cus_com_id',$com)
                                  ->where('inv_cus_status',1)
                                  ->get();

        $groups = Inv_product_groups::where('inv_pro_grp_com_id',$com)
                                    ->where('inv_pro_grp_status',1)
                                    ->get();

        if ($request->has('searchbtn')) {
            $sell_pro = Inv_product_detail::where('inv_pro_det_com_id',$com)
                                        ->where('inv_pro_det_type_id',$request->type)
                                        ->where('inv_pro_det_status',1)
                                        ->get();
        }else{
            $sell_pro = Inv_product_detail::where('inv_pro_det_com_id',$com)
                                    ->where('inv_pro_det_status',1)
                                    ->get();
        }

        $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->whereIn('inv_pro_temp_deal_type',[2,5])
            ->orderBy('inv_pro_temp_deal_type','asc')
            ->get();
            $total = 0;
            $amount = 0;
            foreach ($cart_content as $content) {
                $amount = $content->inv_pro_temp_unit_price * $content->inv_pro_temp_qty;
            }
            $total += $amount;


        return view('inventory.product_inventory.sell_product',compact('sell_pro','customers','groups','total'));
    }

    public function show_pro_type_ajax(Request $request) {
        $types = Inv_product_type::where('inv_pro_type_grp_id', $request->grp_id)
                                ->where('inv_pro_type_status',1)
                                ->get();
        return view('pages.ajax.sell_product_model_ajax', compact('types'));
    }

    public function product_search_ajax(Request $request){
        $com = Auth::user()->au_company_id;
        // return request()->all();
        $sell_product = Inv_product_detail::where('inv_pro_det_com_id',$com)
                                          ->where('inv_pro_det_type_id',$request->type_id)
                                          ->where('inv_pro_det_status',1)
                                          ->get();
        return view('pages.ajax.product_search_sell_ajax',compact('sell_product'));

    }


    public function damage_add(){
        $com = Auth::user()->au_company_id;
        $pro_grp = Inv_product_groups::where('inv_pro_grp_com_id',$com)
                                         ->where('inv_pro_grp_status',1)
                                         ->get();
        return view('inventory.damage.add',compact('pro_grp'));
    }

    public function show_pro_name_ajax(Request $request) {
        $types = Inv_product_detail::where('inv_pro_det_type_id', $request->model_id)
                                ->where('inv_pro_det_status',1)
                                ->get();
        return view('pages.ajax.pro_name_ajax', compact('types'));
    }

    public function damage_add_submit(){
            $com = Auth::user()->au_company_id;
            $submit_at = Carbon::now()->format('Y-m-d H:i:s');
            $submit_by = Auth::user()->au_id;
        try {
            $damage = new Inv_product_inventory;
            $damage->inv_pro_inv_com_id = $com;
            $damage->inv_pro_inv_prodet_id = Input::get('product');
            $damage->inv_pro_inv_total_qty = Input::get('short_total');
            $damage->inv_pro_inv_short_qty = 0;
            $damage->inv_pro_inv_qty = Input::get('short_total');
            $damage->inv_pro_inv_deal_type = 2;
            $damage->inv_pro_inv_damage_status = 1;
            $damage->inv_pro_inv_tran_type = 1;
            $damage->inv_pro_inv_submit_at = $submit_at;
            $damage->inv_pro_inv_submit_by = $submit_by;
            $damage->save();

        } catch (\Exception $e) {
            return redirect()->back()->with(['err' => $e->getMessage()]);
        }
        return redirect()->back()->with(['damage' => 'Damage Product Submitted Successfully']);
    }

    public function damage_list(){
        $com = Auth::user()->au_company_id;
        $damage_list = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                         ->where('inv_pro_inv_deal_type',2)
                                         ->where('inv_pro_inv_damage_status',1)
                                         ->where('inv_pro_inv_tran_type',1)
                                         ->get();
        return view('inventory.damage.list',compact('damage_list'));
    }
    // public function buy_currency_new(){
    //     $com = Auth::user()->au_company_id;
    //     $customer = MexCustomer::where('com_id',$com)
    //                             ->where('status',1)
    //                             ->get();
    //     $buy_curr = MexCurrency::all();
    //     // $suppliers = Inv_supplier::where('inv_sup_com_id',$com)
    //     //                          ->where('inv_sup_status',1)
    //     //                          ->orderBy('inv_sup_com_name','ASC')
    //     //                          ->get();
    //     // $sell_pro = Inv_product_detail::where('inv_pro_det_com_id',$com)
    //     //                               ->where('inv_pro_det_status',1)
    //     //                               ->get();
    //     // $customers = Inv_customer::where('inv_cus_com_id',$com)
    //     //                           ->where('inv_cus_status',1)
    //     //                           ->get();
    //     return view('moneyExchange.currency_inventory.add_new',compact('buy_curr','customer'));
    // }

    // public function damage_add(){
    //     $currency = MexCountry::all();
    //     return view('moneyExchange.damage.add',compact('currency'));
    // }
    public function show_note_by_ajax(Request $request) {
        $notes = MexCurrency::where('country_id', $request->country_id)
                                ->get();
        return view('moneyExchange.damage.ajax.currency_note', compact('notes'));
    }

    // public function damage_list(){
    //     return view('moneyExchange.damage.list');
    // }

    public function sell_currency_new(Request $request){

        $com = Auth::user()->au_company_id;

        $customers = MexCustomer::where('com_id',$com)
                                ->where('status',1)
                                ->get();

        $countries = MexCurrencyDetail::with('country')->groupBy('mex_curr_det_country_id')->get();


        $sell_curr = MexCurrencyDetail::with('currency_note')->where('mex_curr_det_com_id',$com)
                                    ->where('mex_curr_det_status',1)
                                    ->get();

    //    dd($sell_curr);


        return view('moneyExchange.currency_inventory.sell_product',compact('sell_curr','customers','countries'));
    }
    public function currency_submit_ajax(Request $request){
        $country = MexCountry::where('id', $request->country_id)->first();
        return view('moneyExchange.currency_inventory.ajax.currency_list_ajax', compact('country'));
    }

    public function currency_search_ajax(Request $request){
        $currencies = MexCurrencyDetail::with('currency_note')->where('mex_curr_det_country_id', $request->id)->get();
        return view('moneyExchange.currency_inventory.ajax.currency_search_sell_ajax',compact('currencies'));

    }
}

<?php

namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inv_customer;
use App\Models\Inv_product_groups;
use App\Models\Inv_product_detail;
use App\Models\Inv_product_inventory;
use App\Models\Inv_product_temporary;
use App\Models\Inv_product_inventory_detail;
use DB;
use Carbon\Carbon;

class ProductSellEditController extends Controller
{


    public function sell_product_edit(Request $request,$id){

        DB::beginTransaction();
        try {
            $user_id = Auth::user()->au_id;
            $com = Auth::user()->au_company_id;

            $customers = Inv_customer::where('inv_cus_com_id',$com)
                                    ->where('inv_cus_status',1)
                                    ->get();

            $groups = Inv_product_groups::where('inv_pro_grp_com_id',$com)
                                        ->where('inv_pro_grp_status',1)
                                        ->get();

            $sell_pro = Inv_product_detail::where('inv_pro_det_com_id',$com)
                ->where('inv_pro_det_status',1)
                ->get();


            $invoice_no = $id;

            $invoice_products = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                            ->where('inv_pro_inv_invoice_no',$id)
                            ->where('inv_pro_inv_status',1)
                            ->where('inv_pro_inv_deal_type', 2)
                            ->whereIn('inv_pro_inv_tran_type', [1,10,11,12])
                            ->get();



            if (count($invoice_products)>0) {

                $sell_customer = $invoice_products->first()->inv_pro_inv_party_id;

                $check_inv_temp = Inv_product_temporary::where('inv_pro_temp_invoice_no', $invoice_no)
                    ->whereIn('inv_pro_temp_deal_type', [4,6,7,8])
                    ->get();

                if(count($check_inv_temp) > 0) {
                  if ($check_inv_temp->first()->inv_pro_temp_user_id != $user_id) {
                      return redirect()->back()->with(['check' => 'Someone is Edit this Invoice']);
                  }
                } else {
                    Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)->delete();
                    foreach ($invoice_products as $invoice_product) {
                        // dd($invoice_product);
                        $pro_sl_array = Inv_product_inventory_detail::select('inv_pro_invdet_slno')
                                                                ->where('inv_pro_invdet_com_id',$com)
                                                                ->where('inv_pro_invdet_proinv_sell_id',$invoice_product->inv_pro_inv_id)
                                                                ->where('inv_pro_invdet_pro_id',$invoice_product->inv_pro_inv_prodet_id)
                                                                ->pluck('inv_pro_invdet_slno')->toArray();
                        // dd($pro_sl_array);
                        if (count($pro_sl_array) > 0) {
                            $pro_sl = implode(',',$pro_sl_array);
                        }else {
                            $pro_sl = '';
                        }
                        $edit_pro_temp = new Inv_product_temporary();

                        $edit_pro_temp->inv_pro_temp_user_id = $user_id;
                        $edit_pro_temp->inv_pro_temp_qty = $invoice_product->inv_pro_inv_qty;
                        $edit_pro_temp->inv_pro_temp_invoice_no = $invoice_product->inv_pro_inv_invoice_no;
                        $edit_pro_temp->inv_pro_temp_unit_price = $invoice_product->inv_pro_inv_unit_price;
                        $edit_pro_temp->inv_pro_temp_slno = $pro_sl;
                        $edit_pro_temp->inv_pro_temp_status = 1;
                        $edit_pro_temp->inv_pro_temp_type = 2;
                        $edit_pro_temp->inv_pro_temp_created_at = Carbon::now();
                        if ($invoice_product->inv_pro_inv_tran_type == 1) {
                            $edit_pro_temp->inv_pro_temp_deal_type = 4;
                            $edit_pro_temp->inv_pro_temp_pro_id = $invoice_product->inv_pro_inv_prodet_id;
                            $edit_pro_temp->inv_pro_temp_pro_name = $invoice_product->getProductInfo['inv_pro_name'];
                        $edit_pro_temp->inv_pro_temp_type_name = $invoice_product->getProductInfo->type_info['inv_pro_type_name'];
                        } elseif ($invoice_product->inv_pro_inv_tran_type == 10) {
                            $edit_pro_temp->inv_pro_temp_deal_type = 6;
                            $edit_pro_temp->inv_pro_temp_pro_id = '';
                            $edit_pro_temp->inv_pro_temp_pro_name = "Sell Service Charge";
                            $edit_pro_temp->inv_pro_temp_type_name = "Sell Service Charge";
                        } elseif ($invoice_product->inv_pro_inv_tran_type == 11) {
                            $edit_pro_temp->inv_pro_temp_deal_type = 7;
                            $edit_pro_temp->inv_pro_temp_pro_id = '';
                            $edit_pro_temp->inv_pro_temp_pro_name = "Sell Delivery Charge";
                            $edit_pro_temp->inv_pro_temp_type_name = "Sell Delivery Charge";
                        } elseif ($invoice_product->inv_pro_inv_tran_type == 12) {
                            $edit_pro_temp->inv_pro_temp_deal_type = 8;
                            $edit_pro_temp->inv_pro_temp_pro_id = '';
                            $edit_pro_temp->inv_pro_temp_pro_name = "Sell Discount";
                            $edit_pro_temp->inv_pro_temp_type_name = "Sell Discount";
                        }
                        $edit_pro_temp->save();
                    }
                }
            } else {
                // dd("as");
                return redirect()->back()->with(['sub_err', 'Invalid Inventory']);
            }


        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['sub_err' => 'Something went wrong'.$e->getMessage()]);
        }

        DB::commit();
        return view('inventory.product_return.sell_product_edit',compact('customers','groups','sell_pro', 'invoice_no', 'sell_customer'));
    }

    public function addToCart(Request $request){
        $user_id = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
        $product = Inv_product_detail::where('inv_pro_det_id', $request->pro_id)
            ->where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_status', 1)
            ->first();

        if(!empty($product)) {

            if($product->inv_pro_det_available_qty < $request->pro_qty) {
                return response()->json(['status' => 400]);
            }

            $row = Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)
                ->where('inv_pro_temp_pro_id', $request->pro_id)
                ->where('inv_pro_temp_deal_type',4)
                ->first();
            if(!empty($row)) {
                $row->inv_pro_temp_pro_name = $product->inv_pro_name;
                $row->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);
                $row->inv_pro_temp_qty = $request->pro_qty;
                $row->inv_pro_temp_unit_price = $request->pro_price;
                $row->inv_pro_temp_exp_date = $request->exp_date;
                $row->inv_pro_temp_updated_at = Carbon::now();

                $row->save();

            } else {
                $pro_temp_add = new Inv_product_temporary;
                $pro_temp_add->inv_pro_temp_user_id = $user_id;
                $pro_temp_add->inv_pro_temp_pro_id = $product->inv_pro_det_id;
                $pro_temp_add->inv_pro_temp_pro_name = $product->inv_pro_name;
                $pro_temp_add->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);
                $pro_temp_add->inv_pro_temp_qty = $request->pro_qty;
                $pro_temp_add->inv_pro_temp_unit_price = $request->pro_price;
                $pro_temp_add->inv_pro_temp_exp_date = $request->exp_date;
                $pro_temp_add->inv_pro_temp_invoice_no = $request->invoice_no;
                $pro_temp_add->inv_pro_temp_slno = '';
                $pro_temp_add->inv_pro_temp_deal_type = '4';//1=purchase,2=sale,3=purchase-edit,4=sell-edit
                $pro_temp_add->inv_pro_temp_status = 1;

                $pro_temp_add->inv_pro_temp_created_at = Carbon::now();

                $pro_temp_add->save();
            }

        } else {
            return response()->json(['status' => 404]);
        }
    }

    public function addServiceCharges(Request $request)
    {
            $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', '')
                ->where('inv_pro_temp_deal_type',6)
                ->first();
            if(!empty($row)) {
                $row->inv_pro_temp_pro_name = 'Sell Service Charge';
                $row->inv_pro_temp_type_name = 'Sell Service Charge';
                $row->inv_pro_temp_qty = $request->qty;
                $row->inv_pro_temp_unit_price = $request->service;
                $row->inv_pro_temp_updated_at = Carbon::now();

                $row->save();

            } else {
                $pro_temp_add = new Inv_product_temporary;
                $pro_temp_add->inv_pro_temp_user_id = Auth::user()->au_id;
                $pro_temp_add->inv_pro_temp_pro_id = '';
                $pro_temp_add->inv_pro_temp_pro_name = 'Sell Service Charge';
                $pro_temp_add->inv_pro_temp_type_name = 'Sell Service Charge';
                $pro_temp_add->inv_pro_temp_qty = $request->qty;
                $pro_temp_add->inv_pro_temp_unit_price = $request->service;
                $pro_temp_add->inv_pro_temp_slno = '';
                $pro_temp_add->inv_pro_temp_deal_type = '6';//6=Sell Edit Service Charge
                $pro_temp_add->inv_pro_temp_status = 1;
                $pro_temp_add->inv_pro_temp_created_at = Carbon::now();

                $pro_temp_add->save();
            }


    }


    public function addToCartWarrentyProduct(Request $request)
    {

        $product = Inv_product_detail::where('inv_pro_det_id', $request->pro_id)
        ->where('inv_pro_det_com_id', Auth::user()->au_company_id)
        ->where('inv_pro_det_status', 1)
        ->where('inv_pro_det_pro_warranty', '!=', 0)
        ->first();

        $product_exist_slno = Inv_product_inventory_detail::where('inv_pro_invdet_com_id',Auth::user()->au_company_id)
                                                           ->where('inv_pro_invdet_sell_id',NULL)
                                                           ->where('inv_pro_invdet_pro_id',$product->inv_pro_det_id)
                                                           ->where('inv_pro_invdet_sell_status',0)
                                                           ->get();

        $product_sl_no = array();
        if(!empty($product)) {

            $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', $request->pro_id)
                ->where('inv_pro_temp_deal_type',4)
                ->first();
            if(!empty($row)) {
                $row->inv_pro_temp_pro_name = $product->inv_pro_name;
                $row->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);

                $row->inv_pro_temp_unit_price = $request->pro_price;
                $row->inv_pro_temp_exp_date = $request->exp_date;
                $row->inv_pro_temp_updated_at = Carbon::now();

                $row->save();
                if($row->inv_pro_temp_slno != '') {
                    $product_sl_no = explode(',', $row->inv_pro_temp_slno);
                }

            } else {
                $pro_temp_add = new Inv_product_temporary;
                $pro_temp_add->inv_pro_temp_user_id = Auth::user()->au_id;
                $pro_temp_add->inv_pro_temp_pro_id = $product->inv_pro_det_id;
                $pro_temp_add->inv_pro_temp_pro_name = $product->inv_pro_name;
                $pro_temp_add->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);
                $pro_temp_add->inv_pro_temp_qty = 0;

                $pro_temp_add->inv_pro_temp_unit_price = $request->pro_price;
                $pro_temp_add->inv_pro_temp_exp_date = $request->exp_date;
                $pro_temp_add->inv_pro_temp_invoice_no = $request->invoice_no;
                $pro_temp_add->inv_pro_temp_slno = '';
                $pro_temp_add->inv_pro_temp_deal_type = '4';//1=purchase,2=sale
                $pro_temp_add->inv_pro_temp_status = 1;
                $pro_temp_add->inv_pro_temp_type = 2; //1=non-warranty , 2= warranty
                $pro_temp_add->inv_pro_temp_updated_at = Carbon::now();
                $pro_temp_add->save();
            }

        } else {
            return response()->json(['status' => 404]);
        }

        return view('pages.ajax.warrenty_product_get_sl_no_inner_form', compact('product', 'product_sl_no','product_exist_slno'));
    }

    public function addWarrentyProductSlNo(Request $request)
    {
        $com = Auth::user()->au_company_id;

        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_pro_id', $request->pro_id)
            ->where('inv_pro_temp_deal_type',4)
            ->first();
        if(($request->sl_no == null) || ($request->sl_no == '')) {
            return false;
        }
        if(!empty($row)) {
            if($row->inv_pro_temp_slno == '') {
                $pre_sl_no = array();
            } else {
                $pre_sl_no = explode(',',$row->inv_pro_temp_slno);
            }
            if(in_array($request->sl_no, $pre_sl_no)) {
                return response()->json(['status' => 402]);//already added
            }

            // all inventory id of requested product
            $all_inv_ids_of_this_product = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                            ->where('inv_pro_inv_prodet_id', $request->pro_id)
                            ->where('inv_pro_inv_deal_type', 1)
                            ->where('inv_pro_inv_tran_type', 1)
                            ->pluck('inv_pro_inv_id')
                            ->toArray();//deal_type=1=supplier, tran_type=1=buy-sale

            // search this sl no products
            $pro_inv_det = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', $com)
                ->where('inv_pro_invdet_slno', '!=', null)
                ->where('inv_pro_invdet_sell_status', 0)
                ->whereIn('inv_pro_invdet_proinv_id', $all_inv_ids_of_this_product)
                ->where('inv_pro_invdet_slno', $request->sl_no)
                ->first();

            if(empty($pro_inv_det)) {
                return response()->json(['status' => 404]); //sl no not found
            }
            $pro_all_sl_no = $pre_sl_no;
            $pro_all_sl_no[] = $request->sl_no;
            $content_quantity = count($pro_all_sl_no);

            $row->inv_pro_temp_slno = implode(',', $pro_all_sl_no);
            $row->inv_pro_temp_qty = $content_quantity;
            $row->inv_pro_temp_updated_at = Carbon::now();
            $row->save();

            $content = $row;

            return view("pages.ajax.warrenty_product_sl_no_list", compact('content'));
        } else {
            return response()->json(['status' => 406]); //sl no not found
        }
    }

    public function removeWarrentyProductSlNo(Request $request)
    {
        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', $request->pro_id)
                ->where('inv_pro_temp_deal_type',4)
                ->first();
        if(!empty($row)) {
            $pro_all_sl_no = explode(',',$row->inv_pro_temp_slno);

            if (($key = array_search($request->sl_no, $pro_all_sl_no)) !== false) {
                unset($pro_all_sl_no[$key]);
            }
            $content_quantity = count($pro_all_sl_no);

            $row->inv_pro_temp_slno = implode(',', $pro_all_sl_no);
            $row->inv_pro_temp_qty = $content_quantity;
            $row->inv_pro_temp_updated_at = Carbon::now();
            $row->save();

            $content = $row;

            return view("pages.ajax.warrenty_product_sl_no_list", compact('content'));
        } else {
            return response()->json(['status' => 406]); //sl no not found
        }
    }

    public function removecart(Request $request){
        Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', $request->content_id)
                ->where('inv_pro_temp_deal_type',4)
                ->delete();
    }

    public function updatecart(Request $request){
        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', $request->content_id)
                ->where('inv_pro_temp_deal_type',4)
                ->get();
        $row->inv_pro_temp_qty = $request->pro_qty;
        $row->inv_pro_temp_updated_at = Carbon::now();
        $row->save();
    }


    public function getCartContent()
    {

        $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
        ->whereIn('inv_pro_temp_deal_type', [4,6,7,8])
        ->orderBy('inv_pro_temp_deal_type', 'ASC')
        ->get();


        return view('pages.ajax.sell_edit.get_cart_content', compact('cart_content'));
    }

    public function invTemporaryProduct(Request $request){
        $user = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
        $pro_temps = Inv_product_temporary::where('inv_pro_temp_user_id', $user)
                                            ->where('inv_pro_temp_deal_type',4)
                                            ->get();
        $pro_cus = Inv_customer::where('inv_cus_com_id', $com)
            ->where('inv_cus_id', $request->customer)
            ->first();
        return view('inventory.product_inventory.product_sell_invoice',compact('pro_temps','pro_cus'));
    }


    public function sell_edit_again(Request $request,$invoice){
              $com = Auth::user()->au_company_id;
        $invoice_no = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                        ->where('inv_pro_inv_invoice_no',$invoice)
                                        ->first();
        return view('inventory.product_inventory.sell_product_edit_again',compact('invoice_no'));
    }

    public function sellEditConfirmFormShow(Request $request)
    {
        $user = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
        $pro_temps = Inv_product_temporary::where('inv_pro_temp_user_id', $user)
                                            ->whereIn('inv_pro_temp_deal_type',[4,6])
                                            ->get();
        $delivery = $request->delivery;
        $discount = $request->discount;
        $pro_cus = Inv_customer::where('inv_cus_com_id', $com)
            ->where('inv_cus_id', $request->customer)
            ->first();
        return view('inventory.product_return.product_sell_edit_invoice',compact('pro_temps','pro_cus', 'delivery', 'discount'));
    }


    // Submit cart form for sale
    public function cartSubmit(Request $request){

        $request->validate([
            'customer' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $com = Auth::user()->au_company_id;
            $user_id = Auth::user()->au_id;
            $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_deal_type',4)
                ->get();

            if(count($cart_content) <= 0) {
                return redirect()->back()->with(['sub_err' => 'Empty Cart']);
            }

            $new_memo_no = $cart_content->first()->inv_pro_temp_invoice_no;

            // check and update previous product available quantity, inventory //
            $pre_sell_products = Inv_product_inventory::where('inv_pro_inv_invoice_no', $new_memo_no)
                ->where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_deal_type', 2)
                ->where('inv_pro_inv_tran_type', 1)
                ->get();

            $pre_inv_edit_count = $pre_sell_products->first()->inv_pro_inv_edit_count;

            $pre_sell_product_invID = array();

            foreach ($pre_sell_products as $pre_sell_product) {
                $pre_sell_product_invID[] = $pre_sell_product->inv_pro_inv_id;
                $pre_pro_det = Inv_product_detail::where('inv_pro_det_com_id', $com)
                    ->where('inv_pro_det_id', $pre_sell_product->inv_pro_inv_prodet_id)
                    ->first();
                if (!empty($pre_pro_det)) {
                    $pre_pro_det->inv_pro_det_available_qty = $pre_pro_det->inv_pro_det_available_qty + $pre_sell_product->inv_pro_inv_qty;
                    $pre_pro_det->save();
                }
            }

            Inv_product_inventory::where('inv_pro_inv_invoice_no', $new_memo_no)
                ->where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_deal_type', 2)
                ->whereIn('inv_pro_inv_tran_type', [1,10,11,12])
                ->delete();

            Inv_product_inventory_detail::where('inv_pro_invdet_com_id',$com)
                ->whereIn('inv_pro_invdet_proinv_sell_id',$pre_sell_product_invID)
                ->update(['inv_pro_invdet_proinv_sell_id' => NULL,'inv_pro_invdet_sell_id' => NULL, 'inv_pro_invdet_sell_status' => 0]);

            foreach ($cart_content as $content) {
                $product_id = $content->inv_pro_temp_pro_id;
                $req_qty = $content->inv_pro_temp_qty;

                $check_product = Inv_product_detail::where('inv_pro_det_id', $product_id)
                    ->where('inv_pro_det_com_id', $com)
                    ->where('inv_pro_det_available_qty', '>=', $req_qty)
                    ->where('inv_pro_det_status', 1)
                    ->first();

                if(empty($check_product)) {
                    DB::rollback();
                    $msg = "Invalid/Insufficient ". $content->inv_pro_temp_pro_name ." Product. error-code: 1006";
                    return redirect()->back()->with(['sub_err' => $msg]);
                }
            }

            // insert in product inventory table
            $total_purchase_amount = 0;

            foreach ($cart_content as $content) {
                $product_id = $content->inv_pro_temp_pro_id;
                $req_qty = $content->inv_pro_temp_qty;
                $product_price = $content->inv_pro_temp_unit_price;
                $sub_total = $req_qty * $product_price;
                $total_purchase_amount += $sub_total;

                $check_product = Inv_product_detail::where('inv_pro_det_id', $product_id)
                    ->where('inv_pro_det_com_id', $com)
                    ->where('inv_pro_det_status', 1)
                    ->first();

                if($check_product->inv_pro_det_pro_warranty == 0) {
                    $req_qty = $content->inv_pro_temp_qty;
                }  else {
                    $new_req_sl_ids = array();
                    $req_sl_ids = explode(',', $content->inv_pro_temp_slno);
                    $k = 0;
                    foreach ($req_sl_ids as $sl_id) {
                        if($sl_id == '') {
                            unset($req_sl_ids[$k]);
                        } else {
                            $new_req_sl_ids[] = $req_sl_ids[$k];
                        }
                        $k++;
                    }

                    if (!isset($new_req_sl_ids)) {
                        $new_req_sl_ids = array();
                    }

                    $req_sl_ids = $new_req_sl_ids;
                    $req_qty = count($req_sl_ids);
                }



                $product_inventory = new Inv_product_inventory();
                $product_inventory->inv_pro_inv_com_id = $com;
                $product_inventory->inv_pro_inv_prodet_id = $content->inv_pro_temp_pro_id;
                $product_inventory->inv_pro_inv_party_id = $request->customer;
                $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                $product_inventory->inv_pro_inv_qty = $req_qty;
                $product_inventory->inv_pro_inv_unit_price = $product_price;
                $product_inventory->inv_pro_inv_debit = $sub_total;
                $product_inventory->inv_pro_inv_credit = 0;
                $product_inventory->inv_pro_inv_issue_date = Carbon::now();
                $product_inventory->inv_pro_inv_tran_desc = "Sell Product";
                $product_inventory->inv_pro_inv_deal_type =  2;//2=customer
                $product_inventory->inv_pro_inv_tran_type =  1;//1=buy/sell product-buy
                $product_inventory->inv_pro_inv_edit_count = $pre_inv_edit_count + 1;
                $product_inventory->inv_pro_inv_status = 1;
                $product_inventory->inv_pro_inv_submit_by = $user_id;
                $product_inventory->inv_pro_inv_submit_at = Carbon::now();

                $product_inventory->save();

                for ($i=0; $i < $req_qty; $i++) {

                    if($check_product->inv_pro_det_pro_warranty == 0) {

                    } else {


                        $request_sl_no = $req_sl_ids[$i];
                        if($request_sl_no == '') {
                            DB::rollback();
                            $msg = "Something went wrong to sell product. product name: ". $content->inv_pro_temp_pro_name .". error-code: 1003";
                            return redirect()->back()->with(['sub_err' => $msg]);
                        }
                        //dd(Inv_product_inventory_detail::all());
                        // search and update previous product details sales_status
                        $all_inv_ids_of_this_product = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                            ->where('inv_pro_inv_prodet_id', $check_product->inv_pro_det_id)
                            ->where('inv_pro_inv_deal_type', 1)
                            ->where('inv_pro_inv_tran_type', 1)
                            ->pluck('inv_pro_inv_id')
                            ->toArray();
                        //dump($all_inv_ids_of_this_product);
                        $pre_pro_inv_details = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', $com)
                            ->whereIn('inv_pro_invdet_proinv_id', $all_inv_ids_of_this_product)
                            ->where('inv_pro_invdet_sell_status', 0)
                            ->where('inv_pro_invdet_slno', $request_sl_no)
                            ->first();
                            //dd($pre_pro_inv_details);

                        if(!empty($pre_pro_inv_details)) {
                            $pre_pro_inv_details->inv_pro_invdet_sell_status = 1;
                            $pre_pro_inv_details->inv_pro_invdet_proinv_sell_id = $product_inventory->inv_pro_inv_id;
                            $pre_pro_inv_details->inv_pro_invdet_sell_id = $request->customer;

                            $pre_pro_inv_details->save();
                        } else {
                            DB::rollback();
                            $msg = "Something went wrong to sell product. product name: ". $content->name .". error-code: 1002";
                            return redirect()->back()->with(['sub_err' => $msg]);
                        }

                    }
                }


                // available quantity update in product detail table
                $after_submit_pro_available_quantity = $check_product->inv_pro_det_available_qty - $req_qty;
                $check_product->inv_pro_det_available_qty = $after_submit_pro_available_quantity;

                $check_product->save();


            }


            $service_charge_cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type',6)
            ->first();
            if (!empty($service_charge_cart_content) > 0) {
                $product_inventory = new Inv_product_inventory();
                $debit = $service_charge_cart_content->inv_pro_temp_unit_price * $service_charge_cart_content->inv_pro_temp_qty;
                $product_inventory->inv_pro_inv_com_id = $com;
                $product_inventory->inv_pro_inv_prodet_id = NULL;
                $product_inventory->inv_pro_inv_party_id = $request->customer;
                $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                $product_inventory->inv_pro_inv_qty = $service_charge_cart_content->inv_pro_temp_qty;
                $product_inventory->inv_pro_inv_unit_price = $service_charge_cart_content->inv_pro_temp_unit_price;
                $product_inventory->inv_pro_inv_debit = $debit;
                $product_inventory->inv_pro_inv_credit = 0;
                $product_inventory->inv_pro_inv_issue_date = Carbon::now();
                $product_inventory->inv_pro_inv_tran_desc = "Setup/Service/Install Charges";
                $product_inventory->inv_pro_inv_deal_type =  2;//2=customer
                $product_inventory->inv_pro_inv_tran_type =  10;//10=serviceCharges,11=deliveryCharges
                $product_inventory->inv_pro_inv_status = 1;
                $product_inventory->inv_pro_inv_submit_by = $user_id;
                $product_inventory->inv_pro_inv_submit_at = Carbon::now();

                $product_inventory->save();
            }
            if ($request->delivery > 0) {
                $product_inventory = new Inv_product_inventory();
                $product_inventory->inv_pro_inv_com_id = $com;
                $product_inventory->inv_pro_inv_prodet_id = NULL;
                $product_inventory->inv_pro_inv_party_id = $request->customer;
                $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                $product_inventory->inv_pro_inv_qty = 0;
                $product_inventory->inv_pro_inv_unit_price = $request->delivery;
                $product_inventory->inv_pro_inv_debit = $request->delivery;
                $product_inventory->inv_pro_inv_credit = 0;
                $product_inventory->inv_pro_inv_issue_date = Carbon::now();
                $product_inventory->inv_pro_inv_tran_desc = "Sell Product Delivery Charge";
                $product_inventory->inv_pro_inv_deal_type =  2;//2=customer
                $product_inventory->inv_pro_inv_tran_type =  11;//10=serviceCharges,11=deliveryCharges
                $product_inventory->inv_pro_inv_status = 1;
                $product_inventory->inv_pro_inv_submit_by = $user_id;
                $product_inventory->inv_pro_inv_submit_at = Carbon::now();

                $product_inventory->save();
            }

            if ($request->discount > 0) {
                $product_inventory = new Inv_product_inventory();
                $product_inventory->inv_pro_inv_com_id = $com;
                $product_inventory->inv_pro_inv_prodet_id = NULL;
                $product_inventory->inv_pro_inv_party_id = $request->customer;
                $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                $product_inventory->inv_pro_inv_qty = 0;
                $product_inventory->inv_pro_inv_unit_price = $request->discount;
                $product_inventory->inv_pro_inv_debit = 0;
                $product_inventory->inv_pro_inv_credit = $request->discount;
                $product_inventory->inv_pro_inv_issue_date = Carbon::now();
                $product_inventory->inv_pro_inv_tran_desc = "Sell Product Discount";
                $product_inventory->inv_pro_inv_deal_type =  2;//2=customer
                $product_inventory->inv_pro_inv_tran_type =  12;//10=serviceCharges,11=deliveryCharges,12-discount
                $product_inventory->inv_pro_inv_status = 1;
                $product_inventory->inv_pro_inv_submit_by = $user_id;
                $product_inventory->inv_pro_inv_submit_at = Carbon::now();

                $product_inventory->save();
            }


            Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->whereIn('inv_pro_temp_deal_type',[4,6,7,8])
                ->delete();

        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Something went wrong to sell product. error-code: 1010".$e->getMessage();
            return redirect()->back()->with(['sub_err' => $msg]);
        }

        DB::commit();
        $msg = "Sell Products Successfully completed";
        return redirect()->route('buy.pro_sell')->with(['sub_success' => $msg,'print_invoice' => $new_memo_no]);
    }
}

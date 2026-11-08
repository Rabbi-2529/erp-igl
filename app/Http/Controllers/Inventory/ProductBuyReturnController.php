<?php

namespace App\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Auth;
use App\Models\Inv_product_detail;
use App\Models\Inv_product_temporary;
use App\Models\Inv_product_inventory;
use App\Models\Inv_supplier;

use Carbon\Carbon;

class ProductBuyReturnController extends Controller
{
    public function addToCart(Request $request){
        $user_id = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
        $inventory = Inv_product_inventory::where('inv_pro_inv_id', $request->inv_id)
            ->where('inv_pro_inv_com_id', $com)
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_tran_type', 1)
            ->first();

        if(!empty($inventory)) {
            $return_available_qty = Inv_product_inventory::product_buy_return_available_qty($inventory->inv_pro_inv_id);

            if($return_available_qty < $request->pro_qty) {
                return response()->json(['status' => 400]);
            }

            $product = $inventory->getProductInfo;


            $row = Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)
                ->where('inv_pro_temp_pro_id', $product->inv_pro_det_id)
                ->where('inv_pro_temp_deal_type',5)
                ->first();


            if(!empty($row)) {
                $row->inv_pro_temp_pro_name = $product->inv_pro_name;
                $row->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);
                $row->inv_pro_temp_qty = $request->pro_qty;
                $row->inv_pro_temp_unit_price = $inventory->inv_pro_inv_unit_price;
                $row->inv_pro_temp_exp_date = '';
                $row->inv_pro_temp_updated_at = Carbon::now();

                $row->save();

            } else {
                $pro_temp_add = new Inv_product_temporary;
                $pro_temp_add->inv_pro_temp_user_id = $user_id;
                $pro_temp_add->inv_pro_temp_pro_id = $product->inv_pro_det_id;
                $pro_temp_add->inv_pro_temp_pro_name = $product->inv_pro_name;
                $pro_temp_add->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);
                $pro_temp_add->inv_pro_temp_qty = $request->pro_qty;
                $pro_temp_add->inv_pro_temp_unit_price = $inventory->inv_pro_inv_unit_price;
                $pro_temp_add->inv_pro_temp_exp_date = '';
                $pro_temp_add->inv_pro_temp_invoice_no = $inventory->inv_pro_inv_invoice_no;
                $pro_temp_add->inv_pro_temp_slno = '';
                $pro_temp_add->inv_pro_temp_deal_type = 5;//1=purchase,2=sale,3=purchase-edit,4=sell-edit,5=purchase-return,6=sale-return
                $pro_temp_add->inv_pro_temp_status = 1;
                $pro_temp_add->inv_pro_temp_created_at = Carbon::now();

                $pro_temp_add->save();
            }

        } else {
            return response()->json(['status' => 404]);
        }
    }


    public function addToCartWarrentyProduct(Request $request)
    {

        $product = Inv_product_detail::where('inv_pro_det_id', $request->pro_id)
        ->where('inv_pro_det_com_id', Auth::user()->au_company_id)
        ->where('inv_pro_det_status', 1)
        ->where('inv_pro_det_pro_warranty', '!=', 0)
        ->first();

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
                $pro_temp_add->inv_pro_temp_updated_at = Carbon::now();
                $pro_temp_add->save();
            }

        } else {
            return response()->json(['status' => 404]);
        }

        return view('pages.ajax.warrenty_product_get_sl_no_inner_form', compact('product', 'product_sl_no'));
    }

    public function addWarrentyProductSlNo(Request $request)
    {
        $com = Auth::user()->au_company_id;

        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_pro_id', $request->pro_id)
            ->where('inv_pro_temp_deal_type',2)
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
                ->where('inv_pro_temp_deal_type',2)
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
                ->where('inv_pro_temp_deal_type',5)
                ->delete();
    }


    public function getCartContent()
    {

        $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type',5)
            ->get();


        return view('pages.ajax.return.buy_return_cart_content', compact('cart_content'));
    }

    public function invTemporaryProduct(Request $request){
        $user = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
        $pro_temps = Inv_product_temporary::where('inv_pro_temp_user_id', $user)
                                            ->where('inv_pro_temp_deal_type',5)
                                            ->get();

        if(count($pro_temps) < 1) {
            return redirect()->back();
        }

        $inventory = Inv_product_inventory::where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_tran_type', 1)
            ->where('inv_pro_inv_invoice_no', $pro_temps->first()->inv_pro_temp_invoice_no)
            ->first();

        $pro_sup = Inv_supplier::where('inv_sup_com_id', $com)
            ->where('inv_sup_id', $inventory->inv_pro_inv_party_id)
            ->first();
        return view('inventory.product_return.product_buy_return_invoice',compact('pro_temps','pro_sup'));
    }


    // Submit cart form for sale
    public function cartSubmit(Request $request){

        DB::beginTransaction();
        try {
            $com = Auth::user()->au_company_id;
            $user_id = Auth::user()->au_id;
            $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_deal_type',5)
                ->get();

            if(count($cart_content) <= 0) {
                return redirect()->back()->with(['sub_err' => 'Empty Cart']);
            }

            $last_pro_inv = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 2)
                ->orderBy('inv_pro_inv_id', 'DESC')
                ->first();
            if(!empty($last_pro_inv)) {
                $last_pro_inv_memo_no = $last_pro_inv->inv_pro_inv_invoice_no;

                $last_data = substr($last_pro_inv_memo_no, 15);
                if(is_numeric($last_data)) {
                    $last_number = $last_data + 1;
                    $last_number_length = strlen($last_number);

                    if ($last_number_length < 6) {
                        $less_number = 6-$last_number_length;
                        $sl_prefix = "";
                        for ($x=0; $x <$less_number ; $x++) {
                            $sl_prefix = $sl_prefix . "0";
                        }
                        $last_number = $sl_prefix . $last_number;
                    }

                    $new_memo_no = "INVPR".$com.date('Y').($last_number);
                } else {
                    $new_memo_no = "INVPR".$com.date('Y')."000001";
                }
            } else {
                $new_memo_no = "INVPR".$com.date('Y')."000001";
            }

            // check product inventory
            foreach ($cart_content as $content) {
                $product_id = $content->inv_pro_temp_pro_id;
                $req_qty = $content->inv_pro_temp_qty;

                $check_product = Inv_product_inventory::where('inv_pro_inv_prodet_id', $product_id)
                    ->where('inv_pro_inv_com_id', $com)
                    ->where('inv_pro_inv_invoice_no', $content->inv_pro_temp_invoice_no)
                    ->where('inv_pro_inv_deal_type', 1)
                    ->where('inv_pro_inv_tran_type', 1)
                    ->first();

                if(empty($check_product)) {
                    DB::rollback();
                    $msg = "Invalid/Insufficient ". $content->inv_pro_temp_pro_name ." Product. error-code: 1006";
                    return redirect()->back()->with(['sub_err' => $msg]);
                }
                $available_return_qty = Inv_product_inventory::product_buy_return_available_qty($check_product->inv_pro_inv_id);
                if($available_return_qty < $req_qty) {
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

                $pre_inventory = Inv_product_inventory::where('inv_pro_inv_prodet_id', $product_id)
                    ->where('inv_pro_inv_com_id', $com)
                    ->where('inv_pro_inv_invoice_no', $content->inv_pro_temp_invoice_no)
                    ->where('inv_pro_inv_deal_type', 1)
                    ->where('inv_pro_inv_tran_type', 1)
                    ->first();

                $product_inventory = new Inv_product_inventory();
                $product_inventory->inv_pro_inv_com_id = $com;
                $product_inventory->inv_pro_inv_prodet_id = $content->inv_pro_temp_pro_id;
                $product_inventory->inv_pro_inv_party_id = $pre_inventory->inv_pro_inv_party_id;
                $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                $product_inventory->inv_pro_inv_qty = $req_qty;
                $product_inventory->inv_pro_inv_unit_price = $product_price;
                $product_inventory->inv_pro_inv_debit = 0;
                $product_inventory->inv_pro_inv_credit = $sub_total;
                $product_inventory->inv_pro_inv_issue_date = Carbon::now();
                $product_inventory->inv_pro_inv_tran_desc = "Purchase Return Product";
                $product_inventory->inv_pro_inv_deal_type =  1;//1=supplier
                $product_inventory->inv_pro_inv_tran_type =  2;//2=buy/sell return product
                $product_inventory->inv_pro_inv_sell_ref = $pre_inventory->inv_pro_inv_id;
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

                        // search and update previous product details sales_status
                        $all_inv_ids_of_this_product = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                            ->where('inv_pro_inv_prodet_id', $check_product->inv_pro_det_id)
                            ->where('inv_pro_inv_deal_type', 1)
                            ->where('inv_pro_inv_tran_type', 1)
                            ->pluck('inv_pro_inv_id')
                            ->toArray();

                        $pre_pro_inv_details = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', $com)
                            ->whereIn('inv_pro_invdet_proinv_id', $all_inv_ids_of_this_product)
                            ->where('inv_pro_invdet_sell_status', 0)
                            ->where('inv_pro_invdet_slno', $request_sl_no)
                            ->first();

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


            Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_deal_type',5)
                ->delete();


        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Something went wrong to buy return product. error-code: 1010".$e->getMessage();
            return redirect()->back()->with(['sub_err' => $msg]);
        }

        DB::commit();
        $msg = "Buy Return Products Successfully completed";
        return redirect()->route('product_return.buy-return-view')->with(['sub_success' => $msg,'print_invoice' => $new_memo_no]);
    }

}

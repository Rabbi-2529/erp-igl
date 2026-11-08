<?php

namespace App\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Carbon\Carbon;
use App\Models\Inv_supplier;
use App\Models\Inv_product_detail;
use App\Models\Inv_product_inventory;
use App\Models\Inv_product_temporary;
use App\Models\Inv_product_inventory_detail;

class ProductBuyEditController extends Controller
{
    public function buy_product_edit(Request $request,$id){
        DB::beginTransaction();
        try {
            $user_id = Auth::user()->au_id;
            $com = Auth::user()->au_company_id;
            $branch = Auth::user()->au_branch_id;
            $suppliers = Inv_supplier::where('inv_sup_com_id',$com)
                                    ->where('inv_sup_status',1)
                                    ->get();
            $sell_pro = Inv_product_detail::with(['product', 'type_info'])
            ->where('inv_pro_det_com_id',$com)
            ->where('inv_pro_det_branch_id',$branch)
            ->where('inv_pro_det_status',1)
            ->get();


            $invoice_no = $id;

            $invoice_products = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                            ->where('inv_pro_inv_branch_id',$branch)
                            ->where('inv_pro_inv_invoice_no',$id)
                            ->where('inv_pro_inv_status',1)
                            ->where('inv_pro_inv_deal_type', 1)
                            ->where('inv_pro_inv_tran_type', 1)
                            ->get();


            if (count($invoice_products)>0) {
                $issue_date = Carbon::parse($invoice_products->first()->inv_pro_inv_issue_date)->format('Y-m-d');

                $buy_supplier = $invoice_products->first()->inv_pro_inv_party_id;

                // Coming back from the confirm page (?back=1) keeps the cart being edited,
                // otherwise the cart is reloaded from the saved invoice.
                $check_inv_temp = Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)
                    ->where('inv_pro_temp_invoice_no', $invoice_no)
                    ->where('inv_pro_temp_deal_type', 3)
                    ->count();
                if($request->has('back') && $check_inv_temp > 0) {

                } else {
                    // only clear this user's purchase-edit cart, not their purchase/sale carts
                    Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)
                        ->where('inv_pro_temp_deal_type', 3)
                        ->delete();
                    foreach ($invoice_products as $invoice_product) {
                        $pro_det = Inv_product_detail::with('product')->find($invoice_product->inv_pro_inv_project_id);
                        $pro_sl_array = Inv_product_inventory_detail::where('inv_pro_invdet_com_id',$com)
                                                                ->where('inv_pro_invdet_proinv_id',$invoice_product->inv_pro_inv_id)
                                                                ->whereNotNull('inv_pro_invdet_slno')
                                                                ->where('inv_pro_invdet_slno', '!=', '')
                                                                ->pluck('inv_pro_invdet_slno')->toArray();
                        $pro_sl = implode(',',$pro_sl_array);
                        $is_warranty = $pro_det && $pro_det->inv_pro_det_pro_warranty != 0;

                        $edit_pro_temp = new Inv_product_temporary();

                        $edit_pro_temp->inv_pro_temp_user_id = $user_id;
                        $edit_pro_temp->inv_pro_temp_pro_id = $invoice_product->inv_pro_inv_project_id;
                        $edit_pro_temp->inv_pro_temp_pro_name = $pro_det->product->inv_pro_name ?? $invoice_product->inv_pro_inv_project_id;
                        $edit_pro_temp->inv_pro_temp_type_name = Inv_product_detail::get_type_name($invoice_product->inv_pro_inv_project_id);
                        $edit_pro_temp->inv_pro_temp_qty = $invoice_product->inv_pro_inv_total_qty ?: $invoice_product->inv_pro_inv_qty;
                        $edit_pro_temp->inv_pro_temp_short_qty = $invoice_product->inv_pro_inv_short_qty ?? 0;
                        $edit_pro_temp->inv_pro_temp_short_remarks = $invoice_product->inv_pro_inv_short_remarks;
                        $edit_pro_temp->inv_pro_temp_exp_date = $invoice_product->inv_pro_inv_exp_date;
                        $edit_pro_temp->inv_pro_temp_invoice_no = $invoice_product->inv_pro_inv_invoice_no;
                        $edit_pro_temp->inv_pro_temp_unit_price = $invoice_product->inv_pro_inv_unit_price;
                        $edit_pro_temp->inv_pro_temp_deal_type = 3;//3=purchase-edit
                        $edit_pro_temp->inv_pro_temp_status = 1;
                        $edit_pro_temp->inv_pro_temp_type = $is_warranty ? 2 : 1; //1=non-warranty , 2= warranty
                        $edit_pro_temp->inv_pro_temp_slno = $pro_sl;
                        $edit_pro_temp->inv_pro_temp_created_at = Carbon::now();
                        $edit_pro_temp->save();
                    }
                }
            } else {
                return redirect()->back()->with(['sub_err' => 'Invalid Inventory']);
            }


        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['sub_err' => 'Something went wrong'.$e->getMessage()]);
        }

        DB::commit();
        return view('inventory.product_return.buy_product_edit',compact('suppliers','sell_pro', 'invoice_no', 'buy_supplier', 'issue_date'));
    }


    public function addToCart(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $product = Inv_product_detail::where('inv_pro_det_id', $request->pro_id)
            ->where('inv_pro_det_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_det_status', 1)
            ->first();

        if(!empty($product)) {

            $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', $request->pro_id)
                ->where('inv_pro_temp_deal_type',3)
                ->first();
            // return $request->all();
            $pre_pro_inv = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $request->pro_id)
                ->where('inv_pro_inv_invoice_no', $request->invoice_no)
                ->where('inv_pro_inv_deal_type',1)
                ->where('inv_pro_inv_tran_type',1)
                ->first();

            if (!empty($pre_pro_inv)) {

                if(($pre_pro_inv->inv_pro_inv_qty - $product->inv_pro_det_available_qty) > ($request->pro_qty - (int)$request->short_qty)) {
                    return response()->json(['status' => 408]);
                }
            }

            if(!empty($row)) {
                $row->inv_pro_temp_pro_name = $product->product->inv_pro_name ?? $product->inv_pro_det_id;
                $row->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);
                $row->inv_pro_temp_short_qty = $request->short_qty;
                $row->inv_pro_temp_qty = $request->pro_qty;
                $row->inv_pro_temp_short_remarks = $request->remarks;
                $row->inv_pro_temp_unit_price = $request->pro_price;
                $row->inv_pro_temp_exp_date = $request->exp_date;
                $row->inv_pro_temp_updated_at = Carbon::now();

                $row->save();

            } else {
                $pro_temp_add = new Inv_product_temporary;
                $pro_temp_add->inv_pro_temp_user_id = Auth::user()->au_id;
                $pro_temp_add->inv_pro_temp_pro_id = $product->inv_pro_det_id;
                $pro_temp_add->inv_pro_temp_pro_name = $product->product->inv_pro_name ?? $product->inv_pro_det_id;
                $pro_temp_add->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);
                $pro_temp_add->inv_pro_temp_short_qty = $request->short_qty;
                $pro_temp_add->inv_pro_temp_qty = $request->pro_qty;
                $pro_temp_add->inv_pro_temp_short_remarks = $request->remarks;
                $pro_temp_add->inv_pro_temp_unit_price = $request->pro_price;
                $pro_temp_add->inv_pro_temp_exp_date = $request->exp_date;
                $pro_temp_add->inv_pro_temp_invoice_no = $request->invoice_no;
                $pro_temp_add->inv_pro_temp_slno = '';
                $pro_temp_add->inv_pro_temp_deal_type = '3';//1=purchase,2=sale,3=purchase-edit
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
                ->where('inv_pro_temp_deal_type',3)
                ->first();
            if(!empty($row)) {
                $row->inv_pro_temp_pro_name = $product->product->inv_pro_name ?? $product->inv_pro_det_id;
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
                $pro_temp_add->inv_pro_temp_pro_name = $product->product->inv_pro_name ?? $product->inv_pro_det_id;
                $pro_temp_add->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id);
                $pro_temp_add->inv_pro_temp_qty = 0;
                $pro_temp_add->inv_pro_temp_unit_price = $request->pro_price;
                $pro_temp_add->inv_pro_temp_exp_date = $request->exp_date;
                $pro_temp_add->inv_pro_temp_invoice_no = $request->invoice_no;
                $pro_temp_add->inv_pro_temp_slno = '';
                $pro_temp_add->inv_pro_temp_deal_type = '3';//1=purchase,2=sale
                $pro_temp_add->inv_pro_temp_status = 1;
                $pro_temp_add->inv_pro_temp_type = 2; //1=non-warranty , 2= warranty
                $pro_temp_add->inv_pro_temp_updated_at = Carbon::now();


                $pro_temp_add->save();


            }

        } else {
            return response()->json(['status' => 404]);
        }

        $product_details = $product->product;
        $product_sl_no = array_values(array_filter($product_sl_no, function ($sl) {
            return trim($sl) !== '';
        }));

        return view('pages.ajax.purchase_product.warrenty_product_get_sl_no_inner_form', compact('product', 'product_sl_no', 'product_details'));
    }

    public function addWarrentyProductSlNo(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $sl_no = trim((string) $request->sl_no);

        if ($sl_no === '') {
            return response()->json(['status' => 400, 'reason' => 'SL No is required.']);
        }
        if (strpos($sl_no, ',') !== false) {
            return response()->json(['status' => 400, 'reason' => 'SL No cannot contain a comma.']);
        }

        // Lock the cart row so fast scans are saved one after another and none get lost
        return DB::transaction(function () use ($request, $sl_no, $com) {
            $user_id = Auth::user()->au_id;

            $row = Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)
                    ->where('inv_pro_temp_pro_id', $request->pro_id)
                    ->where('inv_pro_temp_deal_type',3)
                    ->lockForUpdate()
                    ->first();

            if (empty($row)) {
                return response()->json(['status' => 406, 'reason' => 'Cart row not found. Close and open the product again.']);
            }

            // already scanned for this or another product of this invoice
            $cart_rows = Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)
                ->where('inv_pro_temp_deal_type', 3)
                ->get();
            foreach ($cart_rows as $cart_row) {
                $cart_sl = array_map('strtoupper', array_map('trim', explode(',', (string) $cart_row->inv_pro_temp_slno)));
                if (in_array(strtoupper($sl_no), $cart_sl)) {
                    $where = $cart_row->inv_pro_temp_id == $row->inv_pro_temp_id ? 'this product' : $cart_row->inv_pro_temp_pro_name;
                    return response()->json(['status' => 402, 'reason' => 'Already added to ' . $where . '.']);
                }
            }

            // in stock from another purchase (serials of the invoice being edited do not count)
            $invoice_no = $request->invoice_no ?: $cart_rows->pluck('inv_pro_temp_invoice_no')->filter()->first();
            $own_line_ids = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_invoice_no', $invoice_no)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 1)
                ->pluck('inv_pro_inv_id');
            $in_stock = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', $com)
                ->where('inv_pro_invdet_slno', $sl_no)
                ->where('inv_pro_invdet_status', 1)
                ->where('inv_pro_invdet_sell_status', 0)
                ->whereNotIn('inv_pro_invdet_proinv_id', $own_line_ids)
                ->exists();
            if ($in_stock) {
                return response()->json(['status' => 403, 'reason' => 'Already in stock from another purchase.']);
            }

            $pro_all_sl_no = array_values(array_filter(explode(',', (string) $row->inv_pro_temp_slno), function ($sl) {
                return trim($sl) !== '';
            }));
            $pro_all_sl_no[] = $sl_no;

            $row->inv_pro_temp_slno = implode(',', $pro_all_sl_no);
            $row->inv_pro_temp_qty = count($pro_all_sl_no);
            $row->inv_pro_temp_updated_at = Carbon::now();
            $row->save();

            $content = $row;

            return view("pages.ajax.purchase_product.warrenty_product_sl_no_list", compact('content'));
        });
    }

    public function removeWarrentyProductSlNo(Request $request)
    {
        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', $request->pro_id)
                ->where('inv_pro_temp_deal_type',3)
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

            return view("pages.ajax.purchase_product.warrenty_product_sl_no_list", compact('content'));
        } else {
            return response()->json(['status' => 406]); //sl no not found
        }
    }


    public function cartSubmit(Request $request){
        $request->validate([
            'supplier' => 'required',
            'memo' => 'required',
            'issue_date' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $com = Auth::user()->au_company_id;
            $branch = Auth::user()->au_branch_id;
            $user_id = Auth::user()->au_id;
            $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_deal_type',3)
                ->get();

            if ($cart_content->isEmpty()) {
                throw new \Exception('No product in the invoice. Remove the purchase instead of saving it empty.');
            }

            $new_memo_no = $cart_content->pluck('inv_pro_temp_invoice_no')->filter()->first() ?: $request->memo;

            // previous lines of this invoice
            $pre_sell_products = Inv_product_inventory::where('inv_pro_inv_invoice_no', $new_memo_no)
                ->where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_branch_id', $branch)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 1)
                ->get();

            if ($pre_sell_products->isEmpty()) {
                throw new \Exception('Invoice ' . $new_memo_no . ' not found');
            }

            $pre_first = $pre_sell_products->first();
            $pre_inv_edit_count = $pre_first->inv_pro_inv_edit_count;
            $pre_inv_confirm_status = $pre_first->inv_pro_inv_confirm;
            $pre_submit_by = $pre_first->inv_pro_inv_submit_by;
            $pre_submit_at = $pre_first->inv_pro_inv_submit_at;
            $pre_sell_product_invID = $pre_sell_products->pluck('inv_pro_inv_id')->toArray();

            // The lines are deleted and re-created, so refuse when other records point at them
            $sold_sl = Inv_product_inventory_detail::whereIn('inv_pro_invdet_proinv_id', $pre_sell_product_invID)
                ->where('inv_pro_invdet_sell_status', 1)
                ->pluck('inv_pro_invdet_slno')->toArray();
            if (count($sold_sl) > 0) {
                throw new \Exception('Cannot edit - these serial numbers are already sold: ' . implode(', ', $sold_sl));
            }
            $has_return = Inv_product_inventory::whereIn('inv_pro_inv_sell_ref', $pre_sell_product_invID)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 2)
                ->exists();
            if ($has_return) {
                throw new \Exception('Cannot edit - a purchase return was made against this invoice');
            }

            $stock_before = Inv_product_detail::whereIn('inv_pro_det_id', array_merge(
                    $pre_sell_products->pluck('inv_pro_inv_project_id')->toArray(),
                    $cart_content->pluck('inv_pro_temp_pro_id')->toArray()
                ))->pluck('inv_pro_det_available_qty', 'inv_pro_det_id');

            // take the previous quantities back out of stock
            $touched_det_ids = array();
            foreach ($pre_sell_products as $pre_sell_product) {
                $pre_pro_det = Inv_product_detail::where('inv_pro_det_com_id', $com)
                    ->where('inv_pro_det_id', $pre_sell_product->inv_pro_inv_project_id)
                    ->first();
                if (!empty($pre_pro_det)) {
                    $pre_pro_det->inv_pro_det_available_qty = $pre_pro_det->inv_pro_det_available_qty - $pre_sell_product->inv_pro_inv_qty;
                    $pre_pro_det->save();
                    $touched_det_ids[] = $pre_pro_det->inv_pro_det_id;
                }
            }

            Inv_product_inventory::whereIn('inv_pro_inv_id', $pre_sell_product_invID)->delete();
            Inv_product_inventory_detail::where('inv_pro_invdet_com_id',$com)
                                        ->whereIn('inv_pro_invdet_proinv_id',$pre_sell_product_invID)
                                        ->delete();

            foreach ($cart_content as $content) {
                $product_id = $content->inv_pro_temp_pro_id;

                $check_product = Inv_product_detail::where('inv_pro_det_id', $product_id)
                    ->where('inv_pro_det_com_id', $com)
                    ->where('inv_pro_det_status', 1)
                    ->first();

                if (empty($check_product)) {
                    throw new \Exception('Product not found or inactive: ' . $content->inv_pro_temp_pro_name);
                }

                $req_sl_ids = array_values(array_filter(explode(',', (string) $content->inv_pro_temp_slno), function ($sl) {
                    return trim($sl) !== '';
                }));

                if($check_product->inv_pro_det_pro_warranty == 0) {
                    $req_qty = $content->inv_pro_temp_qty;
                } else {
                    $req_qty = count($req_sl_ids);
                }

                if ($req_qty < 1) {
                    throw new \Exception('Quantity must be at least 1 for ' . $content->inv_pro_temp_pro_name);
                }

                $short_qty = (int) $content->inv_pro_temp_short_qty;
                $product_price = $content->inv_pro_temp_unit_price;
                $sub_total = $req_qty * $product_price;
                $available_quantity = max(0, $req_qty - $short_qty);

                $product_inventory = new Inv_product_inventory();
                $product_inventory->inv_pro_inv_com_id = $com;
                $product_inventory->inv_pro_inv_branch_id = $branch;
                $product_inventory->inv_pro_inv_project_id = $product_id;
                $product_inventory->inv_pro_inv_party_id = $request->supplier;
                $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
                $product_inventory->inv_pro_inv_total_qty = $req_qty;
                $product_inventory->inv_pro_inv_short_qty = $short_qty;
                $product_inventory->inv_pro_inv_short_remarks = $content->inv_pro_temp_short_remarks;
                $product_inventory->inv_pro_inv_qty = $available_quantity;
                $product_inventory->inv_pro_inv_unit_price = $product_price;
                $product_inventory->inv_pro_inv_debit = $sub_total;
                $product_inventory->inv_pro_inv_credit = 0;
                $product_inventory->inv_pro_inv_issue_date = Carbon::parse($request->issue_date)->format('Y-m-d');
                $product_inventory->inv_pro_inv_exp_date = $content->inv_pro_temp_exp_date;
                $product_inventory->inv_pro_inv_tran_desc = "Purchase Product";
                $product_inventory->inv_pro_inv_deal_type =  1;//1=supplier
                $product_inventory->inv_pro_inv_tran_type =  1;//1=buy/sell product-buy
                $product_inventory->inv_pro_inv_status = 1;
                $product_inventory->inv_pro_inv_confirm = $pre_inv_confirm_status;
                $product_inventory->inv_pro_inv_edit_count = $pre_inv_edit_count + 1;
                $product_inventory->inv_pro_inv_submit_by = $pre_submit_by;
                $product_inventory->inv_pro_inv_submit_at = $pre_submit_at;
                $product_inventory->inv_pro_inv_update_by = $user_id;
                $product_inventory->inv_pro_inv_update_at = Carbon::now();
                $product_inventory->save();

                // warranty product serial numbers
                if ($check_product->inv_pro_det_pro_warranty != 0) {
                    foreach ($req_sl_ids as $sl_no) {
                        $pro_inv_details = new Inv_product_inventory_detail();
                        $pro_inv_details->inv_pro_invdet_com_id = $com;
                        $pro_inv_details->inv_pro_invdet_branch_id = $branch;
                        $pro_inv_details->inv_pro_invdet_proinv_id = $product_inventory->inv_pro_inv_id;
                        $pro_inv_details->inv_pro_invdet_pro_id = $check_product->inv_pro_det_id;
                        $pro_inv_details->inv_pro_invdet_buy_id = $request->supplier;
                        $pro_inv_details->inv_pro_invdet_slno = trim($sl_no);
                        $pro_inv_details->inv_pro_invdet_sell_status = 0;
                        $pro_inv_details->inv_pro_invdet_status = 1;
                        $pro_inv_details->inv_pro_invdet_submit_by = $user_id;
                        $pro_inv_details->inv_pro_invdet_submit_at = Carbon::now();
                        $pro_inv_details->save();
                    }
                }

                // available quantity update in product detail table
                $check_product->inv_pro_det_available_qty = $check_product->inv_pro_det_available_qty + $available_quantity;
                $check_product->save();
                $touched_det_ids[] = $check_product->inv_pro_det_id;
            }

            // stock already sold out of this purchase cannot be removed
            $negative = Inv_product_detail::with('product')
                ->whereIn('inv_pro_det_id', array_unique($touched_det_ids))
                ->where('inv_pro_det_available_qty', '<', 0)
                ->get()
                ->filter(function ($d) use ($stock_before) {
                    return $d->inv_pro_det_available_qty < ($stock_before[$d->inv_pro_det_id] ?? 0);
                });
            if ($negative->isNotEmpty()) {
                throw new \Exception('Stock would go negative for: ' . $negative->map(function ($d) {
                    return $d->product->inv_pro_name ?? $d->inv_pro_det_id;
                })->implode(', ') . ' (already sold). Increase the quantity.');
            }

            Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_deal_type',3)
                ->delete();

        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Purchase edit failed: ".$e->getMessage();
            return redirect()->back()->with(['sub_err' => $msg]);
        }

        DB::commit();
        $msg = "Buy Products Successfully completed";
        return redirect()->route('buy.buy-product-new')->with(['sub_success' => $msg , 'print_buy_invoice' => $new_memo_no]);
    }

    public function removecart(Request $request){
        Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', $request->content_id)
                ->where('inv_pro_temp_deal_type',3)
                ->delete();

    }

    // public function updatecart(Request $request){
    //     $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
    //             ->where('inv_pro_temp_pro_id', $request->content_id)
    //             ->where('inv_pro_temp_deal_type',3)
    //             ->get();
    //     $row->inv_pro_temp_qty = $request->pro_qty;
    //     $row->inv_pro_temp_updated_at = Carbon::now();
    //     $row->save();
    // }

    public function getCartContent()
    {
        $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type',3)
            ->get();

        return view('pages.ajax.purchase_edit.get_cart_content', compact('cart_content'));
    }

    public function invTemporaryBuy(Request $request){
        $user = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
        $pro_temps = Inv_product_temporary::where('inv_pro_temp_user_id', $user)
                                            ->where('inv_pro_temp_deal_type',3)
                                            ->get();
        $pro_sup = Inv_supplier::where('inv_sup_com_id', $com)
            ->where('inv_sup_id', $request->supplier)
            ->first();
        return view('inventory.product_inventory.product_buy_invoice',compact('pro_temps','pro_sup'));
    }


    public function buyEditConfirmFormShow(Request $request)
    {
        $user = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
        $pro_temps = Inv_product_temporary::where('inv_pro_temp_user_id', $user)
                                            ->where('inv_pro_temp_deal_type',3)
                                            ->get();
        $pro_sup = Inv_supplier::where('inv_sup_com_id', $com)
            ->where('inv_sup_id', $request->supplier)
            ->first();
        return view('inventory.product_return.product_buy_edit_invoice',compact('pro_temps','pro_sup'));
    }
}

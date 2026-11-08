<?php

namespace App\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Cart;
use DB;
use Auth;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Inv_product_detail;
use App\Models\Inv_product;

use App\Models\Admin_user;
use App\Models\Inv_supplier;
use App\Models\Inv_product_inventory;
use App\Models\Inv_product_inventory_detail;
use App\Models\Inv_customer_inventory;
use App\Models\Inv_product_temporary;


class InventoryPurchaseCartController extends Controller
{
    // ------------------------------------------------------------------
    //  NON-WARRANTY: ADD TO CART
    //  Receives a DETAIL ID (inv_pro_det_id) from the purchase page.
    // ------------------------------------------------------------------
    public function addToCart(Request $request)
    {
        // 1. Find the DETAIL row directly
        $detail = Inv_product_detail::where('inv_pro_det_id', $request->pro_id)
            ->where('inv_pro_det_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_det_branch_id', Auth::user()->au_branch_id)
            ->where('inv_pro_det_status', 1)
            ->first();

        if (empty($detail)) {
            return response()->json([
                'status' => 404,
                'reason' => 'Product detail not found for your company/branch.',
            ]);
        }

        // 2. Parent product
        $product = Inv_product::where('inv_pro_id', $detail->inv_pro_det_pro_id)
            ->where('inv_pro_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_status', 1)
            ->first();

        if (empty($product)) {
            return response()->json([
                'status' => 404,
                'reason' => 'Parent product not found or inactive.',
            ]);
        }

        $typeName = Inv_product_detail::get_type_name($detail->inv_pro_det_id);

        // 3. Upsert into temporary cart — KEYED ON DETAIL ID
        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_pro_id', $detail->inv_pro_det_id)
            ->where('inv_pro_temp_deal_type', 1)
            ->first();

        if (!empty($row)) {
            $row->inv_pro_temp_pro_name       = $product->inv_pro_name;
            $row->inv_pro_temp_type_name      = $typeName;
            $row->inv_pro_temp_short_qty      = $request->short_qty ?? 0;
            $row->inv_pro_temp_qty            = $request->pro_qty;
            $row->inv_pro_temp_short_remarks  = $request->remarks;
            $row->inv_pro_temp_unit_price     = $request->pro_price;
            $row->inv_pro_temp_exp_date       = $request->exp_date;
            $row->inv_pro_temp_updated_at     = Carbon::now();
            $row->save();
        } else {
            $pro_temp_add = new Inv_product_temporary;
            $pro_temp_add->inv_pro_temp_user_id        = Auth::user()->au_id;
            $pro_temp_add->inv_pro_temp_pro_id         = $detail->inv_pro_det_id;   // detail id
            $pro_temp_add->inv_pro_temp_pro_name       = $product->inv_pro_name;
            $pro_temp_add->inv_pro_temp_type_name      = $typeName;
            $pro_temp_add->inv_pro_temp_short_qty      = $request->short_qty ?? 0;
            $pro_temp_add->inv_pro_temp_qty            = $request->pro_qty;
            $pro_temp_add->inv_pro_temp_short_remarks  = $request->remarks;
            $pro_temp_add->inv_pro_temp_unit_price     = $request->pro_price;
            $pro_temp_add->inv_pro_temp_exp_date       = $request->exp_date;
            $pro_temp_add->inv_pro_temp_slno           = '';
            $pro_temp_add->inv_pro_temp_deal_type      = '1';
            $pro_temp_add->inv_pro_temp_status         = 1;
            $pro_temp_add->inv_pro_temp_type           = 1; // 1 = non-warranty
            $pro_temp_add->inv_pro_temp_created_at     = Carbon::now();
            $pro_temp_add->save();
        }

        return response()->json(['status' => 200]);
    }

    // ------------------------------------------------------------------
    //  WARRANTY: ADD TO CART + OPEN SL-NO MODAL
    //  Receives a DETAIL ID (inv_pro_det_id).
    // ------------------------------------------------------------------
    public function addToCartWarrentyProduct(Request $request)
    {
        // 1. Detail row (must be warranty-bearing)
        $product = Inv_product_detail::where('inv_pro_det_id', $request->pro_id)
            ->where('inv_pro_det_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_det_branch_id', Auth::user()->au_branch_id)
            ->where('inv_pro_det_status', 1)
            ->where('inv_pro_det_pro_warranty', '!=', 0)
            ->first();

        if (empty($product)) {
            // Diagnose
            $exists = Inv_product_detail::where('inv_pro_det_id', $request->pro_id)->first();

            if (!$exists) {
                return response()->json([
                    'status' => 404,
                    'reason' => 'Product detail ID not found (inv_pro_det_id = ' . $request->pro_id . ')',
                ]);
            }
            if ($exists->inv_pro_det_com_id != Auth::user()->au_company_id) {
                return response()->json([
                    'status' => 404,
                    'reason' => 'Company mismatch (detail = ' . $exists->inv_pro_det_com_id
                              . ', you = ' . Auth::user()->au_company_id . ')',
                ]);
            }
            if ($exists->inv_pro_det_branch_id != Auth::user()->au_branch_id) {
                return response()->json([
                    'status' => 404,
                    'reason' => 'Branch mismatch (detail = ' . $exists->inv_pro_det_branch_id
                              . ', you = ' . Auth::user()->au_branch_id . ')',
                ]);
            }
            if ($exists->inv_pro_det_status != 1) {
                return response()->json([
                    'status' => 404,
                    'reason' => 'Detail is inactive (status = ' . $exists->inv_pro_det_status . ')',
                ]);
            }
            if ($exists->inv_pro_det_pro_warranty == 0 || $exists->inv_pro_det_pro_warranty === null) {
                return response()->json([
                    'status' => 404,
                    'reason' => 'No warranty on product (warranty = '
                              . var_export($exists->inv_pro_det_pro_warranty, true) . ')',
                ]);
            }
            return response()->json([
                'status' => 404,
                'reason' => 'Unknown reason — please inspect the detail row.',
            ]);
        }

        // 2. Parent product
        $product_details = Inv_product::where('inv_pro_id', $product->inv_pro_det_pro_id)
            ->where('inv_pro_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_status', 1)
            ->first();

        if (empty($product_details)) {
            return response()->json([
                'status' => 404,
                'reason' => 'Parent product not found or inactive (inv_pro_id = '
                          . $product->inv_pro_det_pro_id . ')',
            ]);
        }

        $product_sl_no = [];

        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_pro_id', $product->inv_pro_det_id)
            ->where('inv_pro_temp_deal_type', 1)
            ->first();

        if (!empty($row)) {
            $row->inv_pro_temp_pro_name    = $product_details->inv_pro_name;
            $row->inv_pro_temp_type_name   = Inv_product_detail::get_type_name($product->inv_pro_det_id);
            $row->inv_pro_temp_unit_price  = $request->pro_price;
            $row->inv_pro_temp_exp_date    = $request->exp_date;
            $row->inv_pro_temp_updated_at  = Carbon::now();
            $row->save();

            if ($row->inv_pro_temp_slno != '') {
                $product_sl_no = explode(',', $row->inv_pro_temp_slno);
            }
        } else {
            $pro_temp_add = new Inv_product_temporary;
            $pro_temp_add->inv_pro_temp_user_id     = Auth::user()->au_id;
            $pro_temp_add->inv_pro_temp_pro_id      = $product->inv_pro_det_id;
            $pro_temp_add->inv_pro_temp_pro_name    = $product_details->inv_pro_name;
            $pro_temp_add->inv_pro_temp_type_name   = Inv_product_detail::get_type_name($product->inv_pro_det_id);
            $pro_temp_add->inv_pro_temp_qty         = 0;
            $pro_temp_add->inv_pro_temp_unit_price  = $request->pro_price;
            $pro_temp_add->inv_pro_temp_exp_date    = $request->exp_date;
            $pro_temp_add->inv_pro_temp_slno        = '';
            $pro_temp_add->inv_pro_temp_deal_type   = '1';
            $pro_temp_add->inv_pro_temp_status      = 1;
            $pro_temp_add->inv_pro_temp_type        = 2; // 2 = warranty
            $pro_temp_add->inv_pro_temp_updated_at  = Carbon::now();
            $pro_temp_add->save();
        }

return view('pages.ajax.purchase_product.warrenty_product_get_sl_no_inner_form',
    compact('product', 'product_sl_no', 'product_details'));
    }

    // ------------------------------------------------------------------
    //  SERIAL NUMBER: ADD
    // ------------------------------------------------------------------
    public function addWarrentyProductSlNo(Request $request)
    {
        $sl_no = trim((string) $request->sl_no);

        if ($sl_no === '') {
            return response()->json(['status' => 400, 'reason' => 'SL No is required.']);
        }
        if (strpos($sl_no, ',') !== false) {
            return response()->json(['status' => 400, 'reason' => 'SL No cannot contain a comma.']);
        }

        // Lock the cart row so fast scans are saved one after another and none get lost
        return DB::transaction(function () use ($request, $sl_no) {
            $user_id = Auth::user()->au_id;

            $row = Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)
                ->where('inv_pro_temp_pro_id', $request->pro_id)
                ->where('inv_pro_temp_deal_type', 1)
                ->lockForUpdate()
                ->first();

            if (empty($row)) {
                return response()->json(['status' => 406, 'reason' => 'Cart row not found. Close and open the product again.']);
            }

            // already scanned for this or another product in this purchase
            $cart_rows = Inv_product_temporary::where('inv_pro_temp_user_id', $user_id)
                ->where('inv_pro_temp_deal_type', 1)
                ->get();
            foreach ($cart_rows as $cart_row) {
                $cart_sl = array_map('strtoupper', array_map('trim', explode(',', (string) $cart_row->inv_pro_temp_slno)));
                if (in_array(strtoupper($sl_no), $cart_sl)) {
                    $where = $cart_row->inv_pro_temp_id == $row->inv_pro_temp_id ? 'this product' : $cart_row->inv_pro_temp_pro_name;
                    return response()->json(['status' => 402, 'reason' => 'Already added to ' . $where . '.']);
                }
            }

            // already purchased and still in stock
            $in_stock = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', Auth::user()->au_company_id)
                ->where('inv_pro_invdet_slno', $sl_no)
                ->where('inv_pro_invdet_status', 1)
                ->where('inv_pro_invdet_sell_status', 0)
                ->exists();
            if ($in_stock) {
                return response()->json(['status' => 403, 'reason' => 'Already in stock from an earlier purchase.']);
            }

            $pre_sl_no = array_values(array_filter(explode(',', (string) $row->inv_pro_temp_slno), function ($sl) {
                return trim($sl) !== '';
            }));
            $pre_sl_no[] = $sl_no;

            $row->inv_pro_temp_slno       = implode(',', $pre_sl_no);
            $row->inv_pro_temp_qty        = count($pre_sl_no);
            $row->inv_pro_temp_updated_at = Carbon::now();
            $row->save();

            $content = $row;

            return view('pages.ajax.purchase_product.warrenty_product_sl_no_list', compact('content'));
        });
    }

    // ------------------------------------------------------------------
    //  SERIAL NUMBER: REMOVE
    // ------------------------------------------------------------------
    public function removeWarrentyProductSlNo(Request $request)
    {
        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_pro_id', $request->pro_id)
            ->where('inv_pro_temp_deal_type', 1)
            ->first();

        if (empty($row)) {
            return response()->json(['status' => 406, 'reason' => 'Cart row not found.']);
        }

        $pro_all_sl_no = explode(',', $row->inv_pro_temp_slno);

        if (($key = array_search($request->sl_no, $pro_all_sl_no)) !== false) {
            unset($pro_all_sl_no[$key]);
        }
        $pro_all_sl_no = array_values($pro_all_sl_no);

        $row->inv_pro_temp_slno       = implode(',', $pro_all_sl_no);
        $row->inv_pro_temp_qty        = count($pro_all_sl_no);
        $row->inv_pro_temp_updated_at = Carbon::now();
        $row->save();

        $content = $row;

        return view('pages.ajax.purchase_product.warrenty_product_sl_no_list', compact('content'));
    }

    // ------------------------------------------------------------------
    //  CART: REMOVE
    // ------------------------------------------------------------------
    public function removecart(Request $request)
    {
        Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_pro_id', $request->content_id)
            ->where('inv_pro_temp_deal_type', 1)
            ->delete();

        return response()->json(['status' => 200]);
    }

    // ------------------------------------------------------------------
    //  CART: UPDATE QTY
    // ------------------------------------------------------------------
    public function updatecart(Request $request)
    {
        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_pro_id', $request->content_id)
            ->where('inv_pro_temp_deal_type', 1)
            ->first();   // 👈 was ->get(), now ->first()

        if (!empty($row)) {
            $row->inv_pro_temp_qty        = $request->pro_qty;
            $row->inv_pro_temp_updated_at = Carbon::now();
            $row->save();
        }

        return response()->json(['status' => 200]);
    }

    // ------------------------------------------------------------------
    //  CART: FETCH HTML
    // ------------------------------------------------------------------
    public function getCartContent()
    {
        // Remove warranty rows that have no serial numbers yet
        Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type', 1)
            ->where('inv_pro_temp_type', 2)
            ->where('inv_pro_temp_slno', '')
            ->delete();

        $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type', 1)
            ->get();

        return view('pages.ajax.purchase_product.get_cart_content', compact('cart_content'));
    }

    // ------------------------------------------------------------------
    //  SUBMIT CART → create inventory rows
    // ------------------------------------------------------------------
    public function cartSubmit(Request $request)
    {
        $request->validate([
            'supplier'   => 'required',
            'memo'       => 'required',
            'issue_date' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $com      = Auth::user()->au_company_id;
            $branch   = Auth::user()->au_branch_id;
            $user_id  = Auth::user()->au_id;

            // Duplicate memo check
            $check_memo = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_branch_id', $branch)
                ->where('inv_pro_inv_invoice_no', $request->memo)
                ->first();

            if (!empty($check_memo)) {
                return redirect()->back()->with(['sub_err' => 'Duplicate memo no']);
            }

            $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_deal_type', 1)
                ->get();

            $new_memo_no            = $request->memo;
            $total_purchase_amount  = 0;

            foreach ($cart_content as $content) {
                $det_id = $content->inv_pro_temp_pro_id;   // now a detail ID

                // Fetch the DETAIL row
                $check_product = Inv_product_detail::where('inv_pro_det_id', $det_id)
                    ->where('inv_pro_det_com_id', $com)
                    ->where('inv_pro_det_branch_id', $branch)
                    ->where('inv_pro_det_status', 1)
                    ->first();

                if (empty($check_product)) {
                    throw new \Exception('Product detail not found for temp cart row (det_id = ' . $det_id . ')');
                }

                // Serial numbers
                $req_sl_ids = explode(',', $content->inv_pro_temp_slno);
                $new_req_sl_ids = [];
                foreach ($req_sl_ids as $sl_id) {
                    if ($sl_id !== '') {
                        $new_req_sl_ids[] = $sl_id;
                    }
                }
                $req_sl_ids = $new_req_sl_ids;

                if ($check_product->inv_pro_det_pro_warranty == 0) {
                    $req_qty = $content->inv_pro_temp_qty;
                } else {
                    $req_qty = count($req_sl_ids);
                }

                if ($req_qty < 1) {
                    throw new \Exception('Quantity must be at least 1 for detail id ' . $det_id);
                }

                $product_price  = $content->inv_pro_temp_unit_price;
                $sub_total      = $req_qty * $product_price;
                $total_purchase_amount += $sub_total;
                $exp_date       = $content->inv_pro_temp_exp_date;

                $short_qty      = (int) $content->inv_pro_temp_short_qty;
                $available_qty  = max(0, $req_qty - $short_qty);

                // Inventory master
                $product_inventory = new Inv_product_inventory();
                $product_inventory->inv_pro_inv_com_id         = $com;
                $product_inventory->inv_pro_inv_branch_id      = $branch;
                $product_inventory->inv_pro_inv_project_id     = $det_id;                    // detail id
                $product_inventory->inv_pro_inv_party_id       = $request->supplier;
                $product_inventory->inv_pro_inv_invoice_no     = $new_memo_no;
                $product_inventory->inv_pro_inv_total_qty      = $req_qty;
                $product_inventory->inv_pro_inv_short_qty      = $short_qty;
                $product_inventory->inv_pro_inv_short_remarks  = $content->inv_pro_temp_short_remarks;
                $product_inventory->inv_pro_inv_qty            = $available_qty;
                $product_inventory->inv_pro_inv_unit_price     = $product_price;
                $product_inventory->inv_pro_inv_debit          = $sub_total;
                $product_inventory->inv_pro_inv_credit         = 0;
                $product_inventory->inv_pro_inv_issue_date     = Carbon::now();
                $product_inventory->inv_pro_inv_exp_date       = $exp_date;
                $product_inventory->inv_pro_inv_tran_desc      = 'Purchase Product';
                $product_inventory->inv_pro_inv_deal_type      = 1;
                $product_inventory->inv_pro_inv_tran_type      = 1;
                $product_inventory->inv_pro_inv_status         = 1;
                $product_inventory->inv_pro_inv_submit_by      = $user_id;
                $product_inventory->inv_pro_inv_submit_at      = Carbon::now();
                $product_inventory->save();

                // Warranty serial rows
                if ($check_product->inv_pro_det_pro_warranty != 0) {
                    for ($i = 0; $i < $req_qty; $i++) {
                        $pro_inv_details = new Inv_product_inventory_detail();
                        $pro_inv_details->inv_pro_invdet_com_id        = $com;
                        $pro_inv_details->inv_pro_invdet_branch_id     = $branch;
                        $pro_inv_details->inv_pro_invdet_proinv_id     = $product_inventory->inv_pro_inv_id;
                        $pro_inv_details->inv_pro_invdet_pro_id        = $check_product->inv_pro_det_id;
                        $pro_inv_details->inv_pro_invdet_buy_id        = $request->supplier;
                        $pro_inv_details->inv_pro_invdet_slno          = $req_sl_ids[$i] ?? null;
                        $pro_inv_details->inv_pro_invdet_sell_status   = 0;
                        $pro_inv_details->inv_pro_invdet_status        = 1;
                        $pro_inv_details->inv_pro_invdet_submit_by     = $user_id;
                        $pro_inv_details->inv_pro_invdet_submit_at     = Carbon::now();
                        $pro_inv_details->save();
                    }
                }

                // Update stock on the DETAIL row
                $check_product->inv_pro_det_available_qty += $available_qty;
                $check_product->save();
            }

            // Clear cart
            Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_deal_type', 1)
                ->delete();

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with(['sub_err' => 'Something went wrong: ' . $e->getMessage()]);
        }

        DB::commit();

        return redirect()->route('buy.buy-product-new')
            ->with(['sub_success' => 'Buy Products Successfully completed',
                    'print_buy_invoice' => $new_memo_no]);
    }

    // ------------------------------------------------------------------
    //  PREVIEW INVOICE
    // ------------------------------------------------------------------
    public function invTemporaryBuy(Request $request)
    {
        $user = Auth::user()->au_id;
        $com  = Auth::user()->au_company_id;

        $pro_temps = Inv_product_temporary::where('inv_pro_temp_user_id', $user)
            ->where('inv_pro_temp_deal_type', 1)
            ->get();

        $invoice = $request->memo;

        $pro_sup = Inv_supplier::where('inv_sup_com_id', $com)
            ->where('inv_sup_id', $request->supplier)
            ->first();

        return view('inventory.product_inventory.product_buy_invoice',
            compact('pro_temps', 'pro_sup', 'invoice'));
    }
}
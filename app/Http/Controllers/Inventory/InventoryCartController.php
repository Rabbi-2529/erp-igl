<?php

namespace App\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Cart;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Inv_product_detail;
use App\Models\Admin_user;
use App\Models\Inv_product_inventory;
use App\Models\Inv_product_inventory_detail;
use App\Models\EmployeeComission;
use App\Models\Inv_customer_inventory;
use App\Models\Inv_product_temporary;
use App\Models\Inv_customer;



class InventoryCartController extends Controller
{
public function addToCart(Request $request)
{
    $product = Inv_product_detail::with('product') // Eager load the product relationship
        ->where('inv_pro_det_id', $request->pro_id)
        ->where('inv_pro_det_com_id', Auth::user()->au_company_id)
        ->where('inv_pro_det_branch_id', Auth::user()->au_branch_id)
        ->where('inv_pro_det_status', 1)
        ->first();

    if(!empty($product)) {
        if($product->inv_pro_det_available_qty < $request->pro_qty) {
            return response()->json(['status' => 400, 'message' => 'Insufficient quantity']);
        }

        // Safely get the product name with fallback
        $productName = $product->product->inv_pro_name ?? 'Unknown Product';
        
        // Additional validation
        if(empty($productName)) {
            return response()->json(['status' => 400, 'message' => 'Product name not found']);
        }

        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_pro_id', $request->pro_id)
            ->where('inv_pro_temp_deal_type', 2)
            ->first();

        if(!empty($row)) {
            $row->inv_pro_temp_pro_name = $productName;
            $row->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id) ?? 'Unknown Type';
            $row->inv_pro_temp_qty = $request->pro_qty;
            $row->inv_pro_temp_unit_price = $request->pro_price ?? 0;
            $row->inv_pro_temp_exp_date = $request->exp_date ?? null;
            $row->inv_pro_temp_updated_at = Carbon::now();
            $row->save();
        } else {
            $pro_temp_add = new Inv_product_temporary;
            $pro_temp_add->inv_pro_temp_user_id = Auth::user()->au_id;
            $pro_temp_add->inv_pro_temp_pro_id = $product->inv_pro_det_id;
            $pro_temp_add->inv_pro_temp_pro_name = $productName;
            $pro_temp_add->inv_pro_temp_type_name = Inv_product_detail::get_type_name($product->inv_pro_det_id) ?? 'Unknown Type';
            $pro_temp_add->inv_pro_temp_qty = $request->pro_qty;
            $pro_temp_add->inv_pro_temp_unit_price = $request->pro_price ?? 0;
            $pro_temp_add->inv_pro_temp_exp_date = $request->exp_date ?? null;
            $pro_temp_add->inv_pro_temp_slno = '';
            $pro_temp_add->inv_pro_temp_deal_type = '2'; // 1=purchase, 2=sale
            $pro_temp_add->inv_pro_temp_status = 1;
            $pro_temp_add->inv_pro_temp_created_at = Carbon::now();
            $pro_temp_add->save();
        }

        return response()->json(['status' => 200, 'message' => 'Product added to cart']);
    } else {
        return response()->json(['status' => 404, 'message' => 'Product not found']);
    }
}
    public function addServiceCharges(Request $request)
    {
            $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', '')
                ->where('inv_pro_temp_deal_type',5)
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
                $pro_temp_add->inv_pro_temp_deal_type = '5';//5=Sell Service Charge
                $pro_temp_add->inv_pro_temp_status = 1;
                $pro_temp_add->inv_pro_temp_created_at = Carbon::now();

                $pro_temp_add->save();
            }


    }

 public function addToCartWarrentyProduct(Request $request)
{
    // First validate the request
    $request->validate([
        'pro_id' => 'required|integer',
        'pro_price' => 'required|numeric',
        'exp_date' => 'nullable|date'
    ]);

    // Get product with eager loading
    $product = Inv_product_detail::with('product')
        ->where('inv_pro_det_id', $request->pro_id)
        ->where('inv_pro_det_com_id', Auth::user()->au_company_id)
        ->where('inv_pro_det_branch_id', Auth::user()->au_branch_id)
        ->where('inv_pro_det_status', 1)
        ->where('inv_pro_det_pro_warranty', '!=', 0)
        ->first();

    if(empty($product)) {
        return response()->json(['status' => 404, 'message' => 'Product not found or not a warranty product']);
    }

    // Get available serial numbers
    $product_exist_slno = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', Auth::user()->au_company_id)
        ->where('inv_pro_invdet_branch_id', Auth::user()->au_branch_id)
        ->where('inv_pro_invdet_sell_id', NULL)
        ->where('inv_pro_invdet_pro_id', $product->inv_pro_det_id)
        ->where('inv_pro_invdet_sell_status', 0)
        ->get();

    $product_sl_no = [];
    $productName = $product->product->inv_pro_name ?? 'Unknown Product';
    $productTypeName = Inv_product_detail::get_type_name($product->inv_pro_det_id) ?? 'Unknown Type';

    // Find or create temporary record
    $row = Inv_product_temporary::firstOrNew([
        'inv_pro_temp_user_id' => Auth::user()->au_id,
        'inv_pro_temp_pro_id' => $request->pro_id,
        'inv_pro_temp_deal_type' => 2
    ]);

    // Update or set attributes
    $row->inv_pro_temp_pro_name = $productName;
    $row->inv_pro_temp_type_name = $productTypeName;
    $row->inv_pro_temp_unit_price = $request->pro_price;
    $row->inv_pro_temp_exp_date = $request->exp_date;
    $row->inv_pro_temp_updated_at = Carbon::now();
    
    // Set additional fields for new records
    if(!$row->exists) {
        $row->inv_pro_temp_qty = 0;
        $row->inv_pro_temp_slno = '';
        $row->inv_pro_temp_deal_type = '2';
        $row->inv_pro_temp_status = 1;
        $row->inv_pro_temp_type = 2;
        $row->inv_pro_temp_created_at = Carbon::now();
    }

    $row->save();

    // Get existing serial numbers if any
    if($row->inv_pro_temp_slno != '') {
        $product_sl_no = explode(',', $row->inv_pro_temp_slno);
    }

    return view('pages.ajax.warrenty_product_get_sl_no_inner_form', [
        'product' => $product,
        'product_sl_no' => $product_sl_no,
        'product_exist_slno' => $product_exist_slno
    ]);
}

    public function addWarrentyProductSlNo(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;
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
                ->where('inv_pro_inv_branch_id', $branch)
                            ->where('inv_pro_inv_project_id', $request->pro_id)
                            ->where('inv_pro_inv_deal_type', 1)
                            ->where('inv_pro_inv_tran_type', 1)
                            ->pluck('inv_pro_inv_id')
                            ->toArray();//deal_type=1=supplier, tran_type=1=buy-sale

            // search this sl no products
            $pro_inv_det = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', $com)
                 ->where('inv_pro_invdet_branch_id', $branch)
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
                ->whereIn('inv_pro_temp_deal_type',[2,5])
                ->delete();
    }

    public function updatecart(Request $request){
        $row = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
                ->where('inv_pro_temp_pro_id', $request->content_id)
                ->where('inv_pro_temp_deal_type',2)
                ->get();
        $row->inv_pro_temp_qty = $request->pro_qty;
        $row->inv_pro_temp_updated_at = Carbon::now();
        $row->save();
    }


    public function getCartContent()
    {
        // dd('a');
        Inv_product_temporary::where('inv_pro_temp_type',2)
                                ->where('inv_pro_temp_slno','')
                                ->delete();
        $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
        ->whereIn('inv_pro_temp_deal_type',[2,5])
        ->orderBy('inv_pro_temp_deal_type','asc')
        ->get();
        // dd($cart_content);

        return view('page.ajax.get_cart_content', compact('cart_content'));
    }




    // Submit cart form for sale
// Submit cart form for sale
public function cartSubmit(Request $request){
    $request->validate([
        'customer' => 'required'
    ]);

    DB::beginTransaction();
    try {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;
        $user_id = Auth::user()->au_id;
        
        // ===== GET DATE FROM REQUEST =====
        // Get the date from the hidden field in the invoice preview
        $invoiceDate = $request->input('inv_pro_temp_created_at');
        
        // If date is empty, use current date
        if (empty($invoiceDate)) {
            $invoiceDate = date('Y-m-d');
        }
        
        // Parse and validate the date
        try {
            $parsedDate = Carbon::parse($invoiceDate);
            $invoiceDate = $parsedDate->format('Y-m-d');
            $invoiceYearMonth = $parsedDate->format('Ym');
        } catch (\Exception $e) {
            // If parsing fails, use current date
            $now = Carbon::now();
            $invoiceDate = $now->format('Y-m-d');
            $invoiceYearMonth = $now->format('Ym');
            $parsedDate = $now;
        }
        
        // Log the date for debugging
        \Log::info('Cart Submit - Invoice Date', [
            'selected_date' => $invoiceDate,
            'year_month' => $invoiceYearMonth,
            'user_id' => $user_id
        ]);
        // ===== END DATE HANDLING =====
        
        // Load cart content with relationships
        $cart_content = Inv_product_temporary::with(['productDetail.product'])
            ->where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type', 2)
            ->get();

        // Sell service charge is not a stock item, so it can be sold on its own
        $service_charge_cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->where('inv_pro_temp_deal_type', 5)
            ->first();

        // Check if cart is empty
        if ($cart_content->isEmpty() && empty($service_charge_cart_content)) {
            DB::rollback();
            return redirect()->back()->with(['sub_err' => 'Cart is empty. Please add products or a sell service charge first.']);
        }

        // Generate invoice number with date-wise serial
        $new_memo_no = $this->generateInvoiceNumber($com, $invoiceYearMonth, $parsedDate->format('Ymd'));

        // Validate stock availability
        foreach ($cart_content as $content) {
            $product_id = $content->inv_pro_temp_pro_id;
            $req_qty = $content->inv_pro_temp_qty;

            $check_product = Inv_product_detail::where('inv_pro_det_id', $product_id)
                ->where('inv_pro_det_branch_id', $branch)
                ->where('inv_pro_det_com_id', $com)
                ->where('inv_pro_det_status', 1)
                ->where('inv_pro_det_available_qty', '>=', $req_qty)
                ->first();

            if(empty($check_product)) {
                DB::rollback();
                $productName = $content->inv_pro_temp_pro_name ?? 'Unknown Product';
                $msg = "Invalid/Insufficient ". $productName ." Product. error-code: 1006";
                return redirect()->back()->with(['sub_err' => $msg]);
            }
        }

        // Insert in product inventory table
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

            if(empty($check_product)) {
                DB::rollback();
                $productName = $content->inv_pro_temp_pro_name ?? 'Unknown Product';
                $msg = "Product not found: ". $productName .". error-code: 1007";
                return redirect()->back()->with(['sub_err' => $msg]);
            }

            // Get product name safely
            $productName = $content->inv_pro_temp_pro_name ?? 'Unknown Product';
            if(empty($productName) && $content->productDetail && $content->productDetail->product) {
                $productName = $content->productDetail->product->inv_pro_name ?? 'Unknown Product';
            }

            // Handle warranty vs non-warranty products
            if($check_product->inv_pro_det_pro_warranty == 0) {
                // Non-warranty product - just use quantity
                $req_qty = $content->inv_pro_temp_qty;
                $req_sl_ids = [];
            } else {
                // Warranty product - get serial numbers
                $req_sl_ids = !empty($content->inv_pro_temp_slno) ? explode(',', $content->inv_pro_temp_slno) : [];
                $new_req_sl_ids = array_filter($req_sl_ids, function($id) {
                    return !empty(trim($id));
                });
                
                if(empty($new_req_sl_ids)) {
                    DB::rollback();
                    $msg = "No serial numbers provided for warranty product: {$productName}. error-code: 1008";
                    return redirect()->back()->with(['sub_err' => $msg]);
                }
                
                $req_sl_ids = array_values($new_req_sl_ids);
                $req_qty = count($req_sl_ids);
            }

            // Create product inventory record with SELECTED DATE
            $product_inventory = new Inv_product_inventory();
            $product_inventory->inv_pro_inv_com_id = $com;
            $product_inventory->inv_pro_inv_branch_id = $branch;
            $product_inventory->inv_pro_inv_project_id = $content->inv_pro_temp_pro_id;
            $product_inventory->inv_employee_id = Auth::user()->au_id;
            $product_inventory->inv_pro_inv_party_id = $request->customer;
            $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
            $product_inventory->inv_pro_inv_qty = $req_qty;
            $product_inventory->inv_pro_inv_unit_price = $product_price;
            $product_inventory->inv_pro_inv_debit = $sub_total;
            $product_inventory->inv_pro_inv_credit = 0;
            $product_inventory->inv_pro_inv_issue_date = $parsedDate; // USE SELECTED DATE
            $product_inventory->inv_pro_inv_tran_desc = "Sell Product";
            $product_inventory->inv_pro_inv_deal_type = 2;
            $product_inventory->inv_pro_inv_tran_type = 1;
            $product_inventory->inv_pro_inv_status = 1;
            $product_inventory->inv_pro_inv_isproject = $request->is_project ?? 0;
            $product_inventory->inv_pro_inv_submit_by = $user_id;
            $product_inventory->inv_pro_inv_submit_at = Carbon::now();
            $product_inventory->save();

            // Save employee commission if applicable
            if (Auth::user()->au_user_type == 4) {
                $emp_com = new EmployeeComission();
                $emp_com->company_id = $com;
                $emp_com->emp_id = $request->employee;
                $emp_com->comission = $request->comission ?? 0;
                $emp_com->invoice_no = $new_memo_no;
                $emp_com->invoice_pro_qty = $req_qty;
                $emp_com->invoice_pro_price = $sub_total;
                $emp_com->created_at = Carbon::now();
                $emp_com->save();
            }

            // Handle warranty product serial numbers
            if($check_product->inv_pro_det_pro_warranty != 0) {
                for ($i = 0; $i < $req_qty; $i++) {
                    if (!isset($req_sl_ids[$i]) || empty(trim($req_sl_ids[$i]))) {
                        DB::rollback();
                        $msg = "Invalid serial number for product: {$productName}. error-code: 1003";
                        return redirect()->back()->with(['sub_err' => $msg]);
                    }

                    $request_sl_no = trim($req_sl_ids[$i]);
                    
                    $all_inv_ids_of_this_product = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                        ->where('inv_pro_inv_branch_id', $branch)
                        ->where('inv_pro_inv_project_id', $check_product->inv_pro_det_id)
                        ->where('inv_pro_inv_deal_type', 1)
                        ->where('inv_pro_inv_tran_type', 1)
                        ->pluck('inv_pro_inv_id')
                        ->toArray();

                    $pre_pro_inv_details = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', $com)
                        ->where('inv_pro_invdet_branch_id', $branch)
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
                        $msg = "Something went wrong to sell product. product name: {$productName}. Serial: {$request_sl_no}. error-code: 1002";
                        return redirect()->back()->with(['sub_err' => $msg]);
                    }
                }
            }

            // Update available quantity
            $after_submit_pro_available_quantity = $check_product->inv_pro_det_available_qty - $req_qty;
            $check_product->inv_pro_det_available_qty = $after_submit_pro_available_quantity;
            $check_product->save();
        }

        // Handle service charge with SELECTED DATE
        if (!empty($service_charge_cart_content)) {
            $product_inventory = new Inv_product_inventory();
            $debit = $service_charge_cart_content->inv_pro_temp_unit_price * $service_charge_cart_content->inv_pro_temp_qty;
            $product_inventory->inv_pro_inv_com_id = $com;
            $product_inventory->inv_pro_inv_branch_id = $branch;
            $product_inventory->inv_pro_inv_project_id = NULL;
            $product_inventory->inv_pro_inv_party_id = $request->customer;
            $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
            $product_inventory->inv_pro_inv_qty = $service_charge_cart_content->inv_pro_temp_qty;
            $product_inventory->inv_pro_inv_unit_price = $service_charge_cart_content->inv_pro_temp_unit_price;
            $product_inventory->inv_pro_inv_debit = $debit;
            $product_inventory->inv_pro_inv_credit = 0;
            $product_inventory->inv_pro_inv_issue_date = $parsedDate; // USE SELECTED DATE
            $product_inventory->inv_pro_inv_tran_desc = "Setup/Service/Install Charges";
            $product_inventory->inv_pro_inv_deal_type = 2;
            $product_inventory->inv_pro_inv_tran_type = 10;
            $product_inventory->inv_pro_inv_status = 1;
            $product_inventory->inv_pro_inv_submit_by = $user_id;
            $product_inventory->inv_pro_inv_submit_at = Carbon::now();
            $product_inventory->save();
        }

        // Handle delivery charge with SELECTED DATE
        if ($request->has('delivery') && $request->delivery > 0) {
            $product_inventory = new Inv_product_inventory();
            $product_inventory->inv_pro_inv_com_id = $com;
            $product_inventory->inv_pro_inv_branch_id = $branch;
            $product_inventory->inv_pro_inv_project_id = NULL;
            $product_inventory->inv_pro_inv_party_id = $request->customer;
            $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
            $product_inventory->inv_pro_inv_qty = 0;
            $product_inventory->inv_pro_inv_unit_price = $request->delivery;
            $product_inventory->inv_pro_inv_debit = $request->delivery;
            $product_inventory->inv_pro_inv_credit = 0;
            $product_inventory->inv_pro_inv_issue_date = $parsedDate; // USE SELECTED DATE
            $product_inventory->inv_pro_inv_tran_desc = "Sell Product Delivery Charge";
            $product_inventory->inv_pro_inv_deal_type = 2;
            $product_inventory->inv_pro_inv_tran_type = 11;
            $product_inventory->inv_pro_inv_status = 1;
            $product_inventory->inv_pro_inv_submit_by = $user_id;
            $product_inventory->inv_pro_inv_submit_at = Carbon::now();
            $product_inventory->save();
        }

        // Handle discount with SELECTED DATE
        if ($request->has('discount') && $request->discount > 0) {
            $product_inventory = new Inv_product_inventory();
            $product_inventory->inv_pro_inv_com_id = $com;
            $product_inventory->inv_pro_inv_branch_id = $branch;
            $product_inventory->inv_pro_inv_project_id = NULL;
            $product_inventory->inv_pro_inv_party_id = $request->customer;
            $product_inventory->inv_pro_inv_invoice_no = $new_memo_no;
            $product_inventory->inv_pro_inv_qty = 0;
            $product_inventory->inv_pro_inv_unit_price = $request->discount;
            $product_inventory->inv_pro_inv_debit = 0;
            $product_inventory->inv_pro_inv_credit = $request->discount;
            $product_inventory->inv_pro_inv_issue_date = $parsedDate; // USE SELECTED DATE
            $product_inventory->inv_pro_inv_tran_desc = "Sell Product Discount";
            $product_inventory->inv_pro_inv_deal_type = 2;
            $product_inventory->inv_pro_inv_tran_type = 12;
            $product_inventory->inv_pro_inv_status = 1;
            $product_inventory->inv_pro_inv_submit_by = $user_id;
            $product_inventory->inv_pro_inv_submit_at = Carbon::now();
            $product_inventory->save();
        }

        // Clear cart
        Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
            ->whereIn('inv_pro_temp_deal_type', [2, 5])
            ->delete();

        DB::commit();
        
        $msg = "Sell Products Successfully completed";
        return redirect()->route('buy.pro_sell')->with([
            'sub_success' => $msg,
            'print_invoice' => $new_memo_no
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        \Log::error('Cart submit error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        $msg = "Something went wrong to sell product. error-code: 1010 - " . $e->getMessage();
        return redirect()->back()->with(['sub_err' => $msg]);
    }
}

/**
 * Generate invoice number with date-wise serial
 * Format: IGLYYYYMMSERIAL (4 digits)
 * Example: IGL2025080001 for August 2025
 * Company 445001 uses FTYYYYMMDD + 3 random digits instead (e.g. FT20251028472)
 */
private function generateInvoiceNumber($companyId, $yearMonth, $date = null)
{
    if ((int) $companyId === 445001) {
        return $this->generateFtInvoiceNumber($companyId, $date ?: date('Ymd'));
    }

    // Get the last invoice for this company and year-month
    // (tran type 10 too, so a service-charge-only invoice number is not reused)
    $lastInvoice = Inv_product_inventory::where('inv_pro_inv_com_id', $companyId)
        ->where('inv_pro_inv_deal_type', 2)
        ->whereIn('inv_pro_inv_tran_type', [1, 10])
        ->where('inv_pro_inv_invoice_no', 'like', 'IGL' . $yearMonth . '%')
        ->orderBy('inv_pro_inv_id', 'DESC')
        ->first();

    // Log the search for debugging
    \Log::info('Invoice Number Generation', [
        'company_id' => $companyId,
        'year_month' => $yearMonth,
        'search_pattern' => 'IGL' . $yearMonth . '%',
        'last_invoice_found' => $lastInvoice ? $lastInvoice->inv_pro_inv_invoice_no : 'none'
    ]);

    if ($lastInvoice && !empty($lastInvoice->inv_pro_inv_invoice_no)) {
        // Extract the serial number from the last invoice
        $invoiceNo = $lastInvoice->inv_pro_inv_invoice_no;
        $prefix = 'IGL' . $yearMonth;
        
        // Get the serial part (after the prefix)
        $serialPart = substr($invoiceNo, strlen($prefix));
        
        // If serial part is numeric, increment it
        if (is_numeric($serialPart)) {
            $nextSerial = intval($serialPart) + 1;
            $nextSerial = str_pad($nextSerial, 4, '0', STR_PAD_LEFT);
            return $prefix . $nextSerial;
        }
    }

    // If no previous invoice found for this month, start from 0001
    return 'IGL' . $yearMonth . '0001';
}

/**
 * Format: FTYYYYMMDD + 3 random digits, never repeating an invoice number already used that day
 * Example: FT20251028472
 */
private function generateFtInvoiceNumber($companyId, $date)
{
    $prefix = 'FT' . $date;

    $used = Inv_product_inventory::where('inv_pro_inv_com_id', $companyId)
        ->where('inv_pro_inv_invoice_no', 'like', $prefix . '%')
        ->pluck('inv_pro_inv_invoice_no')
        ->map(function ($no) use ($prefix) { return substr($no, strlen($prefix)); })
        ->all();

    $free = array_values(array_diff(
        array_map(function ($n) { return str_pad($n, 3, '0', STR_PAD_LEFT); }, range(0, 999)),
        $used
    ));

    if (empty($free)) {
        throw new \Exception('All 1000 FT invoice numbers for ' . $date . ' are used.');
    }

    return $prefix . $free[random_int(0, count($free) - 1)];
}

public function invTemporaryProduct(Request $request){
    $user = Auth::user()->au_id;
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;
    
    // Get the date from request
    $invoiceDate = $request->input('inv_pro_temp_created_at');
    if (empty($invoiceDate)) {
        $invoiceDate = date('Y-m-d');
    }
    
    // Store date in session for later use
    session(['invoice_date' => $invoiceDate]);
    
    // Load relationships properly
    $pro_temps = Inv_product_temporary::with([
        'pro_warranty',
        'sold_by',
        'productDetail',
        'productDetail.product'
    ])
    ->where('inv_pro_temp_user_id', $user)
    ->whereIn('inv_pro_temp_deal_type', [2, 5])
    ->orderBy('inv_pro_temp_deal_type', 'asc')
    ->get();

    // Update missing product names in temporary table
    foreach ($pro_temps as $temp) {
        if (empty($temp->inv_pro_temp_pro_name) && $temp->productDetail && $temp->productDetail->product) {
            $temp->inv_pro_temp_pro_name = $temp->productDetail->product->inv_pro_name;
            $temp->save();
        }
    }

    $emp = Admin_user::where('au_id', $request->employee)->first();
    $delivery = $request->delivery ?? 0;
    $discount = $request->discount ?? 0;
    
    $pro_cus = Inv_customer::where('inv_cus_com_id', $com)
        ->where('inv_cus_branch_id', $branch)
        ->where('inv_cus_id', $request->customer)
        ->first();

    return view('inventory.product_inventory.product_sell_invoice', compact(
        'pro_temps',
        'pro_cus',
        'delivery',
        'discount',
        'emp',
        'invoiceDate'  // Pass the date to the view
    ));
}
}

<?php

namespace App\Http\Controllers\Inventory;

use Illuminate\Support\Facades\Auth;
use Cart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Models\Inv_supplier;
use App\Models\Inv_product_detail;
use App\Models\Inv_product;
use App\Models\Admin_user;
use App\Models\Inv_customer;
use App\Models\Inv_product_type;
use App\Models\Inv_product_groups;
use App\Models\Inv_product_inventory;
use App\Models\Inv_supplier_inventory;
use App\Models\Inv_product_temporary;
use App\Models\Inv_product_inventory_detail;
use Illuminate\Support\Facades\DB;

class ProductInventoryController extends Controller
{
    //
public function buy_product_add(){
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    $suppliers = Inv_supplier::where('inv_sup_com_id', $com)
                             ->where('inv_sup_status', 1)
                             ->orderBy('inv_sup_com_name', 'ASC')
                             ->get();

    $products = Inv_product_detail::where('inv_pro_det_com_id', $com)
                                  ->where('inv_pro_det_branch_id', $branch)
                                  ->where('inv_pro_det_status', 1)
                                  ->orderBy('inv_pro_det_pro_id', 'ASC')
                                  ->get();

    return view('inventory.product_inventory.add', compact('suppliers', 'products'));
}
public function buy_product_new()
{
    $com    = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    $suppliers = Inv_supplier::where('inv_sup_com_id', $com)
        ->where('inv_sup_status', 1)
        ->orderBy('inv_sup_com_name', 'ASC')
        ->get();

    $sell_pro = Inv_product_detail::select([
            'inv_pro_det_id',
            'inv_pro_det_pro_id',
            'inv_pro_det_type_id',
            'inv_pro_det_available_qty',
            'inv_pro_det_buy_price',
            'inv_pro_det_pro_warranty',
        ])
        ->where('inv_pro_det_com_id', $com)
        ->where('inv_pro_det_branch_id', $branch)
        ->where('inv_pro_det_status', 1)
        ->with([
            'product',
            'product.type_info',
            'type_info',
        ])
        ->get()
        ->map(function ($detail) {
            return (object)[
                'inv_pro_det_id'           => $detail->inv_pro_det_id,
                'inv_pro_id'               => $detail->inv_pro_det_pro_id,
                'inv_pro_name'             => $detail->product->inv_pro_name ?? 'N/A',
                'inv_pro_description'      => $detail->product->inv_pro_description ?? 'N/A',
                'type_name'                => $detail->type_info->inv_pro_type_name
                                              ?? $detail->product->type_info->inv_pro_type_name
                                              ?? 'N/A',
                'branch_quantity'          => $detail->inv_pro_det_available_qty,
                'inv_pro_det_buy_price'    => $detail->inv_pro_det_buy_price,
                'inv_pro_det_pro_warranty' => $detail->inv_pro_det_pro_warranty,
            ];
        });

    $customers = Inv_customer::where('inv_cus_com_id', $com)
        ->where('inv_cus_branch_id', $branch)
        ->where('inv_cus_status', 1)
        ->get();

    return view('page.product_inventory.add_product',
        compact('sell_pro', 'customers', 'suppliers'));
}
    public function buy_product_submit(Request $request){
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;
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
                                    ->where('inv_pro_det_branch_id',$branch)
                                    ->where('inv_pro_det_id',Input::get('product'))
                                    ->first();
        if (!empty($pro_det)) {
            $pro_det->inv_pro_det_available_qty = $pro_det->inv_pro_det_available_qty + Input::get('qty');
            $pro_det->save();
            $pro_buy_price = $pro_det->inv_pro_det_buy_price;
            $total_purchase_price = $pro_buy_price * Input::get('qty');

            $pro_inv = new Inv_product_inventory;
            $pro_inv->inv_pro_inv_com_id = $com;
            $pro_inv->inv_pro_inv_branch_id = $branch;
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
        }else {
            return redirect()->back()->with(['invalid' => 'Invalid Product']);
        }


        return redirect()->back()->with(['pur_pro' => 'Product Purchases Successfully']);
    }

    /**
     * View one purchase from the purchase list - opens its full invoice.
     */
    public function view_product_purchase($id)
    {
        $purchase = Inv_product_inventory::where('inv_pro_inv_com_id', Auth::user()->au_company_id)
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_tran_type', 1)
            ->where('inv_pro_inv_id', $id)
            ->firstOrFail();

        return redirect(route('reports.buy-pdf', ['invoice' => $purchase->inv_pro_inv_invoice_no]) . '?view=1');
    }

    /**
     * Edit one purchase line from the purchase list.
     */
    public function edit_product_purchase($id)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $purchase = Inv_product_inventory::with(['getSupplierInfo', 'product_detail.product'])
            ->where('inv_pro_inv_com_id', $com)
            ->where('inv_pro_inv_branch_id', $branch)
            ->where('inv_pro_inv_deal_type', 1)
            ->where('inv_pro_inv_tran_type', 1)
            ->where('inv_pro_inv_id', $id)
            ->firstOrFail();

        $suppliers = Inv_supplier::where('inv_sup_com_id', $com)
            ->where('inv_sup_status', 1)
            ->orderBy('inv_sup_com_name', 'ASC')
            ->get();

        $products = Inv_product_detail::with(['product', 'type_info'])
            ->where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_branch_id', $branch)
            ->where('inv_pro_det_status', 1)
            ->get();

        $serial_numbers = Inv_product_inventory::ProductSerial($purchase->inv_pro_inv_id);

        return view('page.product_inventory.edit_purchase', compact(
            'purchase',
            'products',
            'suppliers',
            'serial_numbers'
        ));
    }

    /**
     * Update one purchase line: adjusts stock, amount and serial numbers.
     */
    public function update_product_purchase(Request $request, $id)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;
        $submit_by = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');

        $request->validate([
            'supplier'   => 'required',
            'product'    => 'required',
            'issue'      => 'required|date',
            'qty'        => 'required|integer|min:1',
            'short_qty'  => 'nullable|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $purchase = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_branch_id', $branch)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 1)
                ->where('inv_pro_inv_id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            $new_det = Inv_product_detail::where('inv_pro_det_com_id', $com)
                ->where('inv_pro_det_branch_id', $branch)
                ->where('inv_pro_det_id', $request->product)
                ->firstOrFail();

            $total_qty = (int) $request->qty;
            $short_qty = (int) $request->short_qty;
            if ($short_qty > $total_qty) {
                throw new \Exception('Short qty cannot be more than total qty');
            }
            $new_qty = $total_qty - $short_qty;   // qty actually received into stock
            $old_qty = (int) $purchase->inv_pro_inv_qty;

            // Serial numbers (warranty products)
            $serials = [];
            if ($new_det->inv_pro_det_pro_warranty != 0) {
                $serials = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $request->serials))));
                if (count($serials) != $total_qty) {
                    throw new \Exception('Please enter ' . $total_qty . ' serial numbers (found ' . count($serials) . ')');
                }
            }

            $sold_serials = Inv_product_inventory_detail::where('inv_pro_invdet_proinv_id', $id)
                ->where('inv_pro_invdet_sell_status', 1)
                ->count();
            if ($sold_serials > 0) {
                throw new \Exception('Cannot edit this purchase - some of its serial numbers are already sold');
            }

            // Stock: take the old qty back out, then add the new qty
            $old_det = Inv_product_detail::where('inv_pro_det_id', $purchase->inv_pro_inv_project_id)->first();
            if ($old_det && $old_det->inv_pro_det_id == $new_det->inv_pro_det_id) {
                $new_det->inv_pro_det_available_qty = $new_det->inv_pro_det_available_qty - $old_qty + $new_qty;
                if ($new_det->inv_pro_det_available_qty < 0) {
                    throw new \Exception('Cannot reduce qty - stock would become negative');
                }
                $new_det->save();
            } else {
                if ($old_det) {
                    $old_det->inv_pro_det_available_qty = $old_det->inv_pro_det_available_qty - $old_qty;
                    if ($old_det->inv_pro_det_available_qty < 0) {
                        throw new \Exception('Cannot change product - stock of the old product would become negative');
                    }
                    $old_det->save();
                }
                $new_det->inv_pro_det_available_qty = $new_det->inv_pro_det_available_qty + $new_qty;
                $new_det->save();
            }

            $purchase->inv_pro_inv_project_id    = $new_det->inv_pro_det_id;
            $purchase->inv_pro_inv_party_id      = $request->supplier;
            $purchase->inv_pro_inv_issue_date    = $request->issue;
            $purchase->inv_pro_inv_total_qty     = $total_qty;
            $purchase->inv_pro_inv_short_qty     = $short_qty;
            $purchase->inv_pro_inv_short_remarks = $request->short_remarks;
            $purchase->inv_pro_inv_qty           = $new_qty;
            $purchase->inv_pro_inv_unit_price    = $request->unit_price;
            $purchase->inv_pro_inv_debit         = $total_qty * $request->unit_price;
            $purchase->inv_pro_inv_exp_date      = $request->expired;
            $purchase->inv_pro_inv_edit_count    = (int) $purchase->inv_pro_inv_edit_count + 1;
            $purchase->inv_pro_inv_update_by     = $submit_by;
            $purchase->inv_pro_inv_update_at     = $submit_at;
            $purchase->save();

            // Replace serial rows
            Inv_product_inventory_detail::where('inv_pro_invdet_proinv_id', $id)->delete();
            foreach ($serials as $slno) {
                $pro_inv_det = new Inv_product_inventory_detail();
                $pro_inv_det->inv_pro_invdet_com_id      = $com;
                $pro_inv_det->inv_pro_invdet_branch_id   = $branch;
                $pro_inv_det->inv_pro_invdet_proinv_id   = $id;
                $pro_inv_det->inv_pro_invdet_pro_id      = $new_det->inv_pro_det_id;
                $pro_inv_det->inv_pro_invdet_buy_id      = $request->supplier;
                $pro_inv_det->inv_pro_invdet_slno        = $slno;
                $pro_inv_det->inv_pro_invdet_sell_status = 0;
                $pro_inv_det->inv_pro_invdet_status      = 1;
                $pro_inv_det->inv_pro_invdet_submit_by   = $submit_by;
                $pro_inv_det->inv_pro_invdet_submit_at   = $submit_at;
                $pro_inv_det->save();
            }

            DB::commit();

            return redirect()->route('buy.purchase.list')
                ->with('msg', 'Purchase updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('errmsg', 'Error updating purchase: ' . $e->getMessage());
        }
    }
public function delete_product_purchase($id)
{
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    DB::beginTransaction();

    try {
        $purchase = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
            ->where('inv_pro_inv_branch_id', $branch)
            ->where('inv_pro_inv_id', $id)
            ->firstOrFail();

        // Check if this purchase has any associated sales (returns or other transactions)
        $has_associated_records = Inv_product_inventory::where('inv_pro_inv_sell_ref', $id)->exists();
        if ($has_associated_records) {
            throw new \Exception('Cannot delete purchase - it has associated transaction records');
        }

        // Get product detail to adjust quantity
        $pro_det = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_branch_id', $branch)
            ->where('inv_pro_det_id', $purchase->inv_pro_inv_project_id)  // Changed from inv_pro_inv_prodet_id
            ->first();

        if ($pro_det) {
            // Reduce the quantity only if it won't go negative
            $new_quantity = $pro_det->inv_pro_det_available_qty - $purchase->inv_pro_inv_qty;
            if ($new_quantity >= 0) {
                $pro_det->inv_pro_det_available_qty = $new_quantity;
                $pro_det->save();
            } else {
                throw new \Exception('Cannot delete purchase - would result in negative inventory');
            }
        }

        // Delete serial numbers if any
        Inv_product_inventory_detail::where('inv_pro_invdet_proinv_id', $id)->delete();

        // Finally delete the purchase record
        $purchase->delete();

        DB::commit();

        return redirect()->route('buy.purchase.list')
            ->with('success', 'Purchase deleted successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('buy.purchase.list')
            ->with('error', $e->getMessage())
            ->withInput();
    }
}
public function product_purchase_list()
{
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    $purchases = Inv_product_inventory::with([
            'getSupplierInfo',
            'product_detail' => function($query) {
                $query->with('product');
            }
        ])
        ->where('inv_pro_inv_com_id', $com)
        ->where('inv_pro_inv_branch_id', $branch)
        ->where('inv_pro_inv_deal_type', 1) // Purchase transactions
        ->where('inv_pro_inv_tran_type', 1) // Buy product type
        ->orderBy('inv_pro_inv_issue_date', 'DESC')
        ->orderBy('inv_pro_inv_id', 'DESC')
        ->get();

    return view('page.product_inventory.purchase_list', compact('purchases'));
}
    public function available_list(){
        $com = Auth::user()->au_company_id;
           $branch = Auth::user()->au_branch_id;
        $available = Inv_product_inventory_detail::where('inv_pro_invdet_com_id',$com)
          ->where('inv_pro_det_branch_id',$branch)
                                                 ->get();
        return view('inventory.product_inventory.available');
    }

   public function sell_product(Request $request){
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;
    $customers = Inv_customer::where('inv_cus_com_id',$com)
                             ->where('inv_cus_branch_id',$branch)
                             ->where('inv_cus_status',1)
                             ->get();

    $groups = Inv_product_groups::where('inv_pro_grp_com_id',$com)
                                // ->where('inv_pro_grp_branch_id',$branch)
                                ->where('inv_pro_grp_status',1)
                                ->get();

    $employees = Admin_user::where('au_company_id',$com)
                           ->where('au_branch_id',$branch)
                           ->whereIn('au_user_type',[5,6])
                           ->get();

    if ($request->has('searchbtn')) {
        $sell_pro = Inv_product_detail::where('inv_pro_det_com_id',$com)
                                      ->where('inv_pro_det_branch_id',$branch)
                                      ->where('inv_pro_det_type_id',$request->type)
                                      ->where('inv_pro_det_status',1)
                                      ->get();
    }else{
        $sell_pro = Inv_product_detail::where('inv_pro_det_com_id',$com)
                                      ->where('inv_pro_det_branch_id',$branch)
                                      ->where('inv_pro_det_status',1)
                                      ->get();
    }

    $cart_content = Inv_product_temporary::where('inv_pro_temp_user_id', Auth::user()->au_id)
        ->whereIn('inv_pro_temp_deal_type',[2,5])
        ->orderBy('inv_pro_temp_deal_type','asc')
        ->get();
    $total = 0;
    foreach ($cart_content as $content) {
        $total += $content->inv_pro_temp_unit_price * $content->inv_pro_temp_qty;
    }

    // Get the date from session if exists, otherwise use today
    $invoiceDate = session('invoice_date', date('Y-m-d'));

    return view('page.product_inventory.sell_product', compact(
        'sell_pro', 
        'customers', 
        'groups', 
        'total', 
        'employees',
        'invoiceDate'  // Pass the date to view
    ));
}
    public function show_pro_type_ajax(Request $request) {
        $types = Inv_product_type::where('inv_pro_type_grp_id', $request->grp_id)
                                ->where('inv_pro_type_status',1)
                                ->get();
        return view('page.ajax.sell_product_model_ajax', compact('types'));
    }

public function product_search_ajax(Request $request)
{
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    $sell_product = Inv_product_detail::with('product')
        ->where('inv_pro_det_com_id', $com)
        ->where('inv_pro_det_branch_id', $branch)
        ->where('inv_pro_det_type_id', $request->type_id)
        ->where('inv_pro_det_status', 1)
        ->get();

    return view('pages.ajax.product_search_sell_ajax', compact('sell_product'));
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
            $branch = Auth::user()->au_branch_id;
            $submit_at = Carbon::now()->format('Y-m-d H:i:s');
            $submit_by = Auth::user()->au_id;
        try {
            $damage = new Inv_product_inventory;
            $damage->inv_pro_inv_com_id = $com;
             $damage->inv_pro_inv_branch_id = $branch;
            $damage->inv_pro_inv_id = Input::get('product');
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
          $branch = Auth::user()->au_branch_id;
        $damage_list = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
        ->where('inv_pro_inv_branch_id',$branch)
                                         ->where('inv_pro_inv_deal_type',2)
                                         ->where('inv_pro_inv_damage_status',1)
                                         ->where('inv_pro_inv_tran_type',1)
                                         ->get();
        return view('inventory.damage.list',compact('damage_list'));
    }

}

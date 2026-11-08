<?php

namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Inv_product_groups;
use App\Models\Inv_product_type;
use App\Models\Inv_product_detail;
use App\Models\Inv_product_inventory_detail;
use App\Models\Inv_product_inventory;

use App\Models\Inv_product;
use App\Models\Inv_supplier;

class InvProductController extends Controller
{
    //
    public function product_add(){
        $com = Auth::user()->au_company_id;
        $pro_grp = Inv_product_groups::where('inv_pro_grp_com_id',$com)
                                         ->where('inv_pro_grp_status',1)
                                         ->get();
        $suppliers = Inv_supplier::where('inv_sup_com_id', $com)
                                        ->where('inv_sup_status', 1)
                                        ->where('inv_sup_type',3)
                                        ->orderBy('inv_sup_com_name','ASC')
                                        ->get();
        return view('inventory.product.add',compact('pro_grp', 'suppliers'));
    }
    
    public function check_unique_product(Request $request) {
        try {
            $com = Auth::user()->au_company_id;
            $product_name = $request->input('pro_name');
    
            if (!$product_name) {
                throw new \Exception('Product name is required.');
            }
    
            $exists = Inv_product_detail::where('inv_pro_det_com_id', $com)
                                        ->where('inv_pro_det_pro_id', $product_name)
                                        ->exists();
    
            return response()->json(['exists' => $exists]);
    
        } catch (\Exception $e) {
            Log::error('Error checking unique product: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

   public function product_detail_submit(Request $request)
{
    $message = [
        'pro_desc.max' => 'Description must be a maximum of 50 characters long',
    ];

    $request->validate([
        'type' => 'required',
        'pro_name' => 'required',
        'pro_buy' => 'required|numeric',
        'pro_sell' => 'required|numeric',
        'pro_desc' => 'max:50',
    ], $message);

    try {
        $submit_by = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        // 1. First, insert into inv_product table
        $product = new \App\Models\Inv_product();
        $product->inv_pro_com_id = $com;
        $product->inv_pro_type_id = $request->input('type');
        $product->inv_pro_sup = implode('-', $request->input('supplier'));
        $product->inv_pro_name = $request->input('pro_name');
        $product->inv_pro_buy_price = $request->input('pro_buy');
        $product->inv_pro_sell_price = $request->input('pro_sell');
        $product->inv_pro_warranty = $request->input('pro_warranty');
        $product->inv_pro_description = $request->input('pro_desc');
        $product->inv_pro_status = 1;
        $product->save();

        // 2. Then, insert into inv_product_details table using the product ID
        $inv_pro_det = new \App\Models\Inv_product_detail();
        $inv_pro_det->inv_pro_det_com_id = $com;
        $inv_pro_det->inv_pro_det_branch_id = $branch;
        $inv_pro_det->inv_pro_det_type_id = $request->input('type');
        $inv_pro_det->inv_pro_det_sup = implode('-', $request->input('supplier'));
        $inv_pro_det->inv_pro_det_pro_id = $product->inv_pro_id; // FK from above
        $inv_pro_det->inv_pro_det_buy_price = $request->input('pro_buy');
        $inv_pro_det->inv_pro_det_sell_price = $request->input('pro_sell');
        $inv_pro_det->inv_pro_det_pro_warranty = $request->input('pro_warranty');
 
        $inv_pro_det->inv_pro_det_short_qty = $request->input('pro_short');
        $inv_pro_det->inv_pro_det_status = 1;
        $inv_pro_det->inv_pro_det_submit_by = $submit_by;
        $inv_pro_det->inv_pro_det_submit_at = $submit_at;
        $inv_pro_det->save();

        return redirect()->back()->with(['det_add' => 'Product Detail Added Successfully']);

    } catch (\Exception $e) {
        \Log::error('Product submission error: ' . $e->getMessage());
        return redirect()->back()->with(['err' => 'Something went wrong. ' . $e->getMessage()]);
    }
}

    
    public function show_pro_grp_by_ajax(Request $request) {
        $types = Inv_product_type::where('inv_pro_type_grp_id', $request->grp_id)
                                ->where('inv_pro_type_status',1)
                                ->get();
        return view('pages.ajax.product_group', compact('types'));
    }

   public function product_list()
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $pro_det = Inv_product::where('inv_pro_com_id', $com)
            ->where('inv_pro_status', 1)
            ->with([
                'type_info',
                'details' => function ($q) use ($com, $branch) {
                    $q->where('inv_pro_det_com_id', $com)
                      ->where('inv_pro_det_status', 1)
                      ->where('inv_pro_det_branch_id', $branch);
                }
            ])
            ->get();

        return view('inventory.product.list', compact('pro_det'));
    }

    public function product_detail_show($id)
    {
        $details = Inv_product_detail::where('inv_pro_det_id', $id)->first();
        return view('inventory.product.product_detail', compact('details'));
    }

public function product_delete(Request $request, $id)
{
    // Find product detail by ID
    $pro_detail = Inv_product_detail::find($id);

    if ($pro_detail) {
        // Soft delete product detail
        $pro_detail->inv_pro_det_status = 0;
        $pro_detail->save();

        // Find the main product by related ID
        $product = Inv_product::where('inv_pro_id', $pro_detail->inv_pro_det_pro_id)->first();

        if ($product) {
            // Soft delete product
            $product->inv_pro_status = 0;
            $product->save();
        }

        return redirect()->back()->with(['pro_del' => 'Product deleted successfully']);
    }

    return redirect()->back()->with(['pro_del' => 'Product not found']);
}

public function product_edit_page($id)
{
    $com = Auth::user()->au_company_id;

    // $id is a DETAIL ID (from the route)
    $product_details = Inv_product_detail::where('inv_pro_det_id', $id)->first();

    if (!$product_details) {
        return redirect()->back()->with('error', 'Product detail not found.');
    }

    // Find the PARENT product via the FK
    $product = Inv_product::where('inv_pro_id', $product_details->inv_pro_det_pro_id)->first();

    if (!$product) {
        return redirect()->back()->with('error', 'Product not found.');
    }

    $pro_det_grp = Inv_product_groups::where('inv_pro_grp_com_id', $com)
                                     ->where('inv_pro_grp_status', 1)
                                     ->get();

    $types = Inv_product_type::where('inv_pro_type_com_id', $com)
                              ->where('inv_pro_type_grp_id', optional($product->type_info)['inv_pro_type_grp_id'])
                              ->where('inv_pro_type_status', 1)
                              ->get();

    $suppliers = Inv_supplier::where('inv_sup_com_id', $com)
                              ->where('inv_sup_status', 1)
                              ->get();

    return view('inventory.product.pro_edit', compact('pro_det_grp', 'types', 'product_details', 'product', 'suppliers'));
}


public function pro_edit(Request $request, $id)
{
    $message = [
        'pro_desc.max'               => 'Description must be a maximum of 50 characters long',
        'warranty_days.required_if'  => 'Warranty days is required when warranty is selected',
    ];

    $request->validate([
        'type'          => 'required',
        'pro_name'      => 'required',
        'pro_buy'       => 'required|numeric',
        'pro_sell'      => 'required|numeric',
        'pro_desc'      => 'max:50',
        'warranty_days' => 'required_if:has_warranty,1|numeric|min:0',
    ], $message);

    try {
        $update_by = Auth::user()->au_id;
        $update_at = Carbon::now()->format('Y-m-d H:i:s');
        $com       = Auth::user()->au_company_id;
        $branch    = Auth::user()->au_branch_id;

        // $id is the DETAIL ID
        $productDetail = \App\Models\Inv_product_detail::findOrFail($id);
        $product       = \App\Models\Inv_product::findOrFail($productDetail->inv_pro_det_pro_id);

        $warranty = ($request->has_warranty == 1) ? $request->warranty_days : 0;
        $supplier = $request->supplier ?? [];
        $supplierStr = is_array($supplier) ? implode('-', $supplier) : $supplier;

        // Update parent product
        $product->update([
            'inv_pro_com_id'      => $com,
            'inv_pro_type_id'     => $request->type,
            'inv_pro_sup'         => $supplierStr,
            'inv_pro_name'        => $request->pro_name,
            'inv_pro_buy_price'   => $request->pro_buy,
            'inv_pro_sell_price'  => $request->pro_sell,
            'inv_pro_warranty'    => $warranty,
            'inv_pro_description' => $request->pro_desc,
        ]);

        // Update product detail
        $productDetail->update([
            'inv_pro_det_com_id'        => $com,
            'inv_pro_det_branch_id'     => $branch,
            'inv_pro_det_type_id'       => $request->type,
            'inv_pro_det_sup'           => $supplierStr,
            'inv_pro_det_pro_id'        => $product->inv_pro_id,
            'inv_pro_det_buy_price'     => $request->pro_buy,
            'inv_pro_det_sell_price'    => $request->pro_sell,
            'inv_pro_det_pro_warranty'  => $warranty,
            'inv_pro_det_short_qty'     => $request->pro_short ?? 0,
            'inv_pro_det_update_by'     => $update_by,
            'inv_pro_det_update_at'     => $update_at,
        ]);

        return redirect()->route('inventory.product_list')
                         ->with(['det_up' => 'Product Detail Updated Successfully']);

    } catch (\Exception $e) {
        \Log::error('Product update error: ' . $e->getMessage());
        return redirect()->back()->with(['err' => 'Something went wrong. ' . $e->getMessage()]);
    }
}
    public function product_group(){
        return view('inventory.product_group.add');
    }

    public function product_group_submit(Request $request){
        $company = Auth::user()->au_company_id;
        $submit_by = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');

        $request->validate([
            'type' => 'required',
        ]);

        $table = new Inv_product_groups;
        $table->inv_pro_grp_com_id = $company;
        $table->inv_pro_grp_name = Input::get('type');
        $table->inv_pro_grp_status = 1;
        $table->inv_pro_grp_submit_by = $submit_by;
        $table->inv_pro_grp_submit_at = $submit_at;
        $table->save();

        return redirect()->back()->with(['type' => 'Product Type Added Successfully.']);
    }

    public function product_group_list(){
        $company_id = Auth::user()->au_company_id;
        $types = Inv_product_groups::where('inv_pro_grp_com_id',$company_id)
                                        ->get();
        return view('inventory.product_group.list',compact('types'));
    }

    public function product_group_show($id){
        $company = Auth::user()->au_company_id;
        $pro_grp_info = Inv_product_groups::where('inv_pro_grp_com_id',$company)
                                              ->where('inv_pro_grp_id',$id)
                                              ->first();
        return view('inventory.product_group.pro_grp_show',compact('pro_grp_info'));
    }

    public function pro_grp_del(Request $request,$id){
        $pro_grp_delete = Inv_product_groups::find($id);
        $pro_grp_delete->inv_pro_grp_status = 0;
        $pro_grp_delete->save();
        return redirect()->back()->with(['del_grp' => 'Deletd Successfully']);
    }

    public function pro_grp_edit_page($id){
        $com = Auth::user()->au_company_id;
        $pro_grp = Inv_product_groups::where('inv_pro_grp_id',$id)->first();
        return view('inventory.product_group.pro_grp_edit',compact('pro_grp'));
    }

    public function pro_grp_edit(Request $request,$id){
        $pro_grp_edit = Inv_product_groups::find($id);
        $update_by = Auth::user()->au_id;
        $update_at = Carbon::now()->format('Y-m-d H:i:s');
        $request->validate([
            'type' => 'required',
        ]);
        $pro_grp_edit->inv_pro_grp_name = $request->input('type');
        $pro_grp_edit->inv_pro_grp_status = $request->input('status');
        $pro_grp_edit->inv_pro_grp_updated_at = $update_at;
        $pro_grp_edit->inv_pro_grp_updated_by = $update_by;
        $pro_grp_edit->save();
        return redirect()->route('inventory.group_list')->with(['up_msg' => 'Updated Successfully']);
    }

    public function product_type_add(){
        $com = Auth::user()->au_company_id;
        $pro_grp = Inv_product_groups::where('inv_pro_grp_com_id',$com)
                                         ->where('inv_pro_grp_status',1)
                                         ->get();
        return view('inventory.type.add',compact('pro_grp'));
    }

    public function product_type_submit(Request $request){
        $company = Auth::user()->au_company_id;
        $submit_by = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');
        $table = new Inv_product_type;
        $request->validate([
            'group' => 'required',
            'type' => 'required|unique:inv_product_types,inv_pro_type_name',
        ]);
        $table->inv_pro_type_com_id = $company;
        $table->inv_pro_type_grp_id = Input::get('group');
        $table->inv_pro_type_name = Input::get('type');
        $table->inv_pro_type_status = 1;
        $table->inv_pro_type_submit_by = $submit_by;
        $table->inv_pro_type_submit_at = $submit_at;
        $table->save();

        return redirect()->back()->with(['pro_type' => 'Product Type Added Successfully']);
    }

    public function product_type_list(){
        $com = Auth::user()->au_company_id;
        $pro_type = Inv_product_type::where('inv_pro_type_com_id',$com)
                                      ->where('inv_pro_type_status',1)
                                      ->get();
        return view('inventory.type.list',compact('pro_type'));
    }

    public function product_type_show($id){
        $type_info = Inv_product_type::where('inv_pro_type_id',$id)->first();
        return view('inventory.type.type_detail',compact('type_info'));
    }

    public function product_type_delete(Request $request,$id){
        $type_del = Inv_product_type::find($id);
        $type_del->inv_pro_type_status = 0;
        $type_del->save();
        return redirect()->route('inventory.type_list')->with(['delete_msg' => 'Product type Deleted Successfully']);
    }

    public function pro_type_edit_page($id){
        $com = Auth::user()->au_company_id;
        $mod_edit = Inv_product_type::where('inv_pro_type_id',$id)->first();
        $pro_grp = Inv_product_groups::where('inv_pro_grp_com_id',$com)
                                         ->where('inv_pro_grp_status',1)
                                         ->get();
        return view('inventory.type.type_edit_page',compact('mod_edit','pro_grp'));
    }

    public function pro_type_edit(Request $request,$id){
        $update_by = Auth::user()->au_id;
        $update_at = Carbon::now()->format('Y-m-d H:i:s');
        $type_edit = Inv_product_type::find($id);
        $request->validate([
            'group' => 'required',
            'type' => 'required',
        ]);
        $type_edit->inv_pro_type_grp_id = $request->input('group');
        $type_edit->inv_pro_type_name = $request->input('type');
        $type_edit->inv_pro_type_updated_by = $update_by;
        $type_edit->inv_pro_type_updated_at = $update_at;
        $type_edit->save();
        return redirect()->route('inventory.type_list')->with(['mod_up' => 'Product type Updated Successfully']);
    }


public function product_short_notify(Request $request)
{
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    $pro_det = Inv_product_detail::where('inv_pro_det_com_id', $com)
        ->where('inv_pro_det_branch_id', $branch)
        ->where('inv_pro_det_status', 1)
        ->whereRaw('inv_pro_det_available_qty < inv_pro_det_short_qty')
        ->where('inv_pro_det_short_qty', '!=', '')
        ->with(['product.type_info'])   // 👈 eager load
        ->get();

    // TEMP DEBUG — remove after fixing
    // foreach ($pro_det as $d) {
    //     \Log::info("det={$d->inv_pro_det_id} fk={$d->inv_pro_det_pro_id} name=" . ($d->product->inv_pro_name ?? 'NULL'));
    // }

    return view('inventory.product.short_quantity', compact('pro_det'));
}

    public function warranty_claim(){
        // dd($products);
        return view('inventory.warranty-claim');
    }

    public function warranty_claim_ajax(Request $request){
        $com = Auth::user()->au_company_id;
         $branch = Auth::user()->au_branch_id;
        $products = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', $com)
        ->where('inv_pro_invdet_branch_id',$branch)
        ->where('inv_pro_invdet_slno', 'LIKE' , "%{$request->proSlNo}%")
        ->get();
        //  dd($products);
        if ($products == null) {
            return view('inventory.warranty-claim-ajax',compact('products'));
        }else{
            return view('inventory.warranty-claim-ajax',compact('products'));
        }
    }

    public function sellInvoiceShowAjax(Request $request){
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;
        $invoice = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                        ->where('inv_pro_inv_branch_id',$branch)
                                        ->where('inv_pro_inv_deal_type',2)
                                        ->whereIn('inv_pro_inv_tran_type',[1,10,11,12])
                                        ->where('inv_pro_inv_confirm',0)
                                        ->where('inv_pro_inv_invoice_no',$request->invoice)
                                        ->get();
                                        // dd($invoice);
    }

    public function buyInvoiceShowAjax(Request $request){
        $invoice = $request->invoice;
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;
        $detail_buy = Inv_product_inventory::where('inv_pro_inv_com_id',$com)
                                       ->where('inv_pro_inv_branch_id',$branch)
                                        ->where('inv_pro_inv_deal_type',1)
                                        ->where('inv_pro_inv_tran_type',1)
                                        ->where('inv_pro_inv_confirm',0)
                                        ->where('inv_pro_inv_invoice_no',$request->invoice)
                                        ->get();
                                        // dd($invoice);
        return view('inventory.warranty.buy-invoice-ajax',compact('detail_buy','invoice'));
    }
}

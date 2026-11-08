<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inv_asset_fixed;
use App\Models\Inv_asset_category;
use App\Models\Inv_supplier;
use App\Models\Admin_user;
use Auth;
use Carbon\Carbon;
use PDF;

class AssetController extends Controller
{
    /**
     * Display a listing of assets
     */
    public function index(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $query = Inv_asset_fixed::where('asset_com_id', $com)
            ->where('asset_branch_id', $branch);

        // Search filters
        if ($request->filled('asset_name')) {
            $query->where('asset_name', 'LIKE', '%' . $request->asset_name . '%');
        }
        if ($request->filled('asset_code')) {
            $query->where('asset_code', 'LIKE', '%' . $request->asset_code . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('asset_category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('asset_status', $request->status);
        }

        $assets = $query->with(['category', 'assignedTo', 'supplier'])
                        ->orderBy('asset_id', 'DESC')
                        ->paginate(50);

        $categories = Inv_asset_category::where('category_com_id', $com)
            ->where('category_branch_id', $branch)
            ->where('category_status', 1)
            ->get();

        return view('inventory.assets.index', compact('assets', 'categories'));
    }

    /**
     * Show form to create new asset
     */
    public function create()
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $categories = Inv_asset_category::where('category_com_id', $com)
            ->where('category_branch_id', $branch)
            ->where('category_status', 1)
            ->get();

        // FIX: Remove branch_id from supplier query if the column doesn't exist
        $suppliers = Inv_supplier::where('inv_sup_com_id', $com)
            ->where('inv_sup_status', 1)
            ->get();

        $users = Admin_user::where('au_company_id', $com)
            ->where('au_branch_id', $branch)
            ->where('au_status', 1)
            ->get();

        // Generate asset code
        $lastAsset = Inv_asset_fixed::where('asset_com_id', $com)
            ->where('asset_branch_id', $branch)
            ->orderBy('asset_id', 'DESC')
            ->first();

        $nextId = $lastAsset ? $lastAsset->asset_id + 1 : 1;
        $assetCode = 'AST-' . date('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        return view('inventory.assets.create', compact('categories', 'suppliers', 'users', 'assetCode'));
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $request->validate([
            'asset_name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:inv_asset_category,category_id',
            'asset_purchase_price' => 'required|numeric|min:0',
            'asset_purchase_date' => 'nullable|date',
            'asset_serial_no' => 'nullable|string|max:100',
            'asset_model_no' => 'nullable|string|max:100',
        ]);

        // Generate asset code
        $lastAsset = Inv_asset_fixed::where('asset_com_id', $com)
            ->where('asset_branch_id', $branch)
            ->orderBy('asset_id', 'DESC')
            ->first();

        $nextId = $lastAsset ? $lastAsset->asset_id + 1 : 1;
        $assetCode = 'AST-' . date('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        $asset = new Inv_asset_fixed();
        $asset->asset_com_id = $com;
        $asset->asset_branch_id = $branch;
        $asset->asset_category_id = $request->asset_category_id;
        $asset->asset_name = $request->asset_name;
        $asset->asset_code = $assetCode;
        $asset->asset_description = $request->asset_description;
        $asset->asset_purchase_date = $request->asset_purchase_date;
        $asset->asset_purchase_price = $request->asset_purchase_price;
        $asset->asset_current_value = $request->asset_current_value ?? $request->asset_purchase_price;
        $asset->asset_depreciation_rate = $request->asset_depreciation_rate ?? 0;
        $asset->asset_depreciation_method = $request->asset_depreciation_method ?? 'straight_line';
        $asset->asset_serial_no = $request->asset_serial_no;
        $asset->asset_model_no = $request->asset_model_no;
        $asset->asset_supplier_id = $request->asset_supplier_id;
        $asset->asset_location = $request->asset_location;
        $asset->asset_assigned_to = $request->asset_assigned_to;
        $asset->asset_status = $request->asset_status ?? 1;
        $asset->asset_warranty_expiry = $request->asset_warranty_expiry;
        $asset->asset_notes = $request->asset_notes;
        $asset->asset_submit_by = Auth::user()->au_id;
        $asset->asset_submit_at = now();

        // Handle image upload
        if ($request->hasFile('asset_image')) {
            $image = $request->file('asset_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/assets'), $imageName);
            $asset->asset_image = 'uploads/assets/' . $imageName;
        }

        $asset->save();

        return redirect()->route('assets.index')
            ->with('success', 'Asset added successfully!');
    }

    /**
     * Display asset details
     */
    public function show($id)
    {
        $com = Auth::user()->au_company_id;
        $asset = Inv_asset_fixed::where('asset_com_id', $com)
            ->with(['category', 'supplier', 'assignedTo', 'submitBy', 'updateBy'])
            ->findOrFail($id);

        return view('inventory.assets.show', compact('asset'));
    }

    /**
     * Show form to edit asset
     */
    public function edit($id)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $asset = Inv_asset_fixed::where('asset_com_id', $com)->findOrFail($id);

        $categories = Inv_asset_category::where('category_com_id', $com)
            ->where('category_branch_id', $branch)
            ->where('category_status', 1)
            ->get();

        // FIX: Remove branch_id from supplier query
        $suppliers = Inv_supplier::where('inv_sup_com_id', $com)
            ->where('inv_sup_status', 1)
            ->get();

        $users = Admin_user::where('au_company_id', $com)
            ->where('au_branch_id', $branch)
            ->where('au_status', 1)
            ->get();

        return view('inventory.assets.edit', compact('asset', 'categories', 'suppliers', 'users'));
    }

    /**
     * Update asset
     */
    public function update(Request $request, $id)
    {
        $com = Auth::user()->au_company_id;

        $asset = Inv_asset_fixed::where('asset_com_id', $com)->findOrFail($id);

        $request->validate([
            'asset_name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:inv_asset_category,category_id',
            'asset_purchase_price' => 'required|numeric|min:0',
        ]);

        $asset->asset_category_id = $request->asset_category_id;
        $asset->asset_name = $request->asset_name;
        $asset->asset_description = $request->asset_description;
        $asset->asset_purchase_date = $request->asset_purchase_date;
        $asset->asset_purchase_price = $request->asset_purchase_price;
        $asset->asset_current_value = $request->asset_current_value ?? $request->asset_purchase_price;
        $asset->asset_depreciation_rate = $request->asset_depreciation_rate ?? 0;
        $asset->asset_depreciation_method = $request->asset_depreciation_method ?? 'straight_line';
        $asset->asset_serial_no = $request->asset_serial_no;
        $asset->asset_model_no = $request->asset_model_no;
        $asset->asset_supplier_id = $request->asset_supplier_id;
        $asset->asset_location = $request->asset_location;
        $asset->asset_assigned_to = $request->asset_assigned_to;
        $asset->asset_status = $request->asset_status ?? 1;
        $asset->asset_warranty_expiry = $request->asset_warranty_expiry;
        $asset->asset_notes = $request->asset_notes;
        $asset->asset_update_by = Auth::user()->au_id;
        $asset->asset_update_at = now();

        if ($request->hasFile('asset_image')) {
            // Delete old image if exists
            if ($asset->asset_image && file_exists(public_path($asset->asset_image))) {
                unlink(public_path($asset->asset_image));
            }
            $image = $request->file('asset_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/assets'), $imageName);
            $asset->asset_image = 'uploads/assets/' . $imageName;
        }

        $asset->save();

        return redirect()->route('assets.index')
            ->with('success', 'Asset updated successfully!');
    }

    /**
     * Delete asset
     */
    public function destroy($id)
    {
        $com = Auth::user()->au_company_id;
        $asset = Inv_asset_fixed::where('asset_com_id', $com)->findOrFail($id);

        // Delete image if exists
        if ($asset->asset_image && file_exists(public_path($asset->asset_image))) {
            unlink(public_path($asset->asset_image));
        }

        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset deleted successfully!');
    }

    /**
     * Change asset status
     */
    public function changeStatus($id, $status)
    {
        $com = Auth::user()->au_company_id;
        $asset = Inv_asset_fixed::where('asset_com_id', $com)->findOrFail($id);

        $asset->asset_status = $status;
        $asset->asset_update_by = Auth::user()->au_id;
        $asset->asset_update_at = now();
        $asset->save();

        return redirect()->route('assets.index')
            ->with('success', 'Asset status updated successfully!');
    }

    /**
     * Download asset list as PDF
     */
    public function downloadPDF(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $query = Inv_asset_fixed::where('asset_com_id', $com)
            ->where('asset_branch_id', $branch);

        if ($request->filled('asset_name')) {
            $query->where('asset_name', 'LIKE', '%' . $request->asset_name . '%');
        }

        $assets = $query->with(['category', 'assignedTo'])
            ->orderBy('asset_id', 'DESC')
            ->get();

        $company_info = Admin_user::company_info($com);

        $pdf = PDF::loadView('inventory.assets.asset_pdf', compact('assets', 'company_info'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('fixed_assets_' . date('Y-m-d') . '.pdf');
    }
}
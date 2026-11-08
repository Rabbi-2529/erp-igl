<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inv_asset_category;
use Auth;

class AssetCategoryController extends Controller
{
    /**
     * Display categories list
     */
    public function index()
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $categories = Inv_asset_category::where('category_com_id', $com)
            ->where('category_branch_id', $branch)
            ->orderBy('category_id', 'DESC')
            ->paginate(30);

        return view('inventory.assets.categories', compact('categories'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $request->validate([
            'category_name' => 'required|string|max:255|unique:inv_asset_category,category_name,NULL,category_id,category_com_id,' . $com,
            'category_code' => 'nullable|string|max:50|unique:inv_asset_category,category_code,NULL,category_id,category_com_id,' . $com,
        ]);

        $category = new Inv_asset_category();
        $category->category_com_id = $com;
        $category->category_branch_id = $branch;
        $category->category_name = $request->category_name;
        $category->category_code = $request->category_code;
        $category->category_description = $request->category_description;
        $category->category_status = $request->category_status ?? 1;
        $category->category_submit_by = Auth::user()->au_id;
        $category->category_submit_at = now();

        $category->save();

        return redirect()->back()->with('success', 'Category added successfully!');
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $com = Auth::user()->au_company_id;
        $category = Inv_asset_category::where('category_com_id', $com)
            ->where('category_id', $id)
            ->firstOrFail();

        $request->validate([
            'category_name' => 'required|string|max:255|unique:inv_asset_category,category_name,' . $id . ',category_id,category_com_id,' . $com,
        ]);

        $category->category_name = $request->category_name;
        $category->category_code = $request->category_code;
        $category->category_description = $request->category_description;
        $category->category_status = $request->category_status ?? 1;
        $category->category_update_by = Auth::user()->au_id;
        $category->category_update_at = now();

        $category->save();

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        $com = Auth::user()->au_company_id;
        $category = Inv_asset_category::where('category_com_id', $com)
            ->where('category_id', $id)
            ->firstOrFail();

        // Check if category has assets
        if ($category->assets()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category. It has assets assigned to it.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    /**
     * Change category status
     */
    public function changeStatus($id, $status)
    {
        $com = Auth::user()->au_company_id;
        $category = Inv_asset_category::where('category_com_id', $com)
            ->where('category_id', $id)
            ->firstOrFail();

        $category->category_status = $status;
        $category->category_update_by = Auth::user()->au_id;
        $category->category_update_at = now();
        $category->save();

        return redirect()->back()->with('success', 'Category status updated successfully!');
    }

    /**
     * Get categories for AJAX
     */
    public function getCategoriesAjax(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $categories = Inv_asset_category::where('category_com_id', $com)
            ->where('category_branch_id', $branch)
            ->where('category_status', 1)
            ->select('category_id as id', 'category_name as text')
            ->get();

        return response()->json($categories);
    }
}
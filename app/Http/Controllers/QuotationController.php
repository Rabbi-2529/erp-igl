<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inv_product_detail;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sds_query_book;
use Illuminate\Support\Facades\Session;

use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use DB;

class QuotationController extends Controller
{

public function index()
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    if (Auth::user()->au_user_type == 4) {
        // User type 4 can see all quotations of their company and branch
        $quotations = Quotation::where('qt_company_id', $com_id)
                               ->where('qt_branch_id', $branch_id)
                               ->paginate(10);
    } else {
        // Other users see only their own quotations for the branch
        $quotations = Quotation::where('qt_company_id', $com_id)
                               ->where('qt_branch_id', $branch_id)
                               ->where('qt_entry_by', Auth::id())
                               ->paginate(10);
    }

    return view('page.quotations.index', compact('quotations'));
}

    
  public function create()
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;

    // Fetch customers and product details only for the authenticated user's company and branch
    $customers = Sds_query_book::where('qb_company_id', $com_id)
                               ->where('qb_branch_id', $branch_id)
                               ->get();

    $products = Inv_product_detail::where('inv_pro_det_com_id', $com_id)
                                  ->where('inv_pro_det_branch_id', $branch_id)
                                  ->get();

    $quotation = new Quotation();

    return view('page.quotations.create', compact('customers', 'products', 'quotation'));
}

    public function store(Request $request)
{
    // Debugging: Log the incoming form data
    \Log::debug('Form Data:', $request->all());

    // Validate the incoming data
    $request->validate([
        'customer_id' => 'required|exists:sds_query_book,qb_id',
        'qdate' => 'required|date',
        'expiry_date' => 'required|date',
        'product_details' => 'required|string',
        'discount' => 'nullable|numeric|min:0', // Add validation for discount
        // Validate as string (since it's JSON)
    ]);

    // Prepare the data for saving
    $com_id = Auth::user()->au_company_id;
       $branch_id = Auth::user()->au_branch_id;

    // Decode the JSON string for product details
    $productDetails = json_decode($request->input('product_details'), true); // Decode JSON into array

    if ($productDetails === null) {
        return redirect()->back()->with('error', 'Product details are not valid.');
    }

    // Create a new array with only product IDs


    // Calculate total amount and grand total
    $totalAmount = 0;
    foreach ($productDetails as $product) {
        $totalAmount += $product['quantity'] * $product['rate'];
    }

    $discount = $request->discount ?? 0;

    $grandTotal = $totalAmount - $discount;
   
    // Generate a unique quotation_id
    $lastQuotation = Quotation::orderBy('qt_id', 'desc')->first();
    $newQuotationId = 'Quotation_' . str_pad($lastQuotation ? $lastQuotation->qt_id + 1 : 1, 3, '0', STR_PAD_LEFT);

    // Save the quotation data
    $quotation = new Quotation();
    $quotation->qt_company_id = $com_id;
      $quotation->qt_branch_id       = $branch_id;
    $quotation->qt_entry_by = auth()->id();
    $quotation->qt_customer_id = $request->customer_id;
    $quotation->qt_date = $request->qdate;
    $quotation->qt_expiry_date = $request->expiry_date;
    $quotation->qt_total_amount = $totalAmount;
    $quotation->qt_discount = $discount;
    $quotation->qt_final_amount = $grandTotal;
    $quotation->qt_status = 'Active'; // Add status if needed
    $quotation->qt_created_at = now();


    $quotation->qt_product_details = json_encode($productDetails);

    // Save the dynamically generated quotation_id
    $quotation->quotation_id = $newQuotationId;

    // Try to save the quotation
    if ($quotation->save()) {
        // Log the saved quotation data for debugging
        \Log::debug('Quotation saved:', $quotation->toArray());
        return redirect()->route('quotations.index')->with('success', 'Quotation created successfully!');
    } else {
        // Log failure for debugging
        \Log::debug('Failed to save quotation');
        return redirect()->back()->with('error', 'Failed to create quotation.');
    }
}

    
// The edit function to display the quotation for editing
// The 'edit' method for fetching the quotation details
public function edit($id)
{
    // Fetch the quotation details by ID
    $quotation = Quotation::findOrFail($id);

    // Fetch all customers and products
    $customers = Sds_query_book::all();
    $products = Inv_product_detail::all();

    // Decode the product details from the quotation (Handle empty case)
    $productDetails = json_decode($quotation->qt_product_details, true) ?? [];

    // Calculate subtotal and grand total
    $subtotal = 0;
    foreach ($productDetails as $item) {
        $product = $products->firstWhere('inv_pro_det_id', $item['product_id']);
        if ($product) {
            // Accumulate the total for the products
            $subtotal += $item['quantity'] * $product->inv_pro_det_sell_price;
        }
    }

    // Apply the discount and calculate grand total
    $grandTotal = $subtotal - ($quotation->qt_discount ?? 0);

    // Pass data to the view
    return view('page.quotations.edit', compact(
        'quotation',
        'customers',
        'products',
        'productDetails',
        'subtotal',
        'grandTotal'
    ));
}

// The 'update' method to handle the form submission and update quotation details
public function update(Request $request, $id)
{
    // Validate incoming data
    $request->validate([
        'customer_id' => 'required|exists:sds_query_book,qb_id',
        'qdate' => 'required|date',
        'expiry_date' => 'required|date',
        'product_details' => 'required|string', // Ensure product_details is a valid string
        'discount' => 'nullable|numeric|min:0',
    ]);

    // Retrieve the quotation to update
    $quotation = Quotation::findOrFail($id);

    // Decode the JSON string for product details
    $productDetails = json_decode($request->input('product_details'), true);

    // Check if product details are valid
    if ($productDetails === null || !is_array($productDetails)) {
        return redirect()->back()->with('error', 'Product details are not valid.');
    }

    // Calculate total amount and grand total
    $totalAmount = 0;
    foreach ($productDetails as $product) {
        // Ensure product has a valid rate and quantity
        if (isset($product['quantity']) && isset($product['rate'])) {
            $totalAmount += $product['quantity'] * $product['rate'];
        }
    }

    $discount = $request->discount ?? 0;

    // Calculate the grand total (after discount)
    $grandTotal = $totalAmount - $discount;

    // Update the quotation details
    $quotation->qt_customer_id = $request->customer_id;
    $quotation->qt_date = $request->qdate;
    $quotation->qt_expiry_date = $request->expiry_date;
    $quotation->qt_total_amount = $totalAmount;
    $quotation->qt_discount = $discount;
    $quotation->qt_final_amount = $grandTotal;
    $quotation->qt_status = 'Active'; // Status remains 'Active'

    // Save product details as JSON (including quantity and rate for the update)
    $quotation->qt_product_details = json_encode($productDetails);

    // Attempt to save the updated quotation
    if ($quotation->save()) {
        return redirect()->route('quotations.index')->with('success', 'Quotation updated successfully!');
    } else {
        return redirect()->back()->with('error', 'Failed to update quotation.');
    }
}
public function destroy($id)
{
    $quotation = Quotation::findOrFail($id);
    $quotation->delete();

    return redirect()->route('quotations.index')->with('success', 'Quotation deleted successfully!');
}  
    




    // Helper function to prepare product data for storage
    private function prepareProductData($request)
    {
        $productData = [];
        foreach ($request->product_id as $index => $productId) {
            $productData[] = [
                'product_id' => $productId,
                'quantity' => $request->product_quantity[$index],
                'rate' => $request->product_rate[$index],
                'total_price' => $request->total_price[$index],
            ];
        }
        return $productData;
    }

    // Helper function to calculate the final amount after discount
    private function calculateFinalAmount($totalPrice, $discount)
    {
        $totalAmount = array_sum($totalPrice);
        $discountAmount = ($discount / 100) * $totalAmount;
        return $totalAmount - $discountAmount;
    }
    public function printQuotation($id)
    {
        $quotation = Quotation::findOrFail($id);  // Fetch the quotation using the ID
        $productDetails = json_decode($quotation->qt_product_details, true);  // Decode the product details
        $entryByUser = \App\Models\Admin_user::find($quotation->qt_entry_by); // Fetch the user who created the quotation

        // Fetch product details including name, description, brand, and warranty
        foreach ($productDetails as &$product) {
            $productDetail = Inv_product_detail::find($product['id']);
            if ($productDetail) {
                $product['inv_pro_det_pro_id'] = $productDetail->inv_pro_det_pro_id;  // Product name
                $product['description'] = $productDetail->inv_pro_det_pro_description;  // Product description
                $product['brand'] = $productDetail->inv_pro_det_brand;  // Product brand
                $product['warranty'] = $productDetail->inv_pro_det_pro_warranty;  // Product warranty
            } else {
                $product['inv_pro_det_pro_id'] = 'No name available';  // Fallback if not found
                $product['description'] = 'No description available';  // Fallback if not found
                $product['brand'] = 'No brand available';  // Fallback if not found
                $product['warranty'] = 'No warranty available';  // Fallback if not found
            }
        }
    
        return view('page.quotations.print', compact('quotation', 'productDetails','entryByUser'));
    }
    
    
  
}

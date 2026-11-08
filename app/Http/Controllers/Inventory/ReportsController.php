<?php

namespace App\Http\Controllers\Inventory;

use PDF;
use Auth;
use Illuminate\Http\Request;
use App\Models\Inv_product_inventory;
use App\Models\Inv_product_detail;
use App\Models\Admin_user;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportsController extends Controller
{
    /**
     * Buy Invoice PDF
     */
    public function buy_invoice_pdf()
    {
        return view('inventory.reports.buy_invoice');
    }

    /**
     * Generate Buy Invoice PDF
     */
    public function buy_invoice_pdf_generate(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $invoice = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 1)
                                        ->first();
        $pdf = PDF::loadView('inventory.reports.buy_invoice');
        return $pdf->download($invoice->inv_pro_inv_invoice_no . '.pdf');
    }

    /**
     * Generate Sell Invoice PDF
     */
    public function sell_invoice_pdf_generate(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $invoice = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 2)
                                        ->first();
        $pdf = PDF::loadView('inventory.reports.buy_invoice');
        return $pdf->download($invoice->inv_pro_inv_invoice_no . '.pdf');
    }

    /**
     * Display Sell Reports with filters
     */
    public function sell_reports(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        // ===== STORE FILTERS IN SESSION =====
        if ($request->has('searchbtn')) {
            session([
                'sell_report_filters' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'invoice_no' => $request->invoice_no
                ]
            ]);
        }

        // Base query
        $query = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
            ->where('inv_pro_inv_deal_type', 2)
            ->whereIn('inv_pro_inv_tran_type', [1, 10, 11, 12])
            ->where('inv_pro_inv_status', 1)
            ->where('inv_pro_inv_confirm', 0);

        // Search by Invoice Number
        if ($request->filled('invoice_no')) {
            $query->where('inv_pro_inv_invoice_no', 'LIKE', '%' . $request->invoice_no . '%');
        }

        // Date Filter with Conversion
        if ($request->has('searchbtn') && $request->filled('start_date') && $request->filled('end_date')) {
            
            $startDateInput = $request->start_date;
            $endDateInput = $request->end_date;

            $convertToDbFormat = function($dateString) {
                if (empty($dateString)) {
                    return null;
                }
                if (strpos($dateString, '/') !== false) {
                    $parts = explode('/', $dateString);
                    if (count($parts) == 3) {
                        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    }
                }
                return $dateString;
            };

            $startDate = $convertToDbFormat($startDateInput);
            $endDate = $convertToDbFormat($endDateInput);

            if ($startDate && $endDate) {
                $query->whereDate('inv_pro_inv_issue_date', '>=', $startDate)
                      ->whereDate('inv_pro_inv_issue_date', '<=', $endDate);
            }
        }

        // Fetch and group results by invoice number - ORDER BY ISSUE DATE ASCENDING
        $sell_reports = $query->orderBy('inv_pro_inv_issue_date', 'ASC')
                             ->orderBy('inv_pro_inv_id', 'DESC')
                             ->get()
                             ->groupBy('inv_pro_inv_invoice_no')
                             ->map(function ($items) {
                                 return $items->first();
                             });

        // Default: Show current month's records if no search is performed
        if ($sell_reports->isEmpty() && !$request->has('searchbtn') && !$request->filled('invoice_no')) {
            $sell_reports = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_deal_type', 2)
                ->whereIn('inv_pro_inv_tran_type', [1, 10, 11, 12])
                ->where('inv_pro_inv_status', 1)
                ->where('inv_pro_inv_confirm', 0)
                ->whereMonth('inv_pro_inv_issue_date', date('m'))
                ->whereYear('inv_pro_inv_issue_date', date('Y'))
                ->orderBy('inv_pro_inv_issue_date', 'ASC')
                ->orderBy('inv_pro_inv_id', 'DESC')
                ->get()
                ->groupBy('inv_pro_inv_invoice_no')
                ->map(function ($items) {
                    return $items->first();
                });
        }

        return view('page.report.sell-report', compact('sell_reports'));
    }

    /**
     * Display Confirmed Sell Reports
     */
    public function sell_confirm_reports(Request $request)
    {
        $com = Auth::user()->au_company_id;

        if ($request->has('searchbtn')) {
            $request->validate([
                'start_date' => 'required',
                'end_date' => 'required',
            ]);
            $sell_reports = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                            ->whereDate('inv_pro_inv_issue_date', '>=', $request->start_date)
                                            ->whereDate('inv_pro_inv_issue_date', '<=', $request->end_date)
                                            ->where('inv_pro_inv_status', 1)
                                            ->where('inv_pro_inv_deal_type', 2)
                                            ->where('inv_pro_inv_tran_type', 1)
                                            ->where('inv_pro_inv_confirm', 1)
                                            ->groupBy('inv_pro_inv_invoice_no')
                                            ->get();
        } else {
            $sell_reports = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                                ->where('inv_pro_inv_deal_type', 2)
                                                ->where('inv_pro_inv_tran_type', 1)
                                                ->where('inv_pro_inv_confirm', 1)
                                                ->groupBy('inv_pro_inv_invoice_no')
                                                ->get();
        }
        return view('inventory.reports.sell_reports_confirm', compact('sell_reports'));
    }

    /**
     * Get Sell Report Details via AJAX
     */
    public function sell_report_ajax(Request $request)
    {
        $detail_ajax = Inv_product_inventory::where('inv_pro_inv_invoice_no', $request->sell_id)
                                            ->where('inv_pro_inv_deal_type', 2)
                                            ->whereIn('inv_pro_inv_tran_type', [1, 10, 11])
                                            ->get();

        $discount = Inv_product_inventory::where('inv_pro_inv_invoice_no', $request->sell_id)
                                        ->where('inv_pro_inv_deal_type', 2)
                                        ->where('inv_pro_inv_tran_type', 12)
                                        ->first();
        if (!empty($discount)) {
            $discount_amount = $discount->inv_pro_inv_credit;
        } else {
            $discount_amount = 0;
        }

        return view('pages.ajax.sell_reports_ajax', compact('detail_ajax', 'discount_amount'));
    }

    /**
     * Display Buy Reports
     */
    public function buy_reports(Request $request)
    {
        $com = Auth::user()->au_company_id;

        if ($request->has('searchbtn')) {
            $request->validate([
                'start_date' => 'required',
                'end_date' => 'required',
            ]);
            $buy_reports = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                            ->whereDate('inv_pro_inv_issue_date', '>=', $request->start_date)
                                            ->whereDate('inv_pro_inv_issue_date', '<=', $request->end_date)
                                            ->where('inv_pro_inv_status', 1)
                                            ->where('inv_pro_inv_deal_type', 1)
                                            ->where('inv_pro_inv_tran_type', 1)
                                            ->where('inv_pro_inv_confirm', 0)
                                            ->groupBy('inv_pro_inv_invoice_no')
                                            ->get();
        } else {
            $buy_reports = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                            ->where('inv_pro_inv_deal_type', 1)
                                            ->where('inv_pro_inv_tran_type', 1)
                                            ->where('inv_pro_inv_confirm', 0)
                                            ->groupBy('inv_pro_inv_invoice_no')
                                            ->get();
        }
        return view('inventory.product_inventory.buy_reports', compact('buy_reports'));
    }

    /**
     * Display Confirmed Buy Reports
     */
    public function buy_reports_confirm(Request $request)
    {
        $com = Auth::user()->au_company_id;

        if ($request->has('searchbtn')) {
            $request->validate([
                'start_date' => 'required',
                'end_date' => 'required',
            ]);
            $buy_reports = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                            ->whereDate('inv_pro_inv_issue_date', '>=', $request->start_date)
                                            ->whereDate('inv_pro_inv_issue_date', '<=', $request->end_date)
                                            ->where('inv_pro_inv_status', 1)
                                            ->where('inv_pro_inv_deal_type', 1)
                                            ->where('inv_pro_inv_tran_type', 1)
                                            ->where('inv_pro_inv_confirm', 1)
                                            ->groupBy('inv_pro_inv_invoice_no')
                                            ->get();
        } else {
            $buy_reports = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                            ->where('inv_pro_inv_deal_type', 1)
                                            ->where('inv_pro_inv_tran_type', 1)
                                            ->where('inv_pro_inv_confirm', 1)
                                            ->groupBy('inv_pro_inv_invoice_no')
                                            ->get();
        }

        return view('inventory.reports.buy_reports_confirm', compact('buy_reports'));
    }

    /**
     * Get Buy Report Details via AJAX
     */
    public function buy_reports_ajax(Request $request)
    {
        $detail_buy = Inv_product_inventory::where('inv_pro_inv_com_id', Auth::user()->au_company_id)
                                            ->where('inv_pro_inv_invoice_no', $request->buy_id)
                                            ->where('inv_pro_inv_deal_type', 1)
                                            ->where('inv_pro_inv_tran_type', 1)
                                            ->with(['getProductWarranty', 'getProductWarranty.product'])
                                            ->get();
        return view('pages.ajax.purchase_product.buy_report_ajax', compact('detail_buy'));
    }

    /**
     * Generate Buy Report PDF
     */
    public function buy_reports_pdf(Request $request, $invoice_r)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        $invoice_detail = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 1)
                                        ->where('inv_pro_inv_tran_type', 1)
                                        ->where('inv_pro_inv_invoice_no', $invoice_r)
                                        ->with(['getSupplierInfo', 'getSoldByInfo'])
                                        ->first();

        if (!$invoice_detail) {
            return redirect()->back()->with('error', 'Invoice not found');
        }

        // Purchased product lines
        $invoice = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 1)
                                        ->where('inv_pro_inv_tran_type', 1)
                                        ->where('inv_pro_inv_invoice_no', $invoice_r)
                                        ->with(['getProductWarranty', 'getProductWarranty.product'])
                                        ->orderBy('inv_pro_inv_id', 'asc')
                                        ->get();

        // Payments (credit) and supplier discount (tran type 6) booked against this invoice
        $paidAmount = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 1)
                                        ->where('inv_pro_inv_invoice_no', $invoice_r)
                                        ->where('inv_pro_inv_tran_type', '!=', 6)
                                        ->sum('inv_pro_inv_credit');

        $totalDiscount = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 1)
                                        ->where('inv_pro_inv_tran_type', 6)
                                        ->where('inv_pro_inv_invoice_no', $invoice_r)
                                        ->sum('inv_pro_inv_debit');

        $company_info = Admin_user::company_info($com);

        // ========== PREVIOUS PAYABLE TO SUPPLIER ==========
        // Same logic as the sales invoice: balance (credit - debit) of this supplier
        // excluding the current invoice; a negative balance means we still owe them.
        // Opening balance rows are saved without a branch, so include those too.
        $supplierRows = function () use ($com, $branch, $invoice_detail) {
            return Inv_product_inventory::where('inv_pro_inv_party_id', $invoice_detail->inv_pro_inv_party_id)
                    ->where('inv_pro_inv_deal_type', 1)
                    ->where('inv_pro_inv_status', 1)
                    ->where('inv_pro_inv_com_id', $com)
                    ->where(function ($q) use ($branch) {
                        $q->where('inv_pro_inv_branch_id', $branch)->orWhereNull('inv_pro_inv_branch_id');
                    })
                    ->where('inv_pro_inv_invoice_no', '!=', $invoice_detail->inv_pro_inv_invoice_no);
        };

        $availableBalance = $supplierRows()->sum('inv_pro_inv_credit') - $supplierRows()->sum('inv_pro_inv_debit');
        $previousDue = $availableBalance < 0 ? abs($availableBalance) : 0;

        $data = compact('invoice', 'invoice_detail', 'company_info', 'previousDue', 'paidAmount', 'totalDiscount');

        if (isset($request->print) || isset($request->view)) {
            $data['autoPrint'] = isset($request->print);
            return view('inventory.reports.purchase_invoice', $data);
        }

        $pdf = PDF::loadView('inventory.reports.purchase_invoice', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->download($invoice_detail->inv_pro_inv_invoice_no . '.pdf');
    }

    /**
     * Generate Sell Report PDF - UPDATED with services_delivery and company info
     */
  /**
 * Generate Sell Report PDF - UPDATED with services_delivery and company info
 */
public function sell_reports_pdf(Request $request, $invoice_r)
{
    // Increase memory for PDF generation
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 300);
    
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    // Get invoice detail (a service-charge-only invoice has no product row, so fall back to it)
    $invoice_detail = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 2)
                                        ->whereIn('inv_pro_inv_tran_type', [1, 10])
                                        ->where('inv_pro_inv_invoice_no', $invoice_r)
                                        ->orderBy('inv_pro_inv_tran_type', 'asc')
                                        ->first();

    if (!$invoice_detail) {
        return redirect()->back()->with('error', 'Invoice not found');
    }

    // Get product items with all relationships
    $invoice = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                    ->where('inv_pro_inv_deal_type', 2)
                                    ->whereIn('inv_pro_inv_tran_type', [1, 10])
                                    ->where('inv_pro_inv_invoice_no', $invoice_r)
                                    ->with([
                                        'getProductWarranty', 
                                        'getProductWarranty.product'
                                    ])
                                    ->orderBy('inv_pro_inv_tran_type', 'asc')
                                    ->get();

    // Get delivery and discount
    $services_delivery = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                    ->where('inv_pro_inv_deal_type', 2)
                                    ->whereIn('inv_pro_inv_tran_type', [11, 12])
                                    ->where('inv_pro_inv_invoice_no', $invoice_r)
                                    ->orderBy('inv_pro_inv_tran_type', 'desc')
                                    ->get();

    // Generate QR Code
    $qrCode = QrCode::size(100)
            ->backgroundColor(255, 255, 255)
            ->color(0, 0, 0)
            ->generate('INVOICE NUMBER: ' . $invoice_detail->inv_pro_inv_invoice_no);

    // Get company info
    $company_info = Admin_user::company_info($com);

    // ========== CALCULATE PREVIOUS DUE ==========
    // Using the same logic as ajaxLoadCustomerBalance
    $customerId = $invoice_detail->inv_pro_inv_party_id;
    $currentInvoiceNo = $invoice_detail->inv_pro_inv_invoice_no;

    // Sum all CREDIT transactions (payments, returns, etc.) for this customer
    // EXCLUDING the current invoice
    $totalCredit = Inv_product_inventory::where('inv_pro_inv_party_id', $customerId)
                    ->where('inv_pro_inv_isproject', 0)
                    ->where('inv_pro_inv_deal_type', 2)
                    ->where('inv_pro_inv_com_id', $com)
                    ->where('inv_pro_inv_branch_id', $branch)
                    ->where('inv_pro_inv_invoice_no', '!=', $currentInvoiceNo)
                    ->sum('inv_pro_inv_credit');
    
    // Sum all DEBIT transactions (sales, charges, etc.) for this customer
    // EXCLUDING the current invoice
    $totalDebit = Inv_product_inventory::where('inv_pro_inv_party_id', $customerId)
                    ->where('inv_pro_inv_isproject', 0)
                    ->where('inv_pro_inv_deal_type', 2)
                    ->where('inv_pro_inv_com_id', $com)
                    ->where('inv_pro_inv_branch_id', $branch)
                    ->where('inv_pro_inv_invoice_no', '!=', $currentInvoiceNo)
                    ->sum('inv_pro_inv_debit');
    
    // Calculate Available Balance (Credit - Debit)
    $availableBalance = $totalCredit - $totalDebit;
    
    // If Available Balance is negative, that means customer has Previous Due
    // Previous Due = absolute value of negative available balance
    if ($availableBalance < 0) {
        $previousDue = abs($availableBalance); // Convert to positive
    } else {
        $previousDue = 0; // No previous due if balance is positive or zero
    }

    // For debugging - you can remove this after testing
    // \Log::info('Previous Due Calculation:', [
    //     'customer_id' => $customerId,
    //     'total_credit' => $totalCredit,
    //     'total_debit' => $totalDebit,
    //     'available_balance' => $availableBalance,
    //     'previous_due' => $previousDue
    // ]);

    // For print/view
    if (isset($request->print)) {
        session()->flash('print_invoice', true);
        return view('inventory.reports.sell_print', compact(
            'invoice', 
            'invoice_detail', 
            'services_delivery', 
            'qrCode', 
            'company_info', 
            'previousDue'
        ));
    } elseif (isset($request->view)) {
        return view('inventory.reports.sell_print', compact(
            'invoice', 
            'invoice_detail', 
            'services_delivery', 
            'qrCode', 
            'company_info', 
            'previousDue'
        ));
    }

    // Generate PDF with all required variables
    $pdf = PDF::loadView('inventory.reports.SellIndividualInvoicePdf', compact(
        'invoice', 
        'invoice_detail', 
        'services_delivery', 
        'qrCode',
        'company_info',
        'previousDue' 
    ));
    
    $pdf->setPaper('A4', 'portrait');
    $pdf->setOptions([
        'defaultFont' => 'Arial',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]);
    
    return $pdf->download($invoice_detail->inv_pro_inv_invoice_no . '.pdf');
}

    /**
     * Confirm Sell Invoice
     */
    public function sell_confirm(Request $request, $invoice)
    {
        Inv_product_inventory::where('inv_pro_inv_invoice_no', $invoice)
            ->update(['inv_pro_inv_confirm' => 1]);

        return redirect()->back()->with(['confirm' => 'Product Confirm Successfully']);
    }

    /**
     * Confirm Buy Invoice
     */
    public function buy_confirm(Request $request, $invoice)
    {
        Inv_product_inventory::where('inv_pro_inv_invoice_no', $invoice)
            ->update(['inv_pro_inv_confirm' => 1]);
        return redirect()->back()->with(['buy_confirm' => 'Buy Confirm Successfully']);
    }

    /**
     * Download Sell Reports with Filters
     */
    public function sellReportsDownload(Request $request)
    {
        $com = Auth::user()->au_company_id;

        // ===== GET FILTERS FROM SESSION =====
        $filters = session('sell_report_filters', []);
        
        // Override with request parameters if they exist
        $startDate = $request->input('start_date') ?? $filters['start_date'] ?? null;
        $endDate = $request->input('end_date') ?? $filters['end_date'] ?? null;
        $invoiceNo = $request->input('invoice_no') ?? $filters['invoice_no'] ?? null;

        // Start with base query
        $query = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                    ->where('inv_pro_inv_deal_type', 2)
                    ->whereIn('inv_pro_inv_tran_type', [1, 10, 11, 12])
                    ->where('inv_pro_inv_status', 1)
                    ->where('inv_pro_inv_confirm', 0);

        // ===== CONVERT DD/MM/YYYY TO YYYY-MM-DD =====
        $startDateFormatted = $this->convertDateFormat($startDate);
        $endDateFormatted = $this->convertDateFormat($endDate);

        // ===== APPLY DATE FILTERS =====
        if (!empty($startDateFormatted) && !empty($endDateFormatted)) {
            $query->whereDate('inv_pro_inv_issue_date', '>=', $startDateFormatted)
                  ->whereDate('inv_pro_inv_issue_date', '<=', $endDateFormatted);
        } 

        // ===== APPLY INVOICE NUMBER FILTER =====
        if (!empty($invoiceNo)) {
            $query->where('inv_pro_inv_invoice_no', 'LIKE', '%' . $invoiceNo . '%');
        }

        // Get results grouped by invoice number
        $SellStatments = $query->orderBy('inv_pro_inv_issue_date', 'ASC')
                               ->orderBy('inv_pro_inv_id', 'DESC')
                               ->get()
                               ->groupBy('inv_pro_inv_invoice_no')
                               ->map(function ($items) {
                                   return $items->first();
                               });

        // If no records found, redirect back with message
        if ($SellStatments->isEmpty()) {
            return redirect()->back()->with('error', 'No records found for the selected filters.');
        }

        // ===== PASS FILTERS TO VIEW =====
        $filterData = [
            'start_date' => $startDateFormatted,
            'end_date' => $endDateFormatted,
            'invoice_no' => $invoiceNo,
            'has_filters' => !empty($startDateFormatted) || !empty($endDateFormatted) || !empty($invoiceNo)
        ];

        // Generate PDF - Pass $com to view for quantity calculation
        $pdf = PDF::loadView('inventory.reports.sell_statement_download', compact('SellStatments', 'filterData', 'com'));
        
        // Generate filename with date
        $filename = 'sell_statement_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Download Buy Reports with Filters
     */
    public function BuyReportsDownload(Request $request)
    {
        $com = Auth::user()->au_company_id;

        // Start with base query
        $query = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                    ->where('inv_pro_inv_status', 1)
                    ->where('inv_pro_inv_deal_type', 1)
                    ->where('inv_pro_inv_tran_type', 1);

        // Get filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $invoiceNo = $request->input('invoice_no');

        // ===== CONVERT DD/MM/YYYY TO YYYY-MM-DD =====
        $startDateFormatted = $this->convertDateFormat($startDate);
        $endDateFormatted = $this->convertDateFormat($endDate);

        // Apply date filters
        if (!empty($startDateFormatted) && !empty($endDateFormatted)) {
            $query->whereDate('inv_pro_inv_issue_date', '>=', $startDateFormatted)
                  ->whereDate('inv_pro_inv_issue_date', '<=', $endDateFormatted);
        } elseif (!empty($startDateFormatted)) {
            $query->whereDate('inv_pro_inv_issue_date', '>=', $startDateFormatted);
        } elseif (!empty($endDateFormatted)) {
            $query->whereDate('inv_pro_inv_issue_date', '<=', $endDateFormatted);
        }

        // Apply invoice number filter if provided
        if (!empty($invoiceNo)) {
            $query->where('inv_pro_inv_invoice_no', 'LIKE', '%' . $invoiceNo . '%');
        }

        // Get results grouped by invoice number
        $buyStatments = $query->groupBy('inv_pro_inv_invoice_no')
                              ->orderBy('inv_pro_inv_issue_date', 'desc')
                              ->get();

        // If no records found, redirect back with message
        if ($buyStatments->isEmpty()) {
            return redirect()->back()->with('error', 'No records found for the selected filters.');
        }

        // Pass filters to view
        $filterData = [
            'start_date' => $startDateFormatted,
            'end_date' => $endDateFormatted,
            'invoice_no' => $invoiceNo,
            'searchbtn' => true,
            'has_filters' => !empty($startDateFormatted) || !empty($endDateFormatted) || !empty($invoiceNo)
        ];

        // Generate PDF
        $pdf = PDF::loadView('inventory.reports.buy_statement_download', compact('buyStatments', 'filterData'));
        return $pdf->download('buy_statement_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Buy Return Invoice Print
     */
    public function buyReturnInvoicePrint(Request $request, $invoice)
    {
        $com = Auth::user()->au_company_id;

        $invoice_detail = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 1)
                                        ->where('inv_pro_inv_tran_type', 2)
                                        ->where('inv_pro_inv_invoice_no', $invoice)
                                        ->first();

        $invoice = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 1)
                                        ->where('inv_pro_inv_tran_type', 2)
                                        ->where('inv_pro_inv_invoice_no', $invoice)
                                        ->get();

        if (isset($request->print)) {
            return view('inventory.reports.buy_return_print', compact('invoice', 'invoice_detail'));
        }

        $pdf = PDF::loadView('inventory.reports.BuyIndividualInvoicePdf', compact('invoice', 'invoice_detail'));
        return $pdf->download($invoice_detail->inv_pro_inv_invoice_no . '.pdf');
    }

    /**
     * Sell Return Invoice Print
     */
    public function sellReturnInvoicePrint(Request $request, $invoice)
    {
        $com = Auth::user()->au_company_id;

        $invoice_detail = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 2)
                                        ->where('inv_pro_inv_tran_type', 2)
                                        ->where('inv_pro_inv_invoice_no', $invoice)
                                        ->first();

        $invoice = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                        ->where('inv_pro_inv_deal_type', 2)
                                        ->where('inv_pro_inv_tran_type', 2)
                                        ->where('inv_pro_inv_invoice_no', $invoice)
                                        ->get();

        if (isset($request->print)) {
            return view('inventory.reports.sell_return_print', compact('invoice', 'invoice_detail'));
        }

        $pdf = PDF::loadView('inventory.reports.SellIndividualInvoicePdf', compact('invoice', 'invoice_detail'));
        return $pdf->download($invoice_detail->inv_pro_inv_invoice_no . '.pdf');
    }

    /**
     * Combined Reports
     */
    public function combinedReports(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $reports = null;
        
        if ($request->has('searchbtn')) {
            $request->validate([
                'start_date' => 'required',
                'end_date' => 'required',
            ]);
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        } else {
            $start_date = Carbon::now()->format('Y-m-d');
            $end_date = Carbon::now()->format('Y-m-d');
        }
        
        $reports = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                                            ->whereDate('inv_pro_inv_issue_date', '>=', $start_date)
                                            ->whereDate('inv_pro_inv_issue_date', '<=', $end_date)
                                            ->where('inv_pro_inv_status', 1)
                                            ->where('inv_pro_inv_tran_type', 1)
                                            ->where('inv_pro_inv_confirm', 0)
                                            ->groupBy('inv_pro_inv_invoice_no')
                                            ->get();
        
        return view('inventory.reports.combined_reports', compact('reports', 'start_date', 'end_date'));
    }

    /**
     * ===== HELPER: Convert Date Format =====
     * Converts DD/MM/YYYY to YYYY-MM-DD
     */
    private function convertDateFormat($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        
        // Trim whitespace
        $dateString = trim($dateString);
        
        // Check if it's in DD/MM/YYYY format (contains '/')
        if (strpos($dateString, '/') !== false) {
            $parts = explode('/', $dateString);
            if (count($parts) == 3) {
                // Rebuild as YYYY-MM-DD
                return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }
        
        // Check if it's in YYYY-MM-DD format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }
        
        // Try to parse with strtotime
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        // Return as is if format is unknown
        return $dateString;
    }
    public function stockReport(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        // ===== STORE FILTERS IN SESSION =====
        if ($request->has('searchbtn')) {
            session([
                'stock_report_filters' => [
                    'product_name' => $request->product_name,
                    'category' => $request->category,
                    'stock_status' => $request->stock_status
                ]
            ]);
        }

        // Base query - Get all products with their stock details
        $query = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_branch_id', $branch)
            ->where('inv_pro_det_status', 1);

        // Search by Product Name
        if ($request->filled('product_name')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('inv_pro_name', 'LIKE', '%' . $request->product_name . '%');
            });
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('inv_pro_det_type_id', $request->category);
        }

        // Filter by Stock Status
        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'in_stock') {
                $query->where('inv_pro_det_available_qty', '>', 0);
            } elseif ($request->stock_status == 'low_stock') {
                $query->where('inv_pro_det_available_qty', '<=', 10)
                      ->where('inv_pro_det_available_qty', '>', 0);
            } elseif ($request->stock_status == 'out_of_stock') {
                $query->where('inv_pro_det_available_qty', '=', 0);
            }
        }

        // Get stock data with eager loading
        $stockData = $query->with(['product', 'type_info'])
            ->orderBy('inv_pro_det_id', 'DESC')
            ->paginate(50);

        // Get categories for filter dropdown
        $categories = \App\Models\Inv_product_type::where('inv_pro_type_com_id', $com)
            ->where('inv_pro_type_status', 1)
            ->get();

        return view('inventory.reports.stock_report', compact('stockData', 'categories'));
    }

    /**
     * Download Stock Report as PDF
     */
    public function stockReportDownload(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch = Auth::user()->au_branch_id;

        // ===== GET FILTERS FROM SESSION =====
        $filters = session('stock_report_filters', []);
        
        // Override with request parameters if they exist
        $productName = $request->input('product_name') ?? $filters['product_name'] ?? null;
        $category = $request->input('category') ?? $filters['category'] ?? null;
        $stockStatus = $request->input('stock_status') ?? $filters['stock_status'] ?? null;

        // Base query
        $query = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_branch_id', $branch)
            ->where('inv_pro_det_status', 1);

        // Search by Product Name
        if (!empty($productName)) {
            $query->whereHas('product', function($q) use ($productName) {
                $q->where('inv_pro_name', 'LIKE', '%' . $productName . '%');
            });
        }

        // Filter by Category
        if (!empty($category)) {
            $query->where('inv_pro_det_type_id', $category);
        }

        // Filter by Stock Status
        if (!empty($stockStatus)) {
            if ($stockStatus == 'in_stock') {
                $query->where('inv_pro_det_available_qty', '>', 0);
            } elseif ($stockStatus == 'low_stock') {
                $query->where('inv_pro_det_available_qty', '<=', 10)
                      ->where('inv_pro_det_available_qty', '>', 0);
            } elseif ($stockStatus == 'out_of_stock') {
                $query->where('inv_pro_det_available_qty', '=', 0);
            }
        }

        // Get stock data
        $stockData = $query->with(['product', 'type_info'])
            ->orderBy('inv_pro_det_id', 'DESC')
            ->get();

        // If no records found, redirect back with message
        if ($stockData->isEmpty()) {
            return redirect()->back()->with('error', 'No stock records found for the selected filters.');
        }

        // Get company info
        $company_info = Admin_user::company_info($com);

        // Generate PDF
        $pdf = PDF::loadView('inventory.reports.stock_report_pdf', compact('stockData', 'company_info', 'productName', 'category', 'stockStatus'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->download('stock_report_' . date('Y-m-d') . '.pdf');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inv_product_inventory;
use App\Models\Admin_user;
use App\Models\Client_feedback;
use Carbon\Carbon;

class SellMonitoringController extends Controller
{
    /**
     * Display sell monitoring dashboard
     */
 public function sellMonitoring(Request $request)
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;
    
    // Date range filters
    $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
    $userId = $request->get('user_id');
    
    // Base query for sales
    $salesQuery = Inv_product_inventory::where('inv_pro_inv_com_id', $companyId)
        ->where('inv_pro_inv_deal_type', 2)
        ->where('inv_pro_inv_tran_type', 1)
        ->whereBetween('inv_pro_inv_issue_date', [$startDate, $endDate]);
        
    if ($branchId) {
        $salesQuery->where('inv_pro_inv_branch_id', $branchId);
    }
    
    if ($userId) {
        $salesQuery->where('inv_pro_inv_submit_by', $userId);
    }
    
    // Get sales data
    $sales = $salesQuery->with(['getSoldByInfo', 'getCustomerInfo', 'product_detail'])
        ->orderBy('inv_pro_inv_issue_date', 'desc')
        ->get();
        
    // Get client feedback orders (status = 6) within date range
    $feedbackOrdersQuery = Client_feedback::where('cf_company_id', $companyId)
        ->where('cf_status', 6) // Order status
        ->whereBetween('cf_date', [$startDate, $endDate]);
        
    if ($branchId) {
        $feedbackOrdersQuery->where('cf_branch_id', $branchId);
    }
    
    if ($userId) {
        $feedbackOrdersQuery->where('cf_entry_by', $userId);
    }
    
    $feedbackOrders = $feedbackOrdersQuery->with(['salesPerson', 'orderInfo'])
        ->orderBy('cf_date', 'desc')
        ->get();
    
    // Combine sales and feedback orders for comprehensive reporting
    $allSalesData = $sales->merge($feedbackOrders->map(function($feedback) {
        return [
            'type' => 'feedback_order',
            'data' => $feedback,
            'sales_person' => $feedback->salesPerson,
            'date' => $feedback->cf_date,
            'amount' => $feedback->cf_price ?? 0
        ];
    }));
        
    // Get users for filter dropdown
    $users = Admin_user::where('au_company_id', $companyId)
        ->whereIn('au_user_type', [4, 5, 6]) // Admin, Team Leader, User
        ->get();
        
    // Summary statistics - including feedback orders
    $totalSales = $sales->count() + $feedbackOrders->count();
    $totalRevenue = $sales->sum('inv_pro_inv_credit') + $feedbackOrders->sum('cf_price');
    $totalQuantity = $sales->sum('inv_pro_inv_qty');
    
    // Sales by user - including feedback orders
    $salesByUser = [];
    
    // Process inventory sales
    foreach ($sales as $sale) {
        $userId = $sale->inv_pro_inv_submit_by;
        if (!isset($salesByUser[$userId])) {
            $salesByUser[$userId] = [
                'user' => $sale->getSoldByInfo,
                'total_sales' => 0,
                'total_revenue' => 0,
                'total_quantity' => 0,
                'inventory_sales' => 0,
                'feedback_orders' => 0
            ];
        }
        $salesByUser[$userId]['total_sales']++;
        $salesByUser[$userId]['total_revenue'] += $sale->inv_pro_inv_credit;
        $salesByUser[$userId]['total_quantity'] += $sale->inv_pro_inv_qty;
        $salesByUser[$userId]['inventory_sales']++;
    }
    
    // Process feedback orders
    foreach ($feedbackOrders as $order) {
        $userId = $order->cf_entry_by;
        if (!isset($salesByUser[$userId])) {
            $salesByUser[$userId] = [
                'user' => $order->salesPerson,
                'total_sales' => 0,
                'total_revenue' => 0,
                'total_quantity' => 0,
                'inventory_sales' => 0,
                'feedback_orders' => 0
            ];
        }
        $salesByUser[$userId]['total_sales']++;
        $salesByUser[$userId]['total_revenue'] += $order->cf_price ?? 0;
        $salesByUser[$userId]['feedback_orders']++;
    }
    
    return view('page.sell-monitoring.index', compact(
        'sales', 
        'feedbackOrders',
        'allSalesData',
        'users',
        'salesByUser',
        'totalSales',
        'totalRevenue',
        'totalQuantity',
        'startDate',
        'endDate',
        'userId'
    ));
}
    
    /**
     * Get user-wise sales performance
     */
    public function userWiseSales(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $userSales = DB::table('inv_product_inventories as inv')
            ->join('admin_user as user', 'inv.inv_pro_inv_submit_by', '=', 'user.au_id')
            ->select(
                'user.au_id',
                'user.au_name',
                'user.au_user_type',
                DB::raw('COUNT(inv.inv_pro_inv_id) as total_orders'),
                DB::raw('SUM(inv.inv_pro_inv_credit) as total_revenue'),
                DB::raw('SUM(inv.inv_pro_inv_qty) as total_quantity'),
                DB::raw('AVG(inv.inv_pro_inv_credit) as average_order_value')
            )
            ->where('inv.inv_pro_inv_com_id', $companyId)
            ->where('inv.inv_pro_inv_deal_type', 2)
            ->where('inv.inv_pro_inv_tran_type', 1)
            ->whereBetween('inv.inv_pro_inv_issue_date', [$startDate, $endDate])
            ->groupBy('user.au_id', 'user.au_name', 'user.au_user_type')
            ->orderBy('total_revenue', 'desc')
            ->get();
            
        return view('page.sell-monitoring.user-wise', compact('userSales', 'startDate', 'endDate'));
    }
    
    /**
     * Get daily sales report
     */
    public function dailySalesReport(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        $userId = $request->get('user_id');
        $date = $request->get('date', Carbon::now()->format('Y-m-d'));
        
        $salesQuery = Inv_product_inventory::where('inv_pro_inv_com_id', $companyId)
            ->where('inv_pro_inv_deal_type', 2)
            ->where('inv_pro_inv_tran_type', 1)
            ->whereDate('inv_pro_inv_issue_date', $date);
            
        if ($userId) {
            $salesQuery->where('inv_pro_inv_submit_by', $userId);
        }
        
        $dailySales = $salesQuery->with(['getSoldByInfo', 'getCustomerInfo', 'product_detail'])
            ->orderBy('inv_pro_inv_issue_date', 'desc')
            ->get();
            
        $users = Admin_user::where('au_company_id', $companyId)
            ->whereIn('au_user_type', [4, 5, 6])
            ->get();
            
        return view('page.sell-monitoring.daily-report', compact('dailySales', 'users', 'date', 'userId'));
    }
    
    /**
     * API endpoint for sales data (for charts)
     */
    public function getSalesData(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        $days = $request->get('days', 30);
        
        $salesData = DB::table('inv_product_inventories')
            ->select(
                DB::raw('DATE(inv_pro_inv_issue_date) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(inv_pro_inv_credit) as total_revenue'),
                DB::raw('SUM(inv_pro_inv_qty) as total_quantity')
            )
            ->where('inv_pro_inv_com_id', $companyId)
            ->where('inv_pro_inv_deal_type', 2)
            ->where('inv_pro_inv_tran_type', 1)
            ->where('inv_pro_inv_issue_date', '>=', Carbon::now()->subDays($days))
            ->groupBy(DB::raw('DATE(inv_pro_inv_issue_date)'))
            ->orderBy('date')
            ->get();
            
        return response()->json($salesData);
    }
    
    /**
     * Export sales report
     */
    public function exportSalesReport(Request $request)
    {
        $companyId = Auth::user()->au_company_id;
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $userId = $request->get('user_id');
        
        $sales = Inv_product_inventory::where('inv_pro_inv_com_id', $companyId)
            ->where('inv_pro_inv_deal_type', 2)
            ->where('inv_pro_inv_tran_type', 1)
            ->whereBetween('inv_pro_inv_issue_date', [$startDate, $endDate])
            ->when($userId, function($query) use ($userId) {
                return $query->where('inv_pro_inv_submit_by', $userId);
            })
            ->with(['getSoldByInfo', 'getCustomerInfo', 'product_detail'])
            ->get();
            
        // CSV export logic here
        $fileName = 'sales-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];
        
        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Date',
                'Invoice No',
                'Sales Person',
                'Customer',
                'Product',
                'Quantity',
                'Unit Price',
                'Total Amount',
                'Service Charges',
                'Delivery Charges'
            ]);
            
            // Data
            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->inv_pro_inv_issue_date,
                    $sale->inv_pro_inv_invoice_no,
                    $sale->getSoldByInfo->au_name ?? 'N/A',
                    $sale->getCustomerInfo->inv_cus_name ?? 'N/A',
                    $sale->product_detail->inv_pro_det_name ?? 'N/A',
                    $sale->inv_pro_inv_qty,
                    $sale->inv_pro_inv_unit_price,
                    $sale->inv_pro_inv_credit,
                    $sale->inv_pro_inv_service_charges,
                    $sale->inv_pro_inv_delivery_charges
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
@extends('layouts.master')
@section('title', 'Sell Monitoring')
@section('human_resource_class', 'active')
@section('hrm_class', 'active')
@section('sell_monitoring_class', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Sell Monitoring Dashboard</h4>
                </div>
            </div>
            <div class="panel-body">
                
                <!-- Filter Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Filter Options</div>
                            <div class="panel-body">
                                <form method="GET" action="{{ route('sell.monitoring') }}" class="form-horizontal">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Start Date</label>
                                                <input type="text" name="start_date" class="form-control datepicker" 
                                                       value="{{ $startDate }}" required readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">End Date</label>
                                                <input type="text" name="end_date" class="form-control datepicker" 
                                                       value="{{ $endDate }}" required readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Sales Person</label>
                                                <select name="user_id" class="form-control select2">
                                                    <option value="">All Sales Persons</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->au_id }}" 
                                                                {{ $userId == $user->au_id ? 'selected' : '' }}>
                                                            {{ $user->au_name }}- {{ $user->au_user_id }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group" style="margin-top: 25px;">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fa fa-search"></i> Search
                                                </button>
                                                <a href="{{ route('export.sales.report') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&user_id={{ $userId }}" 
                                                   class="btn btn-primary">
                                                    <i class="fa fa-download"></i> Export
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="card card-success">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="fa fa-shopping-cart text-success"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Sales</div>
                                        <div class="stat-digit">{{ $totalSales }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="card card-primary">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="fa fa-money-bill-wave text-primary"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Revenue</div>
                                        <div class="stat-digit">৳{{ number_format($totalRevenue, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="card card-warning">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="fa fa-boxes text-warning"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Total Quantity</div>
                                        <div class="stat-digit">{{ $totalQuantity }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="card card-info">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib">
                                        <i class="fa fa-users text-info"></i>
                                    </div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">Active Sales Persons</div>
                                        <div class="stat-digit">{{ count($salesByUser) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User-wise Performance -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart"></i> User-wise Sales Performance
                                <div class="panel-tools">
                                    <a class="btn btn-sm btn-primary" href="{{ route('user.wise.sales') }}?start_date={{ $startDate }}&end_date={{ $endDate }}">
                                        View Detailed Report
                                    </a>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="user-performance-table">
                                        <thead>
                                            <tr class="bg-primary">
                                                <th>#</th>
                                                <th>Sales Person</th>
                                                <th>Total Orders</th>
                                                <th>Inventory Sales</th>
                                                <th>Feedback Orders</th>
                                                <th>Total Revenue</th>
                                                <th>Average Order Value</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $index = 1; @endphp
                                            @forelse($salesByUser as $userId => $performance)
                                            <tr>
                                                <td>{{ $index++ }}</td>
                                                <td>
                                                    <strong>{{ $performance['user']->au_name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $performance['user']->au_email ?? '' }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-success" style="font-size: 14px;">
                                                        {{ $performance['total_sales'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info" style="font-size: 12px;">
                                                        {{ $performance['inventory_sales'] ?? 0 }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-warning" style="font-size: 12px;">
                                                        {{ $performance['feedback_orders'] ?? 0 }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <strong>৳{{ number_format($performance['total_revenue'], 2) }}</strong>
                                                </td>
                                                <td class="text-right">
                                                    ৳{{ number_format($performance['total_revenue'] / max($performance['total_sales'], 1), 2) }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('sell.monitoring') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&user_id={{ $userId }}" 
                                                       class="btn btn-xs btn-warning" title="View Details">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('daily.sales.report') }}?date={{ $endDate }}&user_id={{ $userId }}" 
                                                       class="btn btn-xs btn-info" title="Daily Report">
                                                        <i class="fa fa-calendar"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-danger">
                                                    <i class="fa fa-exclamation-circle"></i> No sales data found for the selected period.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if(count($salesByUser) > 0)
                                        <tfoot>
                                            <tr class="bg-light">
                                                <td colspan="2" class="text-right"><strong>Grand Total:</strong></td>
                                                <td class="text-center"><strong>{{ $totalSales }}</strong></td>
                                                <td class="text-center"><strong>{{ $sales->count() }}</strong></td>
                                                <td class="text-center"><strong>{{ $feedbackOrders->count() }}</strong></td>
                                                <td class="text-right"><strong>৳{{ number_format($totalRevenue, 2) }}</strong></td>
                                                <td class="text-right"><strong>৳{{ number_format($totalRevenue / max($totalSales, 1), 2) }}</strong></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Details Table -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-list"></i> Sales Details
                                <span class="pull-right">
                                    Showing {{ $sales->count() + $feedbackOrders->count() }} records 
                                    ({{ $sales->count() }} Inventory Sales + {{ $feedbackOrders->count() }} Feedback Orders)
                                </span>
                            </div>
                            <div class="panel-body">
                                @if($sales->count() > 0 || $feedbackOrders->count() > 0)
                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#allSales">All Sales</a></li>
                                    <li><a data-toggle="tab" href="#inventorySales">Inventory Sales ({{ $sales->count() }})</a></li>
                                    <li><a data-toggle="tab" href="#feedbackOrders">Feedback Orders ({{ $feedbackOrders->count() }})</a></li>
                                </ul>

                                <div class="tab-content">
                                    <!-- All Sales Tab -->
                                    <div id="allSales" class="tab-pane fade in active">
                                        <div class="table-responsive" style="margin-top: 15px;">
                                            <table class="table table-bordered table-striped table-hover" id="all-sales-table">
                                                <thead>
                                                    <tr class="bg-success">
                                                        <th>#</th>
                                                        <th>Type</th>
                                                        <th>Date</th>
                                                        <th>Reference No</th>
                                                        <th>Sales Person</th>
                                                        <th>Customer/Query</th>
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-right">Amount</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $allIndex = 1; @endphp
                                                    <!-- Inventory Sales -->
                                                    @foreach($sales as $sale)
                                                    <tr>
                                                        <td>{{ $allIndex++ }}</td>
                                                        <td>
                                                            <span class="label label-info">Inventory Sale</span>
                                                        </td>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($sale->inv_pro_inv_issue_date)->format('d M, Y') }}
                                                        </td>
                                                        <td>
                                                            <strong>{{ $sale->inv_pro_inv_invoice_no }}</strong>
                                                        </td>
                                                        <td>
                                                            @if($sale->getSoldByInfo)
                                                                <span class="text-primary">{{ $sale->getSoldByInfo->au_name }}</span>
                                                            @else
                                                                <span class="text-danger">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($sale->getCustomerInfo)
                                                                {{ $sale->getCustomerInfo->inv_cus_name }}
                                                                @if($sale->getCustomerInfo->inv_cus_mobile)
                                                                    <br>
                                                                    <small class="text-muted">{{ $sale->getCustomerInfo->inv_cus_mobile }}</small>
                                                                @endif
                                                            @else
                                                                <span class="text-danger">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">
                                                                {{ $sale->inv_pro_inv_qty }}
                                                            </span>
                                                        </td>
                                                        <td class="text-right">
                                                            <strong class="text-success">
                                                                ৳{{ number_format($sale->inv_pro_inv_credit, 2) }}
                                                            </strong>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($sale->inv_pro_inv_status == 1)
                                                                <span class="badge badge-success">Active</span>
                                                            @else
                                                                <span class="badge badge-danger">Inactive</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach

                                                    <!-- Feedback Orders -->
                                                    @foreach($feedbackOrders as $order)
                                                    <tr>
                                                        <td>{{ $allIndex++ }}</td>
                                                        <td>
                                                            <span class="label label-warning">Feedback Order</span>
                                                        </td>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($order->cf_date)->format('d M, Y') }}
                                                        </td>
                                                        <td>
                                                            <strong>FB-{{ $order->cf_id }}</strong>
                                                            @if($order->orderInfo)
                                                                <br>
                                                                <small class="text-muted">QB: {{ $order->orderInfo->qb_code ?? 'N/A' }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($order->salesPerson)
                                                                <span class="text-primary">{{ $order->salesPerson->au_name }}</span>
                                                            @else
                                                                <span class="text-danger">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($order->orderInfo)
                                                                {{ $order->orderInfo->qb_name ?? 'N/A' }}
                                                                @if($order->orderInfo->qb_mobile)
                                                                    <br>
                                                                    <small class="text-muted">{{ $order->orderInfo->qb_mobile }}</small>
                                                                @endif
                                                            @else
                                                                <span class="text-danger">Query Info Not Found</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">1</span>
                                                        </td>
                                                        <td class="text-right">
                                                            <strong class="text-success">
                                                                ৳{{ number_format($order->cf_price ?? 0, 2) }}
                                                            </strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success">Order</span>
                                                            <br>
                                                            <small class="text-muted">Status: {{ $order->cf_status }}</small>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Inventory Sales Tab -->
                                    <div id="inventorySales" class="tab-pane fade">
                                        <div class="table-responsive" style="margin-top: 15px;">
                                            <table class="table table-bordered table-striped table-hover" id="inventory-sales-table">
                                                <thead>
                                                    <tr class="bg-info">
                                                        <th>#</th>
                                                        <th>Date</th>
                                                        <th>Invoice No</th>
                                                        <th>Sales Person</th>
                                                        <th>Customer</th>
                                                        <th>Product</th>
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-right">Unit Price</th>
                                                        <th class="text-right">Total Amount</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($sales as $index => $sale)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($sale->inv_pro_inv_issue_date)->format('d M, Y') }}
                                                        </td>
                                                        <td>
                                                            <strong>{{ $sale->inv_pro_inv_invoice_no }}</strong>
                                                        </td>
                                                        <td>
                                                            @if($sale->getSoldByInfo)
                                                                <span class="text-primary">{{ $sale->getSoldByInfo->au_name }}</span>
                                                                <br>
                                                                <small class="text-muted">{{ $sale->getSoldByInfo->au_email }}</small>
                                                            @else
                                                                <span class="text-danger">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($sale->getCustomerInfo)
                                                                {{ $sale->getCustomerInfo->inv_cus_name }}
                                                                @if($sale->getCustomerInfo->inv_cus_mobile)
                                                                    <br>
                                                                    <small class="text-muted">{{ $sale->getCustomerInfo->inv_cus_mobile }}</small>
                                                                @endif
                                                            @else
                                                                <span class="text-danger">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($sale->product_detail)
                                                                {{ $sale->product_detail->inv_pro_det_name }}
                                                            @else
                                                                <span class="text-danger">Product Not Found</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">
                                                                {{ $sale->inv_pro_inv_qty }}
                                                            </span>
                                                        </td>
                                                        <td class="text-right">
                                                            ৳{{ number_format($sale->inv_pro_inv_unit_price, 2) }}
                                                        </td>
                                                        <td class="text-right">
                                                            <strong class="text-success">
                                                                ৳{{ number_format($sale->inv_pro_inv_credit, 2) }}
                                                            </strong>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($sale->inv_pro_inv_status == 1)
                                                                <span class="badge badge-success">Active</span>
                                                            @else
                                                                <span class="badge badge-danger">Inactive</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Feedback Orders Tab -->
                                    <div id="feedbackOrders" class="tab-pane fade">
                                        <div class="table-responsive" style="margin-top: 15px;">
                                            <table class="table table-bordered table-striped table-hover" id="feedback-orders-table">
                                                <thead>
                                                    <tr class="bg-warning">
                                                        <th>#</th>
                                                        <th>Date</th>
                                                        <th>Feedback ID</th>
                                                        <th>Sales Person</th>
                                                        <th>Customer/Query</th>
                                                        <th>Feedback Result</th>
                                                        <th class="text-right">Order Value</th>
                                                        <th class="text-center">Next Followup</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($feedbackOrders as $index => $order)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($order->cf_date)->format('d M, Y') }}
                                                        </td>
                                                        <td>
                                                            <strong>FB-{{ $order->cf_id }}</strong>
                                                            @if($order->orderInfo)
                                                                <br>
                                                                <small class="text-muted">QB: {{ $order->orderInfo->qb_code ?? 'N/A' }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($order->salesPerson)
                                                                <span class="text-primary">{{ $order->salesPerson->au_name }}</span>
                                                                <br>
                                                                <small class="text-muted">{{ $order->salesPerson->au_email ?? '' }}</small>
                                                            @else
                                                                <span class="text-danger">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($order->orderInfo)
                                                                <strong>{{ $order->orderInfo->qb_name ?? 'N/A' }}</strong>
                                                                @if($order->orderInfo->qb_mobile)
                                                                    <br>
                                                                    <small class="text-muted">{{ $order->orderInfo->qb_mobile }}</small>
                                                                @endif
                                                                @if($order->orderInfo->qb_email)
                                                                    <br>
                                                                    <small class="text-muted">{{ $order->orderInfo->qb_email }}</small>
                                                                @endif
                                                            @else
                                                                <span class="text-danger">Query Info Not Found</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $order->cf_result ?? 'N/A' }}
                                                            @if($order->cf_client_message)
                                                                <br>
                                                                <small class="text-muted"><em>"{{ Str::limit($order->cf_client_message, 50) }}"</em></small>
                                                            @endif
                                                        </td>
                                                        <td class="text-right">
                                                            <strong class="text-success">
                                                                ৳{{ number_format($order->cf_price ?? 0, 2) }}
                                                            </strong>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($order->cf_next_date)
                                                                {{ \Carbon\Carbon::parse($order->cf_next_date)->format('d M, Y') }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success">Order Confirmed</span>
                                                            <br>
                                                            <small class="text-muted">Status: {{ $order->cf_status }}</small>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-warning text-center">
                                    <h4><i class="fa fa-exclamation-triangle"></i> No Sales Data Found</h4>
                                    <p>No sales records found for the selected criteria. Please adjust your filters and try again.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-link"></i> Quick Reports
                            </div>
                            <div class="panel-body text-center">
                                <div class="btn-group">
                                    <a href="{{ route('sell.monitoring') }}?start_date={{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}&end_date={{ \Carbon\Carbon::now()->format('Y-m-d') }}" 
                                       class="btn btn-outline-primary">
                                        <i class="fa fa-calendar"></i> This Month
                                    </a>
                                    <a href="{{ route('sell.monitoring') }}?start_date={{ \Carbon\Carbon::now()->subDays(7)->format('Y-m-d') }}&end_date={{ \Carbon\Carbon::now()->format('Y-m-d') }}" 
                                       class="btn btn-outline-success">
                                        <i class="fa fa-calendar-week"></i> Last 7 Days
                                    </a>
                                    <a href="{{ route('sell.monitoring') }}?start_date={{ \Carbon\Carbon::now()->startOfYear()->format('Y-m-d') }}&end_date={{ \Carbon\Carbon::now()->format('Y-m-d') }}" 
                                       class="btn btn-outline-info">
                                        <i class="fa fa-calendar-alt"></i> This Year
                                    </a>
                                    <a href="{{ route('daily.sales.report') }}?date={{ \Carbon\Carbon::now()->format('Y-m-d') }}" 
                                       class="btn btn-outline-warning">
                                        <i class="fa fa-chart-line"></i> Today's Sales
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();

        // Initialize datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        // Initialize DataTables for all tables
        var allSalesTable = $('#all-sales-table').DataTable({
            "dom": '<"top"Bf>rt<"bottom"lip><"clear">',
            "buttons": ['copy', 'csv', 'excel', 'pdf', 'print'],
            "pageLength": 25,
            "order": [[2, 'desc']],
            "language": {
                "search": "Search All Sales:"
            }
        });

        var inventoryTable = $('#inventory-sales-table').DataTable({
            "dom": '<"top"Bf>rt<"bottom"lip><"clear">',
            "buttons": ['copy', 'csv', 'excel', 'pdf', 'print'],
            "pageLength": 25,
            "order": [[1, 'desc']],
            "language": {
                "search": "Search Inventory Sales:"
            }
        });

        var feedbackTable = $('#feedback-orders-table').DataTable({
            "dom": '<"top"Bf>rt<"bottom"lip><"clear">',
            "buttons": ['copy', 'csv', 'excel', 'pdf', 'print'],
            "pageLength": 25,
            "order": [[1, 'desc']],
            "language": {
                "search": "Search Feedback Orders:"
            }
        });

        var userTable = $('#user-performance-table').DataTable({
            "dom": '<"top"Bf>rt<"bottom"lip><"clear">',
            "buttons": ['copy', 'csv', 'excel', 'pdf', 'print'],
            "pageLength": 10,
            "order": [[5, 'desc']],
            "language": {
                "search": "Search Users:"
            }
        });

        // Tab activation handling
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });
</script>

<style>
    .nav-tabs > li.active > a {
        background-color: #f5f5f5 !important;
        font-weight: bold;
    }
    .label {
        padding: 4px 8px;
        font-size: 11px;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
</style>
@endsection
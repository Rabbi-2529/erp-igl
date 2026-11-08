@extends('layouts.master')
@section('title', 'User-wise Sales Report')

@section('inventory_class', 'active')
@section('sell_monitoring_class', 'active')
@section('reports_manage', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>User-wise Sales Performance Report</h4>
                </div>
            </div>
            <div class="panel-body">
                
                <!-- Filter Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Filter Options</div>
                            <div class="panel-body">
                                <form method="GET" action="{{ route('user.wise.sales') }}" class="form-horizontal">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Start Date</label>
                                                <input type="date" name="start_date" class="form-control" 
                                                       value="{{ $startDate }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">End Date</label>
                                                <input type="date" name="end_date" class="form-control" 
                                                       value="{{ $endDate }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group" style="margin-top: 25px;">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fa fa-search"></i> Generate Report
                                                </button>
                                                <a href="{{ route('sell.monitoring') }}" class="btn btn-primary">
                                                    <i class="fa fa-arrow-left"></i> Back to Dashboard
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Period -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> Report Period</h4>
                            <p class="mb-0">
                                <strong>From:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} 
                                <strong>To:</strong> {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}
                                | <strong>Total Users:</strong> {{ $userSales->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- User Performance Table -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-users"></i> Sales Team Performance Summary
                            </div>
                            <div class="panel-body">
                                @if($userSales->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <tr class="bg-primary">
                                                <th>Rank</th>
                                                <th>Sales Person</th>
                                                <th>User Type</th>
                                                <th>Total Orders</th>
                                                <th>Total Revenue</th>
                                                <th>Total Quantity</th>
                                                <th>Average Order Value</th>
                                                <th>Performance Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($userSales as $index => $user)
                                            <tr>
                                                <td class="text-center">
                                                    @if($index == 0)
                                                        <span class="badge badge-warning" style="font-size: 14px;">🥇</span>
                                                    @elseif($index == 1)
                                                        <span class="badge badge-secondary" style="font-size: 14px;">🥈</span>
                                                    @elseif($index == 2)
                                                        <span class="badge badge-danger" style="font-size: 14px;">🥉</span>
                                                    @else
                                                        <strong>#{{ $index + 1 }}</strong>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $user->au_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">ID: {{ $user->au_user_id }}</small>
                                                </td>
                                                <td>
                                                    @if($user->au_user_type == 4)
                                                        <span class="badge badge-danger">Admin</span>
                                                    @elseif($user->au_user_type == 5)
                                                        <span class="badge badge-warning">Team Leader</span>
                                                    @else
                                                        <span class="badge badge-info">Sales Person</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-success" style="font-size: 14px;">
                                                        {{ $user->total_orders }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <strong class="text-primary">৳{{ number_format($user->total_revenue, 2) }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info" style="font-size: 14px;">
                                                        {{ $user->total_quantity }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    ৳{{ number_format($user->average_order_value, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $maxRevenue = $userSales->max('total_revenue');
                                                        $performance = $maxRevenue > 0 ? ($user->total_revenue / $maxRevenue) * 100 : 0;
                                                    @endphp
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar 
                                                            @if($performance >= 80) bg-success
                                                            @elseif($performance >= 50) bg-warning
                                                            @else bg-danger
                                                            @endif" 
                                                            role="progressbar" 
                                                            style="width: {{ $performance }}%"
                                                            aria-valuenow="{{ $performance }}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100">
                                                            {{ number_format($performance, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                                                <td class="text-center"><strong>{{ $userSales->sum('total_orders') }}</strong></td>
                                                <td class="text-right"><strong>৳{{ number_format($userSales->sum('total_revenue'), 2) }}</strong></td>
                                                <td class="text-center"><strong>{{ $userSales->sum('total_quantity') }}</strong></td>
                                                <td class="text-right"><strong>৳{{ number_format($userSales->avg('average_order_value'), 2) }}</strong></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-warning text-center">
                                    <h4><i class="fa fa-exclamation-triangle"></i> No Sales Data Found</h4>
                                    <p>No sales records found for the selected period. Please adjust your date range and try again.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Charts Section -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-chart-pie"></i> Revenue Distribution by User
                            </div>
                            <div class="panel-body">
                                <div id="revenueChart" style="height: 300px;">
                                    <!-- Chart will be rendered here -->
                                    <div class="text-center text-muted">
                                        <i class="fa fa-chart-pie fa-3x"></i>
                                        <p>Revenue distribution chart would be displayed here</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-chart-bar"></i> Orders Distribution by User
                            </div>
                            <div class="panel-body">
                                <div id="ordersChart" style="height: 300px;">
                                    <!-- Chart will be rendered here -->
                                    <div class="text-center text-muted">
                                        <i class="fa fa-chart-bar fa-3x"></i>
                                        <p>Orders distribution chart would be displayed here</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="panel panel-success">
                            <div class="panel-heading">
                                <i class="fa fa-trophy"></i> Top Revenue Generator
                            </div>
                            <div class="panel-body text-center">
                                @if($userSales->count() > 0)
                                <div class="top-performer">
                                    <h3>🥇</h3>
                                    <h4>{{ $userSales->first()->au_name }}</h4>
                                    <p class="text-success">
                                        <strong>৳{{ number_format($userSales->first()->total_revenue, 2) }}</strong>
                                    </p>
                                    <p>
                                        <span class="badge badge-primary">{{ $userSales->first()->total_orders }} Orders</span>
                                        <span class="badge badge-info">{{ $userSales->first()->total_quantity }} Qty</span>
                                    </p>
                                </div>
                                @else
                                <p class="text-muted">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-warning">
                            <div class="panel-heading">
                                <i class="fa fa-chart-line"></i> Most Orders
                            </div>
                            <div class="panel-body text-center">
                                @if($userSales->count() > 0)
                                @php
                                    $mostOrders = $userSales->sortByDesc('total_orders')->first();
                                @endphp
                                <div class="top-performer">
                                    <h3>📊</h3>
                                    <h4>{{ $mostOrders->au_name }}</h4>
                                    <p class="text-warning">
                                        <strong>{{ $mostOrders->total_orders }} Orders</strong>
                                    </p>
                                    <p>
                                        <span class="badge badge-primary">৳{{ number_format($mostOrders->total_revenue, 2) }}</span>
                                        <span class="badge badge-info">{{ $mostOrders->total_quantity }} Qty</span>
                                    </p>
                                </div>
                                @else
                                <p class="text-muted">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <i class="fa fa-boxes"></i> Highest Quantity
                            </div>
                            <div class="panel-body text-center">
                                @if($userSales->count() > 0)
                                @php
                                    $highestQty = $userSales->sortByDesc('total_quantity')->first();
                                @endphp
                                <div class="top-performer">
                                    <h3>📦</h3>
                                    <h4>{{ $highestQty->au_name }}</h4>
                                    <p class="text-info">
                                        <strong>{{ $highestQty->total_quantity }} Units</strong>
                                    </p>
                                    <p>
                                        <span class="badge badge-primary">{{ $highestQty->total_orders }} Orders</span>
                                        <span class="badge badge-success">৳{{ number_format($highestQty->total_revenue, 2) }}</span>
                                    </p>
                                </div>
                                @else
                                <p class="text-muted">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Add any specific JavaScript for this page here
        console.log('User-wise sales report loaded');
    });
</script>
@endsection

<style>
    .top-performer {
        padding: 20px 0;
    }
    .top-performer h3 {
        font-size: 48px;
        margin-bottom: 10px;
    }
    .top-performer h4 {
        margin-bottom: 10px;
        color: #333;
    }
    .progress {
        margin-bottom: 0;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
</style>
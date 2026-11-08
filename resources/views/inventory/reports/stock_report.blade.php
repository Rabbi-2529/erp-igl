@extends('layouts.master')
@section('inventory_class','menu-open')
@section('report_class','menu-open')
@section('stock_report','active')
@section('content')
<section class="content">
    @if(session()->has('check'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('check') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if(session()->has('sub_err'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('sub_err') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if(session()->has('confirm'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('confirm') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <section class="content-header">
        <div class="box">
            <div class="box-header">
                <form action="{{ route('reports.stock.report') }}" method="get" id="searchForm">
                    @csrf
                    <div class="row">
                        <!-- Product Name Search -->
                        <div class="col-xs-3">
                            <label>Product Name</label>
                            <input type="text" name="product_name" autocomplete="off" value="{{ request()->product_name }}" class="form-control" id="product_name" placeholder="Search Product Name">
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="col-xs-3">
                            <label>Category</label>
                            <select name="category" class="form-control" id="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->inv_pro_type_id }}" 
                                        {{ request('category') == $cat->inv_pro_type_id ? 'selected' : '' }}>
                                        {{ $cat->inv_pro_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Stock Status Filter -->
                        <div class="col-xs-3">
                            <label>Stock Status</label>
                            <select name="stock_status" class="form-control" id="stock_status">
                                <option value="">All Status</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                        
                        <!-- Search Buttons -->
                        <div class="col-xs-3" style="padding-top: 25px;">
                            <button type="submit" class="btn btn-info" name="searchbtn" id="searchBtn">
                                <i class="fa fa-search"></i> Search
                            </button>
                            <a href="{{ route('reports.stock.report') }}" class="btn btn-default">
                                <i class="fa fa-refresh"></i> Reset
                            </a>
                            
                            <!-- Download Button -->
                            <a href="javascript:void(0);" onclick="downloadReport()" class="btn btn-warning">
                                <i class="fa fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                    
                    <!-- Active Filters Display -->
                    @if(request()->has('searchbtn') || request()->filled('product_name') || request()->filled('category') || request()->filled('stock_status'))
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-xs-12">
                            <div class="alert alert-info">
                                <strong>Active Filters:</strong>
                                @if(request()->filled('product_name'))
                                    <span class="label label-primary">Product: {{ request()->product_name }}</span>
                                @endif
                                @if(request()->filled('category'))
                                    @php
                                        $catName = \App\Models\Inv_product_type::where('inv_pro_type_id', request('category'))->value('inv_pro_type_name');
                                    @endphp
                                    <span class="label label-success">Category: {{ $catName ?? request('category') }}</span>
                                @endif
                                @if(request()->filled('stock_status'))
                                    <span class="label label-warning">Status: {{ ucfirst(str_replace('_', ' ', request('stock_status'))) }}</span>
                                @endif
                                <span class="label label-default pull-right">Total Records: {{ $stockData->total() }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </form>
            </div>
        </div>
        <h1>Stock Report of {{ Auth::user()->au_company_name }}</h1>
    </section>
    
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Stock Detail</h3>
            <div class="pull-right">
                <span class="label label-success">Total: {{ $stockData->total() }} Products</span>
            </div>
        </div>
        <div class="box-body">
            <table id="example6" class="table table-bordered table-striped table-hover datatable" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">Product Code</th>
                        <th width="20%">Product Name</th>
                        <th width="15%">Category</th>
                        <th width="10%">Buy Price</th>
                        <th width="10%">Sell Price</th>
                        <th width="10%">Available Qty</th>
                        <th width="10%">Stock Value</th>
                        <th width="10%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalStockValue = 0;
                    @endphp
                    @if($stockData->isNotEmpty())
                        @foreach ($stockData as $key => $item)
                            @php
                                $stockValue = $item->inv_pro_det_available_qty * $item->inv_pro_det_buy_price;
                                $totalStockValue += $stockValue;
                                $statusClass = $item->inv_pro_det_available_qty <= 0 ? 'danger' : 
                                               ($item->inv_pro_det_available_qty <= 10 ? 'warning' : 'success');
                                $statusText = $item->inv_pro_det_available_qty <= 0 ? 'Out of Stock' : 
                                             ($item->inv_pro_det_available_qty <= 10 ? 'Low Stock' : 'In Stock');
                            @endphp
                            <tr>
                                <td class="text-center">{{ $stockData->firstItem() + $key }}</td>
                                <td class="text-center">{{ $item->inv_pro_det_pro_id ?? 'N/A' }}</td>
                                <td>{{ $item->product->inv_pro_name ?? 'N/A' }}</td>
                                <td>{{ $item->type_info->inv_pro_type_name ?? 'N/A' }}</td>
                                <td class="text-right">{{ number_format($item->inv_pro_det_buy_price, 2) }}</td>
                                <td class="text-right">{{ number_format($item->inv_pro_det_sell_price, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $statusClass }}" style="font-size: 12px; padding: 5px 10px;">
                                        {{ number_format($item->inv_pro_det_available_qty, 0) }}
                                    </span>
                                </td>
                                <td class="text-right">{{ number_format($stockValue, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $statusClass }}" style="font-size: 11px; padding: 5px 8px;">
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="text-center text-danger">
                                <strong>No stock records found</strong>
                                @if(request()->filled('product_name'))
                                    <br>No product found with name: "{{ request()->product_name }}"
                                @endif
                                @if(request()->filled('category'))
                                    <br>No products found in the selected category
                                @endif
                                @if(request()->filled('stock_status'))
                                    <br>No products found with status: {{ ucfirst(str_replace('_', ' ', request('stock_status'))) }}
                                @endif
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td colspan="7" style="text-align:right;">Total Stock Value:</td>
                        <td style="text-align: right;">{{ number_format($totalStockValue, 2) }}</td>
                        <td style="text-align: center;">---</td>
                    </tr>
                </tfoot>
            </table>
            
            <!-- Pagination -->
            <div class="row">
                <div class="col-md-12">
                    {{ $stockData->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('custom_style')
<style>
    .badge-success {
        background-color: #28a745;
        color: #fff;
    }
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    .badge-danger {
        background-color: #dc3545;
        color: #fff;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table tfoot td {
        font-weight: bold;
    }
    .label {
        margin-right: 5px;
    }
    .table > tbody > tr > td {
        vertical-align: middle;
    }
</style>
@endsection

@section('custom_script')
<script type="text/javascript">
$(document).ready(function() {
    // ===== FIX: Initialize DataTable with retrieve option =====
    var table = $('#example6').DataTable({
        retrieve: true, // This prevents "Cannot reinitialise DataTable" error
        "rowReorder": {
            selector: 'td:nth-child(2)'
        },
        "responsive": true,
        "order": [[0, 'asc']],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        "language": {
            "search": "Search in results:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ products"
        },
        "processing": true,
        "serverSide": false
    });
    
    // Quick search by product name (client-side)
    $('#product_name').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // ===== FIX: Simple pagination handling with page reload =====
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        window.location.href = url;
    });
});

// ===== DOWNLOAD FUNCTION WITH FILTERS =====
function downloadReport() {
    // Get current filter values from the form
    var productName = document.getElementById('product_name').value;
    var category = document.getElementById('category').value;
    var stockStatus = document.getElementById('stock_status').value;
    
    // Build URL with parameters
    var url = "{{ route('reports.stock.report.download') }}?";
    var params = [];
    
    // Add parameters
    if (productName) {
        params.push('product_name=' + encodeURIComponent(productName));
    }
    if (category) {
        params.push('category=' + encodeURIComponent(category));
    }
    if (stockStatus) {
        params.push('stock_status=' + encodeURIComponent(stockStatus));
    }
    
    // Add searchbtn to indicate search was performed
    params.push('searchbtn=true');
    
    // Redirect to download URL
    window.location.href = url + params.join('&');
    
    // Log for debugging
    console.log('Download URL:', url + params.join('&'));
    console.log('Filters Applied:', {
        product_name: productName,
        category: category,
        stock_status: stockStatus
    });
}

// ===== FORM SUBMISSION HANDLING =====
$(document).ready(function() {
    // Handle search form submission
    $('#searchForm').on('submit', function(e) {
        // Allow normal form submission
        return true;
    });
});

// Debug - Log current parameters on page load
$(document).ready(function() {
    console.log('Current Page Parameters:', {
        product_name: '{{ request()->product_name }}',
        category: '{{ request()->category }}',
        stock_status: '{{ request()->stock_status }}',
        searchbtn: '{{ request()->has("searchbtn") ? "true" : "false" }}'
    });
});
</script>
@endsection
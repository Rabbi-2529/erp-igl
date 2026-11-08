@extends('layouts.master')
@section('inventory_class','menu-open')
@section('report_class','menu-open')
@section('sell_reports','active')
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
                <form action="" method="get" id="searchForm">
                    @csrf
                    <div class="row">
                        <!-- Invoice Number Search -->
                        <div class="col-xs-2">
                            <label>Invoice No</label>
                            <input type="text" name="invoice_no" autocomplete="off" value="{{ request()->invoice_no }}" class="form-control" id="invoice_no" placeholder="Search Invoice No">
                        </div>
                        
                        <!-- Start Date -->
                        <div class="col-xs-2">
                            <label>Start Date</label>
                            <?php
                                $startDate = request()->start_date ?? date('Y-m-d', strtotime('-30 days'));
                            ?>
                            <input type="text" name="start_date" autocomplete="off" value="{{ $startDate }}" class="form-control datepicker" id="start_date" placeholder="YYYY-MM-DD">
                        </div>
                        
                        <!-- End Date -->
                        <div class="col-xs-2">
                            <label>End Date</label>
                            <?php
                                $endDate = request()->end_date ?? date('Y-m-d');
                            ?>
                            <input type="text" name="end_date" autocomplete="off" value="{{ $endDate }}" class="form-control datepicker" id="end_date" placeholder="YYYY-MM-DD">
                        </div>
                        
                        <!-- Search Buttons -->
                        <div class="col-xs-4" style="padding-top: 25px;">
                            <button type="submit" class="btn btn-info" name="searchbtn" id="searchBtn">
                                <i class="fa fa-search"></i> Search
                            </button>
                            <a href="{{ route('reports.sell-reports') }}" class="btn btn-default">
                                <i class="fa fa-refresh"></i> Reset
                            </a>
                            
                            <!-- ===== FIXED: Download Button with JavaScript ===== -->
                            <a href="javascript:void(0);" onclick="downloadReport()" class="btn btn-warning">
                                <i class="fa fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                    
                    <!-- Active Filters Display -->
                    @if(request()->has('searchbtn') || request()->filled('invoice_no') || request()->filled('start_date') || request()->filled('end_date'))
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-xs-12">
                            <div class="alert alert-info">
                                <strong>Active Filters:</strong>
                                @if(request()->filled('invoice_no'))
                                    <span class="label label-primary">Invoice: {{ request()->invoice_no }}</span>
                                @endif
                                @if(request()->filled('start_date') && request()->filled('end_date'))
                                    <span class="label label-success">From: {{ request()->start_date }} To: {{ request()->end_date }}</span>
                                @elseif(request()->filled('start_date'))
                                    <span class="label label-success">From: {{ request()->start_date }}</span>
                                @elseif(request()->filled('end_date'))
                                    <span class="label label-success">To: {{ request()->end_date }}</span>
                                @endif
                                <span class="label label-default pull-right">Total Records: {{ $sell_reports->count() }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </form>
            </div>
        </div>
        <h1>Sell Reports of {{ Auth::user()->au_company_name }}</h1>
    </section>
    
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Sell Detail</h3>
            <div class="pull-right">
                <span class="label label-success">Total: {{ $sell_reports->count() }} Invoices</span>
            </div>
        </div>
        <div class="box-body">
            <table id="example6" class="table table-bordered table-striped table-hover datatable" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th width="5%">SL</th>
                        <th width="15%">Invoice No</th>
                        <th width="12%">Issue Date</th>
                        <th width="12%">Issue By</th>
                        <th width="15%">Customer</th>
                        <th width="20%">Description</th>
                        <th width="10%">Amount</th>
                        <th width="11%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sl = 0;
                        $balance = 0;
                    @endphp
                    @if($sell_reports->isNotEmpty())
                        @foreach ($sell_reports as $sell)
                            @php
                                $total = App\Models\Inv_product_inventory::getTotalDebit($sell->inv_pro_inv_invoice_no);
                                $total_amount = $total - App\Models\Inv_product_inventory::get_discount_amount($sell->inv_pro_inv_invoice_no);
                                $balance += $total_amount;
                            @endphp
                            <tr>
                                <td class="text-center">{{ ++$sl }}</td>
                                <td>
                                    <strong>{{ $sell->inv_pro_inv_invoice_no }}</strong>
                                    @if($sell->inv_pro_inv_confirm == 1)
                                        <span class="label label-success">Confirmed</span>
                                    @endif
                                </td>
                                <td>{{ $sell->inv_pro_inv_issue_date }}</td>
                                <td>{{ $sell->users->au_name ?? 'N/A' }}</td>
                                <td>{{ $sell->getCustomerInfo['inv_cus_name'] ?? 'N/A' }}</td>
                                <td>{{ $sell->inv_pro_inv_tran_desc }}</td>
                                <td class="text-right">{{ number_format($total_amount, 2) }}</td>
                                <td style="text-align: center;">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-xs">Action</button>
                                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                                            <li>
                                                <a href="javascript:void(0);" onclick="sell_reports('{{ $sell->inv_pro_inv_invoice_no }}')">
                                                    <i class="fa fa-eye"></i> Details
                                                </a>
                                            </li>
                                            <li class="divider"></li>
                                            @if (Auth::user()->au_user_type == 4)
                                            <li>
                                                <a href="{{ route('sell_edit.sell-pro-edit',['invoice' => $sell->inv_pro_inv_invoice_no]) }}">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li class="divider"></li>
                                            @endif
                                            <li>
                                                <a href="{{ route('reports.sell-pdf',['invoice' => $sell->inv_pro_inv_invoice_no]) }}?print=1" target="_blank">
                                                    <i class="fa fa-print"></i> Print
                                                </a>
                                            </li>
                                            <li class="divider"></li>
                                            <li>
                                                <a href="{{ route('reports.sell-pdf',['invoice' => $sell->inv_pro_inv_invoice_no]) }}?view=1" target="_blank">
                                                    <i class="fa fa-file-text-o"></i> View
                                                </a>
                                            </li>
                                            <li class="divider"></li>
                                            <li>
                                                <a href="{{ route('reports.sell-pdf',['invoice' => $sell->inv_pro_inv_invoice_no]) }}" target="_blank">
                                                    <i class="fa fa-download"></i> Download
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center text-danger">
                                <strong>No records found</strong>
                                @if(request()->filled('invoice_no'))
                                    <br>No invoice found with number: "{{ request()->invoice_no }}"
                                @endif
                                @if(request()->filled('start_date') || request()->filled('end_date'))
                                    <br>No records found in the selected date range
                                @endif
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td colspan="6" style="text-align:right;">Total Amount:</td>
                        <td style="text-align: right;">{{ number_format($balance, 2) }}</td>
                        <td style="text-align: center;">---</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="sellReports" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Invoice Details</h4>
            </div>
            <div class="modal-body" id="sellModalDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom_style')
<style>
    .action_btn{
        border: 1px solid;
        padding: 5px;
    }
    .btn_show{
        background-color: green;
        color: white;
    }
    .btn_delete{
        background-color: red;
        color: white;
    }
    .btn_edit{
        background-color: cornflowerblue;
        color: white;
    }
    .datepicker {
        cursor: pointer;
        width: 100%;
    }
    .label {
        margin-right: 5px;
    }
    .table > tbody > tr > td {
        vertical-align: middle;
    }
    .dropdown-menu > li > a {
        padding: 6px 20px;
    }
</style>
@endsection

@section('custom_script')
<script type="text/javascript">
$(document).ready(function() {
    // Initialize datepickers with YYYY-MM-DD format
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        todayBtn: true
    });

    // Initialize DataTable
    var table = $('#example6').DataTable({
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true,
        order: [[1, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        language: {
            search: "Search in results:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ invoices"
        }
    });
    
    // Quick search by invoice number (client-side)
    $('#invoice_no').on('keyup', function() {
        table.search(this.value).draw();
    });
});

function sell_reports(sell_id) {
    let url = "{{ route('reports.sell-reports-ajax') }}";
    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "GET",
        url: url,
        data: { 
            sell_id: sell_id,
            _token: _token 
        },
        success: function (result) {
            $("#sellModalDetails").html(result);
            $("#sellReports").modal("show");
        },
        error: function(xhr, status, error) {
            alert('Error loading details. Please try again.');
            console.log(error);
        }
    });
}

// ===== DOWNLOAD FUNCTION WITH DATE FILTERS =====
function downloadReport() {
    // Get current filter values from the form
    var startDate = document.getElementById('start_date').value;
    var endDate = document.getElementById('end_date').value;
    var invoiceNo = document.getElementById('invoice_no').value;
    
    // Build URL with parameters
    var url = "{{ route('reports.sell-reports-download') }}?";
    var params = [];
    
    // Always include date filters - use default if empty
    if (!startDate) {
        // Default to 30 days ago
        var date = new Date();
        date.setDate(date.getDate() - 30);
        startDate = date.toISOString().split('T')[0];
    }
    if (!endDate) {
        // Default to today
        var date = new Date();
        endDate = date.toISOString().split('T')[0];
    }
    
    // Add parameters
    params.push('start_date=' + encodeURIComponent(startDate));
    params.push('end_date=' + encodeURIComponent(endDate));
    
    if (invoiceNo) {
        params.push('invoice_no=' + encodeURIComponent(invoiceNo));
    }
    
    // Add searchbtn to indicate search was performed
    params.push('searchbtn=true');
    
    // Redirect to download URL
    window.location.href = url + params.join('&');
    
    // Log for debugging
    console.log('Download URL:', url + params.join('&'));
    console.log('Filters Applied:', {
        start_date: startDate,
        end_date: endDate,
        invoice_no: invoiceNo
    });
}

// Form validation before submit
$('#searchForm').on('submit', function(e) {
    var invoiceNo = $('#invoice_no').val();
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();
    
    // If no filters are filled, show alert
    if (!invoiceNo && !startDate && !endDate) {
        e.preventDefault();
        alert('Please enter at least one search criteria (Invoice No, Start Date, or End Date)');
        return false;
    }
    
    // Validate date format (YYYY-MM-DD)
    if (startDate) {
        var datePattern = /^\d{4}-\d{2}-\d{2}$/;
        if (!datePattern.test(startDate)) {
            e.preventDefault();
            alert('Start date must be in YYYY-MM-DD format');
            return false;
        }
    }
    
    if (endDate) {
        var datePattern = /^\d{4}-\d{2}-\d{2}$/;
        if (!datePattern.test(endDate)) {
            e.preventDefault();
            alert('End date must be in YYYY-MM-DD format');
            return false;
        }
    }
    
    // Validate date range if both dates are filled
    if (startDate && endDate) {
        if (startDate > endDate) {
            e.preventDefault();
            alert('Start date cannot be greater than end date');
            return false;
        }
    }
    
    return true;
});

// Debug - Log current parameters on page load
$(document).ready(function() {
    console.log('Current Page Parameters:', {
        start_date: '{{ request()->start_date }}',
        end_date: '{{ request()->end_date }}',
        invoice_no: '{{ request()->invoice_no }}',
        searchbtn: '{{ request()->has("searchbtn") ? "true" : "false" }}'
    });
});
</script>
@endsection
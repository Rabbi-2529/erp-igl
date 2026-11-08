@extends('layouts.master')
@section('title','Statement Details')
@section('inventory_class','active')
@section('customer_class','active')
@section('inv_customer_acc_class','active')
@section('customer_account_statement','active')
@section('content')
<section class="content">
    <section class="content-header">
        <div class="box">
            <div class="box-header">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="container">
            <form action="{{ route('customer.accounts.account-statement-details', request()->customer_id) }}" method="get">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" id="_token">
                <input type="hidden" name="customer_id" value="{{ request()->customer_id }}">
                <div class="row">
                    <div class="col-xs-3">
                        <input type="text" name="start_date" autocomplete="off"
                            value="{{ request()->start_date ?? '' }}" class="form-control datepicker" id="start_date"
                            placeholder="Enter Start Date">
                    </div>
                    <div class="col-xs-3">
                        <input type="text" name="end_date" autocomplete="off"
                            value="{{ request()->end_date ?? '' }}" class="form-control datepicker" id="end_date"
                            placeholder="Enter End Date">
                    </div>
                    <div class="col-xs-3">
                        <button type="submit" class="btn btn-info" name="searchbtn">
                            <i class="fa fa-search"></i> Search
                        </button>
                        <button type="button" class="btn btn-warning" id="download_statement_btn">
                            <i class="fa fa-download"></i> Download
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <h1>
            Account Statement Details of 
            {{ App\Models\Inv_customer::getCustomerCompany(request()->customer_id)->inv_cus_com_name ?? 'N/A' }}
        </h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            {{ App\Models\Inv_customer::getCustomerCompany(request()->customer_id)->inv_cus_name ?? 'N/A' }}'s
                            Statement Details
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="col-xs-12">
                        <h4>
                            <b>{{ App\Models\Inv_customer::getCustomerCompany(request()->customer_id)->inv_cus_name ?? 'N/A' }}</b>
                            Total Discount is 
                            {{ number_format(App\Models\Inv_product_inventory::getTotalDiscountByCustomerID(request()->customer_id), 2) }}
                            Tk.
                        </h4>
                    </div>
                    <table id="example1" class="table table-bordered table-striped" style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th style="width: 5%;">SL</th>
                                <th style="width: 12%;">Issue Date</th>
                                <th style="width: 15%;">Invoice No</th>
                                <th style="width: 25%;">Description</th>
                                <th style="width: 13%;">Debit</th>
                                <th style="width: 13%;">Credit</th>
                                <th style="width: 12%;">Balance</th>
                                <th style="width: 5%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sl = 0;
                                $total_credit = 0;
                                $total_debit = 0;
                                $running_balance = 0;
                            @endphp

                            @if(isset($inv_custs) && count($inv_custs) > 0)
                                @foreach ($inv_custs as $inv_cust)
                                    @php
                                        $debit = App\Models\Inv_product_inventory::getDebitByInvoiceNo($inv_cust->inv_pro_inv_invoice_no);
                                        $credit = App\Models\Inv_product_inventory::getCreditByInvoiceNo($inv_cust->inv_pro_inv_invoice_no);

                                        $total_debit += $debit;
                                        $total_credit += $credit;
                                        $running_balance += ($credit - $debit);
                                    @endphp
                                    <tr>
                                        <td style="text-align: center;">{{ ++$sl }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($inv_cust->inv_pro_inv_issue_date)->format('d-m-Y') }}</td>
                                        <td>{{ $inv_cust->inv_pro_inv_invoice_no }}</td>
                                        <td class="text-left">{{ $inv_cust->inv_pro_inv_tran_desc ?? 'N/A' }}</td>
                                        <td style="text-align: right;">{{ number_format($debit, 2) }}</td>
                                        <td style="text-align: right;">{{ number_format($credit, 2) }}</td>
                                        <td style="text-align: right;">
                                            @if($running_balance >= 0)
                                                <span style="color: green; font-weight: bold;">{{ number_format($running_balance, 2) }}</span>
                                            @else
                                                <span style="color: red; font-weight: bold;">{{ number_format($running_balance, 2) }}</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="#" onclick="show_invoice_details('{{ $inv_cust->inv_pro_inv_invoice_no }}')" data-toggle="modal" data-target="#showInvoiceDetailsModal" class="btn btn-info btn-xs">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" style="text-align: center; color: red;">
                                        <strong>No transactions found.</strong>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background-color: #f5f5f5;">
                                <td style="text-align: center; font-weight: bolder;">#</td>
                                <td colspan="3" style="text-align:right; font-weight: bolder;">Total:</td>
                                <td style="font-weight: bolder; text-align: right;">
                                    {{ number_format($total_debit, 2) }}
                                </td>
                                <td style="font-weight: bolder; text-align: right;">
                                    {{ number_format($total_credit, 2) }}
                                </td>
                                <td style="font-weight: bolder; text-align: right;">
                                    @if($running_balance >= 0)
                                        <span style="color: green;">{{ number_format($running_balance, 2) }}</span>
                                    @else
                                        <span style="color: red;">{{ number_format($running_balance, 2) }}</span>
                                    @endif
                                </td>
                                <td style="text-align: center; font-weight: bolder;">---</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="showInvoiceDetailsModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Invoice Details</h4>
            </div>
            <div class="modal-body">
                <div class="load-details"></div>
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
    .action_btn {
        border: 1px solid;
        padding: 5px;
    }

    .btn_show {
        background-color: green;
        color: white;
    }

    .btn_delete {
        background-color: red;
        color: white;
    }

    .btn_edit {
        background-color: cornflowerblue;
        color: white;
    }
    
    .datepicker {
        z-index: 9999 !important;
    }
    
    .ui-datepicker {
        z-index: 9999 !important;
    }

    .btn-info {
        background-color: #17a2b8;
        color: white;
        border: 1px solid #17a2b8;
        padding: 5px 10px;
        border-radius: 4px;
        text-decoration: none;
    }

    .btn-info:hover {
        background-color: #138496;
        color: white;
        text-decoration: none;
    }

    .btn-info.btn-xs {
        padding: 2px 5px;
        font-size: 12px;
    }
</style>
@endsection

@section('custom_script')
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize datepicker with correct format
        $("#start_date, #end_date").datepicker({
            dateFormat: 'yy-mm-dd',
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            changeMonth: true,
            changeYear: true,
            zIndex: 9999
        });
    });

    function show_invoice_details(invoice_id) {
        var requestUrl = "{{ route('customer.accounts.accounts.cusotmer-voucher-details') }}";
        var _token = $("#_token").val();
        
        // Show loading
        $(".load-details").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>');
        
        $.ajax({
            type: "GET",
            url: requestUrl,
            data: { 
                invoice_id: invoice_id,
                _token: _token 
            },
            success: function(result) {
                $(".load-details").html(result);
                $("#showInvoiceDetailsModal").modal("show");
            },
            error: function(xhr, status, error) {
                $(".load-details").html('<div class="alert alert-danger">Error loading details: ' + error + '</div>');
            }
        });
    }
</script>

<!-- Download Script -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#download_statement_btn').on('click', function(e) {
            e.preventDefault();
            
            var customerId = "{{ request()->customer_id }}";
            var startDate = $('input[name="start_date"]').val();
            var endDate = $('input[name="end_date"]').val();
            
            // Build download URL
            var downloadUrl = "{{ route('customer.accounts.download-customer-account-statement-details', request()->customer_id) }}";
            var params = [];
            
            if (startDate) {
                params.push('start_date=' + startDate);
            }
            if (endDate) {
                params.push('end_date=' + endDate);
            }
            if (params.length > 0) {
                downloadUrl += '?' + params.join('&');
            }
            
            // Redirect to download
            window.location.href = downloadUrl;
        });
    });
</script>
@endsection
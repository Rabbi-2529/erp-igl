<!DOCTYPE html>
<html>
<head>
    <title>Account Statement Report</title>
    <style type="text/css">
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: center;
            font-weight: bolder;
            background-color: #f5f5f5;
        }

        .table th, .table td {
            border: 1px solid black;
            font-weight: normal;
            padding: 5px 3px;
            font-size: 14px;
        }

        .table td {
            text-align: center;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-left {
            text-align: left !important;
        }

        .page-break {
            page-break-after: always;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            height: 60px;
            width: 60px;
        }

        .header h1 {
            margin: 5px 0;
            font-size: 24px;
        }

        .header h3 {
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }

        .customer-info {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .customer-info table {
            width: 100%;
        }

        .customer-info td {
            padding: 3px 5px;
            font-size: 14px;
        }

        .footer {
            margin-top: 80px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .discount-info {
            margin: 10px 0;
            padding: 8px;
            background-color: #e8f4fd;
            border-left: 4px solid #2196F3;
        }

        .balance-positive {
            color: green;
            font-weight: bold;
        }

        .balance-negative {
            color: red;
            font-weight: bold;
        }

        .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .total-row td {
            font-weight: bold;
        }
    </style>
</head>
<body>

@php
    $sl = 0;
    $total_credit = 0;
    $total_debit = 0;
    $running_balance = 0;
    $countData = $inv_custs->count();
    $slno = 0;
    $customerName = App\Models\Inv_customer::getCustomerNameById(request()->customer_id) ?? 'N/A';
    $customerInfo = App\Models\Inv_customer::getCustomerCompany(request()->customer_id);
    $totalDiscount = App\Models\Inv_product_inventory::getTotalDiscountByCustomerID(request()->customer_id);
@endphp

<center>

@foreach($inv_custs as $inv_cust)

    @if($slno == 0)
        <!-- Header -->
        <div class="header">
            <hr style="margin-top: 25px;">
          
            <h1>{{ Auth::user()->au_company_name ?? 'Company Name' }}</h1>
            <h3>{{ Auth::user()->au_address ?? '' }}</h3>
            <h3>Mobile: {{ Auth::user()->au_mobile ?? '' }}</h3>
            <hr>
        </div>

        <!-- Title -->
        <div style="margin-top: -10px;">
            <h2>Account Statement</h2>
        </div>

        <!-- Customer Info -->
        <div class="customer-info">
            <table>
                <tr>
                    <td style="width: 50%; text-align: left;">
                        <b>Name:</b> {{ $customerName }}
                    </td>
                    <td style="width: 50%; text-align: left;">
                        <b>Mobile:</b> {{ $customerInfo->inv_cus_mobile ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: left;">
                        <b>Address:</b> {{ $customerInfo->inv_cus_address ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Discount Info -->
        <div class="discount-info">
            <b>{{ $customerName }}'s</b> Total Discount is 
            <b>{{ number_format($totalDiscount, 2) }}</b> Tk.
        </div>

        <!-- Table Start -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th style="width: 12%;">Issue Date</th>
                    <th style="width: 15%;">Invoice No</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 13%;">Debit</th>
                    <th style="width: 13%;">Credit</th>
                    <th style="width: 17%;">Balance</th>
                </tr>
            </thead>
            <tbody>
    @endif

    @php
        $countData--;
        $slno++;
        
        $debit = App\Models\Inv_product_inventory::getDebitByInvoiceNo($inv_cust->inv_pro_inv_invoice_no);
        $credit = App\Models\Inv_product_inventory::getCreditByInvoiceNo($inv_cust->inv_pro_inv_invoice_no);
        $balance = $credit - $debit;
        
        $total_debit += $debit;
        $total_credit += $credit;
        $running_balance += $balance;
    @endphp

    <tr>
        <td class="text-center">{{ ++$sl }}</td>
        <td class="text-center">{{ \Carbon\Carbon::parse($inv_cust->inv_pro_inv_issue_date)->format('d-m-Y') }}</td>
        <td>{{ $inv_cust->inv_pro_inv_invoice_no }}</td>
        <td class="text-left">{{ $inv_cust->inv_pro_inv_tran_desc ?? 'N/A' }}</td>
        <td class="text-right">{{ number_format($debit, 2) }}</td>
        <td class="text-right">{{ number_format($credit, 2) }}</td>
        <td class="text-right">
            @if($running_balance >= 0)
                <span class="balance-positive">{{ number_format($running_balance, 2) }}</span>
            @else
                <span class="balance-negative">{{ number_format($running_balance, 2) }}</span>
            @endif
        </td>
    </tr>

    <!-- Page Break Logic -->
    @if($slno % 10 == 0 && $countData != 0)
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            Powered By IGL Web Ltd | +880-1958-666999
        </div>

        <div class="page-break"></div>

        <!-- New Page Header -->
        <div class="header">
            <hr style="margin-top: 25px;">
            <img src="{{ public_path('/asset/image/') }}{{ Auth::user()->au_company_img ?? 'default.png' }}" style="height: 60px; width: 60px;">
            <h1>{{ Auth::user()->au_company_name ?? 'Company Name' }}</h1>
            <h3>{{ Auth::user()->au_address ?? '' }}</h3>
            <h3>Mobile: {{ Auth::user()->au_mobile ?? '' }}</h3>
            <hr>
        </div>

        <table class="table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th style="width: 12%;">Issue Date</th>
                    <th style="width: 15%;">Invoice No</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 13%;">Debit</th>
                    <th style="width: 13%;">Credit</th>
                    <th style="width: 17%;">Balance</th>
                </tr>
            </thead>
            <tbody>
    @endif

    <!-- Last Page Footer -->
    @if($countData == 0)
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="text-center">#</td>
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">{{ number_format($total_debit, 2) }}</td>
                    <td class="text-right">{{ number_format($total_credit, 2) }}</td>
                    <td class="text-right">
                        @if($running_balance >= 0)
                            <span class="balance-positive">{{ number_format($running_balance, 2) }}</span>
                        @else
                            <span class="balance-negative">{{ number_format($running_balance, 2) }}</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="footer">
            Powered By IGL Web Ltd | +880-1958-666999
        </div>
    @endif

@endforeach

</center>

</body>
</html>
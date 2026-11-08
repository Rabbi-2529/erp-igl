<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{env('APP_NAME')}} - Sales Invoice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #ffffff;
            padding: 10px;
            line-height: 1.4;
        }

        .invoice-wrapper {
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
        }

        /* ===== HEADER ===== */
        .header-top {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 6px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-left {
            flex: 1;
        }

        .company-logo {
            display: block;
            max-height: 60px;
            max-width: 200px;
            margin-bottom: 4px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-address {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
            line-height: 1.4;
            font-weight: bold;
        }

        .company-contact {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
            font-weight: bold;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #1a1a1a;
            text-align: right;
        }

        /* ===== CUSTOMER INFO - TABLE ===== */
        .customer-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 11px;
        }

        .customer-table td {
            padding: 3px 5px;
            vertical-align: top;
            font-weight: bold;
        }

        .customer-table .label-text {
            font-weight: bold;
            color: #555;
            min-width: 110px;
            display: inline-block;
        }

        .customer-table .label {
            color: #666;
            font-weight: bold;
            min-width: 95px;
            display: inline-block;
        }

        .customer-table .value {
            font-weight: bold;
        }

        .customer-left-col {
            width: 50%;
            padding-right: 10px;
        }

        .customer-right-col {
            width: 50%;
            padding-left: 10px;
            text-align: left;
        }

        .customer-address-row td {
            padding-top: 4px;
            border-top: 1px dashed #ddd;
        }

        /* ===== TABLE ===== */
        .table-container {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        table thead {
            background: #f0f0f0;
            color: #1a1a1a;
        }

        table thead th {
            padding: 4px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1a1a1a;
        }

        table tbody td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-weight: bold;
            font-size: 10.5px;
        }

        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .product-description {
            font-size: 10px;
            line-height: 1.4;
        }

        .product-description .product-name {
            font-weight: bold;
            font-size: 10.5px;
            display: block;
            margin-bottom: 1px;
            color: #1a1a1a;
        }

        .product-description .name-line {
            display: block;
            width: 100%;
            height: 1px;
            background: #1a1a1a;
            margin: 1px 0 2px 0;
            border: none;
        }

        .product-description .product-desc {
            font-size: 9.5px;
            color: #444;
            display: block;
            margin-bottom: 1px;
            word-wrap: break-word;
            font-weight: bold;
        }

        .product-description .product-name .product-desc {
            display: inline;
            margin-bottom: 0;
        }

        .product-description .serial {
            font-size: 9px;
            color: #555;
            display: block;
            margin-top: 1px;
            font-weight: bold;
        }

        .product-description .warranty {
            color: #c0392b;
            font-weight: bold;
            font-size: 9px;
            display: block;
            margin-top: 1px;
        }

        /* ===== TOTALS ===== */
        .totals-wrapper {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin: 5px 0;
        }

        /* ===== PREVIOUS BALANCE BOX ===== */
        .balance-box {
            border: 2px solid #1a1a1a;
            background: #f8f9fa;
            padding: 6px 10px;
            border-radius: 4px;
            width: 35%;
            min-width: 150px;
        }

        .balance-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .balance-box table td {
            padding: 2px 5px;
            border: none;
            font-size: 10px;
            font-weight: bold;
        }

        .balance-box .label {
            text-align: left;
            color: #555;
            font-weight: bold;
            padding-right: 5px;
        }

        .balance-box .amount {
            text-align: right;
            font-weight: bold;
        }

        .balance-box .header-row td {
            font-weight: bold;
            font-size: 10px;
            padding-top: 3px;
            padding-bottom: 3px;
            border-bottom: 1px solid #1a1a1a;
            text-align: center;
        }

        .balance-box .total-due td {
            border-top: 1px solid #1a1a1a;
            font-weight: bold;
            font-size: 10.5px;
            padding-top: 3px;
            color: #c0392b;
        }

        .balance-box .previous-due .amount {
            color: #c0392b;
            font-weight: bold;
        }

        /* ===== TOTALS - RIGHT ===== */
        .totals-right {
            width: 35%;
            min-width: 150px;
        }

        .totals-right table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-right td {
            padding: 2px 5px;
            border: none;
            font-size: 10px;
            font-weight: bold;
        }

        .totals-right .label {
            text-align: right;
            font-weight: bold;
            color: #555;
            padding-right: 8px;
        }

        .totals-right .amount {
            text-align: right;
            font-weight: bold;
            min-width: 75px;
        }

        .totals-right .total-row td {
            border-top: 1px solid #1a1a1a;
            font-weight: bold;
            font-size: 10px;
            padding-top: 3px;
        }

        .totals-right .grand-total td {
            border-top: 2px double #1a1a1a;
            font-size: 11px;
            font-weight: bold;
            padding-top: 3px;
        }

        .totals-right .grand-total .amount {
            color: #c0392b;
            font-size: 11.5px;
            font-weight: bold;
        }

        /* ===== AMOUNT IN WORDS ===== */
        .amount-words {
            padding: 4px 10px;
            margin: 4px 0;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            font-size: 10.5px;
            font-weight: bold;
        }

        .amount-words .label {
            color: #555;
            font-weight: normal;
        }

        /* ===== TERMS ===== */
        .terms-section {
            margin: 4px 0;
            padding: 4px 10px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
        }

        .terms-section .title {
            font-weight: bold;
            font-size: 10.5px;
            margin-bottom: 2px;
            color: #1a1a1a;
        }

        .terms-section ul {
            padding-left: 15px;
            margin: 0;
        }

        .terms-section ul li {
            font-size: 9.5px;
            color: #555;
            margin-bottom: 1px;
            font-weight: bold;
        }

        /* ===== SIGNATURES ===== */
        .signatures {
            margin: 10px 0 4px 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 30px;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-box .label {
            font-weight: bold;
            font-size: 10.5px;
            margin-bottom: 8px;
            color: #1a1a1a;
        }

        .signature-box .line {
            border-top: 1px solid #1a1a1a;
            padding-top: 6px;
            font-size: 10px;
            color: #1a1a1a;
            font-weight: bold;
            min-height: 30px;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9.5px;
            color: #888;
            font-weight: bold;
        }

        /* ===== PAGE NUMBER ===== */
        .page-number {
            text-align: center;
            font-size: 9.5px;
            color: #888;
            margin-top: 3px;
            font-weight: bold;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            body {
                padding: 6px;
                font-size: 12px;
            }
            
            .no-print {
                display: none;
            }
            
            table tbody tr {
                page-break-inside: avoid;
            }

            .amount-words {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .terms-section {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            table thead {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .balance-box {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Wider left/right margin so binding or a file cover doesn't hide the edges.
           !important: dompdf otherwise lets the "* { margin: 0 }" reset wipe it out. */
        @page {
            size: A4;
            margin: 5mm 15mm !important;
        }
    </style>
</head>
<body>
    @php
        $sl = 0;
        $balance = 0;
        $totalDiscount = 0;
        $deliveryCharge = 0;
        $subTotal = 0;
        $paidAmount = 0;
        $outstanding = 0;
        $invoiceDue = 0;
        $totalDue = 0;
        
        function limitWords($text, $limit = 20) {
            if (empty($text)) return '';
            $words = explode(' ', $text);
            if (count($words) <= $limit) return $text;
            return implode(' ', array_slice($words, 0, $limit)) . '...';
        }
    @endphp

    <div class="invoice-wrapper">
        <!-- ===== HEADER ===== -->
        <div class="header-top">
            <div class="company-left">
                <img class="company-logo" src="{{ asset('assets/img/icons/logo.png') }}" alt="{{ $company_info->au_company_name ?? 'Logo' }}">
                <div class="company-address">
                    {{ $company_info->au_address ?? 'Popular Tower, Level 5, Shop # 508, B2, Mirpur Road (Laboratory Road) New Elephant Road, Dhaka-1205' }}
                </div>
                <div class="company-contact">
                    Phone: {{ $company_info->au_phone ?? '01840893303' }} | Mobile: {{ $company_info->au_mobile ?? '01620815977' }} | Email: {{ $company_info->au_email ?? 'info@company.com' }}
                </div>
            </div>
            <div class="invoice-title">SALES INVOICE</div>
        </div>

        <!-- ===== CUSTOMER INFO - TABLE ===== -->
        <table class="customer-table">
            <tr>
                <td class="customer-left-col">
                    <div><span class="label-text">Company Name:</span> {{ isset($invoice_detail->getCustomerInfo->inv_cus_name) ? $invoice_detail->getCustomerInfo->inv_cus_name : 'N/A' }}</div>
                    <div><span class="label-text">Cell:</span> {{ isset($invoice_detail->getCustomerInfo->inv_cus_mobile) ? $invoice_detail->getCustomerInfo->inv_cus_mobile : 'N/A' }}</div>
                    <div><span class="label-text">Sales Person:</span> {{ isset($invoice_detail->getSoldByInfo->au_name) ? $invoice_detail->getSoldByInfo->au_name : 'N/A' }}</div>
                    <div><span class="label-text">Approved By:</span> {{ isset($invoice_detail->getSoldByInfo->au_name) ? $invoice_detail->getSoldByInfo->au_name : 'N/A' }}</div>
                </td>
                <td class="customer-right-col">
                    <div><span class="label">Invoice ID:</span> <span class="value">{{ $invoice_detail->inv_pro_inv_invoice_no ?? 'N/A' }}</span></div>
                    <div><span class="label">Date:</span> <span class="value">{{ $invoice_detail->inv_pro_inv_issue_date ? \Carbon\Carbon::parse($invoice_detail->inv_pro_inv_issue_date)->format('d M Y') : date('d M Y') }}</span></div>
                    <div><span class="label">Sales Mode:</span> <span class="value">Cash</span></div>
                    <div><span class="label">Challan No:</span> <span class="value">{{ $invoice_detail->inv_pro_inv_id ?? 'N/A' }}</span></div>
                </td>
            </tr>
            <tr class="customer-address-row">
                <td colspan="2">
                    <span class="label-text">Address:</span> {{ isset($invoice_detail->getCustomerInfo->inv_cus_address) ? $invoice_detail->getCustomerInfo->inv_cus_address : 'N/A' }}
                </td>
            </tr>
        </table>

        <!-- ===== TABLE ===== -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 60%;">DESCRIPTION</th>
                        <th style="width: 10%;">PRICE</th>
                        <th style="width: 8%;">QTY</th>
                        <th style="width: 8%;">DIS</th>
                        <th style="width: 10%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice as $sell)
                        @php
                            $slno = App\Models\Inv_product_inventory::ProductSerialSell($sell->inv_pro_inv_id);
                            $sl++;
                            $balance = $balance + $sell->inv_pro_inv_debit;
                            
                            $productName = 'N/A';
                            $productDescription = '';
                            $warrantyDays = 0;
                            
                            $productDetail = $sell->getProductWarranty;
                            
                            if ($productDetail) {
                                $product = $productDetail->product;
                                
                                if ($product) {
                                    if (!empty($product->inv_pro_name)) {
                                        $productName = $product->inv_pro_name;
                                    } elseif (!empty($product->inv_pro_description)) {
                                        $productName = $product->inv_pro_description;
                                    }
                                    
                                    if (!empty($product->inv_pro_description)) {
                                        $productDescription = limitWords($product->inv_pro_description, 20);
                                    }
                                } else {
                                    $productId = $productDetail->inv_pro_det_pro_id;
                                    if (!empty($productId)) {
                                        $directProduct = \App\Models\Inv_product::find($productId);
                                        if ($directProduct) {
                                            if (!empty($directProduct->inv_pro_name)) {
                                                $productName = $directProduct->inv_pro_name;
                                            } elseif (!empty($directProduct->inv_pro_description)) {
                                                $productName = $directProduct->inv_pro_description;
                                            }
                                            if (!empty($directProduct->inv_pro_description)) {
                                                $productDescription = limitWords($directProduct->inv_pro_description, 20);
                                            }
                                        }
                                    }
                                }
                                
                                $warrantyDays = $productDetail->inv_pro_det_pro_warranty ?? 0;
                                
                                if ($productName == 'N/A') {
                                    $productName = $productDetail->inv_pro_det_pro_description ?? 'N/A';
                                }
                            }
                            
                        @endphp
                    <tr>
                        <td class="text-center">{{ $sl }}</td>
                        <td>
                            <div class="product-description">
                                @if ($sell->inv_pro_inv_tran_type == 10)
                                    <span class="product-name">{{ $sell->inv_pro_inv_tran_desc }}</span>
                                @else
                                    {{-- Model (Description) on one line --}}
                                    <span class="product-name">{{ $productName }}@if(!empty($productDescription) && $productDescription != $productName) <span class="product-desc">({{ $productDescription }})</span>@endif</span>
                                    <span class="name-line"></span>

                                    @if(!empty($slno))
                                        <span class="serial">SL#{{ implode(', ', $slno) }}</span>
                                    @endif
                                    @if($warrantyDays > 0)
                                        <span class="warranty">{{ $warrantyDays }} Days warranty.</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="text-right">{{ number_format($sell->inv_pro_inv_unit_price, 2) }}</td>
                        <td class="text-center">{{ number_format($sell->inv_pro_inv_qty, 2) }}</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">{{ number_format($sell->inv_pro_inv_debit, 2) }}</td>
                    </tr>
                    @endforeach

                    @foreach ($services_delivery as $charge)
                        @php
                            if($charge->inv_pro_inv_tran_type == 11) {
                                $deliveryCharge = $charge->inv_pro_inv_debit;
                                $balance = $balance + $charge->inv_pro_inv_debit;
                            } elseif($charge->inv_pro_inv_tran_type == 12) {
                                $totalDiscount = $charge->inv_pro_inv_credit;
                                $balance = $balance - $totalDiscount;
                            }
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- ===== TOTALS ===== -->
        @php
            $subTotal = $balance + $totalDiscount - $deliveryCharge;
            $paidAmount = isset($invoice_detail->inv_pro_inv_paid_amount) ? $invoice_detail->inv_pro_inv_paid_amount : 0;
            $outstanding = $balance - $paidAmount;
            $invoiceDue = $outstanding;
            $totalDue = $previousDue + $invoiceDue;
        @endphp

        <div class="totals-wrapper">
            <!-- ===== PREVIOUS BALANCE - BOX ===== -->
            <div class="balance-box">
                <table>
                    <tr class="header-row">
                        <td colspan="2">PREVIOUS BALANCE</td>
                    </tr>
                    <tr class="previous-due">
                        <td class="label">Previous Due</td>
                        <td class="amount" style="color: #c0392b; font-weight: bold;">{{ number_format($previousDue, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Invoice Due</td>
                        <td class="amount">{{ number_format($invoiceDue, 2) }}</td>
                    </tr>
                    <tr class="total-due">
                        <td class="label">Total Due</td>
                        <td class="amount">{{ number_format($totalDue, 2) }}</td>
                    </tr>
                </table>
            </div>

            <!-- ===== TOTALS - RIGHT ===== -->
            <div class="totals-right">
                <table>
                    <tr>
                        <td class="label">Sub Total:</td>
                        <td class="amount">{{ number_format($subTotal, 2) }}</td>
                    </tr>
                    @if($totalDiscount > 0)
                    <tr>
                        <td class="label">Discount:</td>
                        <td class="amount" style="color: #c0392b;">- {{ number_format($totalDiscount, 2) }}</td>
                    </tr>
                    @endif
                    @if($deliveryCharge > 0)
                    <tr>
                        <td class="label">Delivery:</td>
                        <td class="amount">{{ number_format($deliveryCharge, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td class="label">Gross Total:</td>
                        <td class="amount">{{ number_format($balance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Paid Amount:</td>
                        <td class="amount">{{ number_format($paidAmount, 2) }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td class="label">Outstanding:</td>
                        <td class="amount">{{ number_format($outstanding, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ===== AMOUNT IN WORDS ===== -->
        <div class="amount-words">
            <span class="label">Amount in words:</span> 
            @if(class_exists('App\Models\NumberConverter'))
                {{ App\Models\NumberConverter::number_to_text($outstanding) }}
            @else
                {{ number_format($outstanding, 2) }} Taka Only
            @endif
        </div>

        <!-- ===== TERMS & CONDITIONS ===== -->
        <div class="terms-section">
            <div class="title">Terms & Conditions:</div>
            <ul>
                @php
                    $terms = isset($company_info->au_terms_conditions) ? $company_info->au_terms_conditions : '1. VAT & Taxes are not included in the above price.';
                    $termsArray = explode("\n", $terms);
                @endphp
                @foreach($termsArray as $term)
                    @if(trim($term))
                        <li>{{ trim($term) }}</li>
                    @endif
                @endforeach
            </ul>
        </div>

        <!-- ===== SIGNATURES ===== -->
        <div class="signatures">
            <div class="signature-box">
                <div class="label">Authorized Signature</div>
                <div class="line">{{ isset($invoice_detail->getSoldByInfo->au_name) ? $invoice_detail->getSoldByInfo->au_name : '' }}</div>
            </div>
            
            <div class="signature-box">
                <div class="label">Customer Signature</div>
                <div class="line">{{ isset($invoice_detail->getCustomerInfo->inv_cus_name) ? $invoice_detail->getCustomerInfo->inv_cus_name : '' }}</div>
            </div>
        </div>

        <!-- ===== PAGE NUMBER ===== -->
        <div class="page-number">Page 1 of 1</div>

        <!-- ===== FOOTER ===== -->
        <div class="footer">
            Powered By: {{ isset($company_info->au_footer_text) ? $company_info->au_footer_text : 'IGL Web Ltd.' }} | {{ isset($company_info->au_contact) ? $company_info->au_contact : '+880-1958-666999' }}
        </div>
    </div>

    <script>
        (function(){
            @if(session()->has('print_invoice'))
                window.print();
            @endif
        })();
    </script>
</body>
</html>
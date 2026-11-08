<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{env('APP_NAME')}} - Purchase Invoice</title>
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

        /* Layout tables (used instead of flex so the same view also works in the PDF) */
        table.layout {
            width: 100%;
            border-collapse: collapse;
        }

        table.layout td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        /* ===== HEADER ===== */
        .header-top {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .header-top td {
            vertical-align: middle !important;
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

        .invoice-barcode {
            text-align: right;
            margin-top: 4px;
        }

        .invoice-barcode img {
            height: 40px;
            max-width: 100%;
        }

        .invoice-barcode .barcode-text {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        /* serial number QR code inside an item row */
        table.item-detail {
            width: 100%;
            border-collapse: collapse;
        }

        table.item-detail td {
            border: none !important;
            padding: 0 !important;
            vertical-align: top;
            background: transparent;
        }

        table.item-detail td.serial-qr {
            width: 78px;
            text-align: right;
            padding-left: 6px !important;
        }

        .serial-qr img {
            width: 72px;
            height: 72px;
        }

        .serial-qr .qr-caption {
            font-size: 7.5px;
            color: #555;
            text-align: center;
        }

        /* ===== SUPPLIER INFO ===== */
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

        /* ===== ITEMS TABLE ===== */
        .table-container {
            margin: 5px 0;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        table.items thead {
            background: #f0f0f0;
            color: #1a1a1a;
        }

        table.items thead th {
            padding: 4px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1a1a1a;
        }

        table.items tbody td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-weight: bold;
            font-size: 10.5px;
        }

        table.items tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
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

        .product-description .short-note {
            color: #c0392b;
            font-size: 9px;
            display: block;
            margin-top: 1px;
            font-weight: bold;
        }

        /* ===== TOTALS ===== */
        .totals-wrapper {
            margin: 5px 0;
        }

        .balance-box {
            border: 2px solid #1a1a1a;
            background: #f8f9fa;
            padding: 6px 10px;
            border-radius: 4px;
        }

        .balance-box table,
        .totals-right table {
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
            margin: 30px 0 4px 0;
        }

        .signature-box {
            text-align: center;
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

            table.items tbody tr {
                page-break-inside: avoid;
            }

            .amount-words,
            .terms-section,
            table.items thead,
            .balance-box {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Wider left/right margin so binding or a file cover doesn't hide the edges.
           !important: dompdf (PDF download) otherwise lets the "* { margin: 0 }" reset wipe it out. */
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
        $supplier = $invoice_detail->getSupplierInfo;
    @endphp

    <div class="invoice-wrapper">
        <!-- ===== HEADER ===== -->
        <table class="layout header-top">
            <tr>
                <td>
                    <div class="company-name">{{ $company_info->au_company_name ?? 'HR TECHNOLOGY' }}</div>
                    <div class="company-address">
                        {{ $company_info->au_address ?? 'Popular Tower, Level 5, Shop # 508, B2, Mirpur Road (Laboratory Road) New Elephant Road, Dhaka-1205' }}
                    </div>
                    <div class="company-contact">
                        Phone: {{ $company_info->au_phone ?? '01840893303' }} | Mobile: {{ $company_info->au_mobile ?? '01620815977' }} | Email: {{ $company_info->au_email ?? 'info@company.com' }}
                    </div>
                </td>
                <td style="width: 38%;">
                    <div class="invoice-title">PURCHASE INVOICE</div>
                    <div class="invoice-barcode">
                        <img src="{{ \App\Helpers\Barcode128::dataUri($invoice_detail->inv_pro_inv_invoice_no, 2, 40) }}" alt="{{ $invoice_detail->inv_pro_inv_invoice_no }}">
                        <div class="barcode-text">{{ $invoice_detail->inv_pro_inv_invoice_no }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ===== SUPPLIER INFO ===== -->
        <table class="customer-table">
            <tr>
                <td class="customer-left-col">
                    <div><span class="label-text">Supplier Name:</span> {{ $supplier->inv_sup_com_name ?? 'N/A' }}</div>
                    <div><span class="label-text">Contact Person:</span> {{ $supplier->inv_sup_person ?? 'N/A' }}</div>
                    <div><span class="label-text">Cell:</span> {{ $supplier->inv_sup_mobile ?? 'N/A' }}</div>
                    <div><span class="label-text">Purchased By:</span> {{ $invoice_detail->getSoldByInfo->au_name ?? 'N/A' }}</div>
                </td>
                <td class="customer-right-col">
                    <div><span class="label">Invoice ID:</span> {{ $invoice_detail->inv_pro_inv_invoice_no ?? 'N/A' }}</div>
                    <div><span class="label">Date:</span> {{ $invoice_detail->inv_pro_inv_issue_date ? \Carbon\Carbon::parse($invoice_detail->inv_pro_inv_issue_date)->format('d M Y') : date('d M Y') }}</div>
                    <div><span class="label">Purchase Mode:</span> {{ $paidAmount > 0 ? 'Cash' : 'Credit' }}</div>
                    <div><span class="label">Challan No:</span> {{ $invoice_detail->inv_pro_inv_id ?? 'N/A' }}</div>
                </td>
            </tr>
            <tr class="customer-address-row">
                <td colspan="2">
                    <span class="label-text">Address:</span> {{ $supplier->inv_sup_address ?? 'N/A' }}
                </td>
            </tr>
        </table>

        <!-- ===== ITEMS ===== -->
        <div class="table-container">
            <table class="items">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 55%;">DESCRIPTION</th>
                        <th style="width: 11%;">PRICE</th>
                        <th style="width: 9%;">QTY</th>
                        <th style="width: 8%;">SHORT</th>
                        <th style="width: 12%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice as $buy)
                        @php
                            $slno = App\Models\Inv_product_inventory::ProductSerial($buy->inv_pro_inv_id);
                            $sl++;
                            $balance = $balance + $buy->inv_pro_inv_debit;

                            $productName = 'N/A';
                            $productDescription = '';
                            $warrantyDays = 0;

                            $productDetail = $buy->getProductWarranty;

                            if ($productDetail) {
                                $product = $productDetail->product ?? \App\Models\Inv_product::find($productDetail->inv_pro_det_pro_id);

                                if ($product) {
                                    $productName = $product->inv_pro_name ?: ($product->inv_pro_description ?: 'N/A');
                                    if (!empty($product->inv_pro_description)) {
                                        $productDescription = \Illuminate\Support\Str::words($product->inv_pro_description, 20, '...');
                                    }
                                }

                                if ($productName == 'N/A') {
                                    $productName = $productDetail->inv_pro_det_pro_description ?? 'N/A';
                                }

                                $warrantyDays = $productDetail->inv_pro_det_pro_warranty ?? 0;
                            }

                            $totalQty = $buy->inv_pro_inv_total_qty ?: $buy->inv_pro_inv_qty;

                            // QR code holds the serial numbers, one per line, so a scanner types them with Enter
                            $serials = array_values(array_filter(array_map('trim', $slno)));
                            $serialQr = count($serials)
                                ? 'data:image/svg+xml;base64,' . base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->margin(0)->errorCorrection('L')->generate(implode("\n", $serials)))
                                : null;
                        @endphp
                    <tr>
                        <td class="text-center">{{ $sl }}</td>
                        <td>
                            <table class="item-detail">
                                <tr>
                                    <td>
                                        <div class="product-description">
                                            {{-- Model (Description) on one line --}}
                                            <span class="product-name">{{ $productName }}@if(!empty($productDescription) && $productDescription != $productName) <span class="product-desc">({{ $productDescription }})</span>@endif</span>
                                            <span class="name-line"></span>
                                            @if(count($serials))
                                                <span class="serial">SL#{{ implode(', ', $serials) }}</span>
                                            @endif
                                            @if($warrantyDays > 0)
                                                <span class="warranty">{{ $warrantyDays }} Days warranty.</span>
                                            @endif
                                            @if($buy->inv_pro_inv_short_qty > 0 && !empty($buy->inv_pro_inv_short_remarks))
                                                <span class="short-note">Short: {{ $buy->inv_pro_inv_short_remarks }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    @if($serialQr)
                                    <td class="serial-qr"@if(count($serials) > 6) style="width: 100px;"@endif>
                                        <img src="{{ $serialQr }}" alt="Serial numbers"@if(count($serials) > 6) style="width: 94px; height: 94px;"@endif>
                                        <div class="qr-caption">{{ count($serials) }} SL</div>
                                    </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                        <td class="text-right">{{ number_format($buy->inv_pro_inv_unit_price, 2) }}</td>
                        <td class="text-center">{{ number_format($totalQty, 2) }}</td>
                        <td class="text-center">{{ number_format($buy->inv_pro_inv_short_qty ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($buy->inv_pro_inv_debit, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- ===== TOTALS ===== -->
        @php
            $grossTotal = $balance - $totalDiscount;
            $outstanding = $grossTotal - $paidAmount;
            $totalDue = $previousDue + $outstanding;
        @endphp

        <table class="layout totals-wrapper">
            <tr>
                <td style="width: 38%;">
                    <div class="balance-box">
                        <table>
                            <tr class="header-row">
                                <td colspan="2">PREVIOUS BALANCE</td>
                            </tr>
                            <tr>
                                <td class="label">Previous Payable</td>
                                <td class="amount" style="color: #c0392b;">{{ number_format($previousDue, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Invoice Due</td>
                                <td class="amount">{{ number_format($outstanding, 2) }}</td>
                            </tr>
                            <tr class="total-due">
                                <td class="label">Total Payable</td>
                                <td class="amount">{{ number_format($totalDue, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td></td>
                <td style="width: 38%;" class="totals-right">
                    <table>
                        <tr>
                            <td class="label">Sub Total:</td>
                            <td class="amount">{{ number_format($balance, 2) }}</td>
                        </tr>
                        @if($totalDiscount > 0)
                        <tr>
                            <td class="label">Discount:</td>
                            <td class="amount" style="color: #c0392b;">- {{ number_format($totalDiscount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td class="label">Gross Total:</td>
                            <td class="amount">{{ number_format($grossTotal, 2) }}</td>
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
                </td>
            </tr>
        </table>

        <!-- ===== AMOUNT IN WORDS ===== -->
        <div class="amount-words">
            <span class="label">Amount in words:</span>
            @if(class_exists('App\Models\NumberConverter'))
                {{ App\Models\NumberConverter::number_to_text($grossTotal) }}
            @else
                {{ number_format($grossTotal, 2) }} Taka Only
            @endif
        </div>

        <!-- ===== TERMS & CONDITIONS ===== -->
        <div class="terms-section">
            <div class="title">Terms & Conditions:</div>
            <ul>
                @php
                    $terms = isset($company_info->au_terms_conditions) ? $company_info->au_terms_conditions : '1. Goods received in good condition as per the quantities above.';
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
        <table class="layout signatures">
            <tr>
                <td style="width: 42%;" class="signature-box">
                    <div class="label">Received By</div>
                    <div class="line">{{ $invoice_detail->getSoldByInfo->au_name ?? '' }}</div>
                </td>
                <td></td>
                <td style="width: 42%;" class="signature-box">
                    <div class="label">Supplier Signature</div>
                    <div class="line">{{ $supplier->inv_sup_com_name ?? '' }}</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Powered By: {{ isset($company_info->au_footer_text) ? $company_info->au_footer_text : 'IGL Web Ltd.' }} | {{ isset($company_info->au_contact) ? $company_info->au_contact : '+880-1958-666999' }}
        </div>
    </div>

    @if(!empty($autoPrint))
    <script>
        window.onload = function () { window.print(); };
    </script>
    @endif
</body>
</html>

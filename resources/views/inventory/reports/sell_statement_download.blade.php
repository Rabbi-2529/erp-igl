<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ env('APP_NAME') }} - Sell Statement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            position: relative;
            width: 21cm;
            min-height: 29.7cm;
            margin: 0;
            padding: 8px 8px 8px 2px;
            color: #1a1a1a;
            background: #FFFFFF;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
        }

        /* ===== HEADER ===== */
        .header {
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 8px;
            margin-bottom: 12px;
            text-align: center;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a1a;
            
            letter-spacing: 2px;
        }
        .company-details {
            color: #555;
            font-size: 11px;
            margin-top: 2px;
        }
        .company-details span {
            margin: 0 4px;
        }

        /* ===== TITLE ===== */
        .report-title {
            text-align: center;
            margin: 10px 0;
        }
        .report-title h2 {
            display: inline-block;
            background: #1a1a1a;
            color: #ffffff;
            padding: 6px 30px;
            font-size: 18px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* ===== FILTER INFO ===== */
        .filter-info {
            background: #f0f4f8;
            padding: 6px 10px;
            margin: 6px 0 10px 0;
            border-left: 4px solid #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            font-size: 11px;
        }
        .filter-info .label {
            font-weight: bold;
            color: #1a1a1a;
        }
        .filter-info .no-filter {
            color: #c0392b;
            font-weight: bold;
        }
        .filter-info .meta-info {
            color: #555;
            font-size: 10px;
        }
        .filter-info .meta-info strong {
            color: #1a1a1a;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            font-size: 11px;
        }
        table thead th {
            padding: 6px 3px;
            color: #ffffff;
            background: #1a1a1a;
            text-align: center;
            font-weight: bold;
            border: 1px solid #1a1a1a;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table tbody td {
            padding: 5px 3px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table td.text-left {
            text-align: left;
            padding-left: 6px;
        }
        
        table td.text-right {
            text-align: right;
            padding-right: 6px;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            font-size: 11px;
        }
        table th.text-right {
            text-align: right;
            padding-right: 6px;
        }

        /* ===== TOTALS ===== */
        .subtotal-row td {
            background: #f5f5f5 !important;
            font-weight: bold;
            padding: 6px 8px;
            font-size: 12px;
        }
        .discount-row td {
            background: #fdf0ed !important;
            color: #c0392b;
            font-weight: bold;
            padding: 6px 8px;
            font-size: 12px;
        }
        .grand-total-row td {
            background: #1a1a1a !important;
            color: #ffffff;
            font-weight: bold;
            font-size: 15px;
            padding: 7px 8px;
        }
        .grand-total-row td.text-right {
            font-size: 15px;
        }

        /* ===== AMOUNT IN WORDS ===== */
        .amount-words {
            margin: 8px 0;
            padding: 6px 12px;
            border-top: 2px solid #1a1a1a;
            border-bottom: 2px solid #1a1a1a;
            background: #f8f9fa;
            font-size: 12px;
            text-align: center;
        }
        .amount-words strong {
            color: #1a1a1a;
        }
        .amount-words .words {
            color: #1a1a1a;
            font-weight: 600;
            text-transform: capitalize;
        }

        /* ===== SUMMARY ===== */
        .summary-inline {
            margin: 8px 0;
            padding: 6px 12px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            text-align: center;
            font-size: 13px;
        }
        .summary-inline .item {
            display: inline-block;
            margin: 0 10px;
        }
        .summary-inline .label {
            color: #555;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .summary-inline .value {
            font-size: 17px;
            font-weight: bold;
            color: #1a1a1a;
            margin-left: 4px;
            font-family: 'Courier New', monospace;
        }
        .summary-inline .value.amount {
            color: #27ae60;
        }
        .summary-inline .value.discount {
            color: #c0392b;
        }
        .summary-inline .value.net {
            color: #2980b9;
        }

        /* ===== SIGNATURE ===== */
        .signature-section {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px solid #ddd;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 160px;
        }
        .signature-box .line {
            margin-top: 18px;
            border-top: 1px solid #333;
            padding-top: 3px;
            font-size: 10px;
            color: #555;
        }
        .signature-box .label {
            font-weight: bold;
            color: #1a1a1a;
            font-size: 11px;
        }

        /* ===== FOOTER ===== */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            color: #777;
            text-align: center;
            padding: 5px 0;
            background: #f8f9fa;
            border-top: 1px solid #ddd;
            font-size: 10px;
        }
        .footer span {
            margin: 0 8px;
        }

        .confirmed-badge {
            display: inline-block;
            background: #27ae60;
            color: #ffffff;
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 2px;
        }

        @media print {
            body { padding: 6px 5px 6px 2px; }
            .signature-section { page-break-inside: avoid; }
            table tbody tr { page-break-inside: avoid; }
        }
        @page {
            size: A4;
            margin: 4mm 6mm 12mm 3mm;
        }
    </style>
</head>
<body>

    <!-- ===== HEADER ===== -->
    <div class="header">
        <div class="company-name">{{ Auth::user()->au_company_name }}</div>
        <div class="company-details">
            @php
                $company_info = App\Models\Admin_user::company_info(Auth::user()->au_company_id);
            @endphp
            <span>{{ $company_info->au_address ?? 'N/A' }}</span>
            <span>|</span>
            <span>Phone: {{ $company_info->au_mobile ?? 'N/A' }}</span>
            <span>|</span>
            <span>Email: {{ $company_info->au_email ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- ===== TITLE ===== -->
    <div class="report-title">
        <h2>SELL STATEMENT</h2>
    </div>

    <!-- ===== FILTER INFO ===== -->
    <div class="filter-info">
        <div>
            <span class="label">Filter Applied:</span>
            @php
                $startDate = isset($filterData['start_date']) ? $filterData['start_date'] : null;
                $endDate = isset($filterData['end_date']) ? $filterData['end_date'] : null;
                $invoiceNo = isset($filterData['invoice_no']) ? $filterData['invoice_no'] : null;
                
                $filterParts = [];
                if(!empty($startDate)) { $filterParts[] = 'From: ' . $startDate; }
                if(!empty($endDate)) { $filterParts[] = 'To: ' . $endDate; }
                if(!empty($invoiceNo)) { $filterParts[] = 'Invoice: ' . $invoiceNo; }
            @endphp
            
            @if(!empty($filterParts))
                <span style="font-weight: 500;">{{ implode(' | ', $filterParts) }}</span>
            @else
                <span class="no-filter">No filters applied - Showing all records</span>
            @endif
        </div>
        <div class="meta-info">
            <strong>Records:</strong> {{ isset($SellStatments) ? $SellStatments->count() : 0 }} &nbsp;|&nbsp;
            <strong>Generated:</strong> {{ date('d-m-Y h:i A') }}
        </div>
    </div>

    <!-- ===== TABLE WITH TOTAL QUANTITY COLUMN ===== -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">SL</th>
                <th style="width: 18%;">Invoice No</th>
                <th style="width: 12%;">Issue Date</th>
                <th style="width: 20%;">Customer</th>
                <th style="width: 10%;">Total Qty</th>
                <th style="width: 18%;" class="text-right">Amount (BDT)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $sl = 0; 
                $totalAmount = 0; 
                $totalDiscount = 0; 
                $totalNetAmount = 0;
                $totalQuantity = 0;
            @endphp
            
            @if(isset($SellStatments) && $SellStatments->isNotEmpty())
                @foreach ($SellStatments as $sell)
                    @php
                        $amount = App\Models\Inv_product_inventory::getTotalDebit($sell->inv_pro_inv_invoice_no);
                        $discount = App\Models\Inv_product_inventory::get_discount_amount($sell->inv_pro_inv_invoice_no);
                        $netAmount = $amount - $discount;
                        
                        // Calculate total quantity for this invoice
                        $quantity = App\Models\Inv_product_inventory::where('inv_pro_inv_invoice_no', $sell->inv_pro_inv_invoice_no)
                            ->where('inv_pro_inv_com_id', $com)
                            ->where('inv_pro_inv_deal_type', 2)
                            ->whereIn('inv_pro_inv_tran_type', [1, 10, 11, 12])
                            ->where('inv_pro_inv_status', 1)
                            ->where('inv_pro_inv_confirm', 0)
                            ->sum('inv_pro_inv_qty'); // Assuming the column name is 'inv_pro_inv_qty'
                        
                        $totalAmount += $amount;
                        $totalDiscount += $discount;
                        $totalNetAmount += $netAmount;
                        $totalQuantity += $quantity;
                        
                        $customerName = $sell->getCustomerInfo['inv_cus_name'] ?? '';
                        if(empty($customerName) || trim($customerName) == '') {
                            $customerName = 'Cash Customer';
                        }
                    @endphp
                    <tr>
                        <td>{{ ++$sl }}</td>
                        <td>
                            <strong>{{ $sell->inv_pro_inv_invoice_no }}</strong>
                            @if($sell->inv_pro_inv_confirm == 1)
                                <span class="confirmed-badge">Confirmed</span>
                            @endif
                        </td>
                        <td>{{ $sell->inv_pro_inv_issue_date ?? date('Y-m-d') }}</td>
                        <td class="text-left">{{ $customerName }}</td>
                        <td>{{ number_format($quantity, 0) }}</td>
                        <td class="text-right">{{ number_format($netAmount, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #999; font-size: 14px;">
                        No records found for the selected criteria
                    </td>
                </tr>
            @endif

            <!-- ===== TOTALS ===== -->
            @if(isset($SellStatments) && $SellStatments->isNotEmpty())
                <tr class="subtotal-row">
                    <td colspan="4" class="text-right" style="padding-right: 12px;">
                        SUBTOTAL
                    </td>
                    <td class="text-right">
                        {{ number_format($totalQuantity, 0) }}
                    </td>
                    <td class="text-right">
                        {{ number_format($totalAmount, 2) }}
                    </td>
                </tr>
                
                @if($totalDiscount > 0)
                <tr class="discount-row">
                    <td colspan="5" class="text-right" style="padding-right: 12px;">
                        DISCOUNT
                    </td>
                    <td class="text-right">
                        - {{ number_format($totalDiscount, 2) }}
                    </td>
                </tr>
                @endif
                
                <!--<tr class="grand-total-row">-->
                <!--    <td colspan="4" class="text-right" style="padding-right: 12px;">-->
                <!--        NET PAYABLE-->
                <!--    </td>-->
                <!--    <td class="text-right">-->
                <!--        {{ number_format($totalQuantity, 0) }}-->
                <!--    </td>-->
                <!--    <td class="text-right">-->
                <!--        {{ number_format($totalNetAmount, 2) }}-->
                <!--    </td>-->
                <!--</tr>-->
            @endif
        </tbody>
    </table>

    <!-- ===== AMOUNT IN WORDS ===== -->
    @if(isset($totalNetAmount) && $totalNetAmount > 0)
    <div class="amount-words">
        <strong>Amount in Words:</strong>
        <span class="words">
            <?php
                function numberToWords($number) {
                    $words = [
                        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
                        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
                        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
                        14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
                        18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
                        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
                        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
                    ];
                    
                    $amountInt = floor($number);
                    if ($amountInt <= 20) {
                        $word = $words[$amountInt] ?? $amountInt;
                    } elseif ($amountInt < 100) {
                        $tens = floor($amountInt / 10) * 10;
                        $ones = $amountInt % 10;
                        $word = $words[$tens] . ($ones ? ' ' . $words[$ones] : '');
                    } elseif ($amountInt < 1000) {
                        $hundreds = floor($amountInt / 100);
                        $remainder = $amountInt % 100;
                        $word = $words[$hundreds] . ' Hundred' . ($remainder ? ' ' . numberToWords($remainder) : '');
                    } elseif ($amountInt < 100000) {
                        $thousands = floor($amountInt / 1000);
                        $remainder = $amountInt % 1000;
                        $word = numberToWords($thousands) . ' Thousand' . ($remainder ? ' ' . numberToWords($remainder) : '');
                    } elseif ($amountInt < 10000000) {
                        $lakhs = floor($amountInt / 100000);
                        $remainder = $amountInt % 100000;
                        $word = numberToWords($lakhs) . ' Lakh' . ($remainder ? ' ' . numberToWords($remainder) : '');
                    } else {
                        $word = number_format($number, 2);
                    }
                    return $word;
                }
                $netAmount = $totalNetAmount ?? 0;
                echo numberToWords($netAmount) . ' Taka Only';
            ?>
        </span>
    </div>
    @endif

    <!-- ===== SUMMARY ===== -->
    @if(isset($SellStatments) && $SellStatments->isNotEmpty())
    <div class="summary-inline">
        <span class="item">
            <span class="label">Total Invoices</span>
            <span class="value">{{ $SellStatments->count() }}</span>
        </span>
        <span class="item">
            <span class="label">Total Quantity</span>
            <span class="value amount">{{ number_format($totalQuantity, 0) }}</span>
        </span>
        <span class="item">
            <span class="label">Total Amount</span>
            <span class="value amount">{{ number_format($totalAmount, 2) }}</span>
        </span>
        <span class="item">
            <span class="label">Discount</span>
            <span class="value discount">{{ number_format($totalDiscount, 2) }}</span>
        </span>
        
    </div>
    @endif

    <!-- ===== FOOTER ===== -->
    <div class="footer">
        <span>{{ Auth::user()->au_company_name }}</span>
        <span>|</span>
        <span>Powered By: IGL Web</span>
        <span>|</span>
        <span>Generated: {{ date('d-m-Y h:i A') }}</span>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                if ($PAGE_COUNT > 1) {
                    $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
                } else {
                    $text = "Page 1";
                }
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $size = 8;
                $y = $pdf->get_height() - 10;
                $x = $pdf->get_width() - 12 - $fontMetrics->get_text_width($text, $font, $size);
                $pdf->text($x, $y, $text, $font, $size);
            ');
        }
    </script>
</body>
</html>
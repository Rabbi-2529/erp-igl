<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{env('APP_NAME')}} - Stock Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #ffffff;
            padding: 10px;
            line-height: 1.4;
        }

        .report-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }

        /* ===== HEADER ===== */
        .header-top {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-left {
            flex: 1;
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

        .report-title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: right;
        }

        /* ===== FILTER INFO ===== */
        .filter-info {
            margin-bottom: 10px;
            padding: 6px 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
        }

        .filter-info .label {
            font-weight: bold;
            color: #555;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table thead {
            background: #f0f0f0;
            color: #1a1a1a;
        }

        table thead th {
            padding: 5px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1a1a1a;
        }

        table tbody td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            vertical-align: middle;
            font-weight: bold;
            font-size: 10px;
        }

        table tfoot td {
            padding: 5px 4px;
            border: 1px solid #1a1a1a;
            vertical-align: middle;
            font-weight: bold;
            font-size: 11px;
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

        /* ===== STATUS COLORS ===== */
        .status-in-stock {
            color: #28a745;
            font-weight: bold;
        }

        .status-low-stock {
            color: #ffc107;
            font-weight: bold;
        }

        .status-out-of-stock {
            color: #dc3545;
            font-weight: bold;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #888;
        }

        .page-number {
            text-align: center;
            font-size: 9px;
            color: #888;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="report-wrapper">
        <!-- ===== HEADER ===== -->
        <div class="header-top">
            <div class="company-left">
                <div class="company-name">{{ $company_info->au_company_name ?? 'IGL WEB' }}</div>
                <div class="company-address">
                    {{ $company_info->au_address ?? 'House # 33, Road # 4, Dhanmondi' }}
                </div>
                <div class="company-contact">
                    Phone: {{ $company_info->au_phone ?? '01840893303' }} | Mobile: {{ $company_info->au_mobile ?? '01958666900' }} | Email: {{ $company_info->au_email ?? 'info@iglweb.com' }}
                </div>
            </div>
            <div class="report-title">STOCK REPORT</div>
        </div>

        <!-- ===== FILTER INFO ===== -->
        <div class="filter-info">
            <div>
                <span class="label">Filters Applied:</span>
                @if(!empty($productName))
                    <span>Product: {{ $productName }} |</span>
                @endif
                @if(!empty($category))
                    @php
                        $catName = \App\Models\Inv_product_type::where('inv_pro_type_id', $category)->value('inv_pro_type_name');
                    @endphp
                    <span>Category: {{ $catName ?? $category }} |</span>
                @endif
                @if(!empty($stockStatus))
                    <span>Status: {{ ucfirst(str_replace('_', ' ', $stockStatus)) }} |</span>
                @endif
                @if(empty($productName) && empty($category) && empty($stockStatus))
                    <span>All Products</span>
                @endif
            </div>
            <div>
                <span class="label">Generated:</span> {{ date('d-m-Y h:i A') }}
            </div>
        </div>

        <!-- ===== TABLE ===== -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 10%;">Product Code</th>
                    <th style="width: 20%;">Product Name</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 10%;">Buy Price</th>
                    <th style="width: 10%;">Sell Price</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 10%;">Stock Value</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalStockValue = 0;
                @endphp
                @foreach($stockData as $key => $item)
                @php
                    $stockValue = $item->inv_pro_det_available_qty * $item->inv_pro_det_buy_price;
                    $totalStockValue += $stockValue;
                    $statusClass = $item->inv_pro_det_available_qty <= 0 ? 'status-out-of-stock' : 
                                   ($item->inv_pro_det_available_qty <= 10 ? 'status-low-stock' : 'status-in-stock');
                    $statusText = $item->inv_pro_det_available_qty <= 0 ? 'Out of Stock' : 
                                 ($item->inv_pro_det_available_qty <= 10 ? 'Low Stock' : 'In Stock');
                @endphp
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td class="text-center">{{ $item->inv_pro_det_pro_id ?? 'N/A' }}</td>
                    <td>{{ $item->product->inv_pro_name ?? 'N/A' }}</td>
                    <td>{{ $item->type_info->inv_pro_type_name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item->inv_pro_det_buy_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->inv_pro_det_sell_price, 2) }}</td>
                    <td class="text-center">{{ number_format($item->inv_pro_det_available_qty, 0) }}</td>
                    <td class="text-right">{{ number_format($stockValue, 2) }}</td>
                    <td class="text-center">
                        <span class="{{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </td>
                </tr>
                @endforeach
                @if($stockData->isEmpty())
                <tr>
                    <td colspan="9" class="text-center" style="padding: 30px; color: #999;">
                        No stock records found.
                    </td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right">Total Stock Value:</td>
                    <td class="text-right">{{ number_format($totalStockValue, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- ===== PAGE NUMBER ===== -->
        <div class="page-number">Page 1 of 1</div>

        <!-- ===== FOOTER ===== -->
        <div class="footer">
            Powered By: {{ $company_info->au_footer_text ?? 'IGL Web Ltd.' }} | {{ $company_info->au_contact ?? '+880-1958-666999' }}
        </div>
    </div>

    <script>
        (function(){
            window.print();
        })();
    </script>
</body>
</html>
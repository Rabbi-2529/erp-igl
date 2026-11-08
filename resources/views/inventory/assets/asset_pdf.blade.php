<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{env('APP_NAME')}} - Fixed Assets Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #ffffff;
            padding: 10px;
            line-height: 1.4;
        }
        .report-wrapper { max-width: 100%; margin: 0 auto; }

        .header-top {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .company-name { font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .company-address { font-size: 10px; color: #555; margin-top: 2px; font-weight: bold; }
        .company-contact { font-size: 10px; color: #555; margin-top: 2px; font-weight: bold; }
        .report-title { font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; text-align: right; }

        .filter-info {
            margin-bottom: 10px;
            padding: 6px 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
        }
        .filter-info .label { font-weight: bold; color: #555; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table thead { background: #f0f0f0; color: #1a1a1a; }
        table thead th {
            padding: 5px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #1a1a1a;
        }
        table tbody td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            vertical-align: middle;
            font-size: 9.5px;
        }
        table tfoot td {
            padding: 5px 4px;
            border: 1px solid #1a1a1a;
            vertical-align: middle;
            font-weight: bold;
            font-size: 10px;
            background: #f8f9fa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

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
        .status-active { color: #28a745; font-weight: bold; }
        .status-inactive { color: #dc3545; font-weight: bold; }
        .status-maintenance { color: #ffc107; font-weight: bold; }
        .status-disposed { color: #6c757d; font-weight: bold; }
    </style>
</head>
<body>
    <div class="report-wrapper">
        <div class="header-top">
            <div>
                <div class="company-name">{{ $company_info->au_company_name ?? 'IGL WEB' }}</div>
                <div class="company-address">{{ $company_info->au_address ?? 'House # 33, Road # 4, Dhanmondi' }}</div>
                <div class="company-contact">
                    Phone: {{ $company_info->au_phone ?? '01840893303' }} | Mobile: {{ $company_info->au_mobile ?? '01958666900' }}
                </div>
            </div>
            <div class="report-title">FIXED ASSETS REPORT</div>
        </div>

        <div class="filter-info">
            <div>
                <span class="label">Generated:</span> {{ date('d-m-Y h:i A') }}
                <span style="margin-left: 15px;"><span class="label">Total Assets:</span> {{ $assets->count() }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 10%;">Code</th>
                    <th style="width: 20%;">Asset Name</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 12%;">Serial No</th>
                    <th style="width: 12%;">Purchase Price</th>
                    <th style="width: 12%;">Current Value</th>
                    <th style="width: 14%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $totalPurchase = 0; $totalCurrent = 0; @endphp
                @foreach($assets as $key => $asset)
                @php
                    $totalPurchase += $asset->asset_purchase_price;
                    $totalCurrent += $asset->asset_current_value;
                    $statusText = [0=>'Inactive',1=>'Active',2=>'Maintenance',3=>'Disposed'];
                    $statusClass = [0=>'status-inactive',1=>'status-active',2=>'status-maintenance',3=>'status-disposed'];
                @endphp
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td class="text-center"><strong>{{ $asset->asset_code }}</strong></td>
                    <td>{{ $asset->asset_name }}</td>
                    <td>{{ $asset->category->category_name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $asset->asset_serial_no ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($asset->asset_purchase_price, 2) }}</td>
                    <td class="text-right">{{ number_format($asset->asset_current_value, 2) }}</td>
                    <td class="text-center"><span class="{{ $statusClass[$asset->asset_status] ?? '' }}">{{ $statusText[$asset->asset_status] ?? 'Unknown' }}</span></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right"><strong>Total Value:</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalPurchase, 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalCurrent, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="page-number">Page 1 of 1</div>
        <div class="footer">Powered By: {{ $company_info->au_footer_text ?? 'IGL Web Ltd.' }}</div>
    </div>
</body>
</html>
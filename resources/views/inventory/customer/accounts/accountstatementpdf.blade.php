<!DOCTYPE html>
<html>
<head>
    <title>Account Statement</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 { 
            font-size: 22px; 
            margin: 5px 0; 
        }
        .header h3 { 
            font-size: 14px; 
            margin: 3px 0; 
            font-weight: normal; 
        }
        .header h2 {
            font-size: 18px;
            margin: 10px 0;
            color: #333;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px;
        }
        .table th { 
            background-color: #4CAF50; 
            color: white; 
            padding: 8px; 
            border: 1px solid #000; 
            text-align: center; 
            font-size: 12px;
        }
        .table td { 
            padding: 6px; 
            border: 1px solid #000; 
            text-align: center; 
            font-size: 12px;
        }
        .table .text-right { 
            text-align: right; 
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
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 11px; 
            border-top: 1px solid #ddd; 
            padding-top: 10px; 
        }
        .company-logo {
            max-height: 60px;
            max-width: 60px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

<div class="header">
    @if(isset(Auth::user()->au_company_img) && Auth::user()->au_company_img)
        <img src="{{ public_path('/asset/image/') }}{{ Auth::user()->au_company_img }}" class="company-logo">
    @endif
    <h1>{{ Auth::user()->au_company_name ?? 'Company Name' }}</h1>
    <h3>{{ Auth::user()->au_address ?? '' }}</h3>
    <h3>Mobile: {{ Auth::user()->au_mobile ?? '' }}</h3>
    <h2>Account Statement</h2>
    <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
</div>

<table class="table">
    <thead>
        <tr>
            <th style="width: 5%;">SL</th>
            <th style="width: 25%;">Company</th>
            <th style="width: 20%;">Name</th>
            <th style="width: 15%;">Debit</th>
            <th style="width: 15%;">Credit</th>
            <th style="width: 20%;">Balance</th>
        </tr>
    </thead>
    <tbody>
        @php $sl = 0; @endphp
        @forelse($inv_cus_invts as $customer)
        <tr>
            <td>{{ ++$sl }}</td>
            <td>{{ $customer->inv_cus_com_name ?? 'N/A' }}</td>
            <td>{{ $customer->inv_cus_name ?? 'N/A' }}</td>
            <td class="text-right">{{ number_format($customer->debit ?? 0, 2) }}</td>
            <td class="text-right">{{ number_format($customer->credit ?? 0, 2) }}</td>
            <td class="text-right">
                @if(($customer->balance ?? 0) >= 0)
                    <span class="balance-positive">{{ number_format($customer->balance ?? 0, 2) }}</span>
                @else
                    <span class="balance-negative">{{ number_format($customer->balance ?? 0, 2) }}</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align: center; color: red;">No customers found.</td>
        </tr>
        @endforelse
    </tbody>
    @if(count($inv_cus_invts) > 0)
    <tfoot>
        <tr class="total-row">
            <td colspan="3" style="text-align: right;">Total:</td>
            <td class="text-right">{{ number_format($total_debit ?? 0, 2) }}</td>
            <td class="text-right">{{ number_format($total_credit ?? 0, 2) }}</td>
            <td class="text-right">
                @if(($total_balance ?? 0) >= 0)
                    <span class="balance-positive">{{ number_format($total_balance ?? 0, 2) }}</span>
                @else
                    <span class="balance-negative">{{ number_format($total_balance ?? 0, 2) }}</span>
                @endif
            </td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">
    <p>Powered By IGL Web | +880-1958-666999</p>
</div>

</body>
</html>
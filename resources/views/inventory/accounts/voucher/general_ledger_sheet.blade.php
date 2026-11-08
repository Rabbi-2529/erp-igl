{{-- Supplier general ledger sheet, shared by the Print view and the PDF download --}}
@php
    // Header logo, embedded so the PDF shows it (dompdf can't load it by URL)
    $logoPath = base_path('assets/img/icons/logo.png');
    $companyLogo = is_file($logoPath) ? 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath)) : null;

    // Balance = credit - debit: below zero we owe the supplier
    $balanceTag = function ($amount) {
        return $amount < 0 ? 'Payable' : ($amount > 0 ? 'Advance' : '');
    };
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Ledger - {{ $ledger->supplier->inv_sup_person }}</title>
    <style type="text/css">
        @page { margin: 12mm 10mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 6px; margin-bottom: 8px; }
        .header img { height: 40px; width: auto; }
        .header h3 { margin: 4px 0 0 0; font-size: 14px; }
        table.info { width: 100%; margin-bottom: 8px; }
        table.info td { vertical-align: top; padding: 2px 4px; }
        table.ledger { width: 100%; border-collapse: collapse; }
        table.ledger thead { display: table-header-group; }
        table.ledger tr { page-break-inside: avoid; }
        table.ledger th, table.ledger td { border: 1px solid #555; padding: 4px 5px; }
        table.ledger th { background: #eeeeee; }
        .r { text-align: right; }
        .c { text-align: center; }
        .nowrap { white-space: nowrap; }
        .tag { font-size: 10px; color: #555; }
        .total td { font-weight: bold; background: #f4f6f8; }
        .footer { margin-top: 12px; text-align: center; font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        @if($companyLogo)
            <img src="{{ $companyLogo }}" alt="Logo">
        @else
            <h2 style="margin: 0;">{{ Auth::user()->au_company_name }}</h2>
        @endif
        <h3>{{ Auth::user()->au_address }}<br>Mobile: {{ Auth::user()->au_mobile }}</h3>
    </div>

    <table class="info">
        <tr>
            <td>
                <b>Supplier:</b> {{ $ledger->supplier->inv_sup_person }}<br>
                <b>Company:</b> {{ $ledger->supplier->inv_sup_com_name }}<br>
                <b>Period:</b> {{ $ledger->startDate }} To {{ $ledger->endDate }}
            </td>
            <td class="c"><b>General Ledger<br>(Account Wise)</b></td>
            <td class="r">
                <b>Date:</b> {{ date('l, d-m-Y') }}<br>
                <b>Time:</b> {{ date('h:i:s A') }}
            </td>
        </tr>
    </table>

    <table class="ledger">
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice / Voucher</th>
                <th>Type</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="nowrap">{{ $ledger->startDate }}</td>
                <td colspan="5" class="c">Opening Balance as on {{ $ledger->startDate }}</td>
                <td class="r nowrap">{{ number_format(abs($ledger->openingBalance), 2) }} <span class="tag">{{ $balanceTag($ledger->openingBalance) }}</span></td>
            </tr>
            @forelse($ledger->lines as $line)
            <tr>
                <td class="nowrap">{{ $line->date }}</td>
                <td>{{ $line->invoice }}</td>
                <td>{{ $line->type }}</td>
                <td>{{ $line->description }}@if($line->items > 1) ({{ $line->items }} lines)@endif</td>
                <td class="r nowrap">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                <td class="r nowrap">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                <td class="r nowrap">{{ number_format(abs($line->balance), 2) }} <span class="tag">{{ $balanceTag($line->balance) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="c">No transactions with this supplier in this period.</td>
            </tr>
            @endforelse
            <tr class="total">
                <td colspan="4" class="r">Total</td>
                <td class="r nowrap">{{ number_format($ledger->totalDebit, 2) }}</td>
                <td class="r nowrap">{{ number_format($ledger->totalCredit, 2) }}</td>
                <td class="r nowrap">{{ number_format(abs($ledger->closingBalance), 2) }} <span class="tag">{{ $balanceTag($ledger->closingBalance) }}</span></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Payable = we owe the supplier; Advance = the supplier owes us.<br>
        Powered By IGL Web Ltd | +880-1958-666999
    </div>

    @if(!empty($autoPrint))
    <script type="text/javascript">
        window.print();
    </script>
    @endif
</body>
</html>

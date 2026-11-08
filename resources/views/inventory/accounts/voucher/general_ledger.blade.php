@extends('layouts.master')
@section('title','General Ledger')
@section('inventory_class','active')
@section('accounts_class','active')
@section('voucher_class','active')
@section('ledger_class','active')
@section('content')
{{-- Kept in the content: the master layout does not output a custom_style section --}}
<style>
    .ledger-filter .form-group { margin-right: 12px; }
    .ledger-filter label { margin-right: 6px; }
    .ledger-filter select { min-width: 280px; }
    .small-box h3 { font-size: 24px; white-space: nowrap; }
    #ledger_table td, #ledger_table th { vertical-align: middle; }
    #ledger_table .amount-debit  { color: #c0392b; }
    #ledger_table .amount-credit { color: #1e8449; }
    #ledger_table tfoot th { background: #f4f6f8; }
    .balance-tag { font-size: 11px; font-weight: normal; color: #777; }
</style>
<section class="content">
    <section class="content-header">
        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul style="margin-bottom:0;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <h1>General Ledger <small>Supplier account</small></h1>
    </section>

    {{-- Filter --}}
    <div class="panel panel-bd">
        <div class="panel-body">
            <form action="{{ route('accounts.general_ledger') }}" method="post" class="form-inline ledger-filter">
                @csrf
                <div class="form-group">
                    <label for="supplier_id">Supplier</label>
                    <select class="form-control" name="supplier_id" id="supplier_id" required>
                        <option value="">Select A Supplier</option>
                        @foreach($inv_Suppliers as $supplier)
                        <option value="{{ $supplier->inv_sup_id }}" {{ (string) old('supplier_id', isset($ledger) ? $ledger->supplier->inv_sup_id : '') === (string) $supplier->inv_sup_id ? 'selected' : '' }}>
                            {{ $supplier->inv_sup_person }} ({{ $supplier->inv_sup_com_name }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_date">From</label>
                    <input type="text" name="start_date" id="start_date" class="form-control"
                           value="{{ old('start_date', $startDate) }}" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="end_date">To</label>
                    <input type="text" name="end_date" id="end_date" class="form-control"
                           value="{{ old('end_date', $endDate) }}" autocomplete="off" required>
                </div>
                <button type="submit" class="btn btn-info" name="searchbtn">
                    <i class="fa fa-search"></i> Search
                </button>

                @isset($ledger)
                <div class="pull-right">
                    <button class="btn btn-warning" type="button" id="print_btn"><i class="fa fa-print"></i> Print</button>
                    <button class="btn btn-primary" type="button" id="download_btn"><i class="fa fa-download"></i> Download</button>
                </div>
                @endisset
            </form>
        </div>
    </div>

    @isset($ledger)
    @php
        // Balance = credit - debit: below zero we owe the supplier
        $balanceTag = function ($amount) {
            return $amount < 0 ? 'Payable' : ($amount > 0 ? 'Advance' : '');
        };
        $periodLabel = \Carbon\Carbon::parse($ledger->startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($ledger->endDate)->format('d M Y');
    @endphp

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-opening">
                <div class="inner">
                    <h3>৳ {{ number_format(abs($ledger->openingBalance), 2) }}</h3>
                    <p>Opening {{ $balanceTag($ledger->openingBalance) }} ({{ \Carbon\Carbon::parse($ledger->startDate)->format('d M Y') }})</p>
                </div>
                <div class="icon"><i class="ion ion-folder"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>৳ {{ number_format($ledger->totalDebit, 2) }}</h3>
                    <p>Total Debit (Purchases)</p>
                </div>
                <div class="icon"><i class="ion ion-bag"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>৳ {{ number_format($ledger->totalCredit, 2) }}</h3>
                    <p>Total Credit (Payments)</p>
                </div>
                <div class="icon"><i class="ion ion-cash"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>৳ {{ number_format(abs($ledger->closingBalance), 2) }}</h3>
                    <p>Closing {{ $balanceTag($ledger->closingBalance) }} ({{ \Carbon\Carbon::parse($ledger->endDate)->format('d M Y') }})</p>
                </div>
                <div class="icon"><i class="ion ion-stats-bars"></i></div>
            </div>
        </div>
    </div>

    {{-- Ledger --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            {{ $ledger->supplier->inv_sup_person }}
                            <small>{{ $ledger->supplier->inv_sup_com_name }} &middot; {{ $periodLabel }} &middot; {{ $ledger->lines->count() }} entries</small>
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    <table id="ledger_table" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Date</th>
                                <th>Invoice / Voucher</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ledger->lines as $line)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($line->date)->format('d M Y') }}</td>
                                <td>{{ $line->invoice }}</td>
                                <td><span class="label {{ $line->credit > 0 ? 'label-success' : 'label-danger' }}">{{ $line->type }}</span></td>
                                <td>
                                    {{ $line->description }}
                                    @if($line->items > 1)
                                    <br><small class="text-muted">{{ $line->items }} lines</small>
                                    @endif
                                </td>
                                <td class="text-right amount-debit">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                                <td class="text-right amount-credit">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                                <td class="text-right">
                                    <b>{{ number_format(abs($line->balance), 2) }}</b>
                                    <span class="balance-tag">{{ $balanceTag($line->balance) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-right">Total</th>
                                <th class="text-right amount-debit">{{ number_format($ledger->totalDebit, 2) }}</th>
                                <th class="text-right amount-credit">{{ number_format($ledger->totalCredit, 2) }}</th>
                                <th class="text-right">
                                    {{ number_format(abs($ledger->closingBalance), 2) }}
                                    <span class="balance-tag">{{ $balanceTag($ledger->closingBalance) }}</span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                    <small class="text-muted">
                        Balance starts from the opening {{ strtolower($balanceTag($ledger->openingBalance)) ?: 'balance' }}
                        of ৳ {{ number_format(abs($ledger->openingBalance), 2) }}. Payable = we owe the supplier; Advance = the supplier owes us.
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endisset
</section>
@endsection

@section('custom_script')
<script type="text/javascript">
$(document).ready(function () {
    $("#start_date, #end_date").datepicker({
        dateFormat: 'yy-mm-dd', // the server reads Y-m-d
        changeMonth: true,
        changeYear: true
    });

    @isset($ledger)
    var exportTitle = 'General Ledger - {{ $ledger->supplier->inv_sup_person }} - {{ $periodLabel }}';

    $('#ledger_table').DataTable({
        responsive: true,
        ordering: false, // keep date order so the running balance reads top to bottom
        pageLength: 50,
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
        dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tip",
        buttons: [
            {extend: 'copy', className: 'btn-sm prints', title: exportTitle, footer: true},
            {extend: 'csv', className: 'btn-sm prints', title: exportTitle, footer: true},
            {extend: 'excel', className: 'btn-sm prints', title: exportTitle, footer: true},
            {extend: 'pdf', className: 'btn-sm prints', title: exportTitle, footer: true, orientation: 'landscape'},
            {extend: 'print', className: 'btn-sm prints', title: exportTitle, footer: true}
        ],
        language: {
            emptyTable: 'No transactions with this supplier in this period.'
        }
    });

    var ledgerQuery = "sdate={{ $ledger->startDate }}&edate={{ $ledger->endDate }}&supid={{ $ledger->supplier->inv_sup_id }}";

    $("#download_btn").click(function () {
        window.open("{{ route('accounts.general_ledger_download') }}?" + ledgerQuery + "&download_btn", '_blank');
    });

    $("#print_btn").click(function () {
        window.open("{{ route('accounts.general_ledger_download') }}?" + ledgerQuery + "&print_btn", '_blank');
    });
    @endisset
});
</script>
@endsection

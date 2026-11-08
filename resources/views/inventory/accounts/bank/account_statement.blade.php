@extends('layouts.master')
@section('title','Account Statements')
@section('inventory_class','active')
@section('accounts_class','active')
@section('acc_state_class','active')
@section('content')
@php
    $totalDebit     = $statements->sum('inv_abs_debit');
    $totalCredit    = $statements->sum('inv_abs_credit');
    $closingBalance = $openingBalance + $totalCredit - $totalDebit;
    $balance        = $openingBalance;
    $periodLabel    = \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y');
@endphp
{{-- Kept in the content: the master layout does not output a custom_style section --}}
<style>
    .statement-filter .form-group { margin-right: 12px; }
    .statement-filter label { margin-right: 6px; }
    .small-box h3 { font-size: 24px; white-space: nowrap; }
    #account_statement_table td,
    #account_statement_table th { vertical-align: middle; }
    #account_statement_table .amount-out { color: #c0392b; }
    #account_statement_table .amount-in  { color: #1e8449; }
    #account_statement_table tfoot th { background: #f4f6f8; }
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

        <h1>Account Statement</h1>
    </section>

    {{-- Period Filter --}}
    <div class="panel panel-bd">
        <div class="panel-body">
            <form action="{{ route('accounts.account-statement') }}" method="post" class="form-inline statement-filter">
                @csrf
                <div class="form-group">
                    <label for="start_date">From</label>
                    <input type="text" name="start_date" id="start_date" class="form-control"
                           value="{{ $startDate }}" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="end_date">To</label>
                    <input type="text" name="end_date" id="end_date" class="form-control"
                           value="{{ $endDate }}" autocomplete="off" required>
                </div>
                <button type="submit" class="btn btn-info" name="searchbtn">
                    <i class="fa fa-search"></i> Search
                </button>

                <div class="pull-right">
                    <button class="btn btn-warning" type="button" id="print_btn"><i class="fa fa-print"></i> Print</button>
                    <button class="btn btn-primary" type="button" id="download_btn"><i class="fa fa-download"></i> Download</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-opening">
                <div class="inner">
                    <h3>৳ {{ number_format($openingBalance, 2) }}</h3>
                    <p>Opening Balance ({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }})</p>
                </div>
                <div class="icon"><i class="ion ion-folder"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>৳ {{ number_format($totalCredit, 2) }}</h3>
                    <p>Total Credit (Money In)</p>
                </div>
                <div class="icon"><i class="ion ion-arrow-down-a"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>৳ {{ number_format($totalDebit, 2) }}</h3>
                    <p>Total Debit (Money Out)</p>
                </div>
                <div class="icon"><i class="ion ion-arrow-up-a"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>৳ {{ number_format($closingBalance, 2) }}</h3>
                    <p>Closing Balance ({{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})</p>
                </div>
                <div class="icon"><i class="ion ion-cash"></i></div>
            </div>
        </div>
    </div>

    {{-- Statement --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            Statement <small>{{ $periodLabel }} &middot; {{ $statements->count() }} transactions</small>
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    <table id="account_statement_table" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Date</th>
                                <th>Voucher</th>
                                <th>Bank / Account</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-right">Debit (Out)</th>
                                <th class="text-right">Credit (In)</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statements as $statement)
                            @php
                                $balance += $statement->inv_abs_credit - $statement->inv_abs_debit;
                                $bank     = $banks[$statement->inv_abs_bank_id] ?? null;
                                $moneyIn  = $statement->inv_abs_credit > 0;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($statement->inv_abs_transaction_date)->format('d M Y') }}</td>
                                <td><small>{{ $statement->inv_abs_voucher_no }}</small></td>
                                <td>
                                    {{ $bank->bank_info->bank_name ?? ($bank->inv_abi_account_name ?? 'N/A') }}
                                    @if($bank)
                                    <br><small class="text-muted">{{ $bank->inv_abi_account_no }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="label {{ $moneyIn ? 'label-success' : 'label-danger' }}">
                                        {{ App\Models\Inv_acc_bank_statement::getReferenceTypeLabel($statement) }}
                                    </span>
                                </td>
                                <td>{{ $statement->inv_abs_description }}</td>
                                <td class="text-right amount-out">{{ $statement->inv_abs_debit > 0 ? number_format($statement->inv_abs_debit, 2) : '' }}</td>
                                <td class="text-right amount-in">{{ $statement->inv_abs_credit > 0 ? number_format($statement->inv_abs_credit, 2) : '' }}</td>
                                <td class="text-right"><b class="{{ $balance < 0 ? 'text-danger' : '' }}">{{ number_format($balance, 2) }}</b></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-right">Total</th>
                                <th class="text-right amount-out">{{ number_format($totalDebit, 2) }}</th>
                                <th class="text-right amount-in">{{ number_format($totalCredit, 2) }}</th>
                                <th class="text-right">{{ number_format($closingBalance, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                    <small class="text-muted">
                        Balance starts from the opening balance of ৳ {{ number_format($openingBalance, 2) }}, all banks and cash together.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Used by the Print / Download buttons --}}
    <input type="hidden" id="sdate" value="{{ $startDate }}">
    <input type="hidden" id="edate" value="{{ $endDate }}">
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

    var exportTitle = 'Account Statement {{ $periodLabel }}';

    $('#account_statement_table').DataTable({
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
            emptyTable: 'No transactions in this period.'
        }
    });
});

$("#download_btn").click(function () {
    let route = "{{ route('accounts.account-statement-download')}}?sdate=" + $("#sdate").val() + "&edate=" + $("#edate").val() + "&download_btn";
    window.open(route, '_blank');
});

$("#print_btn").click(function () {
    let route = "{{ route('accounts.account-statement-download')}}?sdate=" + $("#sdate").val() + "&edate=" + $("#edate").val() + "&print_btn";
    window.open(route, '_blank');
});
</script>
@endsection

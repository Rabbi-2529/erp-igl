@extends('layouts.master')
@section('title','Bank Loan Report')
@section('inventory_class','active')
@section('accounts_class','active')
@section('bank_class','active')
@section('bank_loan_class','active')
@section('bank_loan_report','active')
@section('content')
<section class="content">
    <section class="content-header">
        <h1>Bank Loan Report</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            Loan Report
                            <div class="pull-right">
                                <button onclick="window.print()" class="btn btn-primary btn-sm">
                                    <i class="fa fa-print"></i> Print
                                </button>
                                <a href="{{ route('accounts.bank-loan.list') }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    {{-- Summary Cards --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3>৳ {{ number_format($totalPrincipal, 2) }}</h3>
                                    <p>Total Principal</p>
                                </div>
                                <div class="icon"><i class="ion ion-cash"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3>৳ {{ number_format($totalOutstanding, 2) }}</h3>
                                    <p>Total Outstanding</p>
                                </div>
                                <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>৳ {{ number_format($totalPaid, 2) }}</h3>
                                    <p>Total Paid</p>
                                </div>
                                <div class="icon"><i class="ion ion-checkmark"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3>৳ {{ number_format($totalInterest, 2) }}</h3>
                                    <p>Total Interest Paid</p>
                                </div>
                                <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped table-hover datatable">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Loan Name</th>
                                    <th>Bank</th>
                                    <th>Type</th>
                                    <th>Frequency</th>
                                    <th style="text-align:right;">Principal</th>
                                    <th>Rate (%)</th>
                                    <th style="text-align:right;">EMI</th>
                                    <th style="text-align:right;">Outstanding</th>
                                    <th style="text-align:right;">Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php($sl = 0)
                                @foreach($loans as $loan)
                                <tr>
                                    <td>{{ ++$sl }}</td>
                                    <td>{{ $loan->inv_abl_loan_name }}</td>
                                    <td>{{ $loan->bank_info->bank_info->bank_name ?? 'N/A' }}</td>
                                    <td><span class="label label-info">{{ $loan->loan_type_label }}</span></td>
                                    <td><span class="label label-default">{{ $loan->repayment_frequency_label }}</span></td>
                                    <td style="text-align:right;">{{ number_format($loan->inv_abl_principal_amount, 2) }}</td>
                                    <td>{{ $loan->inv_abl_interest_rate }}%</td>
                                    <td style="text-align:right;">{{ number_format($loan->inv_abl_emi_amount, 2) }}</td>
                                    <td style="text-align:right;" class="text-danger">
                                        {{ number_format($loan->inv_abl_outstanding_balance, 2) }}
                                    </td>
                                    <td style="text-align:right;" class="text-success">
                                        {{ number_format($loan->inv_abl_total_paid, 2) }}
                                    </td>
                                    <td>
                                        @if($loan->inv_abl_status == 1)
                                            <span class="label label-success">Active</span>
                                        @elseif($loan->inv_abl_status == 2)
                                            <span class="label label-default">Closed</span>
                                        @else
                                            <span class="label label-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background:#f4f4f4; font-weight:bold;">
                                    <td colspan="5" class="text-right">Total:</td>
                                    <td style="text-align:right;">{{ number_format($totalPrincipal, 2) }}</td>
                                    <td>—</td>
                                    <td>—</td>
                                    <td style="text-align:right;" class="text-danger">
                                        {{ number_format($totalOutstanding, 2) }}
                                    </td>
                                    <td style="text-align:right;" class="text-success">
                                        {{ number_format($totalPaid, 2) }}
                                    </td>
                                    <td>—</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
<style>
    .action_btn { border: 1px solid; padding: 5px; }
    .btn_show { background-color: green; color: white; }
    .btn_delete { background-color: red; color: white; }
    .btn_edit { background-color: cornflowerblue; color: white; }

    @media print {
        .btn, .pull-right, .content-header h1 { display: none !important; }
        .panel { border: none !important; box-shadow: none !important; }
    }
</style>
@endsection
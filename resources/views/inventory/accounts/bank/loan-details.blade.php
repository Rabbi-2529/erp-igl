@extends('layouts.master')
@section('title','Loan Details')
@section('inventory_class','active')
@section('accounts_class','active')
@section('bank_class','active')
@section('bank_loan_class','active')
@section('bank_loan_list','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(Session::has('msg'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('msg') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <h1>Loan Details</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            {{ $loan->inv_abl_loan_name }}
                            <div class="pull-right">
                                @if($loan->inv_abl_status == 1)
                                <a href="{{ route('accounts.bank-loan.payment-form', $loan->inv_abl_id) }}" 
                                   class="btn btn-success btn-sm">
                                    <i class="fa fa-money"></i> Pay EMI
                                </a>
                                <a href="{{ route('accounts.bank-loan.transfer-form', $loan->inv_abl_id) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fa fa-exchange"></i> Transfer
                                </a>
                                @endif
                                <a href="{{ route('accounts.bank-loan.edit', $loan->inv_abl_id) }}" 
                                   class="btn btn-info btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('accounts.bank-loan.list') }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    {{-- Balance Summary --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3>৳ {{ number_format($loan->inv_abl_outstanding_balance, 2) }}</h3>
                                    <p>Outstanding Balance</p>
                                </div>
                                <div class="icon"><i class="ion ion-cash"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>৳ {{ number_format($loan->inv_abl_total_paid, 2) }}</h3>
                                    <p>Total Paid</p>
                                </div>
                                <div class="icon"><i class="ion ion-checkmark"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-purple">
                                <div class="inner">
                                    <h3>৳ {{ number_format($loan->inv_abl_total_interest_paid, 2) }}</h3>
                                    <p>Interest Paid</p>
                                </div>
                                <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3>{{ $loan->remaining_installments }}</h3>
                                    <p>Remaining Installments</p>
                                </div>
                                <div class="icon"><i class="ion ion-calendar"></i></div>
                            </div>
                        </div>
                    </div>

                    {{-- Repayment Progress --}}
                    <div class="row">
                        <div class="col-md-12">
                            <label><strong>Repayment Progress:</strong> {{ $loan->progress_percentage }}%</label>
                            <div class="progress" style="height: 22px; background-color:#f0f0f0; border-radius:4px; border:1px solid #e0e0e0;">
                                @php($pct = $loan->progress_percentage)
                                @if($pct >= 70)
                                    @php($barClass = 'bar-green')
                                @elseif($pct >= 30)
                                    @php($barClass = 'bar-orange')
                                @else
                                    @php($barClass = 'bar-red')
                                @endif
                                <div class="progress-bar {{ $barClass }} progress-bar-striped active" 
                                     style="width: {{ $pct }}%; color:#fff; font-weight:600; line-height:22px; text-shadow:0 1px 1px rgba(0,0,0,0.25);">
                                    {{ $pct }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Loan Information --}}
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th width="45%">Loan Name</th>
                                    <td>{{ $loan->inv_abl_loan_name }}</td>
                                </tr>
                                <tr>
                                    <th>Loan Account No</th>
                                    <td>{{ $loan->inv_abl_loan_account_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Bank</th>
                                    <td>
                                        @if($loan->bank_info && $loan->bank_info->bank_info)
                                            {{ $loan->bank_info->bank_info->bank_name }}
                                            ({{ $loan->bank_info->inv_abi_account_name }})
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Loan Type</th>
                                    <td><span class="label label-info">{{ $loan->loan_type_label }}</span></td>
                                </tr>
                                <tr>
                                    <th>Interest Type</th>
                                    <td>{{ $loan->inv_abl_interest_type == 1 ? 'Simple' : 'Compound' }}</td>
                                </tr>
                                <tr>
                                    <th>Total Installments</th>
                                    <td>{{ $loan->total_installments }} installments</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
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
                            </table>
                        </div>

                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th width="45%">Principal Amount</th>
                                    <td style="text-align: right;">{{ number_format($loan->inv_abl_principal_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Interest Rate (Annual)</th>
                                    <td>{{ $loan->inv_abl_interest_rate }}%</td>
                                </tr>
                                <tr>
                                    <th>Tenure</th>
                                    <td>{{ $loan->inv_abl_tenure_months }} Months</td>
                                </tr>
                                <tr>
                                    <th>EMI Amount</th>
                                    <td style="text-align: right;">
                                        <b>{{ number_format($loan->inv_abl_emi_amount, 2) }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Payable</th>
                                    <td style="text-align: right;">{{ number_format($loan->total_loan_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Interest Payable</th>
                                    <td style="text-align: right;">{{ number_format($loan->total_interest_payable, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Remarks</th>
                                    <td>{{ $loan->inv_abl_remarks ?? '—' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Disbursement Date</span>
                                    <span class="info-box-number">
                                        {{ $loan->inv_abl_disbursement_date ? $loan->inv_abl_disbursement_date->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-yellow">
                                <span class="info-box-icon"><i class="fa fa-calendar-check-o"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">First Payment Date</span>
                                    <span class="info-box-number">
                                        {{ $loan->inv_abl_first_emi_date ? $loan->inv_abl_first_emi_date->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-red">
                                <span class="info-box-icon"><i class="fa fa-calendar-times-o"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Maturity Date</span>
                                    <span class="info-box-number">
                                        {{ $loan->inv_abl_maturity_date ? $loan->inv_abl_maturity_date->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Tabs --}}
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a data-toggle="tab" href="#scheduleTab">
                                <i class="fa fa-calendar"></i> Amortization Schedule
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#paymentsTab">
                                <i class="fa fa-money"></i> Payment History ({{ $payments->count() }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" style="margin-top:15px;">
                        {{-- Schedule --}}
                        <div id="scheduleTab" class="tab-pane fade in active">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Payment Date</th>
                                            <th style="text-align:right;">EMI Amount</th>
                                            <th style="text-align:right;">Principal</th>
                                            <th style="text-align:right;">Interest</th>
                                            <th style="text-align:right;">Remaining Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($schedule as $row)
                                        <tr>
                                            <td>{{ $row['installment_no'] }}</td>
                                            <td>{{ \Carbon\Carbon::parse($row['payment_date'])->format('d/m/Y') }}</td>
                                            <td style="text-align:right;">{{ number_format($row['emi_amount'], 2) }}</td>
                                            <td style="text-align:right;" class="text-success">
                                                {{ number_format($row['principal_component'], 2) }}
                                            </td>
                                            <td style="text-align:right;" class="text-danger">
                                                {{ number_format($row['interest_component'], 2) }}
                                            </td>
                                            <td style="text-align:right;">
                                                {{ number_format($row['remaining_balance'], 2) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No schedule available</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if(count($schedule) > 0)
                                    <tfoot>
                                        <tr style="background:#f4f4f4; font-weight:bold;">
                                            <td colspan="2" class="text-right">Total:</td>
                                            <td style="text-align:right;">{{ number_format(collect($schedule)->sum('emi_amount'), 2) }}</td>
                                            <td style="text-align:right;" class="text-success">
                                                {{ number_format(collect($schedule)->sum('principal_component'), 2) }}
                                            </td>
                                            <td style="text-align:right;" class="text-danger">
                                                {{ number_format(collect($schedule)->sum('interest_component'), 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                        {{-- Payments --}}
                        <div id="paymentsTab" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Payment Date</th>
                                            <th>Type</th>
                                            <th style="text-align:right;">Principal</th>
                                            <th style="text-align:right;">Interest</th>
                                            <th style="text-align:right;">Total</th>
                                            <th>Voucher No</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $key => $payment)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $payment->inv_ablp_payment_date ? $payment->inv_ablp_payment_date->format('d/m/Y') : 'N/A' }}</td>
                                            <td><span class="label label-info">{{ $payment->payment_type_label }}</span></td>
                                            <td style="text-align:right;" class="text-success">
                                                {{ number_format($payment->inv_ablp_principal_amount, 2) }}
                                            </td>
                                            <td style="text-align:right;" class="text-danger">
                                                {{ number_format($payment->inv_ablp_interest_amount, 2) }}
                                            </td>
                                            <td style="text-align:right;">
                                                <b>{{ number_format($payment->inv_ablp_total_amount, 2) }}</b>
                                            </td>
                                            <td>{{ $payment->inv_ablp_voucher_no }}</td>
                                            <td>{{ $payment->inv_ablp_description }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                No payments recorded yet.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

    /* Custom purple for Interest Paid box */
    .bg-purple {
        background-color: #8e44ad !important;
        color: #ffffff !important;
    }
    .bg-purple .inner h3,
    .bg-purple .inner p {
        color: #ffffff !important;
    }
    .bg-purple .icon {
        color: rgba(255, 255, 255, 0.35) !important;
    }

    /* Progress bar colors */
    .bar-red    { background-color: #e74c3c !important; }
    .bar-orange { background-color: #f39c12 !important; }
    .bar-green  { background-color: #27ae60 !important; }

    /* Ensure white text on small-box labels */
    .small-box .inner h3,
    .small-box .inner p {
        color: #ffffff !important;
    }
</style>
@endsection
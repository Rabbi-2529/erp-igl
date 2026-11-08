@extends('layouts.master')
@section('title','Bank Loan List')
@section('inventory_class','active')
@section('accounts_class','active')
@section('bank_class','active')
@section('bank_loan_class','active')
@section('bank_loan_list','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(Session::has('errmsg'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('errmsg') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        @if(Session::has('msg'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('msg') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <h1>Bank Loan</h1>
    </section>

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>৳ {{ number_format($totalOutstanding, 2) }}</h3>
                    <p>Total Outstanding Balance</p>
                </div>
                <div class="icon">
                    <i class="ion ion-cash"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>৳ {{ number_format($totalMonthlyEMI, 2) }}</h3>
                    <p>Total EMI Commitment</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $activeLoansCount }}</h3>
                    <p>Active Loans</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            Loan List
                            <a href="{{ route('accounts.bank-loan.create') }}" class="btn btn-success btn-sm pull-right">
                                <i class="fa fa-plus"></i> Add New Loan
                            </a>
                            <a href="{{ route('accounts.bank-loan.report') }}" class="btn btn-info btn-sm pull-right" style="margin-right:5px;">
                                <i class="fa fa-file-text"></i> Loan Report
                            </a>
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Loan Name</th>
                                <th>Bank</th>
                                <th>Type</th>
                                <th>Principal</th>
                                <th>EMI</th>
                                <th>Outstanding</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th class="text-center" style="min-width:180px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($sl = 0)
                            @foreach ($loans as $loan)
                            <tr>
                                <td>{{ ++$sl }}</td>
                                <td>
                                    <strong>{{ $loan->inv_abl_loan_name }}</strong>
                                    @if($loan->inv_abl_loan_account_no)
                                    <br><small class="text-muted">A/C: {{ $loan->inv_abl_loan_account_no }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($loan->bank_info && $loan->bank_info->bank_info)
                                        {{ $loan->bank_info->bank_info->bank_name }}
                                        <br><small class="text-muted">{{ $loan->bank_info->inv_abi_account_name }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><span class="label label-info">{{ $loan->loan_type_label }}</span></td>
                                <td style="text-align: right;">{{ number_format($loan->inv_abl_principal_amount, 2) }}</td>
                                <td style="text-align: right;">{{ number_format($loan->inv_abl_emi_amount, 2) }}</td>
                                <td style="text-align: right;">
                                    <span class="text-danger">
                                        <b>{{ number_format($loan->inv_abl_outstanding_balance, 2) }}</b>
                                    </span>
                                </td>
                                <td>
                                    @php($pct = $loan->progress_percentage)
                                    @if($pct >= 70)
                                        @php($barClass = 'bar-green')
                                    @elseif($pct >= 30)
                                        @php($barClass = 'bar-orange')
                                    @else
                                        @php($barClass = 'bar-red')
                                    @endif
                                    <div class="progress" style="height: 18px; margin-bottom: 3px; background-color:#f0f0f0; border:1px solid #e0e0e0; border-radius:4px;">
                                        <div class="progress-bar {{ $barClass }}" 
                                             style="width: {{ $pct }}%; color:#fff; font-weight:600; line-height:18px; text-shadow:0 1px 1px rgba(0,0,0,0.25);">
                                            {{ $pct }}%
                                        </div>
                                    </div>
                                    <small>{{ $loan->remaining_installments }} left</small>
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
                                <td align="center">
                                    <div class="action-cell">
                                        <a href="{{ route('accounts.bank-loan.details', $loan->inv_abl_id) }}" 
                                           class="action_btn btn-view" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('accounts.bank-loan.edit', $loan->inv_abl_id) }}" 
                                           class="action_btn btn_edit" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if($loan->inv_abl_status == 1)
                                        <a href="{{ route('accounts.bank-loan.payment-form', $loan->inv_abl_id) }}" 
                                           class="action_btn btn_pay" title="Pay EMI">
                                            <i class="fa fa-credit-card"></i> Pay EMI
                                        </a>
                                        <a href="{{ route('accounts.bank-loan.transfer-form', $loan->inv_abl_id) }}"
                                           class="action_btn btn_transfer" title="Transfer Loan Amount">
                                            <i class="fa fa-exchange"></i>
                                        </a>
                                        @endif
                                        <form action="{{ route('accounts.bank-loan.delete', $loan->inv_abl_id) }}" 
                                              method="POST" style="display:inline-block;"
                                              onsubmit="return confirm('Are you sure you want to delete this loan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action_btn btn_delete" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
<style>
    /* Action buttons */
    .action-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex-wrap: wrap;
    }
    .action_btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        padding: 6px 8px;
        border-radius: 4px;
        color: #ffffff !important;
        text-decoration: none !important;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        min-width: 30px;
        min-height: 30px;
    }
    .action_btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        opacity: 0.9;
    }
    .action_btn:focus {
        outline: 2px solid #34495e;
        outline-offset: 2px;
    }
    .btn-view   { background-color: #3498db; }
    .btn_edit   { background-color: cornflowerblue; }
    .btn_delete { background-color: #e74c3c; }
    .btn_transfer { background-color: #8e44ad; }

    /* Big Pay EMI button */
    .btn_pay {
        background-color: #27ae60 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        padding: 7px 16px !important;
        border-radius: 20px !important;
        font-size: 12px !important;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        gap: 6px;
        min-width: 100px;
        min-height: 32px;
        box-shadow: 0 2px 6px rgba(39, 174, 96, 0.4);
        border: 2px solid #1e8449 !important;
        animation: payPulse 2.4s infinite;
    }
    .btn_pay i { font-size: 13px; }
    .btn_pay:hover {
        background-color: #1e8449 !important;
        color: #ffffff !important;
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 5px 14px rgba(39, 174, 96, 0.5);
    }
    .btn_pay:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.35);
    }
    @keyframes payPulse {
        0%   { box-shadow: 0 2px 6px rgba(39,174,96,0.4), 0 0 0 0  rgba(39,174,96,0.55); }
        70%  { box-shadow: 0 2px 6px rgba(39,174,96,0.4), 0 0 0 10px rgba(39,174,96,0); }
        100% { box-shadow: 0 2px 6px rgba(39,174,96,0.4), 0 0 0 0  rgba(39,174,96,0); }
    }

    /* Progress bar colors */
    .bar-red    { background-color: #e74c3c !important; }
    .bar-orange { background-color: #f39c12 !important; }
    .bar-green  { background-color: #27ae60 !important; }

    @media (max-width: 767px) {
        .btn_pay { min-width: 44px; padding: 6px 10px !important; }
        .btn_pay span { display: none; }
    }
</style>
@endsection
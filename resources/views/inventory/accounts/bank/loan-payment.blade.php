@extends('layouts.master')
@section('title','Loan Payment')
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
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul style="margin-bottom:0;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <h1>Loan Payment</h1>
    </section>

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            Payment - {{ $loan->inv_abl_loan_name }}
                            <a href="{{ route('accounts.bank-loan.details', $loan->inv_abl_id) }}" 
                               class="btn btn-default btn-sm pull-right">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    {{-- Loan Summary --}}
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th width="50%">Bank</th>
                                    <td>{{ $loan->bank_info->bank_info->bank_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Principal Amount</th>
                                    <td style="text-align:right;">{{ number_format($loan->inv_abl_principal_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>EMI Amount</th>
                                    <td style="text-align:right;">
                                        <b>{{ number_format($loan->inv_abl_emi_amount, 2) }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Interest Rate</th>
                                    <td>{{ $loan->inv_abl_interest_rate }}% p.a.</td>
                                </tr>
                                <tr>
                                    <th>Repayment Frequency</th>
                                    <td>{{ $loan->repayment_frequency_label }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th width="50%">Outstanding Balance</th>
                                    <td style="text-align:right;" class="text-danger">
                                        <b>{{ number_format($loan->inv_abl_outstanding_balance, 2) }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Paid So Far</th>
                                    <td style="text-align:right;" class="text-success">
                                        {{ number_format($loan->inv_abl_total_paid, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Remaining Installments</th>
                                    <td>{{ $loan->remaining_installments }}</td>
                                </tr>
                                <tr>
                                    <th>Maturity Date</th>
                                    <td>{{ $loan->inv_abl_maturity_date ? $loan->inv_abl_maturity_date->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Suggested Breakdown --}}
                    <div class="alert alert-info">
                        <h5><i class="fa fa-info-circle"></i> Suggested Payment Breakdown (Next EMI)</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Principal Component:</strong><br>
                                ৳ {{ number_format($principalComponent, 2) }}
                            </div>
                            <div class="col-md-4">
                                <strong>Interest Component:</strong><br>
                                ৳ {{ number_format($interestComponent, 2) }}
                            </div>
                            <div class="col-md-4">
                                <strong>Suggested Total:</strong><br>
                                ৳ {{ number_format($principalComponent + $interestComponent, 2) }}
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('accounts.bank-loan.payment-store', $loan->inv_abl_id) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                                    <input type="text" name="payment_date" id="payment_date" 
                                           class="form-control datepicker" 
                                           value="{{ date('d/m/Y') }}" required autocomplete="off">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_amount">Payment Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="total_amount" id="total_amount" 
                                           class="form-control" 
                                           value="{{ round($principalComponent + $interestComponent, 2) }}" 
                                           required min="0.01">
                                    <small class="text-muted">
                                        Max: ৳ {{ number_format($loan->inv_abl_outstanding_balance + $interestComponent, 2) }}
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_type">Payment Type</label>
                                    <select name="payment_type" id="payment_type" class="form-control">
                                        <option value="1">EMI Payment</option>
                                        <option value="2">Principal Only</option>
                                        <option value="3">Interest Only</option>
                                        <option value="4">Full Settlement</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <input type="text" name="description" id="description" class="form-control" 
                                           placeholder="Optional note">
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-success" 
                                    onclick="return confirm('Are you sure you want to record this payment?');">
                                <i class="fa fa-check"></i> Record Payment
                            </button>
                            <a href="{{ route('accounts.bank-loan.details', $loan->inv_abl_id) }}" 
                               class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
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
</style>
@endsection

@section('custom_script')
<script>
    $(document).ready(function() {
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });
        }

        $('#payment_type').on('change', function() {
            if ($(this).val() == '4') {
                var outstanding = {{ $loan->inv_abl_outstanding_balance }};
                var interest = {{ $interestComponent }};
                $('#total_amount').val((outstanding + interest).toFixed(2));
            }
        });
    });
</script>
@endsection
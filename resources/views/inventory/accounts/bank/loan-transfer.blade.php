@extends('layouts.master')
@section('title','Loan Transfer')
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

        <h1>Loan Transfer</h1>
    </section>

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            Transfer - {{ $loan->inv_abl_loan_name }}
                            <a href="{{ route('accounts.bank-loan.list') }}"
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
                                    <th width="50%">Loan Received In</th>
                                    <td>{{ $accountLabels[$loan->inv_abl_bank_id] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Loan Amount</th>
                                    <td style="text-align:right;">{{ number_format($loan->inv_abl_principal_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Already Transferred</th>
                                    <td style="text-align:right;">{{ number_format($transferred, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th width="50%">Available To Transfer</th>
                                    <td style="text-align:right;" class="text-success">
                                        <b>{{ number_format($available, 2) }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Loan Bank Balance</th>
                                    <td style="text-align:right;" class="{{ $bankBalance < 0 ? 'text-danger' : '' }}">
                                        {{ number_format($bankBalance, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($available <= 0)
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> The full loan amount has already been transferred.
                        </div>
                    @elseif($accounts->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i> There is no other bank or cash account to transfer to.
                            <a href="{{ route('accounts.add-bank') }}">Add a bank</a> first.
                        </div>
                    @else
                    <form action="{{ route('accounts.bank-loan.transfer-store', $loan->inv_abl_id) }}" method="POST" id="transfer_form">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="transfer_date">Transfer Date <span class="text-danger">*</span></label>
                                    <input type="text" name="transfer_date" id="transfer_date"
                                           class="form-control datepicker"
                                           value="{{ old('transfer_date', date('d/m/Y')) }}" required autocomplete="off">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="note">Note</label>
                                    <input type="text" name="note" id="note" class="form-control"
                                           value="{{ old('note') }}" placeholder="Optional note" maxlength="100">
                                </div>
                            </div>
                        </div>

                        @php
                            $oldBanks   = old('to_bank_id', ['']);
                            $oldAmounts = old('amount', []);
                        @endphp

                        <table class="table table-bordered" id="transfer_rows">
                            <thead>
                                <tr>
                                    <th>Transfer To (Bank / Cash) <span class="text-danger">*</span></th>
                                    <th width="30%">Amount <span class="text-danger">*</span></th>
                                    <th width="60" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($oldBanks as $i => $oldBank)
                                <tr class="transfer-row">
                                    <td>
                                        <select name="to_bank_id[]" class="form-control" required>
                                            <option value="">Select Bank / Cash</option>
                                            @foreach($accounts as $account)
                                            <option value="{{ $account->inv_abi_id }}" {{ (string) $oldBank === (string) $account->inv_abi_id ? 'selected' : '' }}>
                                                {{ $accountLabels[$account->inv_abi_id] }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="amount[]"
                                               class="form-control transfer-amount" style="text-align:right;"
                                               value="{{ $oldAmounts[$i] ?? '' }}" placeholder="0.00" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" id="add_row">
                                            <i class="fa fa-plus"></i> Add Another Bank / Cash
                                        </button>
                                        <span class="pull-right"><b>Total Transfer</b></span>
                                    </td>
                                    <td style="text-align:right;"><b id="transfer_total">0.00</b></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><span class="pull-right">Left To Transfer After This</span></td>
                                    <td style="text-align:right;" id="transfer_left">{{ number_format($available, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>

                        <p class="text-danger" id="transfer_over" style="display:none;">
                            Total transfer is more than the available amount ({{ number_format($available, 2) }}).
                        </p>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-success" id="transfer_submit"
                                    onclick="return confirm('Are you sure you want to transfer this amount?');">
                                <i class="fa fa-exchange"></i> Transfer
                            </button>
                            <a href="{{ route('accounts.bank-loan.list') }}" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                    @endif

                    {{-- Transfer History --}}
                    <h4>Transfer History</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Voucher</th>
                                <th>Transferred To</th>
                                <th style="text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfers as $transfer)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($transfer->inv_abs_transaction_date)->format('d/m/Y') }}</td>
                                <td>{{ $transfer->inv_abs_voucher_no }}</td>
                                <td>{{ $accountLabels[$transfer->inv_abs_bank_id] ?? 'N/A' }}</td>
                                <td style="text-align:right;">{{ number_format($transfer->inv_abs_credit, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No transfers yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
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

        var available = {{ round($available, 2) }};

        function money(value) {
            return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Keep the total and "left" figures in step with the rows
        function updateTotal() {
            var total = 0;
            $('.transfer-amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            total = Math.round(total * 100) / 100;

            var over = total > available;
            $('#transfer_total').text(money(total));
            $('#transfer_left').text(money(available - total)).toggleClass('text-danger', over);
            $('#transfer_over').toggle(over);
            $('#transfer_submit').prop('disabled', over || total <= 0);
            $('.remove-row').prop('disabled', $('.transfer-row').length === 1);
        }

        $('#add_row').on('click', function() {
            var row = $('.transfer-row:first').clone();

            // custom.js turns the select into select2; strip the copied widget and start a fresh one
            row.find('.select2-container').remove();
            var select = row.find('select')
                .removeClass('select2-hidden-accessible')
                .removeAttr('data-select2-id aria-hidden tabindex');
            select.find('option').removeAttr('data-select2-id selected');
            select.val('');
            row.find('input').val('');

            $('#transfer_rows tbody').append(row);
            if ($.fn.select2) {
                select.select2({ placeholder: 'Select option', allowClear: true });
            }
            updateTotal();
        });

        $('#transfer_rows').on('click', '.remove-row', function() {
            if ($('.transfer-row').length > 1) {
                $(this).closest('tr').remove();
                updateTotal();
            }
        });

        $('#transfer_rows').on('input change', '.transfer-amount', updateTotal);

        updateTotal();
    });
</script>
@endsection

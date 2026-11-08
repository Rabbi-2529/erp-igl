@extends('layouts.master')
@section('title','Add Expense')
@section('inventory_class','active')
@section('accounts_class','active')
@section('expense_class','active')
@section('expense_add','active')
@section('content')
{{-- Kept in the content: the master layout does not output a custom_style section --}}
<style>
    #expense_rows td { vertical-align: middle; }
    #expense_rows .row-sl { width: 40px; text-align: center; }
    #expense_rows .expense-amount { text-align: right; }
</style>
<section class="content">
    <section class="content-header">
        @if(session()->has('suc'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('suc') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        @if(session()->has('err'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('err') }}
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

        <h1>Add Expense</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            Expense Entry
                            <a href="{{ route('accounts.expenses-voucher-list') }}" class="btn btn-info btn-sm pull-right">
                                <i class="fa fa-list"></i> Expenses List
                            </a>
                        </h3>
                    </div>
                </div>

                <div class="panel-body">
                    @if($expenses->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i> No expense heads yet.
                            Add them on the <a href="{{ route('accounts.expenses') }}">Expenses</a> page first.
                        </div>
                    @else
                    <form action="{{ route('accounts.expense-cost-add') }}" method="post" id="expense_form">
                        @csrf

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="trans_date">Date <span class="text-danger">*</span></label>
                                    <input type="text" name="trans_date" id="trans_date" class="form-control"
                                           value="{{ old('trans_date', date('d/m/Y')) }}" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="bank_id">Pay From (Bank / Cash) <span class="text-danger">*</span></label>
                                    <select name="bank_id" id="bank_id" class="form-control" style="width: 100%;" required onchange="loadAvailableBalanceOfBank()">
                                        <option value="">Select Bank / Cash</option>
                                        @foreach($banks as $bank)
                                        <option value="{{ $bank->inv_abi_id }}" {{ old('bank_id') == $bank->inv_abi_id ? 'selected' : '' }}>
                                            {{ $bank->bank_info['bank_name'] ?? $bank->inv_abi_account_name }} ({{ $bank->inv_abi_account_no }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="bank_balance_div" style="margin-top: 4px;"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="voucher_no">Voucher No</label>
                                    <input type="text" name="voucher_no" id="voucher_no" class="form-control"
                                           value="{{ old('voucher_no') }}" maxlength="128" placeholder="Optional (auto if empty)">
                                </div>
                            </div>
                        </div>

                        @php
                            $oldExpenses = old('expense_id', ['']);
                            $oldNotes    = old('note', []);
                            $oldAmounts  = old('amount', []);
                        @endphp

                        <table class="table table-bordered" id="expense_rows">
                            <thead>
                                <tr>
                                    <th class="row-sl">SL</th>
                                    <th width="35%">Expense <span class="text-danger">*</span></th>
                                    <th>Narration</th>
                                    <th width="18%">Amount <span class="text-danger">*</span></th>
                                    <th width="60"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($oldExpenses as $i => $oldExpense)
                                <tr class="expense-row">
                                    <td class="row-sl">{{ $i + 1 }}</td>
                                    <td>
                                        <select name="expense_id[]" class="form-control" style="width: 100%;" required>
                                            <option value="">Select Expense</option>
                                            @foreach($exp_categories as $category)
                                                @if(isset($expenses[$category->inv_acc_exp_cat_category_id]))
                                                <optgroup label="{{ $category->inv_acc_exp_cat_category_name }}">
                                                    @foreach($expenses[$category->inv_acc_exp_cat_category_id] as $expense)
                                                    <option value="{{ $expense->inv_acc_exp_id }}" {{ (string) $oldExpense === (string) $expense->inv_acc_exp_id ? 'selected' : '' }}>
                                                        {{ $expense->inv_acc_exp_expense_name }}
                                                    </option>
                                                    @endforeach
                                                </optgroup>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="note[]" class="form-control" maxlength="255"
                                               value="{{ $oldNotes[$i] ?? '' }}" placeholder="Optional">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="amount[]"
                                               class="form-control expense-amount" value="{{ $oldAmounts[$i] ?? '' }}" placeholder="0.00" required>
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
                                    <td colspan="3">
                                        <button type="button" class="btn btn-info btn-sm" id="add_row">
                                            <i class="fa fa-plus"></i> Add Another Expense
                                        </button>
                                        <span class="pull-right"><b>Total</b></span>
                                    </td>
                                    <td class="text-right"><b id="expense_total">0.00</b></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-success" id="submit-button-id">
                                <i class="fa fa-check"></i> Save Expense
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script type="text/javascript">
$(document).ready(function () {
    $("#trans_date").datepicker({
        dateFormat: 'dd/mm/yy', // the server reads this date as d/m/Y
        changeMonth: true,
        changeYear: true
    });

    function money(value) {
        return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Row numbers, total and the remove buttons follow the rows
    function refreshRows() {
        var total = 0;
        $('.expense-row').each(function (i) {
            $(this).find('.row-sl').text(i + 1);
            total += parseFloat($(this).find('.expense-amount').val()) || 0;
        });
        $('#expense_total').text(money(Math.round(total * 100) / 100));
        $('.remove-row').prop('disabled', $('.expense-row').length === 1);
    }

    $('#add_row').on('click', function () {
        var row = $('.expense-row:first').clone();

        // custom.js turns the select into select2; strip the copied widget and start a fresh one
        row.find('.select2-container').remove();
        var select = row.find('select')
            .removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id aria-hidden tabindex');
        select.find('option').removeAttr('data-select2-id selected');
        select.val('');
        row.find('input').val('');

        $('#expense_rows tbody').append(row);
        if ($.fn.select2) {
            select.select2({ placeholder: 'Select option', allowClear: true });
        }
        refreshRows();
    });

    $('#expense_rows').on('click', '.remove-row', function () {
        if ($('.expense-row').length > 1) {
            $(this).closest('tr').remove();
            refreshRows();
        }
    });

    $('#expense_rows').on('input change', '.expense-amount', refreshRows);

    refreshRows();
    if ($('#bank_id').val()) {
        loadAvailableBalanceOfBank();
    }
});

function loadAvailableBalanceOfBank() {
    var bank_id = $("#bank_id").val();
    if (!bank_id) {
        $(".bank_balance_div").html('');
        return;
    }
    $.ajax({
        type: "GET",
        url: "{{ route('accounts.ajax-load-bank-balance') }}",
        data: { bank_id: bank_id },
        success: function (result) {
            $(".bank_balance_div").html(result);
        }
    });
}

$("#expense_form").submit(function () {
    var btn = $("#submit-button-id");
    btn.prop('disabled', true);
    setTimeout(function () {
        btn.prop('disabled', false);
    }, 5000);
    return true;
});
</script>
@endsection

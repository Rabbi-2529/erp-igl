@extends('layouts.master')
@section('title', 'OMS Accounts')

@section('oms_class', 'active')
@section('holiday_class', 'active')

@section('content')

{{-- Custom date picker (flatpickr) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<style>
    /* Field with calendar icon */
    .oms-date { position: relative; }
    .oms-date .flatpickr-wrapper { display: block; width: 100%; }
    .oms-date__input { background: #fff !important; cursor: pointer; padding-right: 38px; }
    .oms-date__icon {
        position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
        width: 18px; height: 18px; color: #3c8dbc; pointer-events: none;
    }

    /* Calendar theme */
    .flatpickr-calendar {
        border: 0; border-radius: 10px; font-family: inherit;
        box-shadow: 0 12px 32px rgba(0, 0, 0, .18);
    }
    .flatpickr-calendar.static { top: calc(100% + 6px); z-index: 1060; }
    .flatpickr-months { background: #3c8dbc; border-radius: 10px 10px 0 0; padding: 4px 0; }
    .flatpickr-months .flatpickr-month,
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-current-month input.cur-year { color: #fff; fill: #fff; font-weight: 600; }
    .flatpickr-current-month .flatpickr-monthDropdown-months option { color: #333; }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month { color: #fff; fill: #fff; padding-top: 14px; }
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg { fill: #ffd54f; }
    span.flatpickr-weekday { color: #3c8dbc; font-weight: 700; }
    .flatpickr-day { border-radius: 8px; }
    .flatpickr-day.today { border-color: #3c8dbc; }
    .flatpickr-day.today:hover { background: #3c8dbc; color: #fff; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover {
        background: #3c8dbc; border-color: #3c8dbc; color: #fff;
    }

    /* Quick buttons under the calendar */
    .oms-date__quick {
        display: flex; gap: 6px; padding: 8px; border-top: 1px solid #eee;
    }
    .oms-date__quick button {
        flex: 1; border: 1px solid #d6e4ee; background: #f4f9fc; color: #3c8dbc;
        border-radius: 6px; padding: 5px 0; font-size: 12px; font-weight: 600;
    }
    .oms-date__quick button:hover { background: #3c8dbc; border-color: #3c8dbc; color: #fff; }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">

            <div class="panel-heading">
                <div class="panel-title row">
                    <div class="col-md-6">
                        <h4>OMS User Bill Details</h4>
                    </div>

                    <div class="col-md-6 text-right">
                        <a href=""
                           class="btn btn-info"
                           style="margin-top: 10px;"
                           data-toggle="modal"
                           data-target="#oms_convence_pay">
                            PAID
                        </a>
                    </div>
                </div>
            </div>

            {{-- Pay Bill Modal START --}}
            <div class="modal fade" id="oms_convence_pay" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">

                        {{ Form::open([
                            'action' => 'OmsController@pay_bills',
                            'method' => 'post',
                            'id' => 'pay_bill_form',
                            'class' => 'form-horizontal'
                        ]) }}

                        <div class="modal-header">
                            <button type="button"
                                    class="close"
                                    data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>

                            <h4 class="modal-title">Pay Bill</h4>
                        </div>

                        <div class="modal-body">
                            <div class="box-body">

                                {{-- Pay To --}}
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        Pay To
                                    </label>

                                    <div class="col-sm-9">
                                        @php
                                            $sorted_users = $users->sortBy('au_user_id');
                                        @endphp

                                        <select
                                            class="form-control select2"
                                            style="width: 100%;"
                                            name="oms_staff_id"
                                            onchange="showUser(this.value)"
                                            required>
                                            <option value="">Select</option>

                                            @foreach($sorted_users as $user)
                                                <option value="{{ $user->au_id }}">
                                                    {{ $user->au_user_id }}
                                                    ({{ $user->au_name }})
                                                </option>
                                            @endforeach
                                        </select>

                                        <b>
                                            <p id="balance" class="text-success"></p>
                                        </b>
                                    </div>
                                </div>

                                {{-- Pay For --}}
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        Pay For
                                    </label>

                                    <div class="col-sm-9">
                                        <select
                                            class="form-control select2"
                                            style="width: 100%;"
                                            name="paid_for"
                                            required>
                                            <option value="">Select</option>
                                            <option value="7">Conveyance</option>
                                            <option value="8">Lunch</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Payment System --}}
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        Payment System
                                    </label>

                                    <div class="col-sm-9">
                                        <select
                                            class="form-control select2"
                                            style="width: 100%;"
                                            name="oms_payment"
                                            required>
                                            <option value="">Select</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Check">Check</option>
                                            <option value="Mobile Banking">Mobile Banking</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Bank (payment and service charge are cut from this bank) --}}
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        Bank
                                    </label>

                                    <div class="col-sm-9">
                                        <select
                                            class="form-control select2"
                                            style="width: 100%;"
                                            name="bank_id"
                                            onchange="showBankBalance(this.value)"
                                            required>
                                            <option value="">Select</option>

                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->inv_abi_id }}">
                                                    {{ $bank->bank_info['bank_name'] ?? $bank->inv_abi_account_name }}
                                                    ({{ $bank->inv_abi_account_no }})
                                                </option>
                                            @endforeach
                                        </select>

                                        <b>
                                            <p id="bank_balance"></p>
                                        </b>
                                    </div>
                                </div>

                                {{-- Purpose --}}
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        Purpose
                                    </label>

                                    <div class="col-sm-9">
                                        <input
                                            type="text"
                                            name="oms_reason"
                                            class="form-control"
                                            placeholder="Enter Purpose..."
                                            required>
                                    </div>
                                </div>

                                {{-- Amount --}}
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        Amount
                                    </label>

                                    <div class="col-sm-9">
                                        <input
                                            type="number"
                                            name="oms_credit"
                                            class="form-control"
                                            placeholder="Enter Amount..."
                                            min="0"
                                            step="0.01"
                                            required>
                                    </div>
                                </div>

                                {{-- Service Charge (optional, saved as its own bank transaction) --}}
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        Service Charge
                                    </label>

                                    <div class="col-sm-9">
                                        <input
                                            type="number"
                                            name="service_charge"
                                            class="form-control"
                                            placeholder="Optional"
                                            min="0"
                                            step="0.01">
                                        <small class="text-muted">
                                            Also cut from the selected bank as a separate transaction.
                                        </small>
                                    </div>
                                </div>

                                {{-- Payment Date (custom date picker) --}}
                                <div class="form-group">
                                    <label for="oms_transaction_date"
                                           class="col-sm-2 control-label">
                                        Payment Date
                                    </label>

                                    <div class="col-sm-9">
                                        <div class="oms-date">
                                            {{-- Sends Y-m-d to the server; shows "18 Oct 2026" to the user --}}
                                            <input
                                                type="text"
                                                name="oms_transaction_date"
                                                id="oms_transaction_date"
                                                class="form-control"
                                                value="{{ date('Y-m-d') }}"
                                                placeholder="Select payment date"
                                                autocomplete="off"
                                                required>
                                            <svg class="oms-date__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-warning pull-left"
                                    data-dismiss="modal">
                                Close
                            </button>

                            <button type="submit"
                                    id="submitBtn"
                                    class="btn btn-primary">
                                PAID
                            </button>
                        </div>

                        {{ Form::close() }}

                    </div>
                </div>
            </div>
            {{-- Pay Bill Modal END --}}

            {{-- Accounts Table --}}
            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Employee ID</th>
                            <th>User Name</th>
                            <th>Company Name</th>
                            <th>Mobile</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $balance = 0;
                            $sl = 0;

                            $sorted_confirms = $confirms->sortBy(function ($item) {
                                return $item->first()->employee_info['au_user_id'] ?? '';
                            });
                        @endphp

                        @foreach($sorted_confirms as $staffId => $group)
                            @php
                                $firstConfirm = $group->first();
                                $employeeInfo = $firstConfirm
                                    ? $firstConfirm->employee_info
                                    : null;

                                $debit = $group->sum('oms_debit') ?? 0;
                                $credit = $group->sum('oms_credit') ?? 0;
                                $currentBalance = $credit - $debit;

                                $balance += $currentBalance;
                            @endphp

                            <tr>
                                <td>{{ ++$sl }}</td>

                                <td>
                                    {{ $employeeInfo['au_user_id'] ?? 'No Data' }}
                                </td>

                                <td>
                                    {{ $employeeInfo['au_name'] ?? 'No Data' }}
                                </td>

                                <td>
                                    {{ $employeeInfo['au_company_name'] ?? 'No Data' }}
                                </td>

                                <td>
                                    {{ $employeeInfo['au_mobile'] ?? 'No Data' }}
                                </td>

                                <td align="right">
                                    {{ number_format($debit, 2) }}
                                </td>

                                <td align="right">
                                    {{ number_format($credit, 2) }}
                                </td>

                                <td align="right">
                                    {{ number_format($currentBalance, 2) }}
                                </td>

                                <td align="center">
                                    @if($firstConfirm && isset($firstConfirm->oms_staff_id))
                                        <a href="{{ url('/accounts_details', ['id' => $firstConfirm->oms_staff_id]) }}"
                                           class="btn btn-info btn-sm">
                                            Details
                                        </a>
                                    @else
                                        <span class="text-muted">No Details</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="6" align="right">Total</td>
                            <td align="right">{{ number_format($balance, 2) }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- Custom date picker setup --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
var paymentDatePicker = flatpickr('#oms_transaction_date', {
    dateFormat: 'Y-m-d',          // value sent to the server
    altInput: true,               // friendly text shown to the user
    altFormat: 'd M Y',
    altInputClass: 'form-control oms-date__input',
    defaultDate: '{{ date('Y-m-d') }}',
    static: true,                 // render inside the modal so Bootstrap focus handling doesn't fight it
    disableMobile: true,          // use this picker on phones too
    onReady: function (selectedDates, dateStr, instance) {
        // "Today" and "Yesterday" quick buttons under the calendar
        var bar = document.createElement('div');
        bar.className = 'oms-date__quick';
        [['Today', 0], ['Yesterday', 1]].forEach(function (item) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = item[0];
            btn.addEventListener('click', function () {
                var d = new Date();
                d.setDate(d.getDate() - item[1]);
                instance.setDate(d, true);
                instance.close();
            });
            bar.appendChild(btn);
        });
        instance.calendarContainer.appendChild(bar);
    }
});

// Close the calendar when the modal closes
$('#oms_convence_pay').on('hide.bs.modal', function () {
    paymentDatePicker.close();
});
</script>

{{-- Pay Bill AJAX --}}
<script>
$(document).ready(function () {

    $('#pay_bill_form').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var submitBtn = $('#submitBtn');

        if (submitBtn.prop('disabled')) {
            return;
        }

        // The date picker's text box is read-only, so the browser can't check "required" itself
        if (!$('#oms_transaction_date').val()) {
            alert('Please select the payment date.');
            paymentDatePicker.open();
            return;
        }

        submitBtn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),

            success: function (response) {
                form[0].reset();
                $('#bank_balance').text('');
                paymentDatePicker.setDate(new Date(), true);
                submitBtn.prop('disabled', false);

                setTimeout(function () {
                    location.reload();
                }, 1000);
            },

            error: function (xhr) {
                submitBtn.prop('disabled', false);

                if (xhr.status === 422 && xhr.responseJSON) {
                    var errors = xhr.responseJSON.errors;
                    var messages = [];

                    if (errors) {
                        $.each(errors, function (field, fieldErrors) {
                            messages.push(fieldErrors.join('\n'));
                        });
                    }

                    alert(messages.length
                        ? messages.join('\n')
                        : 'Please check the submitted information.');
                } else {
                    alert('Payment failed. Please try again.');
                }
            }
        });
    });

});
</script>

{{-- Employee Balance --}}
<script>
function showUser(id) {

    var link = "{{ route('accounts_user_amount') }}";

    $.ajax({
        url: link,
        type: 'GET',
        data: {
            id: id
        },

        success: function (result) {
            if (result == 0) {
                $('#balance').text('No Due Balance!');
            } else {
                $('#balance').text('Amount is: ' + result);
            }
        },

        error: function () {
            $('#balance').text('Unable to load balance.');
        }
    });
}

// Selected bank balance
function showBankBalance(id) {

    if (!id) {
        $('#bank_balance').text('');
        return;
    }

    $.ajax({
        url: "{{ route('accounts_bank_balance') }}",
        type: 'GET',
        data: {
            bank_id: id
        },

        success: function (result) {
            var balance = parseFloat(result) || 0;

            $('#bank_balance')
                .toggleClass('text-success', balance >= 0)
                .toggleClass('text-danger', balance < 0)
                .text('Bank Balance: ' + balance.toFixed(2));
        },

        error: function () {
            $('#bank_balance').text('Unable to load bank balance.');
        }
    });
}
</script>

@endsection
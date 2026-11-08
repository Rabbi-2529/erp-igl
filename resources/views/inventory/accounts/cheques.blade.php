@extends('layouts.master')
@section('title', 'Cheque Register')
@section('accounts_class', 'active')
@section('bank_class', 'active')
@section('cheques_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Cheque Register', 'printInfo' => ['Showing' => ucfirst($status === 'all' ? 'all' : $status) . ' cheques' . ($direction ? ' (' . $direction . ')' : ''), 'Cheques' => $list->count()]])
@include('lc._alerts')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $badge = ['pending' => 'label-warning', 'cleared' => 'label-success', 'bounced' => 'label-danger', 'cancelled' => 'label-default'];
@endphp
<style>
    .chq-party { display: none; }
    .chq-party.on { display: block; }
    .chq-late td:first-child { box-shadow: inset 4px 0 0 #c4161f; }
    .chq-soon td:first-child { box-shadow: inset 4px 0 0 #e0a800; }
    .chq-actions { white-space: nowrap; }
    .chq-actions .btn { margin: 1px 0; }
</style>

<div class="rep-head">
    <div>
        <h3>Cheque Register</h3>
        <p class="rep-sub">Cheques received and issued. A pending cheque moves no money; mark it cleared when the bank pays it.</p>
    </div>
    <div>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#chqAdd"><i class="fa fa-plus"></i> Add a cheque</button>
    </div>
</div>

<div class="rep-cards">
    <div class="rep-card green"><b>{{ $money($cards['received']) }}</b><span>Pending cheques received (to come in)</span></div>
    <div class="rep-card red"><b>{{ $money($cards['issued']) }}</b><span>Pending cheques issued (to go out)</span></div>
    <div class="rep-card blue"><b>{{ $cards['due'] }}</b><span>Pending with a date up to {{ \Carbon\Carbon::parse($soon)->format('d M') }}</span></div>
    <div class="rep-card black"><b>{{ $cards['bounced'] }}</b><span>Bounced this month</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <form method="GET" class="rep-filter" style="margin-bottom: 12px;">
            <div>
                <label for="status">Status</label>
                <select class="form-control dont-select-me" id="status" name="status">
                    @foreach(['pending' => 'Pending', 'cleared' => 'Cleared', 'bounced' => 'Bounced', 'cancelled' => 'Cancelled', 'all' => 'All'] as $k => $v)
                        <option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="direction">Type</label>
                <select class="form-control dont-select-me" id="direction" name="direction">
                    <option value="">Received and issued</option>
                    <option value="received" @selected($direction === 'received')>Received</option>
                    <option value="issued" @selected($direction === 'issued')>Issued</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        </form>

        @if($list->isEmpty())
            <p class="rep-empty">No {{ $status === 'all' ? '' : $status }} cheques.</p>
        @else
            <table class="table table-bordered table-striped rep-table">
                <thead>
                    <tr><th>Cheque date</th><th>Type</th><th>From / to</th><th>Cheque no.</th><th>Bank</th><th>Our account</th><th class="num">Amount</th><th>Status</th><th class="rep-noprint"></th></tr>
                </thead>
                <tbody>
                    @foreach($list as $c)
                        @php
                            $late = $c->chq_status === 'pending' && $c->chq_date < $today->toDateString();
                            $near = $c->chq_status === 'pending' && !$late && $c->chq_date <= $soon;
                        @endphp
                        <tr class="{{ $late ? 'chq-late' : ($near ? 'chq-soon' : '') }}">
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($c->chq_date)->format('d M Y') }}
                                @if($late)<br><small class="rep-neg">date passed</small>@elseif($near)<br><small class="text-muted">due soon</small>@endif</td>
                            <td>{{ $c->chq_direction === 'received' ? 'Received' : 'Issued' }}</td>
                            <td>{{ $c->chq_party_name }} <small class="text-muted">{{ $c->chq_party_type }}</small></td>
                            <td>{{ $c->chq_number }}</td>
                            <td>{{ $c->chq_bank_name }}</td>
                            <td>{{ $c->chq_account_id ? ($accountNames[$c->chq_account_id] ?? '#' . $c->chq_account_id) : '' }}</td>
                            <td class="num {{ $c->chq_direction === 'received' ? 'rep-pos' : 'rep-neg' }}">{{ $money($c->chq_amount) }}</td>
                            <td><span class="label {{ $badge[$c->chq_status] ?? 'label-default' }}">{{ ucfirst($c->chq_status) }}</span>
                                @if($c->chq_status_date && $c->chq_status !== 'pending')<br><small class="text-muted">{{ \Carbon\Carbon::parse($c->chq_status_date)->format('d M Y') }}</small>@endif
                                @if($c->chq_note)<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($c->chq_note, 60) }}</small>@endif</td>
                            <td class="chq-actions rep-noprint">
                                @if($c->chq_status === 'pending')
                                    <button type="button" class="btn btn-success btn-xs chq-clear" data-url="{{ route('accounts.cheques.clear', $c->chq_id) }}"
                                        data-account="{{ $c->chq_account_id }}" data-label="{{ $c->chq_direction === 'received' ? 'Deposit to' : 'Pay from' }}"
                                        data-title="Cheque {{ $c->chq_number }} - {{ $money($c->chq_amount) }}"><i class="fa fa-check"></i> Cleared</button>
                                    <button type="button" class="btn btn-danger btn-xs chq-close" data-url="{{ route('accounts.cheques.bounce', $c->chq_id) }}" data-what="Bounced"
                                        data-title="Cheque {{ $c->chq_number }}"><i class="fa fa-times"></i> Bounced</button>
                                    <button type="button" class="btn btn-default btn-xs chq-close" data-url="{{ route('accounts.cheques.cancel', $c->chq_id) }}" data-what="Cancelled"
                                        data-title="Cheque {{ $c->chq_number }}"><i class="fa fa-ban"></i> Cancel</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <p class="rep-note">
            Cleared from a customer: saved as a Customer Payment (customer due goes down, money into the account).
            Cleared to a supplier: saved as a Supplier Payment. Other cheques: saved as a Bank Deposit or Withdraw.
            Bounced and cancelled cheques move no money. Mark a cheque cleared only when the bank has paid it.
        </p>
    </div>
</div>

{{-- Add a cheque --}}
<div class="modal fade" id="chqAdd" tabindex="-1" role="dialog" aria-labelledby="chqAddTitle">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('accounts.cheques.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="chqAddTitle">Add a cheque</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label for="add_direction">Type</label>
                        <select class="form-control dont-select-me" id="add_direction" name="direction">
                            <option value="received" @selected(old('direction', 'received') === 'received')>Received (money coming in)</option>
                            <option value="issued" @selected(old('direction') === 'issued')>Issued (money going out)</option>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="add_party_type">From / to</label>
                        <select class="form-control dont-select-me" id="add_party_type" name="party_type">
                            <option value="customer" @selected(old('party_type', 'customer') === 'customer')>A customer</option>
                            <option value="supplier" @selected(old('party_type') === 'supplier')>A supplier</option>
                            <option value="other" @selected(old('party_type') === 'other')>Someone else</option>
                        </select>
                    </div>
                </div>
                <div class="form-group chq-party" data-party="customer">
                    <label for="add_customer">Customer</label>
                    <select class="form-control" id="add_customer" name="customer_id" style="width: 100%;">
                        <option value="">Choose a customer</option>
                        @foreach($customers as $cu)<option value="{{ $cu->inv_cus_id }}" @selected(old('customer_id') == $cu->inv_cus_id)>{{ $cu->inv_cus_name }}{{ $cu->inv_cus_mobile ? ' - ' . $cu->inv_cus_mobile : '' }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group chq-party" data-party="supplier">
                    <label for="add_supplier">Supplier</label>
                    <select class="form-control" id="add_supplier" name="supplier_id" style="width: 100%;">
                        <option value="">Choose a supplier</option>
                        @foreach($suppliers as $su)<option value="{{ $su->inv_sup_id }}" @selected(old('supplier_id') == $su->inv_sup_id)>{{ $su->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group chq-party" data-party="other">
                    <label for="add_party_name">Name</label>
                    <input type="text" class="form-control" id="add_party_name" name="party_name" maxlength="120" value="{{ old('party_name') }}">
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group"><label for="add_number">Cheque number</label><input type="text" class="form-control" id="add_number" name="number" maxlength="40" value="{{ old('number') }}" required></div>
                    <div class="col-sm-6 form-group"><label for="add_bank">Bank on the cheque</label><input type="text" class="form-control" id="add_bank" name="bank_name" maxlength="120" value="{{ old('bank_name') }}"></div>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group"><label for="add_date">Cheque date</label><input type="text" class="form-control datepicker" id="add_date" name="chq_date" value="{{ old('chq_date', $today->toDateString()) }}" autocomplete="off" required></div>
                    <div class="col-sm-6 form-group"><label for="add_amount">Amount</label><input type="number" class="form-control" id="add_amount" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required></div>
                </div>
                <div class="form-group">
                    <label for="add_account" id="add_account_label">Deposit to (can be chosen later)</label>
                    <select class="form-control" id="add_account" name="account_id" style="width: 100%;">
                        <option value="">Choose later</option>
                        @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('account_id') == $a->id)>{{ $a->is_cash ? 'Cash: ' : '' }}{{ $a->label }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label for="add_note">Note</label><input type="text" class="form-control" id="add_note" name="note" maxlength="255" value="{{ old('note') }}"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save as pending</button>
            </div>
        </form>
    </div>
</div>

{{-- Mark cleared --}}
<div class="modal fade" id="chqClear" tabindex="-1" role="dialog" aria-labelledby="chqClearTitle">
    <div class="modal-dialog modal-sm" role="document">
        <form method="POST" action="" class="modal-content" id="chqClearForm">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="chqClearTitle">Cheque cleared</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="clear_account" id="clear_account_label">Account</label>
                    <select class="form-control dont-select-me" id="clear_account" name="account_id" required>
                        <option value="">Choose an account</option>
                        @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->is_cash ? 'Cash: ' : '' }}{{ $a->label }} ({{ $money($a->closing) }})</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label for="clear_date">Date the bank paid it</label><input type="text" class="form-control datepicker" id="clear_date" name="date" value="{{ $today->toDateString() }}" autocomplete="off" required></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Bounced / cancelled --}}
<div class="modal fade" id="chqClose" tabindex="-1" role="dialog" aria-labelledby="chqCloseTitle">
    <div class="modal-dialog modal-sm" role="document">
        <form method="POST" action="" class="modal-content" id="chqCloseForm">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="chqCloseTitle">Cheque</h4>
            </div>
            <div class="modal-body">
                <div class="form-group"><label for="close_reason">Reason (optional)</label><input type="text" class="form-control" id="close_reason" name="reason" maxlength="120"></div>
                <p class="rep-note">No money is moved.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger" id="chqCloseBtn">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom_script')
<script>
(function ($) {
    function parties() {
        var dir = $('#add_direction').val(), type = $('#add_party_type');
        type.find('option[value=customer]').prop('disabled', dir === 'issued');
        type.find('option[value=supplier]').prop('disabled', dir === 'received');
        if (type.find('option:selected').prop('disabled')) { type.val(dir === 'issued' ? 'supplier' : 'customer'); }
        $('.chq-party').removeClass('on').filter('[data-party=' + type.val() + ']').addClass('on');
        $('#add_account_label').text((dir === 'issued' ? 'Pay from' : 'Deposit to') + ' (can be chosen later)');
    }
    $('#add_direction, #add_party_type').on('change', parties);
    parties();

    $(document).on('click', '.chq-clear', function () {
        var b = $(this);
        $('#chqClearForm').attr('action', b.data('url'));
        $('#chqClearTitle').text(b.data('title') + ' cleared');
        $('#clear_account_label').text(b.data('label'));
        $('#clear_account').val(b.data('account') || '');
        $('#chqClear').modal('show');
    });
    $(document).on('click', '.chq-close', function () {
        var b = $(this);
        $('#chqCloseForm').attr('action', b.data('url'));
        $('#chqCloseTitle').text(b.data('title') + ': ' + b.data('what').toLowerCase());
        $('#chqCloseBtn').text('Mark ' + b.data('what').toLowerCase());
        $('#close_reason').val('');
        $('#chqClose').modal('show');
    });
    @if($errors->any() || session('errmsg'))
        @if(old('number'))$('#chqAdd').modal('show');@endif
    @endif
})(jQuery);
</script>
@endsection

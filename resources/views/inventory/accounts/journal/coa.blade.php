@extends('layouts.master')
@section('title', 'Chart of Accounts')
@section('accounts_class', 'active')
@section('accounts_journal_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Chart of Accounts', 'printInfo' => ['Balances on' => now()->format('d M Y')]])
@include('inventory.accounts.journal._nav')
@include('lc._alerts')
@php
    $types = \App\Services\AccountsJournal::TYPES;
    $money = function ($v) { return number_format((float) $v, 2); };
@endphp
<style>
    .coa-tag { display: inline-block; margin-left: 6px; padding: 0 6px; border-radius: 9px; font-size: 11px; line-height: 17px; border: 1px solid var(--erp-border); color: var(--erp-muted); white-space: nowrap; }
    .coa-tag.auto { border-color: #0b7a3b; color: #0b7a3b; }
    html[data-theme="dark"] .coa-tag.auto { border-color: #6fd79a; color: #6fd79a; }
    .coa-group > td { font-weight: 700; }
    .coa-off td { opacity: .6; }
</style>

<div class="rep-head">
    <div>
        <h3>Chart of Accounts</h3>
        <p class="rep-sub">Accounts marked <span class="coa-tag auto">auto</span> are filled by the ERP from your sales, purchases, payments, expenses and payroll. Add your own accounts for journal vouchers.</p>
    </div>
    <div>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#coaAdd"><i class="fa fa-plus"></i> Add an account</button>
    </div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <table class="table table-bordered rep-table">
            <thead>
                <tr><th style="width: 110px;">Code</th><th>Account</th><th class="text-center" style="width: 110px;">In vouchers</th><th class="num" style="width: 150px;">Balance today</th><th class="rep-noprint" style="width: 120px;"></th></tr>
            </thead>
            <tbody>
                @foreach($types as $type => $label)
                    <tr class="rep-group"><th colspan="5">{{ $label }}</th></tr>
                    @foreach($rows->filter(function ($r) use ($type) { return $r->account->type === $type; }) as $r)
                        @php($a = $r->account)
                        <tr class="{{ $r->depth === 0 && !$a->dynamic ? 'coa-group' : '' }} {{ $a->active ? '' : 'coa-off' }}">
                            <td style="white-space: nowrap;">{{ $a->code }}</td>
                            <td style="padding-left: {{ 8 + $r->depth * 22 }}px !important;">
                                {{ $a->name }}
                                @if($a->system && !$a->dynamic)<span class="coa-tag auto" title="{{ $a->note }}">auto</span>@endif
                                @if(!$a->active)<span class="coa-tag">inactive</span>@endif
                                @if($a->note && !$a->dynamic)<br><small class="text-muted">{{ $a->note }}</small>@endif
                            </td>
                            <td class="text-center">{{ $a->manual ? 'Yes' : 'No' }}</td>
                            <td class="num {{ $r->balance < 0 ? 'rep-neg' : '' }}">{{ abs($r->balance) > 0.004 ? $money($r->balance) : '' }}</td>
                            <td class="rep-noprint" style="white-space: nowrap;">
                                <a href="{{ route('accounts.journal.ledger', ['account' => $a->key]) }}" class="btn btn-default btn-xs" title="Account ledger"><i class="fa fa-list-alt"></i> Ledger</a>
                                @if(!$a->dynamic)
                                    <button type="button" class="btn btn-default btn-xs coa-edit" title="Edit"
                                        data-id="{{ $a->coa_id }}" data-code="{{ $a->code }}" data-name="{{ $a->name }}" data-type="{{ $a->type }}"
                                        data-parent="{{ $a->parent }}" data-manual="{{ $a->manual ? 1 : 0 }}" data-status="{{ $a->active ? 1 : 0 }}"
                                        data-note="{{ $a->note }}" data-system="{{ $a->system ? 1 : 0 }}"><i class="fa fa-pencil"></i></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
        <p class="rep-note">
            Banks and cash accounts come from Accounts &gt; Bank; expense heads from Expense Categories and ledger heads from Ledger, so they are listed here by themselves.
            Balances are in each account's own direction: assets and expenses as debits, the rest as credits. A red figure is the other way round.
        </p>
    </div>
</div>

{{-- Add an account --}}
<div class="modal fade" id="coaAdd" tabindex="-1" role="dialog" aria-labelledby="coaAddTitle">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('accounts.journal.coa.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="coaAddTitle">Add an account</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4 form-group"><label for="add_code">Code</label><input type="text" class="form-control" id="add_code" name="code" maxlength="20" value="{{ old('code') }}" placeholder="e.g. 6150" required></div>
                    <div class="col-sm-8 form-group"><label for="add_name">Name</label><input type="text" class="form-control" id="add_name" name="name" maxlength="150" value="{{ old('name') }}" placeholder="e.g. Office Rent" required></div>
                </div>
                <div class="row">
                    <div class="col-sm-4 form-group">
                        <label for="add_type">Type</label>
                        <select class="form-control dont-select-me coa-type" id="add_type" name="type" data-parent="#add_parent">
                            @foreach($types as $k => $v)<option value="{{ $k }}" @selected(old('type', 'expense') === $k)>{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-sm-8 form-group">
                        <label for="add_parent">Show under (optional)</label>
                        <select class="form-control dont-select-me coa-parent" id="add_parent" name="parent">
                            <option value="">- top level -</option>
                            @foreach($parents as $p)<option value="{{ $p->key }}" data-type="{{ $p->type }}" @selected(old('parent') === $p->key)>{{ $p->code }} {{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group"><label for="add_note">Note (optional)</label><input type="text" class="form-control" id="add_note" name="note" maxlength="255" value="{{ old('note') }}"></div>
                <div class="form-group"><label style="font-weight: 400; cursor: pointer;"><input type="hidden" name="manual" value="0"><input type="checkbox" name="manual" value="1" checked> Can be used in journal vouchers</label></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Add</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit an account --}}
<div class="modal fade" id="coaEdit" tabindex="-1" role="dialog" aria-labelledby="coaEditTitle">
    <div class="modal-dialog" role="document">
        <form method="POST" action="" class="modal-content" id="coaEditForm">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="coaEditTitle">Edit account</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4 form-group"><label for="edit_code">Code</label><input type="text" class="form-control" id="edit_code" name="code" maxlength="20" required></div>
                    <div class="col-sm-8 form-group"><label for="edit_name">Name</label><input type="text" class="form-control" id="edit_name" name="name" maxlength="150" required></div>
                </div>
                <div class="coa-user-only">
                    <div class="form-group">
                        <label for="edit_parent">Show under</label>
                        <select class="form-control dont-select-me coa-parent" id="edit_parent" name="parent">
                            <option value="">- top level -</option>
                            @foreach($parents as $p)<option value="{{ $p->key }}" data-type="{{ $p->type }}">{{ $p->code }} {{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group"><label style="font-weight: 400; cursor: pointer;"><input type="hidden" name="manual" value="0"><input type="checkbox" name="manual" value="1" id="edit_manual"> Can be used in journal vouchers</label></div>
                    <div class="form-group"><label style="font-weight: 400; cursor: pointer;"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" id="edit_status"> Active</label></div>
                </div>
                <p class="rep-note coa-system-only">This account is filled by the ERP, so only its code, name and note can change.</p>
                <div class="form-group"><label for="edit_note">Note</label><input type="text" class="form-control" id="edit_note" name="note" maxlength="255"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom_script')
<script>
(function ($) {
    var updateUrl = {!! json_encode(route('accounts.journal.coa.update', 0)) !!};
    // only groups of the same type can hold an account
    function filterParents(select, type, self) {
        $(select).find('option[data-type]').each(function () {
            var ok = $(this).data('type') === type && this.value !== self;
            $(this).prop('disabled', !ok).toggle(ok);
        });
        if ($(select).find('option:selected').prop('disabled')) { $(select).val(''); }
    }
    $('#add_type').on('change', function () { filterParents('#add_parent', this.value, ''); }).trigger('change');
    $(document).on('click', '.coa-edit', function () {
        var b = $(this), system = String(b.data('system')) === '1';
        $('#coaEditForm').attr('action', updateUrl.replace(/0$/, b.data('id')));
        $('#coaEditTitle').text('Edit ' + b.data('code') + ' ' + b.data('name'));
        $('#edit_code').val(b.data('code'));
        $('#edit_name').val(b.data('name'));
        $('#edit_note').val(b.data('note') || '');
        filterParents('#edit_parent', b.data('type'), 'coa:' + b.data('id'));
        $('#edit_parent').val(b.data('parent') || '');
        $('#edit_manual').prop('checked', String(b.data('manual')) === '1');
        $('#edit_status').prop('checked', String(b.data('status')) === '1');
        $('.coa-user-only').toggle(!system);
        $('.coa-system-only').toggle(system);
        $('#coaEdit').modal('show');
    });
    @if($errors->any() || session('errmsg'))
        @if(old('code') && !old('_method'))$('#coaAdd').modal('show');@endif
    @endif
})(jQuery);
</script>
@endsection

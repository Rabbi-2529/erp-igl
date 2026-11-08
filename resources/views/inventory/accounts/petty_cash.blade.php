@extends('layouts.master')
@section('title', 'Petty Cash')
@section('accounts_class', 'active')
@section('bank_class', 'active')
@section('petty_cash_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Petty Cash', 'printInfo' => ['Date' => now()->format('d M Y'), 'Boxes' => $boxes->count()]])
@include('lc._alerts')

<div class="rep-head">
    <div>
        <h3>Petty Cash</h3>
        <p class="rep-sub">Small cash boxes for daily spending. Top up from a bank or the main cash, spend with Add Expense, and return what is left.</p>
    </div>
    <div>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <a href="{{ route('accounts.cost-add') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add an expense</a>
    </div>
</div>

@if($boxes->count())
    <div class="rep-cards">
        @foreach($boxes as $b)
            <div class="rep-card green">
                <b class="{{ $b->closing < 0 ? 'rep-neg' : '' }}">{{ number_format($b->closing, 2) }}</b>
                <span>{{ $b->account_name }} &middot; spent this month {{ number_format((float) ($spent[$b->id] ?? 0), 2) }}</span>
                <a href="{{ route('accounts.reports.cash-book', ['account' => $b->id]) }}">book</a>
            </div>
        @endforeach
    </div>
@endif

<div class="row">
    <div class="col-md-7">
        @if($boxes->count())
            <div class="panel panel-bd rep-noprint">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-exchange"></i> Top up or return</h4></div></div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('accounts.petty-cash.move') }}">
                        @csrf
                        <div class="rep-filter">
                            <div>
                                <label for="direction">What</label>
                                <select class="form-control dont-select-me" id="direction" name="direction">
                                    <option value="topup" @selected(old('direction') !== 'return')>Top up (money into the box)</option>
                                    <option value="return" @selected(old('direction') === 'return')>Return (money back out)</option>
                                </select>
                            </div>
                            <div>
                                <label for="box_id">Petty cash box</label>
                                <select class="form-control dont-select-me" id="box_id" name="box_id">
                                    @foreach($boxes as $b)<option value="{{ $b->id }}" @selected(old('box_id') == $b->id)>{{ $b->account_name }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label for="other_id" id="otherLabel">From account</label>
                                <select class="form-control dont-select-me" id="other_id" name="other_id" style="min-width: 260px;">
                                    @foreach($others as $a)<option value="{{ $a->id }}" @selected(old('other_id') == $a->id)>{{ $a->is_cash ? 'Cash: ' : '' }}{{ $a->label }} ({{ number_format($a->closing, 2) }})</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <div class="rep-filter" style="margin-top: 10px;">
                            <div><label for="amount">Amount</label><input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required></div>
                            <div><label for="date">Date</label><input type="text" class="form-control datepicker" id="date" name="date" value="{{ old('date', now()->toDateString()) }}" autocomplete="off" required></div>
                            <div style="flex: 1 1 200px;"><label for="note">Note</label><input type="text" class="form-control" id="note" name="note" maxlength="150" value="{{ old('note') }}"></div>
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="panel panel-bd">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-history"></i> Latest petty cash movements</h4></div></div>
                <div class="panel-body">
                    @if($recent->isEmpty())
                        <p class="rep-empty">Nothing yet. Top up a box to start.</p>
                    @else
                        <table class="table table-bordered table-striped rep-table">
                            <thead><tr><th>Date</th><th>Box</th><th>What</th><th>Note</th><th class="num">In</th><th class="num">Out</th></tr></thead>
                            <tbody>
                                @foreach($recent as $m)
                                    <tr><td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($m->inv_abs_transaction_date)->format('d M Y') }}</td><td>{{ $m->account }}</td><td>{{ $m->type_label }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($m->inv_abs_description, 60) }}</td><td class="num rep-pos">{{ $m->in ? number_format($m->in, 2) : '' }}</td><td class="num rep-neg">{{ $m->out ? number_format($m->out, 2) : '' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @else
            <div class="panel panel-bd"><div class="panel-body"><p class="rep-empty">No petty cash box yet. Make one on the right.</p></div></div>
        @endif
    </div>

    <div class="col-md-5 rep-noprint">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-plus-circle"></i> New petty cash box</h4></div></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('accounts.petty-cash.create') }}">
                    @csrf
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" maxlength="60" placeholder="e.g. Office petty cash" value="{{ old('name') }}" required>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Make box</button>
                </form>
                <p class="rep-note">
                    A box is a cash account, so it shows on Add Expense ("Pay from"), in the Cash &amp; Bank Book and on the Accounts Dashboard.
                    Your main cash account stays as it is.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_script')
<script>
(function ($) {
    function label() { $('#otherLabel').text($('#direction').val() === 'return' ? 'Back to account' : 'From account'); }
    $('#direction').on('change', label);
    label();
})(jQuery);
</script>
@endsection

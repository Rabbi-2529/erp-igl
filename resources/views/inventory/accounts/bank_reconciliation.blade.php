@extends('layouts.master')
@section('title', 'Bank Reconciliation')
@section('accounts_class', 'active')
@section('bank_class', 'active')
@section('bank_recon_class', 'active')

@section('content')
@include('inventory.accounts.reports._style', ['reportLinks' => false, 'printTitle' => 'Bank Reconciliation', 'printInfo' => ['Bank account' => optional($account)->label, 'Statement date' => $asOf->format('d M Y'), 'Bank statement balance' => $bankBalance === null ? '-' : number_format($bankBalance, 2)]])
@include('lc._alerts')
@php
    $money = function ($v) { return number_format((float) $v, 2); };
    $keep = array_filter(['account' => optional($account)->id, 'as_of' => $asOf->toDateString(), 'bank_balance' => $bankBalance], function ($v) { return $v !== null; });
@endphp
<style>
    .recon-sum td { padding: 6px 10px !important; }
    .recon-diff-ok { color: #0b7a3b; font-weight: 700; }
    .recon-diff-bad { color: #c4161f; font-weight: 700; }
    html[data-theme="dark"] .recon-diff-ok { color: #6fd79a; }
    html[data-theme="dark"] .recon-diff-bad { color: #f58b90; }
    .recon-bar { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; margin: 10px 0 0; }
    .recon-bar .form-control { height: 30px; width: 140px; }
    .recon-pick { width: 34px; text-align: center; }
</style>

<div class="rep-head">
    <div>
        <h3>Bank Reconciliation</h3>
        <p class="rep-sub">Tick each movement that is on your real bank statement. What stays unticked explains the difference.</p>
    </div>
    <form method="GET" class="rep-filter">
        <div>
            <label for="account">Bank account</label>
            <select class="form-control dont-select-me" id="account" name="account" style="min-width: 240px;">
                @forelse($banks as $b)
                    <option value="{{ $b->id }}" @selected(optional($account)->id == $b->id)>{{ $b->label }}</option>
                @empty
                    <option value="">No bank account</option>
                @endforelse
            </select>
        </div>
        <div><label for="as_of">Statement date</label><input type="text" class="form-control datepicker" id="as_of" name="as_of" value="{{ $asOf->toDateString() }}" autocomplete="off"></div>
        <div><label for="bank_balance">Balance on bank statement</label><input type="number" step="0.01" class="form-control" id="bank_balance" name="bank_balance" value="{{ $bankBalance }}" placeholder="from the bank"></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

@if(!$account)
    <div class="panel panel-bd"><div class="panel-body"><p class="rep-empty">No bank account in this branch. Add one in Accounts &gt; Bank first.</p></div></div>
@else
<div class="rep-cards">
    <div class="rep-card black"><b>{{ $money($sum['erp']) }}</b><span>Balance in the ERP on {{ $asOf->format('d M Y') }}</span></div>
    <div class="rep-card blue"><b>{{ $money($sum['ticked']) }}</b><span>Ticked movements (what the bank should show)</span></div>
    <div class="rep-card {{ $difference === null ? 'black' : ($difference == 0 ? 'green' : 'red') }}">
        <b class="{{ $difference === null ? '' : ($difference == 0 ? 'recon-diff-ok' : 'recon-diff-bad') }}">{{ $difference === null ? '-' : $money($difference) }}</b>
        <span>{{ $difference === null ? 'Enter the bank statement balance to see the difference' : ($difference == 0 ? 'Reconciled: no difference' : 'Difference (bank less ticked)') }}</span>
    </div>
    <div class="rep-card red"><b>{{ $open->count() }}</b><span>Not ticked yet</span></div>
</div>

<div class="row">
    <div class="col-md-5 col-md-push-7">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-balance-scale"></i> Summary</h4></div></div>
            <div class="panel-body">
                <table class="table rep-table recon-sum">
                    <tbody>
                        <tr><td>Balance in the ERP</td><td class="num">{{ $money($sum['erp']) }}</td></tr>
                        <tr><td class="rep-indent">Less: money in not on the bank statement yet</td><td class="num rep-neg">-{{ $money($sum['open_in']) }}</td></tr>
                        <tr><td class="rep-indent">Add: money out not on the bank statement yet</td><td class="num">{{ $money($sum['open_out']) }}</td></tr>
                        <tr class="rep-sub-total"><td>The bank should show</td><td class="num">{{ $money($sum['ticked']) }}</td></tr>
                        <tr><td>Balance on the bank statement</td><td class="num">{{ $bankBalance === null ? '-' : $money($bankBalance) }}</td></tr>
                        <tr class="rep-grand"><td>Difference</td>
                            <td class="num {{ $difference === null ? '' : ($difference == 0 ? 'recon-diff-ok' : 'recon-diff-bad') }}" id="reconDiff">{{ $difference === null ? '-' : $money($difference) }}</td></tr>
                    </tbody>
                </table>
                <p class="rep-note">
                    If the difference is not zero: the bank has a charge, interest or deposit that is not in the ERP yet (add it, then tick it),
                    or a movement is ticked by mistake. Ticking saves nothing else; you can untick any time.
                </p>
                <form method="POST" action="{{ route('accounts.bank-reconciliation.mark') }}" class="recon-bar rep-noprint" onsubmit="return confirm('Tick every movement up to this date as on the bank statement?');">
                    @csrf
                    @foreach($keep as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                    <input type="hidden" name="action" value="tick_until">
                    <div><label for="until" class="small">First time? Tick everything up to</label><input type="text" class="form-control datepicker" id="until" name="until" value="{{ $asOf->toDateString() }}" autocomplete="off"></div>
                    <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-check-square-o"></i> Tick all</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7 col-md-pull-5">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-square-o"></i> Not ticked yet</h4></div></div>
            <div class="panel-body">
                @if($open->isEmpty())
                    <p class="rep-empty">Everything up to {{ $asOf->format('d M Y') }} is ticked.</p>
                @else
                    <form method="POST" action="{{ route('accounts.bank-reconciliation.mark') }}" id="tickForm">
                        @csrf
                        @foreach($keep as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                        <input type="hidden" name="action" value="tick">
                        <table class="table table-bordered table-striped rep-table">
                            <thead><tr><th class="recon-pick rep-noprint"><input type="checkbox" id="tickAll" title="Choose all"></th><th>Date</th><th>What</th><th>Note</th><th class="num">In</th><th class="num">Out</th></tr></thead>
                            <tbody>
                                @foreach($open as $r)
                                    <tr>
                                        <td class="recon-pick rep-noprint"><input type="checkbox" class="tick-row" name="ids[]" value="{{ $r->inv_abs_id }}" data-v="{{ $r->in - $r->out }}" aria-label="Tick movement {{ $r->inv_abs_id }}"></td>
                                        <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->inv_abs_transaction_date)->format('d M Y') }}</td>
                                        <td>{{ $r->type_label }}@if($r->party)<br><small class="text-muted">{{ $r->party }}</small>@endif</td>
                                        <td>{{ \Illuminate\Support\Str::limit($r->inv_abs_description, 50) }}@if($r->inv_abs_voucher_no)<br><small class="text-muted">{{ $r->inv_abs_voucher_no }}</small>@endif</td>
                                        <td class="num rep-pos">{{ $r->in ? $money($r->in) : '' }}</td>
                                        <td class="num rep-neg">{{ $r->out ? $money($r->out) : '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="recon-bar">
                            <div><label for="cleared_date" class="small">Date on bank statement (optional)</label><input type="text" class="form-control datepicker" id="cleared_date" name="cleared_date" autocomplete="off" placeholder="same as ERP"></div>
                            <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Tick chosen (<span id="tickCount">0</span>)</button>
                            @if($bankBalance !== null)<span class="rep-note" style="margin: 0 0 6px;">Difference after ticking: <b id="tickPreview">{{ $money($difference) }}</b></span>@endif
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-check-square-o"></i> Ticked (latest {{ $ticked->count() }})</h4></div></div>
            <div class="panel-body">
                @if($ticked->isEmpty())
                    <p class="rep-empty">Nothing ticked yet.</p>
                @else
                    <form method="POST" action="{{ route('accounts.bank-reconciliation.mark') }}">
                        @csrf
                        @foreach($keep as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                        <input type="hidden" name="action" value="untick">
                        <table class="table table-bordered table-striped rep-table">
                            <thead><tr><th class="recon-pick rep-noprint"></th><th>Bank date</th><th>What</th><th>Note</th><th class="num">In</th><th class="num">Out</th></tr></thead>
                            <tbody>
                                @foreach($ticked as $r)
                                    <tr>
                                        <td class="recon-pick rep-noprint"><input type="checkbox" name="ids[]" value="{{ $r->inv_abs_id }}" aria-label="Untick movement {{ $r->inv_abs_id }}"></td>
                                        <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->cleared_date ?: $r->inv_abs_transaction_date)->format('d M Y') }}</td>
                                        <td>{{ $r->type_label }}@if($r->party)<br><small class="text-muted">{{ $r->party }}</small>@endif</td>
                                        <td>{{ \Illuminate\Support\Str::limit($r->inv_abs_description, 50) }}</td>
                                        <td class="num rep-pos">{{ $r->in ? $money($r->in) : '' }}</td>
                                        <td class="num rep-neg">{{ $r->out ? $money($r->out) : '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="recon-bar"><button type="submit" class="btn btn-default btn-sm"><i class="fa fa-undo"></i> Untick chosen</button></div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('custom_script')
<script>
(function ($) {
    var bank = {{ $bankBalance === null ? 'null' : $bankBalance }}, ticked = {{ $sum['ticked'] }};
    function update() {
        var add = 0, n = 0;
        $('.tick-row:checked').each(function () { add += parseFloat($(this).data('v')) || 0; n++; });
        $('#tickCount').text(n);
        if (bank !== null) {
            var d = Math.round((bank - ticked - add) * 100) / 100;
            $('#tickPreview').text(d.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
                .toggleClass('recon-diff-ok', d === 0).toggleClass('recon-diff-bad', d !== 0);
        }
    }
    $(document).on('change', '.tick-row', update);
    $('#tickAll').on('change', function () { $('.tick-row').prop('checked', this.checked); update(); });
    $('#tickForm').on('submit', function () { if (!$('.tick-row:checked').length) { alert('Tick the movements that are on the bank statement first.'); return false; } });
    update();
})(jQuery);
</script>
@endsection

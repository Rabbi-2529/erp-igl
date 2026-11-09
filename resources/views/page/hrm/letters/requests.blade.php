@extends('layouts.master')
@section('title', 'Letter Requests')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
<style>
    .rq-tabs { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 12px; }
    .rq-tabs .active { color: var(--erp-on-primary); background: var(--erp-green-btn); border-color: var(--erp-green-btn); }
    .rq-tabs form { margin-left: auto; display: flex; gap: 6px; flex: 0 1 340px; }
    .rq-tabs form input { flex: 1 1 auto; min-width: 0; }
    .rq-purpose { font-weight: 600; }
    .rq-meta { color: var(--erp-muted); font-size: 12px; }
    .rq-soon { color: #b3261e; font-weight: 700; }
    html[data-theme="dark"] .rq-soon { color: #f58b90; }
    .rq-decline { margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap; }
    .rq-decline input { flex: 1 1 160px; min-width: 140px; }
    .rq-actions { white-space: nowrap; }
    .rq-actions form { display: inline; margin: 0; }
</style>

<div class="lt-head">
    <div>
        <h3>Letter Requests</h3>
        <p class="lt-sub">Letters employees asked for from their My Letters page. Make the letter (it opens as a draft with their details) or decline with a reason.</p>
    </div>
</div>

@if(!empty($setup))
    <div class="alert alert-warning">
        <b><i class="fa fa-exclamation-triangle"></i> Letter requests are not switched on in this database yet.</b><br>
        Run the file <code>database/hrm_letter_requests.sql</code> on this database (for example in phpMyAdmin: choose the ERP database, then Import that file).
        It only adds two new tables, <code>hrm_letter_requests</code> and <code>hrm_letter_request_types</code>, and changes nothing else.
        Then reload this page: employees get a <b>Request a letter</b> button on My Letters, and their requests appear here.
    </div>
@else
<div class="rq-tabs">
    <a href="{{ route('hrm.letters.requests', ['show' => 'pending']) }}" class="btn btn-default btn-sm {{ $show === 'pending' ? 'active' : '' }}"><i class="fa fa-inbox"></i> Waiting ({{ $pending }})</a>
    <a href="{{ route('hrm.letters.requests', ['show' => 'all']) }}" class="btn btn-default btn-sm {{ $show === 'all' ? 'active' : '' }}"><i class="fa fa-list"></i> All requests</a>
    <form method="GET" action="{{ route('hrm.letters.requests') }}">
        <input type="hidden" name="show" value="{{ $show }}">
        <input type="text" class="form-control input-sm" name="q" value="{{ $term }}" placeholder="Employee, ID, purpose or reference" aria-label="Search requests">
        <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i></button>
    </form>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        @if($list->isEmpty())
            <p class="text-center text-muted" style="padding: 24px 0;">{{ $show === 'pending' ? 'No requests waiting. When an employee asks for a letter, it appears here.' : 'No requests found.' }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped lt-table">
                    <thead><tr><th>Asked on</th><th>Employee</th><th>Letter</th><th>For</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($list as $r)
                            @php
                                $stage = \App\Services\HrmLetterRequests::stage($r);
                                [$stageLabel, $stageClass] = \App\Services\HrmLetterRequests::SHOWN[$stage];
                                $needed = $r->hlq_needed_by ? \Carbon\Carbon::parse($r->hlq_needed_by) : null;
                                $soon = $needed && $stage === 'pending' && $needed->lte(\Carbon\Carbon::today()->addDays(2));
                            @endphp
                            <tr>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->hlq_created_at)->format('d M Y') }}
                                    @if($needed)<div class="rq-meta {{ $soon ? 'rq-soon' : '' }}">needed by {{ $needed->format('d M Y') }}</div>@endif</td>
                                <td>{{ $r->au_name }}@if($r->au_user_id)<br><small class="text-muted">{{ $r->au_user_id }}</small>@endif</td>
                                <td>{{ $r->hlt_name }}@unless($r->hlt_status)<br><small class="text-muted">letter type is off</small>@endunless</td>
                                <td><span class="rq-purpose">{{ $r->hlq_purpose }}</span>
                                    @if($r->hlq_addressed_to)<div class="rq-meta">To: {{ $r->hlq_addressed_to }}</div>@endif
                                    @if($r->hlq_note)<div class="rq-meta">{{ $r->hlq_note }}</div>@endif</td>
                                <td><span class="label {{ $stageClass }}">{{ $stageLabel }}</span>
                                    @if($r->hlq_letter_id && $r->hl_reference)<div class="rq-meta"><a href="{{ route('hrm.letters.show', $r->hlq_letter_id) }}" class="lt-prefix">{{ $r->hl_reference }}</a></div>@endif
                                    @if($stage === 'declined' && $r->hlq_answer)<div class="rq-meta">{{ $r->hlq_answer }}</div>@endif
                                    @if($r->handled_name && $stage !== 'pending')<div class="rq-meta">by {{ $r->handled_name }}, {{ \Carbon\Carbon::parse($r->hlq_handled_at)->format('d M Y') }}</div>@endif</td>
                                <td class="rq-actions">
                                    @if($stage === 'pending')
                                        <form method="POST" action="{{ route('hrm.letters.requests.make', $r->hlq_id) }}">@csrf<button type="submit" class="btn btn-success btn-xs"><i class="fa fa-file-text-o"></i> Make letter</button></form>
                                        <button type="button" class="btn btn-default btn-xs" data-toggle="collapse" data-target="#decline{{ $r->hlq_id }}" aria-expanded="false"><i class="fa fa-times"></i> Decline</button>
                                        <div id="decline{{ $r->hlq_id }}" class="collapse">
                                            <form method="POST" action="{{ route('hrm.letters.requests.decline', $r->hlq_id) }}" class="rq-decline">@csrf
                                                <input type="text" class="form-control input-sm" name="reason" maxlength="255" placeholder="Reason, shown to the employee" aria-label="Reason to decline" required>
                                                <button type="submit" class="btn btn-danger btn-xs">Decline</button>
                                            </form>
                                        </div>
                                    @elseif($r->hlq_letter_id)
                                        <a href="{{ route('hrm.letters.show', $r->hlq_letter_id) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Open letter</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($list->count() >= 500)<p class="lt-note">Showing the latest 500. Use the search to narrow down.</p>@endif
        @endif
    </div>
</div>
@endif
@endsection

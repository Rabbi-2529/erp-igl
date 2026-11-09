@extends('layouts.master')
@section('title', 'My Letters')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')
@section('my_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
<style>
    .rq-form .form-group { margin-bottom: 12px; }
    .rq-hint { color: var(--erp-muted); font-size: 12px; margin-top: 3px; }
    .rq-purpose { font-weight: 600; }
    .rq-meta { color: var(--erp-muted); font-size: 12px; }
    .rq-answer { font-size: 12px; margin-top: 3px; }
</style>
@php
    $canAsk = isset($requestTypes) && $requestTypes->isNotEmpty();
    $openForm = $canAsk && ($errors->any() || old('purpose'));
@endphp

<div class="lt-head">
    <div>
        <h3>My Letters</h3>
        <p class="lt-sub">Letters the company has issued to you: appointment, salary and experience certificates, promotions and more.</p>
    </div>
    @if($canAsk)
        <button type="button" class="btn btn-success" data-toggle="collapse" data-target="#rqForm" aria-expanded="{{ $openForm ? 'true' : 'false' }}" aria-controls="rqForm"><i class="fa fa-paper-plane"></i> Request a letter</button>
    @endif
</div>

@if($canAsk)
    <div id="rqForm" class="collapse {{ $openForm ? 'in' : '' }}">
        <form method="POST" action="{{ route('hrm.letters.requests.store') }}" class="panel panel-bd rq-form">
            @csrf
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-paper-plane"></i> Ask HR for a letter</h4></div></div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label for="rq_type">Letter</label>
                        <select class="form-control dont-select-me" id="rq_type" name="type_id" required>
                            <option value="">Choose a letter</option>
                            @foreach($requestTypes as $t)<option value="{{ $t->hlt_id }}" @selected((int) old('type_id') === (int) $t->hlt_id)>{{ $t->hlt_code === 'custom' ? 'Other letter (describe it below)' : $t->hlt_name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="rq_purpose">What is it for?</label>
                        <input type="text" class="form-control" id="rq_purpose" name="purpose" maxlength="255" value="{{ old('purpose') }}" placeholder="e.g. Bank loan, visa application, new job" required>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="rq_to">Addressed to <small class="text-muted">(optional)</small></label>
                        <input type="text" class="form-control" id="rq_to" name="addressed_to" maxlength="255" value="{{ old('addressed_to') }}" placeholder="e.g. The Manager, ABC Bank, Dhanmondi Branch">
                        <div class="rq-hint">Leave empty for "To whom it may concern".</div>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="rq_needed">Needed by <small class="text-muted">(optional)</small></label>
                        <input type="text" class="form-control datepicker" id="rq_needed" name="needed_by" value="{{ old('needed_by') }}" autocomplete="off" placeholder="yyyy-mm-dd">
                    </div>
                    <div class="col-sm-12 form-group">
                        <label for="rq_note">Anything else HR should know <small class="text-muted">(optional)</small></label>
                        <textarea class="form-control" id="rq_note" name="note" rows="2" maxlength="1000">{{ old('note') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> Send to HR</button>
                <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#rqForm">Close</button>
            </div>
        </form>
    </div>
@endif

@if(isset($requests) && $requests->isNotEmpty())
    <div class="panel panel-bd">
        <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-inbox"></i> My requests</h4></div></div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped lt-table">
                    <thead><tr><th>Asked on</th><th>Letter</th><th>For</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($requests as $r)
                            @php
                                $stage = \App\Services\HrmLetterRequests::stage($r);
                                [$stageLabel, $stageClass] = \App\Services\HrmLetterRequests::SHOWN[$stage];
                            @endphp
                            <tr>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->hlq_created_at)->format('d M Y') }}</td>
                                <td>{{ $r->hlt_name }}</td>
                                <td><span class="rq-purpose">{{ $r->hlq_purpose }}</span>
                                    @if($r->hlq_addressed_to || $r->hlq_needed_by)<div class="rq-meta">{{ $r->hlq_addressed_to }}{{ $r->hlq_addressed_to && $r->hlq_needed_by ? ' · ' : '' }}{{ $r->hlq_needed_by ? 'needed by ' . \Carbon\Carbon::parse($r->hlq_needed_by)->format('d M Y') : '' }}</div>@endif</td>
                                <td><span class="label {{ $stageClass }}">{{ $stageLabel }}</span>
                                    @if($stage === 'declined' && $r->hlq_answer)<div class="rq-answer text-muted">{{ $r->hlq_answer }}</div>@endif
                                    @if($stage === 'ready')<div class="rq-answer"><span class="lt-prefix">{{ $r->hl_reference }}</span></div>@endif</td>
                                <td style="white-space: nowrap;">
                                    @if($stage === 'pending')
                                        <form method="POST" action="{{ route('hrm.letters.requests.withdraw', $r->hlq_id) }}" style="display: inline; margin: 0;" onsubmit="return confirm('Withdraw this request?');">@csrf<button type="submit" class="btn btn-default btn-xs"><i class="fa fa-undo"></i> Withdraw</button></form>
                                    @elseif($stage === 'ready')
                                        <a href="{{ route('hrm.letters.show', $r->hlq_letter_id) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Open</a>
                                        <a href="{{ route('hrm.letters.print', $r->hlq_letter_id) }}" class="btn btn-default btn-xs" target="_blank" rel="noopener"><i class="fa fa-print"></i> Print</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="panel panel-bd">
    @if(isset($requests) && $requests->isNotEmpty())<div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-envelope-o"></i> My letters</h4></div></div>@endif
    <div class="panel-body">
        @if($list->isEmpty())
            <p class="text-center text-muted" style="padding: 24px 0;">No letters for you yet. When HR issues a letter to you, it appears here.@if($canAsk) Need a certificate? Use <b>Request a letter</b>.@endif</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped lt-table">
                    <thead><tr><th>Reference</th><th>Date</th><th>Letter</th><th>Subject</th><th></th></tr></thead>
                    <tbody>
                        @foreach($list as $r)
                            <tr>
                                <td style="white-space: nowrap;"><span class="lt-prefix">{{ $r->hl_reference }}</span></td>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->hl_letter_date)->format('d M Y') }}</td>
                                <td>{{ $r->hlt_name }}</td>
                                <td>{{ $r->hl_subject }}</td>
                                <td style="white-space: nowrap;"><a href="{{ route('hrm.letters.show', $r->hl_id) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Open</a>
                                    <a href="{{ route('hrm.letters.print', $r->hl_id) }}" class="btn btn-default btn-xs" target="_blank" rel="noopener"><i class="fa fa-print"></i> Print</a>
                                    <form method="POST" action="{{ route('hrm.letters.pdf', $r->hl_id) }}" style="display: inline; margin: 0;">@csrf<button type="submit" class="btn btn-default btn-xs"><i class="fa fa-file-pdf-o"></i> PDF</button></form></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

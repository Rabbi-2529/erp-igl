@extends('layouts.master')
@section('title', 'HR Letters')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
@php
    $statuses = \App\Services\HrmLetterFlow::STATUS;
    $keep = request()->except(['scope', 'page']);
@endphp

<div class="lt-head">
    <div>
        <h3>HR Letters</h3>
        <p class="lt-sub">Every letter made, with its reference number. Search by reference, name or subject, or filter.</p>
    </div>
    @if($canCreate)
        <a href="{{ route('hrm.letters.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> Generate letter</a>
    @endif
</div>

<div class="lt-links" style="margin-bottom: 10px;">
    <a href="{{ route('hrm.letters.index', $keep) }}" class="btn btn-default btn-sm {{ $scope === 'all' ? 'active' : '' }}">All letters</a>
    <a href="{{ route('hrm.letters.index', $keep + ['scope' => 'waiting']) }}" class="btn btn-default btn-sm {{ $scope === 'waiting' ? 'active' : '' }}">Waiting for me @if($counts['waiting'])<span class="badge">{{ $counts['waiting'] }}</span>@endif</a>
    @if($full)<a href="{{ route('hrm.letters.index', $keep + ['scope' => 'drafts']) }}" class="btn btn-default btn-sm {{ $scope === 'drafts' ? 'active' : '' }}">Drafts @if($counts['drafts'])<span class="badge">{{ $counts['drafts'] }}</span>@endif</a>@endif
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; margin-bottom: 12px;">
            <input type="hidden" name="scope" value="{{ $scope }}">
            <div style="flex: 1 1 200px;"><label for="q" style="display: block; font-size: 12px;">Search</label><input type="text" class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Reference, name, ID or subject"></div>
            <div style="flex: 1 1 200px;">
                <label for="employee" style="display: block; font-size: 12px;">Employee</label>
                <select class="form-control" id="employee" name="employee" style="width: 100%;">
                    <option value="">Everyone</option>
                    @foreach($employees as $e)<option value="{{ $e->au_id }}" @selected((int) request('employee') === (int) $e->au_id)>{{ $e->au_name }}{{ $e->au_user_id ? ' (' . $e->au_user_id . ')' : '' }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="department" style="display: block; font-size: 12px;">Department</label>
                <select class="form-control dont-select-me" id="department" name="department">
                    <option value="">All</option>
                    @foreach($departments as $id => $name)<option value="{{ $id }}" @selected((int) request('department') === (int) $id)>{{ $name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="type" style="display: block; font-size: 12px;">Letter type</label>
                <select class="form-control dont-select-me" id="type" name="type" style="max-width: 200px;">
                    <option value="">All</option>
                    @foreach($types as $t)<option value="{{ $t->hlt_id }}" @selected((int) request('type') === (int) $t->hlt_id)>{{ $t->hlt_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="status" style="display: block; font-size: 12px;">Status</label>
                <select class="form-control dont-select-me" id="status" name="status">
                    <option value="">All</option>
                    @foreach($statuses as $k => [$label, $cls])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div><label for="from" style="display: block; font-size: 12px;">From</label><input type="text" class="form-control datepicker" id="from" name="from" value="{{ request('from') }}" autocomplete="off" style="width: 120px;"></div>
            <div><label for="to" style="display: block; font-size: 12px;">To</label><input type="text" class="form-control datepicker" id="to" name="to" value="{{ request('to') }}" autocomplete="off" style="width: 120px;"></div>
            <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Show</button>
            <a href="{{ route('hrm.letters.index', ['scope' => $scope]) }}" class="btn btn-default">Clear</a>
        </form>

        @if($list->isEmpty())
            <p class="text-center text-muted" style="padding: 24px 0;">No letters found.@if($canCreate) <a href="{{ route('hrm.letters.create') }}">Generate a letter</a>.@endif</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped lt-table">
                    <thead><tr><th>Reference</th><th>Date</th><th>Employee</th><th>Letter</th><th>Subject</th><th>Status</th><th>Made by</th><th></th></tr></thead>
                    <tbody>
                        @foreach($list as $r)
                            @php
                                [$label, $cls] = $statuses[$r->hl_status] ?? [$r->hl_status, 'label-default'];
                                $snap = json_decode((string) $r->hl_employee, true) ?: [];
                                $wait = $r->hl_status === 'submitted' ? ($r->hlt_needs_review && $r->au_dep_head_id ? 'department review' : 'approval') : ($r->hl_status === 'reviewed' ? 'approval' : null);
                            @endphp
                            <tr>
                                <td style="white-space: nowrap;"><a href="{{ route('hrm.letters.show', $r->hl_id) }}" class="lt-prefix">{{ $r->hl_reference }}</a></td>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->hl_letter_date)->format('d M Y') }}</td>
                                <td>{{ ($snap['name'] ?? '') ?: $r->au_name }}@if($r->au_user_id)<br><small class="text-muted">{{ $r->au_user_id }}{{ !empty($snap['designation']) ? ' · ' . $snap['designation'] : '' }}</small>@endif</td>
                                <td>{{ $r->hlt_name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit(strip_tags((string) $r->hl_subject), 60) }}</td>
                                <td><span class="label {{ $cls }}">{{ $label }}</span>@if($wait)<br><small class="text-muted">waiting for {{ $wait }}</small>@endif</td>
                                <td>{{ $r->created_name ?: '-' }}<br><small class="text-muted">{{ \Carbon\Carbon::parse($r->hl_created_at)->format('d M Y') }}</small></td>
                                <td style="white-space: nowrap;">@if(in_array($r->hl_status, ['approved', 'issued'], true))<a href="{{ route('hrm.letters.print', $r->hl_id) }}" class="btn btn-default btn-xs" target="_blank" rel="noopener" title="Print preview"><i class="fa fa-print"></i> Print</a> <form method="POST" action="{{ route('hrm.letters.pdf', $r->hl_id) }}" style="display: inline; margin: 0;">@csrf<button type="submit" class="btn btn-default btn-xs" title="Download PDF"><i class="fa fa-file-pdf-o"></i> PDF</button></form>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($list->count() >= 500)<p class="lt-note">Showing the latest 500. Use the filters to narrow down.</p>@endif
        @endif
    </div>
</div>
@endsection

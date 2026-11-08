@extends('layouts.master')
@section('title', 'Letter Templates')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')

<div class="lt-head">
    <div>
        <h3>Letter Templates</h3>
        <p class="lt-sub">The text of each letter, with placeholders like <code>&#123;&#123;employee_name&#125;&#125;</code> that fill in from the employee and the letter. Each letter type has a default template.</p>
    </div>
    <form method="GET" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">
        <div>
            <label for="type" style="display: block; font-size: 12px; margin-bottom: 3px;">Letter type</label>
            <select class="form-control dont-select-me" id="type" name="type" onchange="this.form.submit()" style="min-width: 220px;">
                <option value="">All letter types</option>
                @foreach($types as $t)<option value="{{ $t->hlt_id }}" @selected($typeId === (int) $t->hlt_id)>{{ $t->hlt_name }}</option>@endforeach
            </select>
        </div>
        @if($isAdmin)
            <a href="{{ route('hrm.letters.templates.create', ['type' => $typeId ?: null]) }}" class="btn btn-success"><i class="fa fa-plus"></i> New template</a>
        @endif
    </form>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped lt-table">
                <thead><tr><th>Letter type</th><th>Template</th><th>Subject</th><th>Language</th><th class="text-right">Letters</th><th>Last change</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($list as $t)
                        <tr>
                            <td><b>{{ $t->hlt_name }}</b> <span class="lt-prefix text-muted">{{ $t->hlt_prefix }}</span>@unless($t->hlt_status)<br><small class="text-muted">letter type is off</small>@endunless</td>
                            <td>{{ $t->hltp_name }}@if($t->hltp_is_default) <span class="lt-tag on">default</span>@endif</td>
                            <td>{{ $t->hltp_subject }}</td>
                            <td>{{ $t->hltp_lang === 'bn' ? 'Bangla' : 'English' }}</td>
                            <td class="text-right">{{ $used[$t->hltp_id] ?? 0 }}</td>
                            <td>{{ $t->au_name ?: '-' }}<br><small class="text-muted">{{ \Carbon\Carbon::parse($t->hltp_updated_at ?: $t->hltp_created_at)->format('d M Y') }}</small></td>
                            <td>@if($t->hltp_status)<span class="label label-success">Active</span>@else<span class="label label-default">Off</span>@endif</td>
                            <td style="white-space: nowrap;">
                                <a href="{{ route('hrm.letters.templates.edit', $t->hltp_id) }}" class="btn btn-default btn-xs"><i class="fa {{ $isAdmin ? 'fa-pencil' : 'fa-eye' }}"></i> {{ $isAdmin ? 'Edit' : 'View' }}</a>
                                @if($isAdmin)
                                    @unless($t->hltp_is_default)
                                        <form method="POST" action="{{ route('hrm.letters.templates.default', $t->hltp_id) }}" style="display: inline;">@csrf<button type="submit" class="btn btn-default btn-xs" title="Use for new letters of this type"><i class="fa fa-star-o"></i> Make default</button></form>
                                    @endunless
                                    <form method="POST" action="{{ route('hrm.letters.templates.duplicate', $t->hltp_id) }}" style="display: inline;">@csrf<button type="submit" class="btn btn-default btn-xs"><i class="fa fa-copy"></i> Copy</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted" style="padding: 20px;">No templates for this letter type yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="lt-note">
            Every standard letter type starts with a ready template. A letter copies the text of its template when it is made, so editing a template never changes letters already made.
            @unless($isAdmin) Only an HR Admin can change templates. @endunless
        </p>
    </div>
</div>
@endsection

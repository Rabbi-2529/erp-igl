@extends('layouts.master')
@section('title', 'Letter Access')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
@php
    $roleNames = \App\Services\HrmLetters::ROLES;
@endphp

<div class="lt-head">
    <div>
        <h3>Access</h3>
        <p class="lt-sub">Who can do what with HR letters. Your existing ERP permissions are not changed; these roles only apply to letters.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-user-secret"></i> HR Admins and HR Executives</h4></div></div>
            <div class="panel-body">
                @if($isAdmin)
                    <form method="POST" action="{{ route('hrm.letters.access.add') }}" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; margin-bottom: 14px;">
                        @csrf
                        <div style="flex: 1 1 240px;">
                            <label for="user_id">Employee</label>
                            <select class="form-control" id="user_id" name="user_id" required style="width: 100%;">
                                <option value="">Choose an employee</option>
                                @foreach($employees as $e)<option value="{{ $e->au_id }}">{{ $e->au_name }}{{ $e->au_user_id ? ' (' . $e->au_user_id . ')' : '' }}{{ $e->au_branch_name ? ' - ' . $e->au_branch_name : '' }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="role">Role</label>
                            <select class="form-control dont-select-me" id="role" name="role">
                                @foreach($roleNames as $k => $v)<option value="{{ $k }}" @selected($k === 'hr_executive')>{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add</button>
                    </form>
                @endif
                @if($roles->isEmpty())
                    <p class="text-muted">No one added yet. Company admins are always HR Admins.</p>
                @else
                    <table class="table table-bordered table-striped lt-table">
                        <thead><tr><th>Employee</th><th>Role</th><th>Added by</th>@if($isAdmin)<th></th>@endif</tr></thead>
                        <tbody>
                            @foreach($roles as $r)
                                <tr>
                                    <td>{{ $r->au_name }}@if($r->au_branch_name)<br><small class="text-muted">{{ $r->au_branch_name }}</small>@endif</td>
                                    <td>{{ $roleNames[$r->hlr_role] ?? $r->hlr_role }}</td>
                                    <td>{{ $r->added_by ?: '-' }}<br><small class="text-muted">{{ \Carbon\Carbon::parse($r->hlr_created_at)->format('d M Y') }}</small></td>
                                    @if($isAdmin)
                                        <td><form method="POST" action="{{ route('hrm.letters.access.remove', $r->hlr_id) }}" onsubmit="return confirm('Remove {{ addslashes($r->au_name) }} from {{ $roleNames[$r->hlr_role] ?? '' }}?');">@csrf
                                            <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-times"></i> Remove</button></form></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-info-circle"></i> What each role does</h4></div></div>
            <div class="panel-body">
                <table class="table table-bordered lt-table">
                    <tbody>
                        <tr><td><b>HR Admin</b></td><td>Manages all letters, templates, letter types, letterhead, signatories and access. Can approve.</td></tr>
                        <tr><td><b>HR Executive</b></td><td>Makes letters, edits drafts and sends them for approval.</td></tr>
                        <tr><td><b>Department Head</b></td><td>Reviews letters of their own team when the letter type asks for review.</td></tr>
                        <tr><td><b>Approver</b></td><td>Approves or rejects letters (a signatory marked "Can approve").</td></tr>
                        <tr><td><b>Employee</b></td><td>Sees their own issued letters.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-users"></i> Who has a role automatically</h4></div></div>
            <div class="panel-body">
                <p><b>HR Admin</b> (company admins):<br>@forelse($admins as $a)<span class="lt-tag on">{{ $a->au_name }}</span>@empty<span class="text-muted">none</span>@endforelse</p>
                <p><b>Approvers</b> (signatories who can approve):<br>@forelse($approvers as $a)<span class="lt-tag on">{{ $a->au_name }} &middot; {{ $a->hls_designation }}</span>@empty<span class="text-muted">none yet: mark a signatory "Can approve" on <a href="{{ route('hrm.letters.signatories') }}">Signatories</a></span>@endforelse</p>
                <p style="margin-bottom: 0;"><b>Department heads</b> (from each employee's record):<br>@forelse($heads as $d)<span class="lt-tag">{{ $d->au_name }} &middot; {{ $d->n }} employees</span>@empty<span class="text-muted">no department heads set on employee records</span>@endforelse</p>
            </div>
        </div>
    </div>
</div>
@endsection

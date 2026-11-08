@extends('layouts.master')
@section('title', 'Generate Letter')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
@php
    $tplData = [];
    foreach ($templates as $typeId => $list) {
        $tplData[$typeId] = $list->map(function ($t) { return ['id' => $t->hltp_id, 'name' => $t->hltp_name . ($t->hltp_lang === 'bn' ? ' (Bangla)' : '') . ($t->hltp_is_default ? ' - default' : '')]; })->values();
    }
    $typeInfo = $types->mapWithKeys(function ($t) {
        return [$t->hlt_id => ['prefix' => $t->hlt_prefix, 'review' => (bool) $t->hlt_needs_review,
            'asks' => array_map(function ($f) { return \App\Services\HrmLetters::FIELDS[$f][0]; }, $t->field_list)]];
    });
@endphp

<div class="lt-head">
    <div>
        <h3>Generate Letter</h3>
        <p class="lt-sub">Choose the employee and the letter. The reference number is given and the employee's details are filled in at once; you check them on the next page.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('hrm.letters.store') }}" class="panel panel-bd">
            @csrf
            <div class="panel-body">
                <div class="form-group">
                    <label for="employee_id">Employee</label>
                    <select class="form-control" id="employee_id" name="employee_id" required style="width: 100%;">
                        <option value="">Choose an employee</option>
                        @foreach($employees as $e)<option value="{{ $e->au_id }}" @selected((int) old('employee_id', $preset['employee']) === (int) $e->au_id)>{{ $e->au_name }}{{ $e->au_user_id ? ' (' . $e->au_user_id . ')' : '' }}{{ $e->au_branch_name ? ' - ' . $e->au_branch_name : '' }}</option>@endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-sm-7 form-group">
                        <label for="type_id">Letter type</label>
                        <select class="form-control" id="type_id" name="type_id" required style="width: 100%;">
                            <option value="">Choose a letter type</option>
                            @foreach($types as $t)<option value="{{ $t->hlt_id }}" @selected((int) old('type_id', $preset['type']) === (int) $t->hlt_id)>{{ $t->hlt_name }} ({{ $t->hlt_prefix }})</option>@endforeach
                        </select>
                    </div>
                    <div class="col-sm-5 form-group">
                        <label for="template_id">Template</label>
                        <select class="form-control dont-select-me" id="template_id" name="template_id"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5 form-group"><label for="letter_date">Letter date</label><input type="text" class="form-control datepicker" id="letter_date" name="letter_date" value="{{ old('letter_date', now()->toDateString()) }}" autocomplete="off" required></div>
                    <div class="col-sm-7 form-group">
                        <label for="signatory_id">Signed by</label>
                        <select class="form-control dont-select-me" id="signatory_id" name="signatory_id">
                            <option value="">The default signatory</option>
                            @foreach($signatories as $s)<option value="{{ $s->hls_id }}" @selected(old('signatory_id') == $s->hls_id)>{{ $s->hls_name }} - {{ $s->hls_designation }}{{ $s->hls_is_default ? ' (default)' : '' }}</option>@endforeach
                        </select>
                    </div>
                </div>
                @if($signatories->isEmpty())
                    <p class="lt-note" style="margin-top: 0;"><i class="fa fa-info-circle"></i> No signatory yet: add one on <a href="{{ route('hrm.letters.signatories') }}">Signatories</a> before the letter is sent for approval.</p>
                @endif
                <button type="submit" class="btn btn-success"><i class="fa fa-file-text-o"></i> Create draft</button>
            </div>
        </form>
    </div>
    <div class="col-lg-5">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-info-circle"></i> This letter</h4></div></div>
            <div class="panel-body" id="typeInfo">
                <p class="text-muted" style="margin: 0;">Choose a letter type to see what it needs.</p>
            </div>
        </div>
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-random"></i> How a letter goes</h4></div></div>
            <div class="panel-body">
                <ol style="padding-left: 18px; margin: 0;">
                    <li><b>Draft:</b> HR checks the details and edits the text.</li>
                    <li><b>Sent for approval</b> (some letters are reviewed by the employee's department head first).</li>
                    <li><b>Approved:</b> the letter is fixed for good and can be printed.</li>
                </ol>
                <p class="lt-note">The reference number is given when the draft is made, and is never used again, even if the letter is cancelled.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_script')
<script>
(function ($) {
    var templates = {!! json_encode($tplData) !!}, info = {!! json_encode($typeInfo) !!};
    var today = {!! json_encode(now()->format('Ymd')) !!}, oldTpl = {!! json_encode(old('template_id')) !!};
    function update() {
        var t = $('#type_id').val(), sel = $('#template_id').empty();
        (templates[t] || []).forEach(function (x) { sel.append($('<option>').val(x.id).text(x.name)); });
        if (!sel.children().length) { sel.append($('<option>').val('').text(t ? 'No active template' : 'Choose a letter type first')); }
        if (oldTpl) { sel.val(oldTpl); oldTpl = null; }
        var i = info[t];
        if (!i) { $('#typeInfo').html('<p class="text-muted" style="margin: 0;">Choose a letter type to see what it needs.</p>'); return; }
        var html = '<p>Reference: <span class="lt-prefix">' + i.prefix + today + 'XXX</span></p>';
        html += '<p style="margin-bottom: 4px;"><b>You will fill in:</b></p>';
        html += i.asks.length ? '<p>' + i.asks.map(function (a) { return '<span class="lt-tag">' + $('<i>').text(a).html() + '</span>'; }).join(' ') + '</p>' : '<p class="text-muted">Nothing extra: the employee\'s details are enough.</p>';
        if (i.review) { html += '<p class="lt-note"><i class="fa fa-user"></i> The employee\'s department head reviews this letter before approval.</p>'; }
        $('#typeInfo').html(html);
    }
    $('#type_id').on('change', update);
    update();
})(jQuery);
</script>
@endsection

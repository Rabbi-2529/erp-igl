@extends('layouts.master')
@section('title', $l->hl_reference)
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
@php
    [$statusText, $statusClass] = \App\Services\HrmLetterFlow::STATUS[$l->hl_status] ?? [$l->hl_status, 'label-default'];
    $emp = $l->employee;
    $fieldsDef = \App\Services\HrmLetters::FIELDS;
    $C = \App\Services\HrmLetterContent::class;
    $old = function ($key, $value) { return old($key, $value); };
@endphp
@if($can['edit'])<link href="{{ asset('assets/plugins/summernote/summernote.css') }}" rel="stylesheet" type="text/css">@endif
<style>
    .lt-ref { font-family: Consolas, monospace; font-weight: 700; letter-spacing: .5px; }
    .lt-preview-wrap { overflow: hidden; padding: 14px; background: #d9dedb; border-radius: 6px; }
    html[data-theme="dark"] .lt-preview-wrap { background: #3a403d; }
    .lt-scale { transform-origin: top left; }
    .lt-kv td { padding: 5px 8px !important; }
    .lt-kv td:first-child { color: var(--erp-muted); width: 38%; }
    .lt-missing { margin: 8px 0 0; font-size: 13px; }
    .lt-missing mark { background: #fff1a8; color: #7a4f00; padding: 0 3px; border-radius: 2px; }
    .lt-actions form { margin-bottom: 10px; }
    .lt-sticky { position: sticky; top: 70px; }
    .lt-head-btns { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .note-editor .note-editable { background: #ffffff; color: #1a1a1a; font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; }
    .note-editor .note-editable blockquote { margin: 6px 0 10px 18px; padding: 4px 12px; border-left: 3px solid #c8c8c8; font-size: 14px; }
</style>

<div class="lt-head">
    <div>
        <h3><span class="lt-ref">{{ $l->hl_reference }}</span> <span class="label {{ $statusClass }}" style="font-size: 12px; vertical-align: middle;">{{ $statusText }}</span></h3>
        <p class="lt-sub">{{ $l->hlt_name }} for <b>{{ $emp->name ?? '' }}</b>{{ !empty($emp->employee_id) ? ' (' . $emp->employee_id . ')' : '' }} &middot; made by {{ $l->created_name ?: '-' }} on {{ \Carbon\Carbon::parse($l->hl_created_at)->format('d M Y, h:i A') }}</p>
    </div>
    <div class="lt-head-btns">
        @if(\App\Services\HrmLetterFlow::printable($l))
            <a href="{{ route('hrm.letters.print', $l->hl_id) }}" class="btn btn-success" target="_blank" rel="noopener"><i class="fa fa-print"></i> Print preview</a>
            <form method="POST" action="{{ route('hrm.letters.pdf', $l->hl_id) }}" style="display: inline; margin: 0;">@csrf<button type="submit" class="btn btn-default"><i class="fa fa-file-pdf-o"></i> Download PDF</button></form>
        @elseif($l->hl_status !== 'cancelled')
            <a href="{{ route('hrm.letters.print', $l->hl_id) }}" class="btn btn-default" target="_blank" rel="noopener" title="Printing opens after approval"><i class="fa fa-file-text-o"></i> Page preview</a>
        @endif
        @if(\App\Services\HrmLetters::forUser()->isHrExecutive() || \App\Services\HrmLetters::forUser()->canApprove())
            <a href="{{ route('hrm.letters.index') }}" class="btn btn-default"><i class="fa fa-list"></i> All letters</a>
        @else
            <a href="{{ route('hrm.letters.my') }}" class="btn btn-default"><i class="fa fa-list"></i> My letters</a>
        @endif
    </div>
</div>

@if(!empty($asked) && $can['history'])
    <div class="alert alert-info"><i class="fa fa-inbox"></i> <b>Asked for by the employee</b> on {{ \Carbon\Carbon::parse($asked->hlq_created_at)->format('d M Y') }}.
        For: <b>{{ $asked->hlq_purpose }}</b>@if($asked->hlq_addressed_to). Addressed to: <b>{{ $asked->hlq_addressed_to }}</b>@endif @if($asked->hlq_needed_by). Needed by <b>{{ \Carbon\Carbon::parse($asked->hlq_needed_by)->format('d M Y') }}</b>@endif.
        @if($asked->hlq_note)<br>{{ $asked->hlq_note }}@endif
        @if($l->hl_status === 'draft' && $asked->hlq_addressed_to)<br><small>Change "To whom it may concern" in the letter text if it should be addressed to them.</small>@endif</div>
@endif
@if($l->hl_status === 'draft' && $l->hl_note)
    <div class="alert alert-warning"><b>Sent back:</b> {{ $l->hl_note }}</div>
@elseif($l->hl_status === 'rejected')
    <div class="alert alert-danger"><b>Rejected:</b> {{ $l->hl_note }}</div>
@elseif($l->hl_status === 'cancelled')
    <div class="alert alert-danger"><b>Cancelled:</b> {{ $l->hl_note }}. The reference number stays used.</div>
@elseif($can['awaiting'] === 'review')
    <div class="alert alert-info">Waiting for the department head's review{{ $deptHead ? ': ' . $deptHead : '' }}.</div>
@elseif($can['awaiting'] === 'approval')
    <div class="alert alert-info">Waiting for approval.</div>
@elseif(in_array($l->hl_status, ['approved', 'issued'], true))
    <div class="alert alert-success"><b>Approved</b> on {{ \Carbon\Carbon::parse($l->hl_approved_at)->format('d M Y, h:i A') }}. The text, employee details, letterhead and signatory are fixed and will not change.
        @if($l->hl_status === 'issued' && $l->hl_issued_at)<br><b>Issued</b> on {{ \Carbon\Carbon::parse($l->hl_issued_at)->format('d M Y, h:i A') }}.@else<br>It becomes <b>issued</b> when HR prints it or downloads the PDF.@endif
        Printed {{ (int) $l->hl_print_count }} {{ (int) $l->hl_print_count === 1 ? 'time' : 'times' }}, PDF downloaded {{ (int) $l->hl_download_count }} {{ (int) $l->hl_download_count === 1 ? 'time' : 'times' }}.</div>
@endif

<div class="row">
    <div class="col-lg-6">
        @if($can['edit'])
            <form method="POST" action="{{ route('hrm.letters.update', $l->hl_id) }}" id="draftForm" class="panel panel-bd">
                @csrf
                <input type="hidden" name="then" value="save" id="draftThen">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-pencil"></i> Letter details</h4></div></div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-5 form-group"><label for="letter_date">Letter date</label><input type="text" class="form-control datepicker lt-live" id="letter_date" name="letter_date" value="{{ $old('letter_date', $l->hl_letter_date) }}" autocomplete="off" required></div>
                        <div class="col-sm-7 form-group">
                            <label for="signatory_id">Signed by</label>
                            <select class="form-control dont-select-me lt-live" id="signatory_id" name="signatory_id">
                                <option value="">Choose a signatory</option>
                                @foreach($signatories as $s)<option value="{{ $s->hls_id }}" @selected((int) $old('signatory_id', $l->hl_signatory_id) === (int) $s->hls_id)>{{ $s->hls_name }} - {{ $s->hls_designation }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <h5 style="margin: 4px 0 8px; font-weight: 700;">Employee on this letter</h5>
                    <div class="row">
                        <div class="col-sm-6 form-group"><label>Name</label><input type="text" class="form-control" value="{{ $emp->name ?? '' }}" disabled></div>
                        <div class="col-sm-6 form-group"><label>Employee ID</label><input type="text" class="form-control" value="{{ $emp->employee_id ?? '' }}" disabled></div>
                        <div class="col-sm-6 form-group"><label for="e_designation">Designation</label><input type="text" class="form-control lt-live" id="e_designation" name="employee[designation]" maxlength="100" value="{{ $old('employee.designation', $emp->designation ?? '') }}" placeholder="Type the designation"></div>
                        <div class="col-sm-6 form-group"><label for="e_department">Department</label><input type="text" class="form-control lt-live" id="e_department" name="employee[department]" maxlength="150" value="{{ $old('employee.department', $emp->department ?? '') }}"></div>
                        <div class="col-sm-6 form-group"><label for="e_joining">Joining date</label><input type="text" class="form-control datepicker lt-live" id="e_joining" name="employee[joining_date]" value="{{ $old('employee.joining_date', $emp->joining_date ?? '') }}" autocomplete="off"></div>
                        <div class="col-sm-6 form-group"><label for="e_salary">Monthly salary</label><input type="number" class="form-control lt-live" id="e_salary" name="employee[salary]" min="0" step="0.01" value="{{ $old('employee.salary', $emp->salary ?? '') }}"></div>
                    </div>
                    @if($l->field_list)
                        <h5 style="margin: 4px 0 8px; font-weight: 700;">{{ $l->hlt_name }} details</h5>
                        <div class="row">
                            @foreach($l->field_list as $k)
                                @php
                                    [$label, $kind] = $fieldsDef[$k];
                                @endphp
                                <div class="{{ $kind === 'textarea' ? 'col-sm-12' : 'col-sm-6' }} form-group">
                                    <label for="f_{{ $k }}">{{ $label }}</label>
                                    @if($kind === 'textarea')
                                        <textarea class="form-control lt-live" id="f_{{ $k }}" name="fields[{{ $k }}]" rows="3" maxlength="2000">{{ $old('fields.' . $k, $l->fields[$k] ?? '') }}</textarea>
                                    @else
                                        <input type="{{ $kind === 'number' ? 'number' : 'text' }}" class="form-control lt-live {{ $kind === 'date' ? 'datepicker' : '' }}" id="f_{{ $k }}" name="fields[{{ $k }}]"
                                            value="{{ $old('fields.' . $k, $l->fields[$k] ?? '') }}" @if($kind === 'number') min="0" step="0.01" @endif @if($kind === 'date') autocomplete="off" @endif>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="form-group"><label for="subject">Subject</label><input type="text" class="form-control lt-live" id="subject" name="subject" maxlength="255" value="{{ $old('subject', $l->hl_subject) }}"></div>
                    <div class="form-group">
                        <label for="body" style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span>Letter text</span>
                            <select class="form-control dont-select-me input-sm" id="phInsert" style="width: auto; height: 28px; font-weight: 400;" aria-label="Insert a placeholder">
                                <option value="">Insert a placeholder...</option>
                                @foreach($placeholders as $group => $list)
                                    <optgroup label="{{ $group }}">@foreach($list as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach</optgroup>
                                @endforeach
                                <optgroup label="Letter details">@foreach($fieldsDef as $k => [$label, $kind])<option value="{{ $k }}">{{ $label }}</option>@endforeach</optgroup>
                            </select>
                        </label>
                        <textarea id="body" name="body" class="form-control" rows="14">{{ $old('body', $l->hl_body) }}</textarea>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="submit" class="btn btn-default" data-then="save"><i class="fa fa-save"></i> Save draft</button>
                        <button type="submit" class="btn btn-success" data-then="submit"><i class="fa fa-paper-plane"></i> Save and send for approval</button>
                    </div>
                    <p class="lt-note">Placeholders in the text fill in from the details above; the preview shows the result. They are fixed into the letter when it is approved.</p>
                </div>
            </form>
            <form method="POST" action="{{ route('hrm.letters.refill', $l->hl_id) }}" onsubmit="return confirm('Replace the letter text with the template text? Your edits to the text are lost; the details stay.');" style="margin: -10px 0 16px;">
                @csrf
                <button type="submit" class="btn btn-link btn-sm" style="padding-left: 0;"><i class="fa fa-undo"></i> Take the text from the template again</button>
            </form>
        @else
            <div class="panel panel-bd">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-id-card-o"></i> Letter details</h4></div></div>
                <div class="panel-body">
                    <table class="table table-bordered lt-kv" style="margin-bottom: 0;">
                        <tbody>
                            <tr><td>Letter</td><td>{{ $l->hlt_name }}</td></tr>
                            <tr><td>Letter date</td><td>{{ \Carbon\Carbon::parse($l->hl_letter_date)->format('d F Y') }}</td></tr>
                            <tr><td>Employee</td><td>{{ $emp->name ?? '' }}{{ !empty($emp->employee_id) ? ' (' . $emp->employee_id . ')' : '' }}</td></tr>
                            @foreach(\App\Services\HrmLetterFlow::EDITABLE_EMPLOYEE as $k => $label)
                                <tr><td>{{ $label }}</td><td>{{ $k === 'joining_date' ? ($emp->$k ? $C::date($emp->$k) : '') : ($k === 'salary' ? ($emp->$k ? 'BDT ' . $C::money($emp->$k) : '') : ($emp->$k ?? '')) }}</td></tr>
                            @endforeach
                            @foreach($l->field_list as $k)
                                <tr><td>{{ $fieldsDef[$k][0] }}</td><td>{{ ($l->fields[$k] ?? '') === '' ? '' : (in_array($k, $C::DATE_FIELDS, true) ? $C::date($l->fields[$k]) : (in_array($k, $C::MONEY_FIELDS, true) ? 'BDT ' . $C::money($l->fields[$k]) : $l->fields[$k])) }}</td></tr>
                            @endforeach
                            <tr><td>Signed by</td><td>{{ $letter->signatory->name ?? '' }}{{ !empty($letter->signatory->designation) ? ', ' . $letter->signatory->designation : '' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($can['review'] || $can['decide'] || $can['revise'] || $can['cancel'])
            <div class="panel panel-bd lt-actions">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-check-square-o"></i> Your action</h4></div></div>
                <div class="panel-body">
                    @if($can['review'])
                        @if($can['headReview'])<p class="lt-note" style="margin-top: 0;">This letter waits for the department head. As HR Admin you can act for them.</p>@endif
                        <form method="POST" action="{{ route('hrm.letters.review', $l->hl_id) }}">@csrf<input type="hidden" name="action" value="forward">
                            <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Reviewed: send for approval</button></form>
                        <form method="POST" action="{{ route('hrm.letters.review', $l->hl_id) }}" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">@csrf<input type="hidden" name="action" value="back">
                            <div style="flex: 1 1 220px;"><label for="back_note">What has to change</label><input type="text" class="form-control" id="back_note" name="note" maxlength="255" required></div>
                            <button type="submit" class="btn btn-default"><i class="fa fa-reply"></i> Send back to HR</button></form>
                    @endif
                    @if($can['decide'])
                        @if($can['awaiting'] === 'review')<p class="lt-note" style="margin-top: 0;">The department head has not reviewed it yet; approving now skips the review (it is noted in the history).</p>@endif
                        <form method="POST" action="{{ route('hrm.letters.approve', $l->hl_id) }}" onsubmit="return confirm('Approve {{ $l->hl_reference }}? The letter is then fixed and cannot be changed.');">@csrf
                            <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> Approve</button></form>
                        <form method="POST" action="{{ route('hrm.letters.reject', $l->hl_id) }}" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">@csrf
                            <div style="flex: 1 1 220px;"><label for="reject_reason">Reason to reject</label><input type="text" class="form-control" id="reject_reason" name="reason" maxlength="255" required></div>
                            <button type="submit" class="btn btn-danger"><i class="fa fa-times"></i> Reject</button></form>
                    @endif
                    @if($can['revise'])
                        <form method="POST" action="{{ route('hrm.letters.revise', $l->hl_id) }}">@csrf
                            <button type="submit" class="btn btn-default"><i class="fa fa-pencil"></i> Correct it (back to draft)</button></form>
                    @endif
                    @if($can['cancel'])
                        <form method="POST" action="{{ route('hrm.letters.cancel', $l->hl_id) }}" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;" onsubmit="return confirm('Cancel {{ $l->hl_reference }}? This cannot be undone.');">@csrf
                            <div style="flex: 1 1 220px;"><label for="cancel_reason">Reason to cancel</label><input type="text" class="form-control" id="cancel_reason" name="reason" maxlength="255" required></div>
                            <button type="submit" class="btn btn-default"><i class="fa fa-ban"></i> Cancel letter</button></form>
                    @endif
                </div>
            </div>
        @endif

        @if($can['history'])
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-history"></i> History</h4></div></div>
            <div class="panel-body">
                <table class="table table-bordered table-striped lt-table">
                    <thead><tr><th>When</th><th>Who</th><th>What</th></tr></thead>
                    <tbody>
                        @foreach($history as $hrow)
                            <tr><td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($hrow->hla_created_at)->format('d M Y, h:i A') }}</td><td>{{ $hrow->au_name ?: '-' }}</td>
                                <td><b>{{ ucfirst(str_replace('_', ' ', $hrow->hla_action)) }}</b>@if($hrow->hla_note)<br><small class="text-muted">{{ $hrow->hla_note }}</small>@endif</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-6">
        <div class="panel panel-bd lt-sticky">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-eye"></i> {{ $l->frozen ? 'The approved letter' : 'Preview' }}</h4></div></div>
            <div class="panel-body">
                <div class="lt-preview-wrap" id="ltPreview"><div class="lt-scale">@include('page.hrm.letters._pad', ['pad' => $pad, 'letter' => $letter])</div></div>
                <p class="lt-missing" id="ltMissing">
                    @if($missing)Still to fill (in <mark>yellow</mark>): {{ implode(', ', array_map([\App\Services\HrmLetterFlow::class, 'label'], $missing)) }}.@endif
                    @if($unknown)<b class="text-danger">Unknown placeholders:</b> {{ implode(', ', $unknown) }}.@endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_script')
@if($can['edit'])<script src="{{ asset('assets/plugins/summernote/summernote.min.js') }}"></script>@endif
<script>
(function ($) {
    function fit() {
        var wrap = $('#ltPreview'), box = wrap.find('.lt-scale'), sheet = box.find('.pad-sheet');
        if (!sheet.length) { return; }
        box.css({ transform: 'none', width: 'auto', height: 'auto' });
        var w = sheet.outerWidth(), h = sheet.outerHeight(), k = Math.min(1, wrap.width() / w);
        box.css({ transform: 'scale(' + k + ')', width: w + 'px', height: h + 'px' });
        wrap.css('height', Math.ceil(h * k) + 28 + 'px');
    }
    $(window).on('load resize', fit);
    fit();
    @if($can['edit'])
    var $body = $('#body'), timer = null;
    var previewUrl = {!! json_encode(route('hrm.letters.preview', $l->hl_id)) !!};
    $body.summernote({
        height: 360,
        toolbar: [['style', ['style']], ['font', ['bold', 'italic', 'underline', 'clear']], ['para', ['ul', 'ol', 'paragraph']], ['table', ['table']], ['misc', ['undo', 'redo', 'codeview']]],
        styleTags: ['p', 'blockquote', 'h4'],
        callbacks: { onBlur: function () { $body.summernote('saveRange'); }, onChange: function () { schedule(); } }
    });
    $('#phInsert').on('change', function () {
        if (!this.value) { return; }
        $body.summernote('restoreRange'); $body.summernote('focus');
        $body.summernote('insertText', '{' + '{' + this.value + '}' + '}');
        this.value = '';
    });
    function preview() {
        var data = $('#draftForm').serializeArray().filter(function (x) { return x.name !== '_token' && x.name !== 'body'; });
        data.push({ name: 'body', value: $body.summernote('code') });
        $.ajax({ url: previewUrl, type: 'POST', dataType: 'json', data: $.param(data), headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } })
            .done(function (r) {
                $('#ltPreview .lt-scale').html(r.html); fit();
                var note = '';
                if (r.missing && r.missing.length) { note += 'Still to fill (in <mark>yellow</mark>): ' + r.missing.map(function (x) { return $('<i>').text(x).html(); }).join(', ') + '. '; }
                if (r.unknown && r.unknown.length) { note += '<b class="text-danger">Unknown placeholders:</b> ' + r.unknown.join(', ') + '.'; }
                $('#ltMissing').html(note);
            });
    }
    function schedule() { clearTimeout(timer); timer = setTimeout(preview, 600); }
    $(document).on('input change', '.lt-live', schedule);
    $('#draftForm [data-then]').on('click', function () { $('#draftThen').val($(this).data('then')); });
    $('#draftForm').on('submit', function () { $body.val($body.summernote('code')); });
    @endif
})(jQuery);
</script>
@endsection

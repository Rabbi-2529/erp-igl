@extends('layouts.master')
@section('title', $template->hltp_id ? 'Edit Letter Template' : 'New Letter Template')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
<link href="{{ asset('assets/plugins/summernote/summernote.css') }}" rel="stylesheet" type="text/css">
<style>
    .ph-group { margin-bottom: 12px; }
    .ph-group h5 { margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; color: var(--erp-muted); }
    .ph-btn { display: inline-block; margin: 0 4px 5px 0; padding: 2px 8px; font-size: 12px; border: 1px solid var(--erp-border); border-radius: 4px;
        background: var(--erp-white); color: var(--erp-text); cursor: pointer; }
    .ph-btn:hover, .ph-btn:focus { border-color: var(--erp-green-btn); }
    .ph-btn code { background: none; color: inherit; padding: 0; font-size: 11px; }
    .ph-btn.ph-asked { border-color: #0b7a3b; background: #e9f6ee; color: #0b4f27; }
    html[data-theme="dark"] .ph-btn.ph-asked { background: #12351f; color: #bfe9cd; border-color: #2e8a50; }
    .lt-preview-wrap { overflow: hidden; padding: 16px; background: #d9dedb; border-radius: 6px; min-height: 120px; }
    html[data-theme="dark"] .lt-preview-wrap { background: #3a403d; }
    .lt-scale { transform-origin: top left; }
    .lt-missing { margin: 8px 0 0; font-size: 13px; }
    .lt-missing mark { background: #fff1a8; color: #7a4f00; padding: 0 3px; border-radius: 2px; }
    .note-editor.note-frame { border-color: var(--erp-border); }
    .note-editor .note-editable { background: #ffffff; color: #1a1a1a; font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; }
    .note-editor .note-editable blockquote { margin: 6px 0 10px 18px; padding: 4px 12px; border-left: 3px solid #c8c8c8; font-size: 14px; }
    .note-editor .note-editable table td { border: 1px solid #bbb; padding: 4px 8px; }
</style>

<div class="lt-head">
    <div>
        <h3>{{ $template->hltp_id ? ($isAdmin ? 'Edit template' : 'Template') . ': ' . $template->hltp_name : 'New template' }}</h3>
        <p class="lt-sub">Write the letter once with placeholders; each letter fills them with the employee's details. Click a placeholder to put it where the cursor is.</p>
    </div>
    <a href="{{ route('hrm.letters.templates', ['type' => $template->hltp_type_id]) }}" class="btn btn-default"><i class="fa fa-list"></i> All templates</a>
</div>

<form method="POST" action="{{ $template->hltp_id ? route('hrm.letters.templates.update', $template->hltp_id) : route('hrm.letters.templates.store') }}" id="tplForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="panel panel-bd">
                <div class="panel-body">
                    <fieldset @unless($isAdmin) disabled @endunless>
                    <div class="row">
                        <div class="col-sm-5 form-group">
                            <label for="type_id">Letter type</label>
                            <select class="form-control dont-select-me" id="type_id" name="type_id">
                                @foreach($types as $t)<option value="{{ $t->hlt_id }}" @selected((int) old('type_id', $template->hltp_type_id) === (int) $t->hlt_id)>{{ $t->hlt_name }} ({{ $t->hlt_prefix }})</option>@endforeach
                            </select>
                        </div>
                        <div class="col-sm-4 form-group"><label for="name">Template name</label><input type="text" class="form-control" id="name" name="name" maxlength="100" value="{{ old('name', $template->hltp_name) }}" placeholder="e.g. Standard" required></div>
                        <div class="col-sm-3 form-group">
                            <label for="lang">Language</label>
                            <select class="form-control dont-select-me" id="lang" name="lang">
                                <option value="en" @selected(old('lang', $template->hltp_lang) === 'en')>English</option>
                                <option value="bn" @selected(old('lang', $template->hltp_lang) === 'bn')>Bangla</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label for="subject">Subject</label><input type="text" class="form-control" id="subject" name="subject" maxlength="200" value="{{ old('subject', $template->hltp_subject) }}"></div>
                    <div class="form-group">
                        <label for="body">Letter text</label>
                        <textarea id="body" name="body" class="form-control" rows="16">{{ old('body', $template->hltp_body) }}</textarea>
                    </div>
                    <div style="display: flex; gap: 18px; flex-wrap: wrap;">
                        <label class="lt-check"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" {{ old('is_default', $template->hltp_is_default) ? 'checked' : '' }}> Default for this letter type</label>
                        <label class="lt-check"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" {{ old('status', $template->hltp_status) ? 'checked' : '' }}> Active</label>
                    </div>
                    </fieldset>
                    @if($isAdmin)
                        <div style="margin-top: 12px;"><button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save template</button></div>
                    @endif
                    <p class="lt-note">The signature, seal, reference number and date are printed by the letterhead, so the text does not need them. Scripts, links and images are removed when saved.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel panel-bd">
                <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-code"></i> Placeholders</h4></div></div>
                <div class="panel-body">
                    <div class="ph-group">
                        <h5>This letter asks for</h5>
                        <div id="phAsked">
                            @foreach($fields as $key => [$label, $kind])
                                <button type="button" class="ph-btn ph-asked" data-key="{{ $key }}" data-field="{{ $key }}" title="Insert {{ $label }}">{{ $label }} <code>&#123;&#123;{{ $key }}&#125;&#125;</code></button>
                            @endforeach
                        </div>
                        <p class="lt-note" id="phAskedNone" style="margin-top: 0; display: none;">Nothing extra: this type only uses the employee's details.</p>
                    </div>
                    @foreach($placeholders as $group => $list)
                        <div class="ph-group">
                            <h5>{{ $group }}</h5>
                            @foreach($list as $key => $label)
                                <button type="button" class="ph-btn" data-key="{{ $key }}" title="Insert {{ $label }}">{{ $label }} <code>&#123;&#123;{{ $key }}&#125;&#125;</code></button>
                            @endforeach
                        </div>
                    @endforeach
                    <div class="ph-group" id="phOther">
                        <h5>Other letter details</h5>
                        @foreach($fields as $key => [$label, $kind])
                            <button type="button" class="ph-btn" data-key="{{ $key }}" data-other="{{ $key }}" title="Insert {{ $label }}">{{ $label }} <code>&#123;&#123;{{ $key }}&#125;&#125;</code></button>
                        @endforeach
                    </div>
                    <p class="lt-note">The last field you clicked (subject or text) gets the placeholder. "Asks for" details are typed on each letter.</p>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="panel panel-bd">
    <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-eye"></i> Preview on the letterhead</h4></div></div>
    <div class="panel-body">
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 12px;">
            <div style="flex: 0 1 340px;">
                <label for="preview_employee" style="display: block; font-size: 12px; margin-bottom: 3px;">Preview with</label>
                <select class="form-control" id="preview_employee" style="width: 100%;">
                    <option value="">A sample employee</option>
                    @foreach($employees as $e)<option value="{{ $e->au_id }}">{{ $e->au_name }}{{ $e->au_user_id ? ' (' . $e->au_user_id . ')' : '' }}{{ $e->au_branch_name ? ' - ' . $e->au_branch_name : '' }}</option>@endforeach
                </select>
            </div>
            <button type="button" class="btn btn-default" id="previewBtn"><i class="fa fa-refresh"></i> Refresh preview</button>
        </div>
        <div class="lt-preview-wrap" id="tplPreview"><div class="lt-scale"><p class="text-muted" style="padding: 20px;">Loading the preview...</p></div></div>
        <p class="lt-missing" id="tplMissing"></p>
    </div>
</div>
@endsection

@section('custom_script')
<script src="{{ asset('assets/plugins/summernote/summernote.min.js') }}"></script>
<script>
(function ($) {
    var typeFields = {!! json_encode($typeFields) !!};
    var previewUrl = {!! json_encode(route('hrm.letters.templates.preview')) !!};
    var canEdit = {{ $isAdmin ? 'true' : 'false' }};
    var $body = $('#body'), lastTarget = 'body', timer = null;

    $body.summernote({
        height: 420,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['misc', ['undo', 'redo', 'codeview']]
        ],
        styleTags: ['p', 'blockquote', 'h4'],
        callbacks: {
            onFocus: function () { lastTarget = 'body'; },
            onBlur: function () { $body.summernote('saveRange'); },
            onChange: function () { schedule(); }
        }
    });
    if (!canEdit) { $body.summernote('disable'); }

    // the type's own details first; the rest under "Other letter details"
    function showAsked() {
        var asked = typeFields[$('#type_id').val()] || [];
        $('#phAsked .ph-btn').each(function () { $(this).toggle(asked.indexOf($(this).data('field')) >= 0); });
        $('#phOther .ph-btn').each(function () { $(this).toggle(asked.indexOf($(this).data('other')) < 0); });
        $('#phAskedNone').toggle(asked.length === 0);
    }
    $('#type_id').on('change', function () { showAsked(); schedule(); });
    showAsked();

    $('#subject').on('focus', function () { lastTarget = 'subject'; }).on('input', schedule);
    $(document).on('click', '.ph-btn', function () {
        if (!canEdit) { return; }
        var text = '{' + '{' + $(this).data('key') + '}' + '}';
        if (lastTarget === 'subject') {
            var el = document.getElementById('subject'), s = el.selectionStart || el.value.length, e = el.selectionEnd || s;
            el.value = el.value.slice(0, s) + text + el.value.slice(e);
            el.focus(); el.setSelectionRange(s + text.length, s + text.length);
            schedule();
        } else {
            $body.summernote('restoreRange');
            $body.summernote('focus');
            $body.summernote('insertText', text);
        }
    });

    function fit() {
        var wrap = $('#tplPreview'), box = wrap.find('.lt-scale'), sheet = box.find('.pad-sheet');
        if (!sheet.length) { return; }
        box.css({ transform: 'none', width: 'auto', height: 'auto' });
        var w = sheet.outerWidth(), h = sheet.outerHeight(), k = Math.min(1, wrap.width() / w);
        box.css({ transform: 'scale(' + k + ')', width: w + 'px', height: h + 'px' });
        wrap.css('height', Math.ceil(h * k) + 32 + 'px');
    }
    function preview() {
        $.ajax({
            url: previewUrl, type: 'POST', dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { type_id: $('#type_id').val(), subject: $('#subject').val(), body: $body.summernote('code'), employee_id: $('#preview_employee').val() }
        }).done(function (r) {
            $('#tplPreview .lt-scale').html(r.html);
            fit();
            var note = '';
            if (r.unknown && r.unknown.length) { note += '<b class="text-danger">Unknown placeholders (fix before saving):</b> ' + r.unknown.map(function (k) { return '{' + '{' + k + '}' + '}'; }).join(', ') + '. '; }
            if (r.missing && r.missing.length) { note += 'Shown in <mark>yellow</mark>: details this employee does not have yet; they are typed on the letter.'; }
            $('#tplMissing').html(note);
        }).fail(function () { $('#tplMissing').text('The preview could not be loaded.'); });
    }
    function schedule() { clearTimeout(timer); timer = setTimeout(preview, 700); }
    $('#previewBtn').on('click', preview);
    $('#preview_employee').on('change', preview);
    $(window).on('resize', fit);
    $('#tplForm').on('submit', function () { $body.val($body.summernote('code')); });
    preview();
})(jQuery);
</script>
@endsection

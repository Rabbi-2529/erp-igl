@extends('layouts.master')
@section('title', 'Letterhead PAD')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
@php
    $v = function ($field) use ($saved) { return old($field, $saved ? $saved->{'hlh_' . $field} : null); };
    $sourceText = ['branch' => 'this branch\'s own letterhead', 'company' => 'the company letterhead', 'erp' => 'the company and branch details in the ERP (no letterhead saved yet)'];
@endphp
<style>
    .lh-branches { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .lh-branches a.active { color: var(--erp-on-primary); background: var(--erp-green-btn); border-color: var(--erp-green-btn); }
    .lh-preview-wrap { overflow: hidden; padding: 16px; background: #d9dedb; border-radius: 6px; }
    .lh-scale { transform-origin: top left; }
    html[data-theme="dark"] .lh-preview-wrap { background: #3a403d; }
    .lh-imgrow { display: flex; align-items: center; gap: 10px; }
    .lh-color { width: 60px; height: 34px; padding: 2px; }
</style>

<div class="lt-head">
    <div>
        <h3>Letterhead PAD</h3>
        <p class="lt-sub">The top and foot of every letter. One letterhead for the company; a branch can have its own. Empty fields use the details already in the ERP.</p>
    </div>
</div>

<div class="lh-branches">
    <a href="{{ route('hrm.letters.letterhead') }}" class="btn btn-default btn-sm {{ $branchId === 0 ? 'active' : '' }}"><i class="fa fa-building"></i> Company letterhead</a>
    @foreach($branches as $id => $name)
        <a href="{{ route('hrm.letters.letterhead', ['branch' => $id]) }}" class="btn btn-default btn-sm {{ $branchId === (int) $id ? 'active' : '' }}">{{ $name }}@if(in_array($id, $own)) <span class="lt-tag on" style="margin-left: 4px;">own</span>@endif</a>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-5">
        <form method="POST" action="{{ route('hrm.letters.letterhead.save') }}" enctype="multipart/form-data" class="panel panel-bd">
            @csrf
            <input type="hidden" name="branch" value="{{ $branchId }}">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-file-text-o"></i> {{ $branchId ? ($branches[$branchId] . ' branch') : 'Company' }} letterhead</h4></div></div>
            <div class="panel-body">
                <p class="lt-note" style="margin-top: 0;">
                    @if($branchId && !$saved) This branch prints with the company letterhead. Save here to give it its own.
                    @else Letters of {{ $branchId ? 'this branch' : 'branches without their own letterhead' }} print with {{ $sourceText[$pad->source] }}. @endif
                </p>
                <fieldset @unless($isAdmin) disabled @endunless>
                <div class="form-group"><label for="company_name">Company name</label><input type="text" class="form-control" id="company_name" name="company_name" maxlength="150" value="{{ $v('company_name') }}" placeholder="{{ $erp->company_name }}"></div>
                <div class="form-group"><label for="tagline">Tagline (optional)</label><input type="text" class="form-control" id="tagline" name="tagline" maxlength="200" value="{{ $v('tagline') }}" placeholder="e.g. Your trusted technology partner"></div>
                <div class="form-group"><label for="address">Address</label><input type="text" class="form-control" id="address" name="address" maxlength="255" value="{{ $v('address') }}" placeholder="{{ $erp->address }}"></div>
                <div class="row">
                    <div class="col-sm-6 form-group"><label for="phone">Phone</label><input type="text" class="form-control" id="phone" name="phone" maxlength="100" value="{{ $v('phone') }}" placeholder="{{ $erp->phone }}"></div>
                    <div class="col-sm-6 form-group"><label for="email">Email</label><input type="email" class="form-control" id="email" name="email" maxlength="150" value="{{ $v('email') }}" placeholder="{{ $erp->email }}"></div>
                </div>
                <div class="row">
                    <div class="col-sm-8 form-group"><label for="website">Website</label><input type="text" class="form-control" id="website" name="website" maxlength="150" value="{{ $v('website') }}" placeholder="e.g. www.example.com"></div>
                    <div class="col-sm-4 form-group"><label for="accent">Line colour</label><input type="color" class="form-control lh-color" id="accent" name="accent" value="{{ $v('accent') ?: ($pad->accent ?? '#0e8a43') }}"></div>
                </div>
                <div class="form-group"><label for="footer_text">Footer line (optional)</label><input type="text" class="form-control" id="footer_text" name="footer_text" maxlength="255" value="{{ $v('footer_text') }}" placeholder="Company name, phone, email and website"></div>

                <div class="form-group">
                    <label for="logo">Logo</label>
                    <div class="lh-imgrow">
                        <img src="{{ \App\Services\HrmLetters::imageUrl($pad->logo) }}" alt="Current logo" class="lt-thumb">
                        <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                    </div>
                    @if($saved && $saved->hlh_logo)<label class="lt-check"><input type="checkbox" name="remove_logo" value="1"> Remove this logo (use the ERP logo)</label>@endif
                </div>
                <div class="form-group">
                    <label for="seal">Company seal / stamp (optional)</label>
                    <div class="lh-imgrow">
                        @if($pad->seal)<img src="{{ \App\Services\HrmLetters::imageUrl($pad->seal) }}" alt="Current seal" class="lt-thumb">@endif
                        <input type="file" id="seal" name="seal" accept="image/png,image/jpeg,image/webp">
                    </div>
                    @if($saved && $saved->hlh_seal)<label class="lt-check"><input type="checkbox" name="remove_seal" value="1"> Remove the seal image (leave a space to stamp)</label>@endif
                </div>

                <div class="row">
                    <div class="col-xs-4 form-group"><label for="margin_top">Top (mm)</label><input type="number" class="form-control" id="margin_top" name="margin_top" min="5" max="40" value="{{ $v('margin_top') ?? ($pad->margin_top ?? 12) }}" required></div>
                    <div class="col-xs-4 form-group"><label for="margin_bottom">Bottom (mm)</label><input type="number" class="form-control" id="margin_bottom" name="margin_bottom" min="5" max="40" value="{{ $v('margin_bottom') ?? ($pad->margin_bottom ?? 12) }}" required></div>
                    <div class="col-xs-4 form-group"><label for="margin_side">Sides (mm)</label><input type="number" class="form-control" id="margin_side" name="margin_side" min="8" max="40" value="{{ $v('margin_side') ?? ($pad->margin_side ?? 18) }}" required></div>
                </div>
                <label class="lt-check"><input type="hidden" name="page_numbers" value="0"><input type="checkbox" name="page_numbers" value="1" {{ ($saved ? $saved->hlh_page_numbers : ($pad->page_numbers ?? 1)) ? 'checked' : '' }}> Page numbers in the footer</label>
                </fieldset>
                @if($isAdmin)
                    <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save letterhead</button>
                    </div>
                @endif
                <p class="lt-note">Images: PNG, JPG or WEBP, up to 1 MB. A logo about 600 &times; 200 pixels and a seal with a clear background print best.</p>
            </div>
        </form>
        @if($isAdmin && $branchId && $saved)
            <form method="POST" action="{{ route('hrm.letters.letterhead.reset') }}" onsubmit="return confirm('Make this branch use the company letterhead again?');">
                @csrf
                <input type="hidden" name="branch" value="{{ $branchId }}">
                <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-undo"></i> Use the company letterhead for this branch</button>
            </form>
        @endif
    </div>

    <div class="col-lg-7">
        <div class="panel panel-bd">
            <div class="panel-heading"><div class="panel-title"><h4><i class="fa fa-eye"></i> Preview (A4)</h4></div></div>
            <div class="panel-body">
                <div class="lh-preview-wrap" id="lhPreview">
                    <div class="lh-scale">@include('page.hrm.letters._pad', ['pad' => $pad, 'letter' => $sample])</div>
                </div>
                <p class="lt-note">The preview shows the saved letterhead. Save to see your changes here. Letters print on A4 portrait with these margins.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_script')
<script>
(function ($) {
    // the A4 page is scaled down to fit the panel, like a print preview
    function fit() {
        var wrap = $('#lhPreview'), box = wrap.find('.lh-scale'), sheet = box.find('.pad-sheet');
        if (!sheet.length) { return; }
        box.css({ transform: 'none', width: 'auto', height: 'auto' });
        var w = sheet.outerWidth(), h = sheet.outerHeight(), avail = wrap.width();
        var k = Math.min(1, avail / w);
        box.css({ transform: 'scale(' + k + ')', width: w + 'px', height: h + 'px' });
        wrap.css('height', Math.ceil(h * k) + 32 + 'px');
    }
    $(window).on('load resize', fit);
    fit();
})(jQuery);
</script>
@endsection

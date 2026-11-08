{{--
    The letterhead PAD: an A4 corporate letter page.
    $pad    letterhead (HrmLetters::letterhead or a letter's frozen copy)
    $letter reference, date (Carbon), subject, body (HTML), signatory {name, designation, signature}
    $images 'url' (screen / print) or 'data' (PDF: images embedded)
--}}
@php
    $images = $images ?? 'url';
    $img = function ($path) use ($images) {
        return $images === 'data' ? \App\Services\HrmLetters::imageData($path) : \App\Services\HrmLetters::imageUrl($path);
    };
    $accent = $pad->accent ?: '#0e8a43';
    $contact = array_filter([$pad->phone ? 'Phone: ' . $pad->phone : null, $pad->email ? 'Email: ' . $pad->email : null, $pad->website ? $pad->website : null]);
    $logo = $img($pad->logo);
    $seal = $pad->seal ? $img($pad->seal) : null;
    $sign = !empty($letter->signatory->signature) ? $img($letter->signatory->signature) : null;
@endphp
<style>
    .pad-sheet { width: 210mm; min-height: 297mm; margin: 0 auto; background: #ffffff !important; color: #1a1a1a !important; position: relative; box-sizing: border-box;
        padding: {{ $pad->margin_top }}mm {{ $pad->margin_side }}mm {{ $pad->margin_bottom + 16 }}mm; font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; line-height: 1.55;
        box-shadow: 0 2px 14px rgba(0, 0, 0, .18); text-align: left; }
    .pad-sheet * { color: inherit; }
    .pad-head { display: flex; align-items: center; gap: 16px; padding-bottom: 8px; border-bottom: 3px solid {{ $accent }}; }
    .pad-logo img { display: block; max-height: 58px; max-width: 190px; }
    .pad-org { margin-left: auto; text-align: right; }
    .pad-org .pad-name { font-size: 19pt; font-weight: 700; color: {{ $accent }} !important; letter-spacing: .6px; text-transform: uppercase; line-height: 1.15; }
    .pad-org .pad-tag { font-size: 9.5pt; color: #555 !important; font-style: italic; margin-top: 1px; }
    .pad-org .pad-addr, .pad-org .pad-contact { font-size: 8.5pt; color: #444 !important; margin-top: 2px; }
    .pad-meta { display: flex; justify-content: space-between; gap: 12px; margin: 14px 0 16px; font-size: 10.5pt; }
    .pad-meta b { color: #1a1a1a !important; }
    .pad-subject { margin: 0 0 12px; font-size: 11.5pt; font-weight: 700; }
    .pad-subject span { text-decoration: underline; }
    .pad-body { text-align: justify; }
    .pad-body p { margin: 0 0 10px; color: #1a1a1a !important; }
    .pad-body table { border-collapse: collapse; margin: 6px 0 10px; }
    .pad-body td, .pad-body th { border: 1px solid #999; padding: 4px 8px; }
    .pad-sign { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 34px; page-break-inside: avoid; break-inside: avoid; }
    .pad-sign .pad-sig { min-width: 62mm; }
    .pad-sign .pad-sig img { display: block; max-height: 48px; max-width: 170px; margin-bottom: 2px; }
    .pad-sign .pad-sig .pad-line { border-top: 1px solid #1a1a1a; padding-top: 4px; min-height: 0; }
    .pad-sign .pad-sig .pad-sig-space { height: 48px; }
    .pad-sign .pad-signame { font-weight: 700; }
    .pad-sign .pad-sigdes, .pad-sign .pad-sigorg { font-size: 10pt; color: #333 !important; }
    .pad-seal { width: 30mm; height: 30mm; border: 1px dashed #b5b5b5; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 8pt; color: #6f6f6f !important; text-align: center; }
    .pad-seal.has-img { border: 0; }
    .pad-seal img { max-width: 30mm; max-height: 30mm; opacity: .9; }
    .pad-foot { position: absolute; left: {{ $pad->margin_side }}mm; right: {{ $pad->margin_side }}mm; bottom: {{ max(6, $pad->margin_bottom - 4) }}mm;
        border-top: 1px solid {{ $accent }}; padding-top: 4px; display: flex; justify-content: space-between; gap: 10px; font-size: 8pt; color: #555 !important; }
    .pad-foot .pad-page { white-space: nowrap; }
    .pad-sheet mark.pad-missing { background: #fff1a8 !important; color: #7a4f00 !important; padding: 0 2px; border-radius: 2px; font-family: Consolas, monospace; font-size: 9.5pt; }
    .pad-body blockquote { margin: 6px 0 12px 18px; padding: 4px 12px; border-left: 3px solid #c8c8c8; font-size: 11pt; color: #1a1a1a !important; }
    .pad-body ol, .pad-body ul { margin: 0 0 10px; padding-left: 22px; }
    .pad-body li { margin-bottom: 4px; }
</style>
<div class="pad-sheet erp-keep-colors">
    <div class="pad-head">
        @if($logo)<div class="pad-logo"><img src="{{ $logo }}" alt="{{ $pad->company_name ?: 'Logo' }}"></div>@endif
        <div class="pad-org">
            @if($pad->company_name)<div class="pad-name">{{ $pad->company_name }}</div>@endif
            @if($pad->tagline)<div class="pad-tag">{{ $pad->tagline }}</div>@endif
            @if($pad->address)<div class="pad-addr">{{ $pad->address }}</div>@endif
            @if($contact)<div class="pad-contact">{{ implode('  |  ', $contact) }}</div>@endif
        </div>
    </div>

    <div class="pad-meta">
        <div>Ref: <b>{{ $letter->reference }}</b></div>
        <div>Date: <b>{{ $letter->date->format('d F Y') }}</b></div>
    </div>

    @if($letter->subject)<p class="pad-subject">Subject: <span>{{ $letter->subject }}</span></p>@endif

    <div class="pad-body">{!! $letter->body !!}</div>

    <div class="pad-sign">
        <div class="pad-sig">
            @if($sign)<img src="{{ $sign }}" alt="Signature">@else<div class="pad-sig-space"></div>@endif
            <div class="pad-line">
                <div class="pad-signame">{{ $letter->signatory->name }}</div>
                <div class="pad-sigdes">{{ $letter->signatory->designation }}</div>
                @if($pad->company_name)<div class="pad-sigorg">{{ $pad->company_name }}</div>@endif
            </div>
        </div>
        <div class="pad-seal {{ $seal ? 'has-img' : '' }}">@if($seal)<img src="{{ $seal }}" alt="Company seal">@else Company<br>seal @endif</div>
    </div>

    <div class="pad-foot">
        <div>{{ $pad->footer_text ?: trim(($pad->company_name ? $pad->company_name . ' · ' : '') . implode(' · ', array_filter([$pad->phone, $pad->email, $pad->website]))) }}</div>
        @if($pad->page_numbers)<div class="pad-page">Page 1 of 1</div>@endif
    </div>
</div>

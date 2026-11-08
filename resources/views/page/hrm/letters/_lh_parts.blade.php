{{--
    The letterhead PAD for printing and PDF, built with tables so the browser and dompdf show it the same.
    $part: 'css' | 'head' | 'foot' | 'meta' | 'sign'.  $pad letterhead, $letter {reference, date, subject, body, signatory}, $img(path) image source.
--}}
@php
    $accent = ($pad->accent ?? null) ?: '#0e8a43';
@endphp
@if($part === 'css')
    .lh-head { width: 100%; border-collapse: collapse; }
    .lh-head td { vertical-align: middle; padding: 0; }
    .lh-logo img { max-height: 58px; max-width: 190px; }
    .lh-org { text-align: right; }
    .lh-name { font-size: 19pt; font-weight: bold; color: {{ $accent }}; letter-spacing: .5px; text-transform: uppercase; line-height: 1.15; }
    .lh-tag { font-size: 9.5pt; color: #555555; font-style: italic; margin-top: 1px; }
    .lh-addr, .lh-contact { font-size: 8.5pt; color: #444444; margin-top: 2px; }
    .lh-rule { height: 0; border-top: 3px solid {{ $accent }}; margin-top: 8px; }
    .lh-meta { width: 100%; border-collapse: collapse; margin: 14px 0 16px; font-size: 10.5pt; }
    .lh-meta td { padding: 0; }
    .lh-right { text-align: right; }
    .lh-subject { margin: 0 0 12px; font-size: 11.5pt; font-weight: bold; }
    .lh-subject span { text-decoration: underline; }
    .lh-body { text-align: justify; }
    .lh-body p { margin: 0 0 10px; }
    .lh-body ol, .lh-body ul { margin: 0 0 10px; padding-left: 30px; }
    .lh-body li { margin-bottom: 4px; }
    .lh-body blockquote { margin: 6px 0 12px 18px; padding: 4px 12px; border-left: 3px solid #c8c8c8; }
    .lh-body table { border-collapse: collapse; margin: 6px 0 10px; }
    .lh-body td, .lh-body th { border: 1px solid #999999; padding: 4px 8px; }
    .lh-sign { width: 100%; border-collapse: collapse; margin-top: 30px; page-break-inside: avoid; }
    .lh-sign td { vertical-align: bottom; padding: 0; }
    .lh-sig-img img { max-height: 48px; max-width: 170px; }
    .lh-sig-space { height: 48px; }
    .lh-sig-line { border-top: 1px solid #1a1a1a; padding-top: 4px; width: 62mm; }
    .lh-signame { font-weight: bold; }
    .lh-sigdes { font-size: 10pt; color: #333333; }
    .lh-seal { width: 30mm; height: 30mm; text-align: center; vertical-align: middle; }
    .lh-seal-empty { width: 30mm; height: 30mm; border: 1px dashed #b5b5b5; border-radius: 15mm; color: #6f6f6f; font-size: 8pt; text-align: center; }
    .lh-seal img { max-width: 30mm; max-height: 30mm; }
    .lh-foot-rule { height: 0; border-top: 1px solid {{ $accent }}; }
    .lh-foot { width: 100%; border-collapse: collapse; font-size: 8pt; color: #555555; margin-top: 4px; }
    .lh-foot td { padding: 0; vertical-align: top; }
@elseif($part === 'head')
    @php
        $logo = $img($pad->logo ?? null);
        $contact = array_filter([($pad->phone ?? null) ? 'Phone: ' . $pad->phone : null, ($pad->email ?? null) ? 'Email: ' . $pad->email : null, ($pad->website ?? null) ?: null]);
    @endphp
    <table class="lh-head"><tr>
        <td class="lh-logo" style="width: 45%;">@if($logo)<img src="{{ $logo }}" alt="{{ $pad->company_name ?? 'Logo' }}">@endif</td>
        <td class="lh-org">
            @if(!empty($pad->company_name))<div class="lh-name">{{ $pad->company_name }}</div>@endif
            @if(!empty($pad->tagline))<div class="lh-tag">{{ $pad->tagline }}</div>@endif
            @if(!empty($pad->address))<div class="lh-addr">{{ $pad->address }}</div>@endif
            @if($contact)<div class="lh-contact">{{ implode('  |  ', $contact) }}</div>@endif
        </td>
    </tr></table>
    <div class="lh-rule"></div>
@elseif($part === 'foot')
    <div class="lh-foot-rule"></div>
    <table class="lh-foot"><tr>
        <td class="lh-foot-text">{{ \App\Services\HrmLetterFlow::footerText($pad) }}</td>
        <td class="lh-right" style="width: 28%;">@if(!empty($pad->page_numbers) && empty($pdf))<span class="lh-pagenum">Page 1 of 1</span>@endif</td>
    </tr></table>
@elseif($part === 'meta')
    <table class="lh-meta"><tr>
        <td>Ref: <b>{{ $letter->reference }}</b></td>
        <td class="lh-right">Date: <b>{{ $letter->date->format('d F Y') }}</b></td>
    </tr></table>
    @if($letter->subject)<p class="lh-subject">Subject: <span>{{ $letter->subject }}</span></p>@endif
@elseif($part === 'sign')
    @php
        $sign = !empty($letter->signatory->signature) ? $img($letter->signatory->signature) : null;
        $seal = !empty($pad->seal) ? $img($pad->seal) : null;
    @endphp
    <table class="lh-sign"><tr>
        <td>
            @if($sign)<div class="lh-sig-img"><img src="{{ $sign }}" alt="Signature"></div>@else<div class="lh-sig-space"></div>@endif
            <div class="lh-sig-line">
                <div class="lh-signame">{{ $letter->signatory->name ?? '' }}</div>
                <div class="lh-sigdes">{{ $letter->signatory->designation ?? '' }}</div>
                @if(!empty($pad->company_name))<div class="lh-sigdes">{{ $pad->company_name }}</div>@endif
            </div>
        </td>
        <td class="lh-seal" style="width: 32mm;">
            @if($seal)<img src="{{ $seal }}" alt="Company seal">@else<table class="lh-seal-empty"><tr><td style="vertical-align: middle; text-align: center; color: #6f6f6f;">Company<br>seal</td></tr></table>@endif
        </td>
    </tr></table>
@endif

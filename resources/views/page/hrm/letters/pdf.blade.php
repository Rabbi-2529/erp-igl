@php
    // dompdf: the letterhead header and footer are fixed boxes in the page margins, so they repeat on every page
    $img = function ($path) { return \App\Services\HrmLetters::imageData($path); };
    $mt = (int) ($pad->margin_top ?? 12);
    $mb = (int) ($pad->margin_bottom ?? 12);
    $ms = (int) ($pad->margin_side ?? 18);
    [$headH, $footH] = $bands;
    $parts = ['pad' => $pad, 'letter' => $letter, 'img' => $img, 'pdf' => true];
    [$text, $closing] = \App\Services\HrmLetterFlow::splitClosing($letter->body);
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $l->hl_reference }}</title>
<style>
    @page { margin: {{ $mt + $headH }}mm {{ $ms }}mm {{ $mb + $footH }}mm {{ $ms }}mm; }
    body { margin: 0; font-family: Helvetica, Arial, sans-serif; font-size: 11pt; line-height: 1.55; color: #1a1a1a; }
    #head { position: fixed; top: -{{ $headH }}mm; left: 0; right: 0; height: {{ $headH }}mm; }
    #foot { position: fixed; bottom: -{{ $footH }}mm; left: 0; right: 0; height: {{ $footH }}mm; padding-top: 8pt; }
    @include('page.hrm.letters._lh_parts', $parts + ['part' => 'css'])
    .lh-meta { margin-top: 0; }
    .lh-keep { page-break-inside: avoid; }
</style>
</head>
<body>
<div id="head">@include('page.hrm.letters._lh_parts', $parts + ['part' => 'head'])</div>
<div id="foot">@include('page.hrm.letters._lh_parts', $parts + ['part' => 'foot'])</div>
<div id="letter">
    @include('page.hrm.letters._lh_parts', $parts + ['part' => 'meta'])
    @if(trim($text) !== '')<div class="lh-body">{!! $text !!}</div>@endif
    <div class="lh-keep">
        @if($closing !== '')<div class="lh-body">{!! $closing !!}</div>@endif
        @include('page.hrm.letters._lh_parts', $parts + ['part' => 'sign'])
    </div>
</div>
</body>
</html>

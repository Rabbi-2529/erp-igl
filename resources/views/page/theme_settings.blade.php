@extends('layouts.master')
@section('title', 'Theme Colors')

@section('theme_settings_class','active')

@section('content')
<style>
    .ts-intro { color: var(--erp-muted); margin: 0 0 14px; }
    .ts-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--erp-border); }
    .ts-row:last-of-type { border-bottom: 0; }
    .ts-row .ts-name { flex: 1 1 auto; min-width: 0; }
    .ts-row .ts-name strong { display: block; }
    .ts-row .ts-name small { color: var(--erp-muted); }
    .ts-row input[type=color] { width: 46px; height: 34px; padding: 2px; border: 1px solid var(--erp-border); border-radius: 4px; background: var(--erp-white); cursor: pointer; flex: 0 0 auto; }
    .ts-row .ts-hex { width: 96px; flex: 0 0 auto; font-family: Consolas, monospace; text-transform: lowercase; }
    .ts-row .ts-default { flex: 0 0 auto; width: 34px; padding: 6px 0; }
    .ts-row .ts-hex.ts-bad { border-color: var(--erp-red); }
    .ts-presets { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 6px; }
    .ts-preset { display: inline-flex; align-items: center; gap: 8px; padding: 6px 10px; border: 1px solid var(--erp-border); border-radius: 4px; background: var(--erp-white); color: var(--erp-text); cursor: pointer; font-weight: 700; }
    .ts-preset:hover { border-color: var(--erp-green-btn); }
    .ts-preset.active { border-color: var(--erp-green-btn); box-shadow: inset 0 0 0 1px var(--erp-green-btn); }
    .ts-classic-note { display: none; margin: 10px 0 0; padding: 10px 12px; border-left: 4px solid var(--erp-green-btn); background: var(--erp-green-tint); color: var(--erp-text); border-radius: 3px; }
    .ts-is-classic .ts-classic-note { display: block; }
    .ts-is-classic .ts-row { opacity: .55; }
    .ts-preset .ts-dots { display: inline-flex; }
    .ts-preset .ts-dots i { width: 14px; height: 14px; border-radius: 50%; margin-right: -4px; border: 2px solid #fff; display: inline-block; }
    .ts-checks { margin: 0; padding: 0; list-style: none; }
    .ts-checks li { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 6px 0; border-bottom: 1px dashed var(--erp-border); }
    .ts-checks li:last-child { border-bottom: 0; }
    .ts-checks .ts-pair { display: inline-flex; align-items: center; gap: 8px; }
    .ts-checks .ts-chip { display: inline-block; min-width: 44px; padding: 2px 8px; border-radius: 3px; font-weight: 700; text-align: center; border: 1px solid rgba(0,0,0,.12); }
    .ts-verdict { font-weight: 700; white-space: nowrap; }
    .ts-verdict.ok { color: #1e5c37; }
    .ts-verdict.mid { color: #7a5a00; }
    .ts-verdict.bad { color: #a3121a; }
    .ts-verdict i { margin-right: 4px; }
    html[data-theme="dark"] .ts-verdict.ok { color: #7ee2a4; }
    html[data-theme="dark"] .ts-verdict.mid { color: #f3d58a; }
    html[data-theme="dark"] .ts-verdict.bad { color: #f59a9e; }
    .ts-sample-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 12px; }
    .ts-sample .small-box { margin-bottom: 10px; }
    .ts-sample .small-box h3 { font-size: 26px; }
    .ts-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 14px; }
    .ts-saved-note { color: var(--erp-muted); margin-left: auto; }
    @media (max-width: 480px) {
        .ts-row { flex-wrap: wrap; }
        .ts-row .ts-name { flex-basis: 100%; }
    }
</style>

@include('lc._alerts')

<div class="row">
    {{-- ===== Colour choices ===== --}}
    <div class="col-md-5">
        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="panel-title"><h4>Theme Colors</h4></div>
            </div>
            <div class="panel-body">
                <p class="ts-intro">
                    The whole ERP changes as you pick, so you can see a colour before you save it.
                    Text on each colour (white or dark) is chosen for you.
                    @unless($canChange)
                        <br><strong class="text-danger">Only the company admin can change these colours.</strong>
                    @endunless
                </p>

                <label>Quick start</label>
                <div class="ts-presets" id="ts-presets"></div>
                <hr style="margin:12px 0 4px;">

                <form method="POST" action="{{ route('theme-settings.update') }}" id="ts-form" autocomplete="off">
                    @csrf
                    <input type="hidden" name="style" id="ts-style" value="{{ old('style', $settings['style']) }}">
                    <p class="ts-classic-note">
                        <strong>Previous style is on:</strong> the ERP looks the way it did before the new design.
                        The colours below are for the new style; change any of them, or pick another Quick start, to switch back.
                    </p>
                    @php
                        $fields = [
                            'primary' => ['Main colour', 'Buttons, links, table header line'],
                            'accent'  => ['Highlight colour', 'Delete buttons, active menu marker, red cards'],
                            'sidebar' => ['Sidebar', 'Left menu background'],
                            'search'  => ['Search buttons', 'Search, filter and info buttons'],
                            'page'    => ['Page background', 'Behind the panels'],
                            'text'    => ['Text', 'Body text and the dark cards'],
                        ];
                    @endphp
                    @foreach($fields as $key => $info)
                        <div class="ts-row">
                            <div class="ts-name">
                                <strong>{{ $info[0] }}</strong>
                                <small>{{ $info[1] }}</small>
                            </div>
                            <input type="color" class="ts-picker" data-key="{{ $key }}" value="{{ old($key, $settings[$key]) }}" aria-label="{{ $info[0] }} picker" @disabled(!$canChange)>
                            <input type="text" class="form-control ts-hex" name="{{ $key }}" data-key="{{ $key }}" value="{{ old($key, $settings[$key]) }}" maxlength="7" aria-label="{{ $info[0] }} code" @disabled(!$canChange)>
                            <button type="button" class="btn btn-default ts-default" data-key="{{ $key }}" title="Back to default ({{ $defaults[$key] }})" @disabled(!$canChange)><i class="fa fa-undo"></i></button>
                        </div>
                    @endforeach

                    <div class="ts-row">
                        <div class="ts-name">
                            <strong>Page title font</strong>
                            <small>Page titles and the big card numbers</small>
                        </div>
                        <select name="heading_font" id="ts-font" class="form-control dont-select-me" style="width:150px;" @disabled(!$canChange)>
                            <option value="arial-black" @selected(old('heading_font', $settings['heading_font']) === 'arial-black')>Arial Black</option>
                            <option value="arial" @selected(old('heading_font', $settings['heading_font']) === 'arial')>Arial (lighter)</option>
                        </select>
                    </div>

                    @if($canChange)
                    <div class="ts-actions">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save colours</button>
                        <button type="button" class="btn btn-default" id="ts-undo" title="Go back to the saved colours"><i class="fa fa-history"></i> Undo changes</button>
                        <button type="button" class="btn btn-danger" id="ts-reset"><i class="fa fa-refresh"></i> Reset to default</button>
                    </div>
                    @endif
                </form>

                @if($canChange)
                <form method="POST" action="{{ route('theme-settings.reset') }}" id="ts-reset-form" style="display:none;">
                    @csrf
                </form>
                @endif

                <p class="ts-saved-note" style="margin-top:12px;">
                    @if($saved && $settings['style'] === 'classic')
                        Using the previous style, saved {{ \Carbon\Carbon::parse($saved->ets_updated_at)->format('Y-m-d H:i') }}.
                    @elseif($saved)
                        Using your company colours, saved {{ \Carbon\Carbon::parse($saved->ets_updated_at)->format('Y-m-d H:i') }}.
                    @else
                        Using the default FelnaTech theme.
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ===== Preview ===== --}}
    <div class="col-md-7">
        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="panel-title"><h4>Is it easy to read?</h4></div>
            </div>
            <div class="panel-body">
                <ul class="ts-checks" id="ts-checks"></ul>
            </div>
        </div>

        <div class="panel panel-bd ts-sample">
            <div class="panel-heading">
                <div class="panel-title"><h4>Preview</h4></div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="small-box bg-green"><div class="inner"><h3>52,400</h3><p>Today's Sales</p></div><div class="icon"><i class="fa fa-shopping-cart"></i></div><a href="javascript:void(0)" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small-box bg-red"><div class="inner"><h3>8,150</h3><p>Customer Due</p></div><div class="icon"><i class="fa fa-money"></i></div><a href="javascript:void(0)" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small-box bg-yellow"><div class="inner"><h3>36</h3><p>Low Stock</p></div><div class="icon"><i class="fa fa-cubes"></i></div><a href="javascript:void(0)" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a></div>
                    </div>
                </div>

                <div class="ts-sample-row">
                    <button type="button" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-primary"><i class="fa fa-print"></i> Print</button>
                    <button type="button" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                    <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</button>
                    <button type="button" class="btn btn-default">Cancel</button>
                    <a href="javascript:void(0)">A link</a>
                </div>

                <div class="ts-sample-row">
                    <span class="label label-success">Paid</span>
                    <span class="label label-danger">Due</span>
                    <span class="label label-primary">Pending</span>
                    <span class="label label-warning">Draft</span>
                </div>

                <div class="alert alert-success" style="margin-bottom:8px;">Payment saved.</div>
                <div class="alert alert-danger">Amount is more than the due.</div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="margin-bottom:0;">
                        <thead>
                            <tr><th>Date</th><th>Invoice</th><th>Customer</th><th class="text-right">Amount</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>2026-11-05</td><td>INV-1042</td><td>Rahim Traders</td><td class="text-right">12,500.00</td></tr>
                            <tr><td>2026-11-04</td><td>INV-1041</td><td>Karim Store</td><td class="text-right">4,250.00</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_script')
<script>
(function () {
    'use strict';

    var DEFAULTS = @json($defaults);
    var SAVED = @json($settings);
    var FONTS = @json($fonts);
    var CLASSIC = @json($classicTokens);
    var themeLink = document.getElementById('erp-theme-css');
    var KEYS = ['primary', 'accent', 'sidebar', 'search', 'page', 'text'];

    // Ready-made colour sets (each passes the readability checks below)
    var PRESETS = [
        { name: 'FelnaTech (default)', c: DEFAULTS },
        { name: 'Previous style', c: $.extend({}, DEFAULTS, { style: 'classic' }), dots: ['#37a000', '#dc3545', '#2c3136', '#177fbf'] },
        { name: 'Ocean Blue', c: { primary: '#1565c0', accent: '#d32f2f', sidebar: '#0d2b52', search: '#64b5f6', page: '#eef2f7', text: '#263238', heading_font: 'arial-black', style: 'felna' } },
        { name: 'Royal Purple', c: { primary: '#6a1b9a', accent: '#d81b60', sidebar: '#2e0f45', search: '#ce93d8', page: '#f3eff6', text: '#2d2a33', heading_font: 'arial-black', style: 'felna' } },
        { name: 'Charcoal', c: { primary: '#37474f', accent: '#e8202a', sidebar: '#1f2629', search: '#90a4ae', page: '#f0f2f3', text: '#263238', heading_font: 'arial', style: 'felna' } },
        { name: 'Maroon', c: { primary: '#8e1b23', accent: '#e65100', sidebar: '#3b0b10', search: '#ef9a9a', page: '#f6f0f0', text: '#2b2525', heading_font: 'arial-black', style: 'felna' } }
    ];

    // ---------- colour maths (same formulas as App\Models\Erp_theme_setting) ----------
    function isHex(v) { return /^#[0-9a-fA-F]{6}$/.test(v); }
    function rgb(h) { return [parseInt(h.substr(1, 2), 16), parseInt(h.substr(3, 2), 16), parseInt(h.substr(5, 2), 16)]; }
    function toHex(a) { return '#' + a.map(function (c) { c = Math.max(0, Math.min(255, Math.round(c))); return (c < 16 ? '0' : '') + c.toString(16); }).join(''); }
    function shade(h, a) { return toHex(rgb(h).map(function (c) { return c * (1 - a); })); }
    function tint(h, a) { return toHex(rgb(h).map(function (c) { return c + (255 - c) * a; })); }
    function rgba(h, a) { var c = rgb(h); return 'rgba(' + c[0] + ', ' + c[1] + ', ' + c[2] + ', ' + a + ')'; }
    function lum(h) {
        var l = rgb(h).map(function (c) { c /= 255; return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); });
        return 0.2126 * l[0] + 0.7152 * l[1] + 0.0722 * l[2];
    }
    function contrast(a, b) { var x = lum(a), y = lum(b); return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05); }
    function bestText(bg) { return contrast('#ffffff', bg) >= contrast('#1a1a1a', bg) ? '#ffffff' : '#1a1a1a'; }

    // Every CSS variable the theme uses, worked out from the six colours
    function variables(s) {
        var v = {}, p = s.primary, a = s.accent, sb = s.sidebar, g = s.search;
        v['--erp-green-btn'] = p; v['--erp-green-btn-dark'] = shade(p, .18); v['--erp-green'] = p; v['--erp-green-rgb'] = rgb(p).join(', '); v['--erp-selection'] = tint(p, .7);
        v['--erp-green-tint'] = tint(p, .9); v['--erp-block-green'] = shade(p, .08);
        v['--erp-on-primary'] = bestText(p); v['--erp-on-block-green'] = bestText(shade(p, .08));
        v['--erp-alert-success-text'] = shade(p, .55); v['--erp-alert-success-bg'] = tint(p, .9); v['--erp-alert-success-border'] = tint(p, .7);

        v['--erp-red'] = a; v['--erp-red-dark'] = shade(a, .18); v['--erp-block-red'] = shade(a, .12);
        v['--erp-on-accent'] = bestText(a); v['--erp-on-block-red'] = bestText(shade(a, .12));
        v['--erp-alert-danger-text'] = shade(a, .5); v['--erp-alert-danger-bg'] = tint(a, .9); v['--erp-alert-danger-border'] = tint(a, .7);

        var darkBar = bestText(sb) === '#ffffff', sub = darkBar ? shade(sb, .25) : shade(sb, .08);
        v['--erp-green-dark'] = sb; v['--erp-green-darker'] = sub; v['--erp-green-hover'] = darkBar ? tint(sb, .12) : shade(sb, .12);
        v['--erp-on-sidebar'] = bestText(sb); v['--erp-sidebar-text'] = rgba(bestText(sb), .88);
        v['--erp-sidebar-sub-text'] = rgba(bestText(sub), .8); v['--erp-sidebar-strong'] = bestText(sb);
        v['--erp-sidebar-line'] = rgba(bestText(sb), .15); v['--erp-sidebar-scroll'] = rgba(bestText(sb), .45);

        v['--erp-green-light'] = g; v['--erp-green-light-dark'] = shade(g, .22);
        v['--erp-on-search'] = bestText(g); v['--erp-on-search-hover'] = bestText(shade(g, .22));

        v['--erp-page'] = s.page;
        v['--erp-text'] = s.text; v['--erp-black'] = s.text; v['--erp-on-black'] = bestText(s.text);
        v['--erp-font-heading'] = FONTS[s.heading_font] || FONTS['arial-black'];
        return v;
    }

    // ---------- form <-> preview ----------
    var root = document.documentElement;
    var savedTag = document.getElementById('erp-theme-colors');
    if (savedTag) { savedTag.parentNode.removeChild(savedTag); } // this page drives the colours itself
    var canChange = {{ $canChange ? 'true' : 'false' }};

    function current() {
        var s = {};
        KEYS.forEach(function (k) {
            var v = $('.ts-hex[data-key=' + k + ']').val().trim().toLowerCase();
            if (v.charAt(0) !== '#') { v = '#' + v; }
            s[k] = isHex(v) ? v : SAVED[k];
        });
        s.heading_font = $('#ts-font').val();
        s.style = $('#ts-style').val() === 'classic' ? 'classic' : 'felna';
        return s;
    }

    var setNames = [];
    function apply() {
        var s = current(), classic = s.style === 'classic';
        var v = classic ? CLASSIC : variables(s);
        if (!classic && root.getAttribute('data-theme') === 'dark') {
            // dark mode keeps its own page, text and table colours; preview only the brand colours
            v = $.extend({}, v);
            ['--erp-page', '--erp-text', '--erp-black', '--erp-on-black', '--erp-green-tint', '--erp-selection',
             '--erp-alert-success-text', '--erp-alert-success-bg', '--erp-alert-success-border',
             '--erp-alert-danger-text', '--erp-alert-danger-bg', '--erp-alert-danger-border'].forEach(function (k) { delete v[k]; });
        }
        setNames.forEach(function (name) { root.style.removeProperty(name); });
        setNames = Object.keys(v);
        setNames.forEach(function (name) { root.style.setProperty(name, v[name]); });
        if (themeLink) { themeLink.media = classic ? 'not all' : 'all'; } // previous style = new stylesheet off
        $('#ts-form').toggleClass('ts-is-classic', classic);
        renderChecks(s, v);
        markPreset(s);
    }

    function markPreset(s) {
        $('.ts-preset').each(function () {
            var c = PRESETS[$(this).data('i')].c, same = c.style === 'classic' ? s.style === 'classic'
                : s.style !== 'classic' && KEYS.concat(['heading_font']).every(function (k) { return c[k] === s[k]; });
            $(this).toggleClass('active', same);
        });
    }

    // Any colour or font change means the new style
    function useNewStyle() { $('#ts-style').val('felna'); }

    function setColour(key, value) {
        $('.ts-hex[data-key=' + key + ']').val(value).removeClass('ts-bad');
        $('.ts-picker[data-key=' + key + ']').val(value);
    }

    function fill(c) {
        KEYS.forEach(function (k) { setColour(k, c[k]); });
        $('#ts-font').val(c.heading_font || 'arial-black');
        $('#ts-style').val(c.style === 'classic' ? 'classic' : 'felna');
        apply();
    }

    // ---------- readability ----------
    function verdict(ratio) {
        if (ratio >= 4.5) { return '<span class="ts-verdict ok"><i class="fa fa-check-circle"></i>Easy to read</span>'; }
        if (ratio >= 3) { return '<span class="ts-verdict mid"><i class="fa fa-exclamation-circle"></i>OK for bold text</span>'; }
        return '<span class="ts-verdict bad"><i class="fa fa-times-circle"></i>Hard to read</span>';
    }

    function renderChecks(s, v) {
        if (s.style === 'classic') {
            $('#ts-checks').html('<li><span>Previous style uses the original ERP colours; these checks are for the new style.</span></li>');
            return;
        }
        var rows = [
            ['Text on the page', s.text, '#ffffff', 'Text'],
            ['Text on page background', s.text, s.page, 'Text'],
            ['Main buttons', v['--erp-on-primary'], s.primary, 'Save'],
            ['Links on white', s.primary, '#ffffff', 'Link'],
            ['Highlight buttons', v['--erp-on-accent'], s.accent, 'Delete'],
            ['Sidebar menu', v['--erp-on-sidebar'], s.sidebar, 'Menu'],
            ['Search buttons', v['--erp-on-search'], s.search, 'Search']
        ];
        var html = rows.map(function (r) {
            var ratio = contrast(r[1], r[2]);
            return '<li><span class="ts-pair"><span class="ts-chip erp-keep-colors" style="color:' + r[1] + ';background:' + r[2] + ';">' + r[3] + '</span>' + r[0] + '</span>' +
                '<span>' + verdict(ratio) + ' <small class="text-muted">' + (Math.floor(ratio * 10) / 10).toFixed(1) + ':1</small></span></li>';
        }).join('');
        $('#ts-checks').html(html);
    }

    // ---------- presets ----------
    PRESETS.forEach(function (p, i) {
        var dots = (p.dots || ['primary', 'accent', 'sidebar', 'search'].map(function (k) { return p.c[k]; })).map(function (c) { return '<i style="background:' + c + '"></i>'; }).join('');
        $('#ts-presets').append('<button type="button" class="ts-preset" data-i="' + i + '"' + (canChange ? '' : ' disabled') + '><span class="ts-dots">' + dots + '</span>' + p.name + '</button>');
    });

    // ---------- events ----------
    $(document).on('click', '.ts-preset', function () { fill(PRESETS[$(this).data('i')].c); });

    $(document).on('input change', '.ts-picker', function () {
        useNewStyle();
        setColour($(this).data('key'), this.value.toLowerCase());
        apply();
    });

    $(document).on('input', '.ts-hex', function () {
        var v = this.value.trim().toLowerCase();
        if (v && v.charAt(0) !== '#') { v = '#' + v; }
        var ok = isHex(v);
        $(this).toggleClass('ts-bad', !ok);
        if (ok) { useNewStyle(); $('.ts-picker[data-key=' + $(this).data('key') + ']').val(v); apply(); }
    });

    $(document).on('blur', '.ts-hex', function () {
        var v = this.value.trim().toLowerCase();
        if (v && v.charAt(0) !== '#') { v = '#' + v; }
        if (isHex(v)) { this.value = v; $(this).removeClass('ts-bad'); }
    });

    $(document).on('click', '.ts-default', function () {
        var k = $(this).data('key');
        useNewStyle();
        setColour(k, DEFAULTS[k]);
        apply();
    });

    $('#ts-font').on('change', function () { useNewStyle(); apply(); });
    $('#ts-undo').on('click', function () { fill(SAVED); });
    $('#ts-reset').on('click', function () {
        if (confirm('Go back to the default FelnaTech colours for everyone in this company?')) {
            $('#ts-reset-form').submit();
        }
    });

    $('#ts-form').on('submit', function (e) {
        var bad = $('.ts-hex').filter(function () { return !isHex(this.value.trim()); });
        if (bad.length) {
            e.preventDefault();
            bad.addClass('ts-bad').first().focus();
            alert('Please enter colours like #1a2b3c.');
        }
    });

    apply();
})();
</script>
@endsection

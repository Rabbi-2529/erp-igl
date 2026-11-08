<!doctype html>
<html lang="{{ $lang === 'en' ? 'en' : 'bn' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="color-scheme" content="only light">
    <title>{{ $title }}{{ $company ? ' - ' . $company : '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <style>
        :root { --green: #0e8a43; --green-dark: #0a6b34; --red: #c4161f; --text: #1f2421; --muted: #5f6b64; --line: #d7e0da; --field: #b9c7be; --page: #eef2ef; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: "Hind Siliguri", "Noto Sans Bengali", "Nirmala UI", Arial, sans-serif; font-size: 16px; color: var(--text); background: var(--page); display: flex; align-items: flex-start; justify-content: center; padding: 24px 14px; }
        .card { width: 100%; max-width: 480px; background: #ffffff; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; box-shadow: 0 6px 24px rgba(0, 0, 0, .06); }
        .brand { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 18px; background: #ffffff; border-bottom: 1px solid var(--line); }
        .brand img { display: block; height: 34px; width: auto; max-width: 60%; }
        .langs { display: inline-flex; border: 1px solid var(--line); border-radius: 18px; overflow: hidden; flex: 0 0 auto; }
        .langs a { padding: 5px 12px; font-size: 14px; font-weight: 700; color: var(--muted); text-decoration: none; }
        .langs a.on { color: #ffffff; background: var(--green); }
        .agent { margin: 0; padding: 8px 22px; font-size: 14px; color: var(--green-dark); background: #eaf6ef; border-bottom: 1px solid var(--line); }
        .agent b { font-weight: 700; }
        form, .msg { padding: 18px 22px 22px; }
        .field { margin-bottom: 14px; }
        label { display: block; margin-bottom: 5px; font-weight: 700; font-size: 15px; }
        label .req { color: var(--red); }
        label .opt { font-weight: 400; color: var(--muted); }
        input { width: 100%; height: 46px; padding: 10px 12px; font: inherit; color: var(--text); background: #ffffff; border: 1px solid var(--field); border-radius: 6px; }
        input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(14, 138, 67, .18); }
        select { width: 100%; height: 46px; font: inherit; }
        .row2 { display: flex; gap: 10px; }
        .row2 .field { flex: 1 1 0; min-width: 0; }
        .hint { margin-top: 5px; font-size: 14px; color: var(--muted); }
        .hint.ok { color: var(--green); font-weight: 700; }
        .hint.bad, .err { margin-top: 5px; font-size: 14px; color: var(--red); }
        .has-err input, input.bad { border-color: var(--red); }
        input.ok { border-color: var(--green); }
        button { width: 100%; padding: 12px; font: inherit; font-size: 18px; font-weight: 700; color: #ffffff; background: var(--green); border: 0; border-radius: 6px; cursor: pointer; }
        button:hover, button:focus { background: var(--green-dark); }
        button:disabled { cursor: not-allowed; background: #9fb5a8; }
        .hp { position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden; }
        .msg { text-align: center; }
        .msg .icon { width: 56px; height: 56px; margin: 6px auto 12px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; color: #ffffff; background: var(--green); }
        .msg.off .icon { background: var(--muted); }
        .msg h2 { margin: 0 0 6px; font-size: 22px; }
        .msg p { margin: 0; color: var(--muted); line-height: 1.6; }
        .foot { padding: 0 22px 16px; font-size: 13px; color: var(--muted); text-align: center; }
        /* select2, matched to the other fields */
        .select2-container { width: 100% !important; font-size: 16px; }
        .select2-container--default .select2-selection--single { height: 46px; border: 1px solid var(--field); border-radius: 6px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 44px; padding-left: 12px; color: var(--text); }
        .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #7a857f; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px; }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single { border-color: var(--green); }
        .select2-container--default.select2-container--disabled .select2-selection--single { background: #f3f6f4; }
        .select2-dropdown { border-color: var(--green); font-family: inherit; }
        .select2-search--dropdown .select2-search__field { height: 40px; padding: 6px 10px; font: inherit; border-radius: 4px; }
        .select2-results__option { padding: 8px 12px; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--green); }
        .select2-container--default .select2-results__group { color: var(--muted); }
        @media (max-width: 420px) { .row2 { display: block; } .brand img { height: 30px; } }
    </style>
</head>
<body>
<div class="card">
    <div class="brand">
        <img src="{{ $logo }}" alt="FelnaTech">
        <nav class="langs" aria-label="Language">
            <a href="{{ url()->current() }}?lang=bn" class="{{ $lang === 'bn' ? 'on' : '' }}" lang="bn">বাংলা</a>
            <a href="{{ url()->current() }}?lang=en" class="{{ $lang === 'en' ? 'on' : '' }}" lang="en">English</a>
        </nav>
    </div>
    @if($agent)<p class="agent">{{ $t['agent'] }}: <b>{{ $agent }}</b></p>@endif

    @if(!empty($done))
        <div class="msg" role="status">
            <div class="icon">&#10003;</div>
            <h2>{{ $t['thanks'] }}</h2>
            <p>{{ $t['thanks_text'] }}</p>
        </div>
    @elseif(!$form->clf_active)
        <div class="msg off" role="status">
            <div class="icon">!</div>
            <h2>{{ $t['closed'] }}</h2>
            <p>{{ $t['closed_text'] }}</p>
        </div>
    @else
        <form method="POST" action="{{ route('lead-form.submit', $token) }}" id="leadForm" novalidate>
            <input type="hidden" name="lang" value="{{ $lang }}">
            <div class="field {{ $errors->has('name') ? 'has-err' : '' }}">
                <label for="name">{{ $t['name'] }} <span class="req">*</span></label>
                <input type="text" id="name" name="name" maxlength="60" value="{{ old('name') }}" autocomplete="name" required>
                @error('name')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field {{ $errors->has('mobile_clean') ? 'has-err' : '' }}">
                <label for="mobile">{{ $t['mobile'] }} <span class="req">*</span></label>
                <input type="tel" id="mobile" name="mobile" maxlength="17" inputmode="tel" placeholder="01XXXXXXXXX" value="{{ old('mobile') }}" autocomplete="tel" required aria-describedby="mobileHint">
                @error('mobile_clean')
                    <div class="err" id="mobileHint">{{ $message }}</div>
                @else
                    <div class="hint" id="mobileHint">{{ $t['mobile_hint'] }}</div>
                @enderror
            </div>
            <div class="field {{ $errors->has('email') ? 'has-err' : '' }}">
                <label for="email">{{ $t['email'] }} <span class="opt">{{ $t['optional'] }}</span></label>
                <input type="email" id="email" name="email" maxlength="80" value="{{ old('email') }}" autocomplete="email">
                @error('email')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="row2">
                <div class="field {{ $errors->has('district_id') ? 'has-err' : '' }}">
                    <label for="district_id">{{ $t['district'] }}</label>
                    <select id="district_id" name="district_id">
                        <option value="">{{ $t['pick_district'] }}</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}" data-alt="{{ $lang === 'en' ? $d->bn_name : $d->name }}" @selected((string) old('district_id') === (string) $d->id)>{{ $lang === 'en' ? ($d->name ?: $d->bn_name) : ($d->bn_name ?: $d->name) }}</option>
                        @endforeach
                    </select>
                    @error('district_id')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field {{ $errors->has('thana_id') ? 'has-err' : '' }}">
                    <label for="thana_id">{{ $t['thana'] }}</label>
                    <select id="thana_id" name="thana_id" data-old="{{ old('thana_id') }}">
                        <option value="">{{ $t['district_first'] }}</option>
                    </select>
                    @error('thana_id')<div class="err">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="field {{ $errors->has('address') ? 'has-err' : '' }}">
                <label for="address">{{ $t['address'] }}</label>
                <input type="text" id="address" name="address" maxlength="150" value="{{ old('address') }}" placeholder="{{ $t['address_hint'] }}" autocomplete="street-address">
                @error('address')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field {{ $errors->has('reason_id') ? 'has-err' : '' }}">
                <label for="reason_id">{{ $t['reason'] }}</label>
                <select id="reason_id" name="reason_id">
                    <option value="">{{ $t['pick_reason'] }}</option>
                    @foreach($reasons->groupBy('category') as $category => $items)
                        <optgroup label="{{ $category ?: $t['other'] }}">
                            @foreach($items as $r)
                                <option value="{{ $r->id }}" @selected((string) old('reason_id') === (string) $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('reason_id')<div class="err">{{ $message }}</div>@enderror
            </div>
            {{-- left empty by people; bots fill it --}}
            <div class="hp" aria-hidden="true"><label for="website">Website</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
            <button type="submit" id="sendBtn">{{ $t['send'] }}</button>
        </form>
    @endif
    <div class="foot">{{ $t['privacy'] }}</div>
</div>

@if(empty($done) && $form->clf_active)
<script src="{{ asset('assets/js/jquery-3.4.1.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
(function ($) {
    var T = @json($t);
    var thanas = @json($thanaList);

    // ---------- Bangladeshi mobile number: checked while typing; Send stays locked until it is valid ----------
    var BN_DIGITS = { '০': '0', '১': '1', '২': '2', '৩': '3', '৪': '4', '৫': '5', '৬': '6', '৭': '7', '৮': '8', '৯': '9' };
    function cleanMobile(v) {
        v = String(v || '').replace(/[০-৯]/g, function (d) { return BN_DIGITS[d]; }).replace(/\D+/g, '');
        if (v.length === 13 && v.slice(0, 2) === '88') { v = v.slice(2); }
        return v;
    }
    function mobileValid(v) { return /^01[3-9][0-9]{8}$/.test(cleanMobile(v)); }
    var $name = $('#name'), $mobile = $('#mobile'), $hint = $('#mobileHint'), $send = $('#sendBtn');
    function check(showError) {
        var raw = $mobile.val(), ok = mobileValid(raw), typed = cleanMobile(raw).length > 0;
        $mobile.toggleClass('ok', ok).toggleClass('bad', !ok && typed && showError);
        if (ok) { $hint.attr('class', 'hint ok').text('✓ ' + T.mobile_ok); }
        else if (typed && showError) { $hint.attr('class', 'hint bad').text(T.e_mobile_bad); }
        else if (!$hint.hasClass('err')) { $hint.attr('class', 'hint').text(T.mobile_hint); }
        $send.prop('disabled', !(ok && $.trim($name.val()) !== ''));
        return ok;
    }
    // only digits, +, spaces and dashes can be typed into the mobile box
    $mobile.on('input', function () {
        var v = this.value.replace(/[^0-9০-৯+\-\s]/g, '');
        if (v !== this.value) { this.value = v; }
        check(cleanMobile(v).length >= 11);
    }).on('blur', function () { check(true); });
    $name.on('input', function () { check(false); });
    $('#leadForm').on('submit', function (e) {
        if (!check(true) || $.trim($name.val()) === '') {
            e.preventDefault();
            (!$.trim($name.val()) ? $name : $mobile).focus();
            return;
        }
        $send.prop('disabled', true).text(T.sending);   // no double sending
    });
    check(false);

    // ---------- searchable lists (select2): find a place in Bangla or English ----------
    function matcher(params, data) {
        var term = $.trim(params.term || '').toLowerCase();
        if (term === '') { return data; }
        if (data.children && data.children.length) {   // reason groups
            var kids = $.map(data.children, function (c) { return matcher(params, c); });
            if (kids.length) { var copy = $.extend({}, data, true); copy.children = kids; return copy; }
            return (data.text || '').toLowerCase().indexOf(term) > -1 ? data : null;
        }
        var alt = data.element ? ($(data.element).attr('data-alt') || '') : '';
        return ((data.text || '') + ' ' + alt).toLowerCase().indexOf(term) > -1 ? data : null;
    }
    var common = { width: '100%', matcher: matcher, language: { noResults: function () { return T.no_match; } } };
    var $district = $('#district_id'), $thana = $('#thana_id');
    $district.select2($.extend({ placeholder: T.pick_district, allowClear: true }, common));
    $('#reason_id').select2($.extend({ placeholder: T.pick_reason, allowClear: true }, common));

    function fillThanas() {
        var d = $district.val() || '', keep = $thana.val() || $thana.attr('data-old');
        if ($thana.data('select2')) { $thana.select2('destroy'); }
        $thana.empty().append($('<option value="">').text(d ? T.pick_thana : T.district_first));
        $.each(thanas, function (i, x) {
            if (String(x[1]) !== String(d)) { return; }
            $thana.append($('<option>').val(x[0]).text(x[2]).attr('data-alt', x[3] || '').prop('selected', String(x[0]) === String(keep)));
        });
        $thana.prop('disabled', !d).attr('data-old', '');
        $thana.select2($.extend({ placeholder: d ? T.pick_thana : T.district_first, allowClear: true }, common));
    }
    $district.on('change', fillThanas);
    fillThanas();
})(jQuery);
</script>
@endif
</body>
</html>

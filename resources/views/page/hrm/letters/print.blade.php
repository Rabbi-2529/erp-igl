@php
    $img = function ($path) { return \App\Services\HrmLetters::imageUrl($path); };
    $mt = (int) ($pad->margin_top ?? 12);
    $mb = (int) ($pad->margin_bottom ?? 12);
    $ms = (int) ($pad->margin_side ?? 18);
    $parts = ['pad' => $pad, 'letter' => $letter, 'img' => $img];
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $l->hl_reference }} - {{ $l->hlt_name }}</title>
<style>
    @page { size: A4 portrait; margin: 0; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body { background: #5f6763; font-family: 'Helvetica', 'Arial', 'Noto Sans Bengali', 'Nirmala UI', 'SolaimanLipi', 'Vrinda', sans-serif; color: #1a1a1a; }
    .bar { position: sticky; top: 0; z-index: 20; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 10px 16px;
        background: #1f2421; color: #ffffff; font-size: 14px; box-shadow: 0 2px 8px rgba(0, 0, 0, .3); }
    .bar .ref { font-family: Consolas, monospace; font-weight: 700; letter-spacing: .5px; }
    .bar .muted { color: #c9d1cc; }
    .bar .grow { flex: 1 1 auto; }
    .bar a, .bar button { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 4px; font: inherit; text-decoration: none; cursor: pointer;
        border: 1px solid #6b746f; background: #2e3531; color: #ffffff; }
    .bar a:hover, .bar button:hover { background: #3b443f; }
    .bar .primary { background: #0e8a43; border-color: #0e8a43; }
    .bar .primary:hover { background: #0b7a3b; }
    .bar .note { color: #f5dfa0; }
    #pages { padding: 24px 12px 40px; overflow-x: auto; }
    .sheet { width: 210mm; height: 297mm; margin: 0 auto 18px; background: #ffffff; position: relative; overflow: hidden;
        padding: {{ $mt }}mm {{ $ms }}mm {{ $mb }}mm; display: flex; flex-direction: column; box-shadow: 0 4px 18px rgba(0, 0, 0, .35);
        font-size: 11pt; line-height: 1.55; color: #1a1a1a; }
    .s-head { flex: 0 0 auto; padding-bottom: 14px; }
    .s-content .lh-meta { margin-top: 0; }
    .s-content { flex: 1 1 auto; overflow: hidden; position: relative; }
    .s-foot { flex: 0 0 auto; padding-top: 6px; }
    .wm { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) rotate(-32deg); font-size: 92pt; font-weight: 700; letter-spacing: 6px;
        color: rgba(196, 22, 31, .14); white-space: nowrap; pointer-events: none; z-index: 5; }
    /* which paper is in the printer: plain paper (the PAD header and footer are printed) or PAD paper (only the text) */
    .bar .padsw { display: inline-flex; padding: 0; gap: 0; border: 1px solid #6b746f; border-radius: 4px; overflow: hidden; }
    .bar .padsw button { border: 0; border-radius: 0; background: #2e3531; }
    .bar .padsw button + button { border-left: 1px solid #6b746f; }
    .bar .padsw button[aria-pressed="true"] { background: #0e8a43; }
    .bar .padsw button:focus-visible { outline: 2px solid #f5dfa0; outline-offset: -2px; }
    #padNote { display: none; }
    body.no-pad #padNote { display: inline; }
    /* on screen the PAD stays faintly visible, to show where the printed PAD paper is; it is not printed */
    body.no-pad .s-head, body.no-pad .s-foot { opacity: .2; }
    @include('page.hrm.letters._lh_parts', $parts + ['part' => 'css'])
    @media print {
        body { background: #ffffff; }
        .bar { display: none !important; }
        body.no-pad .s-head, body.no-pad .s-foot { visibility: hidden; opacity: 1; }
        #pages { padding: 0; overflow: visible; zoom: 1 !important; }
        .sheet { margin: 0; box-shadow: none; page-break-after: always; break-after: page; }
        .sheet:last-child { page-break-after: auto; break-after: auto; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
</head>
<body>
<div class="bar">
    <a href="{{ route('hrm.letters.show', $l->hl_id) }}">&larr; Back to the letter</a>
    <span class="ref">{{ $l->hl_reference }}</span>
    <span class="muted">{{ $l->hlt_name }} &middot; {{ $l->employee->name ?? '' }}</span>
    <span class="grow"></span>
    @if($printable)
        <span class="padsw" role="group" aria-label="Paper in the printer">
            <button type="button" data-pad="1" aria-pressed="true" title="Plain white paper: the PAD header and footer are printed with the letter">Plain paper</button>
            <button type="button" data-pad="0" aria-pressed="false" title="Printed PAD paper is in the printer: only the letter text is printed">PAD paper (text only)</button>
        </span>
        <span class="note" id="padNote" title="Only the letter text prints. Put the PAD paper in the printer.">Load PAD paper</span>
        <button type="button" class="primary" id="printBtn">&#128424; Print</button>
        @if($bangla)
            <span class="note" id="pdfNote">{{ request('save') === 'pdf' ? 'This letter has Bangla text. To save it as a PDF, click Print and choose "Save as PDF" as the printer.' : 'For a PDF: click Print and choose "Save as PDF".' }}</span>
        @else
            <form method="POST" action="{{ route('hrm.letters.pdf', $l->hl_id) }}" style="margin: 0;"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="pad" value="1" id="pdfPad"><button type="submit" id="pdfBtn">&#11015; Download PDF</button></form>
        @endif
    @else
        <span class="note">Preview only: {{ [
            'draft' => 'this letter is still a draft. It can be printed after it is approved.',
            'submitted' => 'this letter is waiting for approval. It can be printed after it is approved.',
            'reviewed' => 'this letter is waiting for approval. It can be printed after it is approved.',
            'rejected' => 'this letter was rejected. Correct it and send it for approval again.',
            'cancelled' => 'this letter is cancelled and cannot be printed.',
        ][$l->hl_status] ?? 'this letter cannot be printed yet.' }}</span>
    @endif
</div>

{{-- the letter, laid out into A4 pages by the script below --}}
<div id="src" hidden>
    <div id="src-head">@include('page.hrm.letters._lh_parts', $parts + ['part' => 'head'])</div>
    <div id="src-foot">@include('page.hrm.letters._lh_parts', $parts + ['part' => 'foot'])</div>
    <div id="src-meta">@include('page.hrm.letters._lh_parts', $parts + ['part' => 'meta'])</div>
    <div id="src-body">{!! $letter->body !!}</div>
    <div id="src-sign">@include('page.hrm.letters._lh_parts', $parts + ['part' => 'sign'])</div>
</div>
<div id="pages"></div>

<script>
(function () {
    var src = document.getElementById('src'), pages = document.getElementById('pages');
    var watermark = {!! json_encode($watermark) !!};
    var printable = {{ $printable ? 'true' : 'false' }};
    var logUrl = {!! json_encode(route('hrm.letters.printed', $l->hl_id)) !!};
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var BLOCK = /^(P|DIV|UL|OL|TABLE|H1|H2|H3|H4|H5|H6|BLOCKQUOTE|HR|PRE|SECTION|ARTICLE)$/;
    // a box (div) holding paragraphs is opened up, so its paragraphs can go onto different pages; each keeps the box's style
    function pieces(n) {
        var onlyBlocks = /^(DIV|SECTION|ARTICLE)$/.test(n.tagName) && n.children.length && Array.prototype.every.call(n.childNodes, function (c) {
            return (c.nodeType === 1 && BLOCK.test(c.tagName)) || (c.nodeType === 3 && !c.textContent.trim()) || c.nodeType === 8;
        });
        if (!onlyBlocks) { return [n]; }
        var out = [];
        Array.prototype.forEach.call(n.children, function (c) {
            pieces(c).forEach(function (x) { var shell = n.cloneNode(false); shell.removeAttribute('id'); shell.appendChild(x.cloneNode(true)); out.push(shell); });
        });
        return out;
    }
    function blocks() {
        var list = [];
        function add(container, body) {
            Array.prototype.forEach.call(container.childNodes, function (n) {
                if (n.nodeType === 1) { (body ? pieces(n) : [n]).forEach(function (x) { list.push({ el: x, body: body }); }); }
                else if (n.nodeType === 3 && n.textContent.trim()) { var p = document.createElement('p'); p.textContent = n.textContent.trim(); list.push({ el: p, body: body }); }
            });
        }
        add(src.querySelector('#src-meta'), false);
        add(src.querySelector('#src-body'), true);
        // empty lines the editor leaves at the end (<p><br></p>) are dropped
        function blank(el) { return !el.textContent.replace(/[\s ]+/g, '') && !el.querySelector('img, table'); }
        while (list.length && list[list.length - 1].body && blank(list[list.length - 1].el)) { list.pop(); }
        // a short closing paragraph goes to the next page together with the signature, never the signature alone
        var last = list[list.length - 1];
        if (last && last.body && last.el.tagName === 'P' && last.el.textContent.length <= 600) { last.keep = true; }
        var signAt = list.length;
        add(src.querySelector('#src-sign'), false);
        if (list[signAt]) { list[signAt].sign = true; }
        return list;
    }

    function paginate() {
        pages.innerHTML = '';
        var content, bodyBox, placed;
        function sheet() {
            var s = document.createElement('div'); s.className = 'sheet';
            var h = document.createElement('div'); h.className = 's-head'; h.innerHTML = src.querySelector('#src-head').innerHTML;
            var c = document.createElement('div'); c.className = 's-content';
            var f = document.createElement('div'); f.className = 's-foot'; f.innerHTML = src.querySelector('#src-foot').innerHTML;
            s.appendChild(h); s.appendChild(c); s.appendChild(f);
            if (watermark) { var w = document.createElement('div'); w.className = 'wm'; w.textContent = watermark; s.appendChild(w); }
            pages.appendChild(s);
            content = c; bodyBox = null; placed = 0;
        }
        function box(b) {
            if (!b.body) { bodyBox = null; return content; }
            if (!bodyBox) { bodyBox = document.createElement('div'); bodyBox.className = 'lh-body'; content.appendChild(bodyBox); }
            return bodyBox;
        }
        function fits() { return content.scrollHeight <= content.clientHeight + 1; }
        var prev = null;
        sheet();
        blocks().forEach(function (b) {
            var node = b.el.cloneNode(true), target = box(b);
            target.appendChild(node);
            if (fits()) { placed++; prev = { b: b, node: node, sheet: content }; return; }
            target.removeChild(node);
            if (b.sign && prev && prev.b.keep && prev.sheet === content && placed > 1) {
                var holder = prev.node.parentNode;
                holder.removeChild(prev.node);
                if (holder !== content && !holder.children.length) { holder.parentNode.removeChild(holder); }
                sheet(); box(prev.b).appendChild(prev.node); placed++;
                target = box(b); target.appendChild(node); placed++;
                if (!fits()) { content.style.overflow = 'visible'; }
                prev = null;
                return;
            }
            prev = null;
            // a list continues on the next page, numbering carried on
            if ((node.tagName === 'OL' || node.tagName === 'UL') && node.children.length > 1) {
                var items = Array.prototype.slice.call(node.children), start = parseInt(node.getAttribute('start') || '1', 10), i = 0;
                var part = node.cloneNode(false); target.appendChild(part);
                while (i < items.length) { part.appendChild(items[i].cloneNode(true)); if (!fits()) { part.removeChild(part.lastChild); break; } i++; }
                if (i === 0) { target.removeChild(part); } else { placed++; }
                while (i < items.length) {
                    sheet(); target = box(b);
                    var rest = node.cloneNode(false); if (node.tagName === 'OL') { rest.setAttribute('start', start + i); } target.appendChild(rest);
                    while (i < items.length) { rest.appendChild(items[i].cloneNode(true)); if (!fits() && rest.children.length > 1) { rest.removeChild(rest.lastChild); break; } i++; }
                    placed++;
                }
                return;
            }
            // a long table continues on the next page row by row, its heading row repeated
            if (node.tagName === 'TABLE' && node.querySelectorAll('tr').length > 1) {
                var thead = node.querySelector('thead');
                var rows = Array.prototype.filter.call(node.querySelectorAll('tr'), function (tr) { return !thead || !thead.contains(tr); });
                var shell = function () {
                    var t = node.cloneNode(false); if (thead) { t.appendChild(thead.cloneNode(true)); }
                    var tb = document.createElement('tbody'); t.appendChild(tb); return { table: t, body: tb };
                };
                var j = 0, piece = shell(); target.appendChild(piece.table);
                while (j < rows.length) { piece.body.appendChild(rows[j].cloneNode(true)); if (!fits()) { piece.body.removeChild(piece.body.lastChild); break; } j++; }
                if (j === 0) { target.removeChild(piece.table); } else { placed++; }
                while (j < rows.length) {
                    sheet(); target = box(b);
                    piece = shell(); target.appendChild(piece.table);
                    while (j < rows.length) { piece.body.appendChild(rows[j].cloneNode(true)); if (!fits() && piece.body.children.length > 1) { piece.body.removeChild(piece.body.lastChild); break; } j++; }
                    placed++;
                }
                return;
            }
            if (placed === 0) { target.appendChild(node); content.style.overflow = 'visible'; placed++; return; }   // taller than a whole page
            sheet(); target = box(b); target.appendChild(node); placed++;
            if (!fits()) { content.style.overflow = 'visible'; }
        });
        var all = pages.querySelectorAll('.sheet');
        Array.prototype.forEach.call(all, function (s, i) {
            var p = s.querySelector('.lh-pagenum'); if (p) { p.textContent = 'Page ' + (i + 1) + ' of ' + all.length; }
        });
        document.body.setAttribute('data-pages', all.length);
    }

    // on a phone the A4 sheets are shown smaller (after the layout is done; printing always uses full size)
    function fitScreen() {
        var sheet = pages.querySelector('.sheet');
        if (!sheet) { return; }
        var room = document.documentElement.clientWidth - 24, width = sheet.offsetWidth;
        pages.style.zoom = room < width ? (room / width).toFixed(3) : '';
    }
    window.addEventListener('resize', fitScreen);

    // lay out once images and fonts are ready, and again if anything still overflows
    function settle() {
        pages.style.zoom = '';
        paginate();
        fitScreen();
        setTimeout(function () {
            pages.style.zoom = '';
            var over = Array.prototype.some.call(pages.querySelectorAll('.s-content'), function (c) { return c.style.overflow !== 'visible' && c.scrollHeight > c.clientHeight + 1; });
            if (over) { paginate(); }
            fitScreen();
        }, 120);
    }
    if (document.readyState === 'complete') { settle(); } else { window.addEventListener('load', settle); }
    if (document.fonts && document.fonts.ready) { document.fonts.ready.then(function () { if (document.readyState === 'complete') { settle(); } }); }

    // plain paper (PAD printed) or PAD paper (text only); the choice is remembered on this computer.
    // Only what is shown changes, not the layout: the text stays in the same place on the page.
    var PAD_KEY = 'erp.letter.pad', withPad = true;
    function setPad(on, remember) {
        withPad = on;
        document.body.classList.toggle('no-pad', !on);
        Array.prototype.forEach.call(document.querySelectorAll('.padsw button'), function (b) { b.setAttribute('aria-pressed', String((b.getAttribute('data-pad') === '1') === on)); });
        var field = document.getElementById('pdfPad'); if (field) { field.value = on ? '1' : '0'; }
        var pdfBtn = document.getElementById('pdfBtn'); if (pdfBtn) { pdfBtn.innerHTML = on ? '&#11015; Download PDF' : '&#11015; Download PDF (text only)'; }
        if (remember) { try { localStorage.setItem(PAD_KEY, on ? '1' : '0'); } catch (e) { /* private window: not remembered */ } }
    }
    if (printable) {
        var asked = new URLSearchParams(window.location.search).get('pad'), stored = null;
        try { stored = localStorage.getItem(PAD_KEY); } catch (e) { /* not available */ }
        setPad(asked !== null ? asked !== '0' : stored !== '0', false);
        Array.prototype.forEach.call(document.querySelectorAll('.padsw button'), function (b) {
            b.addEventListener('click', function () { setPad(b.getAttribute('data-pad') === '1', true); });
        });
    }

    // every print is counted; the first one by HR marks the letter issued
    var logged = false;
    function logPrint() {
        if (logged || !printable) { return; }
        logged = true;
        var fd = new FormData(); fd.append('_token', token); fd.append('pad', withPad ? '1' : '0');
        if (navigator.sendBeacon) { navigator.sendBeacon(logUrl, fd); } else { var x = new XMLHttpRequest(); x.open('POST', logUrl); x.send(fd); }
    }
    var btn = document.getElementById('printBtn');
    if (btn) { btn.addEventListener('click', function () { logPrint(); window.print(); }); }
    window.addEventListener('beforeprint', logPrint);
})();
</script>
</body>
</html>

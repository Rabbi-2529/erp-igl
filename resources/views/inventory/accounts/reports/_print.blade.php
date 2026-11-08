{{--
    Printed look of the Accounts pages, in the same design as the sales invoice (reports/sell_print):
    logo, address and contact on the left, the title big on the right, an info table, grey table headers,
    signature lines and the "Powered By" footer. Nothing of this shows on screen.
    Pages pass: printTitle (default: the page title), printInfo (['Label' => 'value'] for the left column)
    and printPreparedBy (default: who prints).
--}}
@php
    $pCompany = \App\Models\Admin_user::company_info(auth()->user()->au_company_id);
    $pTitle = $printTitle ?? trim($__env->yieldContent('title'));
    $pInfo = array_filter($printInfo ?? [], function ($v) { return $v !== null && $v !== ''; });
    $pAddress = $pCompany->au_address ?? auth()->user()->au_address;
    $pContact = array_filter([
        ($pCompany->au_mobile ?? null) ? 'Mobile: ' . $pCompany->au_mobile : null,
        ($pCompany->au_email ?? null) ? 'Email: ' . $pCompany->au_email : null,
    ]);
@endphp
<style>
    .erp-print-head, .erp-print-foot, .rep-print-only { display: none; }
    @media print {
        @page { size: A4; margin: 5mm 15mm !important; }
        html, body { background: #ffffff !important; }
        body { font-family: 'Helvetica', 'Arial', sans-serif !important; font-size: 12px; color: #1a1a1a !important; line-height: 1.4; }

        /* screen parts left off the paper */
        .main-header, .main-sidebar, .main-footer, .page-loader-wrapper, .se-pre-con, .activity-btn, .activity-sidebar, .activity-sidebar-overlay,
        .rep-head, .rep-links, .rep-filter, .rep-noprint, .modal, .modal-backdrop, .alert, .btn, .select2-container,
        .content input, .content select, .content textarea, .content form > .recon-bar { display: none !important; }
        .wrapper, .content-wrapper, .right-side, .content { margin: 0 !important; padding: 0 !important; min-height: 0 !important; background: #ffffff !important; }
        .content > br, .content-wrapper > br { display: none; }
        .erp-table-scroll, .table-responsive { overflow: visible !important; border: 0 !important; }
        .row { margin: 0 !important; }
        .row > [class*="col-"] { float: none !important; width: 100% !important; left: auto !important; right: auto !important; padding: 0 !important; }
        a, a:visited { color: inherit !important; text-decoration: none !important; }
        a[href]:after, abbr[title]:after { content: none !important; }
        .fa { display: none !important; }

        .erp-print-head, .erp-print-foot { display: block !important; }
        th.rep-print-only, td.rep-print-only { display: table-cell !important; }
        tr.rep-print-only { display: table-row !important; }
        div.rep-print-only, p.rep-print-only { display: block !important; }
        span.rep-print-only { display: inline !important; }

        /* header, as on the invoice */
        .ph-top { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 6px; margin-bottom: 6px; }
        .ph-company { flex: 1; }
        .ph-logo { display: block; max-height: 60px; max-width: 200px; margin-bottom: 4px; }
        .ph-address, .ph-contact { font-size: 10px; color: #555 !important; margin-top: 2px; font-weight: bold; line-height: 1.4; }
        .ph-title { font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 3px; color: #1a1a1a !important; text-align: right; max-width: 55%; line-height: 1.2; }
        .ph-info { width: 100%; border-collapse: collapse; margin: 5px 0 8px; font-size: 11px; }
        .ph-info td { padding: 3px 5px !important; vertical-align: top; font-weight: bold; border: 0 !important; background: none !important; }
        .ph-info .ph-left { width: 55%; padding-right: 10px !important; }
        .ph-info .ph-right { width: 45%; padding-left: 10px !important; }
        .ph-label { color: #555 !important; display: inline-block; min-width: 95px; }

        /* summary cards: boxed like the invoice's balance box */
        .rep-cards { display: flex !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 0 8px !important; }
        .rep-card { flex: 1 1 0 !important; padding: 5px 8px !important; border: 1px solid #1a1a1a !important; border-radius: 4px !important; background: #f8f9fa !important;
            -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .rep-card b { font-size: 13px !important; color: #1a1a1a !important; }
        .rep-card span { font-size: 9.5px !important; color: #555 !important; font-weight: bold; }
        .rep-card a { display: none !important; }

        /* sections */
        .panel { border: 0 !important; box-shadow: none !important; margin: 0 0 8px !important; background: none !important; page-break-inside: auto; }
        .panel::before, .panel::after, .panel-heading::before, .panel-heading::after, .panel-bd::before, .panel-bd::after { display: none !important; }
        .panel-heading { padding: 0 !important; border: 0 !important; background: none !important; }
        .panel-heading .panel-title { display: block !important; float: none !important; width: 100% !important; }
        .panel-heading h4, .panel-title h4 { display: block !important; width: 100% !important; font-family: 'Helvetica', 'Arial', sans-serif !important; font-size: 11px !important;
            font-weight: bold !important; text-transform: uppercase; letter-spacing: .5px; color: #1a1a1a !important;
            border-bottom: 1px solid #1a1a1a; padding: 0 0 2px !important; margin: 8px 0 4px !important; }
        .panel-heading h4::before, .panel-heading h4::after, .panel-title::before, .panel-title::after { display: none !important; }
        .content h1, .content h2, .content h3, .content h4, .content h5, .rep-day, .rep-card b { font-family: 'Helvetica', 'Arial', sans-serif !important; }
        .panel-body { padding: 0 !important; }

        /* tables, as on the invoice */
        .content table.table, .content table.rep-table { width: 100% !important; border-collapse: collapse !important; font-size: 10.5px !important; margin: 0 0 6px !important; }
        .content table.table > thead > tr > th, .content table.rep-table > thead > tr > th {
            background: #f0f0f0 !important; color: #1a1a1a !important; text-transform: uppercase; letter-spacing: .3px; font-size: 9.5px !important; font-weight: bold !important;
            text-align: center !important; padding: 4px !important; border: 1px solid #1a1a1a !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .content table.table > tbody > tr > td, .content table.table > tbody > tr > th, .content table.table > tfoot > tr > td {
            padding: 4px !important; border: 1px solid #ddd !important; vertical-align: top; font-weight: bold; font-size: 10.5px !important; color: #1a1a1a !important; background: #ffffff !important; }
        .content table.table-striped > tbody > tr:nth-of-type(odd) > td { background: #ffffff !important; }
        .content table.table-striped > tbody > tr:nth-of-type(even) > td { background: #f8f9fa !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .content table td.num, .content table th.num { text-align: right !important; }
        .rep-sign-cell { height: 30px; }
        .content table th.num { text-align: center !important; }
        .content table tbody tr { page-break-inside: avoid; }
        .rep-table tr.rep-sub-total td, .rep-table tr.rep-sub-total th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .rep-table tr.rep-grand td, .rep-table tr.rep-grand th { border-top: 2px double #1a1a1a !important; font-size: 11px !important; background: #ffffff !important; }
        .rep-table tr.rep-group th { background: #ffffff !important; border: 0 !important; border-bottom: 1px solid #1a1a1a !important; text-align: left !important; font-size: 9.5px !important; padding-top: 8px !important; }
        .rep-neg, .rep-table .rep-neg { color: #c0392b !important; }
        .rep-pos, .rep-table .rep-pos { color: #1a1a1a !important; }
        .content .text-muted, .rep-note, .rep-sub { color: #555 !important; }
        .rep-note { font-size: 9.5px !important; font-weight: bold; margin: 4px 0 0 !important; padding: 4px 10px; background: #f8f9fa !important; border: 1px solid #e0e0e0;
            -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .rep-empty { padding: 10px 0 !important; color: #555 !important; }
        .rep-day { font-size: 11px !important; margin: 8px 0 3px !important; color: #1a1a1a !important; }
        .label { border: 0 !important; color: #1a1a1a !important; background: none !important; padding: 0 !important; font-size: 10px !important; text-transform: uppercase; }
        canvas { max-width: 100% !important; }

        /* signatures and footer, as on the invoice */
        .ph-signatures { margin: 28px 0 4px; display: flex; justify-content: space-between; align-items: flex-end; gap: 30px; page-break-inside: avoid; }
        .ph-sign { text-align: center; width: 30%; }
        .ph-sign .ph-name { font-weight: bold; font-size: 10px; min-height: 14px; color: #1a1a1a !important; }
        .ph-sign .ph-line { border-top: 1px solid #1a1a1a; padding-top: 4px; font-size: 10.5px; font-weight: bold; color: #1a1a1a !important; }
        .ph-footer { margin-top: 6px; padding-top: 5px; border-top: 1px solid #ddd; text-align: center; font-size: 9.5px; color: #888 !important; font-weight: bold; }
    }
</style>

<div class="erp-print-head">
    <div class="ph-top">
        <div class="ph-company">
            <img class="ph-logo" src="{{ asset('assets/img/icons/logo.png') }}" alt="{{ $pCompany->au_company_name ?? 'Logo' }}">
            @if($pAddress)<div class="ph-address">{{ $pAddress }}</div>@endif
            @if($pContact)<div class="ph-contact">{{ implode(' | ', $pContact) }}</div>@endif
        </div>
        <div class="ph-title">{{ $pTitle }}</div>
    </div>
    <table class="ph-info">
        <tr>
            <td class="ph-left">
                @foreach($pInfo as $label => $value)
                    <div><span class="ph-label">{{ $label }}:</span> {{ $value }}</div>
                @endforeach
            </td>
            <td class="ph-right">
                <div><span class="ph-label">Branch:</span> {{ auth()->user()->au_branch_name }}</div>
                <div><span class="ph-label">Printed on:</span> {{ now()->format('d M Y, h:i A') }}</div>
                <div><span class="ph-label">Printed by:</span> {{ auth()->user()->au_name }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="erp-print-foot" id="erpPrintFoot">
    <div class="ph-signatures">
        <div class="ph-sign"><div class="ph-name">{{ $printPreparedBy ?? auth()->user()->au_name }}</div><div class="ph-line">Prepared By</div></div>
        <div class="ph-sign"><div class="ph-name"></div><div class="ph-line">Checked By</div></div>
        <div class="ph-sign"><div class="ph-name"></div><div class="ph-line">Approved By</div></div>
    </div>
    <div class="ph-footer">
        Powered By: {{ isset($pCompany->au_footer_text) ? $pCompany->au_footer_text : 'IGL Web Ltd.' }} | {{ isset($pCompany->au_contact) ? $pCompany->au_contact : '+880-1958-666999' }}
    </div>
</div>
<script>
    // the signatures and footer go at the end of the page
    document.addEventListener('DOMContentLoaded', function () {
        var foot = document.getElementById('erpPrintFoot'), content = foot && foot.closest('.content');
        if (content) { content.appendChild(foot); }
    });
    // charts: redraw at the paper width with full bars (no grow animation half way on the printout)
    (function () {
        function settle() {
            if (!window.Chart || !Chart.instances) { return; }
            Object.keys(Chart.instances).forEach(function (k) {
                var c = Chart.instances[k];
                try { c.stop(); c.resize(); c.update(0); } catch (e) {}
            });
        }
        window.addEventListener('beforeprint', settle);
        window.addEventListener('afterprint', settle);
        if (window.matchMedia) {
            var mq = window.matchMedia('print');
            var on = function (e) { settle(); };
            if (mq.addEventListener) { mq.addEventListener('change', on); } else if (mq.addListener) { mq.addListener(on); }
        }
    })();
</script>

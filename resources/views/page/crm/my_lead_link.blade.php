@extends('layouts.master')
@section('title', 'My Lead Link')

@section('customer_relation_class', 'active')
@section('my_lead_link_class', 'active')

@section('content')
<style>
    .lf-link { display: flex; gap: 6px; flex-wrap: wrap; }
    .lf-link input { flex: 1 1 260px; min-width: 0; font-family: Consolas, monospace; }
    .lf-share { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .lf-share .wa { color: #ffffff !important; background: #1f9d55; border-color: #1a8a4a; }
    .lf-share .fb { color: #ffffff !important; background: #1877f2; border-color: #1466d1; }
    .lf-note { color: var(--erp-muted); margin: 10px 0 0; }
    .lf-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
    .lf-stat { flex: 1 1 140px; padding: 12px 14px; border: 1px solid var(--erp-border); border-radius: 6px; background: var(--erp-white); }
    .lf-stat b { display: block; font-size: 24px; line-height: 1.1; color: var(--erp-text); }
    .lf-stat span { color: var(--erp-muted); }
    .lf-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; }
    .lf-badge.new { color: #ffffff; background: var(--erp-green-btn); }
    .lf-badge.duplicate { color: var(--erp-text); background: var(--erp-border); }
</style>

@include('lc._alerts')

<div class="panel panel-bd">
    <div class="panel-heading">
        <div class="panel-title"><h4><i class="fa fa-share-alt"></i> My lead link</h4></div>
    </div>
    <div class="panel-body">
        <div class="lf-link">
            <input type="text" class="form-control" id="lfLink" value="{{ $link }}" readonly aria-label="My lead form link">
            <button type="button" class="btn btn-success" id="lfCopy"><i class="fa fa-copy"></i> Copy</button>
            <a href="{{ $link }}" target="_blank" rel="noopener" class="btn btn-default"><i class="fa fa-external-link"></i> Open</a>
        </div>
        <div class="lf-share">
            <a class="btn wa" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($form->clf_title . ' - ' . $link) }}"><i class="fa fa-whatsapp"></i> Share on WhatsApp</a>
            <a class="btn fb" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($link) }}"><i class="fa fa-facebook"></i> Share on Facebook</a>
        </div>
        <p class="lf-note">
            This link is only yours. Anyone who sends the form through it becomes <strong>your customer</strong> in the
            CRM (My Customers), with their district, thana, address and the reason they chose. If the mobile number is
            already in the CRM, no second lead is made; it is listed below as a repeat request.
            @unless($form->clf_active)
                <br><strong class="text-danger">The company has switched the lead form off for now: visitors see a "closed" message.</strong>
            @endunless
        </p>
    </div>
</div>

<div class="lf-stats">
    <div class="lf-stat"><b>{{ $stats['today'] }}</b><span>My new leads today</span></div>
    <div class="lf-stat"><b>{{ $stats['month'] }}</b><span>This month</span></div>
    <div class="lf-stat"><b>{{ $stats['total'] }}</b><span>In total</span></div>
    <div class="lf-stat"><b>{{ $stats['repeats'] }}</b><span>Repeat requests</span></div>
</div>

<div class="panel panel-bd">
    <div class="panel-heading">
        <div class="panel-title"><h4><i class="fa fa-inbox"></i> Requests from my link</h4></div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr><th>Time</th><th>Name</th><th>Mobile</th><th>Reason</th><th>District / Thana</th><th>Lead</th></tr>
                </thead>
                <tbody>
                    @forelse($recent as $row)
                        <tr>
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($row->cls_created_at)->format('d M, h:i A') }}</td>
                            <td>{{ $row->cls_name }}</td>
                            <td>{{ $row->cls_mobile }}</td>
                            <td>{{ $row->reason_name }}</td>
                            <td>{{ $row->place }}</td>
                            <td>
                                @if($row->cls_status === 'new' && $row->cls_qb_id)
                                    <a href="{{ url('client_feedback/' . $row->cls_qb_id) }}"><span class="lf-badge new">My new customer</span></a>
                                @else
                                    <span class="lf-badge duplicate" title="Already in the CRM{{ $row->given_to ? ' (' . $row->given_to . ')' : '' }}">Already in CRM</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No requests yet. Share your link to start getting customers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <hr>
        <form method="POST" action="{{ route('crm.my-lead-link.new') }}" onsubmit="return confirm('Make a new link? Your old link will stop working.');">
            @csrf
            <button type="submit" class="btn btn-default"><i class="fa fa-refresh"></i> Make a new link</button>
            <span class="lf-note" style="margin-left: 8px;">Only if your link was shared by mistake: the old link stops working.</span>
        </form>
    </div>
</div>
@endsection

@section('custom_script')
<script>
document.getElementById('lfCopy').addEventListener('click', function () {
    var input = document.getElementById('lfLink'), btn = this;
    var done = function () { btn.innerHTML = '<i class="fa fa-check"></i> Copied'; setTimeout(function () { btn.innerHTML = '<i class="fa fa-copy"></i> Copy'; }, 1800); };
    if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(input.value).then(done); }
    else { input.select(); document.execCommand('copy'); done(); }
});
</script>
@endsection

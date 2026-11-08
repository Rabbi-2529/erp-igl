@extends('layouts.master')
@section('title', 'Auto Lead Form')

@section('customer_relation_class', 'active')
@section('customer_class', 'active')
@section('lead_form_class', 'active')

@section('content')
<style>
    .lf-link { display: flex; gap: 6px; flex-wrap: wrap; }
    .lf-link input { flex: 1 1 260px; min-width: 0; font-family: Consolas, monospace; }
    .lf-note { color: var(--erp-muted); margin: 8px 0 0; }
    .lf-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
    .lf-stat { flex: 1 1 140px; padding: 12px 14px; border: 1px solid var(--erp-border); border-radius: 6px; background: var(--erp-white); }
    .lf-stat b { display: block; font-size: 24px; line-height: 1.1; color: var(--erp-text); }
    .lf-stat span { color: var(--erp-muted); }
    .lf-people { display: flex; flex-wrap: wrap; gap: 6px 18px; max-height: 220px; overflow-y: auto; padding: 8px 10px; border: 1px solid var(--erp-border); border-radius: 4px; }
    .lf-people label { font-weight: 400; margin: 0; cursor: pointer; min-width: 180px; }
    .lf-people input { margin-right: 6px; }
    .lf-switch { font-weight: 700; cursor: pointer; }
    .lf-switch input { margin-right: 6px; transform: scale(1.2); }
    .lf-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; }
    .lf-badge.new { color: #ffffff; background: var(--erp-green-btn); }
    .lf-badge.duplicate { color: var(--erp-text); background: var(--erp-border); }
</style>

@include('lc._alerts')

<div class="row">
    <div class="col-md-7">
        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="panel-title"><h4><i class="fa fa-link"></i> Your lead form link</h4></div>
            </div>
            <div class="panel-body">
                <div class="lf-link">
                    <input type="text" class="form-control" id="lfLink" value="{{ $link }}" readonly aria-label="Lead form link">
                    <button type="button" class="btn btn-success" id="lfCopy"><i class="fa fa-copy"></i> Copy</button>
                    <a href="{{ $link }}" target="_blank" rel="noopener" class="btn btn-default"><i class="fa fa-external-link"></i> Open</a>
                </div>
                <p class="lf-note">
                    The branch link for your website or Facebook page: its leads are shared between the people you
                    choose on the right. Every employee also has a personal link (CRM &gt; My Lead Link): leads from it
                    become that employee's own customers. A mobile number that is already in the CRM never makes a
                    second lead; it is listed below as a repeat request. The form shows the FelnaTech logo and can be read
                    in বাংলা or English; a mobile number must be a valid Bangladeshi number before it can be sent.
                    @unless($form->clf_active)
                        <br><strong class="text-danger">The form is switched off: visitors see a "not accepting requests" message.</strong>
                    @endunless
                </p>
            </div>
        </div>

        <div class="lf-stats">
            <div class="lf-stat"><b>{{ $stats['today'] }}</b><span>New leads today</span></div>
            <div class="lf-stat"><b>{{ $stats['month'] }}</b><span>New leads this month</span></div>
            <div class="lf-stat"><b>{{ $stats['total'] }}</b><span>New leads in total</span></div>
            <div class="lf-stat"><b>{{ $stats['repeats'] }}</b><span>Repeat requests</span></div>
        </div>

        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="panel-title"><h4><i class="fa fa-inbox"></i> Latest requests (all links of this branch)</h4></div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr><th>Time</th><th>Name</th><th>Mobile</th><th>Reason</th><th>District / Thana</th><th>Lead</th><th>Given to</th></tr>
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
                                        @if($row->cls_qb_id)
                                            <a href="{{ url('client_feedback/' . $row->cls_qb_id) }}"><span class="lf-badge {{ $row->cls_status }}">{{ $row->cls_status === 'new' ? 'New lead' : 'Already in CRM' }}</span></a>
                                        @else
                                            <span class="lf-badge {{ $row->cls_status }}">{{ $row->cls_status === 'new' ? 'New lead' : 'Already in CRM' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->given_to }}@if($row->cls_link_id) <small class="text-muted">(own link)</small>@endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No requests yet. Share the link to start getting leads.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="panel-title"><h4><i class="fa fa-users"></i> Employee links</h4></div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>Employee</th><th class="text-right">New leads this month</th><th class="text-right">In total</th><th>Link</th></tr></thead>
                        <tbody>
                            @forelse($employeeLinks as $el)
                                <tr>
                                    <td>{{ $linkOwners[$el->cll_user_id] ?? ('#' . $el->cll_user_id) }}</td>
                                    <td class="text-right">{{ (int) optional($linkCounts->get($el->cll_id))->month }}</td>
                                    <td class="text-right">{{ (int) optional($linkCounts->get($el->cll_id))->total }}</td>
                                    <td><a href="{{ url('lead-form/' . $el->cll_token) }}" target="_blank" rel="noopener">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No employee has opened "My Lead Link" yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="panel-title"><h4><i class="fa fa-cog"></i> Form settings</h4></div>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('crm.lead-form.save') }}">
                    @csrf
                    <div class="form-group">
                        <label class="lf-switch"><input type="checkbox" name="clf_active" value="1" {{ $form->clf_active ? 'checked' : '' }}> Form is on (accepting requests)</label>
                    </div>
                    <div class="form-group">
                        <label for="clf_lang">Form opens in</label>
                        <select class="form-control dont-select-me" id="clf_lang" name="clf_lang">
                            <option value="bn" @selected(old('clf_lang', $form->clf_lang) !== 'en')>বাংলা (Bangla)</option>
                            <option value="en" @selected(old('clf_lang', $form->clf_lang) === 'en')>English</option>
                        </select>
                        <p class="lf-note">Visitors can switch between বাংলা and English at the top of the form.</p>
                    </div>
                    <div class="form-group">
                        <label for="clf_find_us">Lead source ("How did you find us")</label>
                        <select class="form-control" id="clf_find_us" name="clf_find_us" required>
                            @foreach($sources as $id => $name)
                                <option value="{{ $id }}" @selected((int) old('clf_find_us', $form->clf_find_us) === (int) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Give branch-link leads to (in turn)</label>
                        @if($people->isEmpty())
                            <p class="lf-note">No active staff in this branch. New leads go to you (the admin).</p>
                        @else
                            <div class="lf-people">
                                @foreach($people as $person)
                                    <label><input type="checkbox" name="assignees[]" value="{{ $person->au_id }}" {{ in_array((int) $person->au_id, $assigned, true) ? 'checked' : '' }}>{{ $person->au_name }}</label>
                                @endforeach
                            </div>
                            <p class="lf-note">Leads from the branch link are shared one by one between the people you tick (tick nobody to get them yourself). Leads from an employee's own link always go to that employee. Language, source and the on/off switch apply to every link.</p>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                </form>

                <hr>
                <form method="POST" action="{{ route('crm.lead-form.new-link') }}" onsubmit="return confirm('Make a new link? The old link will stop working.');">
                    @csrf
                    <button type="submit" class="btn btn-default"><i class="fa fa-refresh"></i> Make a new link</button>
                    <p class="lf-note">Use this if the link was shared by mistake. The old link stops working.</p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_script')
<script>
document.getElementById('lfCopy').addEventListener('click', function () {
    var input = document.getElementById('lfLink'), btn = this;
    var done = function () { btn.innerHTML = '<i class="fa fa-check"></i> Copied'; setTimeout(function () { btn.innerHTML = '<i class="fa fa-copy"></i> Copy'; }, 1800); };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value).then(done);
    } else {
        input.select(); document.execCommand('copy'); done();
    }
});
</script>
@endsection

{{-- Shared look of the HRM letter pages, and the links between them --}}
<style>
    .lt-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
    .lt-head h3 { margin: 0; font-size: 20px; color: var(--erp-text); }
    .lt-head .lt-sub { margin: 2px 0 0; color: var(--erp-muted); }
    .lt-links { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
    .lt-links a.active { color: var(--erp-on-primary); background: var(--erp-green-btn); border-color: var(--erp-green-btn); }
    .lt-table { margin-bottom: 0; }
    .lt-table td, .lt-table th { vertical-align: middle !important; }
    .lt-note { color: var(--erp-muted); font-size: 13px; margin: 10px 0 0; }
    .lt-tag { display: inline-block; margin: 1px 4px 1px 0; padding: 0 7px; border-radius: 9px; font-size: 11px; line-height: 18px; border: 1px solid var(--erp-border); color: var(--erp-muted); white-space: nowrap; }
    .lt-tag.on { border-color: #0b7a3b; color: #0b7a3b; }
    .lt-tag.off { border-color: #c4161f; color: #c4161f; }
    html[data-theme="dark"] .lt-tag.on { border-color: #6fd79a; color: #6fd79a; }
    html[data-theme="dark"] .lt-tag.off { border-color: #f58b90; color: #f58b90; }
    .lt-prefix { font-family: Consolas, monospace; font-weight: 700; letter-spacing: .5px; }
    .lt-check { display: flex; gap: 8px; align-items: flex-start; font-weight: 400; cursor: pointer; margin: 4px 0; }
    .lt-check input { margin-top: 3px; }
    .lt-checks { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 2px 12px; }
    .lt-thumb { max-height: 40px; max-width: 120px; background: #fff; border: 1px solid var(--erp-border); padding: 2px; border-radius: 3px; }
    .content .text-muted { color: var(--erp-muted) !important; }
    .content .label-success { background-color: #0b7a3b; color: #fff; }
    .content .label-default { background-color: #5f6b66; color: #fff; }
    .content .label-warning { background-color: #9a5b00; color: #fff; }
    .content .label-info { background-color: #1f6f8b; color: #fff; }
    .content .label-primary { background-color: #1c5cab; color: #fff; }
    .content .label-danger { background-color: #b3261e; color: #fff; }
    .content .alert-success { background: #e3f4ea; border-color: #9fd4b4; color: #0b4f27; }
    .content .alert-info { background: #e4f1f7; border-color: #9cc9de; color: #134a5e; }
    .content .alert-warning { background: #fff4d6; border-color: #f0c36d; color: #5c4000; }
    .content .alert-danger { background: #fbe5e3; border-color: #eba59f; color: #7a1a14; }
    html[data-theme="dark"] .content .alert-success { background: #12351f; border-color: #2e6b45; color: #bfe9cd; }
    html[data-theme="dark"] .content .alert-info { background: #10303d; border-color: #2c6177; color: #bfe2f0; }
    html[data-theme="dark"] .content .alert-warning { background: #3a2c08; border-color: #7a5b14; color: #f5dfa0; }
    html[data-theme="dark"] .content .alert-danger { background: #3d1513; border-color: #7d2c26; color: #f6c2bd; }
</style>
@php
    $ltH = \App\Services\HrmLetters::forUser();
    $ltFull = $ltH->isHrExecutive() || $ltH->canApprove();
    $ltHead = \Illuminate\Support\Facades\DB::table('admin_user')->where('au_company_id', $ltH->company)->where('au_dep_head_id', auth()->user()->au_id)->exists();
    $ltLinks = [];
    if ($ltFull || $ltHead) { $ltLinks[] = ['hrm.letters.index', 'fa-envelope-o', 'Letters', false]; }
    if ($ltH->isHrExecutive()) { $ltLinks[] = ['hrm.letters.create', 'fa-plus', 'Generate Letter', false]; }
    if ($ltH->isHrExecutive() && \App\Services\HrmLetterRequests::ready()) {
        $ltWaiting = (new \App\Services\HrmLetterRequests($ltH))->pendingCount();
        $ltLinks[] = ['hrm.letters.requests', 'fa-inbox', 'Requests' . ($ltWaiting ? ' (' . $ltWaiting . ')' : ''), false];
    }
    $ltLinks[] = ['hrm.letters.my', 'fa-user', 'My Letters', false];
    if ($ltFull) {
        foreach ([['hrm.letters.types', 'fa-tags', 'Letter Types'], ['hrm.letters.templates', 'fa-file-word-o', 'Templates'], ['hrm.letters.letterhead', 'fa-file-text-o', 'Letterhead PAD'],
                  ['hrm.letters.signatories', 'fa-pencil', 'Signatories'], ['hrm.letters.access', 'fa-user-secret', 'Access']] as [$r, $i, $t]) {
            $ltLinks[] = [$r, $i, $t, true];
        }
    }
@endphp
<div class="lt-links">
    @foreach($ltLinks as [$routeName, $icon, $label, $setup])
        <a href="{{ route($routeName) }}" class="btn btn-default btn-sm {{ request()->routeIs($routeName) || (!in_array($routeName, ['hrm.letters.index', 'hrm.letters.create', 'hrm.letters.my'], true) && request()->routeIs($routeName . '.*')) || ($routeName === 'hrm.letters.index' && request()->routeIs('hrm.letters.show')) ? 'active' : '' }}" @if($setup && $loop->index > 0 && !$ltLinks[$loop->index - 1][3]) style="margin-left: 10px;" @endif><i class="fa {{ $icon }}"></i> {{ $label }}</a>
    @endforeach
</div>

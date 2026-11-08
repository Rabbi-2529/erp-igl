@extends('layouts.master')
@section('title', 'Letter Signatories')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')

<div class="lt-head">
    <div>
        <h3>Signatories</h3>
        <p class="lt-sub">The people who sign letters: their name and designation print under the signature. A signatory linked to an ERP user can also approve letters.</p>
    </div>
    @if($isAdmin)
        <button type="button" class="btn btn-success sig-open" data-id=""><i class="fa fa-plus"></i> Add a signatory</button>
    @endif
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        @if($list->isEmpty())
            <p class="text-center text-muted" style="padding: 24px 0;">No signatories yet. Add the person who signs HR letters (for example the HR Manager or the Managing Director).</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped lt-table">
                    <thead><tr><th>Name</th><th>Designation</th><th>Signature</th><th>ERP user</th><th>Branch</th><th>Approves</th><th>Status</th>@if($isAdmin)<th></th>@endif</tr></thead>
                    <tbody>
                        @foreach($list as $s)
                            <tr>
                                <td><b>{{ $s->hls_name }}</b>@if($s->hls_is_default) <span class="lt-tag on">default</span>@endif</td>
                                <td>{{ $s->hls_designation }}</td>
                                <td>@if($s->hls_signature)<img src="{{ \App\Services\HrmLetters::imageUrl($s->hls_signature) }}" alt="Signature of {{ $s->hls_name }}" class="lt-thumb">@else<span class="text-muted">none (signs by hand)</span>@endif</td>
                                <td>{{ $s->au_name ?: '-' }}@if($s->au_user_id)<br><small class="text-muted">ID {{ $s->au_user_id }}</small>@endif</td>
                                <td>{{ $s->hls_branch_id ? ($branches[$s->hls_branch_id] ?? '#' . $s->hls_branch_id) : 'Every branch' }}</td>
                                <td>@if($s->hls_can_approve)<span class="lt-tag on">Yes</span>@else<span class="text-muted">No</span>@endif</td>
                                <td>@if($s->hls_status)<span class="label label-success">Active</span>@else<span class="label label-default">Inactive</span>@endif</td>
                                @if($isAdmin)
                                    <td><button type="button" class="btn btn-default btn-xs sig-open" data-id="{{ $s->hls_id }}" data-name="{{ $s->hls_name }}" data-designation="{{ $s->hls_designation }}"
                                        data-user="{{ $s->hls_user_id }}" data-branch="{{ $s->hls_branch_id }}" data-approve="{{ $s->hls_can_approve }}" data-default="{{ $s->hls_is_default }}"
                                        data-status="{{ $s->hls_status }}" data-signature="{{ $s->hls_signature ? 1 : 0 }}"><i class="fa fa-pencil"></i> Edit</button></td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <p class="lt-note">
            The default signatory is chosen first on new letters. A signature image is optional; without one, a space is left to sign by hand.
            @unless($isAdmin) Only an HR Admin can change signatories. @endunless
        </p>
    </div>
</div>

@if($isAdmin)
<div class="modal fade" id="sigModal" tabindex="-1" role="dialog" aria-labelledby="sigTitle">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('hrm.letters.signatories.store') }}" enctype="multipart/form-data" class="modal-content" id="sigForm">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="sigTitle">Add a signatory</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 form-group"><label for="sig_name">Name</label><input type="text" class="form-control" id="sig_name" name="name" maxlength="100" value="{{ old('name') }}" required></div>
                    <div class="col-sm-6 form-group"><label for="sig_designation">Designation</label><input type="text" class="form-control" id="sig_designation" name="designation" maxlength="100" value="{{ old('designation') }}" placeholder="e.g. Head of HR" required></div>
                </div>
                <div class="form-group">
                    <label for="sig_user">ERP user (needed to approve letters)</label>
                    <select class="form-control" id="sig_user" name="user_id" style="width: 100%;">
                        <option value="">Not linked</option>
                        @foreach($employees as $e)<option value="{{ $e->au_id }}" @selected(old('user_id') == $e->au_id)>{{ $e->au_name }}{{ $e->au_user_id ? ' (' . $e->au_user_id . ')' : '' }}{{ $e->au_branch_name ? ' - ' . $e->au_branch_name : '' }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="sig_branch">Signs for</label>
                    <select class="form-control dont-select-me" id="sig_branch" name="branch">
                        <option value="0">Every branch</option>
                        @foreach($branches as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="sig_signature">Signature image (optional)</label>
                    <input type="file" id="sig_signature" name="signature" accept="image/png,image/jpeg,image/webp">
                    <label class="lt-check" id="sig_remove_wrap" style="display: none;"><input type="checkbox" name="remove_signature" value="1"> Remove the current signature image</label>
                </div>
                <label class="lt-check"><input type="hidden" name="can_approve" value="0"><input type="checkbox" name="can_approve" value="1" id="sig_approve"> Can approve letters</label>
                <label class="lt-check"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" id="sig_default"> Default signatory</label>
                <label class="lt-check"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" id="sig_status" checked> Active</label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('custom_script')
<script>
(function ($) {
    var storeUrl = {!! json_encode(route('hrm.letters.signatories.store')) !!};
    var updateUrl = {!! json_encode(route('hrm.letters.signatories.update', 0)) !!};
    $(document).on('click', '.sig-open', function () {
        var b = $(this), id = b.data('id');
        $('#sigForm').attr('action', id ? updateUrl.replace(/0$/, id) : storeUrl);
        $('#sigTitle').text(id ? 'Edit ' + b.data('name') : 'Add a signatory');
        $('#sig_name').val(id ? b.data('name') : '');
        $('#sig_designation').val(id ? b.data('designation') : '');
        $('#sig_user').val(id ? (b.data('user') || '') : '').trigger('change');
        $('#sig_branch').val(id ? (b.data('branch') || 0) : 0);
        $('#sig_approve').prop('checked', id ? String(b.data('approve')) === '1' : false);
        $('#sig_default').prop('checked', id ? String(b.data('default')) === '1' : false);
        $('#sig_status').prop('checked', id ? String(b.data('status')) === '1' : true);
        $('#sig_remove_wrap').toggle(!!id && String(b.data('signature')) === '1');
        $('#sig_signature').val('');
        $('#sigModal').modal('show');
    });
    @if(($errors->any() || session('errmsg')) && old('name'))
        $('#sigModal').modal('show');
    @endif
})(jQuery);
</script>
@endsection

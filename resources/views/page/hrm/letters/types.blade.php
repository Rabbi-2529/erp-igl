@extends('layouts.master')
@section('title', 'Letter Types')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')
@php
    $example = 'PREFIX' . now()->format('Ymd') . 'XXX';
@endphp

<div class="lt-head">
    <div>
        <h3>Letter Types</h3>
        <p class="lt-sub">Each letter type has its own reference prefix: <span class="lt-prefix">{{ $example }}</span>, where XXX is a random number from 100 to 999 that is never used twice.</p>
    </div>
    @if($isAdmin)
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#typeAdd"><i class="fa fa-plus"></i> Add a letter type</button>
    @endif
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped lt-table">
                <thead>
                    <tr><th style="width: 40px;">#</th><th>Letter type</th><th>Prefix</th><th>Reference example</th><th>Default subject</th><th>Asks for</th><th>Review</th><th class="text-right">Letters</th><th>Status</th>@if($isAdmin)<th></th>@endif</tr>
                </thead>
                <tbody>
                    @foreach($types as $t)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><b>{{ $t->hlt_name }}</b>@if(!$t->hlt_code)<br><small class="text-muted">added by an admin</small>@endif</td>
                            <td><span class="lt-prefix">{{ $t->hlt_prefix }}</span></td>
                            <td style="white-space: nowrap;"><span class="lt-prefix text-muted">{{ $t->hlt_prefix }}{{ now()->format('Ymd') }}527</span></td>
                            <td>{{ $t->hlt_subject }}</td>
                            <td>
                                @forelse($t->field_list as $f)<span class="lt-tag">{{ $fields[$f][0] }}</span>@empty<span class="text-muted">-</span>@endforelse
                            </td>
                            <td>@if($t->hlt_needs_review)<span class="lt-tag on">Department head</span>@else<span class="text-muted">-</span>@endif</td>
                            <td class="text-right">{{ $used[$t->hlt_id] ?? 0 }}</td>
                            <td>@if($t->hlt_status)<span class="label label-success">On</span>@else<span class="label label-default">Off</span>@endif</td>
                            @if($isAdmin)
                                <td style="white-space: nowrap;">
                                    <button type="button" class="btn btn-default btn-xs type-edit" title="Edit"
                                        data-id="{{ $t->hlt_id }}" data-name="{{ $t->hlt_name }}" data-prefix="{{ $t->hlt_prefix }}" data-subject="{{ $t->hlt_subject }}"
                                        data-fields="{{ implode(',', $t->field_list) }}" data-review="{{ $t->hlt_needs_review }}" data-status="{{ $t->hlt_status }}" data-sort="{{ $t->hlt_sort }}"><i class="fa fa-pencil"></i> Edit</button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="lt-note">
            "Asks for" are the extra details the letter form will ask for (and the placeholders they fill, e.g. Effective date &rarr; <code>&#123;&#123;effective_date&#125;&#125;</code>).
            "Review": the employee's department head reviews the letter before it is approved.
            Changing a prefix only affects new letters; letters already made keep their reference numbers.
            @unless($isAdmin) Only an HR Admin can change letter types. @endunless
        </p>
    </div>
</div>

@if($isAdmin)
    @foreach(['add' => ['typeAdd', 'Add a letter type', route('hrm.letters.types.store')], 'edit' => ['typeEdit', 'Edit letter type', '']] as $mode => [$modalId, $title, $action])
        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Title">
            <div class="modal-dialog modal-lg" role="document">
                <form method="POST" action="{{ $action }}" class="modal-content" id="{{ $modalId }}Form">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="{{ $modalId }}Title">{{ $title }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6 form-group"><label for="{{ $mode }}_name">Name</label><input type="text" class="form-control" id="{{ $mode }}_name" name="name" maxlength="100" value="{{ $mode === 'add' ? old('name') : '' }}" required></div>
                            <div class="col-sm-3 form-group"><label for="{{ $mode }}_prefix">Prefix (2-6 letters)</label><input type="text" class="form-control lt-prefix" id="{{ $mode }}_prefix" name="prefix" maxlength="6" pattern="[A-Za-z]{2,6}" style="text-transform: uppercase;" value="{{ $mode === 'add' ? old('prefix') : '' }}" required></div>
                            <div class="col-sm-3 form-group"><label for="{{ $mode }}_sort">Order in lists</label><input type="number" class="form-control" id="{{ $mode }}_sort" name="sort" min="0" max="9999" value="{{ $mode === 'add' ? old('sort') : '' }}"></div>
                        </div>
                        <div class="form-group"><label for="{{ $mode }}_subject">Default subject</label><input type="text" class="form-control" id="{{ $mode }}_subject" name="subject" maxlength="200" value="{{ $mode === 'add' ? old('subject') : '' }}"></div>
                        <label>The letter form asks for</label>
                        <div class="lt-checks">
                            @foreach($fields as $key => [$label, $kind])
                                <label class="lt-check"><input type="checkbox" name="fields[]" value="{{ $key }}" class="{{ $mode }}-field" @if($mode === 'add' && in_array($key, old('fields', []), true)) checked @endif> {{ $label }}</label>
                            @endforeach
                        </div>
                        <label class="lt-check" style="margin-top: 10px;"><input type="hidden" name="needs_review" value="0"><input type="checkbox" name="needs_review" value="1" id="{{ $mode }}_review"> The department head reviews it before approval</label>
                        @if($mode === 'edit')
                            <label class="lt-check"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" id="edit_status"> On (can be used for new letters)</label>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endif
@endsection

@section('custom_script')
<script>
(function ($) {
    var updateUrl = {!! json_encode(route('hrm.letters.types.update', 0)) !!};
    $(document).on('click', '.type-edit', function () {
        var b = $(this), fields = String(b.data('fields') || '').split(',');
        $('#typeEditForm').attr('action', updateUrl.replace(/0$/, b.data('id')));
        $('#typeEditTitle').text('Edit ' + b.data('name'));
        $('#edit_name').val(b.data('name'));
        $('#edit_prefix').val(b.data('prefix'));
        $('#edit_subject').val(b.data('subject') || '');
        $('#edit_sort').val(b.data('sort'));
        $('.edit-field').each(function () { this.checked = fields.indexOf(this.value) >= 0; });
        $('#edit_review').prop('checked', String(b.data('review')) === '1');
        $('#edit_status').prop('checked', String(b.data('status')) === '1');
        $('#typeEdit').modal('show');
    });
    $('input[name=prefix]').on('input', function () { this.value = this.value.toUpperCase().replace(/[^A-Z]/g, ''); });
    @if(($errors->any() || session('errmsg')) && old('name'))
        $('#typeAdd').modal('show');
    @endif
})(jQuery);
</script>
@endsection

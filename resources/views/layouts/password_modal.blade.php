{{-- Change Password (opened from the header settings menu and the profile page) --}}
<div class="modal fade" id="erpPasswordModal" tabindex="-1" role="dialog" aria-labelledby="erpPasswordTitle">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 420px; width: auto;">
        <form class="modal-content" id="erpPasswordForm" autocomplete="off" novalidate>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="erpPasswordTitle"><i class="fa fa-lock"></i> Change Password</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="erpPwError" role="alert" style="display: none;"></div>
                <div class="alert alert-success" id="erpPwOk" role="status" style="display: none;"></div>

                @foreach([
                    ['old_password', 'Current password', 'current-password'],
                    ['new_password', 'New password', 'new-password'],
                    ['confirm_password', 'Confirm new password', 'new-password'],
                ] as [$field, $label, $auto])
                    <div class="form-group erp-pw-group" data-field="{{ $field }}">
                        <label for="erp_{{ $field }}">{{ $label }}</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="erp_{{ $field }}" name="{{ $field }}" autocomplete="{{ $auto }}" required>
                            <span class="input-group-btn">
                                <button class="btn btn-default erp-pw-eye" type="button" title="Show password" aria-label="Show {{ strtolower($label) }}"><i class="fa fa-eye"></i></button>
                            </span>
                        </div>
                        @if($field === 'new_password')
                            <small class="text-muted">At least 6 characters.</small>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" id="erpPwSave"><i class="fa fa-save"></i> Save password</button>
            </div>
        </form>
    </div>
</div>

<script>
(function ($) {
    var $modal = $('#erpPasswordModal'), $form = $('#erpPasswordForm'),
        $err = $('#erpPwError'), $ok = $('#erpPwOk'), $save = $('#erpPwSave');

    function reset() {
        $form[0].reset();
        $err.hide(); $ok.hide();
        $form.find('.erp-pw-group').removeClass('has-error');
        $form.find('input').attr('type', 'password');
        $form.find('.erp-pw-eye i').attr('class', 'fa fa-eye');
        $save.prop('disabled', false).html('<i class="fa fa-save"></i> Save password');
    }
    function showError(message, field) {
        $ok.hide();
        $err.text(message).show();
        $form.find('.erp-pw-group').removeClass('has-error');
        if (field) { $form.find('.erp-pw-group[data-field="' + field + '"]').addClass('has-error').find('input').focus(); }
    }

    $modal.on('show.bs.modal', reset);
    $modal.on('shown.bs.modal', function () { $('#erp_old_password').focus(); });

    $form.on('click', '.erp-pw-eye', function () {
        var $input = $(this).closest('.input-group').find('input'), show = $input.attr('type') === 'password';
        $input.attr('type', show ? 'text' : 'password');
        $(this).find('i').attr('class', show ? 'fa fa-eye-slash' : 'fa fa-eye');
        $(this).attr('title', show ? 'Hide password' : 'Show password');
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        var data = {
            old_password: $('#erp_old_password').val(),
            new_password: $('#erp_new_password').val(),
            confirm_password: $('#erp_confirm_password').val()
        };
        // quick checks here; the server checks everything again
        if (!data.old_password) { return showError('Please enter your current password.', 'old_password'); }
        if (data.new_password.length < 6) { return showError('The new password must have at least 6 characters.', 'new_password'); }
        if (data.new_password !== data.confirm_password) { return showError('The new password and its confirmation do not match.', 'confirm_password'); }

        $save.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: @json(url('change-password')),
            method: 'POST',
            data: data,
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).done(function (res) {
            $err.hide();
            $form.find('.erp-pw-group').removeClass('has-error');
            $form[0].reset();
            $ok.text(res.message || 'Password changed.').show();
            $save.html('<i class="fa fa-check"></i> Saved');
            setTimeout(function () { $modal.modal('hide'); }, 1800);
        }).fail(function (xhr) {
            var res = xhr.responseJSON || {};
            if (xhr.status === 419) { showError('Your session has expired. Please refresh the page and log in again.'); }
            else if (res.message) { showError(res.message, res.field); }
            else { showError('Could not change the password (error ' + xhr.status + '). Please try again.'); }
            $save.prop('disabled', false).html('<i class="fa fa-save"></i> Save password');
        });
    });
})(jQuery);
</script>

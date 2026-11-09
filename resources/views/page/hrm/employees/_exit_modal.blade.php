{{-- Exit employee: one pop-up for the page, filled by the button that opens it (data-exit-url, data-name, data-code, data-mobile) --}}
@if(\App\Services\EmployeeTenure::ready())
<style>
    .erp-exit-note { color: var(--erp-muted); margin-bottom: 14px; }
    #erpExitModal label { font-weight: 700; }
</style>
<div class="modal fade" id="erpExitModal" tabindex="-1" role="dialog" aria-labelledby="erpExitTitle">
    <div class="modal-dialog" role="document">
        <form method="POST" action="" class="modal-content" id="erpExitForm">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="erpExitTitle">Exit employee: <span data-exit-name></span></h4>
            </div>
            <div class="modal-body">
                <p class="erp-exit-note">Their record and all their history (attendance, salary, leave, letters ...) stay with them, and their login is turned off.
                    Their <b data-exit-free>Employee ID and mobile</b> can then be given to a new employee from <b>Add Employee</b>, who gets a new record of their own.</p>
                <div class="form-group">
                    <label for="erpExitDay">Last working day <i class="text-danger">*</i></label>
                    <input type="text" class="form-control datepicker" id="erpExitDay" name="last_day" value="{{ date('Y-m-d') }}" autocomplete="off" placeholder="yyyy-mm-dd" required>
                </div>
                <div class="form-group">
                    <label for="erpExitType">Why they left <i class="text-danger">*</i></label>
                    <select class="form-control dont-select-me" id="erpExitType" name="exit_type" required>
                        @foreach(\App\Services\EmployeeTenure::TYPES as $exitKey => $exitLabel)
                            <option value="{{ $exitKey }}">{{ $exitLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="erpExitReason">Note</label>
                    <textarea class="form-control" id="erpExitReason" name="reason" rows="2" maxlength="500" placeholder="Optional, e.g. resigned for higher studies"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fa fa-sign-out"></i> Mark as left</button>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).on('click', '[data-exit-url]', function (e) {
        e.preventDefault();
        var b = $(this), m = $('#erpExitModal'), free = [];
        if (b.attr('data-code')) { free.push('Employee ID ' + b.attr('data-code')); }
        if (b.attr('data-mobile')) { free.push('mobile ' + b.attr('data-mobile')); }
        m.find('form').attr('action', b.attr('data-exit-url'));
        m.find('[data-exit-name]').text(b.attr('data-name'));
        m.find('[data-exit-free]').text(free.length ? free.join(' and ') : 'Employee ID and mobile');
        m.find('textarea').val('');
        m.modal('show');
    });
    $(function () {
        var day = $('#erpExitDay');
        if (day.hasClass('hasDatepicker')) { day.datepicker('option', 'maxDate', 0); }
    });
</script>
@endif

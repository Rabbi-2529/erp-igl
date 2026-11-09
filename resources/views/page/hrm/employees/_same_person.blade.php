{{--
    Warns when a different person's name is typed over an employee (their whole history would show under the new name).
    Needs: $spOldName (the name saved now), $spSelector (the name field). The server checks the same rule
    (EmployeeTenure::looksLikeAnotherPerson) and refuses the change unless same_person=1 is sent.
--}}
@php($spId = 'erpSamePerson' . substr(md5($spSelector), 0, 6))
<style>
    .erp-same-person .alert { background: #fff4d6; border: 1px solid #f0c36d; color: #5c4000; }
    .erp-same-person .alert b, .erp-same-person .alert label { color: inherit; }
    html[data-theme="dark"] .erp-same-person .alert { background: #3a2c08; border-color: #7a5b14; color: #f5dfa0; }
</style>
<div id="{{ $spId }}" class="erp-same-person" style="display: none;">
    <div class="alert alert-warning" style="margin: 8px 0 0;">
        <b><i class="fa fa-exclamation-triangle"></i> This looks like a different person.</b>
        If <b data-sp-new></b> is a new employee, do not write over {{ $spOldName }}: all {{ $spOldName }}'s attendance, salary and other history would show under the new name.
        Use <b>Exit employee</b> on {{ $spOldName }} (their history stays with them) and add the new person from <b>Add Employee</b>.
        <label style="display: flex; gap: 8px; align-items: flex-start; margin: 8px 0 0; font-weight: 700; cursor: pointer;">
            <input type="checkbox" name="same_person" value="1" style="margin-top: 3px;"> Same person, I am only correcting the name
        </label>
    </div>
</div>
<script>
    (function () {
        var oldName = @json((string) $spOldName), selector = @json($spSelector), box = $('#{{ $spId }}');
        var common = @json(\App\Services\EmployeeTenure::COMMON_NAME_WORDS);
        function norm(s) { return String(s || '').toLowerCase().replace(/\s+/g, ' ').trim(); }
        function words(s) {
            var seen = {};
            return String(s || '').toLowerCase().split(/[^\p{L}]+/u).filter(function (w) {
                if (w.length < 2 || common.indexOf(w) !== -1 || seen[w]) { return false; }
                seen[w] = true;
                return true;
            });
        }
        function lev(a, b) {
            var d = [], i, j;
            for (i = 0; i <= a.length; i++) { d[i] = [i]; }
            for (j = 0; j <= b.length; j++) { d[0][j] = j; }
            for (i = 1; i <= a.length; i++) {
                for (j = 1; j <= b.length; j++) {
                    d[i][j] = Math.min(d[i - 1][j] + 1, d[i][j - 1] + 1, d[i - 1][j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
                }
            }
            return d[a.length][b.length];
        }
        // same rule as EmployeeTenure::looksLikeAnotherPerson: the two names share no word (Md, Ahmed, Islam ... do not count)
        function anotherPerson(a, b) {
            if (!norm(a) || !norm(b) || norm(a) === norm(b)) { return false; }
            var x = words(a), y = words(b), i, j;
            if (!x.length || !y.length) { return false; }
            for (i = 0; i < x.length; i++) {
                for (j = 0; j < y.length; j++) {
                    if (x[i] === y[j] || (Math.min(x[i].length, y[j].length) >= 4 && lev(x[i], y[j]) <= 1)) { return false; }
                }
            }
            return true;
        }
        function field() { return box.closest('form').find(selector).first(); }
        function check() {
            var name = String(field().val() || '').trim(), different = anotherPerson(oldName, name);
            box.find('[data-sp-new]').text(name);
            box.toggle(different);
            if (!different) { box.find('input[name="same_person"]').prop('checked', false); }
            return different;
        }
        $(function () {
            var form = box.closest('form');
            form.on('input change', selector, check);
            form.on('submit', function (e) {
                if (!check() || box.find('input[name="same_person"]').is(':checked')) { return; }
                e.preventDefault();
                e.stopImmediatePropagation();
                // the name is on a step of the wizard that is not showing: go back to it
                var step = box.closest('.setup-content');
                if (step.length && !step.is(':visible')) {
                    $('div.setup-panel div a[href="#' + step.attr('id') + '"]').removeAttr('disabled').trigger('click');
                }
                box[0].scrollIntoView({ block: 'center' });
                alert('The name now looks like a different person. Read the yellow note under the name: tick "Same person" if you are only correcting the name.');
            });
        });
    })();
</script>

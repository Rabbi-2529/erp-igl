<?php

namespace App\Http\Controllers;

use App\Models\Admin_user;
use App\Services\EmployeeTenure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * HRM > Manage Employee > Exit employee: an employee leaves (resigned, terminated ...). Their record and all their
 * history stay with them, their login is turned off, and their Employee ID and office mobile can then be given to a
 * new employee, who gets a new record of their own (Add Employee).
 */
class HrmEmployeeExitController extends Controller
{
    /** the employee, if this user may manage them (same company; other branches only for the owner and root) */
    private function employee($id)
    {
        $me = Auth::user();
        $u = Admin_user::find($id);
        abort_if(!$u, 404);
        $type = (int) $me->au_user_type;
        abort_unless($type === 1 || (int) $u->au_company_id === (int) $me->au_company_id, 403);
        abort_unless(in_array($type, [1, 3], true) || (int) $u->au_branch_id === (int) $me->au_branch_id, 403, 'You can only do this for employees of your branch.');
        return $u;
    }

    private function needTable()
    {
        abort_unless(EmployeeTenure::ready(), 404, 'Employee exits are not set up yet: run database/hrm_employee_exits.sql.');
    }

    public function store(Request $request, $id)
    {
        $this->needTable();
        $u = $this->employee($id);
        $back = redirect()->back();
        if ((int) $u->au_id === (int) Auth::id()) {
            return $back->with('error', 'You cannot mark yourself as left.');
        }
        if (in_array((int) $u->au_user_type, [1, 3, 4], true)) {
            return $back->with('error', $u->au_name . ' has a company admin account, which cannot be marked as left here.');
        }
        $v = Validator::make($request->all(), [
            'last_day'  => 'required|date_format:Y-m-d|before_or_equal:today',
            'exit_type' => 'required|in:' . implode(',', array_keys(EmployeeTenure::TYPES)),
            'reason'    => 'nullable|string|max:500',
        ], [
            'last_day.before_or_equal' => 'The last working day cannot be in the future. Record the exit on or after that day.',
            'last_day.date_format'     => 'Choose the last working day from the calendar.',
        ], ['last_day' => 'last working day', 'exit_type' => 'why they left']);
        if ($v->fails()) {
            return $back->with('error', implode(' ', $v->errors()->all()));
        }
        $lastDay = Carbon::createFromFormat('Y-m-d', $request->input('last_day'))->startOfDay();
        $joined = EmployeeTenure::joinDate($u);
        if ($joined && $lastDay->lt($joined)) {
            return $back->with('error', 'The last working day is before ' . $u->au_name . ' joined (' . $joined->format('d M Y') . ').');
        }
        $reason = trim((string) $request->input('reason'));
        try {
            EmployeeTenure::recordExit((int) $u->au_id, [
                'last_day' => $lastDay->toDateString(),
                'type'     => $request->input('exit_type'),
                'reason'   => $reason !== '' ? $reason : null,
            ], (int) Auth::id());
        } catch (\RuntimeException $e) {
            return $back->with('error', $e->getMessage());
        }
        $free = array_filter([
            trim((string) $u->au_user_id) !== '' ? 'Employee ID ' . trim($u->au_user_id) : null,
            trim((string) $u->au_mobile) !== '' ? 'mobile ' . trim($u->au_mobile) : null,
        ]);
        return $back->with('msg', $u->au_name . ' is marked as left (last working day ' . $lastDay->format('d M Y') . '). Their login is off and their history stays on their own record.'
            . ($free ? ' ' . ucfirst(implode(' and ', $free)) . ' can now be given to a new employee from Add Employee.' : ''));
    }

    /** the exit was a mistake, or the person came back */
    public function undo($id)
    {
        $this->needTable();
        $u = $this->employee($id);
        try {
            $done = EmployeeTenure::cancelExit((int) $u->au_id, (int) Auth::id(), 'Exit undone by ' . Auth::user()->au_name);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->back()->with($done ? 'msg' : 'error', $done
            ? 'Exit undone: ' . $u->au_name . ' is working again and can log in.'
            : $u->au_name . ' is not marked as left.');
    }

    /** Add Employee asks: can this office mobile and Employee ID go to the new employee? */
    public function check(Request $request)
    {
        $company = Auth::user()->au_company_id;
        return response()->json([
            'mobile' => EmployeeTenure::check($company, 'mobile', $request->input('mobile')),
            'code'   => EmployeeTenure::check($company, 'code', $request->input('code')),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\HrmLetterRequests;
use App\Services\HrmLetters;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * HRM > Letters > Requests: an employee asks for a letter from My Letters; HR makes the letter from the request
 * (then it is a normal draft) or declines it; HR Admins choose which letter types employees may ask for.
 */
class HrmLetterRequestController extends Controller
{
    private function requests()
    {
        abort_unless(HrmLetterRequests::ready(), 404, 'Letter requests are not set up yet: run database/hrm_letter_requests.sql.');
        return HrmLetterRequests::forUser();
    }

    private function request(HrmLetterRequests $q, $id)
    {
        $r = $q->find($id);
        abort_if(!$r, 404);
        return $r;
    }

    // ------------------------------------------------------------------ HR

    public function index(Request $request)
    {
        $q = $this->requests();
        abort_unless($q->letters->isHrExecutive(), 403, 'Letter requests are handled by HR Admins and HR Executives.');
        $show = in_array($request->input('show'), ['pending', 'all'], true) ? $request->input('show') : 'pending';
        $term = trim((string) $request->input('q'));
        $list = $q->query()
            ->when($show === 'pending', function ($w) { $w->where('q.hlq_status', 'pending'); })
            ->when($term !== '', function ($w) use ($term) {
                $w->where(function ($x) use ($term) {
                    $x->where('e.au_name', 'like', '%' . $term . '%')->orWhere('e.au_user_id', 'like', '%' . $term . '%')
                      ->orWhere('q.hlq_purpose', 'like', '%' . $term . '%')->orWhere('l.hl_reference', 'like', '%' . $term . '%');
                });
            })
            ->orderByRaw("q.hlq_status = 'pending' DESC")->orderByRaw('q.hlq_needed_by IS NULL, q.hlq_needed_by')->orderByDesc('q.hlq_id')
            ->limit(500)->get();
        $pending = $q->pendingCount();
        return view('page.hrm.letters.requests', compact('list', 'show', 'term', 'pending'));
    }

    /** HR makes the letter from the request and goes on with it as a normal draft */
    public function make($id)
    {
        $q = $this->requests();
        abort_unless($q->letters->isHrExecutive(), 403, 'Letters are made by HR Admins and HR Executives.');
        $r = $this->request($q, $id);
        try {
            $letterId = $q->makeLetter($r);
        } catch (\RuntimeException $e) {
            return redirect()->route('hrm.letters.requests')->with('errmsg', $e->getMessage());
        }
        $ref = DB::table('hrm_letters')->where('hl_id', $letterId)->value('hl_reference');
        return redirect()->route('hrm.letters.show', $letterId)
            ->with('msg', 'Draft ' . $ref . ' made from ' . $r->au_name . '\'s request. Check the details, fill what is marked, then send it for approval.');
    }

    public function decline(Request $request, $id)
    {
        $q = $this->requests();
        abort_unless($q->letters->isHrExecutive(), 403, 'Letter requests are handled by HR Admins and HR Executives.');
        $r = $this->request($q, $id);
        $data = $request->validate(['reason' => 'required|string|max:255'], [], ['reason' => 'reason']);
        $done = $q->decline($r, trim($data['reason']));
        return redirect()->route('hrm.letters.requests')->with($done ? 'msg' : 'errmsg', $done
            ? 'Request of ' . $r->au_name . ' declined. They see your reason in My Letters.' : 'This request was already handled a moment ago.');
    }

    /** HR Admin: may employees ask for this letter type? */
    public function requestable(Request $request, $typeId)
    {
        $q = $this->requests();
        abort_unless($q->letters->isHrAdmin(), 403, 'Only an HR Admin can change the letter types.');
        $type = DB::table('hrm_letter_types')->where('hlt_company_id', $q->letters->company)->where('hlt_id', (int) $typeId)->first();
        abort_if(!$type, 404);
        $allowed = $request->input('allowed') === '1';
        $q->setRequestable($type->hlt_id, $allowed);
        HrmLetters::log(0, 'request.types', $type->hlt_name . ($allowed ? ': employees can ask for it' : ': employees can no longer ask for it'));
        return redirect()->route('hrm.letters.types')->with('msg', $type->hlt_name . ($allowed ? ': employees can now ask for it from My Letters.' : ': employees can no longer ask for it.'));
    }

    // ------------------------------------------------------------------ the employee

    public function store(Request $request)
    {
        $q = $this->requests();
        $user = Auth::user();
        $data = $request->validate([
            'type_id'      => 'required|integer',
            'purpose'      => 'required|string|max:255',
            'addressed_to' => 'nullable|string|max:255',
            'needed_by'    => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'note'         => 'nullable|string|max:1000',
        ], ['needed_by.after_or_equal' => 'The "needed by" date cannot be in the past.'],
           ['type_id' => 'letter', 'purpose' => 'purpose', 'addressed_to' => 'addressed to', 'needed_by' => 'needed by']);
        $back = redirect()->route('hrm.letters.my');
        $type = $q->requestableTypes()->firstWhere('hlt_id', (int) $data['type_id']);
        if (!$type) {
            return $back->withInput()->with('errmsg', 'Choose one of the letters in the list.');
        }
        if ($open = $q->openOne($user->au_id, $type->hlt_id)) {
            return $back->with('errmsg', 'You already asked for a ' . $type->hlt_name . ' on ' . Carbon::parse($open->hlq_created_at)->format('d M Y')
                . ' and HR is working on it. You can ask again once it is ready or declined.');
        }
        $q->create($user, $type, array_map(function ($v) { return is_string($v) ? trim($v) : $v; }, $data + ['addressed_to' => null, 'needed_by' => null, 'note' => null]));
        return $back->with('msg', 'Your request for a ' . $type->hlt_name . ' was sent to HR. You can follow it below; the letter appears here when it is ready.');
    }

    public function withdraw($id)
    {
        $q = $this->requests();
        $r = $this->request($q, $id);
        abort_unless((int) $r->hlq_employee_id === (int) Auth::user()->au_id, 403, 'You can only withdraw your own request.');
        $done = $q->withdraw($r);
        return redirect()->route('hrm.letters.my')->with($done ? 'msg' : 'errmsg', $done ? 'Request withdrawn.' : 'HR has already started on this request, so it cannot be withdrawn.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\HrmLetterContent;
use App\Services\HrmLetterFlow;
use App\Services\HrmLetters;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * HRM > Letters (Phase 3): generate a letter, edit the draft with a live preview, send it through review and approval,
 * the letter list with search and filters, and each employee's own letters.
 */
class HrmLetterController extends Controller
{
    private function flow() { return HrmLetterFlow::forUser(); }

    private function letter(HrmLetterFlow $f, $id)
    {
        $l = $f->find($id);
        abort_if(!$l, 404);
        abort_unless($f->canView($l), 403, 'You cannot open this letter.');
        return $l;
    }

    private function back($l)
    {
        return redirect()->route('hrm.letters.show', $l->hl_id);
    }

    /**
     * Saves a change to the letter only if nobody changed its status since it was opened (two people acting at the
     * same moment): the status is read again under a row lock. Returns false when someone else was first.
     */
    private function change($l, array $values)
    {
        return DB::transaction(function () use ($l, $values) {
            $now = DB::table('hrm_letters')->where('hl_id', $l->hl_id)->lockForUpdate()->value('hl_status');
            if ($now !== $l->hl_status) {
                return false;
            }
            DB::table('hrm_letters')->where('hl_id', $l->hl_id)->update($values);
            return true;
        });
    }

    private function changedMeanwhile($l)
    {
        $now = DB::table('hrm_letters')->where('hl_id', $l->hl_id)->value('hl_status');
        return $this->back($l)->with('errmsg', 'Someone else changed this letter a moment ago (it is now ' . strtolower(HrmLetterFlow::statusLabel($now))
            . '). Nothing was saved; please check it and try again.');
    }

    // ------------------------------------------------------------------ list

    public function index(Request $request)
    {
        $f = $this->flow();
        $h = $f->letters;
        $user = Auth::user();
        $full = $h->isHrExecutive() || $h->canApprove();
        $isHead = DB::table('admin_user')->where('au_company_id', $h->company)->where('au_dep_head_id', $user->au_id)->exists();
        if (!$full && !$isHead) {
            return redirect()->route('hrm.letters.my');
        }
        $scope = in_array($request->input('scope'), ['waiting', 'drafts', 'all'], true) ? $request->input('scope') : 'all';
        $from = $request->filled('from') ? HrmLetterContent::parseDate($request->input('from')) : null;
        $to = $request->filled('to') ? HrmLetterContent::parseDate($request->input('to')) : null;
        $q = DB::table('hrm_letters as l')->join('hrm_letter_types as t', 't.hlt_id', '=', 'l.hl_type_id')
            ->leftJoin('admin_user as e', 'e.au_id', '=', 'l.hl_employee_id')->leftJoin('admin_user as c', 'c.au_id', '=', 'l.hl_created_by')
            ->where('l.hl_company_id', $h->company);
        if (!$full) {                                       // a department head sees the letters of their team
            $q->where('e.au_dep_head_id', $user->au_id);
        }
        if ($scope === 'drafts') {
            $q->where('l.hl_status', 'draft');
        } elseif ($scope === 'waiting') {
            $q->where(function ($w) use ($h, $user) {
                // waiting for my department review
                $w->where(function ($r) use ($user) {
                    $r->where('l.hl_status', 'submitted')->where('t.hlt_needs_review', 1)->where('e.au_dep_head_id', $user->au_id);
                });
                if ($h->canApprove()) {         // or waiting for approval
                    $w->orWhere('l.hl_status', 'reviewed')->orWhere(function ($r) {
                        $r->where('l.hl_status', 'submitted')->where(function ($x) {
                            $x->where('t.hlt_needs_review', 0)->orWhereNull('e.au_dep_head_id')->orWhere('e.au_dep_head_id', 0);
                        });
                    });
                }
            });
        }
        $term = trim((string) $request->input('q'));
        $q->when($term !== '', function ($w) use ($term) {
            $w->where(function ($x) use ($term) {
                $x->where('l.hl_reference', 'like', '%' . $term . '%')->orWhere('e.au_name', 'like', '%' . $term . '%')
                  ->orWhere('e.au_user_id', 'like', '%' . $term . '%')->orWhere('l.hl_subject', 'like', '%' . $term . '%');
            });
        })
            ->when($request->filled('employee'), function ($w) use ($request) { $w->where('l.hl_employee_id', (int) $request->input('employee')); })
            ->when($request->filled('type'), function ($w) use ($request) { $w->where('l.hl_type_id', (int) $request->input('type')); })
            ->when($request->filled('status'), function ($w) use ($request) { $w->where('l.hl_status', $request->input('status')); })
            ->when($request->filled('department'), function ($w) use ($request) {
                $w->whereRaw("CONCAT('-', COALESCE(e.au_department, ''), '-') LIKE ?", ['%-' . (int) $request->input('department') . '-%']);
            })
            ->when($from, function ($w) use ($from) { $w->where('l.hl_letter_date', '>=', $from->toDateString()); })
            ->when($to, function ($w) use ($to) { $w->where('l.hl_letter_date', '<=', $to->toDateString()); });
        $list = $q->orderByDesc('l.hl_id')->limit(500)
            ->get(['l.hl_id', 'l.hl_reference', 'l.hl_letter_date', 'l.hl_subject', 'l.hl_status', 'l.hl_employee', 'l.hl_print_count', 'l.hl_created_at',
                't.hlt_name', 't.hlt_needs_review', 'e.au_name', 'e.au_user_id', 'e.au_dep_head_id', 'c.au_name as created_name']);
        $types = $h->types();
        $employees = DB::table('admin_user')->where('au_company_id', $h->company)->whereNotIn('au_user_type', [1])->orderBy('au_name')->get(['au_id', 'au_name', 'au_user_id']);
        $departments = DB::table('departments')->orderBy('name')->pluck('name', 'id');
        $counts = [
            'drafts'  => DB::table('hrm_letters')->where('hl_company_id', $h->company)->where('hl_status', 'draft')->count(),
            'waiting' => $this->waitingCount($f),
        ];
        $canCreate = $h->isHrExecutive();
        return view('page.hrm.letters.letters', compact('list', 'types', 'employees', 'departments', 'scope', 'counts', 'canCreate', 'full'));
    }

    /** How many letters wait for this user (review or approval) */
    private function waitingCount(HrmLetterFlow $f)
    {
        $user = Auth::user();
        $q = DB::table('hrm_letters as l')->join('hrm_letter_types as t', 't.hlt_id', '=', 'l.hl_type_id')
            ->leftJoin('admin_user as e', 'e.au_id', '=', 'l.hl_employee_id')->where('l.hl_company_id', $f->letters->company)
            ->where(function ($w) use ($f, $user) {
                $w->where(function ($r) use ($user) { $r->where('l.hl_status', 'submitted')->where('t.hlt_needs_review', 1)->where('e.au_dep_head_id', $user->au_id); });
                if ($f->letters->canApprove()) {
                    $w->orWhere('l.hl_status', 'reviewed')->orWhere(function ($r) {
                        $r->where('l.hl_status', 'submitted')->where(function ($x) { $x->where('t.hlt_needs_review', 0)->orWhereNull('e.au_dep_head_id')->orWhere('e.au_dep_head_id', 0); });
                    });
                }
            });
        return $q->count();
    }

    /** The signed-in employee's own approved and issued letters */
    public function my()
    {
        $h = HrmLetters::forUser();
        $list = DB::table('hrm_letters as l')->join('hrm_letter_types as t', 't.hlt_id', '=', 'l.hl_type_id')
            ->where('l.hl_company_id', $h->company)->where('l.hl_employee_id', Auth::user()->au_id)->whereIn('l.hl_status', ['approved', 'issued'])
            ->orderByDesc('l.hl_letter_date')->orderByDesc('l.hl_id')->get(['l.hl_id', 'l.hl_reference', 'l.hl_letter_date', 'l.hl_subject', 'l.hl_status', 't.hlt_name']);
        // letters the employee asked HR for (once database/hrm_letter_requests.sql has been run)
        $requests = collect();
        $requestTypes = collect();
        if (class_exists(\App\Services\HrmLetterRequests::class) && \App\Services\HrmLetterRequests::ready()) {
            $rq = new \App\Services\HrmLetterRequests($h);
            $requests = $rq->query()->where('q.hlq_employee_id', Auth::user()->au_id)->orderByDesc('q.hlq_id')->limit(50)->get();
            $requestTypes = $rq->requestableTypes();
        }
        return view('page.hrm.letters.my_letters', compact('list', 'requests', 'requestTypes'));
    }

    // ------------------------------------------------------------------ generate

    public function create(Request $request)
    {
        $f = $this->flow();
        $h = $f->letters;
        abort_unless($h->isHrExecutive(), 403, 'Letters are made by HR Admins and HR Executives.');
        $h->ensureTemplates();
        $types = $h->types(true);
        $templates = DB::table('hrm_letter_templates')->where('hltp_company_id', $h->company)->where('hltp_status', 1)
            ->orderByDesc('hltp_is_default')->orderBy('hltp_name')->get(['hltp_id', 'hltp_type_id', 'hltp_name', 'hltp_is_default', 'hltp_lang'])->groupBy('hltp_type_id');
        $employees = DB::table('admin_user')->where('au_company_id', $h->company)->where('au_status', 1)->whereNotIn('au_user_type', [1])
            ->orderBy('au_name')->get(['au_id', 'au_name', 'au_user_id', 'au_branch_name']);
        $signatories = DB::table('hrm_letter_signatories')->where('hls_company_id', $h->company)->where('hls_status', 1)->orderByDesc('hls_is_default')->orderBy('hls_name')->get();
        $preset = ['employee' => (int) $request->input('employee'), 'type' => (int) $request->input('type')];
        return view('page.hrm.letters.letter_create', compact('types', 'templates', 'employees', 'signatories', 'preset'));
    }

    public function store(Request $request)
    {
        $f = $this->flow();
        $h = $f->letters;
        abort_unless($h->isHrExecutive(), 403, 'Letters are made by HR Admins and HR Executives.');
        $data = $request->validate([
            'employee_id'  => 'required|integer',
            'type_id'      => 'required|integer',
            'template_id'  => 'nullable|integer',
            'letter_date'  => 'required|date_format:Y-m-d',
            'signatory_id' => 'nullable|integer',
        ], [], ['employee_id' => 'employee', 'type_id' => 'letter type', 'letter_date' => 'letter date']);
        $back = redirect()->route('hrm.letters.create')->withInput();
        $type = $h->types(true)->firstWhere('hlt_id', (int) $data['type_id']);
        if (!$type) {
            return $back->with('errmsg', 'Choose a letter type that is on.');
        }
        if (!DB::table('admin_user')->where('au_company_id', $h->company)->where('au_id', (int) $data['employee_id'])->where('au_status', 1)->exists()) {
            return $back->with('errmsg', 'Choose an active employee of this company.');
        }
        $template = !empty($data['template_id'])
            ? DB::table('hrm_letter_templates')->where('hltp_company_id', $h->company)->where('hltp_id', (int) $data['template_id'])->where('hltp_type_id', $type->hlt_id)->where('hltp_status', 1)->first()
            : $h->defaultTemplate($type->hlt_id);
        if (!$template) {
            return $back->with('errmsg', 'This letter type has no active template. Add one on HRM > Letters > Templates.');
        }
        if (!empty($data['signatory_id']) && !$f->signatory($data['signatory_id'])) {
            return $back->with('errmsg', 'Choose a signatory of this company.');
        }
        try {
            $id = $f->create((int) $data['employee_id'], $type, $template, Carbon::parse($data['letter_date']), !empty($data['signatory_id']) ? (int) $data['signatory_id'] : null);
        } catch (\RuntimeException $e) {
            return $back->with('errmsg', $e->getMessage());
        }
        $ref = DB::table('hrm_letters')->where('hl_id', $id)->value('hl_reference');
        return redirect()->route('hrm.letters.show', $id)->with('msg', 'Draft ' . $ref . ' made. Check the details, fill what is marked, then send it for approval.');
    }

    // ------------------------------------------------------------------ the letter page

    public function show($id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        [$pad, $letter, $missing, $unknown] = $f->render($l);
        $history = DB::table('hrm_letter_approvals as a')->leftJoin('admin_user as u', 'u.au_id', '=', 'a.hla_user_id')
            ->where('a.hla_letter_id', $l->hl_id)->orderBy('a.hla_id')->get(['a.*', 'u.au_name']);
        $signatories = DB::table('hrm_letter_signatories')->where('hls_company_id', $f->letters->company)
            ->where(function ($q) use ($l) { $q->where('hls_status', 1)->orWhere('hls_id', (int) $l->hl_signatory_id); })->orderBy('hls_name')->get();
        $can = [
            'edit' => $f->canEdit($l), 'review' => $f->canReview($l), 'decide' => $f->canDecide($l),
            'revise' => $f->canRevise($l), 'cancel' => $f->canCancel($l), 'awaiting' => $f->awaiting($l),
            'headReview' => $f->awaiting($l) === 'review' && !$f->isDeptHead($l),
            // the workflow history (rejection reasons, notes) is for HR, approvers and the department head, not the employee
            'history' => $f->letters->isHrExecutive() || $f->letters->canApprove() || $f->isDeptHead($l),
        ];
        $deptHead = $l->dep_head_now ? DB::table('admin_user')->where('au_id', $l->dep_head_now)->value('au_name') : null;
        $placeholders = HrmLetterContent::PLACEHOLDERS;
        // what the employee asked for, when the letter was made from their request
        $asked = class_exists(\App\Services\HrmLetterRequests::class) && \App\Services\HrmLetterRequests::ready()
            ? DB::table('hrm_letter_requests')->where('hlq_company_id', $f->letters->company)->where('hlq_letter_id', $l->hl_id)->first() : null;
        return view('page.hrm.letters.letter_show', compact('l', 'pad', 'letter', 'missing', 'unknown', 'history', 'signatories', 'can', 'deptHead', 'placeholders', 'asked'));
    }

    /** Draft details from the form: [overrides, error] */
    private function draftInput(Request $request, HrmLetterFlow $f, $l)
    {
        $rules = [
            'letter_date'  => 'required|date_format:Y-m-d',
            'signatory_id' => 'nullable|integer',
            'subject'      => 'nullable|string|max:255',
            'body'         => 'required|string|max:200000',
            'employee'     => 'nullable|array',
            'employee.designation'  => 'nullable|string|max:100',
            'employee.department'   => 'nullable|string|max:150',
            'employee.joining_date' => 'nullable|date_format:Y-m-d',
            'employee.salary'       => 'nullable|numeric|min:0|max:9999999999',
            'fields'       => 'nullable|array',
        ];
        foreach ($l->field_list as $k) {
            $kind = HrmLetters::FIELDS[$k][1];
            $rules['fields.' . $k] = $kind === 'date' ? 'nullable|date_format:Y-m-d' : ($kind === 'number' ? 'nullable|numeric|min:0|max:9999999999' : 'nullable|string|max:2000');
        }
        $data = $request->validate($rules, [], ['body' => 'letter text', 'letter_date' => 'letter date']);
        if (!empty($data['signatory_id']) && !$f->signatory($data['signatory_id'])) {
            return [null, 'Choose a signatory of this company.'];
        }
        $unknown = HrmLetterContent::unknown($data['body'] . ' ' . ($data['subject'] ?? ''));
        if ($unknown) {
            return [null, 'Unknown placeholders: {{' . implode('}}, {{', $unknown) . '}}.'];
        }
        $fields = [];
        foreach ($l->field_list as $k) {
            $fields[$k] = trim((string) ($data['fields'][$k] ?? ''));
        }
        return [['date' => $data['letter_date'], 'signatory_id' => !empty($data['signatory_id']) ? (int) $data['signatory_id'] : null,
            'subject' => trim((string) ($data['subject'] ?? '')), 'body' => $data['body'], 'fields' => $fields,
            'employee' => array_map(function ($v) { return trim((string) $v); }, array_intersect_key($data['employee'] ?? [], HrmLetterFlow::EDITABLE_EMPLOYEE))], null];
    }

    public function update(Request $request, $id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless($f->canEdit($l), 403, 'Only a draft can be changed, by HR.');
        [$o, $error] = $this->draftInput($request, $f, $l);
        if ($error) {
            return $this->back($l)->withInput()->with('errmsg', $error);
        }
        $employee = array_merge((array) $l->employee, $o['employee']);
        if (!$this->change($l, [
            'hl_letter_date' => $o['date'], 'hl_signatory_id' => $o['signatory_id'], 'hl_subject' => $o['subject'] ?: null,
            'hl_body' => HrmLetterContent::clean($o['body']), 'hl_employee' => json_encode($employee), 'hl_fields' => json_encode((object) $o['fields']),
            'hl_updated_by' => Auth::user()->au_id, 'hl_updated_at' => Carbon::now(),
        ])) {
            return $this->changedMeanwhile($l);
        }
        HrmLetters::log($l->hl_id, 'edited', 'Draft saved', 'draft', 'draft');
        $l = $f->find($l->hl_id);
        $problems = $f->problems($l);
        if ($request->input('then') === 'submit') {
            return $this->submit($request, $l->hl_id);
        }
        return $this->back($l)->with('msg', 'Draft saved.' . ($problems ? ' Still to do: ' . implode(' ', $problems) : ' Everything is filled: you can send it for approval.'));
    }

    /** Live preview of the draft while it is edited */
    public function preview(Request $request, $id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        $o = [];
        if ($f->canEdit($l)) {
            $o = ['subject' => (string) $request->input('subject'), 'body' => (string) $request->input('body'), 'fields' => (array) $request->input('fields', []),
                'employee' => array_intersect_key((array) $request->input('employee', []), HrmLetterFlow::EDITABLE_EMPLOYEE),
                'signatory_id' => $request->input('signatory_id') ?: null];
            if ($request->filled('letter_date') && HrmLetterContent::parseDate($request->input('letter_date'))) {
                $o['date'] = HrmLetterContent::parseDate($request->input('letter_date'))->toDateString();
            }
        }
        [$pad, $letter, $missing, $unknown] = $f->render($l, $o);
        return response()->json(['html' => view('page.hrm.letters._pad', ['pad' => $pad, 'letter' => $letter])->render(),
            'missing' => array_map([HrmLetterFlow::class, 'label'], $missing), 'unknown' => $unknown]);
    }

    /** Back to the text of the template (current version), keeping the details */
    public function refill($id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless($f->canEdit($l), 403);
        $tpl = $l->hl_template_id ? DB::table('hrm_letter_templates')->where('hltp_company_id', $f->letters->company)->where('hltp_id', $l->hl_template_id)->first() : null;
        $tpl = $tpl ?: $f->letters->defaultTemplate($l->hl_type_id);
        if (!$tpl) {
            return $this->back($l)->with('errmsg', 'This letter type has no template.');
        }
        if (!$this->change($l, ['hl_body' => $tpl->hltp_body, 'hl_subject' => $tpl->hltp_subject ?: $l->hl_subject,
            'hl_template_id' => $tpl->hltp_id, 'hl_updated_by' => Auth::user()->au_id, 'hl_updated_at' => Carbon::now()])) {
            return $this->changedMeanwhile($l);
        }
        HrmLetters::log($l->hl_id, 'edited', 'Text taken again from template "' . $tpl->hltp_name . '"', 'draft', 'draft');
        return $this->back($l)->with('msg', 'The letter text was taken again from the template "' . $tpl->hltp_name . '".');
    }

    // ------------------------------------------------------------------ workflow

    public function submit(Request $request, $id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless($f->canEdit($l), 403, 'Only a draft can be sent, by HR.');
        if ($problems = $f->problems($l)) {
            return $this->back($l)->with('errmsg', 'Not sent yet. ' . implode(' ', $problems));
        }
        if (!$this->change($l, ['hl_status' => 'submitted', 'hl_note' => null, 'hl_updated_by' => Auth::user()->au_id, 'hl_updated_at' => Carbon::now()])) {
            return $this->changedMeanwhile($l);
        }
        $l = $f->find($l->hl_id);
        $next = $f->awaiting($l) === 'review' ? 'department head review' : 'approval';
        HrmLetters::log($l->hl_id, 'submitted', 'Sent for ' . $next, 'draft', 'submitted');
        return $this->back($l)->with('msg', 'Letter ' . $l->hl_reference . ' sent for ' . $next . '.');
    }

    public function review(Request $request, $id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless($f->canReview($l), 403, 'This letter is not waiting for your review.');
        $data = $request->validate(['action' => 'required|in:forward,back', 'note' => 'nullable|string|max:255']);
        $note = trim((string) ($data['note'] ?? ''));
        if ($data['action'] === 'back' && $note === '') {
            return $this->back($l)->with('errmsg', 'Say what has to change when sending a letter back.');
        }
        $to = $data['action'] === 'forward' ? 'reviewed' : 'draft';
        if (!$this->change($l, ['hl_status' => $to, 'hl_reviewer_id' => Auth::user()->au_id, 'hl_note' => $to === 'draft' ? $note : null,
            'hl_updated_by' => Auth::user()->au_id, 'hl_updated_at' => Carbon::now()])) {
            return $this->changedMeanwhile($l);
        }
        $by = $f->isDeptHead($l) ? '' : ' (HR Admin, for the department head)';
        HrmLetters::log($l->hl_id, $to === 'reviewed' ? 'reviewed' : 'sent_back', ($to === 'reviewed' ? 'Reviewed, sent for approval' : 'Sent back to HR') . $by . ($note !== '' ? ': ' . $note : ''), 'submitted', $to);
        return $this->back($l)->with('msg', $to === 'reviewed' ? 'Reviewed and sent for approval.' : 'Sent back to HR as a draft.');
    }

    public function approve($id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless($f->canDecide($l), 403, 'This letter is not waiting for your approval.');
        $from = $l->hl_status;
        try {
            DB::transaction(function () use ($f, $l) { $f->freeze($l); });
        } catch (\RuntimeException $e) {
            return $this->back($l)->with('errmsg', 'Not approved. ' . $e->getMessage());
        }
        $note = $f->awaiting($l) === 'review' ? 'Approved without the department head review (HR Admin)' : 'Approved';
        HrmLetters::log($l->hl_id, 'approved', $note . '. Text, employee details, letterhead and signatory are now fixed.', $from, 'approved');
        return $this->back($l)->with('msg', 'Letter ' . $l->hl_reference . ' approved. It is now fixed and ready to print.');
    }

    public function reject(Request $request, $id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless($f->canDecide($l), 403, 'This letter is not waiting for your approval.');
        $data = $request->validate(['reason' => 'required|string|max:255'], [], ['reason' => 'reason']);
        if (!$this->change($l, ['hl_status' => 'rejected', 'hl_note' => trim($data['reason']), 'hl_approver_id' => Auth::user()->au_id,
            'hl_updated_by' => Auth::user()->au_id, 'hl_updated_at' => Carbon::now()])) {
            return $this->changedMeanwhile($l);
        }
        HrmLetters::log($l->hl_id, 'rejected', trim($data['reason']), $l->hl_status, 'rejected');
        return $this->back($l)->with('msg', 'Letter ' . $l->hl_reference . ' rejected.');
    }

    public function revise($id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless($f->canRevise($l), 403);
        if (!$this->change($l, ['hl_status' => 'draft', 'hl_updated_by' => Auth::user()->au_id, 'hl_updated_at' => Carbon::now()])) {
            return $this->changedMeanwhile($l);
        }
        HrmLetters::log($l->hl_id, 'revised', 'Opened again as a draft to correct it', 'rejected', 'draft');
        return $this->back($l)->with('msg', 'The letter is a draft again. Correct it and send it for approval. It keeps its reference number.');
    }

    public function cancel(Request $request, $id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless($f->canCancel($l), 403, 'Only an HR Admin can cancel a letter.');
        $data = $request->validate(['reason' => 'required|string|max:255'], [], ['reason' => 'reason']);
        if (!$this->change($l, ['hl_status' => 'cancelled', 'hl_note' => trim($data['reason']),
            'hl_updated_by' => Auth::user()->au_id, 'hl_updated_at' => Carbon::now()])) {
            return $this->changedMeanwhile($l);
        }
        HrmLetters::log($l->hl_id, 'cancelled', trim($data['reason']), $l->hl_status, 'cancelled');
        return $this->back($l)->with('msg', 'Letter ' . $l->hl_reference . ' cancelled. Its reference number stays used and is never given again.');
    }

    // ------------------------------------------------------------------ print and PDF (Phase 4)

    /** Print preview on the letterhead PAD, laid out in A4 pages. Letters not yet approved show marked, without Print. */
    public function printView($id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        [$pad, $letter] = $f->render($l);
        $printable = HrmLetterFlow::printable($l);
        $watermark = HrmLetterFlow::watermark($l);
        $bangla = HrmLetterFlow::hasBangla($pad, $letter);
        return view('page.hrm.letters.print', compact('l', 'pad', 'letter', 'printable', 'watermark', 'bangla'));
    }

    /** Called when the letter is printed from the preview: counted, and an approved letter becomes issued */
    public function printed(Request $request, $id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        abort_unless(HrmLetterFlow::printable($l), 403, 'Only an approved letter can be printed.');
        $status = $f->markOutput($l, 'printed', $request->input('pad') === '0' ? ' on PAD paper (text only)' : '');
        return response()->json(['ok' => true, 'status' => $status, 'label' => HrmLetterFlow::statusLabel($status)]);
    }

    /**
     * The approved letter as an A4 PDF, letterhead header and footer on every page.
     * pad=0: text only, for printing on PAD paper (the text stays in the same place, the header and footer are left blank).
     */
    public function pdf(Request $request, $id)
    {
        $f = $this->flow();
        $l = $this->letter($f, $id);
        if (!HrmLetterFlow::printable($l)) {
            return $this->back($l)->with('errmsg', 'A PDF can be made only after the letter is approved.');
        }
        $textOnly = $request->input('pad') === '0';
        [$pad, $letter] = $f->render($l, [], false);
        if (HrmLetterFlow::hasBangla($pad, $letter)) {
            // the PDF maker cannot draw Bangla: the print window's "Save as PDF" does it right (and that print is logged)
            return redirect()->route('hrm.letters.print', ['id' => $l->hl_id, 'save' => 'pdf'] + ($textOnly ? ['pad' => '0'] : []));
        }
        $output = $this->makePdf($l, $pad, $letter, $textOnly);
        $f->markOutput($l, 'downloaded', $textOnly ? ' (text only, for PAD paper)' : '');
        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . preg_replace('/[^A-Za-z0-9\-]/', '', $l->hl_reference) . '.pdf"',
        ]);
    }

    /**
     * dompdf rendering of a letter; the page numbers are written on each page after the layout is known.
     * $textOnly: for PAD paper, the header and footer (and page numbers) are left out but their space is kept.
     */
    public function makePdf($l, $pad, $letter, $textOnly = false)
    {
        $wrapper = app('dompdf.wrapper');
        $dompdf = $wrapper->getDomPDF();
        $metrics = $dompdf->getFontMetrics();
        $bands = HrmLetterFlow::pdfBands($pad, $metrics);
        $wrapper->setPaper('a4', 'portrait');
        $wrapper->loadView('page.hrm.letters.pdf', compact('l', 'pad', 'letter', 'bands', 'textOnly'));
        // where dompdf writes the footer line, so the page number sits on the same line
        $footY = null;
        $dompdf->setCallbacks([['event' => 'end_frame', 'f' => function ($frame) use (&$footY) {
            $node = $frame->get_node();
            if ($footY === null && $node->nodeName === '#text' && $node->parentNode instanceof \DOMElement && $node->parentNode->getAttribute('class') === 'lh-foot-text') {
                $footY = $frame->get_position()['y'];
            }
        }]]);
        $wrapper->render();
        $dompdf->addInfo('Title', $l->hlt_name . ' - ' . $l->hl_reference);
        $dompdf->addInfo('Subject', (string) $letter->subject);
        $dompdf->addInfo('Author', (string) ($pad->company_name ?? ''));
        if (!empty($pad->page_numbers) && !$textOnly) {
            $side = (int) ($pad->margin_side ?? 18) * 72 / 25.4;
            $top = $footY ?? $bands[2];
            $dompdf->getCanvas()->page_script(function ($page, $pages, $canvas, $fm) use ($side, $top) {
                $text = 'Page ' . $page . ' of ' . $pages;
                $font = $fm->getFont('Helvetica', 'normal');
                $canvas->text($canvas->get_width() - $side - $fm->getTextWidth($text, $font, 8), $top, $text, $font, 8, [0.333, 0.333, 0.333]);
            });
        }
        return $wrapper->output();
    }
}

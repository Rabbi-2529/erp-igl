<?php

namespace App\Http\Controllers;

use App\Services\HrmLetterContent;
use App\Services\HrmLetters;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * HRM > Letters > Templates (Phase 2): the text of each letter type with placeholders like {{employee_name}},
 * an editor with a placeholder picker and a live preview on the letterhead PAD. HR Admins change templates.
 */
class HrmLetterTemplateController extends Controller
{
    private function letters() { return HrmLetters::forUser(); }

    private function canView(HrmLetters $h)
    {
        abort_unless($h->isHrExecutive() || $h->canApprove(), 403, 'Letter templates are for HR Admins and HR Executives.');
    }

    private function adminOnly(HrmLetters $h)
    {
        abort_unless($h->isHrAdmin(), 403, 'Only an HR Admin can change letter templates.');
    }

    private function template(HrmLetters $h, $id)
    {
        $t = DB::table('hrm_letter_templates')->where('hltp_company_id', $h->company)->where('hltp_id', (int) $id)->first();
        abort_if(!$t, 404);
        return $t;
    }

    public function index(Request $request)
    {
        $h = $this->letters();
        $this->canView($h);
        $h->ensureTemplates();
        $types = $h->types();
        $typeId = (int) $request->input('type');
        $list = DB::table('hrm_letter_templates as t')->join('hrm_letter_types as y', 'y.hlt_id', '=', 't.hltp_type_id')
            ->leftJoin('admin_user as u', 'u.au_id', '=', DB::raw('COALESCE(t.hltp_updated_by, t.hltp_created_by)'))
            ->where('t.hltp_company_id', $h->company)->when($typeId, function ($q) use ($typeId) { $q->where('t.hltp_type_id', $typeId); })
            ->orderBy('y.hlt_sort')->orderBy('y.hlt_name')->orderByDesc('t.hltp_is_default')->orderBy('t.hltp_name')
            ->get(['t.*', 'y.hlt_name', 'y.hlt_prefix', 'y.hlt_status', 'u.au_name']);
        $used = DB::table('hrm_letters')->where('hl_company_id', $h->company)->whereNotNull('hl_template_id')->groupBy('hl_template_id')
            ->selectRaw('hl_template_id, COUNT(*) n')->pluck('n', 'hl_template_id');
        $isAdmin = $h->isHrAdmin();
        return view('page.hrm.letters.templates', compact('types', 'typeId', 'list', 'used', 'isAdmin'));
    }

    public function create(Request $request)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $h->ensureTemplates();
        $types = $h->types();
        $type = $types->firstWhere('hlt_id', (int) $request->input('type')) ?: $types->first();
        $start = $h->defaultTemplate($type->hlt_id);
        $template = (object) ['hltp_id' => null, 'hltp_type_id' => $type->hlt_id, 'hltp_name' => '', 'hltp_subject' => $start->hltp_subject ?? $type->hlt_subject,
            'hltp_body' => $start->hltp_body ?? '', 'hltp_lang' => 'en', 'hltp_is_default' => 0, 'hltp_status' => 1];
        return $this->form($h, $types, $template);
    }

    public function edit($id)
    {
        $h = $this->letters();
        $this->canView($h);
        $types = $h->types();
        return $this->form($h, $types, $this->template($h, $id));
    }

    private function form(HrmLetters $h, $types, $template)
    {
        $employees = DB::table('admin_user')->where('au_company_id', $h->company)->where('au_status', 1)->whereNotIn('au_user_type', [1])
            ->orderBy('au_name')->get(['au_id', 'au_name', 'au_user_id', 'au_branch_name']);
        $placeholders = HrmLetterContent::PLACEHOLDERS;
        $fields = HrmLetters::FIELDS;
        $typeFields = $types->mapWithKeys(function ($t) { return [$t->hlt_id => $t->field_list]; });
        $isAdmin = $h->isHrAdmin();
        return view('page.hrm.letters.template_form', compact('types', 'template', 'employees', 'placeholders', 'fields', 'typeFields', 'isAdmin'));
    }

    /** Checks and cleans what the editor sent; returns [values, error or null, notes] */
    private function input(Request $request, HrmLetters $h)
    {
        $data = $request->validate([
            'type_id'    => 'required|integer',
            'name'       => 'required|string|max:100',
            'subject'    => 'nullable|string|max:200',
            'body'       => 'required|string|max:200000',
            'lang'       => 'required|in:en,bn',
            'is_default' => 'nullable|in:0,1',
            'status'     => 'nullable|in:0,1',
        ], [], ['body' => 'letter text']);
        $type = $h->types()->firstWhere('hlt_id', (int) $data['type_id']);
        if (!$type) {
            return [null, 'Choose a letter type.', []];
        }
        $body = HrmLetterContent::clean($data['body']);
        if (trim(strip_tags($body)) === '') {
            return [null, 'The letter text is empty.', []];
        }
        $unknown = HrmLetterContent::unknown($body . ' ' . ($data['subject'] ?? ''));
        if ($unknown) {
            return [null, 'Unknown placeholder' . (count($unknown) > 1 ? 's' : '') . ': {{' . implode('}}, {{', $unknown) . '}}. Use the buttons beside the editor to insert placeholders.', []];
        }
        // the letter type asks for details the text never uses: say so, it is allowed
        $notes = [];
        $notUsed = array_diff($type->field_list, HrmLetterContent::used($body . ' ' . ($data['subject'] ?? '')));
        if ($notUsed) {
            $notes[] = 'The ' . $type->hlt_name . ' form asks for ' . implode(', ', array_map(function ($f) { return HrmLetters::FIELDS[$f][0]; }, $notUsed))
                . ', but this text does not use ' . (count($notUsed) > 1 ? 'them' : 'it') . '.';
        }
        return [[
            'hltp_type_id' => $type->hlt_id, 'hltp_name' => trim($data['name']), 'hltp_subject' => trim((string) ($data['subject'] ?? '')) ?: null,
            'hltp_body' => $body, 'hltp_lang' => $data['lang'], 'hltp_is_default' => (int) ($data['is_default'] ?? 0), 'hltp_status' => (int) ($data['status'] ?? 1),
        ], null, $notes, $type];
    }

    public function store(Request $request)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        [$values, $error, $notes, $type] = array_pad($this->input($request, $h), 4, null);
        if ($error) {
            return redirect()->route('hrm.letters.templates.create', ['type' => $request->input('type_id')])->withInput()->with('errmsg', $error);
        }
        $id = DB::transaction(function () use ($h, $values) {
            if ($values['hltp_is_default']) {
                DB::table('hrm_letter_templates')->where('hltp_company_id', $h->company)->where('hltp_type_id', $values['hltp_type_id'])->update(['hltp_is_default' => 0]);
            }
            return DB::table('hrm_letter_templates')->insertGetId($values + ['hltp_company_id' => $h->company, 'hltp_created_by' => Auth::user()->au_id, 'hltp_created_at' => Carbon::now()]);
        });
        HrmLetters::log(0, 'template.add', 'Template "' . $values['hltp_name'] . '" added for ' . $type->hlt_name);
        return redirect()->route('hrm.letters.templates.edit', $id)->with('msg', trim('Template "' . $values['hltp_name'] . '" saved. ' . implode(' ', $notes)));
    }

    public function update(Request $request, $id)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $old = $this->template($h, $id);
        [$values, $error, $notes, $type] = array_pad($this->input($request, $h), 4, null);
        if ($error) {
            return redirect()->route('hrm.letters.templates.edit', $old->hltp_id)->withInput()->with('errmsg', $error);
        }
        DB::transaction(function () use ($h, $values, $old) {
            if ($values['hltp_is_default']) {
                DB::table('hrm_letter_templates')->where('hltp_company_id', $h->company)->where('hltp_type_id', $values['hltp_type_id'])
                    ->where('hltp_id', '<>', $old->hltp_id)->update(['hltp_is_default' => 0]);
            }
            DB::table('hrm_letter_templates')->where('hltp_id', $old->hltp_id)->update($values + ['hltp_updated_by' => Auth::user()->au_id, 'hltp_updated_at' => Carbon::now()]);
        });
        HrmLetters::log(0, 'template.edit', 'Template "' . $values['hltp_name'] . '" of ' . $type->hlt_name . ' saved' . ($old->hltp_body !== $values['hltp_body'] ? ' (text changed)' : ''));
        return redirect()->route('hrm.letters.templates.edit', $old->hltp_id)
            ->with('msg', trim('Template "' . $values['hltp_name'] . '" saved. Letters already made keep their own text. ' . implode(' ', $notes)));
    }

    public function makeDefault($id)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $t = $this->template($h, $id);
        DB::transaction(function () use ($h, $t) {
            DB::table('hrm_letter_templates')->where('hltp_company_id', $h->company)->where('hltp_type_id', $t->hltp_type_id)->update(['hltp_is_default' => 0]);
            DB::table('hrm_letter_templates')->where('hltp_id', $t->hltp_id)->update(['hltp_is_default' => 1, 'hltp_status' => 1]);
        });
        HrmLetters::log(0, 'template.default', 'Template "' . $t->hltp_name . '" is now the default');
        return redirect()->route('hrm.letters.templates', ['type' => $t->hltp_type_id])->with('msg', 'Template "' . $t->hltp_name . '" is now the default for new letters of its type.');
    }

    public function duplicate($id)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $t = $this->template($h, $id);
        $newId = DB::table('hrm_letter_templates')->insertGetId([
            'hltp_company_id' => $h->company, 'hltp_type_id' => $t->hltp_type_id, 'hltp_name' => mb_substr($t->hltp_name . ' (copy)', 0, 100),
            'hltp_subject' => $t->hltp_subject, 'hltp_body' => $t->hltp_body, 'hltp_lang' => $t->hltp_lang, 'hltp_is_default' => 0, 'hltp_status' => 1,
            'hltp_created_by' => Auth::user()->au_id, 'hltp_created_at' => Carbon::now(),
        ]);
        HrmLetters::log(0, 'template.copy', 'Template "' . $t->hltp_name . '" copied');
        return redirect()->route('hrm.letters.templates.edit', $newId)->with('msg', 'Copy made. Change its name and text, then save.');
    }

    /** Live preview: the text on the letterhead PAD, filled with an employee's details (or sample ones) */
    public function preview(Request $request)
    {
        $h = $this->letters();
        $this->canView($h);
        $type = $h->types()->firstWhere('hlt_id', (int) $request->input('type_id'));
        $employee = $request->filled('employee_id') ? HrmLetterContent::employee($request->input('employee_id'), $h->company) : null;
        $employee = $employee ?: HrmLetterContent::sampleEmployee();
        $pad = $h->letterhead($employee->branch_id ?: $h->branch);
        $sig = $h->defaultSignatory($employee->branch_id ?: $h->branch);
        $signatory = $sig ? (object) ['name' => $sig->hls_name, 'designation' => $sig->hls_designation, 'signature' => $sig->hls_signature]
            : (object) ['name' => 'Authorized Person', 'designation' => 'Designation', 'signature' => null];
        $date = Carbon::today();
        $reference = ($type->hlt_prefix ?? 'REF') . $date->format('Ymd') . 'XXX';
        $values = HrmLetterContent::values($employee, $pad, $signatory, HrmLetterContent::sampleFields(), $reference, $date);
        [$body, $missing] = HrmLetterContent::fill(HrmLetterContent::clean((string) $request->input('body')), $values, true);
        $letter = (object) ['reference' => $reference, 'date' => $date, 'subject' => HrmLetterContent::fillText((string) $request->input('subject'), $values),
            'body' => $body, 'signatory' => $signatory];
        $html = view('page.hrm.letters._pad', ['pad' => $pad, 'letter' => $letter])->render();
        return response()->json(['html' => $html, 'missing' => $missing, 'unknown' => HrmLetterContent::unknown($request->input('body') . ' ' . $request->input('subject'))]);
    }
}

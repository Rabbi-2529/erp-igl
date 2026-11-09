<?php

namespace App\Http\Controllers;

use App\Services\HrmLetters;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * HRM > Letters setup (Phase 1): letter types and prefixes, letterhead PAD, signatories and who does what.
 * HR Admins change these; HR Executives and approvers can look. Writes only to the hrm_letter* tables.
 */
class HrmLetterSetupController extends Controller
{
    private function letters() { return HrmLetters::forUser(); }

    /** Opens to HR Admins, HR Executives and approvers */
    private function canView(HrmLetters $h)
    {
        abort_unless($h->isHrExecutive() || $h->canApprove(), 403, 'The letter settings are for HR Admins and HR Executives.');
    }

    private function adminOnly(HrmLetters $h)
    {
        abort_unless($h->isHrAdmin(), 403, 'Only an HR Admin can change the letter settings.');
    }

    private function branches($company)
    {
        return DB::table('auth_branch')->where('company_id', $company)->orderBy('branch_id')->pluck('branch_name', 'branch_id');
    }

    private function employees($company)
    {
        return DB::table('admin_user')->where('au_company_id', $company)->where('au_status', 1)->whereNotIn('au_user_type', [1])
            ->orderBy('au_name')->get(['au_id', 'au_name', 'au_user_id', 'au_branch_name', 'au_user_type']);
    }

    // ------------------------------------------------------------------ letter types

    public function types()
    {
        $h = $this->letters();
        $this->canView($h);
        $types = $h->types();
        $used = DB::table('hrm_letters')->where('hl_company_id', $h->company)->groupBy('hl_type_id')->selectRaw('hl_type_id, COUNT(*) n')->pluck('n', 'hl_type_id');
        $fields = HrmLetters::FIELDS;
        $isAdmin = $h->isHrAdmin();
        // which types employees may ask for from My Letters (null until database/hrm_letter_requests.sql has been run)
        $askable = class_exists(\App\Services\HrmLetterRequests::class) && \App\Services\HrmLetterRequests::ready() ? (new \App\Services\HrmLetterRequests($h))->requestableIds() : null;
        return view('page.hrm.letters.types', compact('types', 'used', 'fields', 'isAdmin', 'askable'));
    }

    private function typeRules()
    {
        return [
            'name'         => 'required|string|max:100',
            'prefix'       => ['required', 'string', 'regex:/^[A-Za-z]{2,6}$/'],
            'subject'      => 'nullable|string|max:200',
            'fields'       => 'nullable|array',
            'fields.*'     => 'in:' . implode(',', array_keys(HrmLetters::FIELDS)),
            'needs_review' => 'nullable|in:0,1',
            'status'       => 'nullable|in:0,1',
            'sort'         => 'nullable|integer|min:0|max:9999',
        ];
    }

    public function typeStore(Request $request)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $data = $request->validate($this->typeRules(), ['prefix.regex' => 'The prefix must be 2 to 6 letters (A-Z).']);
        $prefix = strtoupper($data['prefix']);
        $back = redirect()->route('hrm.letters.types');
        if (DB::table('hrm_letter_types')->where('hlt_company_id', $h->company)->where('hlt_prefix', $prefix)->exists()) {
            return $back->withInput()->with('errmsg', 'Prefix ' . $prefix . ' is already used by another letter type.');
        }
        $id = DB::table('hrm_letter_types')->insertGetId([
            'hlt_company_id' => $h->company, 'hlt_code' => null, 'hlt_name' => trim($data['name']), 'hlt_prefix' => $prefix,
            'hlt_subject' => $data['subject'] ?? null, 'hlt_fields' => implode(',', $data['fields'] ?? []) ?: null,
            'hlt_needs_review' => (int) ($data['needs_review'] ?? 0), 'hlt_status' => 1,
            'hlt_sort' => (int) ($data['sort'] ?? (DB::table('hrm_letter_types')->where('hlt_company_id', $h->company)->max('hlt_sort') + 10)),
            'hlt_created_by' => Auth::user()->au_id, 'hlt_created_at' => Carbon::now(),
        ]);
        HrmLetters::log(0, 'type.add', 'Letter type "' . trim($data['name']) . '" added with prefix ' . $prefix);
        return $back->with('msg', 'Letter type "' . trim($data['name']) . '" added (prefix ' . $prefix . ').');
    }

    public function typeUpdate(Request $request, $id)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $data = $request->validate($this->typeRules(), ['prefix.regex' => 'The prefix must be 2 to 6 letters (A-Z).']);
        $prefix = strtoupper($data['prefix']);
        $back = redirect()->route('hrm.letters.types');
        $type = DB::table('hrm_letter_types')->where('hlt_company_id', $h->company)->where('hlt_id', (int) $id)->first();
        if (!$type) {
            return $back->with('errmsg', 'Letter type not found.');
        }
        if (DB::table('hrm_letter_types')->where('hlt_company_id', $h->company)->where('hlt_prefix', $prefix)->where('hlt_id', '<>', $type->hlt_id)->exists()) {
            return $back->withInput()->with('errmsg', 'Prefix ' . $prefix . ' is already used by another letter type.');
        }
        DB::table('hrm_letter_types')->where('hlt_id', $type->hlt_id)->update([
            'hlt_name' => trim($data['name']), 'hlt_prefix' => $prefix, 'hlt_subject' => $data['subject'] ?? null,
            'hlt_fields' => implode(',', $data['fields'] ?? []) ?: null, 'hlt_needs_review' => (int) ($data['needs_review'] ?? 0),
            'hlt_status' => (int) ($data['status'] ?? 1), 'hlt_sort' => (int) ($data['sort'] ?? $type->hlt_sort),
            'hlt_updated_by' => Auth::user()->au_id, 'hlt_updated_at' => Carbon::now(),
        ]);
        $changes = [];
        if ($type->hlt_prefix !== $prefix) { $changes[] = 'prefix ' . $type->hlt_prefix . ' -> ' . $prefix; }
        if ($type->hlt_name !== trim($data['name'])) { $changes[] = 'name "' . $type->hlt_name . '" -> "' . trim($data['name']) . '"'; }
        if ((int) $type->hlt_status !== (int) ($data['status'] ?? 1)) { $changes[] = ((int) ($data['status'] ?? 1) ? 'switched on' : 'switched off'); }
        HrmLetters::log(0, 'type.edit', 'Letter type "' . $type->hlt_name . '" saved' . ($changes ? ': ' . implode(', ', $changes) : ''));
        return $back->with('msg', 'Letter type "' . trim($data['name']) . '" saved.' . ($type->hlt_prefix !== $prefix ? ' Letters made before keep their old reference numbers.' : ''));
    }

    // ------------------------------------------------------------------ letterhead PAD

    public function letterhead(Request $request)
    {
        $h = $this->letters();
        $this->canView($h);
        $branches = $this->branches($h->company);
        $branchId = (int) $request->input('branch', 0);
        if ($branchId && !$branches->has($branchId)) {
            $branchId = 0;
        }
        $saved = $h->savedLetterhead($branchId);
        $erp = $h->erpDetails($branchId);          // branch 0: the company's own details
        $pad = $h->letterhead($branchId);          // a branch: its own letterhead, else the company's
        $own = DB::table('hrm_company_letterheads')->where('hlh_company_id', $h->company)->where('hlh_branch_id', '>', 0)->pluck('hlh_branch_id')->all();
        $isAdmin = $h->isHrAdmin();
        $sample = $this->sampleLetter($pad);
        return view('page.hrm.letters.letterhead', compact('branches', 'branchId', 'saved', 'erp', 'pad', 'own', 'isAdmin', 'sample'));
    }

    /** A sample letter for the preview */
    private function sampleLetter($pad)
    {
        $signatory = DB::table('hrm_letter_signatories')->where('hls_company_id', Auth::user()->au_company_id)->where('hls_status', 1)
            ->orderByDesc('hls_is_default')->orderBy('hls_id')->first();
        return (object) [
            'reference' => 'APP' . Carbon::today()->format('Ymd') . '123',
            'date'      => Carbon::today(),
            'subject'   => 'Letter of Appointment',
            'body'      => '<p>To<br><b>Employee Name</b><br>Designation, Department</p>'
                . '<p>Dear Employee Name,</p>'
                . '<p>This is how every letter from ' . e($pad->company_name ?: 'your company') . ' will look: your logo, name and contact details at the top, '
                . 'the reference number and date under them, the letter itself, then the signature and seal, and your contact line at the foot of every page.</p>'
                . '<p>Letters run onto a second page by themselves; the letterhead header and footer repeat on each page and the signature never splits from its name.</p>'
                . '<p>Sincerely,</p>',
            'signatory' => $signatory ? (object) ['name' => $signatory->hls_name, 'designation' => $signatory->hls_designation, 'signature' => $signatory->hls_signature]
                : (object) ['name' => 'Authorized Person', 'designation' => 'Designation', 'signature' => null],
        ];
    }

    public function letterheadSave(Request $request)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $branches = $this->branches($h->company);
        $data = $request->validate([
            'branch'        => 'required|integer|min:0',
            'company_name'  => 'nullable|string|max:150',
            'tagline'       => 'nullable|string|max:200',
            'address'       => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:100',
            'email'         => 'nullable|email|max:150',
            'website'       => 'nullable|string|max:150',
            'footer_text'   => 'nullable|string|max:255',
            'accent'        => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'margin_top'    => 'required|integer|min:5|max:40',
            'margin_bottom' => 'required|integer|min:5|max:40',
            'margin_side'   => 'required|integer|min:8|max:40',
            'page_numbers'  => 'nullable|in:0,1',
            'logo'          => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
            'seal'          => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
            'remove_logo'   => 'nullable|in:0,1',
            'remove_seal'   => 'nullable|in:0,1',
        ], ['logo.max' => 'The logo must be 1 MB or smaller.', 'seal.max' => 'The seal must be 1 MB or smaller.']);
        $branchId = (int) $data['branch'];
        $back = redirect()->route('hrm.letters.letterhead', ['branch' => $branchId]);
        if ($branchId && !$branches->has($branchId)) {
            return $back->with('errmsg', 'Choose a branch of this company.');
        }
        $saved = $h->savedLetterhead($branchId);
        $row = [];
        foreach (['company_name', 'tagline', 'address', 'phone', 'email', 'website', 'footer_text', 'accent'] as $f) {
            $v = trim((string) ($data[$f] ?? ''));
            $row['hlh_' . $f] = $v !== '' ? $v : null;
        }
        $row += ['hlh_margin_top' => (int) $data['margin_top'], 'hlh_margin_bottom' => (int) $data['margin_bottom'],
            'hlh_margin_side' => (int) $data['margin_side'], 'hlh_page_numbers' => (int) ($data['page_numbers'] ?? 0),
            'hlh_updated_by' => Auth::user()->au_id, 'hlh_updated_at' => Carbon::now()];
        foreach (['logo', 'seal'] as $img) {
            if ($request->hasFile($img)) {
                $row['hlh_' . $img] = HrmLetters::storeImage($request->file($img), $img);
            } elseif (!empty($data['remove_' . $img])) {
                $row['hlh_' . $img] = null;
            } else {
                $row['hlh_' . $img] = $saved->{'hlh_' . $img} ?? null;
            }
        }
        DB::table('hrm_company_letterheads')->updateOrInsert(['hlh_company_id' => $h->company, 'hlh_branch_id' => $branchId], $row);
        $label = $branchId ? $branches[$branchId] . ' branch letterhead' : 'Company letterhead';
        HrmLetters::log(0, 'letterhead.save', $label . ' saved');
        return $back->with('msg', $label . ' saved. New letters use it; letters already approved keep the letterhead they were approved with.');
    }

    /** A branch goes back to the company letterhead */
    public function letterheadReset(Request $request)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $branchId = (int) $request->input('branch');
        if ($branchId > 0) {
            DB::table('hrm_company_letterheads')->where('hlh_company_id', $h->company)->where('hlh_branch_id', $branchId)->delete();
            HrmLetters::log(0, 'letterhead.reset', ($this->branches($h->company)[$branchId] ?? 'Branch') . ' now uses the company letterhead');
        }
        return redirect()->route('hrm.letters.letterhead', ['branch' => $branchId])->with('msg', 'This branch now uses the company letterhead.');
    }

    // ------------------------------------------------------------------ signatories

    public function signatories()
    {
        $h = $this->letters();
        $this->canView($h);
        $list = DB::table('hrm_letter_signatories as s')->leftJoin('admin_user as u', 'u.au_id', '=', 's.hls_user_id')
            ->where('s.hls_company_id', $h->company)->orderByDesc('s.hls_status')->orderByDesc('s.hls_is_default')->orderBy('s.hls_name')
            ->get(['s.*', 'u.au_name', 'u.au_user_id']);
        $branches = $this->branches($h->company);
        $employees = $this->employees($h->company);
        $isAdmin = $h->isHrAdmin();
        return view('page.hrm.letters.signatories', compact('list', 'branches', 'employees', 'isAdmin'));
    }

    public function signatorySave(Request $request, $id = null)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'designation'      => 'required|string|max:100',
            'user_id'          => 'nullable|integer',
            'branch'           => 'nullable|integer|min:0',
            'can_approve'      => 'nullable|in:0,1',
            'is_default'       => 'nullable|in:0,1',
            'status'           => 'nullable|in:0,1',
            'signature'        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
            'remove_signature' => 'nullable|in:0,1',
        ], ['signature.max' => 'The signature image must be 1 MB or smaller.']);
        $back = redirect()->route('hrm.letters.signatories');
        $row = $id ? DB::table('hrm_letter_signatories')->where('hls_company_id', $h->company)->where('hls_id', (int) $id)->first() : null;
        if ($id && !$row) {
            return $back->with('errmsg', 'Signatory not found.');
        }
        $userId = !empty($data['user_id']) ? (int) $data['user_id'] : null;
        if ($userId && !DB::table('admin_user')->where('au_company_id', $h->company)->where('au_id', $userId)->exists()) {
            return $back->withInput()->with('errmsg', 'Choose an employee of this company.');
        }
        if (!empty($data['can_approve']) && !$userId) {
            return $back->withInput()->with('errmsg', 'To approve letters, the signatory must be linked to an ERP user (they approve from their own login).');
        }
        $branchId = (int) ($data['branch'] ?? 0);
        if ($branchId && !$this->branches($h->company)->has($branchId)) {
            $branchId = 0;
        }
        $values = [
            'hls_name' => trim($data['name']), 'hls_designation' => trim($data['designation']), 'hls_user_id' => $userId, 'hls_branch_id' => $branchId,
            'hls_can_approve' => (int) ($data['can_approve'] ?? 0), 'hls_is_default' => (int) ($data['is_default'] ?? 0), 'hls_status' => (int) ($data['status'] ?? 1),
        ];
        if ($request->hasFile('signature')) {
            $values['hls_signature'] = HrmLetters::storeImage($request->file('signature'), 'signature');
        } elseif (!empty($data['remove_signature'])) {
            $values['hls_signature'] = null;
        }
        DB::transaction(function () use (&$id, $values, $h, $branchId) {
            if ($values['hls_is_default']) {      // one default signatory per branch (0 = every branch)
                DB::table('hrm_letter_signatories')->where('hls_company_id', $h->company)->where('hls_branch_id', $branchId)
                    ->when($id, function ($q) use ($id) { $q->where('hls_id', '<>', $id); })->update(['hls_is_default' => 0]);
            }
            if ($id) {
                DB::table('hrm_letter_signatories')->where('hls_id', $id)->update($values + ['hls_updated_by' => Auth::user()->au_id, 'hls_updated_at' => Carbon::now()]);
            } else {
                $id = DB::table('hrm_letter_signatories')->insertGetId($values + ['hls_company_id' => $h->company, 'hls_created_by' => Auth::user()->au_id, 'hls_created_at' => Carbon::now()]);
            }
        });
        HrmLetters::log(0, $row ? 'signatory.edit' : 'signatory.add', 'Signatory ' . $values['hls_name'] . ' (' . $values['hls_designation'] . ')' . ($values['hls_can_approve'] ? ', can approve' : '') . ($values['hls_status'] ? '' : ', inactive'));
        return $back->with('msg', 'Signatory ' . $values['hls_name'] . ' saved.');
    }

    // ------------------------------------------------------------------ access

    public function access()
    {
        $h = $this->letters();
        $this->canView($h);
        $roles = DB::table('hrm_letter_roles as r')->join('admin_user as u', 'u.au_id', '=', 'r.hlr_user_id')
            ->leftJoin('admin_user as b', 'b.au_id', '=', 'r.hlr_created_by')
            ->where('r.hlr_company_id', $h->company)->orderBy('r.hlr_role')->orderBy('u.au_name')
            ->get(['r.*', 'u.au_name', 'u.au_user_id', 'u.au_branch_name', 'b.au_name as added_by']);
        $admins = DB::table('admin_user')->where('au_company_id', $h->company)->where('au_status', 1)->whereIn('au_user_type', [3, 4])->orderBy('au_name')->get(['au_name', 'au_user_type', 'au_branch_name']);
        $approvers = DB::table('hrm_letter_signatories as s')->join('admin_user as u', 'u.au_id', '=', 's.hls_user_id')
            ->where('s.hls_company_id', $h->company)->where('s.hls_can_approve', 1)->where('s.hls_status', 1)->get(['u.au_name', 's.hls_designation']);
        $heads = DB::table('admin_user as e')->join('admin_user as d', 'd.au_id', '=', 'e.au_dep_head_id')
            ->where('e.au_company_id', $h->company)->where('e.au_status', 1)->groupBy('d.au_id', 'd.au_name')
            ->selectRaw('d.au_name, COUNT(*) n')->get();
        $employees = $this->employees($h->company);
        $isAdmin = $h->isHrAdmin();
        return view('page.hrm.letters.access', compact('roles', 'admins', 'approvers', 'heads', 'employees', 'isAdmin'));
    }

    public function accessAdd(Request $request)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $data = $request->validate(['user_id' => 'required|integer', 'role' => 'required|in:' . implode(',', array_keys(HrmLetters::ROLES))], [], ['user_id' => 'employee']);
        $back = redirect()->route('hrm.letters.access');
        $user = DB::table('admin_user')->where('au_company_id', $h->company)->where('au_id', (int) $data['user_id'])->first();
        if (!$user) {
            return $back->with('errmsg', 'Choose an employee of this company.');
        }
        DB::table('hrm_letter_roles')->insertOrIgnore(['hlr_company_id' => $h->company, 'hlr_user_id' => $user->au_id, 'hlr_role' => $data['role'],
            'hlr_created_by' => Auth::user()->au_id, 'hlr_created_at' => Carbon::now()]);
        HrmLetters::log(0, 'role.add', $user->au_name . ' made ' . HrmLetters::ROLES[$data['role']]);
        return $back->with('msg', $user->au_name . ' is now ' . HrmLetters::ROLES[$data['role']] . '.');
    }

    public function accessRemove($id)
    {
        $h = $this->letters();
        $this->adminOnly($h);
        $row = DB::table('hrm_letter_roles as r')->join('admin_user as u', 'u.au_id', '=', 'r.hlr_user_id')
            ->where('r.hlr_company_id', $h->company)->where('r.hlr_id', (int) $id)->first(['r.*', 'u.au_name']);
        if ($row) {
            DB::table('hrm_letter_roles')->where('hlr_id', $row->hlr_id)->delete();
            HrmLetters::log(0, 'role.remove', $row->au_name . ' is no longer ' . (HrmLetters::ROLES[$row->hlr_role] ?? $row->hlr_role));
        }
        return redirect()->route('hrm.letters.access')->with('msg', $row ? $row->au_name . ' removed from ' . (HrmLetters::ROLES[$row->hlr_role] ?? $row->hlr_role) . '.' : 'Nothing to remove.');
    }
}

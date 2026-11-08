<?php

namespace App\Http\Controllers;

use App\Models\Admin_user;
use App\Models\Crm_lead_form;
use App\Models\Crm_lead_link;
use App\Models\Crm_lead_submission;
use App\Models\Sds_query_book;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * CRM automatic leads through a public form (Bangla).
 *  - Branch link (CRM > Auto Lead Form, admin): leads go to the chosen sales people in turn.
 *  - Employee link (CRM > My Lead Link, everyone): leads go to the employee who shared the link.
 * Each request creates a new lead in sds_query_book, the same as "Create Customer". Existing leads are
 * never changed: a mobile that is already in the CRM is only logged as a repeat request.
 */
class CrmLeadFormController extends Controller
{
    // ================================================================ shared helpers

    private function isCompanyAdmin()
    {
        return in_array((int) Auth::user()->au_user_type, [1, 3, 4], true);
    }

    /** The branch form (title, message, lead source, on/off); made with defaults the first time */
    private function branchForm($companyId, $branchId, $createdBy = null)
    {
        $form = Crm_lead_form::where('clf_company_id', $companyId)->where('clf_branch_id', (int) $branchId)->first();
        if (!$form) {
            $source = DB::table('sds_findus')->where('sf_status', 1)->where('sf_howto', 'like', '%Online%')->value('sf_slid')
                ?: DB::table('sds_findus')->where('sf_status', 1)->orderBy('sf_slid')->value('sf_slid') ?: 1;
            $form = Crm_lead_form::create([
                'clf_company_id' => $companyId,
                'clf_branch_id'  => (int) $branchId,
                'clf_token'      => Str::random(32),
                'clf_title'      => 'যোগাযোগ করুন',
                'clf_intro'      => 'আপনার নাম ও মোবাইল নম্বর দিন। আমরা খুব শিগগির আপনার সাথে যোগাযোগ করব।',
                'clf_title_en'   => 'Contact us',
                'clf_intro_en'   => 'Leave your name and mobile number. We will call you back soon.',
                'clf_lang'       => 'bn',
                'clf_find_us'    => $source,
                'clf_assignees'  => null,
                'clf_next'       => 0,
                'clf_active'     => 1,
                'clf_created_by' => $createdBy,
                'clf_created_at' => now(),
                'clf_updated_at' => now(),
            ]);
        }
        return $form;
    }

    /** Reasons the customer can pick: the company's own list, otherwise every active reason */
    private function reasonsFor($companyId)
    {
        $ids = DB::table('sds_reason_com')->where('src_company_id', $companyId)->pluck('src_reson_id')->all();
        $q = DB::table('sds_reason as r')->leftJoin('sds_reason_catagory as c', 'c.sc_slid', '=', 'r.sr_catagory')
            ->where('r.sr_status', 1)->orderBy('c.sc_catagory')->orderBy('r.sr_reason')
            ->select('r.sr_slid as id', 'r.sr_reason as name', 'c.sc_catagory as category');
        if ($ids) {
            $q->whereIn('r.sr_slid', $ids);
        }
        return $q->get();
    }

    /** Requests, newest first, with reason / district / thana names for showing in a table */
    private function describe($rows)
    {
        $reasons = DB::table('sds_reason')->whereIn('sr_slid', $rows->pluck('cls_reason_id')->filter()->unique()->all())->pluck('sr_reason', 'sr_slid');
        $districts = DB::table('districts')->whereIn('id', $rows->pluck('cls_district_id')->filter()->unique()->all())->pluck('bn_name', 'id');
        $thanas = DB::table('upazilas')->whereIn('id', $rows->pluck('cls_thana_id')->filter()->unique()->all())->pluck('bn_name', 'id');
        $people = Admin_user::whereIn('au_id', $rows->pluck('cls_assigned_to')->filter()->unique()->all())->pluck('au_name', 'au_id');
        return $rows->map(function ($r) use ($reasons, $districts, $thanas, $people) {
            $r->reason_name = $reasons[$r->cls_reason_id] ?? '';
            $r->place = trim(implode(', ', array_filter([$thanas[$r->cls_thana_id] ?? '', $districts[$r->cls_district_id] ?? ''])));
            $r->given_to = $people[$r->cls_assigned_to] ?? '';
            return $r;
        });
    }

    private function stats($query)
    {
        return [
            'today'   => (clone $query)->where('cls_status', 'new')->whereDate('cls_created_at', Carbon::today())->count(),
            'month'   => (clone $query)->where('cls_status', 'new')->where('cls_created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'total'   => (clone $query)->where('cls_status', 'new')->count(),
            'repeats' => (clone $query)->where('cls_status', 'duplicate')->count(),
        ];
    }

    // ================================================================ admin: branch link + settings

    private function salesPeople()
    {
        $u = Auth::user();
        return Admin_user::where('au_company_id', $u->au_company_id)
            ->where('au_branch_id', $u->au_branch_id)
            ->where('au_status', 1)
            ->whereIn('au_user_type', [5, 6, 7])
            ->orderBy('au_name')
            ->get(['au_id', 'au_name', 'au_user_type']);
    }

    public function settings()
    {
        if (!$this->isCompanyAdmin()) {
            return redirect('/')->with('error', 'Only the company admin can manage the lead form.');
        }
        $u = Auth::user();
        $form = $this->branchForm($u->au_company_id, $u->au_branch_id, $u->au_id);
        $sources = DB::table('sds_findus')->where('sf_status', 1)->orderBy('sf_slid')->pluck('sf_howto', 'sf_slid');
        $people = $this->salesPeople();
        $assigned = $form->assigneeIds();

        // all requests of this branch: branch link and employee links
        $base = Crm_lead_submission::where('cls_company_id', $u->au_company_id)->where('cls_branch_id', (int) $u->au_branch_id);
        $stats = $this->stats($base);
        $recent = $this->describe((clone $base)->orderByDesc('cls_id')->limit(15)->get());
        $link = url('lead-form/' . $form->clf_token);

        // employee links and how many leads each brought
        $employeeLinks = Crm_lead_link::where('cll_company_id', $u->au_company_id)->where('cll_branch_id', (int) $u->au_branch_id)->get();
        $linkCounts = Crm_lead_submission::whereIn('cls_link_id', $employeeLinks->pluck('cll_id')->all())->where('cls_status', 'new')
            ->selectRaw('cls_link_id, COUNT(*) total, SUM(cls_created_at >= ?) month', [Carbon::now()->startOfMonth()])
            ->groupBy('cls_link_id')->get()->keyBy('cls_link_id');
        $linkOwners = Admin_user::whereIn('au_id', $employeeLinks->pluck('cll_user_id')->all())->pluck('au_name', 'au_id');

        return view('page.crm.lead_form_settings', compact('form', 'sources', 'people', 'assigned', 'stats', 'recent', 'link',
            'employeeLinks', 'linkCounts', 'linkOwners'));
    }

    public function saveSettings(Request $request)
    {
        if (!$this->isCompanyAdmin()) {
            return redirect('/')->with('error', 'Only the company admin can manage the lead form.');
        }
        $u = Auth::user();
        $form = $this->branchForm($u->au_company_id, $u->au_branch_id, $u->au_id);
        $data = $request->validate([
            'clf_lang'     => 'required|in:bn,en',
            'clf_find_us' => 'required|integer',
            'assignees'   => 'array',
            'assignees.*' => 'integer',
        ], [], ['clf_find_us' => 'lead source', 'clf_lang' => 'language']);

        $allowed = $this->salesPeople()->pluck('au_id')->map(function ($id) { return (int) $id; })->all();
        $assignees = array_values(array_intersect(array_map('intval', $data['assignees'] ?? []), $allowed));

        $form->update([
            'clf_lang'       => $data['clf_lang'],
            'clf_find_us'    => (int) $data['clf_find_us'],
            'clf_assignees'  => $assignees ? implode(',', $assignees) : null,
            'clf_next'       => 0,
            'clf_active'     => $request->boolean('clf_active') ? 1 : 0,
            'clf_updated_at' => now(),
        ]);
        return redirect()->route('crm.lead-form')->with('msg', 'Lead form saved.');
    }

    public function newLink()
    {
        if (!$this->isCompanyAdmin()) {
            return redirect('/')->with('error', 'Only the company admin can manage the lead form.');
        }
        $u = Auth::user();
        $form = $this->branchForm($u->au_company_id, $u->au_branch_id, $u->au_id);
        $form->update(['clf_token' => Str::random(32), 'clf_updated_at' => now()]);
        return redirect()->route('crm.lead-form')->with('msg', 'New link made. The old link no longer works.');
    }

    // ================================================================ every employee: my own link

    private function myLinkRow()
    {
        $u = Auth::user();
        $link = Crm_lead_link::where('cll_company_id', $u->au_company_id)->where('cll_user_id', $u->au_id)->first();
        if (!$link) {
            $link = Crm_lead_link::create([
                'cll_company_id' => $u->au_company_id,
                'cll_branch_id'  => (int) $u->au_branch_id,
                'cll_user_id'    => $u->au_id,
                'cll_token'      => Str::random(32),
                'cll_active'     => 1,
                'cll_created_at' => now(),
            ]);
        } elseif ((int) $link->cll_branch_id !== (int) $u->au_branch_id) {
            $link->update(['cll_branch_id' => (int) $u->au_branch_id]); // moved to another branch
        }
        return $link;
    }

    public function myLink()
    {
        $u = Auth::user();
        if (!$u->au_company_id || !$u->au_branch_id) {
            return redirect('/')->with('error', 'A lead link needs a company branch.');
        }
        $row = $this->myLinkRow();
        $form = $this->branchForm($u->au_company_id, $u->au_branch_id);
        $link = url('lead-form/' . $row->cll_token);
        $base = Crm_lead_submission::where('cls_link_id', $row->cll_id);
        $stats = $this->stats($base);
        $recent = $this->describe((clone $base)->orderByDesc('cls_id')->limit(15)->get());
        return view('page.crm.my_lead_link', compact('row', 'form', 'link', 'stats', 'recent'));
    }

    public function myNewLink()
    {
        $row = $this->myLinkRow();
        $row->update(['cll_token' => Str::random(32)]);
        return redirect()->route('crm.my-lead-link')->with('msg', 'New link made. The old link no longer works.');
    }

    // ================================================================ public form (no login, Bangla)

    /** [branch form, employee link or null, link owner or null, company name, branch name] */
    private function publicForm($token)
    {
        $link = null;
        $owner = null;
        $form = Crm_lead_form::where('clf_token', $token)->first();
        if (!$form) {
            $link = Crm_lead_link::where('cll_token', $token)->where('cll_active', 1)->first();
            abort_unless($link, 404);
            $form = $this->branchForm($link->cll_company_id, $link->cll_branch_id);
            $owner = Admin_user::where('au_id', $link->cll_user_id)->where('au_company_id', $link->cll_company_id)->first();
        }
        $admin = Admin_user::where('au_company_id', $form->clf_company_id)->where('au_user_type', 4)->first();
        $company = $admin ? ($admin->au_company_name ?: $admin->au_name) : '';
        $branch = Admin_user::where('au_company_id', $form->clf_company_id)->where('au_branch_id', $form->clf_branch_id)->value('au_branch_name');
        return [$form, $link, $owner, $company, $branch];
    }

    /** Every text on the public form, in Bangla and English */
    public static function texts($lang)
    {
        $t = [
            'bn' => [
                'name' => 'নাম', 'mobile' => 'মোবাইল নম্বর', 'email' => 'ইমেইল', 'optional' => '(ঐচ্ছিক)',
                'district' => 'জেলা', 'thana' => 'থানা / উপজেলা', 'address' => 'ঠিকানা', 'reason' => 'কী বিষয়ে জানতে চান',
                'pick_district' => 'জেলা বেছে নিন', 'pick_thana' => 'থানা / উপজেলা বেছে নিন', 'district_first' => 'আগে জেলা বেছে নিন',
                'pick_reason' => 'বিষয় বেছে নিন', 'other' => 'অন্যান্য', 'address_hint' => 'বাড়ি, রোড, এলাকা',
                'no_match' => 'কিছু পাওয়া যায়নি',
                'mobile_hint' => '১১ সংখ্যার বাংলাদেশি মোবাইল নম্বর, যেমন 01712345678',
                'mobile_ok' => 'সঠিক নম্বর', 'send' => 'পাঠিয়ে দিন', 'sending' => 'পাঠানো হচ্ছে...', 'agent' => 'প্রতিনিধি',
                'thanks' => 'ধন্যবাদ!', 'thanks_text' => 'আপনার তথ্য আমরা পেয়েছি। খুব শিগগির আমরা আপনার সাথে যোগাযোগ করব।',
                'closed' => 'ফর্মটি এখন বন্ধ আছে', 'closed_text' => 'দয়া করে পরে আবার চেষ্টা করুন।',
                'privacy' => 'আপনার তথ্য শুধুমাত্র আপনার সাথে যোগাযোগের জন্য ব্যবহার করা হবে।',
                'e_name' => 'আপনার নাম লিখুন।', 'e_name_max' => 'নাম ৬০ অক্ষরের মধ্যে লিখুন।',
                'e_mobile' => 'আপনার মোবাইল নম্বর লিখুন।', 'e_mobile_bad' => 'সঠিক ১১ সংখ্যার বাংলাদেশি মোবাইল নম্বর লিখুন (013 থেকে 019 দিয়ে শুরু), যেমন 01712345678।',
                'e_email' => 'সঠিক ইমেইল ঠিকানা লিখুন, অথবা ঘরটি খালি রাখুন।', 'e_district' => 'তালিকা থেকে জেলা বেছে নিন।',
                'e_thana' => 'তালিকা থেকে থানা / উপজেলা বেছে নিন।', 'e_thana_district' => 'থানা / উপজেলা বেছে নেওয়া জেলার মধ্যে হতে হবে।',
                'e_address' => 'ঠিকানা ১৫০ অক্ষরের মধ্যে লিখুন।', 'e_reason' => 'তালিকা থেকে একটি বিষয় বেছে নিন।',
            ],
            'en' => [
                'name' => 'Name', 'mobile' => 'Mobile number', 'email' => 'Email', 'optional' => '(optional)',
                'district' => 'District', 'thana' => 'Thana / Upazila', 'address' => 'Address', 'reason' => 'What do you need?',
                'pick_district' => 'Choose a district', 'pick_thana' => 'Choose a thana / upazila', 'district_first' => 'Choose a district first',
                'pick_reason' => 'Choose a topic', 'other' => 'Other', 'address_hint' => 'House, road, area',
                'no_match' => 'Nothing found',
                'mobile_hint' => '11-digit Bangladeshi mobile number, like 01712345678',
                'mobile_ok' => 'Valid number', 'send' => 'Send', 'sending' => 'Sending...', 'agent' => 'Your contact',
                'thanks' => 'Thank you!', 'thanks_text' => 'We have your details and will contact you soon.',
                'closed' => 'This form is closed', 'closed_text' => 'Please try again later.',
                'privacy' => 'Your details are only used to contact you.',
                'e_name' => 'Please enter your name.', 'e_name_max' => 'Please keep your name within 60 characters.',
                'e_mobile' => 'Please enter your mobile number.', 'e_mobile_bad' => 'Please enter a valid 11-digit Bangladeshi mobile number (starting 013 to 019), like 01712345678.',
                'e_email' => 'Please enter a valid email address, or leave it empty.', 'e_district' => 'Please choose a district from the list.',
                'e_thana' => 'Please choose a thana / upazila from the list.', 'e_thana_district' => 'The thana / upazila must be in the chosen district.',
                'e_address' => 'Please keep the address within 150 characters.', 'e_reason' => 'Please choose a topic from the list.',
            ],
        ];
        return $t[$lang] ?? $t['bn'];
    }

    /** ?lang=bn|en from the visitor, otherwise the language the admin chose */
    private function langFor($form, Request $request = null)
    {
        $lang = ($request ?: request())->input('lang');
        return in_array($lang, ['bn', 'en'], true) ? $lang : ($form->clf_lang === 'en' ? 'en' : 'bn');
    }

    /** The FelnaTech logo (same as the ERP header) */
    private function logoFor($companyId)
    {
        return asset('assets/img/icons/logo.png');
    }

    private function formView($token, $form, $owner, $company, $branch, $done = false)
    {
        $lang = $this->langFor($form);
        $t = self::texts($lang);
        $districts = DB::table('districts')->orderBy($lang === 'en' ? 'name' : 'bn_name')->get(['id', 'bn_name', 'name']);
        $thanas = DB::table('upazilas')->orderBy($lang === 'en' ? 'name' : 'bn_name')->get(['id', 'district_id', 'bn_name', 'name']);
        // [id, district id, name in this language, name in the other language (for searching)]
        $thanaList = $thanas->map(function ($x) use ($lang) {
            return $lang === 'en'
                ? [$x->id, $x->district_id, $x->name ?: $x->bn_name, $x->bn_name]
                : [$x->id, $x->district_id, $x->bn_name ?: $x->name, $x->name];
        })->values()->all();
        $reasons = $this->reasonsFor($form->clf_company_id);
        $logo = $this->logoFor($form->clf_company_id);
        $title = $lang === 'en' ? ($form->clf_title_en ?: 'Contact us') : $form->clf_title;
        $intro = $lang === 'en' ? $form->clf_intro_en : $form->clf_intro;
        // the employee's name is shown only while they still work here
        $agent = ($owner && (int) $owner->au_status === 1) ? $owner->au_name : null;
        return view('page.crm.lead_form_public', compact('token', 'form', 'company', 'branch', 'districts', 'thanas', 'reasons',
            'agent', 'done', 'lang', 't', 'logo', 'title', 'intro', 'thanaList'));
    }

    public function show($token)
    {
        [$form, $link, $owner, $company, $branch] = $this->publicForm($token);
        return $this->formView($token, $form, $owner, $company, $branch);
    }

    public function submit(Request $request, $token)
    {
        [$form, $link, $owner, $company, $branch] = $this->publicForm($token);
        if (!$form->clf_active) {
            return $this->formView($token, $form, $owner, $company, $branch);
        }
        // bots fill the hidden "website" field; people never see it
        if (trim((string) $request->input('website')) !== '') {
            return $this->formView($token, $form, $owner, $company, $branch, true);
        }

        // mobile: Bangla digits, +88 / 88, spaces and dashes are all fine
        $mobile = strtr((string) $request->input('mobile'), ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        $mobile = preg_replace('/\D+/', '', $mobile);
        if (strlen($mobile) === 13 && substr($mobile, 0, 2) === '88') {
            $mobile = substr($mobile, 2);
        }
        $request->merge(['mobile_clean' => $mobile]);
        $reasonIds = $this->reasonsFor($form->clf_company_id)->pluck('id')->map(function ($id) { return (int) $id; })->all();

        $t = self::texts($this->langFor($form, $request));
        $data = $request->validate([
            'name'         => 'required|string|max:60',
            'mobile_clean' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email'        => 'nullable|email|max:80',
            'district_id'  => 'nullable|integer|exists:districts,id',
            'thana_id'     => 'nullable|integer|exists:upazilas,id',
            'address'      => 'nullable|string|max:150',
            'reason_id'    => ['nullable', 'integer', \Illuminate\Validation\Rule::in($reasonIds)],
        ], [
            'name.required'         => $t['e_name'],
            'name.max'              => $t['e_name_max'],
            'mobile_clean.required' => $t['e_mobile'],
            'mobile_clean.regex'    => $t['e_mobile_bad'],
            'email.email'           => $t['e_email'],
            'email.max'             => $t['e_email'],
            'district_id.integer'   => $t['e_district'],
            'district_id.exists'    => $t['e_district'],
            'thana_id.integer'      => $t['e_thana'],
            'thana_id.exists'       => $t['e_thana'],
            'address.max'           => $t['e_address'],
            'reason_id.integer'     => $t['e_reason'],
            'reason_id.in'          => $t['e_reason'],
        ]);

        // the thana must be in the chosen district
        $thana = !empty($data['thana_id']) ? DB::table('upazilas')->where('id', $data['thana_id'])->first() : null;
        if ($thana && !empty($data['district_id']) && (int) $thana->district_id !== (int) $data['district_id']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['thana_id' => $t['e_thana_district']]);
        }
        $districtId = $data['district_id'] ?? ($thana ? $thana->district_id : null);
        $district = $districtId ? DB::table('districts')->where('id', $districtId)->first() : null;

        $en = $this->langFor($form, $request) === 'en';
        $placeName = function ($row) use ($en) {
            return $row ? ($en ? ($row->name ?: $row->bn_name) : ($row->bn_name ?: $row->name)) : '';
        };
        DB::transaction(function () use ($form, $link, $owner, $data, $mobile, $request, $thana, $district, $placeName) {
            $locked = Crm_lead_form::where('clf_id', $form->clf_id)->lockForUpdate()->first();

            $existing = Sds_query_book::where('qb_company_id', $locked->clf_company_id)
                ->where(function ($q) use ($mobile) {
                    $q->where('qb_mobile', $mobile)->orWhere('qb_mobile1', $mobile)->orWhere('qb_mobile2', $mobile);
                })->first();

            $assignee = null;
            $qbId = $existing ? $existing->qb_id : null;

            if (!$existing) {
                if ($link && $owner && (int) $owner->au_status === 1) {
                    // shared by an employee: the customer is theirs
                    $assignee = (int) $owner->au_id;
                } else {
                    // branch link (or a link of someone who left): the chosen sales people in turn, else the admin
                    $ids = $locked->assigneeIds();
                    if ($ids) {
                        $assignee = $ids[$locked->clf_next % count($ids)];
                        $locked->clf_next = ($locked->clf_next + 1) % count($ids);
                        $locked->save();
                    } else {
                        $assignee = Admin_user::where('au_company_id', $locked->clf_company_id)->where('au_user_type', 4)->value('au_id');
                    }
                }

                $serial = (int) Sds_query_book::where('qb_company_id', $locked->clf_company_id)
                    ->where('qb_branch_id', $locked->clf_branch_id)->max('qb_serial');
                $address = implode(', ', array_filter([
                    trim((string) ($data['address'] ?? '')),
                    $placeName($thana),
                    $placeName($district),
                ]));

                // same fields as SdsController@create_customer_submit
                $lead = new Sds_query_book;
                $lead->qb_mobile = $mobile;
                $lead->qb_entry_by = $assignee;
                $lead->qb_staff_id = $assignee;
                $lead->qb_company_id = $locked->clf_company_id;
                $lead->qb_branch_id = $locked->clf_branch_id;
                $lead->qb_submit_date = Carbon::now()->format('Y-m-d');
                $lead->qb_serial = $serial + 1;
                $lead->qb_status = 1;
                $lead->qb_name = Str::limit($data['name'], 30, '');
                $lead->qb_address = $address !== '' ? Str::limit($address, 150, '') : null;
                $lead->qb_email = $data['email'] ?? null;
                $lead->qb_find_us = $locked->clf_find_us;
                $lead->qb_reason = $data['reason_id'] ?? null;
                $lead->qb_result = 1;      // 1 star: new lead, not called yet
                $lead->qb_cus_type = 1;    // Potential
                $lead->save();
                $qbId = $lead->qb_id;
            }

            Crm_lead_submission::create([
                'cls_form_id'     => $locked->clf_id,
                'cls_company_id'  => $locked->clf_company_id,
                'cls_branch_id'   => $locked->clf_branch_id,
                'cls_qb_id'       => $qbId,
                'cls_status'      => $existing ? 'duplicate' : 'new',
                'cls_name'        => $data['name'],
                'cls_mobile'      => $mobile,
                'cls_email'       => $data['email'] ?? null,
                'cls_assigned_to' => $existing ? $existing->qb_entry_by : $assignee,
                'cls_link_id'     => $link ? $link->cll_id : null,
                'cls_district_id' => $district ? $district->id : null,
                'cls_thana_id'    => $thana ? $thana->id : null,
                'cls_reason_id'   => $data['reason_id'] ?? null,
                'cls_address'     => isset($data['address']) ? Str::limit($data['address'], 150, '') : null,
                'cls_ip'          => $request->ip(),
                'cls_created_at'  => now(),
            ]);
        });

        return $this->formView($token, $form, $owner, $company, $branch, true);
    }
}

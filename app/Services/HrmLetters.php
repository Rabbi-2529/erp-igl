<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * HRM Letters: letter types and their reference prefixes, who may do what, the letterhead PAD,
 * reference numbers (PREFIXYYYYMMDDXXX, no dashes) and the module history. Writes only to the hrm_letter* tables.
 */
class HrmLetters
{
    public $company;
    public $branch;

    /** Standard letter types: code => [name, prefix, default subject, extra fields, department head reviews it] */
    const TYPES = [
        'appointment'            => ['Appointment Letter', 'APP', 'Letter of Appointment', 'effective_date', false],
        'offer'                  => ['Offer Letter', 'OFF', 'Offer of Employment', 'effective_date,reply_by', false],
        'joining'                => ['Joining Letter', 'JOI', 'Joining Letter', 'effective_date', false],
        'employment_certificate' => ['Employment Certificate', 'EMP', 'Employment Certificate', '', false],
        'experience'             => ['Experience Certificate', 'EXP', 'Experience Certificate', 'last_working_day', false],
        'salary_certificate'     => ['Salary Certificate', 'SAL', 'Salary Certificate', '', false],
        'promotion'              => ['Promotion Letter', 'PRO', 'Promotion', 'effective_date,new_designation,new_salary', false],
        'increment'              => ['Increment Letter', 'INC', 'Salary Increment', 'effective_date,increment_amount,new_salary', false],
        'transfer'               => ['Transfer Letter', 'TRF', 'Transfer Order', 'effective_date,transfer_branch', false],
        'department_change'      => ['Department Change Letter', 'DEP', 'Change of Department', 'effective_date,new_department', false],
        'designation_change'     => ['Designation Change Letter', 'DES', 'Change of Designation', 'effective_date,new_designation', false],
        'warning'                => ['Warning Letter', 'WRN', 'Letter of Warning', 'reason', true],
        'show_cause'             => ['Show Cause Letter', 'SCN', 'Show Cause Notice', 'reason,reply_by', true],
        'termination'            => ['Termination Letter', 'TER', 'Termination of Employment', 'effective_date,last_working_day,reason', true],
        'resignation_acceptance' => ['Resignation Acceptance Letter', 'RES', 'Acceptance of Resignation', 'last_working_day', false],
        'relieving'              => ['Relieving Letter', 'REL', 'Relieving Letter', 'last_working_day', false],
        'noc'                    => ['No Objection Certificate (NOC)', 'NOC', 'No Objection Certificate', 'reason', false],
        'leave_approval'         => ['Leave Approval Letter', 'LVA', 'Approval of Leave', 'from_date,to_date,reason', false],
        'confirmation'           => ['Confirmation Letter', 'CNF', 'Confirmation of Employment', 'effective_date', false],
        'contract_renewal'       => ['Contract Renewal Letter', 'CRN', 'Renewal of Employment Contract', 'effective_date,contract_end,new_salary', false],
        'suspension'             => ['Suspension Letter', 'SUS', 'Suspension from Duty', 'from_date,to_date,reason', true],
        'clearance'              => ['Clearance Letter', 'CLR', 'Clearance Certificate', 'last_working_day', false],
        'custom'                 => ['Custom HRM Letter', 'CUS', '', '', false],
    ];

    /** Extra fields a letter type can ask for: key => [label, input type]. Each one is also a placeholder, e.g. {{effective_date}} */
    const FIELDS = [
        'effective_date'   => ['Effective date', 'date'],
        'new_designation'  => ['New designation', 'text'],
        'new_department'   => ['New department', 'text'],
        'new_salary'       => ['New salary', 'number'],
        'increment_amount' => ['Increment amount', 'number'],
        'transfer_branch'  => ['Transfer to (branch / place)', 'text'],
        'from_date'        => ['From date', 'date'],
        'to_date'          => ['To date', 'date'],
        'last_working_day' => ['Last working day', 'date'],
        'contract_end'     => ['Contract ends on', 'date'],
        'reply_by'         => ['Reply / accept by', 'date'],
        'reason'           => ['Reason / details', 'textarea'],
    ];

    const ROLES = ['hr_admin' => 'HR Admin', 'hr_executive' => 'HR Executive'];
    const UPLOAD_DIR = 'public/uploads/hrm_letters';

    public function __construct($companyId, $branchId)
    {
        $this->company = (int) $companyId;
        $this->branch = (int) $branchId;
    }

    public static function forUser($user = null)
    {
        $user = $user ?: Auth::user();
        return new self($user->au_company_id, $user->au_branch_id);
    }

    // ------------------------------------------------------------------ who may do what

    /** Root, company owner or company admin */
    public static function isCompanyAdmin($user = null)
    {
        $user = $user ?: Auth::user();
        return $user && in_array((int) $user->au_user_type, [1, 3, 4], true);
    }

    private function hasRole($user, $role)
    {
        return DB::table('hrm_letter_roles')->where('hlr_company_id', $this->company)->where('hlr_user_id', $user->au_id)->where('hlr_role', $role)->exists();
    }

    /** HR Admin: manages all letters, templates and settings (company admins always are) */
    public function isHrAdmin($user = null)
    {
        $user = $user ?: Auth::user();
        return self::isCompanyAdmin($user) || $this->hasRole($user, 'hr_admin');
    }

    /** HR Executive: makes and manages draft letters */
    public function isHrExecutive($user = null)
    {
        $user = $user ?: Auth::user();
        return $this->isHrAdmin($user) || $this->hasRole($user, 'hr_executive');
    }

    /** Authorized approver: an active signatory who may approve, linked to this user (HR Admins may approve too) */
    public function canApprove($user = null)
    {
        $user = $user ?: Auth::user();
        return $this->isHrAdmin($user) || DB::table('hrm_letter_signatories')->where('hls_company_id', $this->company)
            ->where('hls_user_id', $user->au_id)->where('hls_can_approve', 1)->where('hls_status', 1)->exists();
    }

    /** Department head of an employee (from the employee record) */
    public function isDeptHeadOf($employeeId, $user = null)
    {
        $user = $user ?: Auth::user();
        return (int) DB::table('admin_user')->where('au_id', (int) $employeeId)->where('au_company_id', $this->company)->value('au_dep_head_id') === (int) $user->au_id;
    }

    /** What a user is in the module, for the screens */
    public function rolesOf($user = null)
    {
        $user = $user ?: Auth::user();
        $out = [];
        if ($this->isHrAdmin($user)) { $out[] = 'HR Admin'; }
        elseif ($this->hasRole($user, 'hr_executive')) { $out[] = 'HR Executive'; }
        if (DB::table('hrm_letter_signatories')->where('hls_company_id', $this->company)->where('hls_user_id', $user->au_id)->where('hls_can_approve', 1)->where('hls_status', 1)->exists()) {
            $out[] = 'Approver';
        }
        if (DB::table('admin_user')->where('au_company_id', $this->company)->where('au_dep_head_id', $user->au_id)->exists()) {
            $out[] = 'Department Head';
        }
        return $out ?: ['Employee'];
    }

    // ------------------------------------------------------------------ letter types

    /** Adds the standard letter types the company does not have yet (new rows in hrm_letter_types only) */
    public function ensureTypes()
    {
        $have = DB::table('hrm_letter_types')->where('hlt_company_id', $this->company)->whereNotNull('hlt_code')->pluck('hlt_code')->all();
        $missing = array_diff(array_keys(self::TYPES), $have);
        if (!$missing) {
            return;
        }
        $prefixes = DB::table('hrm_letter_types')->where('hlt_company_id', $this->company)->pluck('hlt_prefix')->map(function ($p) { return strtoupper($p); })->all();
        $sort = array_flip(array_keys(self::TYPES));
        foreach ($missing as $code) {
            [$name, $prefix, $subject, $fields, $review] = self::TYPES[$code];
            $use = $prefix;
            for ($n = 1; in_array($use, $prefixes, true); $n++) {
                $use = substr($prefix, 0, 3) . $n;
            }
            $prefixes[] = $use;
            DB::table('hrm_letter_types')->insertOrIgnore([
                'hlt_company_id' => $this->company, 'hlt_code' => $code, 'hlt_name' => $name, 'hlt_prefix' => $use, 'hlt_subject' => $subject ?: null,
                'hlt_fields' => $fields ?: null, 'hlt_needs_review' => $review ? 1 : 0, 'hlt_sort' => ($sort[$code] + 1) * 10, 'hlt_status' => 1,
                'hlt_created_by' => optional(Auth::user())->au_id, 'hlt_created_at' => Carbon::now(),
            ]);
        }
    }

    public function types($activeOnly = false)
    {
        $this->ensureTypes();
        return DB::table('hrm_letter_types')->where('hlt_company_id', $this->company)
            ->when($activeOnly, function ($q) { $q->where('hlt_status', 1); })
            ->orderBy('hlt_sort')->orderBy('hlt_name')->get()
            ->map(function ($t) {
                $t->field_list = array_values(array_filter(array_map('trim', explode(',', (string) $t->hlt_fields)), function ($f) { return isset(self::FIELDS[$f]); }));
                return $t;
            });
    }

    // ------------------------------------------------------------------ templates

    /** Gives each standard letter type that has no template yet its standard template (new rows only) */
    public function ensureTemplates()
    {
        $types = $this->types();
        $have = DB::table('hrm_letter_templates')->where('hltp_company_id', $this->company)->pluck('hltp_type_id')->unique()->all();
        $defaults = HrmLetterDefaults::all();
        foreach ($types as $t) {
            if (in_array($t->hlt_id, $have) || !$t->hlt_code || !isset($defaults[$t->hlt_code])) {
                continue;
            }
            [$subject, $body] = $defaults[$t->hlt_code];
            DB::table('hrm_letter_templates')->insert([
                'hltp_company_id' => $this->company, 'hltp_type_id' => $t->hlt_id, 'hltp_name' => 'Standard',
                'hltp_subject' => $subject ?: $t->hlt_subject, 'hltp_body' => $body, 'hltp_lang' => 'en', 'hltp_is_default' => 1, 'hltp_status' => 1,
                'hltp_created_by' => optional(Auth::user())->au_id, 'hltp_created_at' => Carbon::now(),
            ]);
        }
    }

    /** The template a new letter of a type starts from: its default, else its first active one */
    public function defaultTemplate($typeId)
    {
        return DB::table('hrm_letter_templates')->where('hltp_company_id', $this->company)->where('hltp_type_id', (int) $typeId)
            ->where('hltp_status', 1)->orderByDesc('hltp_is_default')->orderBy('hltp_id')->first();
    }

    /** The signatory a new letter starts with: the branch's default, else the company default, else the first active one */
    public function defaultSignatory($branchId = null)
    {
        $branchId = $branchId === null ? $this->branch : (int) $branchId;
        return DB::table('hrm_letter_signatories')->where('hls_company_id', $this->company)->where('hls_status', 1)
            ->whereIn('hls_branch_id', [0, $branchId])
            ->orderByRaw('hls_is_default DESC, hls_branch_id = ? DESC, hls_id', [$branchId])->first();
    }

    // ------------------------------------------------------------------ letterhead PAD

    /** Company details already in the ERP, used where the letterhead leaves a field empty */
    public function erpDetails($branchId = null)
    {
        $branchId = $branchId === null ? $this->branch : (int) $branchId;
        $admin = DB::table('admin_user')->where('au_company_id', $this->company)->where('au_user_type', 4)->orderBy('au_id')->first();
        $branch = $branchId ? DB::table('auth_branch')->where('company_id', $this->company)->where('branch_id', $branchId)->first() : null;
        $address = optional($admin)->au_address;
        if ($branch && $branch->branch_address) {
            $address = trim($branch->branch_address);
            if ($branch->branch_city && stripos($address, $branch->branch_city) === false) {
                $address .= ', ' . $branch->branch_city;
            }
            if ($branch->branch_zip && strpos($address, $branch->branch_zip) === false) {
                $address .= ' - ' . $branch->branch_zip;
            }
        }
        return (object) [
            'company_name' => optional($admin)->au_company_name,
            'branch_name'  => $branch->branch_name ?? optional(DB::table('admin_user')->where('au_company_id', $this->company)->where('au_branch_id', $branchId)->first())->au_branch_name,
            'address'      => $address,
            'phone'        => ($branch->branch_hotline ?? null) ?: optional($admin)->au_mobile,
            'email'        => ($branch->branch_email ?? null) ?: optional($admin)->au_email,
        ];
    }

    /** Saved letterhead of a branch (branch 0 = company), or null */
    public function savedLetterhead($branchId)
    {
        return DB::table('hrm_company_letterheads')->where('hlh_company_id', $this->company)->where('hlh_branch_id', (int) $branchId)->first();
    }

    /**
     * The letterhead a branch prints with: its own letterhead, else the company letterhead,
     * with empty fields filled from the company and branch details in the ERP.
     */
    public function letterhead($branchId = null)
    {
        $branchId = $branchId === null ? $this->branch : (int) $branchId;
        $own = $this->savedLetterhead($branchId);
        $company = $this->savedLetterhead(0);
        $erp = $this->erpDetails($branchId);
        $pick = function ($field, $fallback = null) use ($own, $company) {
            $col = 'hlh_' . $field;
            foreach ([$own, $company] as $row) {
                if ($row && isset($row->$col) && $row->$col !== null && $row->$col !== '') {
                    return $row->$col;
                }
            }
            return $fallback;
        };
        $logo = $pick('logo');
        $seal = $pick('seal');
        $source = $branchId === 0 ? ($company ? 'company' : 'erp') : ($own ? 'branch' : ($company ? 'company' : 'erp'));
        $base = $own ?: $company;
        return (object) [
            'source'       => $source,
            'branch_id'    => $branchId,
            'branch_name'  => $erp->branch_name,
            'company_name' => $pick('company_name', $erp->company_name),
            'tagline'      => $pick('tagline'),
            'address'      => $pick('address', $erp->address),
            'phone'        => $pick('phone', $erp->phone),
            'email'        => $pick('email', $erp->email),
            'website'      => $pick('website'),
            'footer_text'  => $pick('footer_text'),
            'accent'       => $pick('accent', '#0e8a43'),
            'logo'         => $logo ?: 'assets/img/icons/logo.png',
            'seal'         => $seal,
            'margin_top'    => (int) ($base->hlh_margin_top ?? 12),
            'margin_bottom' => (int) ($base->hlh_margin_bottom ?? 12),
            'margin_side'   => (int) ($base->hlh_margin_side ?? 18),
            'page_numbers'  => (bool) ($base->hlh_page_numbers ?? 1),
        ];
    }

    // ------------------------------------------------------------------ images

    /** Saves an uploaded logo / seal / signature image and returns its path (relative to the ERP folder) */
    public static function storeImage(UploadedFile $file, $kind)
    {
        $dir = base_path(self::UPLOAD_DIR);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        // the extension comes from the file's real content, never from its uploaded name (a "seal.html" stays a picture)
        $ext = strtolower((string) $file->guessExtension());
        $ext = ['jpeg' => 'jpg', 'jpg' => 'jpg', 'png' => 'png', 'webp' => 'webp', 'gif' => 'gif'][$ext] ?? null;
        if (!$ext) {
            throw new \RuntimeException('Only PNG, JPG or WEBP pictures can be uploaded.');
        }
        $name = $kind . '-' . Carbon::now()->format('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($dir, $name);
        return self::UPLOAD_DIR . '/' . $name;
    }

    /** Web address of a saved image */
    public static function imageUrl($path)
    {
        return $path ? asset($path) : null;
    }

    /** The image itself as a data URI, for PDFs (dompdf cannot always load by URL) */
    public static function imageData($path)
    {
        $file = $path ? base_path($path) : null;
        if (!$file || !is_file($file)) {
            return null;
        }
        return 'data:' . mime_content_type($file) . ';base64,' . base64_encode(file_get_contents($file));
    }

    // ------------------------------------------------------------------ reference numbers

    /**
     * A new reference number PREFIXYYYYMMDDXXX, e.g. JOI20261108645 (XXX random 100-999, no dashes) that no letter of the company has used.
     * Call it inside the transaction that saves the letter; the unique index stops a clash at the same moment.
     */
    public function newReference($prefix, Carbon $date)
    {
        $base = strtoupper($prefix) . $date->format('Ymd');
        $used = DB::table('hrm_letters')->where('hl_company_id', $this->company)->where('hl_reference', 'like', $base . '%')
            ->pluck('hl_reference')->map(function ($r) use ($base) { return (int) substr($r, strlen($base)); })->all();
        $free = array_values(array_diff(range(100, 999), $used));
        if (!$free) {
            throw new \RuntimeException('All 900 reference numbers for ' . strtoupper($prefix) . ' on ' . $date->format('d M Y') . ' are used. Use another date or prefix.');
        }
        for ($i = 0; $i < 30; $i++) {
            $ref = $base . $free[random_int(0, count($free) - 1)];
            if (!DB::table('hrm_letters')->where('hl_company_id', $this->company)->where('hl_reference', $ref)->exists()) {
                return $ref;
            }
        }
        throw new \RuntimeException('Could not find a free reference number, please try again.');
    }

    // ------------------------------------------------------------------ history

    /** One line of history: a letter's step, or a setup change (letter 0) */
    public static function log($letterId, $action, $note = null, $from = null, $to = null)
    {
        DB::table('hrm_letter_approvals')->insert([
            'hla_letter_id' => (int) $letterId, 'hla_action' => mb_substr($action, 0, 20), 'hla_from_status' => $from, 'hla_to_status' => $to,
            'hla_user_id' => optional(Auth::user())->au_id, 'hla_note' => $note !== null ? mb_substr($note, 0, 255) : null,
            'hla_ip' => request() ? mb_substr((string) request()->ip(), 0, 45) : null, 'hla_created_at' => Carbon::now(),
        ]);
    }
}

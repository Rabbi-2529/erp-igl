<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * HRM Letters: an employee asks HR for a letter (salary certificate, experience certificate, NOC ...).
 *
 * The request waits for HR. HR makes the letter from it: a normal draft with the employee's details, which then goes
 * through the usual review and approval. Or HR declines it with a reason. The employee sees how far it is, and the
 * finished letter appears in their My Letters. Writes only to hrm_letter_requests and hrm_letter_request_types.
 */
class HrmLetterRequests
{
    /** Letter types employees may ask for unless an admin changes it */
    const DEFAULT_TYPES = ['employment_certificate', 'experience', 'salary_certificate', 'noc', 'relieving', 'clearance', 'custom'];

    /** What the employee and HR see: key => [label, label class] */
    const SHOWN = [
        'pending'   => ['Waiting for HR', 'label-warning'],
        'preparing' => ['Being prepared', 'label-info'],
        'ready'     => ['Ready', 'label-success'],
        'declined'  => ['Declined', 'label-danger'],
        'withdrawn' => ['Withdrawn', 'label-default'],
        'cancelled' => ['Cancelled', 'label-default'],
    ];

    public $letters;

    private static $ready = null;

    public function __construct(HrmLetters $letters)
    {
        $this->letters = $letters;
    }

    public static function forUser()
    {
        return new self(HrmLetters::forUser());
    }

    /** False until database/hrm_letter_requests.sql has been run (the pages then simply hide requests) */
    public static function ready()
    {
        if (self::$ready === null) {
            try {
                self::$ready = Schema::hasTable('hrm_letter_requests') && Schema::hasTable('hrm_letter_request_types');
            } catch (\Throwable $e) {
                self::$ready = false;
            }
        }
        return self::$ready;
    }

    // ------------------------------------------------------------------ which letters can be asked for

    /** [type id => allowed] as an admin set it; types not in the list use DEFAULT_TYPES */
    private function settings()
    {
        return DB::table('hrm_letter_request_types')->where('hlrt_company_id', $this->letters->company)->pluck('hlrt_allowed', 'hlrt_type_id')->all();
    }

    public function isRequestable($type, $settings = null)
    {
        $settings = $settings ?? $this->settings();
        if (array_key_exists($type->hlt_id, $settings)) {
            return (bool) $settings[$type->hlt_id];
        }
        return in_array($type->hlt_code, self::DEFAULT_TYPES, true);
    }

    /** Letter types switched on that employees may ask for */
    public function requestableTypes()
    {
        $settings = $this->settings();
        return $this->letters->types(true)->filter(function ($t) use ($settings) { return $this->isRequestable($t, $settings); })->values();
    }

    /** All types with a flag saying whether employees may ask for them (for the Letter Types page) */
    public function requestableIds()
    {
        $settings = $this->settings();
        return $this->letters->types()->filter(function ($t) use ($settings) { return $this->isRequestable($t, $settings); })->pluck('hlt_id')->all();
    }

    public function setRequestable($typeId, $allowed)
    {
        DB::table('hrm_letter_request_types')->updateOrInsert(['hlrt_company_id' => $this->letters->company, 'hlrt_type_id' => (int) $typeId],
            ['hlrt_allowed' => $allowed ? 1 : 0, 'hlrt_updated_by' => Auth::user()->au_id, 'hlrt_updated_at' => Carbon::now()]);
    }

    // ------------------------------------------------------------------ reading

    /** Requests with the letter type and, once made, the letter's status */
    public function query()
    {
        return DB::table('hrm_letter_requests as q')
            ->join('hrm_letter_types as t', 't.hlt_id', '=', 'q.hlq_type_id')
            ->leftJoin('hrm_letters as l', 'l.hl_id', '=', 'q.hlq_letter_id')
            ->leftJoin('admin_user as e', 'e.au_id', '=', 'q.hlq_employee_id')
            ->leftJoin('admin_user as h', 'h.au_id', '=', 'q.hlq_handled_by')
            ->where('q.hlq_company_id', $this->letters->company)
            ->select(['q.*', 't.hlt_name', 't.hlt_status', 'l.hl_reference', 'l.hl_status', 'e.au_name', 'e.au_user_id', 'h.au_name as handled_name']);
    }

    public function find($id)
    {
        return $this->query()->where('q.hlq_id', (int) $id)->first();
    }

    /** One word for where a request stands (see SHOWN) */
    public static function stage($r)
    {
        if ($r->hlq_status !== 'made') {
            return $r->hlq_status;
        }
        if (in_array($r->hl_status, ['approved', 'issued'], true)) {
            return 'ready';
        }
        return $r->hl_status === 'cancelled' || $r->hl_status === null ? 'cancelled' : 'preparing';
    }

    public function pendingCount()
    {
        return self::ready() ? DB::table('hrm_letter_requests')->where('hlq_company_id', $this->letters->company)->where('hlq_status', 'pending')->count() : 0;
    }

    /** An open request of the same letter for this employee (waiting, or made and not finished yet) */
    public function openOne($employeeId, $typeId)
    {
        return $this->query()->where('q.hlq_employee_id', (int) $employeeId)->where('q.hlq_type_id', (int) $typeId)
            ->where(function ($w) {
                $w->where('q.hlq_status', 'pending')
                  ->orWhere(function ($m) { $m->where('q.hlq_status', 'made')->whereIn('l.hl_status', ['draft', 'submitted', 'reviewed', 'rejected']); });
            })->first();
    }

    // ------------------------------------------------------------------ changes

    public function create($employee, $type, array $data)
    {
        $id = DB::table('hrm_letter_requests')->insertGetId([
            'hlq_company_id' => $this->letters->company, 'hlq_branch_id' => (int) ($employee->au_branch_id ?? 0), 'hlq_employee_id' => $employee->au_id,
            'hlq_type_id' => $type->hlt_id, 'hlq_purpose' => $data['purpose'], 'hlq_addressed_to' => $data['addressed_to'] ?: null,
            'hlq_needed_by' => $data['needed_by'] ?: null, 'hlq_note' => $data['note'] ?: null, 'hlq_status' => 'pending',
            'hlq_created_at' => Carbon::now(), 'hlq_updated_at' => Carbon::now(),
        ]);
        HrmLetters::log(0, 'request.new', $employee->au_name . ' asked for a ' . $type->hlt_name . ' (request #' . $id . '): ' . $data['purpose']);
        return $id;
    }

    /**
     * HR makes the letter: a draft for the employee with the default template and signatory. The purpose goes into
     * the letter's "Reason" detail when the letter uses one. Returns the letter id.
     */
    public function makeLetter($r)
    {
        return DB::transaction(function () use ($r) {
            $now = DB::table('hrm_letter_requests')->where('hlq_id', $r->hlq_id)->lockForUpdate()->value('hlq_status');
            if ($now !== 'pending') {
                throw new \RuntimeException('This request was already handled a moment ago.');
            }
            $type = DB::table('hrm_letter_types')->where('hlt_company_id', $this->letters->company)->where('hlt_id', $r->hlq_type_id)->first();
            if (!$type || !$type->hlt_status) {
                throw new \RuntimeException('The letter type "' . ($type->hlt_name ?? '') . '" is switched off. Switch it on under Letter Types first.');
            }
            $template = $this->letters->defaultTemplate($type->hlt_id);
            if (!$template) {
                throw new \RuntimeException('The letter type "' . $type->hlt_name . '" has no template yet. Add one under Templates first.');
            }
            $flow = new HrmLetterFlow($this->letters);
            $letterId = $flow->create($r->hlq_employee_id, $type, $template, Carbon::today());
            $l = $flow->find($letterId);
            if (in_array('reason', $l->field_list, true) || in_array('reason', HrmLetterContent::used($l->hl_body . ' ' . $l->hl_subject), true)) {
                DB::table('hrm_letters')->where('hl_id', $letterId)->update(['hl_fields' => json_encode(array_merge($l->fields, ['reason' => $r->hlq_purpose]))]);
            }
            DB::table('hrm_letter_requests')->where('hlq_id', $r->hlq_id)->update(['hlq_status' => 'made', 'hlq_letter_id' => $letterId,
                'hlq_handled_by' => Auth::user()->au_id, 'hlq_handled_at' => Carbon::now(), 'hlq_updated_at' => Carbon::now()]);
            HrmLetters::log($letterId, 'from_request', 'Made from the employee\'s request #' . $r->hlq_id . ' of ' . Carbon::parse($r->hlq_created_at)->format('d M Y') . ': ' . $r->hlq_purpose);
            return $letterId;
        });
    }

    public function decline($r, $reason)
    {
        return $this->close($r, 'declined', $reason, 'request.decline', 'Request #' . $r->hlq_id . ' of ' . $r->au_name . ' for a ' . $r->hlt_name . ' declined: ' . $reason);
    }

    public function withdraw($r)
    {
        return $this->close($r, 'withdrawn', null, 'request.withdraw', $r->au_name . ' withdrew request #' . $r->hlq_id . ' for a ' . $r->hlt_name);
    }

    /** Only a request still waiting can be declined or withdrawn; false when someone else handled it first */
    private function close($r, $status, $answer, $action, $note)
    {
        $done = DB::transaction(function () use ($r, $status, $answer) {
            $now = DB::table('hrm_letter_requests')->where('hlq_id', $r->hlq_id)->lockForUpdate()->value('hlq_status');
            if ($now !== 'pending') {
                return false;
            }
            DB::table('hrm_letter_requests')->where('hlq_id', $r->hlq_id)->update(['hlq_status' => $status, 'hlq_answer' => $answer,
                'hlq_handled_by' => Auth::user()->au_id, 'hlq_handled_at' => Carbon::now(), 'hlq_updated_at' => Carbon::now()]);
            return true;
        });
        if ($done) {
            HrmLetters::log(0, $action, $note);
        }
        return $done;
    }
}

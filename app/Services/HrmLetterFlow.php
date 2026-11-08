<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * HRM Letters (Phase 3): making a letter, who may do what with it, showing it on the letterhead, and freezing it on approval.
 *
 * While a letter is a draft its text keeps the placeholders, so corrected details show at once. When it is approved,
 * the filled text, subject, employee details, letterhead and signatory are frozen into the letter (hl_approved_at set):
 * later changes to the employee, template, letterhead or signatory never change an approved letter.
 */
class HrmLetterFlow
{
    const STATUS = [
        'draft'     => ['Draft', 'label-default'],
        'submitted' => ['Submitted', 'label-warning'],
        'reviewed'  => ['Reviewed', 'label-info'],
        'approved'  => ['Approved', 'label-success'],
        'rejected'  => ['Rejected', 'label-danger'],
        'issued'    => ['Issued', 'label-primary'],
        'cancelled' => ['Cancelled', 'label-default'],
    ];

    /** Employee details that can be corrected on a letter (the rest come from the employee record) */
    const EDITABLE_EMPLOYEE = ['designation' => 'Designation', 'department' => 'Department', 'joining_date' => 'Joining date', 'salary' => 'Monthly salary'];

    public $letters;

    public function __construct(HrmLetters $letters)
    {
        $this->letters = $letters;
    }

    public static function forUser()
    {
        return new self(HrmLetters::forUser());
    }

    // ------------------------------------------------------------------ reading

    public function find($id)
    {
        $l = DB::table('hrm_letters as l')->join('hrm_letter_types as t', 't.hlt_id', '=', 'l.hl_type_id')
            ->leftJoin('admin_user as c', 'c.au_id', '=', 'l.hl_created_by')
            ->leftJoin('admin_user as e', 'e.au_id', '=', 'l.hl_employee_id')
            ->where('l.hl_company_id', $this->letters->company)->where('l.hl_id', (int) $id)
            ->first(['l.*', 't.hlt_name', 't.hlt_prefix', 't.hlt_code', 't.hlt_needs_review', 't.hlt_fields', 'c.au_name as created_name', 'e.au_dep_head_id as dep_head_now']);
        if ($l) {
            // review and viewing follow the employee's department head today (as the letter list does), not the one when the draft was made
            $l->dep_head_now = (int) ($l->dep_head_now ?? 0);
            $l->employee = (object) (json_decode((string) $l->hl_employee, true) ?: []);
            $l->fields = json_decode((string) $l->hl_fields, true) ?: [];
            // the fields the letter type asks for, plus any letter field its text uses (a template may add one, e.g. {{reason}})
            $wanted = array_merge(array_map('trim', explode(',', (string) $l->hlt_fields)), HrmLetterContent::used($l->hl_body . ' ' . $l->hl_subject),
                array_keys(array_filter($l->fields, function ($v) { return trim((string) $v) !== ''; })));
            $l->field_list = array_values(array_intersect(array_keys(HrmLetters::FIELDS), $wanted));
            $l->frozen = $l->hl_approved_at !== null;
        }
        return $l;
    }

    public static function statusLabel($status)
    {
        return self::STATUS[$status][0] ?? ucfirst($status);
    }

    /** Human name of a placeholder */
    public static function label($key)
    {
        foreach (HrmLetterContent::PLACEHOLDERS as $group) {
            if (isset($group[$key])) {
                return $group[$key];
            }
        }
        return HrmLetters::FIELDS[$key][0] ?? $key;
    }

    // ------------------------------------------------------------------ who may do what

    /** What the letter waits for: 'review' (department head), 'approval', or null */
    public function awaiting($l)
    {
        if ($l->hl_status === 'submitted') {
            return $l->hlt_needs_review && $l->dep_head_now ? 'review' : 'approval';
        }
        return $l->hl_status === 'reviewed' ? 'approval' : null;
    }

    public function isDeptHead($l, $user = null)
    {
        $user = $user ?: Auth::user();
        return $l->dep_head_now && $l->dep_head_now === (int) $user->au_id;
    }

    public function canView($l, $user = null)
    {
        $user = $user ?: Auth::user();
        if ($this->letters->isHrExecutive($user) || $this->letters->canApprove($user) || $this->isDeptHead($l, $user)) {
            return true;
        }
        return (int) $l->hl_employee_id === (int) $user->au_id && in_array($l->hl_status, ['approved', 'issued'], true);
    }

    public function canEdit($l)
    {
        return $l->hl_status === 'draft' && $this->letters->isHrExecutive();
    }

    public function canReview($l)
    {
        return $this->awaiting($l) === 'review' && ($this->isDeptHead($l) || $this->letters->isHrAdmin());
    }

    public function canDecide($l)
    {
        $wait = $this->awaiting($l);
        return ($wait === 'approval' && $this->letters->canApprove()) || ($wait === 'review' && $this->letters->isHrAdmin());
    }

    public function canRevise($l)
    {
        return $l->hl_status === 'rejected' && $this->letters->isHrExecutive();
    }

    public function canCancel($l)
    {
        return $l->hl_status !== 'cancelled' && $this->letters->isHrAdmin();
    }

    // ------------------------------------------------------------------ making a letter

    /** A new draft: reference number, employee details and the template text copied in. Returns the letter id. */
    public function create($employeeId, $type, $template, Carbon $date, $signatoryId = null)
    {
        $employee = HrmLetterContent::employee($employeeId, $this->letters->company);
        if (!$employee) {
            throw new \RuntimeException('Employee not found.');
        }
        $snapshot = (array) $employee;
        $signatoryId = $signatoryId ?: optional($this->letters->defaultSignatory($employee->branch_id ?: $this->letters->branch))->hls_id;
        return DB::transaction(function () use ($employee, $snapshot, $type, $template, $date, $signatoryId) {
            $now = Carbon::now();
            for ($try = 0; $try < 5; $try++) {
                $reference = $this->letters->newReference($type->hlt_prefix, $now);      // the generation date
                try {
                    $id = DB::table('hrm_letters')->insertGetId([
                        'hl_company_id' => $this->letters->company, 'hl_branch_id' => (int) ($employee->branch_id ?: $this->letters->branch),
                        'hl_type_id' => $type->hlt_id, 'hl_template_id' => $template->hltp_id ?? null, 'hl_employee_id' => $employee->id,
                        'hl_reference' => $reference, 'hl_letter_date' => $date->toDateString(),
                        'hl_subject' => ($template->hltp_subject ?? null) ?: $type->hlt_subject, 'hl_body' => $template->hltp_body ?? '',
                        'hl_employee' => json_encode($snapshot), 'hl_fields' => json_encode(new \stdClass()), 'hl_signatory_id' => $signatoryId,
                        'hl_status' => 'draft', 'hl_created_by' => Auth::user()->au_id, 'hl_created_at' => $now,
                    ]);
                    HrmLetters::log($id, 'created', $type->hlt_name . ' for ' . $employee->name . ' (' . $reference . ')', null, 'draft');
                    return $id;
                } catch (\Illuminate\Database\QueryException $e) {
                    if (stripos($e->getMessage(), 'Duplicate') === false) {
                        throw $e;
                    }
                    // someone took the same number at the same moment: pick another
                }
            }
            throw new \RuntimeException('Could not get a free reference number, please try again.');
        });
    }

    // ------------------------------------------------------------------ showing on the letterhead

    /**
     * The letter as it prints: [pad, letter, missing, unknown]. An approved letter shows exactly what was frozen;
     * a draft is filled from its details now. $o overrides the details for the live preview while editing.
     */
    public function render($l, array $o = [], $mark = true)
    {
        if ($l->frozen && !$o) {
            $pad = (object) (json_decode((string) $l->hl_letterhead, true) ?: []);
            $sig = (object) (json_decode((string) $l->hl_signatory, true) ?: ['name' => '', 'designation' => '', 'signature' => null]);
            $letter = (object) ['reference' => $l->hl_reference, 'date' => Carbon::parse($l->hl_letter_date), 'subject' => $l->hl_subject, 'body' => $l->hl_body, 'signatory' => $sig];
            return [$pad, $letter, [], []];
        }
        $employee = clone $l->employee;
        foreach (array_keys(self::EDITABLE_EMPLOYEE) as $k) {
            if (array_key_exists($k, $o['employee'] ?? [])) {
                $employee->$k = $o['employee'][$k];
            }
        }
        $fields = array_merge($l->fields, $o['fields'] ?? []);
        $date = isset($o['date']) ? Carbon::parse($o['date']) : Carbon::parse($l->hl_letter_date);
        $pad = $this->letters->letterhead($l->hl_branch_id);
        $sig = $this->signer(array_key_exists('signatory_id', $o) ? $o['signatory_id'] : $l->hl_signatory_id, $l->hl_branch_id);
        $values = HrmLetterContent::values($employee, $pad, $sig, $fields, $l->hl_reference, $date);
        $bodySource = array_key_exists('body', $o) ? HrmLetterContent::clean($o['body']) : (string) $l->hl_body;
        $subjectSource = array_key_exists('subject', $o) ? (string) $o['subject'] : (string) $l->hl_subject;
        [$body, $missing] = HrmLetterContent::fill($bodySource, $values, $mark);
        $subject = HrmLetterContent::fillText($subjectSource, $values);
        $missing = array_values(array_unique(array_merge($missing, array_diff(HrmLetterContent::used($subjectSource), array_keys(array_filter($values, function ($v) { return trim($v) !== ''; }))))));
        if (!$sig->name) {
            $missing[] = 'authorized_person';
        }
        $unknown = HrmLetterContent::unknown($bodySource . ' ' . $subjectSource);
        $letter = (object) ['reference' => $l->hl_reference, 'date' => $date, 'subject' => $subject, 'body' => $body, 'signatory' => $sig];
        return [$pad, $letter, array_values(array_unique($missing)), $unknown];
    }

    public function signatory($id)
    {
        return $id ? DB::table('hrm_letter_signatories')->where('hls_company_id', $this->letters->company)->where('hls_id', (int) $id)->first() : null;
    }

    /**
     * Who signs the letter: the person chosen on it, else the default signatory of its branch or the company,
     * else (when no signatory is set up yet) the admin or approver who approves it. Empty only for someone who cannot approve.
     */
    public function signer($chosenId, $branchId)
    {
        $row = $this->signatory($chosenId) ?: $this->letters->defaultSignatory($branchId);
        if ($row) {
            return (object) ['id' => $row->hls_id, 'name' => $row->hls_name, 'designation' => $row->hls_designation, 'signature' => $row->hls_signature];
        }
        $user = Auth::user();
        if ($user && $this->letters->canApprove($user)) {
            $designation = optional(HrmLetterContent::employee($user->au_id, $this->letters->company))->designation;
            return (object) ['id' => null, 'name' => (string) $user->au_name, 'designation' => $designation ?: 'Authorized Signatory', 'signature' => null];
        }
        return (object) ['id' => null, 'name' => '', 'designation' => '', 'signature' => null];
    }

    /**
     * What still has to be filled before the letter can go further, as readable names. The authorized person never
     * holds a letter back: if none is set up, the admin or approver who approves it signs it.
     */
    public function problems($l)
    {
        [, , $missing, $unknown] = $this->render($l, [], false);
        $missing = array_values(array_diff($missing, ['authorized_person', 'authorized_designation']));
        $out = [];
        if ($missing) {
            $out[] = 'Fill in: ' . implode(', ', array_map([self::class, 'label'], $missing)) . '.';
        }
        if ($unknown) {
            $out[] = 'Unknown placeholders: {{' . implode('}}, {{', $unknown) . '}}.';
        }
        return $out;
    }

    // ------------------------------------------------------------------ printing (Phase 4)

    /** Approved and issued letters print clean; anything else only as a marked preview */
    public static function printable($l)
    {
        return in_array($l->hl_status, ['approved', 'issued'], true);
    }

    /** Word shown across a preview that may not be used as a real letter */
    public static function watermark($l)
    {
        return self::printable($l) ? null : strtoupper(self::statusLabel($l->hl_status));
    }

    /** HR (and approvers) issue letters; the employee may only read their own */
    public function canIssue($l)
    {
        return $this->letters->isHrExecutive() || $this->letters->canApprove();
    }

    /**
     * A print or PDF download: counted and kept in the history. The first one by HR turns an approved letter into "issued".
     * Returns the new status.
     */
    public function markOutput($l, $kind)
    {
        $col = $kind === 'downloaded' ? 'hl_download_count' : 'hl_print_count';
        return DB::transaction(function () use ($l, $kind, $col) {
            $from = DB::table('hrm_letters')->where('hl_id', $l->hl_id)->lockForUpdate()->value('hl_status');   // as it is now, not as it was opened
            $update = [$col => DB::raw($col . ' + 1')];
            $to = $from;
            if ($from === 'approved' && $this->canIssue($l)) {
                $to = 'issued';
                $update += ['hl_status' => 'issued', 'hl_issued_at' => Carbon::now()];
            }
            DB::table('hrm_letters')->where('hl_id', $l->hl_id)->update($update);
            $who = (int) $l->hl_employee_id === (int) Auth::user()->au_id && !$this->canIssue($l) ? ' by the employee' : '';
            HrmLetters::log($l->hl_id, $kind, ($kind === 'downloaded' ? 'PDF downloaded' : 'Printed') . $who . ($to !== $from ? '; the letter is now issued' : ''), $from, $to);
            return $to;
        });
    }

    /**
     * The letter text split as [text, closing paragraph]: a short last paragraph is kept on the same page as the
     * signature, so the signature never stands alone on a page. [text, ''] when the letter does not end in a paragraph.
     */
    public static function splitClosing($html)
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return [$html, ''];
        }
        $doc = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="x">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        $root = $doc->getElementById('x');
        if (!$root) {
            return [$html, ''];
        }
        $blank = function ($n) {
            $text = preg_replace('/[\s\x{00A0}]+/u', '', (string) $n->textContent);
            return $text === '' && ($n->nodeType !== XML_ELEMENT_NODE || ($n->getElementsByTagName('img')->length === 0 && $n->getElementsByTagName('table')->length === 0));
        };
        // empty lines the editor leaves at the end (<p><br></p>) are dropped, so the real closing line is found
        $last = null;
        while ($root->lastChild) {
            $n = $root->lastChild;
            if ($blank($n)) {
                $root->removeChild($n);
                continue;
            }
            $last = $n->nodeType === XML_ELEMENT_NODE ? $n : null;
            break;
        }
        $rebuild = function () use ($doc, $root) {
            $out = '';
            foreach ($root->childNodes as $c) {
                $out .= $doc->saveHTML($c);
            }
            return $out;
        };
        if (!$last || strtolower($last->nodeName) !== 'p' || mb_strlen(trim($last->textContent)) > 600) {
            return [$rebuild(), ''];
        }
        $closing = $doc->saveHTML($last);
        $root->removeChild($last);
        return [$rebuild(), $closing];
    }

    /**
     * True when the letter has Bangla text. The PDF maker (dompdf) cannot draw Bangla, so such a letter is saved as PDF
     * from the browser's print window instead, which draws it correctly.
     */
    public static function hasBangla($pad, $letter)
    {
        $text = implode(' ', [strip_tags((string) $letter->body), (string) $letter->subject, $letter->signatory->name ?? '', $letter->signatory->designation ?? '',
            $pad->company_name ?? '', $pad->tagline ?? '', $pad->address ?? '', $pad->footer_text ?? '']);
        return preg_match('/[\x{0980}-\x{09FF}]/u', $text) === 1;
    }

    /** Footer line of the letterhead (the saved footer text, else company and contact details) */
    public static function footerText($pad)
    {
        return ($pad->footer_text ?? null) ?: trim((($pad->company_name ?? null) ? $pad->company_name . ' · ' : '')
            . implode(' · ', array_filter([$pad->phone ?? null, $pad->email ?? null, $pad->website ?? null])), ' ·');
    }

    /**
     * Header and footer heights in mm for the PDF, measured with the PDF fonts, so a long company name, address
     * or footer that wraps never runs into the letter text. Returns [header, footer, page number top in pt].
     */
    public static function pdfBands($pad, $metrics)
    {
        $pt = 72 / 25.4;
        $px = 0.75;
        $width = (210 - 2 * (int) ($pad->margin_side ?? 18)) * $pt;
        $regular = $metrics->getFont('Helvetica', 'normal');
        $bold = $metrics->getFont('Helvetica', 'bold');
        $italic = $metrics->getFont('Helvetica', 'italic');
        $lines = function ($text, $font, $size, $box, $charSpace = 0.0) use ($metrics) {
            $text = trim(preg_replace('/\s+/u', ' ', (string) $text));
            if ($text === '') {
                return 0;
            }
            $count = 1;
            $used = 0;
            $space = $metrics->getTextWidth(' ', $font, $size, 0, $charSpace);
            foreach (explode(' ', $text) as $word) {
                $w = $metrics->getTextWidth($word, $font, $size, 0, $charSpace);
                if ($used > 0 && $used + $space + $w > $box) {
                    $count++;
                    $used = $w;
                } else {
                    $used += ($used > 0 ? $space : 0) + $w;
                }
                while ($used > $box) {          // one word longer than the line
                    $count++;
                    $used -= $box;
                }
            }
            return $count;
        };
        $org = $width * 0.55 - 2;
        $contact = implode('  |  ', array_filter([($pad->phone ?? null) ? 'Phone: ' . $pad->phone : null, ($pad->email ?? null) ? 'Email: ' . $pad->email : null, ($pad->website ?? null) ?: null]));
        $text = 0;
        if (!empty($pad->company_name)) {
            $text += $lines(mb_strtoupper($pad->company_name), $bold, 19, $org, 0.5 * $px) * 19 * 1.15;
        }
        if (!empty($pad->tagline)) {
            $text += 1 * $px + $lines($pad->tagline, $italic, 9.5, $org) * 9.5 * 1.55;
        }
        if (!empty($pad->address)) {
            $text += 2 * $px + $lines($pad->address, $regular, 8.5, $org) * 8.5 * 1.55;
        }
        if ($contact !== '') {
            $text += 2 * $px + $lines($contact, $regular, 8.5, $org) * 8.5 * 1.55;
        }
        $head = max(58 * $px, $text) + 11 * $px + 10;                 // logo or text, the rule, then a gap before the letter
        $footLines = max(1, $lines(self::footerText($pad), $regular, 8, $width * 0.72 - 4));
        $foot = 8 + 1 * $px + 4 * $px + $footLines * 8 * 1.55;        // a gap after the letter, the rule, the text
        $pageTop = (297 - (int) ($pad->margin_bottom ?? 12)) * $pt - $foot + 8 + 5 * $px + (8 * 1.55 - 8 * 1.16) / 2;
        return [round($head / $pt + 0.6, 1), round($foot / $pt + 0.6, 1), $pageTop];
    }

    /** Approval: the filled text, employee details, letterhead and signatory are written into the letter for good */
    public function freeze($l)
    {
        // someone may have rejected or cancelled it a moment ago (read again under a row lock; call inside a transaction)
        $now = DB::table('hrm_letters')->where('hl_id', $l->hl_id)->lockForUpdate()->value('hl_status');
        if ($now !== $l->hl_status) {
            throw new \RuntimeException('Someone else changed this letter a moment ago (it is now ' . strtolower(self::statusLabel($now)) . ').');
        }
        if ($problems = $this->problems($l)) {
            throw new \RuntimeException(implode(' ', $problems));
        }
        [$pad, $letter] = $this->render($l, [], false);
        DB::table('hrm_letters')->where('hl_id', $l->hl_id)->update([
            'hl_subject' => $letter->subject, 'hl_body' => $letter->body,
            'hl_letterhead' => json_encode($pad), 'hl_signatory' => json_encode($letter->signatory), 'hl_signatory_id' => $letter->signatory->id ?: $l->hl_signatory_id,
            'hl_status' => 'approved', 'hl_approver_id' => Auth::user()->au_id, 'hl_approved_at' => Carbon::now(),
            'hl_updated_by' => Auth::user()->au_id, 'hl_updated_at' => Carbon::now(),
        ]);
    }
}

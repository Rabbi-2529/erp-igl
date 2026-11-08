<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * HRM Letters: placeholders, employee details, filling a template and keeping letter HTML safe.
 * Placeholders like {{employee_name}} are replaced here, never by Blade, and every value is escaped.
 */
class HrmLetterContent
{
    /** Every placeholder, by group: key => what it prints */
    const PLACEHOLDERS = [
        'Employee' => [
            'employee_name'    => 'Employee name',
            'employee_id'      => 'Employee ID',
            'designation'      => 'Designation',
            'department'       => 'Department',
            'joining_date'     => 'Joining date',
            'salary'           => 'Monthly salary',
            'salary_in_words'  => 'Salary in words',
            'employee_mobile'  => 'Mobile',
            'employee_email'   => 'Email',
            'employee_address' => 'Address',
        ],
        'Company' => [
            'company_name'    => 'Company name',
            'branch_name'     => 'Branch name',
            'company_address' => 'Company address',
            'company_phone'   => 'Company phone',
            'company_email'   => 'Company email',
            'company_website' => 'Website',
        ],
        'Letter' => [
            'letter_reference'       => 'Reference number',
            'letter_date'            => 'Letter date',
            'authorized_person'      => 'Authorized person',
            'authorized_designation' => 'Authorized designation',
        ],
    ];

    /** Sample details for previews */
    const SAMPLE = [
        'employee_name' => 'Rahim Uddin', 'employee_id' => 'EMP-0042', 'designation' => 'Senior Executive', 'department' => 'Sales',
        'salary' => 35000, 'employee_mobile' => '01700000000', 'employee_email' => 'rahim@example.com', 'employee_address' => 'Mirpur, Dhaka',
        'effective_date' => '+7 days', 'new_designation' => 'Assistant Manager', 'new_department' => 'Corporate Sales', 'new_salary' => 42000,
        'increment_amount' => 7000, 'transfer_branch' => 'Chattogram branch', 'from_date' => '+3 days', 'to_date' => '+9 days',
        'last_working_day' => '+30 days', 'contract_end' => '+1 year', 'reply_by' => '+5 days', 'reason' => 'Details of the matter are written here.',
    ];

    /** Placeholders that hold dates or money (from the letter's extra fields) */
    const DATE_FIELDS = ['effective_date', 'from_date', 'to_date', 'last_working_day', 'contract_end', 'reply_by'];
    const MONEY_FIELDS = ['new_salary', 'increment_amount'];

    /** All placeholder keys, including the extra letter fields */
    public static function keys()
    {
        $keys = [];
        foreach (self::PLACEHOLDERS as $group) {
            $keys = array_merge($keys, array_keys($group));
        }
        return array_merge($keys, array_keys(HrmLetters::FIELDS));
    }

    /** Placeholders used in a text: ['employee_name', ...] */
    public static function used($text)
    {
        preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', (string) $text, $m);
        return array_values(array_unique($m[1]));
    }

    /** Placeholders in a text that do not exist (typing mistakes) */
    public static function unknown($text)
    {
        return array_values(array_diff(self::used($text), self::keys()));
    }

    // ------------------------------------------------------------------ formatting

    /** A date the ERP saved in any of its formats ("2018-05-10", "01-September-2020", "10/10/2026"), or null */
    public static function parseDate($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '' || $raw === '0000-00-00') {
            return null;
        }
        // day before month (Bangladesh): 10/11/2026 is 10 November
        foreach (['Y-m-d', 'Y-m-d H:i:s', 'd-F-Y', 'j-F-Y', 'd-M-Y', 'd F Y', 'j F Y', 'd M Y', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'd.m.Y', 'F d, Y', 'F j, Y', 'M d, Y'] as $f) {
            try {
                $d = Carbon::createFromFormat($f, $raw);
                if ($d && $d->format($f) === $raw) {
                    return $d->startOfDay();
                }
            } catch (\Throwable $e) {
                // try the next format
            }
        }
        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function date($value)
    {
        if ($value instanceof Carbon) {
            return $value->format('d F Y');
        }
        $d = self::parseDate($value);
        return $d ? $d->format('d F Y') : (string) $value;
    }

    public static function money($value)
    {
        if ($value === null || $value === '') {
            return '';
        }
        $v = (float) $value;
        return number_format($v, fmod($v, 1) ? 2 : 0);
    }

    /** Taka in words, Bangladesh style (crore, lakh): 125000 -> "One Lakh Twenty Five Thousand Taka Only" */
    public static function inWords($amount)
    {
        $amount = round((float) $amount, 2);
        $taka = (int) floor($amount);
        $paisa = (int) round(($amount - $taka) * 100);
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen',
            'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $two = function ($n) use ($ones, $tens) { return $n < 20 ? $ones[$n] : trim($tens[intdiv($n, 10)] . ' ' . $ones[$n % 10]); };
        $three = function ($n) use ($ones, $two) { return trim(($n >= 100 ? $ones[intdiv($n, 100)] . ' Hundred ' : '') . $two($n % 100)); };
        $words = function ($n) use ($two, $three, &$words) {
            if ($n === 0) { return 'Zero'; }
            $parts = [];
            if ($n >= 10000000) { $parts[] = $words(intdiv($n, 10000000)) . ' Crore'; $n %= 10000000; }
            if ($n >= 100000) { $parts[] = $two(intdiv($n, 100000)) . ' Lakh'; $n %= 100000; }
            if ($n >= 1000) { $parts[] = $two(intdiv($n, 1000)) . ' Thousand'; $n %= 1000; }
            if ($n > 0) { $parts[] = $three($n); }
            return implode(' ', $parts);
        };
        return $words($taka) . ' Taka' . ($paisa ? ' and ' . $words($paisa) . ' Paisa' : '') . ' Only';
    }

    // ------------------------------------------------------------------ employee

    /** An employee's details for a letter (designation from their last approved letter) */
    public static function employee($employeeId, $companyId)
    {
        $u = DB::table('admin_user')->where('au_id', (int) $employeeId)->where('au_company_id', (int) $companyId)->first();
        if (!$u) {
            return null;
        }
        $depIds = array_filter(array_map('intval', explode('-', (string) $u->au_department)));
        $departments = $depIds ? DB::table('departments')->whereIn('id', $depIds)->orderBy('name')->pluck('name')->implode(', ') : '';
        $designation = '';
        if (AccountsSettings::hasTable('hrm_letters')) {
            $last = DB::table('hrm_letters')->where('hl_company_id', (int) $companyId)->where('hl_employee_id', $u->au_id)
                ->whereIn('hl_status', ['approved', 'issued'])->orderByDesc('hl_letter_date')->orderByDesc('hl_id')->first(['hl_employee', 'hl_fields']);
            if ($last) {
                $fields = json_decode((string) $last->hl_fields, true) ?: [];
                $snap = json_decode((string) $last->hl_employee, true) ?: [];
                $designation = $fields['new_designation'] ?? ($snap['designation'] ?? '');
            }
        }
        $joined = self::parseDate($u->au_created_date);
        return (object) [
            'id'           => $u->au_id,
            'name'         => $u->au_name,
            'employee_id'  => $u->au_user_id,
            'designation'  => $designation,
            'department'   => $departments,
            'joining_date' => $joined ? $joined->toDateString() : $u->au_created_date,
            'salary'       => (float) $u->au_user_salary,
            'mobile'       => $u->au_mobile ?: $u->persional_mobile,
            'email'        => $u->au_email,
            'address'      => $u->au_address,
            'branch_id'    => $u->au_branch_id,
            'branch_name'  => $u->au_branch_name,
            'dep_head_id'  => $u->au_dep_head_id,
        ];
    }

    /** A made-up employee for previews */
    public static function sampleEmployee()
    {
        $s = self::SAMPLE;
        return (object) ['id' => 0, 'name' => $s['employee_name'], 'employee_id' => $s['employee_id'], 'designation' => $s['designation'],
            'department' => $s['department'], 'joining_date' => Carbon::today()->subYears(2)->toDateString(), 'salary' => $s['salary'],
            'mobile' => $s['employee_mobile'], 'email' => $s['employee_email'], 'address' => $s['employee_address'], 'branch_id' => null, 'branch_name' => null, 'dep_head_id' => null];
    }

    /** Sample values of the extra letter fields, for previews */
    public static function sampleFields()
    {
        $out = [];
        foreach (array_keys(HrmLetters::FIELDS) as $k) {
            $v = self::SAMPLE[$k] ?? '';
            $out[$k] = in_array($k, self::DATE_FIELDS, true) ? Carbon::today()->modify($v)->toDateString() : $v;
        }
        return $out;
    }

    // ------------------------------------------------------------------ filling

    /**
     * The value of every placeholder for one letter.
     * $employee from employee(), $pad letterhead, $signatory {name, designation}, $fields extra fields, $reference, $date (Carbon)
     */
    public static function values($employee, $pad, $signatory, array $fields, $reference, Carbon $date)
    {
        $v = [
            'employee_name'    => $employee->name,
            'employee_id'      => $employee->employee_id,
            'designation'      => $employee->designation,
            'department'       => $employee->department,
            'joining_date'     => $employee->joining_date ? self::date($employee->joining_date) : '',
            'salary'           => $employee->salary ? self::money($employee->salary) : '',
            'salary_in_words'  => $employee->salary ? self::inWords($employee->salary) : '',
            'employee_mobile'  => $employee->mobile,
            'employee_email'   => $employee->email,
            'employee_address' => $employee->address,
            'company_name'     => $pad->company_name,
            'branch_name'      => $pad->branch_name ?: $employee->branch_name,
            'company_address'  => $pad->address,
            'company_phone'    => $pad->phone,
            'company_email'    => $pad->email,
            'company_website'  => $pad->website,
            'letter_reference' => $reference,
            'letter_date'      => $date->format('d F Y'),
            'authorized_person'      => $signatory->name ?? '',
            'authorized_designation' => $signatory->designation ?? '',
        ];
        foreach (array_keys(HrmLetters::FIELDS) as $k) {
            $raw = $fields[$k] ?? '';
            if (in_array($k, self::DATE_FIELDS, true)) {
                $v[$k] = $raw !== '' ? self::date($raw) : '';
            } elseif (in_array($k, self::MONEY_FIELDS, true)) {
                $v[$k] = $raw !== '' ? self::money($raw) : '';
            } else {
                $v[$k] = (string) $raw;
            }
        }
        return array_map(function ($x) { return $x === null ? '' : (string) $x; }, $v);
    }

    /**
     * Fills the placeholders of letter HTML. Values are escaped; a line break in a value becomes <br>.
     * Empty or unknown placeholders stay as they are, marked when $mark is true (previews). Returns [html, missing keys].
     */
    public static function fill($html, array $values, $mark = false)
    {
        $missing = [];
        $out = preg_replace_callback('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', function ($m) use ($values, $mark, &$missing) {
            $key = $m[1];
            if (isset($values[$key]) && trim($values[$key]) !== '') {
                return nl2br(e($values[$key]), false);
            }
            $missing[] = $key;
            return $mark ? '<mark class="pad-missing">{{' . e($key) . '}}</mark>' : '{{' . e($key) . '}}';
        }, (string) $html);
        return [$out, array_values(array_unique($missing))];
    }

    /** Fills a plain-text line (the subject) */
    public static function fillText($text, array $values)
    {
        return preg_replace_callback('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', function ($m) use ($values) {
            return isset($values[$m[1]]) && trim($values[$m[1]]) !== '' ? $values[$m[1]] : $m[0];
        }, (string) $text);
    }

    // ------------------------------------------------------------------ safe HTML

    /** Letter HTML with only formatting kept: no scripts, links, images, frames or event handlers */
    public static function clean($html)
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        $allowed = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'td', 'th',
            'h3', 'h4', 'h5', 'span', 'div', 'blockquote', 'hr', 'sub', 'sup', 'mark'];
        $drop = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'link', 'meta', 'svg', 'math', 'img', 'video', 'audio', 'noscript', 'template'];
        $prev = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="lt-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        $root = $dom->getElementById('lt-root');
        if (!$root) {
            return e(strip_tags($html));
        }
        $walk = function (\DOMNode $node) use (&$walk, $allowed, $drop) {
            for ($child = $node->firstChild; $child; $child = $next) {
                $next = $child->nextSibling;
                if ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                    $node->removeChild($child);
                    continue;
                }
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                $tag = strtolower($child->nodeName);
                if (in_array($tag, $drop, true)) {
                    $node->removeChild($child);
                    continue;
                }
                $walk($child);
                if (!in_array($tag, $allowed, true)) {            // keep the text, lose the tag (e.g. <a>, <font>)
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->nodeName);
                    if (in_array($name, ['colspan', 'rowspan'], true) && ctype_digit($attr->nodeValue)) {
                        continue;
                    }
                    if ($name === 'style') {
                        $kept = [];
                        foreach (explode(';', $attr->nodeValue) as $rule) {
                            [$p, $val] = array_pad(array_map('trim', explode(':', $rule, 2)), 2, '');
                            $p = strtolower($p);
                            if (in_array($p, ['text-align', 'font-weight', 'font-style', 'text-decoration'], true) && preg_match('/^[a-z\- ]{1,30}$/i', $val)) {
                                $kept[] = $p . ': ' . $val;
                            } elseif (in_array($p, ['margin-left', 'padding-left'], true) && preg_match('/^\d{1,3}(px|em)$/', $val)) {
                                $kept[] = $p . ': ' . $val;
                            }
                        }
                        if ($kept) {
                            $child->setAttribute('style', implode('; ', $kept));
                            continue;
                        }
                    }
                    $child->removeAttribute($attr->nodeName);
                }
            }
        };
        $walk($root);
        $out = '';
        foreach ($root->childNodes as $c) {
            $out .= $dom->saveHTML($c);
        }
        return trim($out);
    }
}

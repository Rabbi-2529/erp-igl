<?php

namespace App\Services;

use App\Models\Admin_user;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who had an Employee ID (or office mobile) when.
 *
 * Each person has their own admin_user record (au_id), and attendance, salary, leave, letters ... are saved under
 * that au_id. The Employee ID (au_user_id) and the office mobile go with the seat: when someone leaves, HR records the
 * exit (hrm_employee_exits) and gives the same ID and mobile to the new employee, who gets a NEW record, so the old
 * person's history stays with the old person. The few places that find a person by Employee ID (the attendance
 * machine, fines, blood donation) ask holder() for whoever had the ID on that day.
 */
class EmployeeTenure
{
    const TYPES = [
        'resigned'     => 'Resigned',
        'terminated'   => 'Terminated',
        'contract_end' => 'Contract ended',
        'other'        => 'Other',
    ];

    /** name words many different people share: they do not show that two names are the same person */
    const COMMON_NAME_WORDS = ['md', 'mohammad', 'mohammed', 'muhammad', 'mohamad', 'mohd', 'mst', 'most', 'mosammat', 'mosammad',
        'mrs', 'mr', 'miss', 'ms', 'dr', 'engr', 'sk', 'sheikh', 'shaikh', 'syed', 'sayed', 'abu', 'al', 'el', 'bin', 'binte',
        'begum', 'khatun', 'akter', 'akther', 'akhter', 'uddin', 'islam', 'ahmed', 'ahmad', 'hossain', 'hussain', 'hasan',
        'hassan', 'rahman', 'khan', 'alam', 'ali', 'chowdhury', 'sarkar', 'mia', 'miah', 'das', 'roy', 'saha'];

    private static $ready;
    private static $candidates = [];
    private static $exitCache = [];

    /** false until database/hrm_employee_exits.sql has been run on this database */
    public static function ready(): bool
    {
        if (self::$ready === null) {
            try {
                self::$ready = Schema::hasTable('hrm_employee_exits');
            } catch (\Throwable $e) {
                self::$ready = false;
            }
        }
        return self::$ready;
    }

    public static function forget(): void
    {
        self::$ready = null;
        self::$candidates = [];
        self::$exitCache = [];
    }

    // ------------------------------------------------------------------ dates

    /** reads the dates found in this ERP (2026-01-18, 18/01/2026, 01-June-2024 ...), day before month */
    public static function parseDate($raw): ?Carbon
    {
        if ($raw instanceof \DateTimeInterface) {
            return Carbon::instance($raw)->startOfDay();
        }
        $raw = trim((string) $raw);
        if ($raw === '' || strpos($raw, '0000-00-00') === 0) {
            return null;
        }
        foreach (['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'd.m.Y', 'd-F-Y', 'j-F-Y', 'd-M-Y', 'd F Y', 'j F Y', 'd M Y'] as $f) {
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

    public static function joinDate($user): ?Carbon
    {
        return self::parseDate($user->au_created_date ?? null);
    }

    private static function day($date): Carbon
    {
        return self::parseDate($date) ?: Carbon::today();
    }

    // ------------------------------------------------------------------ exits

    /** the open (not undone) exit of each of these employees, keyed by au_id */
    public static function exitsFor($userIds): Collection
    {
        $ids = collect($userIds)->map(function ($id) { return (int) $id; })->filter()->unique()->values();
        if ($ids->isEmpty() || !self::ready()) {
            return collect();
        }
        $missing = $ids->reject(function ($id) { return array_key_exists($id, self::$exitCache); });
        foreach ($missing->chunk(500) as $chunk) {
            foreach ($chunk as $id) {
                self::$exitCache[$id] = null;
            }
            $rows = DB::table('hrm_employee_exits')->whereIn('hee_user_id', $chunk->all())->whereNull('hee_cancelled_at')->orderBy('hee_id')->get();
            foreach ($rows as $r) {
                self::$exitCache[(int) $r->hee_user_id] = $r;    // the latest one wins
            }
        }
        return $ids->mapWithKeys(function ($id) { return [$id => self::$exitCache[$id]]; })->filter();
    }

    public static function openExit($userId)
    {
        return self::exitsFor([$userId])->get((int) $userId);
    }

    /** HR marks the employee as left: a new exit row, and their login is turned off (as Deactivate does) */
    public static function recordExit(int $userId, array $data, int $by): int
    {
        $id = DB::transaction(function () use ($userId, $data, $by) {
            $u = DB::table('admin_user')->where('au_id', $userId)->lockForUpdate()->first();
            if (!$u) {
                throw new \RuntimeException('Employee not found.');
            }
            if (DB::table('hrm_employee_exits')->where('hee_user_id', $userId)->whereNull('hee_cancelled_at')->lockForUpdate()->exists()) {
                throw new \RuntimeException($u->au_name . ' is already marked as left.');
            }
            $id = DB::table('hrm_employee_exits')->insertGetId([
                'hee_company_id'    => (int) $u->au_company_id,
                'hee_branch_id'     => (int) $u->au_branch_id,
                'hee_user_id'       => (int) $u->au_id,
                'hee_employee_code' => $u->au_user_id !== null ? mb_substr(trim((string) $u->au_user_id), 0, 30) : null,
                'hee_mobile'        => $u->au_mobile !== null ? mb_substr(trim((string) $u->au_mobile), 0, 20) : null,
                'hee_name'          => $u->au_name !== null ? mb_substr((string) $u->au_name, 0, 100) : null,
                'hee_last_day'      => $data['last_day'],
                'hee_type'          => $data['type'],
                'hee_reason'        => $data['reason'] ?? null,
                'hee_status_before' => $u->au_status,
                'hee_created_by'    => $by,
                'hee_created_at'    => Carbon::now(),
            ]);
            DB::table('admin_user')->where('au_id', $userId)->update(['au_status' => 0]);
            return $id;
        });
        unset(self::$exitCache[$userId]);
        self::$candidates = [];
        return $id;
    }

    /**
     * Undo an exit (it was a mistake, or the person came back): the exit row is kept and marked as undone,
     * and the login is turned on again. Refused while someone else who is working uses their mobile or ID.
     */
    public static function cancelExit(int $userId, int $by, string $note): bool
    {
        $done = DB::transaction(function () use ($userId, $by, $note) {
            $u = DB::table('admin_user')->where('au_id', $userId)->lockForUpdate()->first();
            $exit = DB::table('hrm_employee_exits')->where('hee_user_id', $userId)->whereNull('hee_cancelled_at')->lockForUpdate()->get();
            if (!$u || $exit->isEmpty()) {
                return false;
            }
            if ($why = self::activationConflict($u)) {
                throw new \RuntimeException($why);
            }
            DB::table('hrm_employee_exits')->whereIn('hee_id', $exit->pluck('hee_id')->all())->update([
                'hee_cancelled_by' => $by,
                'hee_cancelled_at' => Carbon::now(),
                'hee_cancel_note'  => mb_substr($note, 0, 255),
            ]);
            DB::table('admin_user')->where('au_id', $userId)->update(['au_status' => 1]);
            return true;
        });
        unset(self::$exitCache[$userId]);
        self::$candidates = [];
        return $done;
    }

    // ------------------------------------------------------------------ who has the ID

    /** every record with this Employee ID (in the company, when given), oldest record first */
    public static function candidates($companyId, $code): Collection
    {
        $code = trim((string) $code);
        if ($code === '') {
            return collect();
        }
        $key = (int) $companyId . '|' . $code;
        if (!isset(self::$candidates[$key])) {
            self::$candidates[$key] = Admin_user::where('au_user_id', $code)
                ->when($companyId, function ($q) use ($companyId) { $q->where('au_company_id', $companyId); })
                ->orderBy('au_id')->get();
        }
        return self::$candidates[$key];
    }

    /**
     * The person who had this Employee ID on that day (today when no day is given):
     * not someone who had already left, not someone who joined later; then the one who is on,
     * then the one who joined last, then the oldest record (what the attendance webhook did before).
     */
    public static function holder($companyId, $code, $date = null): ?Admin_user
    {
        $all = self::candidates($companyId, $code);
        if ($all->count() <= 1) {
            return $all->first();
        }
        $day = self::day($date);
        $exits = self::exitsFor($all->pluck('au_id'));

        $pool = $all->reject(function ($u) use ($exits, $day) {
            $e = $exits->get((int) $u->au_id);
            return $e && Carbon::parse($e->hee_last_day)->startOfDay()->lt($day);
        });
        if ($pool->isEmpty()) {
            $pool = $all;
        }
        $joined = $pool->reject(function ($u) use ($day) {
            $j = self::joinDate($u);
            return $j && $j->gt($day);
        });
        if ($joined->isNotEmpty()) {
            $pool = $joined;
        }
        return $pool->sort(function ($a, $b) {
            $on = ((int) $b->au_status === 1) <=> ((int) $a->au_status === 1);
            if ($on !== 0) {
                return $on;
            }
            $ja = self::joinDate($a);
            $jb = self::joinDate($b);
            if ($ja && $jb && $ja->timestamp !== $jb->timestamp) {
                return $jb->timestamp <=> $ja->timestamp;
            }
            if (($ja === null) !== ($jb === null)) {
                return $ja ? -1 : 1;
            }
            return (int) $a->au_id <=> (int) $b->au_id;
        })->first();
    }

    /** did this employee have the Employee ID on that day? (always true when nobody else ever had it) */
    public static function heldBy($user, $code, $date = null): bool
    {
        $h = self::holder($user->au_company_id ?? null, $code, $date);
        return !$h || (int) $h->au_id === (int) $user->au_id;
    }

    /** another employee in the company who is working and uses this value (office mobile or Employee ID) */
    public static function activeHolder($companyId, string $field, $value, $exceptId = null): ?Admin_user
    {
        $value = trim((string) $value);
        if ($value === '' || !in_array($field, ['au_mobile', 'au_user_id'], true)) {
            return null;
        }
        $rows = Admin_user::where('au_company_id', $companyId)->where($field, $value)->where('au_status', 1)
            ->when($exceptId, function ($q) use ($exceptId) { $q->where('au_id', '!=', $exceptId); })
            ->orderBy('au_id')->get();
        $exits = self::exitsFor($rows->pluck('au_id'));
        return $rows->first(function ($u) use ($exits) { return !$exits->has((int) $u->au_id); });
    }

    /**
     * Why this employee cannot be turned on: someone else who is working now uses their office mobile
     * (two people could not log in with one number) or their Employee ID (the attendance machine would mix them).
     */
    public static function activationConflict($user, $mobile = null, $code = null): ?string
    {
        $mobile = $mobile ?? $user->au_mobile;
        $code = $code ?? $user->au_user_id;
        if ($other = self::activeHolder($user->au_company_id, 'au_mobile', $mobile, $user->au_id)) {
            return 'Mobile ' . trim($mobile) . ' is now used by ' . $other->au_name . ', who is working. Give ' . $user->au_name
                . ' another mobile number first (Edit Employee), or exit ' . $other->au_name . '.';
        }
        if ($other = self::activeHolder($user->au_company_id, 'au_user_id', $code, $user->au_id)) {
            return 'Employee ID ' . trim($code) . ' is now used by ' . $other->au_name . ', who is working. Give ' . $user->au_name
                . ' another Employee ID first (Edit Employee), or exit ' . $other->au_name . '.';
        }
        return null;
    }

    /**
     * Add Employee: can this office mobile ($field 'mobile') or Employee ID ($field 'code') go to a new employee?
     * state: free | reuse (it was someone's who left: fine) | inactive (someone turned off but not marked as left has it:
     * allowed, with a warning) | taken (someone who is working has it: not allowed)
     */
    public static function check($companyId, string $field, $value): array
    {
        $value = trim((string) $value);
        $free = ['state' => 'free', 'text' => ''];
        if ($value === '') {
            return $free;
        }
        $columns = $field === 'mobile' ? ['au_mobile', 'persional_mobile', 'f_mobile', 'm_mobile'] : ['au_user_id'];
        $rows = Admin_user::where('au_company_id', $companyId)
            ->where(function ($w) use ($columns, $value) {
                foreach ($columns as $c) {
                    $w->orWhere($c, $value);
                }
            })
            ->orderByDesc('au_id')->get(array_merge(['au_id', 'au_name', 'au_status', 'au_created_date'], $columns));
        if ($rows->isEmpty()) {
            return $free;
        }
        $what = $field === 'mobile' ? 'mobile number' : 'Employee ID';
        $exits = self::exitsFor($rows->pluck('au_id'));
        $isOwn = function ($u) use ($field, $value) {
            return trim((string) ($field === 'mobile' ? $u->au_mobile : $u->au_user_id)) === $value;
        };

        $working = $rows->first(function ($u) use ($exits) { return (int) $u->au_status === 1 && !$exits->has((int) $u->au_id); });
        if ($working) {
            $how = $isOwn($working) ? '' : ' (as a personal or family number)';
            return ['state' => 'taken', 'user_id' => (int) $working->au_id, 'text' => 'This ' . $what . ' belongs to ' . $working->au_name . $how
                . ', who is still working. If they left, mark them as left first: Manage Employee > Action > Exit employee.'];
        }
        $own = $rows->filter($isOwn);
        if ($off = $own->first(function ($u) use ($exits) { return !$exits->has((int) $u->au_id); })) {
            return ['state' => 'inactive', 'user_id' => (int) $off->au_id, 'text' => 'Used before by ' . $off->au_name
                . ' (turned off, not marked as left). You can save: the new employee gets a new record and ' . $off->au_name
                . '\'s history stays with them. Tip: use Exit employee on ' . $off->au_name . ' to record when and why they left.'];
        }
        if ($left = $own->first()) {
            $e = $exits->get((int) $left->au_id);
            return ['state' => 'reuse', 'user_id' => (int) $left->au_id, 'text' => 'Used before by ' . $left->au_name . ', who left on '
                . Carbon::parse($e->hee_last_day)->format('d M Y') . '. The new employee gets a new record; ' . $left->au_name . '\'s history stays with them.'];
        }
        return $free;     // only a personal or family number of someone who is not working any more
    }

    // ------------------------------------------------------------------ the seat's history

    /** everyone in the company who had this employee's Employee ID or office mobile, in the order they joined */
    public static function seatHistory($user): Collection
    {
        $code = trim((string) $user->au_user_id);
        $mobile = trim((string) $user->au_mobile);
        if ($code === '' && $mobile === '') {
            return collect();
        }
        $byExit = collect();
        if (self::ready() && $code !== '') {
            $byExit = DB::table('hrm_employee_exits')->where('hee_company_id', $user->au_company_id)
                ->where('hee_employee_code', $code)->pluck('hee_user_id');
        }
        $people = Admin_user::where('au_company_id', $user->au_company_id)
            ->where(function ($w) use ($code, $mobile, $byExit) {
                if ($code !== '') {
                    $w->orWhere('au_user_id', $code);
                }
                if ($mobile !== '') {
                    $w->orWhere('au_mobile', $mobile);
                }
                if ($byExit->isNotEmpty()) {
                    $w->orWhereIn('au_id', $byExit->all());
                }
            })
            ->get(['au_id', 'au_name', 'au_user_id', 'au_mobile', 'au_status', 'au_created_date']);
        $exits = self::exitsFor($people->pluck('au_id'));
        return $people->map(function ($p) use ($exits) {
            $p->joined = self::joinDate($p);
            $p->exit = $exits->get((int) $p->au_id);
            return $p;
        })->sort(function ($a, $b) {
            if ($a->joined && $b->joined && $a->joined->timestamp !== $b->joined->timestamp) {
                return $a->joined->timestamp <=> $b->joined->timestamp;
            }
            return (int) $a->au_id <=> (int) $b->au_id;
        })->values();
    }

    // ------------------------------------------------------------------ editing an employee

    /**
     * Does the new name look like a different person (not a spelling fix)? True when the two names share
     * no word, apart from words many people share (Md, Ahmed, Islam ...). A one-letter slip still counts as the same word.
     */
    public static function looksLikeAnotherPerson($old, $new): bool
    {
        $norm = function ($s) {
            return trim(preg_replace('/\s+/u', ' ', mb_strtolower((string) $s)));
        };
        if ($norm($old) === '' || $norm($new) === '' || $norm($old) === $norm($new)) {
            return false;
        }
        $words = function ($s) {
            $w = preg_split('/[^\p{L}]+/u', mb_strtolower((string) $s), -1, PREG_SPLIT_NO_EMPTY);
            return array_values(array_unique(array_filter($w, function ($x) {
                return mb_strlen($x) >= 2 && !in_array($x, self::COMMON_NAME_WORDS, true);
            })));
        };
        $a = $words($old);
        $b = $words($new);
        if (!$a || !$b) {
            return false;
        }
        foreach ($a as $x) {
            foreach ($b as $y) {
                if ($x === $y || (min(strlen($x), strlen($y)) >= 4 && levenshtein($x, $y) <= 1)) {
                    return false;
                }
            }
        }
        return true;
    }
}

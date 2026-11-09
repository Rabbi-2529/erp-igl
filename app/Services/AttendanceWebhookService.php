<?php

namespace App\Services;

use App\Models\Admin_user;
use App\Models\EmployeeAttendance;
use App\Models\PayrollShiftManagement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Applies attendance punches pushed by the external device panel onto
 * employee_attendances.
 *
 * Employees are addressed by Admin_user::au_user_id (the payroll / device id),
 * which is translated to the internal au_id that employee_attendances.user_id
 * actually stores.
 *
 * The sync is idempotent: the earliest check-in and the latest check-out of a
 * work date win, so the panel may safely resend the same punches.
 */
class AttendanceWebhookService
{
    /**
     * employee_attendances.check_out is NOT NULL with no default, and existing
     * rows created by the browser flow carry this placeholder until checkout.
     */
    public const ZERO_DATETIME = '0000-00-00 00:00:00';

    /** Accepted aliases for every field, so the panel can post its own naming. */
    private const USER_KEYS = ['au_user_id', 'user_id', 'employee_id', 'emp_id'];
    private const DATE_KEYS = ['work_date', 'date', 'attendance_date'];
    private const IN_KEYS   = ['check_in', 'checkin_timestamp', 'checkin_time', 'check_in_time', 'in_time', 'punch_in'];
    private const OUT_KEYS  = ['check_out', 'checkout_timestamp', 'checkout_time', 'check_out_time', 'out_time', 'punch_out'];
    private const IP_KEYS   = ['device_ip', 'user_ip', 'ip', 'ip_address'];

    /** @var array<string, array> resolved shift settings, keyed by au_id */
    private array $shiftCache = [];

    /**
     * Apply one punch event.
     *
     * @return array{au_user_id: string, result: string, message: string|null, attendance_id: int|null, check_in: string|null, check_out: string|null}
     */
    public function syncEvent(array $raw): array
    {
        $event = $this->normalize($raw);

        if ($event['au_user_id'] === '') {
            return $this->skip('', 'au_user_id is missing or empty.');
        }

        if (! $event['check_in'] && ! $event['check_out']) {
            return $this->skip($event['au_user_id'], 'Neither a check-in nor a check-out time was supplied.');
        }

        $punchDay = $event['work_date']
            ?: ($event['check_in'] ? $event['check_in']->toDateString() : ($event['check_out'] ? $event['check_out']->toDateString() : null));
        $user = $this->resolveUser($event['au_user_id'], $punchDay);

        if (! $user) {
            return $this->skip($event['au_user_id'], 'No employee found with this au_user_id.');
        }

        return DB::transaction(fn () => $this->apply($user, $event));
    }

    /**
     * @return array{au_user_id: string, result: string, message: string|null, attendance_id: int|null, check_in: string|null, check_out: string|null}
     */
    private function apply(Admin_user $user, array $event): array
    {
        $shift  = $this->shiftFor($user);
        $ip     = $event['device_ip'] ?: (string) config('attendance_webhook.device_ip');
        $status = (array) config('attendance_webhook.status');

        $row     = null;
        $created = false;
        $changed = false;

        // ---------------------------------------------------------------- check-in
        if ($event['check_in']) {
            $checkIn = $event['check_in'];
            $row     = $this->findRow($user, $checkIn->toDateString());

            // Both punches are already stored for this date: leave the row
            // untouched, whatever the panel re-sends.
            if ($row && $this->isComplete($row)) {
                return $this->alreadyComplete($event['au_user_id'], $row);
            }

            if (! $row) {
                $row = new EmployeeAttendance();
                $row->company_id = $user->au_company_id;
                $row->branch_id  = $user->au_branch_id;
                $row->user_id    = $user->au_id;
                $row->check_in   = $checkIn->format('Y-m-d H:i:s');
                $row->check_out  = self::ZERO_DATETIME;
                $row->in_click   = 1;
                $row->out_click  = 0;
                $row->user_ip    = $ip;
                $row->company_ip = $user->au_company_ip;
                $row->status     = $this->isLate($checkIn, $shift) ? $status['late'] : $status['on_time'];
                $row->save();

                $created = true;
                $changed = true;
            } else {
                // Keep the earliest punch of the day as the check-in.
                if ($checkIn->lt(Carbon::parse($row->check_in))) {
                    $row->check_in = $checkIn->format('Y-m-d H:i:s');

                    // Only the check-in half of the status is re-derived here; a
                    // completed shift keeps the status set at check-out below.
                    if (! $this->hasCheckOut($row)) {
                        $row->status = $this->isLate($checkIn, $shift) ? $status['late'] : $status['on_time'];
                    }

                    $changed = true;
                }

                if ((int) $row->in_click !== 1) {
                    $row->in_click = 1;
                    $changed = true;
                }

                if (blank($row->user_ip)) {
                    $row->user_ip = $ip;
                    $changed = true;
                }

                if ($changed) {
                    $row->save();
                }
            }
        }

        // --------------------------------------------------------------- check-out
        if ($event['check_out']) {
            $checkOut = $event['check_out'];
            $row = $row ?: $this->findRowForCheckOut($user, $event, $checkOut);

            if (! $row) {
                return $this->skip(
                    $event['au_user_id'],
                    'No check-in record found for this check-out ('.$checkOut->format('Y-m-d H:i:s').').'
                );
            }

            if ($this->isComplete($row)) {
                return $this->alreadyComplete($event['au_user_id'], $row);
            }

            $checkIn  = Carbon::parse($row->check_in);
            $existing = $this->hasCheckOut($row) ? Carbon::parse($row->check_out) : null;

            if ($checkOut->lte($checkIn)) {
                return $this->skip(
                    $event['au_user_id'],
                    'Check-out is not after the stored check-in; ignored.'
                );
            }

            // Keep the latest punch of the day as the check-out.
            if (! $existing || $checkOut->gt($existing)) {
                $row->check_out        = $checkOut->format('Y-m-d H:i:s');
                $row->out_click        = 2;
                $row->user_ip_checkout = $ip;

                $this->applyWorkedTotals($row, $checkIn, $checkOut, $shift, $status);

                $row->save();
                $changed = true;
            }
        }

        return [
            'au_user_id'    => $event['au_user_id'],
            'result'        => $created ? 'created' : ($changed ? 'updated' : 'unchanged'),
            'message'       => null,
            'attendance_id' => (int) $row->id,
            'check_in'      => (string) $row->check_in,
            'check_out'     => $this->hasCheckOut($row) ? (string) $row->check_out : null,
        ];
    }

    /**
     * Fill total / incomplete / overtime columns and the final status, mirroring
     * AttendanceController::attendanceCheckOut.
     */
    private function applyWorkedTotals(
        EmployeeAttendance $row,
        Carbon $checkIn,
        Carbon $checkOut,
        array $shift,
        array $status
    ): void {
        $workedSeconds   = $checkIn->diffInSeconds($checkOut);
        $requiredSeconds = $shift['required_minutes'] * 60;

        $row->total_hour = round($workedSeconds / 3600, 3);

        // Reset both groups so a re-sync never leaves stale figures behind.
        $row->incomplete_hour   = 0;
        $row->incomplete_minute = 0;
        $row->incomplete_sec    = 0;
        $row->over_hour         = 0;
        $row->over_minute       = 0;
        $row->over_sec          = 0;

        if ($workedSeconds < $requiredSeconds) {
            $diff = $requiredSeconds - $workedSeconds;
            $row->incomplete_hour   = intdiv($diff, 3600);
            $row->incomplete_minute = intdiv($diff % 3600, 60);
            $row->incomplete_sec    = $diff % 60;
            $row->status            = $status['incomplete'];
        } elseif ($workedSeconds > $requiredSeconds) {
            $diff = $workedSeconds - $requiredSeconds;
            $row->over_hour   = intdiv($diff, 3600);
            $row->over_minute = intdiv($diff % 3600, 60);
            $row->over_sec    = $diff % 60;
            $row->status      = $status['overtime'];
        } else {
            $row->status = $status['perfect'];
        }
    }

    /**
     * The day's attendance row, identified the same way the rest of the app
     * does: company + internal user id + the date part of check_in.
     */
    private function findRow(Admin_user $user, string $date): ?EmployeeAttendance
    {
        return EmployeeAttendance::where('company_id', $user->au_company_id)
            ->where('user_id', $user->au_id)
            ->whereDate('check_in', $date)
            ->orderBy('check_in')
            ->first();
    }

    /**
     * For a check-out-only event, prefer the explicit work_date, then the
     * check-out's own date, then the previous day for shifts that end after
     * midnight.
     */
    private function findRowForCheckOut(Admin_user $user, array $event, Carbon $checkOut): ?EmployeeAttendance
    {
        $candidates = array_filter([$event['work_date'], $checkOut->toDateString()]);

        foreach (array_unique($candidates) as $date) {
            if ($row = $this->findRow($user, $date)) {
                return $row;
            }
        }

        $previous = $this->findRow($user, $checkOut->copy()->subDay()->toDateString());

        return $previous && ! $this->hasCheckOut($previous) ? $previous : null;
    }

    private function hasCheckIn(EmployeeAttendance $row): bool
    {
        return $this->isRealDateTime($row->check_in);
    }

    private function hasCheckOut(EmployeeAttendance $row): bool
    {
        return $this->isRealDateTime($row->check_out);
    }

    private function isRealDateTime($value): bool
    {
        $value = (string) $value;

        return $value !== '' && ! str_starts_with($value, '0000-00-00');
    }

    /**
     * A finished day: both punches stored. Such a row is never rewritten by the
     * webhook while attendance_webhook.lock_completed_records is on, so a panel
     * that keeps re-sending the same day cannot disturb it.
     */
    private function isComplete(EmployeeAttendance $row): bool
    {
        return (bool) config('attendance_webhook.lock_completed_records', true)
            && $this->hasCheckIn($row)
            && $this->hasCheckOut($row);
    }

    private function alreadyComplete(string $auUserId, EmployeeAttendance $row): array
    {
        return [
            'au_user_id'    => $auUserId,
            'result'        => 'unchanged',
            'message'       => 'Check-in and check-out are already saved for this date; nothing was written.',
            'attendance_id' => (int) $row->id,
            'check_in'      => (string) $row->check_in,
            'check_out'     => (string) $row->check_out,
        ];
    }

    private function isLate(Carbon $checkIn, array $shift): bool
    {
        $deadline = Carbon::parse($checkIn->toDateString().' '.$shift['start'])
            ->addMinutes($shift['late_grace']);

        return $checkIn->gt($deadline);
    }

    /**
     * Shift start/end, required minutes and late grace for an employee.
     *
     * Resolution order: the employee's own shift, then their branch's shift,
     * then the shift string stored on the employee row, then config defaults.
     *
     * @return array{start: string, end: string, required_minutes: int, late_grace: int}
     */
    private function shiftFor(Admin_user $user): array
    {
        $cacheKey = (string) $user->au_id;

        if (isset($this->shiftCache[$cacheKey])) {
            return $this->shiftCache[$cacheKey];
        }

        $shift = $user->au_shifts
            ? PayrollShiftManagement::find($user->au_shifts)
            : null;

        if (! $shift) {
            $shift = PayrollShiftManagement::where('company_id', $user->au_company_id)
                ->where('branch_id', $user->au_branch_id)
                ->first();
        }

        [$start, $end] = $this->splitShiftWindow(
            $shift->working_hour ?? $user->au_work_shift ?? null
        );

        $required = (int) $user->au_working_hour;

        if ($required <= 0) {
            $required = (int) round((float) ($shift->shift_hour ?? 0));
        }

        if ($required <= 0) {
            $required = Carbon::parse('2000-01-01 '.$start)
                ->diffInMinutes(Carbon::parse('2000-01-01 '.$end));
        }

        if ($required <= 0) {
            $required = (int) config('attendance_webhook.default_working_minutes');
        }

        return $this->shiftCache[$cacheKey] = [
            'start'            => $start,
            'end'              => $end,
            'required_minutes' => $required,
            'late_grace'       => (int) ($shift->late_entry ?? 0),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitShiftWindow(?string $window): array
    {
        $parts = array_map('trim', explode(',', (string) $window));
        $start = $parts[0] ?? '';
        $end   = $parts[1] ?? '';

        if (! str_contains($start, ':') || ! str_contains($end, ':')) {
            $parts = array_map('trim', explode(',', (string) config('attendance_webhook.default_shift')));
            $start = $parts[0] ?? '09:00:00';
            $end   = $parts[1] ?? '18:00:00';
        }

        return [$start, $end];
    }

    /**
     * au_user_id is a plain varchar with no unique index: after someone leaves, HR may give the same Employee ID
     * to a new employee (a new record). Use whoever had the ID on the punch day (EmployeeTenure::holder: not
     * someone who had left, not someone who joined later, then active first, then lowest au_id) and flag duplicates.
     */
    private function resolveUser(string $auUserId, ?string $day = null): ?Admin_user
    {
        $matches = EmployeeTenure::candidates(null, $auUserId);
        $user = EmployeeTenure::holder(null, $auUserId, $day);

        if ($matches->count() > 1) {
            Log::channel('attendance_webhook')->warning('Ambiguous au_user_id in admin_user', [
                'au_user_id' => $auUserId,
                'au_ids'     => $matches->pluck('au_id')->all(),
                'used_au_id' => $user ? $user->au_id : null,
                'day'        => $day,
            ]);
        }

        return $user;
    }

    /**
     * Flatten the panel's payload into a single shape.
     *
     * @return array{au_user_id: string, work_date: string|null, check_in: Carbon|null, check_out: Carbon|null, device_ip: string}
     */
    private function normalize(array $raw): array
    {
        $workDate = $this->firstValue($raw, self::DATE_KEYS);

        if ($workDate) {
            try {
                $workDate = Carbon::parse($workDate)->toDateString();
            } catch (\Throwable $e) {
                $workDate = null;
            }
        }

        return [
            'au_user_id' => trim((string) $this->firstValue($raw, self::USER_KEYS)),
            'work_date'  => $workDate,
            'check_in'   => $this->parseMoment($this->firstValue($raw, self::IN_KEYS), $workDate),
            'check_out'  => $this->parseMoment($this->firstValue($raw, self::OUT_KEYS), $workDate),
            'device_ip'  => trim((string) $this->firstValue($raw, self::IP_KEYS)),
        ];
    }

    private function firstValue(array $raw, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $raw) && $raw[$key] !== null && $raw[$key] !== '') {
                return is_scalar($raw[$key]) ? (string) $raw[$key] : null;
            }
        }

        return null;
    }

    /**
     * Accepts a full datetime ("2026-10-01 09:02:13") or a bare time
     * ("09:02:13"), the latter combined with the event's work_date.
     */
    private function parseMoment(?string $value, ?string $workDate): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '' || strtolower($value) === 'null' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            if (! $workDate) {
                return null;
            }

            $value = $workDate.' '.$value;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function skip(string $auUserId, string $message): array
    {
        return [
            'au_user_id'    => $auUserId,
            'result'        => 'skipped',
            'message'       => $message,
            'attendance_id' => null,
            'check_in'      => null,
            'check_out'     => null,
        ];
    }
}

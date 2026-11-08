<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts Phase 3b: company settings (VAT, voucher approval), the closed-year lock and the audit trail.
 * Every method works without the Phase 3b tables too (then: VAT off, no approval, no lock, nothing logged),
 * so the pages that use them never break before database/accounts_phase3b.sql is run.
 */
class AccountsSettings
{
    const DEFAULTS = [
        'vat_on'          => false,   // VAT registered: work out VAT on sales and purchases
        'vat_bin'         => '',      // Business Identification Number
        'vat_rate'        => 15.0,    // VAT on sales, % (prices include VAT)
        'vat_rebate_rate' => 15.0,    // VAT rebate on purchases, % (0 = no rebate)
        'vat_from'        => null,    // VAT counts on invoices from this date (yyyy-mm-dd)
        'approval_on'     => false,   // journal vouchers wait for an admin's approval
    ];

    private static $cache = [];
    private static $tables = [];

    public static function hasTable($table)
    {
        if (!array_key_exists($table, self::$tables)) {
            try {
                self::$tables[$table] = Schema::hasTable($table);
            } catch (\Throwable $e) {
                self::$tables[$table] = false;
            }
        }
        return self::$tables[$table];
    }

    // ------------------------------------------------------------------ settings

    public static function get($companyId)
    {
        $companyId = (int) $companyId;
        if (!isset(self::$cache[$companyId])) {
            $saved = [];
            if (self::hasTable('acc_settings')) {
                $json = DB::table('acc_settings')->where('as_company_id', $companyId)->value('as_settings');
                $saved = $json ? (json_decode($json, true) ?: []) : [];
            }
            $s = array_merge(self::DEFAULTS, array_intersect_key($saved, self::DEFAULTS));
            $s['vat_on'] = (bool) $s['vat_on'];
            $s['approval_on'] = (bool) $s['approval_on'];
            $s['vat_rate'] = (float) $s['vat_rate'];
            $s['vat_rebate_rate'] = (float) $s['vat_rebate_rate'];
            self::$cache[$companyId] = $s;
        }
        return self::$cache[$companyId];
    }

    public static function save($companyId, array $values)
    {
        $s = array_merge(self::get($companyId), array_intersect_key($values, self::DEFAULTS));
        DB::table('acc_settings')->updateOrInsert(['as_company_id' => (int) $companyId], [
            'as_settings' => json_encode($s), 'as_updated_by' => optional(Auth::user())->au_id, 'as_updated_at' => Carbon::now(),
        ]);
        unset(self::$cache[(int) $companyId]);
        return self::get($companyId);
    }

    /** VAT is on and has a rate */
    public static function vat($companyId)
    {
        $s = self::get($companyId);
        return $s['vat_on'] && $s['vat_rate'] > 0 ? $s : null;
    }

    /** Company admin (root, company owner or admin) */
    public static function isAdmin($user = null)
    {
        $user = $user ?: Auth::user();
        return $user && in_array((int) $user->au_user_type, [1, 3, 4], true);
    }

    // ------------------------------------------------------------------ closed years

    /** Last day of the latest closed fiscal year of the branch, or null */
    public static function lockedUntil($companyId, $branchId)
    {
        if (!self::hasTable('acc_fiscal_years')) {
            return null;
        }
        $end = DB::table('acc_fiscal_years')->where('afy_company_id', (int) $companyId)->where('afy_branch_id', (int) $branchId)
            ->where('afy_status', 'closed')->max('afy_end');
        return $end ? Carbon::parse($end)->startOfDay() : null;
    }

    /** Message when a date is in a closed year, or null when it is open */
    public static function lockError($date, $companyId, $branchId)
    {
        $until = self::lockedUntil($companyId, $branchId);
        if ($until && Carbon::parse($date)->startOfDay()->lte($until)) {
            return 'The books are closed up to ' . $until->format('d M Y') . ' (Accounts > Fiscal Years). Use a later date, or reopen that year first.';
        }
        return null;
    }

    // ------------------------------------------------------------------ audit trail

    /** Writes one audit line; never stops the work it records */
    public static function log($action, $summary, $entity = null, $entityId = null, $amount = null, array $details = [])
    {
        try {
            if (!self::hasTable('acc_audit_log') || !Auth::user()) {
                return;
            }
            DB::table('acc_audit_log')->insert([
                'aal_company_id' => (int) Auth::user()->au_company_id, 'aal_branch_id' => (int) Auth::user()->au_branch_id,
                'aal_user_id' => Auth::user()->au_id, 'aal_action' => mb_substr($action, 0, 40), 'aal_entity' => $entity ? mb_substr($entity, 0, 30) : null,
                'aal_entity_id' => $entityId !== null ? mb_substr((string) $entityId, 0, 40) : null, 'aal_summary' => mb_substr($summary, 0, 255),
                'aal_amount' => $amount !== null ? round((float) $amount, 2) : null, 'aal_details' => $details ? json_encode($details) : null,
                'aal_ip' => request() ? mb_substr((string) request()->ip(), 0, 45) : null, 'aal_created_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // the audit line is lost, the user's work is not
        }
    }
}

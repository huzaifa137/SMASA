<?php

namespace App\Audit;

use App\Http\Controllers\Helper;
use App\Support\AdminAccess;

/**
 * Who may read the audit trail.
 *
 *  - Admin Portal users holding `audit_trail.view` (Super Administrators
 *    always do; any other admin role can be granted it under Admin Rights):
 *    every school. `audit_trail.export` / `audit_trail.verify` gate the CSV
 *    export and the integrity check.
 *  - School administrators (teacher with the school-administrator role):
 *    only their own school's entries; they may export those, not verify.
 *  - Everyone else: no access.
 */
class AuditAccess
{
    private static ?bool $platform = null;
    private static ?bool $school = null;

    /** An admin-portal user allowed to see the whole trail. */
    public static function isPlatformAdmin(): bool
    {
        return self::$platform ??= (session()->has('LoggedAdmin') && AdminAccess::can('audit_trail.view'));
    }

    /** A school administrator (no admin session), limited to their school. */
    public static function isSchoolAdmin(): bool
    {
        if (self::isPlatformAdmin() || session()->has('LoggedAdmin')) {
            return false;
        }

        return self::$school ??= (session()->has('LoggedSchool') && (bool) Helper::isTechSateAdminOrSchoolAdminsAlone());
    }

    public static function canView(): bool
    {
        return self::isPlatformAdmin() || self::isSchoolAdmin();
    }

    public static function canExport(): bool
    {
        return self::isSchoolAdmin() || (self::isPlatformAdmin() && AdminAccess::can('audit_trail.export'));
    }

    public static function canVerify(): bool
    {
        return self::isPlatformAdmin() && AdminAccess::can('audit_trail.verify');
    }

    /** School id the viewer is limited to, or null when unrestricted. */
    public static function schoolScope(): ?int
    {
        return self::isPlatformAdmin() ? null : (int) session('LoggedSchool');
    }

    public static function authorize(): void
    {
        abort_unless(self::canView(), 403, 'You do not have permission to view the audit trail.');
    }

    public static function authorizeExport(): void
    {
        abort_unless(self::canExport(), 403, 'You do not have permission to export the audit trail.');
    }

    public static function authorizeVerify(): void
    {
        abort_unless(self::canVerify(), 403, 'You do not have permission to run the integrity check.');
    }

    public static function forgetMemo(): void
    {
        self::$platform = null;
        self::$school = null;
    }
}

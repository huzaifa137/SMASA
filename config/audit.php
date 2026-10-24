<?php

/*
|--------------------------------------------------------------------------
| SMASA Audit Trail
|--------------------------------------------------------------------------
|
| Everything the audit trail records and how. The defaults are safe for
| production; most installs never need to touch this file.
|
| What is captured, in layers:
|   1. Data changes   - every Eloquent create / update / delete / restore,
|                       with before & after values (automatic, all models).
|   2. Requests       - every state-changing HTTP request, sensitive reads
|                       (exports, downloads, prints), denied access and
|                       server errors, with sanitised inputs.
|   3. Authentication - sign-in, failed sign-in, sign-out, password resets.
|   4. Bulk writes    - INSERT/UPDATE/DELETE issued through the query
|                       builder are tallied per request (table => counts),
|                       so work that bypasses Eloquent is still visible.
|   5. Console        - artisan commands (migrations, syncs, clean-ups).
|   6. Explicit       - Audit::log(...) for anything else.
|
*/

return [

    // Master switch. AUDIT_ENABLED=false stops all recording.
    'enabled' => env('AUDIT_ENABLED', true),

    /*
    | Days to keep audit entries. `audit:prune` (scheduled daily) removes
    | older rows and records that it did so. 0 or null = keep forever.
    | Many compliance regimes ask for 1-7 years; 730 (2 years) is a sane
    | default - raise it to match your policy.
    */
    'retention_days' => (int) env('AUDIT_RETENTION_DAYS', 730),

    /*
    | Manual purge (Audit Trail page > "Purge old entries"). Entries newer
    | than this many days can never be purged by hand, whatever the admin
    | types. Purge records themselves are never purged.
    */
    'purge_min_days' => (int) env('AUDIT_PURGE_MIN_DAYS', 90),

    /*
    | Key used to sign every row (HMAC-SHA256) so edits made directly in the
    | database are detectable (`audit:verify`). Defaults to the app key.
    | Do NOT change it after go-live or old rows will fail verification.
    */
    'signing_key' => env('AUDIT_SIGNING_KEY'),

    /*
    | Rows per page in the admin viewer and the hard cap on a CSV export.
    */
    'per_page' => 25,
    'export_limit' => 50000,

    /*
    | Anything stored in old/new values or request inputs whose KEY matches
    | one of these (case-insensitive substring) is replaced by [REDACTED].
    */
    'masked_keys' => [
        'password', 'passwd', 'passcode', 'secret', 'token', 'api_key', 'apikey',
        'authorization', 'cvv', 'card_number', 'private_key', 'remember',
        'temp_otp', 'otp', 'pin_code',
    ],

    /*
    | Keys whose value is partially masked (only the last 3 characters are
    | kept) - identity numbers stay recognisable without being exposed.
    */
    'partially_masked_keys' => [
        'national_id', 'passport_number', 'nin',
    ],

    /*
    | A single stored value longer than this is truncated.
    */
    'max_value_length' => 1000,

    /*
    | Layer 1 - Eloquent model events.
    */
    'models' => [
        'enabled' => true,

        // Only models inside these namespaces are audited (keeps vendor
        // models such as push subscriptions out of the trail).
        'namespaces' => ['App\\'],

        // Models never audited (class names). High-volume, purely technical
        // or self-logging tables belong here. AdminAuditLog and BillingLog
        // are the Admin Rights / Billing modules' own activity logs; their
        // underlying data changes are already audited with before/after
        // values, so mirroring them would just double every entry.
        'excluded' => [
            \App\Models\AuditLog::class,
            \App\Models\AuditTrail::class,
            \App\Models\AdminAuditLog::class,
            \App\Models\BillingLog::class,
            \App\Models\CardScanLog::class,
            \App\Models\SmasaNotificationRecipient::class,
            \App\Models\Translation::class,
        ],

        // Attributes ignored when deciding whether an update is worth
        // recording and omitted from before/after values.
        'excluded_attributes' => [
            'created_at', 'updated_at', 'deleted_at', 'remember_token',
            'last_login_at', 'user_last_active',
        ],

        // Changes to these models are tagged "security" (accounts & access
        // control: admin roles, school roles, user accounts).
        'security_models' => [
            \App\Models\User::class,
            \App\Models\AdminRole::class,
            \App\Models\Role::class,
            \App\Models\SchoolRole::class,
            \App\Models\RoleFeatureAccess::class,
            \App\Models\RoleModuleAccess::class,
            \App\Models\TeacherSchoolRole::class,
        ],

        // An UPDATE that touches any of these attributes (on any model) is
        // also tagged "security": promoting a super admin, changing a role,
        // (de)activating or blocking an account.
        'security_attributes' => [
            'is_super_admin', 'admin_role_id', 'user_role', 'is_active',
            'attached_company_role', 'teacher_role', 'account_status',
            'must_change_password',
        ],

        // Money: always at least "notice" severity so they stand out.
        'notice_models' => [
            \App\Models\BillingInvoice::class,
            \App\Models\BillingInvoiceItem::class,
            \App\Models\BillingPayment::class,
            \App\Models\BillingSchoolRate::class,
            \App\Models\BillingSetting::class,
            \App\Models\BillingTerm::class,
            \App\Models\FeePayment::class,
            \App\Models\FinanceTransaction::class,
            \App\Models\Expense::class,
            \App\Models\PayrollPeriod::class,
            \App\Models\PayrollSlip::class,
            \App\Models\StudentFeeAllocation::class,
        ],

        // Safety valve: after this many model entries in ONE request or
        // command the rest are summarised instead of written one by one
        // (e.g. a 20,000 row import). The summary says how many were skipped.
        'max_per_request' => 5000,
    ],

    /*
    | Layer 2 & 3 - HTTP requests.
    */
    'requests' => [
        'enabled' => true,

        // Log every request that is not GET/HEAD/OPTIONS.
        'log_mutating' => true,

        // Log GET requests matching `sensitive_read_patterns` below.
        'log_sensitive_reads' => true,

        // Log GET requests that match one of these patterns (matched
        // against the URL path and the route name). This system performs
        // some deletes/exports through GET, so they are covered here.
        'sensitive_read_patterns' => [
            '*delete*', '*destroy*', '*remove*', '*export*', '*download*',
            '*print*', '*pdf*', '*excel*', '*csv*', '*passslip*',
            '*report-card*', '*receipt*', '*impersonate*', '*admin-user*', '*reset*',
        ],

        // Subset of the above that is an *export / document access*.
        'export_patterns' => [
            '*export*', '*download*', '*print*', '*pdf*', '*excel*', '*csv*',
            '*passslip*', '*report-card*', '*receipt*',
        ],

        // Background chatter: NOT logged while it succeeds (status < 400),
        // still logged the moment it fails (denied, validation error, 5xx).
        // Matched against the URL path and route name.
        'quiet_when_ok' => [
            '*notifications/push/*', '*push.subscribe', '*push.unsubscribe',
            '*heartbeat*', '*keepalive*', '*session-ping*',
        ],

        // Never logged (path patterns).
        'excluded_paths' => [
            'captcha*', 'language/*', 'broadcasting/*', 'push/*',
            '*notifications/unread*', '*notifications/poll*', 'up', 'build/*',
            'audit-trail/*/ping',
        ],

        // POST/PUT requests that only READ (search boxes, lookups). Skipped
        // unless they turn out to write to the database or fail.
        'read_only_post_patterns' => [
            '*/search*', '*search-*', '*/filter*', '*/fetch*', '*/load-*',
            '*/get-*', '*/preview*', '*/check-*', '*datatable*', '*/list',
        ],

        // Routes that sign a user in. A request is "login" when the
        // session identity changed, "login_failed" otherwise.
        'login_routes' => [
            'auth-user-check', 'parents.login.submit', 'auth-user-selected-school',
        ],

        // Request path/route patterns that sign a user out.
        'logout_patterns' => ['*logout*'],

        // route name => [event, category, severity]
        'route_events' => [
            'teacher.send.reset.link' => ['password_reset_requested', 'security', 'notice'],
            'teacher.password.reset'  => ['password_reset', 'security', 'notice'],
        ],

        // Request input recording.
        'max_inputs' => 80,
        'max_input_depth' => 3,
    ],

    /*
    | Layer 4 - tables ignored by the per-request write tally.
    */
    'tally_ignored_tables' => [
        'audit_logs', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches',
        'failed_jobs', 'migrations', 'password_reset_tokens', 'password_resets',
        'personal_access_tokens', 'push_subscriptions',
    ],

    /*
    | Layer 5 - artisan commands.
    */
    'console' => [
        'enabled' => true,
        'excluded_commands' => [
            'list', 'help', 'schedule:run', 'schedule:finish', 'schedule:work',
            'queue:work', 'queue:listen', 'queue:restart', 'queue:retry',
            'config:cache', 'config:clear', 'route:cache', 'route:clear',
            'view:cache', 'view:clear', 'cache:clear', 'optimize', 'optimize:clear',
            'key:generate', 'storage:link', 'tinker', 'serve', 'audit:verify',
            'audit:prune', 'package:discover', 'vendor:publish',
        ],
    ],

    /*
    | Viewer & export.
    */
    'event_labels' => [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'restored' => 'Restored',
        'request' => 'Request',
        'login' => 'Sign in',
        'login_failed' => 'Failed sign in',
        'logout' => 'Sign out',
        'password_changed' => 'Password changed',
        'password_reset_requested' => 'Password reset requested',
        'password_reset' => 'Password reset',
        'access_denied' => 'Access denied',
        'server_error' => 'Server error',
        'export' => 'Export / document',
        'console_command' => 'Console command',
        'school_deleted' => 'School deleted',
        'audit_exported' => 'Audit log exported',
        'audit_verified' => 'Integrity check run',
        'audit_pruned' => 'Audit log pruned',
        'audit_purged' => 'Audit log purged',
        'audit_purge_denied' => 'Audit purge denied',
    ],
];

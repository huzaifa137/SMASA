# Audit Trail

A permanent, tamper-evident record of **who did what, when, from where, and what changed**,
across every school and the Admin Portal. Open it from the sidebar: **Audit Trail** (`/audit-trail`).

## What is recorded (automatically)

| Layer | Detail |
|---|---|
| Data changes | Every Eloquent create / update / delete / restore, with before & after values. Deletes keep a full snapshot. Covers every model, including ones added later. |
| Requests | Every state-changing request, sensitive reads (exports, downloads, receipts, prints, GET deletes), denied access (401/403/419/429), server errors. |
| Authentication | Sign in, failed sign in (5+ from one IP in 15 min = critical), sign out, password reset requests. |
| Bulk writes | `DB::table()` / mass update & delete are tallied per request (table => insert/update/delete counts). |
| Console | Artisan commands (migrations, syncs, `admin:create-super-admin`...). |
| Explicit | `Audit::log('event', 'Description', ['subject' => $model, 'metadata' => [...]])`. School deletion uses this. |

Security-relevant changes (user accounts, admin roles, school roles, super-admin / role / active status)
are tagged **Security**; money (billing, fees, expenses, payroll) is at least **Notice**.
`admin_audit_logs` and `billing_logs` stay as the modules' own activity screens and are not mirrored.

Every entry stores the actor (name & role snapshot), school, IP, device, route, status code, duration and a
`request_id` shared by everything one request produced (also returned as the `X-Request-Id` header).

## Who can see it

| Who | Access |
|---|---|
| Admin with `audit_trail.view` (Super Admins always) | All schools |
| ...with `audit_trail.export` / `audit_trail.verify` | CSV export / integrity check |
| School administrator | Their own school only; may export it |
| Everyone else | 403 |

Grant the three permissions to any admin role under **Admin Rights → Permissions** (module *Audit Trail*).

## Integrity

* Secrets (passwords, tokens, OTPs) are stored as `[REDACTED]`; identity numbers keep their last 3 characters.
* Every row is HMAC-signed. `php artisan audit:verify` (or **Verify integrity**) reports rows edited outside the
  application and gaps in the id sequence (gaps can also come from rolled-back transactions or pruning).
* The application never updates or deletes entries (the model refuses). Do not change `APP_KEY` / `AUDIT_SIGNING_KEY`
  after go-live. For extra protection, give the app's DB user no UPDATE/DELETE on `audit_logs`.

## Operations

```bash
php artisan migrate
php artisan config:clear && php artisan route:clear && php artisan view:clear
php artisan audit:verify            # integrity check
php artisan audit:prune --dry-run   # what retention would remove
```

* `AUDIT_RETENTION_DAYS` (default 730, 0 = keep forever); `audit:prune` is scheduled daily at 02:30 (needs `schedule:run` cron).
* `AUDIT_ENABLED=false` switches everything off. All tuning lives in `config/audit.php`.
* A single request/command itemises at most 5,000 model changes; the rest are summarised on the request entry.

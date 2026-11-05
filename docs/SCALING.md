# Running SMASA at scale

What the scaling patches added and how to switch each piece on. Everything is
safe to deploy first: with the defaults (`QUEUE_CONNECTION=sync`, file cache,
no Sentry) the system behaves exactly as before.

## 1. Cron (required for scheduled work)
```
* * * * * cd /var/www/smasa && php artisan schedule:run >> /dev/null 2>&1
```
This drives scheduled result releases, promotion, audit pruning, backups, the
weekly restore test, and the report-card file cleanup.

## 2. Redis for sessions, cache and queue
1. Install Redis and the PHP `redis` extension (or `composer require predis/predis`).
2. Set the keys from `.env.production.example` (`SESSION_DRIVER`, `CACHE_DRIVER`, `QUEUE_CONNECTION`, `REDIS_*`).
3. `php artisan config:clear` (everyone is logged out once when sessions move).

## 3. Queue workers
`deploy/supervisor-smasa-worker.conf` keeps three workers alive. After every
deploy: `php artisan queue:restart`. Queues: `notifications` (gate-scan parent
alerts, web push), `reports` (whole-school report cards), `default`.

With a real queue:
* a gate scan answers at once; the parent push is sent by a worker (the scan
  toast says "Parent alert is being sent"; the outcome is in the parent
  notification log);
* notification broadcasts save instantly and push in the background;
* "Print all" report cards shows a progress page and opens the finished cards
  when ready (files live in `storage/app/report-cards`, deleted after a day).

Failed jobs: `php artisan queue:failed`, `php artisan queue:retry all`.

## 4. Query counter
Set `QUERY_COUNTER=true`, reload the slow page, read the `X-DB-Queries`
header (browser dev tools, Network tab). Requests over `QUERY_WARN_THRESHOLD`
queries are logged as `QUERY-COUNT ...` with the most repeated queries, which
is how an N+1 shows itself. Turn it off afterwards.

## 5. Name-lookup cache
Subject/class names, school names, the active year and pass-slip settings are
cached (per request, then in the shared cache). Writing to the underlying table
refreshes them automatically. `php artisan cache:clear` also clears them.

## 6. Rate limits
`throttle:login` (60/min per IP, 10/min per account), `throttle:password-reset`
(10/min per IP, 10/hour per account), `throttle:scan` (240/min per school and IP).
Adjust in `AppServiceProvider::registerRateLimiters()`.

## 7. Error monitoring
`composer require sentry/sentry-laravel`, set `SENTRY_LARAVEL_DSN`. Unhandled
exceptions then go to Sentry; nothing changes if the package is absent.
Alternative: `LOG_STACK=single,slack` with a Slack webhook.

## 8. Backups and the restore test
* `php artisan smasa:backup`: consistent mysqldump, gzip, `.sha256`, prunes old files (daily 01:30).
* `php artisan smasa:backup-verify`: restores the newest backup into the scratch
  database `BACKUP_VERIFY_DATABASE`, checks the tables and key row counts against
  the live database, drops the scratch copy (weekly, Sunday 04:30). A failure is
  logged as CRITICAL and exits non-zero.
* The scratch database user needs `CREATE`/`DROP` on that one database.
* **Copy `BACKUP_PATH` off the server** (rclone/rsync to object storage or another
  machine). A backup on the same disk does not survive the disk.
* Real recovery: `gzip -dc smasa-YYYYmmdd-HHMMSS.sql.gz | mysql -u USER -p DATABASE`
  into an empty database, then point `.env` at it.

## 9. Indexes and the access audit
`php artisan migrate` adds the indexes (patch 09). `php artisan smasa:audit-routes`
lists id-taking routes that any logged-in staff member can reach; re-run it
after adding routes.

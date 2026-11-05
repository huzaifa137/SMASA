<?php

return [

    /*
    | Per-request database query counter (App\Http\Middleware\QueryCounter).
    | Turn it on while investigating a slow page, off again afterwards:
    |   QUERY_COUNTER=true
    | Adds X-DB-Queries / X-DB-Time-ms response headers, and writes a warning
    | to the log - with the most repeated queries (the N+1 culprits) - for any
    | request that runs more than QUERY_WARN_THRESHOLD queries.
    */
    /*
    | Offline pages: pages a signed-in user opens are saved in their browser so they
    | still open with no internet (see public/sw.js). Set OFFLINE_PAGES=false to turn
    | it off for everyone; saved copies are then wiped on each device's next visit.
    */
    'offline_pages' => env('OFFLINE_PAGES', true),

    'query_counter' => env('QUERY_COUNTER', false),
    'query_warn_threshold' => (int) env('QUERY_WARN_THRESHOLD', 60),

    /*
    | Daily database backup + restore test (php artisan smasa:backup / smasa:backup-verify)
    */
    'backup' => [
        'path' => env('BACKUP_PATH', storage_path('app/backups')),
        'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),
        // A SCRATCH database the verify command restores into. It is wiped
        // each time - never point this at the live database.
        'verify_database' => env('BACKUP_VERIFY_DATABASE'),
        'mysqldump' => env('MYSQLDUMP_BINARY', 'mysqldump'),
        'mysql' => env('MYSQL_BINARY', 'mysql'),
    ],

];

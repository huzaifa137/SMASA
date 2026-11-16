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

    /*
    | The ONLY pages kept for offline use (see App\Support\OfflinePages + public/sw.js).
    | Each page is saved when visited, and also saved quietly in the background
    | even if the user has never opened it. A page is offered only to users who
    | hold its module / feature, so nobody saves a page they cannot open.
    |
    |   key     unique id            label / desc / icon   shown on /offline-pages
    |   route   Laravel route name   module / feature      PermissionHelper keys (optional)
    */
    'offline_page_list' => [
        ['key' => 'card-scan',  'route' => 'card-scan.hub',     'label' => 'Card Scan',            'icon' => '📇', 'desc' => 'Gate scanning. Scans taken offline are queued and sent when you reconnect.', 'feature' => 'view_hub'],
        ['key' => 'arrival',    'route' => 'card-scan.arrival', 'label' => 'Arrival Attendance',   'icon' => '🚪', 'desc' => 'Who has arrived at school today.',                                          'feature' => 'manage_arrival_attendance'],
        ['key' => 'dashboard',  'route' => 'school.dashboard',  'label' => 'School Dashboard',     'icon' => '🏫', 'desc' => 'Overview of the school.',                                                    'module' => 'dashboard', 'feature' => 'view_dashboard'],
        ['key' => 'attendance', 'route' => 'attendance.students', 'label' => 'Student Attendance', 'icon' => '✅', 'desc' => 'Class attendance portal.',                                                    'module' => 'attendance'],
        ['key' => 'classes',    'route' => 'all.my-classes',    'label' => 'My Classes',           'icon' => '📚', 'desc' => 'Classes and streams you teach.',                                             'module' => 'classes'],
        ['key' => 'timetable',  'route' => 'timetable.dashboard', 'label' => 'Timetable',          'icon' => '🗓️', 'desc' => 'Lesson timetable.',                                                          'module' => 'timetable'],
        ['key' => 'exams',      'route' => 'examination.index', 'label' => 'Examinations',         'icon' => '📝', 'desc' => 'Examinations dashboard.',                                                    'module' => 'examinations'],
    ],

    // A saved page is refreshed in the background when older than this (hours), and only while online.
    'offline_refresh_hours' => (int) env('OFFLINE_REFRESH_HOURS', 6),

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

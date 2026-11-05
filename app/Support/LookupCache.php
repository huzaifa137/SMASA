<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Two-level cache for the small lookups the app repeats thousands of times
 * (subject / class names, a school's display name, the active year, ...):
 *
 *   1. a per-request memo, so a page that asks for "Mathematics" 500 times
 *      queries once;
 *   2. the shared cache (Redis in production) so the NEXT request does not
 *      query at all.
 *
 * Entries are grouped. Writing to a table flushes its group (see
 * AppServiceProvider::registerLookupInvalidation), so a renamed subject or
 * school shows up immediately, and the TTL is only a safety net.
 */
class LookupCache
{
    /** table => group. A write to any of these tables flushes the group. */
    public const TABLE_GROUPS = [
        'master_datas' => 'master',
        'school_alevel_subjects' => 'master',
        'school_olevel_electives' => 'master',
        'custom_subjects' => 'master',
        'schools' => 'school',
        'school_profiles' => 'school',
        'academic_years' => 'year',
        'passslip_settings' => 'passslip',
    ];

    private static array $memo = [];

    public static function remember(string $group, string $key, int $ttl, Closure $resolve)
    {
        $memoKey = $group . '|' . $key;

        if (array_key_exists($memoKey, self::$memo)) {
            return self::$memo[$memoKey];
        }

        try {
            $version = Cache::get('lookup:v:' . $group, 1);

            // Wrapped so a legitimate null / false result is cached too.
            $boxed = Cache::remember(
                'lookup:' . $group . ':' . $version . ':' . md5($key),
                $ttl,
                fn() => ['v' => $resolve()]
            );

            $value = $boxed['v'];
        } catch (\Throwable $e) {
            // A cache outage must never break a page: fall back to the database.
            $value = $resolve();
        }

        return self::$memo[$memoKey] = $value;
    }

    /** Make every cached entry of a group stale (called after a write to its tables). */
    public static function flush(string $group): void
    {
        try {
            Cache::forever('lookup:v:' . $group, microtime(true) . ':' . mt_rand());
        } catch (\Throwable $e) {
            // ignore: TTL still bounds staleness
        }

        foreach (array_keys(self::$memo) as $k) {
            if (str_starts_with($k, $group . '|')) {
                unset(self::$memo[$k]);
            }
        }
    }

    /** Queue workers live across jobs; start each job with a clean memo. */
    public static function forgetMemo(): void
    {
        self::$memo = [];
    }
}

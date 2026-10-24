<?php

namespace App\Http\Controllers;

use App\Audit\Audit;
use App\Audit\AuditAccess;
use App\Models\AuditLog;
use App\Support\AdminAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Manual retention purge of the audit trail.
 *
 * Individual entries can never be deleted (AuditLog blocks it). This is the
 * one bulk path, and it is deliberately hard to use by accident:
 *
 *  - needs the `audit_trail.purge` admin permission (platform admins only);
 *  - entries newer than config('audit.purge_min_days') are untouchable;
 *  - the exact set must be exported (signed CSV archive) first;
 *  - typed phrase + reason + the admin's own password are required;
 *  - password attempts are rate limited;
 *  - purge records are never purged, and every purge writes its own signed
 *    `audit_purged` entry (who, why, how many, id range).
 */
class AuditPurgeController extends Controller
{
    /** Events that survive every purge. */
    public const PROTECTED_EVENTS = ['audit_purged', 'audit_pruned'];

    private const EXPORT_VALID_SECONDS = 1800;
    private const TIME_BUDGET_SECONDS = 240;
    private const CHUNK = 2000;

    public function preview(Request $request)
    {
        $this->authorizePurge();

        $cutoff = $this->cutoffFromDays($this->validatedDays($request));
        $query = $this->eligible($cutoff);

        $local = fn ($utc) => $utc
            ? Carbon::parse($utc, 'UTC')->setTimezone(config('app.timezone'))->format('d M Y H:i')
            : null;

        return response()->json([
            'count' => (clone $query)->count(),
            'oldest' => $local((clone $query)->min('occurred_at')),
            'newest' => $local((clone $query)->max('occurred_at')),
            'cutoff' => $cutoff->format('d M Y'),
        ]);
    }

    /** Signed, uncapped CSV archive of exactly what a purge would delete. */
    public function export(Request $request): StreamedResponse
    {
        $this->authorizePurge();

        $cutoff = $this->cutoffFromDays($this->validatedDays($request));
        $query = $this->eligible($cutoff);
        $total = (clone $query)->count();

        Audit::log('audit_exported', "Exported {$total} audit entries to CSV (archive before purge)", [
            'category' => 'security',
            'severity' => 'notice',
            'metadata' => ['rows' => $total, 'purpose' => 'purge_archive', 'older_than' => $cutoff->toDateString()],
        ]);

        // Unlocks the purge for this exact cutoff for a limited time.
        session(['audit_purge_export' => [
            'cutoff' => $cutoff->toDateString(),
            'rows' => $total,
            'at' => now()->timestamp,
        ]]);

        $columns = array_merge(['id'], AuditLog::SIGNED_FIELDS, ['signature']);
        $filename = 'audit-archive-before-' . $cutoff->toDateString() . '-' . now()->format('His') . '.csv';

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $columns);

            // Raw stored values + signature, so the archive stays verifiable.
            foreach ($query->lazyById(1000) as $row) {
                fputcsv($out, array_map(
                    fn ($col) => $this->csvCell($row->{$col} ?? null),
                    $columns
                ));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function purge(Request $request)
    {
        $this->authorizePurge();

        $min = $this->minDays();

        $data = $request->validateWithBag('auditPurge', [
            'days' => "required|integer|min:{$min}|max:3650",
            'reason' => 'required|string|min:10|max:255',
            'confirm' => 'required|in:PURGE',
            'password' => 'required|string',
        ], [
            'days.min' => "Entries newer than {$min} days cannot be purged.",
            'confirm.in' => 'Type PURGE (capital letters) to confirm.',
            'reason.min' => 'Give a reason of at least 10 characters.',
        ]);

        $cutoff = $this->cutoffFromDays((int) $data['days']);

        // 1. The exact set must have been exported recently.
        $token = session('audit_purge_export');
        if (
            !is_array($token)
            || ($token['cutoff'] ?? null) !== $cutoff->toDateString()
            || now()->timestamp - (int) ($token['at'] ?? 0) > self::EXPORT_VALID_SECONDS
        ) {
            return $this->fail('Download the CSV archive for this cutoff first (it stays valid for 30 minutes).');
        }

        // 2. Re-authenticate, with rate limiting.
        $key = 'audit-purge:' . AdminAccess::userId();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return $this->fail('Too many attempts. Try again in ' . ceil(RateLimiter::availableIn($key) / 60) . ' minute(s).');
        }

        $user = AdminAccess::user();

        if (!$user || !Hash::check($data['password'], (string) $user->password)) {
            RateLimiter::hit($key, 900);

            Audit::security('audit_purge_denied', 'Audit purge refused: wrong password', [
                'outcome' => 'failure',
                'metadata' => ['days' => (int) $data['days']],
            ]);

            return $this->fail('Incorrect password.');
        }

        RateLimiter::clear($key);

        // 3. Delete in chunks, bounded by a time budget.
        @set_time_limit(self::TIME_BUDGET_SECONDS + 60);

        $started = microtime(true);
        $deleted = 0;
        $firstId = null;
        $lastId = null;
        $error = null;

        try {
            do {
                $ids = $this->eligible($cutoff)->orderBy('id')->limit(self::CHUNK)->pluck('id');

                if ($ids->isEmpty()) {
                    break;
                }

                $firstId ??= $ids->first();
                $lastId = $ids->last();
                $deleted += DB::table('audit_logs')->whereIn('id', $ids)->delete();

                if (microtime(true) - $started > self::TIME_BUDGET_SECONDS) {
                    break;
                }
            } while (true);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            report($e);
        }

        $remaining = $this->eligible($cutoff)->count();

        // 4. The purge is itself a permanent, signed entry (written even on partial failure).
        Audit::log(
            'audit_purged',
            "Purged {$deleted} audit entries older than {$cutoff->format('d M Y')}"
                . ($remaining ? " ({$remaining} still pending)" : ''),
            [
                'category' => 'security',
                'severity' => 'warning',
                'outcome' => $error ? 'failure' : 'success',
                'metadata' => [
                    'deleted' => $deleted,
                    'remaining' => $remaining,
                    'days' => (int) $data['days'],
                    'cutoff_utc' => $cutoff->copy()->utc()->format('Y-m-d H:i:s'),
                    'reason' => $data['reason'],
                    'first_id' => $firstId,
                    'last_id' => $lastId,
                    'exported_rows' => $token['rows'] ?? null,
                    'error' => $error,
                ],
            ]
        );

        Cache::forget('audit:stats:all');

        if (!$remaining && !$error) {
            session()->forget('audit_purge_export');
        }

        return redirect()->route('audit.index')->with('audit_purge_result', [
            'deleted' => $deleted,
            'remaining' => $remaining,
            'error' => $error,
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function authorizePurge(): void
    {
        AuditAccess::authorize();
        AuditAccess::authorizePurge();
    }

    private function minDays(): int
    {
        return max(1, (int) config('audit.purge_min_days', 90));
    }

    private function validatedDays(Request $request): int
    {
        $min = $this->minDays();

        return (int) $request->validate([
            'days' => "required|integer|min:{$min}|max:3650",
        ])['days'];
    }

    /** Start of the local day, $days ago. Everything strictly before it is eligible. */
    private function cutoffFromDays(int $days): Carbon
    {
        return Carbon::now(config('app.timezone'))->subDays($days)->startOfDay();
    }

    private function eligible(Carbon $cutoff)
    {
        return DB::table('audit_logs')
            ->where('occurred_at', '<', $cutoff->copy()->utc()->format('Y-m-d H:i:s.u'))
            ->whereNotIn('event', self::PROTECTED_EVENTS);
    }

    private function fail(string $message)
    {
        return back()->withInput(request()->only('days', 'reason'))
            ->withErrors(['purge' => $message], 'auditPurge');
    }

    /** Neutralise spreadsheet formula injection in exported cells. */
    private function csvCell($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}

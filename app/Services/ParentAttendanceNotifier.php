<?php

namespace App\Services;

use App\Models\ParentAccount;
use App\Models\ParentNotificationLog;
use App\Models\Student;
use App\Notifications\SmasaPushNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\Events\NotificationFailed;
use NotificationChannels\WebPush\Events\NotificationSent;

/**
 * Tells a parent (via Web Push to the phone they registered in the Parent
 * Portal) that their child arrived at / signed out of school.
 *
 * Parents are matched the same way the Parent Portal matches them:
 * Student.primary_contact === ParentAccount.phone.
 *
 * notify() NEVER throws and ALWAYS returns a result array, so a push problem
 * can't break card scanning, and the scanner screen can show the reason:
 *
 *   [
 *     'status'        => 'sent' | 'failed' | 'skipped',
 *     'code'          => machine readable reason (no_phone, no_account, no_device, ...),
 *     'reason'        => human readable explanation (null when fully sent),
 *     'devices_total' => number of phones registered,
 *     'devices_sent'  => number of phones the push service accepted,
 *     'parent_phone'  => the number used for matching,
 *   ]
 */
class ParentAttendanceNotifier
{
    private static bool $listening = false;

    /** Collected by the webpush events during one notifyNow() call. */
    private static array $sentEndpoints = [];
    private static array $failures = [];

    /**
     * @param string $event   'arrival' | 'signout'
     * @param array  $context class, time (h:i A), label, status ('present'|'late' / 'early'|'on_time'), cutoff
     */
    public static function notify(Student $student, string $event, array $context = []): array
    {
        $schoolId = $student->school_id;
        $phone = trim((string) $student->primary_contact);

        try {
            if ($phone === '') {
                return self::finish($student, $event, $phone, 'failed', 'no_phone',
                    "No parent phone number is saved for this student (Primary Contact is empty), so there is nobody to notify.");
            }

            $account = ParentAccount::where('phone', $phone)->first();
            if (!$account) {
                return self::finish($student, $event, $phone, 'failed', 'no_account',
                    "The parent ({$phone}) has not opened the Parent Portal yet, so no phone is registered. "
                    . "Ask them to log in at the Parent Portal and turn Notifications ON.");
            }

            $devices = $account->pushSubscriptions()->count();
            if ($devices === 0) {
                return self::finish($student, $event, $phone, 'failed', 'no_device',
                    "The parent ({$phone}) has an account but has not turned Notifications ON on any phone. "
                    . "They must open the Parent Portal on their phone and switch Notifications ON.", 0, 0);
            }

            [$title, $body] = self::compose($student, $event, $context);

            self::ensureListening();
            self::$sentEndpoints = [];
            self::$failures = [];

            $account->notifyNow(new SmasaPushNotification(
                title: $title,
                body: $body,
                url: route('parents.attendance', $student->id),
            ));

            $sent = count(array_unique(self::$sentEndpoints));
            $failed = self::$failures;

            if ($sent > 0) {
                $note = $failed
                    ? 'Delivered to ' . $sent . ' of ' . $devices . ' device(s). ' . implode(' ', array_column($failed, 'reason'))
                    : null;
                return self::finish($student, $event, $phone, 'sent', $failed ? 'partial' : null, $note, $devices, $sent);
            }

            if ($failed) {
                $first = $failed[0];
                return self::finish($student, $event, $phone, 'failed', $first['code'], $first['reason'], $devices, 0);
            }

            // Channel produced no report at all (e.g. VAPID keys missing so nothing was queued).
            return self::finish($student, $event, $phone, 'failed', 'no_report',
                'The push service returned no delivery report. Check that VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY and VAPID_SUBJECT are set in .env.',
                $devices, 0);
        } catch (\Throwable $e) {
            Log::error('Parent attendance push failed', [
                'student_id' => $student->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            return self::finish($student, $event, $phone, 'failed', 'exception',
                'Notification error: ' . \Illuminate\Support\Str::limit($e->getMessage(), 200));
        }
    }

    /** Record a deliberately skipped notification (setting switched off). */
    public static function skipped(Student $student, string $event, string $why): array
    {
        return [
            'status' => 'skipped',
            'code' => 'disabled',
            'reason' => $why,
            'devices_total' => 0,
            'devices_sent' => 0,
            'parent_phone' => trim((string) $student->primary_contact),
        ];
    }

    // ── internals ──────────────────────────────────────────────────

    private static function compose(Student $student, string $event, array $c): array
    {
        $first = trim((string) $student->firstname) ?: 'Your child';
        $full = trim($student->firstname . ' ' . $student->lastname);
        $class = !empty($c['class']) ? " ({$c['class']})" : '';
        $time = $c['time'] ?? now()->format('h:i A');
        $label = $c['label'] ?? '';
        $status = $c['status'] ?? '';

        if ($event === 'signout') {
            if ($status === 'early') {
                return ["⚠️ {$first} left school early", "{$full}{$class} signed out at {$time}. {$label}."];
            }
            return ["🏠 {$first} has signed out", "{$full}{$class} signed out of school at {$time}."];
        }

        if ($status === 'late') {
            return ["⚠️ {$first} arrived late", "{$full}{$class} arrived at {$time}. {$label}."];
        }
        return ["✅ {$first} has arrived at school", "{$full}{$class} arrived at {$time}. {$label}."];
    }

    private static function finish(
        Student $student, string $event, string $phone, string $status,
        ?string $code, ?string $reason, int $devicesTotal = 0, int $devicesSent = 0
    ): array {
        $result = [
            'status' => $status,
            'code' => $code,
            'reason' => $reason,
            'devices_total' => $devicesTotal,
            'devices_sent' => $devicesSent,
            'parent_phone' => $phone,
        ];

        try {
            ParentNotificationLog::create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'parent_phone' => $phone ?: null,
                'event' => $event,
                'status' => $status,
                'code' => $code,
                'reason' => $reason,
                'devices_total' => $devicesTotal,
                'devices_sent' => $devicesSent,
            ]);
        } catch (\Throwable $e) {
            // Logging must never break scanning (e.g. migration not run yet).
            Log::warning('Could not write parent_notification_logs: ' . $e->getMessage());
        }

        if ($status === 'failed') {
            Log::warning('Parent attendance push not delivered', $result + ['student_id' => $student->id]);
        }

        return $result;
    }

    private static function ensureListening(): void
    {
        if (self::$listening) {
            return;
        }
        self::$listening = true;

        Event::listen(NotificationSent::class, function ($e) {
            self::$sentEndpoints[] = $e->subscription->endpoint;
        });

        Event::listen(NotificationFailed::class, function ($e) {
            self::$failures[] = self::explainFailure($e->report);
        });
    }

    /** Turn a MessageSentReport into a plain-language reason. */
    private static function explainFailure($report): array
    {
        $status = null;
        try {
            $status = $report->getResponse() ? $report->getResponse()->getStatusCode() : null;
        } catch (\Throwable $e) {
        }
        $raw = '';
        try {
            $raw = (string) $report->getReason();
        } catch (\Throwable $e) {
        }

        if ($report->isSubscriptionExpired() || in_array($status, [404, 410], true)) {
            return ['code' => 'device_expired', 'reason' => "The parent's phone registration has expired or was removed, so it was cleared. They must turn Notifications ON again in the Parent Portal."];
        }
        if (in_array($status, [401, 403], true)) {
            return ['code' => 'vapid_rejected', 'reason' => "The push service rejected our credentials (HTTP {$status}). Check VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY / VAPID_SUBJECT in .env — parents must subscribe with the same keys."];
        }
        if ($status === 413) {
            return ['code' => 'payload_too_large', 'reason' => 'The push service refused the message because it was too large.'];
        }
        if ($status === 429) {
            return ['code' => 'rate_limited', 'reason' => 'The push service is rate-limiting us right now. Try again in a few minutes.'];
        }
        if ($status === null) {
            return ['code' => 'unreachable', 'reason' => 'Could not reach the push service from the server' . ($raw ? ': ' . \Illuminate\Support\Str::limit($raw, 160) : '.')];
        }

        return ['code' => 'push_error', 'reason' => "Push service error (HTTP {$status})" . ($raw ? ': ' . \Illuminate\Support\Str::limit($raw, 160) : '.')];
    }
}

<?php

namespace App\Services;

use App\Models\NotificationRoutingRule;
use App\Models\SmasaNotification;
use App\Support\NotificationEvents;
use App\Models\SmasaNotificationRecipient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification to specific recipients.
     *
     * @param array $data
     * @param array $recipients
     * [
     *   ['type' => 'admin', 'id' => 1, 'school_id' => 1],
     *   ['type' => 'teacher', 'id' => 5, 'school_id' => 1]
     * ]
     */
    public static function send(array $data, array $recipients): SmasaNotification
    {
        $typeConfig = SmasaNotification::typeConfig();

        $type = $data['type'] ?? SmasaNotification::TYPE_GENERAL;

        $config = $typeConfig[$type]
            ?? $typeConfig[SmasaNotification::TYPE_GENERAL];

        $schoolId = isset($data['school_id']) ? (int) $data['school_id'] : null;

        // Never deliver to someone who does not belong to the notification's school.
        $recipients = self::restrictToSchool($recipients, $schoolId);

        $notification = SmasaNotification::create([
            'school_id' => $schoolId,
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $type,
            'icon' => $data['icon'] ?? $config['icon'],
            'color' => $data['color'] ?? $config['color'],
            'url' => $data['url'] ?? null,
            'module' => $data['module'] ?? null,
            'triggered_by' => $data['triggered_by'] ?? null,
        ]);

        // One bulk INSERT per 500 recipients instead of one query per recipient.
        $now = now();
        foreach (array_chunk($recipients, 500) as $chunk) {
            \Illuminate\Support\Facades\DB::table('smasa_notification_recipients')->insert(array_map(fn($r) => [
                'notification_id' => $notification->id,
                'recipient_type' => $r['type'],
                'recipient_id' => $r['id'],
                'school_id' => $schoolId,
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk));
        }

        // ── Fire Web Push to all User accounts that are recipients ──
        // Note: $notification->icon is a UI icon keyword (e.g. "bell", "graduation-cap")
        // used for the in-app dropdown, NOT an image URL — do not forward it to the
        // push payload. Let SmasaPushNotification fall back to the real logo image.
        self::dispatchPush($recipients, [
            'title' => $notification->title,
            'body' => $notification->body,
            'url' => $notification->url,
        ]);

        return $notification;
    }

    /**
     * Resolve User models for push-eligible recipients and send.
     * 'admin' = platform users table; 'teacher' = school staff.
     * Teachers/students can be added when those models get Notifiable.
     */
private static function dispatchPush(array $recipients, array $payload): void
{
    // With a real queue (QUEUE_CONNECTION=redis/database) the slow part - one
    // HTTP call per device - runs in a worker; with 'sync' it runs inline as before.
    if (config('queue.default') !== 'sync') {
        $pushable = collect($recipients)->whereIn('type', ['admin', 'teacher'])
            ->map(fn($r) => ['type' => $r['type'], 'id' => $r['id']])->unique()->values()->all();

        if ($pushable) {
            \App\Jobs\SendPushNotifications::dispatch($pushable, $payload)->afterCommit();
        }

        return;
    }

    self::pushNow($recipients, $payload);
}

/** The actual push delivery (called inline, or by the SendPushNotifications job). */
public static function pushNow(array $recipients, array $payload): void
{
    try {
        $pushNotification = new \App\Notifications\SmasaPushNotification(
            title: $payload['title'],
            body: $payload['body'],
            url: $payload['url'] ?? null,
        );

        $adminIds = collect($recipients)->where('type', 'admin')->pluck('id')->unique()->values();
        if ($adminIds->isNotEmpty()) {
            foreach (\App\Models\User::whereIn('id', $adminIds)->get() as $user) {
                if ($user->pushSubscriptions()->exists()) {
                    $user->notify($pushNotification);
                }
            }
        }

        $teacherIds = collect($recipients)->where('type', 'teacher')->pluck('id')->unique()->values();
        if ($teacherIds->isNotEmpty()) {
            foreach (\App\Models\Teacher::whereIn('id', $teacherIds)->get() as $teacher) {
                if ($teacher->pushSubscriptions()->exists()) {
                    $teacher->notify($pushNotification);
                }
            }
        }

        // Student push is not wired yet — Student model has no Notifiable/HasPushSubscriptions
        // trait, and no student-facing page registers a push subscription. Add both if needed.
    } catch (\Throwable $e) {
        // Push failure must NEVER crash the main notification flow
        \Log::warning('WebPush dispatch failed: ' . $e->getMessage());
    }
}

    /**
     * Send to the school's own administrators: the active staff of THIS school
     * whose role grants access to notifications. (Previously this selected every
     * platform admin in the `users` table, across all schools.)
     */
    public static function sendToAllAdmins(
        array $data,
        int $schoolId
    ): SmasaNotification {
        return self::sendToSchoolStaff($data, $schoolId);
    }

    /**
     * Staff of one school who may see notifications (feature `$feature`).
     * A school that has not configured any role yet falls back to all its
     * active staff, so notifications are not silently dropped during set-up.
     */
    public static function sendToSchoolStaff(
        array $data,
        int $schoolId,
        string $feature = 'view_my_notifications'
    ): SmasaNotification {
        $staff = self::schoolStaffIds($schoolId, $feature)
            ->map(fn($id) => ['type' => 'teacher', 'id' => $id, 'school_id' => $schoolId])
            ->toArray();

        return self::send(array_merge($data, ['school_id' => $schoolId]), $staff);
    }

    /**
     * Platform (SMASA) administrators only: for system-wide messages that are
     * not about one school's day-to-day activity.
     */
    public static function sendToPlatformAdmins(array $data): SmasaNotification
    {
        $admins = DB::table('users')
            ->where('user_role', 'admin')
            ->pluck('id')
            ->map(fn($id) => ['type' => 'admin', 'id' => $id])
            ->toArray();

        return self::send(array_merge($data, ['school_id' => null]), $admins);
    }

    /** @return \Illuminate\Support\Collection<int,int> */
    public static function schoolStaffIds(int $schoolId, string $feature)
    {
        $active = fn($q) => $q->where('teachers.school_id', $schoolId)
            ->where(function ($w) {
                $w->whereNull('teachers.account_status')
                    ->orWhereNotIn('teachers.account_status', ['blocked', 'suspended']);
            });

        $schoolHasRoles = DB::table('school_roles')->where('school_id', $schoolId)->exists();

        if (!$schoolHasRoles) {
            return $active(DB::table('teachers'))->pluck('teachers.id')->map(fn($i) => (int) $i);
        }

        return $active(DB::table('teachers'))
            ->join('teacher_school_roles as tsr', function ($j) use ($schoolId) {
                $j->on('tsr.teacher_id', '=', 'teachers.id')->where('tsr.school_id', $schoolId);
            })
            ->join('role_feature_access as rfa', function ($j) {
                $j->on('rfa.school_role_id', '=', 'tsr.school_role_id')->where('rfa.can_access', true);
            })
            ->join('module_features as mf', 'mf.id', '=', 'rfa.feature_id')
            ->where('mf.key', $feature)
            ->distinct()
            ->pluck('teachers.id')
            ->map(fn($i) => (int) $i);
    }

    // ─── Automatic notifications: who gets what (per school) ────────────────

    /**
     * Raise one of the automatic notifications listed in NotificationEvents,
     * delivering it to whoever THIS school chose (Notifications → Who Gets
     * Notified), or to the built-in default when the school chose nothing.
     *
     * Returns null when nothing was sent: the school switched the event off,
     * or nobody qualifies. Never throws, so a notification problem can never
     * break the action that raised it (recording a payment, saving attendance…).
     *
     * @param array $context  for student events: ['student_ids' => [..]] to limit
     *                        the audience (default: every student of the school)
     */
    public static function sendEvent(string $eventKey, array $data, int $schoolId, array $context = []): ?SmasaNotification
    {
        try {
            $event = NotificationEvents::find($eventKey);

            if (!$event) {
                Log::warning("Unknown notification event '{$eventKey}', sending to school staff.");
                return self::sendToSchoolStaff($data, $schoolId);
            }

            $rule = NotificationRoutingRule::lookup($schoolId, $eventKey);

            if ($rule && $rule->mode === NotificationRoutingRule::MODE_DISABLED) {
                return null;
            }

            $data = array_merge(
                ['type' => $event['type'], 'module' => $event['module']],
                $data,
                ['school_id' => $schoolId]
            );

            if ($event['audience'] === 'students') {
                $ids = isset($context['student_ids'])
                    ? collect($context['student_ids'])->map(fn($i) => (int) $i)->unique()->values()
                    : DB::table('students')->where('school_id', $schoolId)->pluck('id')->map(fn($i) => (int) $i);

                $recipients = $ids->map(fn($id) => ['type' => 'student', 'id' => $id, 'school_id' => $schoolId])->all();
            } else {
                $recipients = self::staffIdsForEvent($event, $rule, $schoolId)
                    ->map(fn($id) => ['type' => 'teacher', 'id' => $id, 'school_id' => $schoolId])
                    ->all();
            }

            if (empty($recipients)) {
                return null;
            }

            return self::send($data, $recipients);
        } catch (\Throwable $e) {
            Log::warning("Notification event '{$eventKey}' failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * The staff (teacher ids) who receive an event for this school.
     *
     *  custom   → every active staff member holding one of the chosen roles,
     *             plus the individually chosen people
     *  default  → staff who can open notifications AND whose role can open the
     *             event's module (a librarian is not told about fee payments).
     *             If that leaves nobody, every staff member who can open
     *             notifications, so an event is never silently lost.
     *
     * @return \Illuminate\Support\Collection<int,int>
     */
    public static function staffIdsForEvent(array $event, ?NotificationRoutingRule $rule, int $schoolId)
    {
        if ($rule && $rule->mode === NotificationRoutingRule::MODE_CUSTOM) {
            return self::staffIdsWithRoles($schoolId, (array) $rule->role_ids)
                ->merge(self::activeStaffAmong($schoolId, (array) $rule->teacher_ids))
                ->unique()
                ->values();
        }

        return self::defaultStaffIds($schoolId, $event['module'] ?? null);
    }

    /** @return \Illuminate\Support\Collection<int,int> */
    public static function defaultStaffIds(int $schoolId, ?string $module)
    {
        $all = self::schoolStaffIds($schoolId, 'view_my_notifications');

        $schoolHasRoles = DB::table('school_roles')->where('school_id', $schoolId)->exists();
        if (!$module || !$schoolHasRoles) {
            return $all;
        }

        $withModule = self::activeStaffQuery($schoolId)
            ->join('teacher_school_roles as tsr', function ($j) use ($schoolId) {
                $j->on('tsr.teacher_id', '=', 'teachers.id')->where('tsr.school_id', $schoolId);
            })
            ->join('role_module_access as rma', function ($j) {
                $j->on('rma.school_role_id', '=', 'tsr.school_role_id')->where('rma.can_access', true);
            })
            ->join('system_modules as sm', 'sm.id', '=', 'rma.module_id')
            ->where('sm.key', $module)
            ->distinct()
            ->pluck('teachers.id')
            ->map(fn($i) => (int) $i);

        $narrowed = $all->intersect($withModule)->values();

        return $narrowed->isNotEmpty() ? $narrowed : $all;
    }

    /** @return \Illuminate\Support\Collection<int,int> */
    public static function staffIdsWithRoles(int $schoolId, array $roleIds)
    {
        $roleIds = collect($roleIds)->map(fn($i) => (int) $i)->filter()->unique()->values();
        if ($roleIds->isEmpty()) {
            return collect();
        }

        return self::activeStaffQuery($schoolId)
            ->join('teacher_school_roles as tsr', function ($j) use ($schoolId) {
                $j->on('tsr.teacher_id', '=', 'teachers.id')->where('tsr.school_id', $schoolId);
            })
            ->join('school_roles as sr', function ($j) use ($schoolId) {
                $j->on('sr.id', '=', 'tsr.school_role_id')->where('sr.school_id', $schoolId);
            })
            ->whereIn('tsr.school_role_id', $roleIds)
            ->distinct()
            ->pluck('teachers.id')
            ->map(fn($i) => (int) $i);
    }

    /** @return \Illuminate\Support\Collection<int,int> */
    public static function activeStaffAmong(int $schoolId, array $teacherIds)
    {
        $teacherIds = collect($teacherIds)->map(fn($i) => (int) $i)->filter()->unique()->values();
        if ($teacherIds->isEmpty()) {
            return collect();
        }

        return self::activeStaffQuery($schoolId)
            ->whereIn('teachers.id', $teacherIds)
            ->pluck('teachers.id')
            ->map(fn($i) => (int) $i);
    }

    /** Active (not blocked / suspended) staff of one school. */
    private static function activeStaffQuery(int $schoolId)
    {
        return DB::table('teachers')
            ->where('teachers.school_id', $schoolId)
            ->where(function ($w) {
                $w->whereNull('teachers.account_status')
                    ->orWhereNotIn('teachers.account_status', ['blocked', 'suspended']);
            });
    }

    /**
     * Drop recipients that do not belong to $schoolId. Teachers and students
     * are verified against their own school_id; `admin` (platform) recipients
     * pass through; any other type is discarded. With no school (system
     * message) nothing is filtered.
     */
    private static function restrictToSchool(array $recipients, ?int $schoolId): array
    {
        if ($schoolId === null) {
            return $recipients;
        }

        $valid = [];
        foreach (['teacher' => 'teachers', 'student' => 'students'] as $type => $table) {
            $ids = collect($recipients)->where('type', $type)->pluck('id')->unique()->values();
            $valid[$type] = $ids->isEmpty()
                ? collect()
                : DB::table($table)->where('school_id', $schoolId)->whereIn('id', $ids)->pluck('id')->map(fn($i) => (int) $i);
        }

        return collect($recipients)
            ->filter(function ($r) use ($valid) {
                return match ($r['type']) {
                    'admin' => true,
                    'teacher', 'student' => $valid[$r['type']]->contains((int) $r['id']),
                    default => false,
                };
            })
            ->unique(fn($r) => $r['type'] . ':' . $r['id'])
            ->values()
            ->all();
    }

    /**
     * Send to all teachers in a school.
     */
    public static function sendToAllTeachers(
        array $data,
        int $schoolId
    ): SmasaNotification {
        $teachers = DB::table('teachers')
            ->where('school_id', $schoolId)
            ->pluck('id')
            ->map(fn($id) => [
                'type' => 'teacher',
                'id' => $id,
                'school_id' => $schoolId,
            ])
            ->toArray();

        return self::send(
            array_merge($data, ['school_id' => $schoolId]),
            $teachers
        );
    }

    /**
     * Send to all students in a school.
     */
    public static function sendToAllStudents(
        array $data,
        int $schoolId
    ): SmasaNotification {
        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->pluck('id')
            ->map(fn($id) => [
                'type' => 'student',
                'id' => $id,
                'school_id' => $schoolId,
            ])
            ->toArray();

        return self::send(
            array_merge($data, ['school_id' => $schoolId]),
            $students
        );
    }

    /**
     * Send to everyone in a school.
     */
    public static function sendToEveryone(
        array $data,
        int $schoolId
    ): SmasaNotification {

        $teachers = DB::table('teachers')
            ->where('school_id', $schoolId)
            ->pluck('id')
            ->map(fn($id) => [
                'type' => 'teacher',
                'id' => $id,
                'school_id' => $schoolId,
            ])
            ->toArray();

        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->pluck('id')
            ->map(fn($id) => [
                'type' => 'student',
                'id' => $id,
                'school_id' => $schoolId,
            ])
            ->toArray();

        return self::send(
            array_merge($data, ['school_id' => $schoolId]),
            array_merge($teachers, $students)
        );
    }

    /**
     * Get unread notification count.
     */
    public static function unreadCount(
        string $type,
        int $id,
        ?int $schoolId = null
    ): int {
        return SmasaNotificationRecipient::where(
            'recipient_type',
            $type
        )
            ->where('recipient_id', $id)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get notifications for recipient.
     */
    public static function forRecipient(
        string $type,
        int $id,
        int $perPage = 20,
        ?int $schoolId = null
    ) {
        return SmasaNotificationRecipient::with('notification')
            ->where('recipient_type', $type)
            ->where('recipient_id', $id)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Mark a notification as read.
     */
    public static function markRead(
        string $type,
        int $recipientId,
        int $notificationId,
        ?int $schoolId = null
    ): void {
        SmasaNotificationRecipient::where(
            'recipient_type',
            $type
        )
            ->where('recipient_id', $recipientId)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where('notification_id', $notificationId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Mark all notifications as read.
     */
    public static function markAllRead(
        string $type,
        int $recipientId,
        ?int $schoolId = null
    ): void {
        SmasaNotificationRecipient::where(
            'recipient_type',
            $type
        )
            ->where('recipient_id', $recipientId)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
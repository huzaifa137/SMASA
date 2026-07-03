<?php

namespace App\Services;

use App\Models\SmasaNotification;
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

        $notification = SmasaNotification::create([
            'school_id' => $data['school_id'] ?? null,
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $type,
            'icon' => $data['icon'] ?? $config['icon'],
            'color' => $data['color'] ?? $config['color'],
            'url' => $data['url'] ?? null,
            'module' => $data['module'] ?? null,
            'triggered_by' => $data['triggered_by'] ?? null,
        ]);

        foreach ($recipients as $recipient) {
            SmasaNotificationRecipient::create([
                'notification_id' => $notification->id,
                'recipient_type' => $recipient['type'],
                'recipient_id' => $recipient['id'],
                'school_id' => $recipient['school_id']
                    ?? $data['school_id']
                    ?? null,
                'is_read' => false,
            ]);
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
     * Only 'admin' type recipients map to the users table right now.
     * Teachers/students can be added when those models get Notifiable.
     */
private static function dispatchPush(array $recipients, array $payload): void
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
     * Send to all admins in a school.
     */
    public static function sendToAllAdmins(
        array $data,
        int $schoolId
    ): SmasaNotification {
        $admins = DB::table('users')
            ->where('user_role', 'admin')
            ->pluck('id')
            ->map(fn($id) => [
                'type' => 'admin',
                'id' => $id,
                'school_id' => $schoolId,
            ])
            ->toArray();

        return self::send(
            array_merge($data, ['school_id' => $schoolId]),
            $admins
        );
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

        $admins = DB::table('users')
            ->where('user_role', 'admin')
            ->pluck('id')
            ->map(fn($id) => [
                'type' => 'admin',
                'id' => $id,
                'school_id' => $schoolId,
            ])
            ->toArray();

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
            array_merge($admins, $teachers, $students)
        );
    }

    /**
     * Get unread notification count.
     */
    public static function unreadCount(
        string $type,
        int $id
    ): int {
        return SmasaNotificationRecipient::where(
            'recipient_type',
            $type
        )
            ->where('recipient_id', $id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get notifications for recipient.
     */
    public static function forRecipient(
        string $type,
        int $id,
        int $perPage = 20
    ) {
        return SmasaNotificationRecipient::with('notification')
            ->where('recipient_type', $type)
            ->where('recipient_id', $id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Mark a notification as read.
     */
    public static function markRead(
        string $type,
        int $recipientId,
        int $notificationId
    ): void {
        SmasaNotificationRecipient::where(
            'recipient_type',
            $type
        )
            ->where('recipient_id', $recipientId)
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
        int $recipientId
    ): void {
        SmasaNotificationRecipient::where(
            'recipient_type',
            $type
        )
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
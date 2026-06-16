<?php

namespace App\Services;

use App\Models\SmasaNotification;
use App\Models\SmasaNotificationRecipient;
use Illuminate\Support\Facades\DB;

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

        return $notification;
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
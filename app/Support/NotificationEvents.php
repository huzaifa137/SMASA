<?php

namespace App\Support;

use App\Models\SmasaNotification;

/**
 * Catalogue of the AUTOMATIC notifications the system raises (as opposed to
 * a person broadcasting a message by hand). Each school chooses who receives
 * each one under Notifications → Who Gets Notified; see NotificationRoutingRule
 * and NotificationService::sendEvent().
 *
 * To add a new automatic notification: add it here, then call
 *   NotificationService::sendEvent('your_key', [...title/body...], $schoolId)
 * from the place where it happens. It then shows up on the settings page
 * automatically.
 *
 *   audience = staff     → school staff; the school picks roles / people
 *   audience = students  → the affected students; the school can only switch it on/off
 *   module               → system module whose role access decides the DEFAULT recipients
 */
class NotificationEvents
{
    public const FEE_PAYMENT_RECEIVED = 'fee_payment_received';
    public const ATTENDANCE_ABSENTEES = 'attendance_absentees';
    public const LIBRARY_BOOK_ISSUED = 'library_book_issued';
    public const LIBRARY_BOOK_RETURNED = 'library_book_returned';
    public const EXAM_RESULTS_PUBLISHED = 'exam_results_published';

    public static function all(): array
    {
        return [
            self::FEE_PAYMENT_RECEIVED => [
                'label' => 'Fee payment received',
                'description' => 'A fee payment has been recorded (amount and receipt number).',
                'icon' => 'fa-money-bill-wave',
                'module' => 'finance',
                'type' => SmasaNotification::TYPE_FEE,
                'audience' => 'staff',
            ],
            self::ATTENDANCE_ABSENTEES => [
                'label' => 'Absentees recorded',
                'description' => 'A teacher saved class attendance and at least one student was marked absent.',
                'icon' => 'fa-user-times',
                'module' => 'attendance',
                'type' => SmasaNotification::TYPE_ATTENDANCE,
                'audience' => 'staff',
            ],
            self::LIBRARY_BOOK_ISSUED => [
                'label' => 'Library book issued',
                'description' => 'A book has been issued to a library member.',
                'icon' => 'fa-book-open',
                'module' => 'library',
                'type' => SmasaNotification::TYPE_LIBRARY,
                'audience' => 'staff',
            ],
            self::LIBRARY_BOOK_RETURNED => [
                'label' => 'Library book returned',
                'description' => 'A borrowed book has been returned (includes any fine charged).',
                'icon' => 'fa-undo',
                'module' => 'library',
                'type' => SmasaNotification::TYPE_LIBRARY,
                'audience' => 'staff',
            ],
            self::EXAM_RESULTS_PUBLISHED => [
                'label' => 'Exam results published',
                'description' => 'Results were released. Goes to the students of the classes that sat the exam.',
                'icon' => 'fa-graduation-cap',
                'module' => 'examinations',
                'type' => SmasaNotification::TYPE_EXAM,
                'audience' => 'students',
            ],
        ];
    }

    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}

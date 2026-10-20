<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown while saving a class's subject list when un-ticking a subject would
 * permanently delete data (marks, assessments, timetable slots, attendance,
 * or students' saved electives) and the admin has not yet confirmed it.
 * The surrounding DB transaction is rolled back, so nothing has changed.
 */
class SubjectRemovalNeedsConfirmation extends RuntimeException
{
    public function __construct(public array $impact)
    {
        parent::__construct(self::describe($impact));
    }

    public static function describe(array $impact): string
    {
        $parts = [];

        $add = function (int $n, string $singular, string $plural) use (&$parts) {
            if ($n > 0) {
                $parts[] = $n . ' ' . ($n === 1 ? $singular : $plural);
            }
        };

        $add($impact['marks'] ?? 0, 'entered mark', 'entered marks');
        $add($impact['assessments'] ?? 0, 'NLSC assessment (with its marks)', 'NLSC assessments (with their marks)');
        $add($impact['timetable'] ?? 0, 'timetable slot', 'timetable slots');
        $add($impact['attendance'] ?? 0, 'subject attendance record', 'subject attendance records');

        $names = implode(', ', $impact['names'] ?? []);
        $text = 'Removing ' . ($names !== '' ? $names : 'this subject') . ' from the class will permanently delete: '
            . ($parts ? implode(', ', $parts) : 'its class record') . '.';

        if (($impact['electives'] ?? 0) > 0) {
            $n = $impact['electives'];
            $text .= ' It will also be taken off the electives of ' . $n . ' student' . ($n === 1 ? '' : 's') . '.';
        }

        return $text . ' This cannot be undone.';
    }
}

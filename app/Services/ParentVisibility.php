<?php

namespace App\Services;

use App\Models\Examination;
use App\Models\Student;
use App\Models\StudentFeeAllocation;
use Illuminate\Support\Facades\DB;

/**
 * Decides what a parent may see in the Parent Portal, as configured by the
 * school (Parent Portal Access). A school that has never opened the settings
 * page behaves exactly as the portal always did.
 */
class ParentVisibility
{
    public const DEFAULT_MESSAGE = 'This is currently unavailable. Please contact the school office.';

    private static array $cache = [];

    public static function settings(int $schoolId): object
    {
        if (!isset(self::$cache[$schoolId])) {
            $row = DB::table('parent_portal_settings')->where('school_id', $schoolId)->first();

            self::$cache[$schoolId] = (object) array_merge([
                'show_results' => 1, 'show_attendance' => 1, 'show_fees' => 1,
                'show_arrival_time' => 1, 'show_signout_time' => 0,
                'fee_hold_mode' => 'none', 'fee_hold_amount' => null, 'fee_hold_scope' => 'all',
                'results_release_mode' => 'auto', 'hold_message' => null,
            ], $row ? (array) $row : []);
        }

        return self::$cache[$schoolId];
    }

    public static function forget(int $schoolId): void
    {
        unset(self::$cache[$schoolId]);
    }

    /** Is a whole section (results / attendance / fees) switched on for parents? */
    public static function sectionVisible($student, string $section): bool
    {
        $s = self::settings((int) $student->school_id);

        return (bool) match ($section) {
            'results' => $s->show_results,
            'attendance' => $s->show_attendance,
            'fees' => $s->show_fees,
            default => true,
        };
    }

    public static function sectionMessage($student, string $section): string
    {
        $label = ['results' => 'Results', 'attendance' => 'Attendance', 'fees' => 'Fees'][$section] ?? 'This section';

        return $label . ' are not available in the parent portal for this school. Please contact the school office.';
    }

    public static function override($student): ?object
    {
        return DB::table('parent_visibility_overrides')
            ->where('school_id', $student->school_id)
            ->where('student_id', $student->id)
            ->first();
    }

    /** Fee balance counted for a hold (null exam = every balance the student owes). */
    public static function owing($student, ?Examination $exam, string $scope): float
    {
        $q = StudentFeeAllocation::where('student_id', $student->id)->where('school_id', $student->school_id);

        if ($scope === 'exam_term' && $exam) {
            $q->where('term', $exam->term)->where('academic_year', $exam->academic_year);
        }

        return max(0, (float) $q->sum('balance'));
    }

    /**
     * Result access for ONE exam that the school has already released.
     *
     * Returns ['visible' => bool, 'allowed' => bool, 'message' => ?string]
     *   visible=false → not published to parents yet: leave it off the list entirely
     *   visible=true, allowed=false → listed but locked, with the school's message
     */
    public static function examAccess(Examination $exam, $student): array
    {
        $schoolId = (int) $student->school_id;
        $s = self::settings($schoolId);
        $ov = self::override($student);

        if (!$s->show_results) {
            return ['visible' => false, 'allowed' => false, 'message' => self::sectionMessage($student, 'results')];
        }

        $message = fn(?string $own = null) => $own ?: ($s->hold_message ?: self::DEFAULT_MESSAGE);

        // The school put this student's results on hold.
        if ($ov && $ov->results_access === 'hold') {
            return ['visible' => true, 'allowed' => false, 'message' => $message($ov->message)];
        }

        $allowed = $ov && $ov->results_access === 'allow';

        // Release mode: school default, optionally overridden per exam.
        $rel = DB::table('parent_exam_releases')
            ->where('school_id', $schoolId)->where('examination_id', $exam->id)->first();
        $mode = $rel->mode ?? $s->results_release_mode;

        if (!$allowed) {
            if ($mode === 'manual' && empty($rel->published_at)) {
                return ['visible' => false, 'allowed' => false, 'message' => null];
            }
            if ($mode === 'scheduled' && (empty($rel->publish_at) || now()->lt($rel->publish_at))) {
                return ['visible' => false, 'allowed' => false, 'message' => null];
            }

            // Fee-balance hold.
            if ($s->fee_hold_mode !== 'none') {
                $owing = self::owing($student, $exam, $s->fee_hold_scope);
                $limit = $s->fee_hold_mode === 'above_amount' ? (float) $s->fee_hold_amount : 0.0;

                if ($owing > $limit) {
                    $owingTxt = number_format($owing);
                    $default = ($s->hold_message ?: 'Results are on hold until the outstanding fee balance is cleared.')
                        . ' Outstanding: ' . $owingTxt . '.';

                    return ['visible' => true, 'allowed' => false, 'message' => $default];
                }
            }
        }

        return ['visible' => true, 'allowed' => true, 'message' => null];
    }
}

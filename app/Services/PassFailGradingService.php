<?php

namespace App\Services;

use App\Models\SchoolPassFailGrading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A-Level General Paper and Subsidiary subjects are not graded A-E: a
 * student who passes gets E, one who fails gets O. The pass mark (default
 * 50%), the letters and remarks are per-school settings.
 *
 * Everything that turns a subject mark into a grade asks scale() for the
 * bands to use: for a General Paper / Subsidiary subject it returns the two
 * pass/fail bands, for every other subject it returns the bands it was given
 * untouched, so nothing else changes.
 */
class PassFailGradingService
{
    public const SCHOOL_ALEVEL_OFFSET = 9_000_000_000;
    public const SCHOOL_OLEVEL_OFFSET = 9_500_000_000;

    private static array $settings = [];
    private static array $subjectMemo = [];

    public static function defaults(): array
    {
        return [
            'is_enabled' => true,
            'pass_mark' => 50.0,
            'pass_grade' => 'E',
            'fail_grade' => 'O',
            'pass_remark' => 'Pass',
            'fail_remark' => 'Fail',
            'pass_points' => 1,
            'fail_points' => 0,
            'apply_general_paper' => true,
            'apply_subsidiary' => true,
        ];
    }

    public static function settings($schoolId): object
    {
        $schoolId = (int) $schoolId;

        if (isset(self::$settings[$schoolId])) {
            return self::$settings[$schoolId];
        }

        $row = Schema::hasTable('school_pass_fail_gradings')
            ? SchoolPassFailGrading::where('school_id', $schoolId)->first()
            : null;

        return self::$settings[$schoolId] = (object) ($row ? $row->only(array_keys(self::defaults())) : self::defaults());
    }

    public static function flush(): void
    {
        self::$settings = [];
        self::$subjectMemo = [];
    }

    /** 'general' / 'subsidiary' for an A-Level pass/fail subject, else null. */
    public static function kindOf($subjectId, $customSubjectId = null): ?string
    {
        $subjectId = (int) $subjectId;

        if ($subjectId <= 0) {
            return null;
        }

        if (array_key_exists($subjectId, self::$subjectMemo)) {
            return self::$subjectMemo[$subjectId];
        }

        if ($subjectId >= self::SCHOOL_OLEVEL_OFFSET) {
            $kind = null;
        } elseif ($subjectId >= self::SCHOOL_ALEVEL_OFFSET) {
            $group = DB::table('school_alevel_subjects')->where('id', $subjectId - self::SCHOOL_ALEVEL_OFFSET)->value('subject_group');
            $kind = $group === 'Subsidiary' ? 'subsidiary' : null;
        } else {
            $group = DB::table('master_datas')->where('md_id', $subjectId)->value('md_misc1');
            $kind = $group === 'General' ? 'general' : ($group === 'Subsidiary' ? 'subsidiary' : null);
        }

        return self::$subjectMemo[$subjectId] = $kind;
    }

    public static function appliesTo($schoolId, $subjectId, $customSubjectId = null): bool
    {
        if (empty($subjectId)) {
            return false;
        }

        $s = self::settings($schoolId);

        if (!$s->is_enabled) {
            return false;
        }

        $kind = self::kindOf($subjectId, $customSubjectId);

        return ($kind === 'general' && $s->apply_general_paper)
            || ($kind === 'subsidiary' && $s->apply_subsidiary);
    }

    /** The two bands, highest first, shaped like grading_scales rows. */
    public static function bands($schoolId): Collection
    {
        $s = self::settings($schoolId);
        $pass = max(0.01, min(100, (float) $s->pass_mark));

        return collect([
            (object) ['grade' => $s->pass_grade, 'min_mark' => $pass, 'max_mark' => 100.0, 'remark' => $s->pass_remark, 'points' => (int) $s->pass_points],
            (object) ['grade' => $s->fail_grade, 'min_mark' => 0.0, 'max_mark' => round($pass - 0.0001, 4), 'remark' => $s->fail_remark, 'points' => (int) $s->fail_points],
        ]);
    }

    /** The bands to grade THIS subject with. */
    public static function scale($schoolId, $bands, $subjectId, $customSubjectId = null)
    {
        return self::appliesTo($schoolId, $subjectId, $customSubjectId) ? self::bands($schoolId) : $bands;
    }

    /** [grade, remark, points] for a percentage on the pass/fail bands. */
    public static function gradeFor($schoolId, float $percentage): array
    {
        $percentage = round($percentage, 4);
        $row = self::bands($schoolId)->first(fn($g) => $percentage >= $g->min_mark && $percentage <= $g->max_mark);

        return [$row->grade, $row->remark, $row->points];
    }
}

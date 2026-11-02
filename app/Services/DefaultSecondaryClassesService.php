<?php

namespace App\Services;

use App\Http\Controllers\ClassandSubjectController;
use App\Http\Controllers\Helper;
use App\Models\School;
use Illuminate\Support\Facades\DB;

/**
 * Starter classes for a Secondary school: Senior 1 - Senior 6, each created
 * exactly as Classes -> Create Class would create it with "This class has no
 * streams" ticked:
 *
 *   Senior 1 - 4  (Secondary O-Level)  the 7 compulsory subjects, ticked:
 *                 English Language, Mathematics, Physics, Chemistry, Biology,
 *                 Geography, History and Political Education.
 *   Senior 5 - 6  (Secondary A-Level)  General Paper only (compulsory for
 *                 every A-Level student). Principal/subsidiary subjects come
 *                 from each student's own combination, as before.
 *
 * Nothing here is special-cased afterwards: the rows are the same ones
 * ClassandSubjectController::storeClass() writes (classrooms, streams with the
 * NO_STREAM sentinel, class_stream_assignments, class_subjects), so the school
 * can tick/untick or add subjects (Edit Class Subjects), turn "No Stream" into
 * named streams (Manage Streams -> Rename Stream) and assign teachers exactly
 * like for a class it created by hand.
 *
 * Plain DB inserts with an explicit school_id (not the Eloquent models) on
 * purpose: the models' school scope forces the school_id of the CURRENT
 * SESSION, and the session here belongs to whoever is enrolling the school,
 * not to the new school.
 *
 * Safe to run more than once: a class the school already has is never touched.
 */
class DefaultSecondaryClassesService
{
    /** Master subject names (lowercase) ticked by default for Senior 1-4. Each entry lists accepted names. */
    private const O_LEVEL_COMPULSORY = [
        // The master list calls it "English" in some databases and "English Language" in others.
        ['english language', 'english'],
        ['mathematics'],
        ['physics'],
        ['chemistry'],
        ['biology'],
        ['geography'],
        // The master list stores this one as plain "History"; accept both so a later rename keeps working.
        ['history and political education', 'history'],
    ];

    /**
     * @param  bool  $onlyIfNoClasses  true: do nothing when the school already has any class (used by the bulk backfill).
     * @return array{created: string[], skipped: string[], missing_subjects: string[], note: ?string}
     */
    public static function seedForSchool(int $schoolId, $addedBy = null, bool $onlyIfNoClasses = false): array
    {
        $result = ['created' => [], 'skipped' => [], 'missing_subjects' => [], 'note' => null];

        $school = School::withoutGlobalScopes()->find($schoolId);

        if (!$school) {
            $result['note'] = 'School not found.';
            return $result;
        }

        // A school on its own custom subject list picks its own subjects.
        if ($school->usesCustomSubjects()) {
            $result['note'] = 'School uses custom subjects; nothing seeded.';
            return $result;
        }

        $classTypes = Helper::schoolClassTypes($schoolId);
        $wantsOLevel = in_array('Secondary O-Level', $classTypes, true);
        $wantsALevel = in_array('Secondary A-Level', $classTypes, true);

        if (!$wantsOLevel && !$wantsALevel) {
            $result['note'] = 'Not a Secondary school; nothing seeded.';
            return $result;
        }

        if ($onlyIfNoClasses && DB::table('classrooms')->where('school_id', $schoolId)->exists()) {
            $result['note'] = 'School already has classes; left alone.';
            return $result;
        }

        $oLevelSubjectIds = [];
        $aLevelSubjectIds = [];

        if ($wantsOLevel) {
            [$oLevelSubjectIds, $result['missing_subjects']] = self::resolveOLevelSubjects();
        }

        if ($wantsALevel) {
            $generalPaper = DB::table('master_datas')
                ->where('md_master_code_id', config('constants.options.SECONDARY_ALEVEL_SUBJECTS'))
                ->where('md_misc1', 'General')
                ->orderBy('md_id')
                ->value('md_id');

            if ($generalPaper) {
                $aLevelSubjectIds = [(int) $generalPaper];
            } else {
                $result['missing_subjects'][] = 'General Paper';
            }
        }

        $plan = [];

        if ($wantsOLevel) {
            foreach (self::classesOf('SECONDARY_OLEVEL_CLASSES') as $class) {
                $plan[] = [$class, 'secondary_olevel', $oLevelSubjectIds];
            }
        }

        if ($wantsALevel) {
            foreach (self::classesOf('SECONDARY_ALEVEL_CLASSES') as $class) {
                $plan[] = [$class, 'secondary_alevel', $aLevelSubjectIds];
            }
        }

        $sentinel = ClassandSubjectController::NO_STREAM_SENTINEL;
        $now = now();
        $stamp = $now->toDateTimeString();

        DB::transaction(function () use ($plan, $schoolId, $addedBy, $sentinel, $now, $stamp, &$result) {
            foreach ($plan as [$class, $subjectType, $subjectIds]) {
                $classId = (string) $class->md_id;

                $alreadyThere = DB::table('classrooms')
                        ->where('school_id', $schoolId)->where('class_name', $classId)->exists()
                    || DB::table('streams')
                        ->where('school_id', $schoolId)->where('class_id', $classId)->exists();

                if ($alreadyThere) {
                    $result['skipped'][] = $class->md_name;
                    continue;
                }

                DB::table('classrooms')->insert([
                    'school_id' => $schoolId,
                    'class_name' => $classId,
                    'added_by' => $addedBy,
                    'date_added' => $stamp,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('streams')->insert([
                    'school_id' => $schoolId,
                    'class_id' => $classId,
                    'stream_id' => $sentinel,
                    'added_by' => $addedBy,
                    'date_added' => $stamp,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('class_stream_assignments')->insert([
                    'school_id' => $schoolId,
                    'class_id' => $class->md_id,
                    'stream_id' => $sentinel,
                    'added_by' => $addedBy,
                    'date_added' => $stamp,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($subjectIds as $subjectId) {
                    DB::table('class_subjects')->insert([
                        'class_id' => $class->md_id,
                        'stream_id' => $sentinel,
                        'subject_id' => $subjectId,
                        'subject_source' => 'master',
                        'subject_type' => $subjectType,
                        'school_id' => $schoolId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $result['created'][] = $class->md_name;
            }
        });

        return $result;
    }

    /** Class master records for one master code, in id order (Senior 1, 2, 3 ...). */
    private static function classesOf(string $option)
    {
        return Helper::MasterRecords(config('constants.options.' . $option))->sortBy('md_id')->values();
    }

    /**
     * Resolves the 7 compulsory names to this database's master_datas ids by
     * NAME (ids can differ between databases).
     *
     * @return array{0: int[], 1: string[]}  [ids, names that could not be found]
     */
    private static function resolveOLevelSubjects(): array
    {
        $byName = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_SUBJECTS'))
            ->mapWithKeys(fn ($m) => [mb_strtolower(trim((string) $m->md_name)) => (int) $m->md_id]);

        $ids = [];
        $missing = [];

        foreach (self::O_LEVEL_COMPULSORY as $acceptedNames) {
            $found = null;

            foreach ($acceptedNames as $name) {
                if ($byName->has($name)) {
                    $found = $byName->get($name);
                    break;
                }
            }

            if ($found === null) {
                $missing[] = ucwords($acceptedNames[0]);
            } else {
                $ids[] = $found;
            }
        }

        return [$ids, $missing];
    }
}

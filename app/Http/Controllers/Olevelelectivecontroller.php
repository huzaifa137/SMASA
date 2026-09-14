<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\SchoolOLevelElective;
use App\Models\StudentOLevelElective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

/**
 * Where each Secondary O-Level (Senior 1-4) student's own electives are
 * picked — up to 2, on top of the class's compulsory subjects (the school
 * picks those separately, at class-creation time), for a maximum of 10
 * subjects total per student. Same split ALevelCombinationController
 * already uses: "which subjects does THIS class offer/require" (class_subjects)
 * and "which subjects does THIS student additionally take" (here) are two
 * different questions, answered in two different places.
 */
class OLevelElectiveController extends Controller
{
    public const ELECTIVE_LIMIT = 2;

    /**
     * Pick a Secondary O-Level class/stream, then build every student in
     * it's own electives in one screen — same shape as the A-Level
     * combinations entry screen.
     */
    public function entry(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $schoolId = Session('LoggedSchool');

        $secondaryOLevelClassIds = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
            ->pluck('md_id')
            ->all();

        $classOptions = DB::table('streams')
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $secondaryOLevelClassIds)
            ->get()
            ->map(function ($row) {
                return (object) [
                    'class_id' => $row->class_id,
                    'stream_id' => $row->stream_id,
                    'class_name' => Helper::recordMdname($row->class_id),
                    'stream_name' => $row->stream_id === \App\Http\Controllers\ClassandSubjectController::NO_STREAM_SENTINEL
                        ? null
                        : $row->stream_id,
                ];
            })
            ->sortBy(fn($o) => $o->class_name . $o->stream_name)
            ->values();

        $selectedClassId = $request->get('class_id', $classOptions->first()->class_id ?? null);
        $selectedStreamId = $request->get('stream_id', $classOptions->first()->stream_id ?? null);

        $students = collect();
        $electives = collect();

        if ($selectedClassId !== null) {
            $students = DB::table('students')
                ->where('school_id', $schoolId)
                ->where('senior', $selectedClassId)
                ->where('stream', $selectedStreamId)
                ->orderBy('firstname')
                ->get();

            $electives = StudentOLevelElective::where('school_id', $schoolId)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
        }

        $subjects = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_SUBJECTS'));
        $electiveSubjects = $subjects->filter(fn($s) => ($s->md_misc1 ?? '') === 'Elective')->values();

        // This school's OWN additions on top of the global list above —
        // reshaped into the exact same {md_id, md_name} shape as the
        // master_datas rows (using each row's synthetic offset id — see
        // SchoolOLevelElective::syntheticId()) so olevel-electives.blade.php's
        // loops render both without any special-casing, merged in AFTER
        // the master rows so a school's own subjects always show last.
        $schoolSubjects = SchoolOLevelElective::forSchool($schoolId)->active()->get()
            ->map(fn($s) => (object) [
                'md_id' => $s->syntheticId(),
                'md_name' => $s->subject_name,
                'is_school_added' => true,
            ]);

        $electiveSubjects = $electiveSubjects->concat($schoolSubjects)->values();

        // This school's own subjects again, but as real Eloquent rows (with
        // a real ->id, not just the synthetic id) — for the "Manage Your
        // Subjects" edit/delete list further down the page.
        $mySchoolSubjects = SchoolOLevelElective::forSchool($schoolId)->orderBy('subject_name')->get();

        return view('Class.olevel-electives', compact(
            'classOptions',
            'selectedClassId',
            'selectedStreamId',
            'students',
            'electives',
            'electiveSubjects',
            'mySchoolSubjects'
        ));
    }

    /**
     * A school adding its own elective, on top of the global master_datas
     * list — visible to this school alone. Returned as JSON (synthetic id +
     * name) so the electives page can drop a new checkbox straight into
     * every student's row without a full page reload.
     */
    public function addSchoolSubject(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $request->validate([
            'subject_name' => 'required|string|max:255',
        ]);

        $schoolId = Session('LoggedSchool');

        $exists = SchoolOLevelElective::forSchool($schoolId)
            ->where('subject_name', $request->subject_name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'You already have an elective with that name.'], 422);
        }

        $subject = SchoolOLevelElective::create([
            'school_id' => $schoolId,
            'subject_name' => $request->subject_name,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'subject' => [
                'id' => $subject->id,
                'md_id' => $subject->syntheticId(),
                'md_name' => $subject->subject_name,
            ],
        ]);
    }

    /**
     * Rename one of this school's own electives. Scoped with forSchool() so
     * a school can never touch another school's row just by guessing an id.
     */
    public function updateSchoolSubject(Request $request, $id)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $request->validate([
            'subject_name' => 'required|string|max:255',
        ]);

        $schoolId = Session('LoggedSchool');

        $subject = SchoolOLevelElective::forSchool($schoolId)->find($id);
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Elective not found.'], 404);
        }

        $duplicate = SchoolOLevelElective::forSchool($schoolId)
            ->where('subject_name', $request->subject_name)
            ->where('id', '!=', $subject->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['success' => false, 'message' => 'You already have an elective with that name.'], 422);
        }

        $subject->update([
            'subject_name' => $request->subject_name,
        ]);

        return response()->json([
            'success' => true,
            'subject' => [
                'id' => $subject->id,
                'md_id' => $subject->syntheticId(),
                'md_name' => $subject->subject_name,
            ],
        ]);
    }

    /**
     * Remove one of this school's own electives — blocked if any student
     * currently has it in their saved electives, same "in use" guard
     * ALevelCombinationController::deleteSchoolSubject() uses, so deleting
     * an elective never silently corrupts an already-saved selection.
     */
    public function deleteSchoolSubject($id)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $schoolId = Session('LoggedSchool');

        $subject = SchoolOLevelElective::forSchool($schoolId)->find($id);
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Elective not found.'], 404);
        }

        $syntheticId = $subject->syntheticId();

        $inUse = StudentOLevelElective::where('school_id', $schoolId)
            ->whereJsonContains('elective_subject_ids', $syntheticId)
            ->exists();

        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'This elective is already part of one or more students\' selections, so it cannot be deleted. Remove it from their electives first.',
            ], 422);
        }

        $subject->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Save a whole grid of electives (all students in the picked
     * class/stream) in one request.
     */
    public function save(Request $request)
    {
        if (!PermissionHelper::canFeature('add_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'electives' => 'required|array',
            'electives.*.student_id' => 'required|integer',
            'electives.*.elective_subject_ids' => 'nullable|array|max:' . self::ELECTIVE_LIMIT,
        ]);

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        DB::beginTransaction();
        try {
            foreach ($request->electives as $entry) {
                $electiveIds = array_values(array_filter((array) ($entry['elective_subject_ids'] ?? [])));

                if (count($electiveIds) > self::ELECTIVE_LIMIT) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "A student can only take up to " . self::ELECTIVE_LIMIT . ' electives.',
                    ], 422);
                }

                if (empty($electiveIds)) {
                    // Nothing chosen for this student — clear any
                    // previously saved electives rather than writing an
                    // empty row.
                    StudentOLevelElective::where('school_id', $schoolId)
                        ->where('student_id', $entry['student_id'])
                        ->delete();
                    continue;
                }

                StudentOLevelElective::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'student_id' => $entry['student_id'],
                    ],
                    [
                        'elective_subject_ids' => $electiveIds,
                        'entered_by' => $teacherId,
                        'entered_at' => now(),
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Electives saved.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }
}
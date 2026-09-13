<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\SchoolALevelSubject;
use App\Models\StudentALevelCombination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

/**
 * Where each Senior 5 / Senior 6 (Secondary A-Level) student's own subject
 * combination is built: their principal subjects, plus at most one
 * subsidiary (Subsidiary Mathematics or Subsidiary ICT). General Paper is
 * compulsory for every A-Level student and is never a choice here — it's
 * simply implied, the same "always there, locked on" treatment it already
 * gets on the class-creation subject picker.
 *
 * This is deliberately separate from class_subjects (what a whole
 * class/stream offers) — a class normally offers several different
 * combinations at once (e.g. PCM and HEG in the same Senior 5 stream), so
 * "which subjects does THIS class offer" and "which subjects does THIS
 * student take" are two different questions, answered in two different
 * places.
 */
class ALevelCombinationController extends Controller
{
    /**
     * Pick a Secondary A-Level class/stream, then build every student in
     * it's own combination in one screen — same shape as the Discipline
     * Ratings / Remarks entry screens (a class/stream picker, then a grid,
     * one row per student).
     */
    public function entry(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $schoolId = Session('LoggedSchool');

        // config('constants.options.SECONDARY_ALEVEL_CLASSES') is the
        // master_code id for the "Secondary A-Level Classes" GROUP (Senior 5
        // and Senior 6 live under it) — not a class id itself. Streams are
        // stored against the actual class ids, so those need resolving
        // first before streams can be looked up.
        $secondaryALevelClassIds = Helper::MasterRecords(config('constants.options.SECONDARY_ALEVEL_CLASSES'))
            ->pluck('md_id')
            ->all();

        $classOptions = DB::table('streams')
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $secondaryALevelClassIds)
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
        $combinations = collect();

        if ($selectedClassId !== null) {
            $students = DB::table('students')
                ->where('school_id', $schoolId)
                ->where('senior', $selectedClassId)
                ->where('stream', $selectedStreamId)
                ->orderBy('firstname')
                ->get();

            $combinations = StudentALevelCombination::where('school_id', $schoolId)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
        }

        $subjects = Helper::MasterRecords(config('constants.options.SECONDARY_ALEVEL_SUBJECTS'));
        $principalSubjects = $subjects->filter(fn($s) => str_starts_with((string) ($s->md_misc1 ?? ''), 'Principal'))
            ->groupBy('md_misc1');
        $subsidiarySubjects = $subjects->filter(fn($s) => ($s->md_misc1 ?? '') === 'Subsidiary')->values();

        // This school's OWN additions on top of the global list above —
        // e.g. a Sciences principal subject not in the standard UACE list.
        // Reshaped into the exact same {md_id, md_name, md_misc1} shape as
        // the master_datas rows (using each row's synthetic offset id —
        // see SchoolALevelSubject::syntheticId()) so alevel-combinations.blade.php's
        // existing loops render both without any special-casing, and merged
        // in AFTER the master rows so a school's own subjects always show
        // last within their group.
        $schoolSubjects = SchoolALevelSubject::forSchool($schoolId)->active()->get()
            ->map(fn($s) => (object) [
                'md_id' => $s->syntheticId(),
                'md_name' => $s->subject_name,
                'md_misc1' => $s->subject_group,
                'is_school_added' => true,
            ]);

        $schoolPrincipals = $schoolSubjects->filter(fn($s) => str_starts_with($s->md_misc1, 'Principal'))->groupBy('md_misc1');
        foreach ($schoolPrincipals as $group => $items) {
            $principalSubjects->put($group, $principalSubjects->get($group, collect())->concat($items));
        }
        $subsidiarySubjects = $subsidiarySubjects->concat(
            $schoolSubjects->filter(fn($s) => $s->md_misc1 === 'Subsidiary')
        )->values();

        // This school's own subjects again, but as real Eloquent rows (with
        // a real ->id, not just the synthetic id) — for the "Manage Your
        // Subjects" edit/delete list further down the page. Deliberately a
        // separate query from $schoolSubjects above rather than reusing it:
        // that one already dropped is_active=false rows and reshaped every
        // row into a plain {md_id, md_name, md_misc1} object to match the
        // master_datas shape, neither of which this list wants.
        $mySchoolSubjects = SchoolALevelSubject::forSchool($schoolId)->orderBy('subject_group')->orderBy('subject_name')->get();

        return view('Class.alevel-combinations', compact(
            'classOptions',
            'selectedClassId',
            'selectedStreamId',
            'students',
            'combinations',
            'principalSubjects',
            'subsidiarySubjects',
            'mySchoolSubjects'
        ));
    }

    /**
     * A school adding its own principal/subsidiary subject, on top of the
     * global master_datas list — visible to this school alone. Returned as
     * JSON (synthetic id + name + group) so the combinations page can drop
     * a new checkbox/option straight into every student's row without a
     * full page reload.
     */
    public function addSchoolSubject(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $request->validate([
            'subject_group' => 'required|in:Principal - Arts,Principal - Sciences,Subsidiary',
            'subject_name' => 'required|string|max:255',
        ]);

        $schoolId = Session('LoggedSchool');

        $exists = SchoolALevelSubject::forSchool($schoolId)
            ->where('subject_group', $request->subject_group)
            ->where('subject_name', $request->subject_name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'You already have a subject with that name in this group.'], 422);
        }

        $subject = SchoolALevelSubject::create([
            'school_id' => $schoolId,
            'subject_name' => $request->subject_name,
            'subject_group' => $request->subject_group,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'subject' => [
                'id' => $subject->id,
                'md_id' => $subject->syntheticId(),
                'md_name' => $subject->subject_name,
                'md_misc1' => $subject->subject_group,
            ],
        ]);
    }

    /**
     * Rename (and/or re-group) one of this school's own subjects.
     * Scoped with forSchool() so a school can never touch another
     * school's row just by guessing an id.
     */
    public function updateSchoolSubject(Request $request, $id)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $request->validate([
            'subject_group' => 'required|in:Principal - Arts,Principal - Sciences,Subsidiary',
            'subject_name' => 'required|string|max:255',
        ]);

        $schoolId = Session('LoggedSchool');

        $subject = SchoolALevelSubject::forSchool($schoolId)->find($id);
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
        }

        $duplicate = SchoolALevelSubject::forSchool($schoolId)
            ->where('subject_group', $request->subject_group)
            ->where('subject_name', $request->subject_name)
            ->where('id', '!=', $subject->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['success' => false, 'message' => 'You already have a subject with that name in this group.'], 422);
        }

        $subject->update([
            'subject_name' => $request->subject_name,
            'subject_group' => $request->subject_group,
        ]);

        return response()->json([
            'success' => true,
            'subject' => [
                'id' => $subject->id,
                'md_id' => $subject->syntheticId(),
                'md_name' => $subject->subject_name,
                'md_misc1' => $subject->subject_group,
            ],
        ]);
    }

    /**
     * Remove one of this school's own subjects — blocked if any student
     * currently has it in their saved combination, same "in use" guard
     * MasterDataController::deleteSecondaryALevelSubject() uses for the
     * global list, so deleting a subject never silently corrupts an
     * already-built combination.
     */
    public function deleteSchoolSubject($id)
    {
        PermissionHelper::denyUnlessFeature('add_class');

        $schoolId = Session('LoggedSchool');

        $subject = SchoolALevelSubject::forSchool($schoolId)->find($id);
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
        }

        $syntheticId = $subject->syntheticId();

        $inUse = StudentALevelCombination::where('school_id', $schoolId)
            ->where(function ($q) use ($syntheticId) {
                $q->whereJsonContains('principal_subject_ids', $syntheticId)
                    ->orWhere('subsidiary_subject_id', $syntheticId);
            })
            ->exists();

        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'This subject is already part of one or more students\' combinations, so it cannot be deleted. Remove it from their combinations first.',
            ], 422);
        }

        $subject->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Save a whole grid of combinations (all students in the picked
     * class/stream) in one request.
     */
    public function save(Request $request)
    {
        if (!PermissionHelper::canFeature('add_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'combinations' => 'required|array',
            'combinations.*.student_id' => 'required|integer',
            'combinations.*.principal_subject_ids' => 'nullable|array|max:3',
            'combinations.*.subsidiary_subject_id' => 'nullable|integer',
        ]);

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        DB::beginTransaction();
        try {
            foreach ($request->combinations as $entry) {
                $principalIds = array_values(array_filter((array) ($entry['principal_subject_ids'] ?? [])));
                $subsidiaryId = $entry['subsidiary_subject_id'] ?? null;

                if (empty($principalIds) && empty($subsidiaryId)) {
                    // Nothing chosen for this student — clear any
                    // previously saved combination rather than writing an
                    // empty one.
                    StudentALevelCombination::where('school_id', $schoolId)
                        ->where('student_id', $entry['student_id'])
                        ->delete();
                    continue;
                }

                StudentALevelCombination::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'student_id' => $entry['student_id'],
                    ],
                    [
                        'principal_subject_ids' => $principalIds,
                        'subsidiary_subject_id' => $subsidiaryId ?: null,
                        'entered_by' => $teacherId,
                        'entered_at' => now(),
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Combinations saved.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }
}

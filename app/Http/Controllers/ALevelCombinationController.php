<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
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

        $classId = (int) config('constants.options.SECONDARY_ALEVEL_CLASSES');

        $classOptions = DB::table('streams')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->get()
            ->map(function ($row) {
                return (object) [
                    'class_id' => $row->class_id,
                    'stream_id' => $row->stream_id,
                    'class_name' => Helper::recordMdname($row->class_id),
                    'stream_name' => $row->stream_id,
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

        return view('Class.alevel-combinations', compact(
            'classOptions',
            'selectedClassId',
            'selectedStreamId',
            'students',
            'combinations',
            'principalSubjects',
            'subsidiarySubjects'
        ));
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
            'combinations.*.principal_subject_ids' => 'nullable|array',
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

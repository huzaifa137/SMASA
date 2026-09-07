<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\ClassSubject;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Uganda's PLE-style Aggregate/Division reporting only ever sums grade
 * points over a fixed handful of "examinable" subjects per class —
 * classically English, Mathematics, Science, Social Studies. Everything
 * else a school teaches (e.g. Religious Education) is graded and shown on
 * the pass slip like normal but must never be added into the aggregate.
 *
 * This page lets a school pick exactly which subjects count, per class +
 * stream (class_subjects.counts_towards_aggregate) — see
 * ExaminationController::buildPassslipData()/buildMultiExamPassslipData()
 * for where that flag is actually consumed, and Gradingscheme::divisionFor()
 * for how the resulting number maps to a Division label.
 *
 * A brand new install gets a sensible default already applied via the
 * 2026_09_06_090002 migration's name-based backfill (English/Mathematics/
 * Science/Social Studies auto-flagged); this page is where a school
 * adjusts that default per class.
 */
class AggregateSubjectsController extends Controller
{
    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $schoolId = Session('LoggedSchool');

        // One entry per class+stream this school actually has, using the
        // exact same (class_id, stream_id) pairing class_subjects itself
        // uses — mirrors resources/views/Class/class-streams.blade.php's
        // links into editClassSubjects()/attachedStreamSubjects().
        $classes = Stream::where('school_id', $schoolId)
            ->get()
            ->map(fn($s) => [
                'class_id' => $s->class_id,
                'stream_id' => $s->stream_id,
                'class_name' => Helper::recordMdname($s->class_id) ?? ('Class #' . $s->class_id),
                'stream_name' => Helper::recordMdname($s->stream_id) ?? ('Stream #' . $s->stream_id),
            ])
            ->unique(fn($c) => $c['class_id'] . '-' . $c['stream_id'])
            ->sortBy(fn($c) => $c['class_name'] . ' ' . $c['stream_name'])
            ->values();

        return view('Examination.aggregate-subjects.index', compact('classes', 'schoolId'));
    }

    /**
     * The subjects assigned to one class+stream, with their current
     * counts_towards_aggregate flag — loaded into the panel via AJAX when
     * a class is selected on the index page.
     */
    public function subjects($classId, $streamId)
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $schoolId = Session('LoggedSchool');

        $subjects = ClassSubject::where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->where('school_id', $schoolId)
            ->get()
            ->map(fn($cs) => [
                'id' => $cs->id,
                'name' => $cs->display_name,
                'counts_towards_aggregate' => (bool) $cs->counts_towards_aggregate,
            ])
            ->sortBy('name')
            ->values();

        return response()->json([
            'success' => true,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Save which of this class+stream's subjects count toward the
     * aggregate. Anything not in subject_ids is explicitly turned off —
     * this always sets the complete state for the class rather than only
     * ever adding, so unchecking a subject in the UI actually takes
     * effect.
     */
    public function update(Request $request, $classId, $streamId)
    {
        PermissionHelper::denyUnlessFeature('edit_class');

        $schoolId = Session('LoggedSchool');

        $validated = $request->validate([
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'integer',
        ]);
        $ids = $validated['subject_ids'] ?? [];

        DB::transaction(function () use ($classId, $streamId, $schoolId, $ids) {
            $base = ClassSubject::where('class_id', $classId)
                ->where('stream_id', $streamId)
                ->where('school_id', $schoolId);

            (clone $base)->update(['counts_towards_aggregate' => false]);

            if (!empty($ids)) {
                (clone $base)->whereIn('id', $ids)->update(['counts_towards_aggregate' => true]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Aggregate subjects updated for this class.',
        ]);
    }
}

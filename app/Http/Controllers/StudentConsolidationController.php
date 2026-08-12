<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Student;
use App\Services\StudentConsolidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentConsolidationController extends Controller
{
    protected StudentConsolidationService $service;

    public function __construct(StudentConsolidationService $service)
    {
        $this->service = $service;
    }

    /**
     * Main "Consolidate Students" page: stats + suggested duplicate
     * groups + a manual search-and-link tool + a list of already
     * consolidated students.
     *
     * Query params (all optional, all via GET so the view stays bookmarkable):
     *   class  - filter Suggested Matches to a "senior" (class) value
     *   stream - filter Suggested Matches to a stream value
     *   q      - search box for the Consolidated Students list (name / admission number)
     */
    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_students');

        $schoolId = Helper::requireSchool();

        $classFilter = $request->get('class') ?: null;
        $streamFilter = $request->get('stream') ?: null;
        $consolidatedSearch = trim((string) $request->get('q'));

        $stats = $this->service->stats($schoolId);
        $suggestions = $this->service->findSuggestions($schoolId, $classFilter, $streamFilter);
        $classStreamOptions = $this->service->classStreamOptions($schoolId);

        $consolidatedQuery = Student::where('school_id', $schoolId)
            ->whereNull('linked_student_id')
            ->has('linkedRecords')
            ->with('linkedRecords');

        if ($consolidatedSearch !== '') {
            $consolidatedQuery->where(function ($q) use ($consolidatedSearch) {
                $q->where('firstname', 'like', "%{$consolidatedSearch}%")
                    ->orWhere('lastname', 'like', "%{$consolidatedSearch}%")
                    ->orWhere('admission_number', 'like', "%{$consolidatedSearch}%")
                    ->orWhere('registration_number', 'like', "%{$consolidatedSearch}%")
                    ->orWhereHas('linkedRecords', function ($lq) use ($consolidatedSearch) {
                        $lq->where('admission_number', 'like', "%{$consolidatedSearch}%")
                            ->orWhere('registration_number', 'like', "%{$consolidatedSearch}%");
                    });
            });
        }

        $consolidated = $consolidatedQuery
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'consolidated_page')
            ->withQueryString();

        return view('student.consolidate-students', compact(
            'stats', 'suggestions', 'consolidated', 'classStreamOptions',
            'classFilter', 'streamFilter', 'consolidatedSearch'
        ));
    }

    /**
     * Ajax search used both on the Consolidation page's manual-link tool
     * and on the Add Student form ("link to an existing student").
     */
    public function search(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_students');

        $schoolId = Helper::requireSchool();
        $term = trim((string) $request->get('term'));

        $query = Student::where('school_id', $schoolId);

        if ($request->filled('exclude')) {
            $query->where('id', '!=', $request->get('exclude'));
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('firstname', 'like', "%{$term}%")
                    ->orWhere('lastname', 'like', "%{$term}%")
                    ->orWhere('admission_number', 'like', "%{$term}%")
                    ->orWhere('registration_number', 'like', "%{$term}%");
            });
        }

        $results = $query->orderBy('firstname')
            ->limit(15)
            ->get(['id', 'firstname', 'lastname', 'senior', 'stream', 'gender', 'date_of_birth', 'admission_number', 'linked_student_id'])
            ->map(function (Student $s) {
                return [
                    'id' => $s->id,
                    'firstname' => $s->firstname,
                    'lastname' => $s->lastname,
                    'name' => trim($s->firstname . ' ' . $s->lastname),
                    'class' => Helper::recordMdname($s->senior) ?: $s->senior,
                    'stream' => $s->stream,
                    'gender' => $s->gender,
                    'admission_number' => $s->admission_number,
                    'already_linked' => !is_null($s->linked_student_id),
                ];
            });

        return response()->json(['status' => true, 'results' => $results]);
    }

    /**
     * Link two (or more, called repeatedly) enrollment rows together as
     * the same physical student.
     *
     * Rules:
     *  - Both rows must belong to the current school.
     *  - The chosen "primary" row must not itself be linked to another record.
     *  - The "duplicate" row being folded in must not already have its own
     *    linked children (that would create a chain — instead its children
     *    are re-pointed to the new primary automatically).
     */
    public function link(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_student');

        $request->validate([
            'primary_student_id' => 'required|integer|different:duplicate_student_id',
            'duplicate_student_id' => 'required|integer',
        ]);

        $schoolId = Helper::requireSchool();

        $primary = Student::where('school_id', $schoolId)->findOrFail($request->primary_student_id);
        $duplicate = Student::where('school_id', $schoolId)->findOrFail($request->duplicate_student_id);

        if ($primary->linked_student_id) {
            return response()->json([
                'status' => false,
                'message' => 'The selected primary record is itself linked to another student. Pick its primary record instead.',
            ], 422);
        }

        DB::transaction(function () use ($primary, $duplicate) {
            // Re-point any of the duplicate's own children to the new primary.
            Student::where('linked_student_id', $duplicate->id)->update(['linked_student_id' => $primary->id]);

            $duplicate->linked_student_id = $primary->id;
            $duplicate->save();
        });

        return response()->json([
            'status' => true,
            'message' => 'Students linked as one. School totals will now count them once.',
        ]);
    }

    /**
     * Delete exact duplicate enrollment row(s) — same child, same class,
     * same stream — keeping exactly one. Refuses to delete any row that
     * already has academic/financial history attached, since that would
     * silently strand marks, attendance, or fee records.
     */
    public function deleteDuplicates(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_student');

        $request->validate([
            'keep_student_id' => 'required|integer',
            'delete_student_ids' => 'required|array|min:1',
            'delete_student_ids.*' => 'integer|different:keep_student_id',
        ]);

        $schoolId = Helper::requireSchool();

        $keep = Student::where('school_id', $schoolId)->findOrFail($request->keep_student_id);
        $toDelete = Student::where('school_id', $schoolId)
            ->whereIn('id', $request->delete_student_ids)
            ->get();

        if ($toDelete->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Nothing to delete.'], 422);
        }

        // Refuse to delete anything that already has real data attached —
        // this endpoint is only for genuinely empty accidental duplicates.
        foreach ($toDelete as $dup) {
            if ($this->service->hasRelatedRecords($dup)) {
                return response()->json([
                    'status' => false,
                    'message' => "{$dup->firstname} {$dup->lastname}'s duplicate record already has exam, attendance or fee data attached, so it can't be safely deleted. Use \"Link\" instead, or clear that data first.",
                ], 422);
            }
        }

        DB::transaction(function () use ($keep, $toDelete) {
            $ids = $toDelete->pluck('id');

            // If any duplicate being removed was itself a primary with linked
            // programs, re-point those children to the record we're keeping.
            Student::whereIn('linked_student_id', $ids)->update(['linked_student_id' => $keep->id]);

            DB::table('student_match_dismissals')
                ->where(function ($q) use ($ids) {
                    $q->whereIn('student_id_one', $ids)->orWhereIn('student_id_two', $ids);
                })->delete();

            Student::whereIn('id', $ids)->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Duplicate record(s) removed. ' . $keep->firstname . ' ' . $keep->lastname . ' is now the only record for that class.',
            'deleted_count' => $toDelete->count(),
        ]);
    }

    /**
     * Undo a consolidation — the record goes back to being counted as its
     * own separate student.
     */
    public function unlink(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_student');

        $request->validate(['student_id' => 'required|integer']);

        $schoolId = Helper::requireSchool();
        $student = Student::where('school_id', $schoolId)->findOrFail($request->student_id);
        $student->linked_student_id = null;
        $student->save();

        return response()->json(['status' => true, 'message' => 'Records unlinked.']);
    }

    /**
     * Mark a suggested group as "not the same student" so it stops
     * appearing in future suggestions. Accepts either the classic
     * {student_id_one, student_id_two} pair, or {student_ids: [...]}" for
     * groups with more than two members — every pairwise combination
     * within the group is recorded as dismissed.
     */
    public function dismiss(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_student');

        $ids = $request->get('student_ids');
        if (!is_array($ids) || count($ids) < 2) {
            $request->validate([
                'student_id_one' => 'required|integer',
                'student_id_two' => 'required|integer|different:student_id_one',
            ]);
            $ids = [$request->student_id_one, $request->student_id_two];
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (count($ids) < 2) {
            return response()->json(['status' => false, 'message' => 'Pick at least two records to dismiss.'], 422);
        }

        $schoolId = Helper::requireSchool();
        $dismissedBy = session('LoggedTeacher') ?? session('LoggedAdmin');

        for ($i = 0; $i < count($ids); $i++) {
            for ($j = $i + 1; $j < count($ids); $j++) {
                [$a, $b] = [min($ids[$i], $ids[$j]), max($ids[$i], $ids[$j])];
                DB::table('student_match_dismissals')->updateOrInsert(
                    ['student_id_one' => $a, 'student_id_two' => $b],
                    ['school_id' => $schoolId, 'dismissed_by' => $dismissedBy, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        return response()->json(['status' => true, 'message' => 'Got it — these will no longer be suggested as a match.']);
    }
}
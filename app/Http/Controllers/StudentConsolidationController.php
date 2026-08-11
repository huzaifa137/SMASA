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
     */
    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_students');

        $schoolId = Helper::requireSchool();

        $stats = $this->service->stats($schoolId);
        $suggestions = $this->service->findSuggestions($schoolId);

        $consolidated = Student::where('school_id', $schoolId)
            ->whereNull('linked_student_id')
            ->has('linkedRecords')
            ->with('linkedRecords')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'consolidated_page');

        return view('student.consolidate-students', compact('stats', 'suggestions', 'consolidated'));
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
     * Mark a suggested pair as "not the same student" so it stops
     * appearing in future suggestions.
     */
    public function dismiss(Request $request)
    {
        PermissionHelper::denyUnlessFeature('add_student');

        $request->validate([
            'student_id_one' => 'required|integer',
            'student_id_two' => 'required|integer|different:student_id_one',
        ]);

        $schoolId = Helper::requireSchool();
        [$a, $b] = [min($request->student_id_one, $request->student_id_two), max($request->student_id_one, $request->student_id_two)];

        DB::table('student_match_dismissals')->updateOrInsert(
            ['student_id_one' => $a, 'student_id_two' => $b],
            [
                'school_id' => $schoolId,
                'dismissed_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['status' => true, 'message' => 'Got it — these will no longer be suggested as a match.']);
    }
}

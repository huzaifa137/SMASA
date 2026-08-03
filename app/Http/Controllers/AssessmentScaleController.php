<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\AssessmentScale;
use App\Models\AssessmentScalePreset;
use App\Models\ClassSubject;
use App\Models\GradingScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Lets each school define its own comment/mark scales — a name, the score
 * range it covers (e.g. 1-3, or 80-100), a set of "system comment" presets
 * (score/label/remark), whether teachers may type a score outside that
 * range, and an optional link to one of the school's Grading Schemes so a
 * letter grade can be shown alongside the comment.
 *
 * A scale is applied to marks entry by attaching it to a specific
 * class + stream + subject via class_subjects.assessment_scale_id (see
 * assignToClassSubject() below and Class.attached-stream-subjects.blade.php).
 * This is the generalised, school-configurable replacement for the old
 * hardcoded Nursery-only config('constants.early_years') scale.
 */
class AssessmentScaleController extends Controller
{
    // ─── List / manage page ────────────────────────────────────────────────

    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $schoolId = Session('LoggedSchool');

        $scales = AssessmentScale::forSchool($schoolId)
            ->withCount('classSubjects')
            ->with(['presets', 'gradingScheme'])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $gradingSchemes = GradingScheme::forSchool($schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'total_marks']);

        return view('Examination.assessment-scales.index', compact('scales', 'schoolId', 'gradingSchemes'));
    }

    // ─── Create ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('create_exam')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');

        $validated = $this->validatePayload($request, $schoolId);

        DB::beginTransaction();
        try {
            $scale = AssessmentScale::create([
                'school_id' => $schoolId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'min_score' => $validated['min_score'],
                'max_score' => $validated['max_score'],
                'allow_custom_score' => (bool) ($validated['allow_custom_score'] ?? false),
                'grade_mode' => $validated['grade_mode'],
                'grading_scheme_id' => $validated['grade_mode'] === 'linked_grading_scheme'
                    ? $validated['grading_scheme_id']
                    : null,
                'is_default' => (bool) ($validated['is_default'] ?? false),
                'is_active' => true,
                'created_by' => Session('LoggedTeacher'),
            ]);

            $this->syncPresets($scale, $validated['presets']);

            if ($scale->is_default) {
                $this->clearOtherDefaults($schoolId, $scale->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assessment scale created successfully.',
                'scale' => $scale->load('presets'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Update ─────────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $scale = AssessmentScale::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        $validated = $this->validatePayload($request, $schoolId, $scale->id);

        DB::beginTransaction();
        try {
            $scale->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'min_score' => $validated['min_score'],
                'max_score' => $validated['max_score'],
                'allow_custom_score' => (bool) ($validated['allow_custom_score'] ?? false),
                'grade_mode' => $validated['grade_mode'],
                'grading_scheme_id' => $validated['grade_mode'] === 'linked_grading_scheme'
                    ? $validated['grading_scheme_id']
                    : null,
                'is_default' => (bool) ($validated['is_default'] ?? false),
            ]);

            $scale->presets()->delete();
            $this->syncPresets($scale, $validated['presets']);

            if ($scale->is_default) {
                $this->clearOtherDefaults($schoolId, $scale->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assessment scale updated successfully.',
                'scale' => $scale->fresh('presets'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Toggle active / delete ───────────────────────────────────────────

    public function toggleActive(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $scale = AssessmentScale::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        $scale->is_active = (bool) $request->boolean('is_active');
        $scale->save();

        return response()->json(['success' => true, 'message' => 'Scale updated.']);
    }

    public function destroy($id)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $scale = AssessmentScale::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        if ($scale->classSubjects()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This scale is attached to one or more class subjects. Detach it first or deactivate it instead of deleting.',
            ], 422);
        }

        $scale->delete(); // presets cascade-delete via FK

        return response()->json(['success' => true, 'message' => 'Assessment scale deleted.']);
    }

    // ─── Class-subject assignment ─────────────────────────────────────────

    /**
     * Subjects currently attached to a class+stream, with their assigned
     * teacher and assessment scale — used to populate the "Assessment
     * Type" column on Class.attached-stream-subjects.
     */
    public function classSubjects($classId, $streamId)
    {
        PermissionHelper::denyUnlessFeature('view_classes');

        $schoolId = Session('LoggedSchool');

        $rows = ClassSubject::with('assessmentScale')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', (string) $streamId)
            ->get()
            ->map(fn($row) => [
                'id' => $row->id,
                'subject_name' => $row->display_name,
                'assessment_scale_id' => $row->assessment_scale_id,
            ]);

        return response()->json(['success' => true, 'classSubjects' => $rows]);
    }

    /**
     * Attach (or detach, when scale_id is null) an assessment scale to a
     * single class_subjects row — i.e. one subject, in one class+stream.
     */
    public function assignToClassSubject(Request $request)
    {
        if (!PermissionHelper::canFeature('edit_class')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');

        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subjects,id',
            'assessment_scale_id' => 'nullable|exists:assessment_scales,id',
        ]);

        $classSubject = ClassSubject::where('id', $validated['class_subject_id'])
            ->where('school_id', $schoolId)
            ->firstOrFail();

        if (!empty($validated['assessment_scale_id'])) {
            // Guard against attaching another school's scale.
            AssessmentScale::where('id', $validated['assessment_scale_id'])
                ->where('school_id', $schoolId)
                ->firstOrFail();
        }

        $classSubject->assessment_scale_id = $validated['assessment_scale_id'] ?: null;
        $classSubject->save();

        return response()->json([
            'success' => true,
            'message' => $classSubject->assessment_scale_id
                ? 'Subject now uses the selected assessment scale.'
                : 'Subject switched back to numeric marks.',
        ]);
    }

    /**
     * Dedicated "Assign to Classes & Subjects" screen for one scale —
     * every class > stream > subject in the school, with a checkbox per
     * subject, so a school can attach a scale to as many (or as few)
     * subjects as it likes in one save. Mirrors the class.assign-teachers
     * screen's data shape (Classroom -> Streams -> ClassSubjects).
     */
    public function assignPage($id)
    {
        PermissionHelper::denyUnlessFeature('edit_class');

        $schoolId = Session('LoggedSchool');
        $scale = AssessmentScale::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        $classRecord = \App\Models\Classroom::where('school_id', $schoolId)->orderBy('class_name', 'Asc')->get();

        $classesData = $classRecord->map(function ($classroom) use ($schoolId) {
            $streams = \App\Models\Stream::where('class_id', $classroom->class_name)
                ->where('school_id', $schoolId)
                ->orderBy('stream_id', 'Asc')
                ->get()
                ->map(function ($stream) use ($schoolId) {
                    $stream->subjects = ClassSubject::with('assessmentScale')
                        ->where('class_id', $stream->class_id)
                        ->where('stream_id', $stream->stream_id)
                        ->where('school_id', $schoolId)
                        ->get()
                        ->sortBy('display_name')
                        ->values();

                    return $stream;
                });

            return [
                'classroom' => $classroom,
                'streams' => $streams,
            ];
        })->filter(fn($c) => $c['streams']->sum(fn($s) => $s->subjects->count()) > 0)->values();

        return view('Examination.assessment-scales.assign', compact('scale', 'classesData'));
    }

    /**
     * Saves the checkbox selections from the assign page in one go: every
     * subject checked gets this scale attached (even if it currently
     * belongs to a different one); every subject that was on this scale
     * but got unchecked reverts to numeric marks. Subjects never shown /
     * touched on the page are left completely alone.
     */
    public function assignBulk(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_class')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $scale = AssessmentScale::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        $validated = $request->validate([
            'all_class_subject_ids' => 'required|array',
            'all_class_subject_ids.*' => 'integer',
            'checked_class_subject_ids' => 'nullable|array',
            'checked_class_subject_ids.*' => 'integer',
        ]);

        $allIds = collect($validated['all_class_subject_ids'])->unique()->values();
        $checkedIds = collect($validated['checked_class_subject_ids'] ?? [])->unique()->values();

        // Ownership guard: only touch rows that actually belong to this
        // school, regardless of what ids were posted.
        $ownedIds = ClassSubject::where('school_id', $schoolId)
            ->whereIn('id', $allIds)
            ->pluck('id');

        $toAttach = $checkedIds->intersect($ownedIds);
        $toDetach = $ownedIds->diff($checkedIds);

        DB::beginTransaction();
        try {
            if ($toAttach->isNotEmpty()) {
                ClassSubject::whereIn('id', $toAttach)->update(['assessment_scale_id' => $scale->id]);
            }

            if ($toDetach->isNotEmpty()) {
                // Only clear rows that were actually on THIS scale — don't
                // touch subjects that were left unchecked because they
                // belong to a different scale entirely.
                ClassSubject::whereIn('id', $toDetach)
                    ->where('assessment_scale_id', $scale->id)
                    ->update(['assessment_scale_id' => null]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Saved: {$toAttach->count()} subject(s) now use \"{$scale->name}\", " . $toDetach->count() . ' reverted to numeric marks.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function validatePayload(Request $request, $schoolId, $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:assessment_scales,name,' . ($ignoreId ?? 'NULL') . ',id,school_id,' . $schoolId,
            'description' => 'nullable|string',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'allow_custom_score' => 'nullable|boolean',
            'grade_mode' => 'required|in:none,linked_grading_scheme',
            'grading_scheme_id' => 'nullable|required_if:grade_mode,linked_grading_scheme|exists:grading_schemes,id',
            'is_default' => 'nullable|boolean',
            'presets' => 'required|array|min:1',
            // A preset is either a single value (min_score only, e.g. "3")
            // or a band (min_score + max_score, e.g. "1-39") — one row can
            // now cover a whole range of scores instead of needing a row
            // per exact value.
            'presets.*.min_score' => 'required|numeric',
            'presets.*.max_score' => 'nullable|numeric',
            'presets.*.label' => 'required|string|max:255',
            'presets.*.remark' => 'nullable|string|max:100',
        ]);

        if (!empty($validated['grading_scheme_id'])) {
            GradingScheme::where('id', $validated['grading_scheme_id'])
                ->where('school_id', $schoolId)
                ->firstOrFail();
        }

        // A linked grading scheme converts score/max_score into a fixed
        // 0-100% band lookup — that only means anything if the score has
        // a real ceiling. Combining it with "allow custom (unbounded)
        // scores" would silently produce nonsense grades (or none at
        // all) whenever a teacher typed something beyond max_score, so
        // the two are mutually exclusive by design, not just by UI hint.
        if (($validated['allow_custom_score'] ?? false) && $validated['grade_mode'] === 'linked_grading_scheme') {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                'success' => false,
                'message' => "Custom scores and a linked grading scheme can't be combined — a score with no upper bound can't be converted to a meaningful percentage. Turn off \"Allow Custom Scores\" to link a grading scheme, or set Letter Grade back to \"None\".",
            ], 422));
        }

        // Normalise each preset to an explicit min/max band (a blank
        // max_score just means "same as min_score" — a single value).
        $bands = [];
        foreach ($validated['presets'] as $i => $preset) {
            $min = (float) $preset['min_score'];
            $max = isset($preset['max_score']) && $preset['max_score'] !== '' && $preset['max_score'] !== null
                ? (float) $preset['max_score']
                : $min;

            if ($max < $min) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                    'success' => false,
                    'message' => "Row " . ($i + 1) . ": the upper end of the range ({$max}) can't be less than the lower end ({$min}).",
                ], 422));
            }

            $bands[] = ['row' => $i + 1, 'min' => $min, 'max' => $max];
        }

        // Presets are meant to sit inside the scale's own declared range.
        // When custom scores are allowed, min/max describe the *typical*
        // range shown to teachers rather than a hard boundary, so bands
        // are free to sit outside it too (e.g. a scale marked 1-3 that
        // also keeps a "0 = Absent" preset on hand).
        if (!($validated['allow_custom_score'] ?? false)) {
            foreach ($bands as $band) {
                if ($band['min'] < $validated['min_score'] || $band['max'] > $validated['max_score']) {
                    throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                        'success' => false,
                        'message' => "Row {$band['row']}'s range ({$band['min']}-{$band['max']}) falls outside the scale's {$validated['min_score']}-{$validated['max_score']} range.",
                    ], 422));
                }
            }
        }

        // Two bands covering the same score would make the auto-fill
        // ambiguous (which comment wins?), so overlapping ranges are
        // rejected up front rather than silently picking one at random
        // later during marks entry.
        usort($bands, fn($a, $b) => $a['min'] <=> $b['min']);
        for ($i = 1; $i < count($bands); $i++) {
            if ($bands[$i]['min'] <= $bands[$i - 1]['max']) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                    'success' => false,
                    'message' => "Row {$bands[$i - 1]['row']} ({$bands[$i - 1]['min']}-{$bands[$i - 1]['max']}) and Row {$bands[$i]['row']} ({$bands[$i]['min']}-{$bands[$i]['max']}) overlap. Each range needs to be its own, non-overlapping band.",
                ], 422));
            }
        }

        return $validated;
    }

    private function syncPresets(AssessmentScale $scale, array $presets): void
    {
        foreach ($presets as $i => $preset) {
            $min = (float) $preset['min_score'];
            $max = isset($preset['max_score']) && $preset['max_score'] !== '' && $preset['max_score'] !== null
                ? (float) $preset['max_score']
                : $min;

            AssessmentScalePreset::create([
                'assessment_scale_id' => $scale->id,
                'score' => $min, // kept for any legacy reader; min/max below are authoritative
                'min_score' => $min,
                'max_score' => $max,
                'label' => $preset['label'],
                'remark' => $preset['remark'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }

    private function clearOtherDefaults($schoolId, $exceptId): void
    {
        AssessmentScale::where('school_id', $schoolId)
            ->where('id', '!=', $exceptId)
            ->update(['is_default' => false]);
    }

}
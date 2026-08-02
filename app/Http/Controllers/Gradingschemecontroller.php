<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\GradingScheme;
use App\Models\GradingScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Lets each school define its own grading scheme(s): a name, the scale the
 * exam is set out of (total marks + pass mark), and the grade bands
 * (percentage ranges) used to translate a mark into a letter grade.
 *
 * Grading schemes are per-school — there is no global/system scheme. Every
 * school starts with its own editable starter set (seeded automatically
 * when the school is created, see App\Services\GradingSchemeDefaults) and
 * can freely create, edit, activate/deactivate, or delete any of its own
 * schemes from there.
 */
class GradingSchemeController extends Controller
{
    // ─── List / manage page ────────────────────────────────────────────────

    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $schoolId = Session('LoggedSchool');

        $schemes = GradingScheme::forSchool($schoolId)
            ->withCount('examinations')
            ->with('bands')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('Examination.grading-schemes.index', compact('schemes', 'schoolId'));
    }

    // ─── Create ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('create_exam')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:grading_schemes,name,NULL,id,school_id,' . $schoolId,
            'description' => 'nullable|string',
            'total_marks' => 'required|integer|min:1|max:1000',
            'pass_mark' => 'required|integer|min:1',
            'is_default' => 'nullable|boolean',
            'bands' => 'required|array|min:1',
            'bands.*.grade' => 'required|string|max:20',
            'bands.*.min_mark' => 'required|numeric|min:0|max:100',
            'bands.*.max_mark' => 'required|numeric|min:0|max:100',
            'bands.*.remark' => 'nullable|string|max:100',
            'bands.*.points' => 'nullable|numeric|min:0',
        ]);

        if ($validated['pass_mark'] > $validated['total_marks']) {
            return response()->json(['success' => false, 'message' => 'Pass mark cannot exceed total marks.'], 422);
        }

        DB::beginTransaction();
        try {
            $scheme = GradingScheme::create([
                'school_id' => $schoolId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'total_marks' => $validated['total_marks'],
                'pass_mark' => $validated['pass_mark'],
                'is_default' => (bool) ($validated['is_default'] ?? false),
                'is_active' => true,
                'created_by' => Session('LoggedTeacher'),
            ]);

            $this->syncBands($scheme, $validated['bands']);

            $problems = $scheme->validateBands();
            if ($problems) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => implode(' ', $problems)], 422);
            }

            if ($scheme->is_default) {
                $this->clearOtherDefaults($schoolId, $scheme->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Grading scheme created successfully.',
                'scheme' => $scheme->load('bands'),
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

        // Schools may only edit their OWN schemes.
        $scheme = GradingScheme::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:grading_schemes,name,' . $scheme->id . ',id,school_id,' . $schoolId,
            'description' => 'nullable|string',
            'total_marks' => 'required|integer|min:1|max:1000',
            'pass_mark' => 'required|integer|min:1',
            'is_default' => 'nullable|boolean',
            'bands' => 'required|array|min:1',
            'bands.*.grade' => 'required|string|max:20',
            'bands.*.min_mark' => 'required|numeric|min:0|max:100',
            'bands.*.max_mark' => 'required|numeric|min:0|max:100',
            'bands.*.remark' => 'nullable|string|max:100',
            'bands.*.points' => 'nullable|numeric|min:0',
        ]);

        if ($validated['pass_mark'] > $validated['total_marks']) {
            return response()->json(['success' => false, 'message' => 'Pass mark cannot exceed total marks.'], 422);
        }

        DB::beginTransaction();
        try {
            $scheme->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'total_marks' => $validated['total_marks'],
                'pass_mark' => $validated['pass_mark'],
                'is_default' => (bool) ($validated['is_default'] ?? false),
            ]);

            $scheme->bands()->delete();
            $this->syncBands($scheme, $validated['bands']);

            $problems = $scheme->validateBands();
            if ($problems) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => implode(' ', $problems)], 422);
            }

            if ($scheme->is_default) {
                $this->clearOtherDefaults($schoolId, $scheme->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Grading scheme updated successfully.',
                'scheme' => $scheme->fresh('bands'),
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
        $scheme = GradingScheme::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        $scheme->is_active = (bool) $request->boolean('is_active');
        $scheme->save();

        return response()->json(['success' => true, 'message' => 'Scheme updated.']);
    }

    public function destroy($id)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $scheme = GradingScheme::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        if ($scheme->examinations()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This scheme is already used by one or more examinations. Deactivate it instead of deleting.',
            ], 422);
        }

        $scheme->delete(); // bands cascade-delete via FK

        return response()->json(['success' => true, 'message' => 'Grading scheme deleted.']);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function syncBands(GradingScheme $scheme, array $bands): void
    {
        foreach ($bands as $i => $band) {
            GradingScale::create([
                'grading_scheme_id' => $scheme->id,
                'grade' => $band['grade'],
                'min_mark' => $band['min_mark'],
                'max_mark' => $band['max_mark'],
                'remark' => $band['remark'] ?? null,
                'points' => $band['points'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }

    private function clearOtherDefaults($schoolId, $exceptId): void
    {
        GradingScheme::where('school_id', $schoolId)
            ->where('id', '!=', $exceptId)
            ->update(['is_default' => false]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Examination;
use App\Models\Student;
use App\Services\ParentVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Students → Parent Portal Access.
 *
 * The school decides what parents can see (results / attendance / fees, gate
 * times), how results are released to parents (automatic, manual, scheduled),
 * whether an unpaid fee balance holds results back, and can allow or hold
 * individual students with a message shown to the parent.
 */
class ParentPortalSettingsController extends Controller
{
    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('manage_parent_portal');

        $schoolId = $this->schoolId();
        $settings = ParentVisibility::settings($schoolId);

        $examQ = trim((string) $request->query('exam_q', ''));

        // Paginated so the page stays short for schools with many exams.
        $exams = Examination::withReportCards()
            ->where('school_id', $schoolId)
            ->when($examQ !== '', fn($q) => $q->where(function ($w) use ($examQ) {
                $w->where('exam_name', 'like', "%$examQ%")->orWhere('exam_code', 'like', "%$examQ%");
            }))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(8, ['id', 'exam_name', 'exam_code', 'term', 'academic_year', 'status', 'o_level_mode', 'start_date'], 'exams_page')
            ->withQueryString()
            ->fragment('release-by-exam');

        $releases = DB::table('parent_exam_releases')
            ->where('school_id', $schoolId)
            ->whereIn('examination_id', $exams->pluck('id'))
            ->get()
            ->keyBy('examination_id');

        $overrides = DB::table('parent_visibility_overrides as o')
            ->join('students as s', 's.id', '=', 'o.student_id')
            ->where('o.school_id', $schoolId)
            ->orderByDesc('o.updated_at')
            ->get(['o.id', 'o.results_access', 'o.message', 's.firstname', 's.lastname', 's.admission_number']);

        $q = trim((string) $request->query('q', ''));
        $found = collect();
        if (strlen($q) >= 2) {
            $found = Student::where('school_id', $schoolId)
                ->where(function ($w) use ($q) {
                    $w->where('firstname', 'like', "%$q%")
                        ->orWhere('lastname', 'like', "%$q%")
                        ->orWhere('admission_number', 'like', "%$q%");
                })
                ->orderBy('firstname')
                ->limit(15)
                ->get(['id', 'firstname', 'lastname', 'admission_number']);
        }

        return view('parent_portal.settings', compact('settings', 'exams', 'releases', 'overrides', 'found', 'q', 'examQ'));
    }

    public function update(Request $request)
    {
        PermissionHelper::denyUnlessFeature('manage_parent_portal');
        $schoolId = $this->schoolId();

        $data = $request->validate([
            'fee_hold_mode' => 'required|in:none,any_balance,above_amount',
            'fee_hold_amount' => 'nullable|numeric|min:0',
            'fee_hold_scope' => 'required|in:all,exam_term',
            'results_release_mode' => 'required|in:auto,manual',
            'hold_message' => 'nullable|string|max:500',
        ]);

        if ($data['fee_hold_mode'] === 'above_amount' && !isset($data['fee_hold_amount'])) {
            return back()->withErrors(['fee_hold_amount' => 'Enter the balance amount above which results are held.'])->withInput();
        }

        DB::table('parent_portal_settings')->updateOrInsert(
            ['school_id' => $schoolId],
            [
                'show_results' => $request->boolean('show_results'),
                'show_attendance' => $request->boolean('show_attendance'),
                'show_fees' => $request->boolean('show_fees'),
                'show_arrival_time' => $request->boolean('show_arrival_time'),
                'show_signout_time' => $request->boolean('show_signout_time'),
                'fee_hold_mode' => $data['fee_hold_mode'],
                'fee_hold_amount' => $data['fee_hold_mode'] === 'above_amount' ? $data['fee_hold_amount'] : null,
                'fee_hold_scope' => $data['fee_hold_scope'],
                'results_release_mode' => $data['results_release_mode'],
                'hold_message' => $data['hold_message'] ?: null,
                'updated_by' => session('LoggedTeacher'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        ParentVisibility::forget($schoolId);

        return redirect()->route('parent-portal.index')->with('success', 'Parent portal settings saved.');
    }

    /** Per-exam release: inherit | auto | manual (publish / unpublish) | scheduled. */
    public function examRelease(Request $request)
    {
        PermissionHelper::denyUnlessFeature('manage_parent_portal');
        $schoolId = $this->schoolId();

        $data = $request->validate([
            'examination_id' => 'required|integer',
            'action' => 'required|in:inherit,auto,hold,publish,schedule',
            'publish_at' => 'nullable|date',
        ]);

        Examination::withReportCards()->where('id', $data['examination_id'])->where('school_id', $schoolId)->firstOrFail();

        $key = ['school_id' => $schoolId, 'examination_id' => $data['examination_id']];

        if ($data['action'] === 'inherit') {
            DB::table('parent_exam_releases')->where($key)->delete();
            return back()->with('success', 'Exam now follows the school default.');
        }

        $row = match ($data['action']) {
            'auto' => ['mode' => 'auto', 'publish_at' => null, 'published_at' => null],
            'hold' => ['mode' => 'manual', 'publish_at' => null, 'published_at' => null],
            'publish' => ['mode' => 'manual', 'publish_at' => null, 'published_at' => now()],
            'schedule' => ['mode' => 'scheduled', 'publish_at' => $data['publish_at'], 'published_at' => null],
        };

        if ($data['action'] === 'schedule' && empty($data['publish_at'])) {
            return back()->withErrors(['publish_at' => 'Pick the date and time parents should get access.']);
        }

        DB::table('parent_exam_releases')->updateOrInsert($key, $row + [
            'updated_by' => session('LoggedTeacher'),
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Exam release for parents updated.');
    }

    public function overrideStore(Request $request)
    {
        PermissionHelper::denyUnlessFeature('manage_parent_portal');
        $schoolId = $this->schoolId();

        $data = $request->validate([
            'student_id' => 'required|integer',
            'results_access' => 'required|in:allow,hold',
            'message' => 'nullable|string|max:500',
        ]);

        Student::where('id', $data['student_id'])->where('school_id', $schoolId)->firstOrFail();

        DB::table('parent_visibility_overrides')->updateOrInsert(
            ['school_id' => $schoolId, 'student_id' => $data['student_id']],
            [
                'results_access' => $data['results_access'],
                'message' => $data['message'] ?: null,
                'updated_by' => session('LoggedTeacher'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->route('parent-portal.index')->with('success', 'Student override saved.');
    }

    public function overrideDelete($id)
    {
        PermissionHelper::denyUnlessFeature('manage_parent_portal');

        DB::table('parent_visibility_overrides')->where('id', $id)->where('school_id', $this->schoolId())->delete();

        return redirect()->route('parent-portal.index')->with('success', 'Override removed.');
    }

    private function schoolId(): int
    {
        $id = session('LoggedSchool');
        abort_unless($id, 403, 'Open a school first.');

        return (int) $id;
    }
}

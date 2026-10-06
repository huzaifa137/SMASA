<?php

namespace App\Http\Controllers;

use App\Models\CustomReportTemplate;
use App\Models\Examination;
use App\Models\School;
use App\Models\SchoolCustomReportCard;
use App\Support\CustomReportCards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Platform-admin screens for CUSTOM (per-school) report cards:
 *   - Designs      : register the design files found on disk, rename, switch on/off
 *   - Assignments  : give a school its own design per level (Nursery/Primary/Secondary)
 *   - Studio       : preview any design with a chosen school's REAL exams & students
 *
 * Only platform administrators may use these (Helper::isPlatformAdmin()).
 */
class CustomReportCardController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (!Helper::isPlatformAdmin()) {
            abort(403, 'Only a platform administrator can manage custom report cards.');
        }
    }

    private function adminId(): ?int
    {
        return session('LoggedAdmin') ? (int) session('LoggedAdmin') : null;
    }

    // ─── Designs ────────────────────────────────────────────────────────────

    public function index()
    {
        $this->authorizeAdmin();

        $ready = CustomReportCards::tablesReady();
        $templates = collect();
        $unregistered = [];

        if ($ready) {
            $files = CustomReportCards::discover();
            $templates = CustomReportTemplate::withCount('assignments')->orderBy('level')->orderBy('name')->get()
                ->each(function ($t) use ($files) {
                    $t->file_exists = isset($files[$t->slug]);
                    $t->meta = $files[$t->slug] ?? null;
                });
            $unregistered = array_diff_key($files, $templates->keyBy('slug')->all());
        }

        return view('Admin.custom-report-cards.index', [
            'ready' => $ready,
            'templates' => $templates,
            'unregistered' => $unregistered,
            'levels' => CustomReportCards::LEVELS,
            'viewDir' => 'resources/views/Examination/passslips/custom',
        ]);
    }

    public function sync()
    {
        $this->authorizeAdmin();

        $result = CustomReportCards::sync($this->adminId());
        $msg = count($result['added'])
            ? 'Registered ' . count($result['added']) . ' new design(s): ' . implode(', ', $result['added']) . '.'
            : 'No new design files found.';
        if ($result['missing']) {
            $msg .= ' Registered but file missing on disk: ' . implode(', ', $result['missing']) . '.';
        }

        return back()->with('success', $msg);
    }

    public function updateTemplate(Request $request, $id)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $tpl = CustomReportTemplate::findOrFail($id);
        $tpl->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Design "' . $tpl->name . '" updated.');
    }

    public function destroyTemplate($id)
    {
        $this->authorizeAdmin();

        $tpl = CustomReportTemplate::withCount('assignments')->findOrFail($id);
        if ($tpl->assignments_count > 0) {
            return back()->with('error', 'This design is assigned to ' . $tpl->assignments_count . ' school(s). Unassign it first.');
        }

        $tpl->delete();

        return back()->with('success', 'Design removed from the registry. (The file on disk is untouched.)');
    }

    // ─── Assignments ────────────────────────────────────────────────────────

    public function assignments(Request $request)
    {
        $this->authorizeAdmin();

        $ready = CustomReportCards::tablesReady();
        $schools = School::orderBy('name')->get(['id', 'name']);
        $byKey = $ready
            ? SchoolCustomReportCard::with('template')->get()->keyBy(fn($a) => $a->school_id . '|' . $a->level)
            : collect();
        $templates = $ready ? CustomReportTemplate::where('is_active', true)->orderBy('name')->get() : collect();

        return view('Admin.custom-report-cards.assignments', [
            'ready' => $ready,
            'schools' => $schools,
            'assignments' => $byKey,
            'templates' => $templates,
            'levels' => CustomReportCards::LEVELS,
        ]);
    }

    public function assign(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'level' => ['required', Rule::in(array_keys(CustomReportCards::LEVELS))],
            'template_id' => 'required|integer|exists:custom_report_templates,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $tpl = CustomReportTemplate::findOrFail($data['template_id']);
        if ($tpl->level !== $data['level']) {
            return back()->with('error', 'That design is for the ' . CustomReportCards::LEVELS[$tpl->level] . ' level, not ' . CustomReportCards::LEVELS[$data['level']] . '.');
        }
        if (!$tpl->is_active || !is_file(CustomReportCards::filePath($tpl->slug))) {
            return back()->with('error', 'That design is switched off or its file is missing.');
        }

        SchoolCustomReportCard::updateOrCreate(
            ['school_id' => $data['school_id'], 'level' => $data['level']],
            [
                'custom_report_template_id' => $tpl->id,
                'is_active' => true,
                'lock_to_custom' => $request->boolean('lock_to_custom'),
                'notes' => $data['notes'] ?? null,
                'assigned_by' => $this->adminId(),
                'assigned_at' => now(),
            ]
        );

        return back()->with('success', 'Assigned "' . $tpl->name . '" to the school (' . CustomReportCards::LEVELS[$data['level']] . ').');
    }

    public function updateAssignment(Request $request, $id)
    {
        $this->authorizeAdmin();

        $row = SchoolCustomReportCard::findOrFail($id);
        $row->update([
            'is_active' => $request->boolean('is_active'),
            'lock_to_custom' => $request->boolean('lock_to_custom'),
        ]);

        return back()->with('success', 'Assignment updated.');
    }

    public function unassign($id)
    {
        $this->authorizeAdmin();

        SchoolCustomReportCard::findOrFail($id)->delete();

        return back()->with('success', 'Custom design removed - the school is back on the standard designs for that level.');
    }

    // ─── Studio ─────────────────────────────────────────────────────────────

    public function studio(Request $request)
    {
        $this->authorizeAdmin();

        $ready = CustomReportCards::tablesReady();

        return view('Admin.custom-report-cards.studio', [
            'ready' => $ready,
            'schools' => School::orderBy('name')->get(['id', 'name']),
            'templates' => $ready ? CustomReportTemplate::orderBy('name')->get() : collect(),
            'preselectSlug' => $request->query('slug'),
            'preselectSchool' => $request->query('school_id'),
        ]);
    }

    /** AJAX: exams of a school. */
    public function studioExams(Request $request)
    {
        $this->authorizeAdmin();
        $schoolId = (int) $request->query('school_id');

        $exams = Examination::where('school_id', $schoolId)
            ->orderByDesc('academic_year')->orderByDesc('term')->orderByDesc('id')
            ->limit(100)->get()
            ->map(fn($e) => ['id' => $e->id, 'label' => $e->exam_name . ' — ' . \App\Support\Term::label($e->term) . ' ' . $e->academic_year]);

        return response()->json(['exams' => $exams]);
    }

    /** AJAX: students (that have marks in the exam) of the level the design is for. */
    public function studioStudents(Request $request)
    {
        $this->authorizeAdmin();
        $schoolId = (int) $request->query('school_id');
        $examId = (int) $request->query('exam_id');
        $level = $request->query('level');

        $ids = DB::table('examination_marks')
            ->where('examination_id', $examId)->where('school_id', $schoolId)
            ->distinct()->pluck('student_id');

        $students = DB::table('students')
            ->whereIn('id', $ids)->where('school_id', $schoolId)
            ->orderBy('lastname')->limit(600)
            ->get(['id', 'firstname', 'lastname', 'senior', 'stream'])
            ->filter(fn($s) => !$level || CustomReportCards::levelForClass($s->senior) === $level)
            ->map(fn($s) => [
                'id' => $s->id,
                'label' => trim($s->lastname . ' ' . $s->firstname) . ' — ' . (Helper::recordMdname($s->senior) ?? '') . ' ' . $s->stream,
            ])->values();

        return response()->json(['students' => $students]);
    }

    /**
     * Renders a design with real data of the chosen school. Works for ANY
     * registered design (assigned or not) - this is the admin's design tool.
     */
    public function preview(Request $request)
    {
        $this->authorizeAdmin();

        $schoolId = (int) $request->query('school_id');
        $slug = (string) $request->query('slug');
        $meta = CustomReportCards::readMeta($slug);
        abort_unless($meta, 404, 'Design file not found.');

        $exam = Examination::where('id', $request->query('exam_id'))->where('school_id', $schoolId)->firstOrFail();
        $student = DB::table('students')->where('id', $request->query('student_id'))->where('school_id', $schoolId)->firstOrFail();

        $custom = [
            'view' => CustomReportCards::viewName($slug),
            'key' => CustomReportCards::templateKey($slug),
            'slug' => $slug,
            'template' => CustomReportTemplate::where('slug', $slug)->first(),
            'meta' => $meta,
            'level' => $meta['level'],
            'locked' => true,
        ];

        // Some helpers fall back to the school session; the admin has none,
        // so bind it for this request only and restore it afterwards.
        $previous = session('LoggedSchool');
        session(['LoggedSchool' => $schoolId]);

        try {
            $ec = app(ExaminationController::class);
            $data = $ec->buildPassslipData($exam->id, $student->id, $schoolId, $exam, $student);
            $html = $ec->renderCustomSlips($custom, $exam, $schoolId, [$ec->slipFromPassslipData($student, $data)], 'single', ['embed' => true])->render();
        } finally {
            $previous ? session(['LoggedSchool' => $previous]) : session()->forget('LoggedSchool');
        }

        return response($html);
    }
}

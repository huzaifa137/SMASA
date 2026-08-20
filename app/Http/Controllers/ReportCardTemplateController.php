<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use App\Models\ReportCardTemplate;
use App\Models\School;
use App\Models\SchoolProfile;
use App\Models\Student;
use App\Services\ReportCardRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReportCardTemplateController extends Controller
{
    public function __construct(private ReportCardRenderer $renderer)
    {
    }

    /**
     * Gallery: the 3 starter templates (Nursery/Primary/Secondary) plus
     * whatever this school has cloned or built for itself, grouped by
     * category — resources/views/report-cards/index.blade.php.
     */
    public function index()
    {
        $schoolId = session('LoggedSchool');

        $all = ReportCardTemplate::active()
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('school_id')->orWhere('school_id', $schoolId);
            })
            ->orderByDesc('school_id') // this school's own designs float above the starters within each category
            ->orderBy('name')
            ->get();

        $templates = collect(ReportCardTemplate::CATEGORIES)
            ->mapWithKeys(fn ($cat) => [$cat => $all->where('category', $cat)->values()])
            ->all();

        return view('report-cards.index', compact('templates'));
    }

    /** Blank-canvas template, or start from nothing at all. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => ['required', Rule::in(ReportCardTemplate::CATEGORIES)],
        ]);

        $schoolId = session('LoggedSchool');

        $template = ReportCardTemplate::create([
            'school_id'   => $schoolId,
            'name'        => $data['name'],
            'category'    => $data['category'],
            'description' => 'Blank canvas',
            'canvas_width' => 794,
            'canvas_height' => 1123,
            'background'  => ['color' => '#FFFFFF'],
            'elements'    => [],
            'is_default'  => false,
            'is_active'   => true,
            'created_by'  => Auth::id(),
        ]);

        return redirect()->route('report-templates.edit', $template)
            ->with('success', 'Blank template created — start adding elements.');
    }

    /** Clone a starter (or another school's) template into this school's own editable copy. */
    public function duplicate(Request $request, ReportCardTemplate $template)
    {
        $schoolId = session('LoggedSchool');

        $name = $request->input('name', $template->name . ' (My Version)');

        $copy = $template->duplicate($name, $schoolId, Auth::id());

        return redirect()->route('report-templates.edit', $copy)
            ->with('success', 'Template duplicated — customize it below.');
    }

    /** The drag-and-drop builder (Fabric.js canvas + properties panel). */
    public function edit(ReportCardTemplate $template)
    {
        $this->authorizeEdit($template);

        return view('report-cards.builder', compact('template'));
    }

    /** Debounced autosave from the builder — always writes to the DRAFT column. */
    public function autosave(Request $request, ReportCardTemplate $template)
    {
        $this->authorizeEdit($template);

        $data = $request->validate([
            'elements'   => 'present|array',
            'name'       => 'sometimes|string|max:255',
            'background' => 'sometimes|array',
        ]);

        $template->update(array_filter([
            'elements'   => $data['elements'],
            'name'       => $data['name'] ?? null,
            'background' => $data['background'] ?? null,
        ], fn ($v) => $v !== null));

        return response()->json(['status' => 'saved', 'saved_at' => now()->toDateTimeString()]);
    }

    /** Copies the current draft into published_elements — this is what live report cards use. */
    public function publish(ReportCardTemplate $template)
    {
        $this->authorizeEdit($template);

        $template->publish();

        return response()->json(['status' => 'published']);
    }

    /**
     * Chooses this template as the one the school prints with for its
     * category. Only meaningful for a school's OWN template — starter
     * templates can't be "set default" directly, they must be duplicated
     * first (the gallery only shows this button on school-owned cards).
     */
    public function setDefault(ReportCardTemplate $template)
    {
        $this->authorizeEdit($template);

        $template->makeDefaultForSchool();

        return redirect()->route('report-templates.index')
            ->with('success', "\"{$template->name}\" is now the default {$template->category} template.");
    }

    /**
     * Un-sets this school's chosen default for a category, so printing
     * falls back to the built-in system design (Modern / Classic / Minimal)
     * for that category again. The school's own custom template(s) are not
     * touched or deleted — just no longer marked default — so they can pick
     * "Set as default" again later if they change their mind.
     */
    public function restoreDefault(Request $request, string $category)
    {
        abort_unless(in_array($category, ReportCardTemplate::CATEGORIES, true), 404);

        $schoolId = session('LoggedSchool');

        ReportCardTemplate::restoreSystemDefault($schoolId, $category);

        return redirect()->route('report-templates.index')
            ->with('success', "Switched back to the system default {$category} design.");
    }

    /**
     * Wipes every template this school has created/duplicated, across all
     * categories, and puts every category back on the system default
     * (Modern / Classic / Minimal). This is the "start over completely"
     * button — unlike restoreDefault() above (which just un-chooses one
     * category's default and keeps the design saved), this permanently
     * removes the designs themselves.
     */
    public function resetAll(Request $request)
    {
        $schoolId = session('LoggedSchool');

        $removed = ReportCardTemplate::resetSchoolToDefaults($schoolId);

        return redirect()->route('report-templates.index')->with('success', $removed > 0
            ? "Removed all {$removed} of your custom designs — every category is back to the system default (Modern, Classic, Minimal)."
            : "You don't have any custom designs to remove — everything is already on the system defaults.");
    }

    /**
     * Live preview with sample data — used both as the small iframe
     * thumbnail on the gallery and the full "Preview with sample data"
     * button inside the builder. Renders the DRAFT (unpublished) layout so
     * designers see their in-progress changes.
     */
    public function preview(ReportCardTemplate $template)
    {
        $data = $this->renderer->sampleData();
        $html = $this->renderer->renderHtml($template, $data, usePublished: false);

        return view('report-cards.preview', [
            'template' => $template,
            'html'     => $html,
        ]);
    }

    public function destroy(ReportCardTemplate $template)
    {
        $this->authorizeEdit($template);

        if (is_null($template->school_id)) {
            abort(403, 'Starter templates cannot be deleted.');
        }

        $template->update(['is_active' => false]);

        return redirect()->route('report-templates.index')->with('success', 'Template removed.');
    }

    /**
     * Real integration point: generates one student's PDF report card
     * using their SCHOOL'S chosen design instead of the old fixed Blade
     * template. Reuses ExaminationController::buildPassslipData() so the
     * marks/grades/rank logic stays identical to the existing pass slips —
     * only the visual layout changes.
     */
    public function downloadForStudent(Request $request, $examId, $studentId)
    {
        $schoolId = session('LoggedSchool');

        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->firstOrFail();
        $student = Student::where('school_id', $schoolId)->findOrFail($studentId);
        $school = School::find($schoolId);
        $profile = SchoolProfile::where('school_id', $schoolId)->first();

        $examController = app(ExaminationController::class);
        $passslipData = $examController->buildPassslipData($examId, $studentId, $schoolId, $exam, $student);

        $isEarlyYears = $passslipData['isEarlyYears'] ?? false;
        $category = $this->renderer->categoryForClass($student->senior, $isEarlyYears);
        $className = \App\Http\Controllers\Helper::item_md_name($student->senior);

        $template = $request->filled('template_id')
            ? ReportCardTemplate::where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })->findOrFail($request->integer('template_id'))
            : ReportCardTemplate::resolveForSchool($schoolId, $category);

        abort_unless($template, 404, "No {$category} report card template is available yet.");

        $data = $this->renderer->dataForResult($student, $exam, $passslipData, $className, $school, $profile, forPdf: true);

        $pdf = $this->renderer->renderPdf($template, $data);

        $safeName = str_replace(' ', '_', $student->firstname . '_' . $student->lastname);

        return $pdf->download("ReportCard_{$safeName}_{$exam->exam_name}.pdf");
    }

    private function authorizeEdit(ReportCardTemplate $template): void
    {
        $schoolId = session('LoggedSchool');

        // Starter templates (school_id null) are read-only — schools must
        // duplicate one before they can edit/publish/delete/default it.
        abort_unless($template->school_id !== null && (int) $template->school_id === (int) $schoolId, 403,
            'This is a starter template — duplicate it first to make your own editable copy.');
    }
}
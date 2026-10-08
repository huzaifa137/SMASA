<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\GradingScheme;
use App\Models\NlscAssessment;
use App\Models\OLevelReportCardComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Secondary O-Level (Senior 1-4, new NLSC curriculum) report cards.
 *
 * A report card here is a saved COMPOSITION, not an exam anyone sits:
 *
 *   • assessments component  – any number of NLSC assessments (one, a few, or
 *     all of them), their 0-3 competency scores rescaled to a weight
 *     (e.g. "Assessments /20");
 *   • exam component         – one standard examination (Create Exam → "Standard
 *     examination"), its marks rescaled to a weight (e.g. "Exam /80").
 *
 * A card may hold one component or several, and the same assessment / exam can
 * appear on any number of cards. The weights are whatever the school wants
 * (20 + 80 = 100 is the usual combined card; a lone exam can be "out of 80" or
 * "out of 100").
 *
 * Storage: each card is saved as an Examination row with
 * o_level_mode = 'report_card' (hidden from every ordinary exam list by
 * ExcludeReportCardsScope) plus its components. That lets the card reuse the
 * whole existing pass-slip pipeline — templates, customisation, custom school
 * designs, remarks, discipline, rank — which is keyed by examination id; the
 * marks themselves come from App\Services\OLevelReportCardService.
 */
class OLevelReportCardController extends Controller
{
    // ── List ────────────────────────────────────────────────────────────────

    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_olevel_report_cards');

        $schoolId = Session('LoggedSchool');

        $cards = Examination::withReportCards()
            ->where('school_id', $schoolId)
            ->where('o_level_mode', Examination::MODE_REPORT_CARD)
            ->with('reportCardComponents')
            ->orderByDesc('id')
            ->get()
            ->each(function ($card) use ($schoolId) {
                $card->setAttribute('class_labels', ExaminationClass::where('examination_id', $card->id)
                    ->get()
                    ->map(fn($ec) => Helper::recordMdname($ec->class_id) . ($ec->stream_id ? ' · ' . $ec->stream_id : ''))
                    ->all());
            });

        return view('Examination.olevel-report-cards.index', compact('cards'));
    }

    // ── Create / edit form ───────────────────────────────────────────────────

    public function create()
    {
        PermissionHelper::denyUnlessFeature('create_olevel_report_card');

        return view('Examination.olevel-report-cards.form', $this->formData(null));
    }

    public function edit($id)
    {
        PermissionHelper::denyUnlessFeature('edit_olevel_report_card');

        $card = $this->findCard($id);

        return view('Examination.olevel-report-cards.form', $this->formData($card));
    }

    private function formData(?Examination $card): array
    {
        $schoolId = Session('LoggedSchool');

        $oLevelClassIds = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
            ->pluck('md_id')->map(fn($v) => (string) $v)->all();

        $classStreams = DB::table('class_stream_assignments')
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $oLevelClassIds)
            ->get();

        $gradingSchemes = GradingScheme::availableTo($schoolId)
            ->orderByRaw('school_id IS NULL')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $selectedClassStreams = [];
        $components = [];

        if ($card) {
            $selectedClassStreams = ExaminationClass::where('examination_id', $card->id)->get()
                ->map(fn($ec) => $ec->class_id . '_' . ($ec->stream_id ?? ''))->all();

            $components = $card->reportCardComponents()->with('assessments:id')->get()->map(fn($c) => [
                'type' => $c->type,
                'label' => $c->label,
                'weight' => (float) $c->weight,
                'source_examination_id' => $c->source_examination_id,
                'assessment_ids' => $c->assessments->pluck('id')->all(),
            ])->all();
        }

        return compact('card', 'classStreams', 'gradingSchemes', 'selectedClassStreams', 'components');
    }

    // ── AJAX: what can be put on a card for the ticked classes ───────────────

    /**
     * Assessments created for the chosen class-streams, grouped by the exam they
     * were created under and then by subject, so the form can offer
     * "tick any of them" or "all of this exam's assessments".
     */
    public function assessmentOptions(Request $request)
    {
        PermissionHelper::denyUnlessFeature('create_olevel_report_card');

        $schoolId = Session('LoggedSchool');
        $pairs = $this->parseClassStreams((array) $request->query('class_streams', []));

        if (empty($pairs)) {
            return response()->json(['exams' => []]);
        }

        $assessments = NlscAssessment::with(['exam', 'topic', 'project', 'subjectAchievement'])
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as [$classId, $streamId]) {
                    $q->orWhere(fn($w) => $w->where('class_id', $classId)->where('stream_id', (string) $streamId));
                }
            })
            ->orderBy('examination_id')
            ->orderBy('subject_id')
            ->orderBy('id')
            ->get()
            ->filter(fn($a) => $a->exam); // exam row may be a (hidden) report card or deleted

        $exams = $assessments->groupBy('examination_id')->map(function ($group) {
            $exam = $group->first()->exam;

            return [
                'exam_id' => $exam->id,
                'exam_name' => $exam->exam_name,
                'term' => \App\Support\Term::label($exam->term),
                'year' => $exam->academic_year,
                'subjects' => $group->groupBy('subject_id')->map(function ($rows, $subjectId) {
                    return [
                        'subject' => Helper::examSubjectName($subjectId, null),
                        'assessments' => $rows->map(fn($a) => [
                            'id' => $a->id,
                            'class' => Helper::recordMdname($a->class_id) . ' · ' . $a->stream_id,
                            'type' => ucwords(str_replace('_', ' ', $a->assessment_type)),
                            'title' => $this->assessmentTitle($a),
                            'max_marks' => $a->max_marks,
                            'in_report' => (bool) $a->include_in_report,
                        ])->values(),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json(['exams' => $exams]);
    }

    /**
     * Standard examinations (and nothing else) that sit the chosen class-streams —
     * the only exams that make sense as the "exam" part of a card, because they
     * hold ordinary marks.
     */
    public function examOptions(Request $request)
    {
        PermissionHelper::denyUnlessFeature('create_olevel_report_card');

        $schoolId = Session('LoggedSchool');
        $pairs = $this->parseClassStreams((array) $request->query('class_streams', []));

        if (empty($pairs)) {
            return response()->json(['exams' => []]);
        }

        $exams = Examination::where('school_id', $schoolId)
            ->where('o_level_mode', Examination::MODE_STANDARD)
            ->whereHas('examinationClasses', function ($q) use ($pairs) {
                $q->where(function ($w) use ($pairs) {
                    foreach ($pairs as [$classId, $streamId]) {
                        $w->orWhere(function ($x) use ($classId, $streamId) {
                            $x->where('class_id', $classId);
                            $streamId === null ? $x->whereNull('stream_id') : $x->where('stream_id', $streamId);
                        });
                    }
                });
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->exam_name . ' — ' . \App\Support\Term::label($e->term) . ' ' . $e->academic_year,
                'total_marks' => $e->total_marks,
            ]);

        return response()->json(['exams' => $exams]);
    }

    // ── Save ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        PermissionHelper::denyUnlessFeature('create_olevel_report_card');

        $data = $this->validated($request);
        $schoolId = Session('LoggedSchool');

        DB::beginTransaction();
        try {
            $card = Examination::withReportCards()->create([
                'exam_code' => $this->nextCode(),
                'exam_name' => $data['exam_name'],
                'exam_type' => 'Report Card',
                'term' => $data['term'],
                'academic_year' => $data['academic_year'],
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'marks_entry_deadline' => now()->toDateString(),
                'description' => $data['description'] ?? null,
                'total_marks' => (int) round(collect($data['components'])->sum('weight')),
                'pass_mark' => 50,
                'grading_scheme_id' => $data['grading_scheme_id'],
                // 'closed' = pass slips open straight away (classIsReleased()) while
                // staying out of anything that only looks at 'results_released'.
                'status' => 'closed',
                'o_level_mode' => Examination::MODE_REPORT_CARD,
                'school_id' => $schoolId,
                'created_by' => Session('LoggedTeacher'),
            ]);

            $this->syncClassesAndComponents($card, $data, $schoolId);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report card saved.',
            'redirect' => route('examination.passslips.index', $card->id),
        ]);
    }

    public function update(Request $request, $id)
    {
        PermissionHelper::denyUnlessFeature('edit_olevel_report_card');

        $card = $this->findCard($id);
        $data = $this->validated($request);
        $schoolId = Session('LoggedSchool');

        DB::beginTransaction();
        try {
            $card->update([
                'exam_name' => $data['exam_name'],
                'term' => $data['term'],
                'academic_year' => $data['academic_year'],
                'description' => $data['description'] ?? null,
                'grading_scheme_id' => $data['grading_scheme_id'],
                'total_marks' => (int) round(collect($data['components'])->sum('weight')),
            ]);

            $this->syncClassesAndComponents($card, $data, $schoolId);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report card updated.',
            'redirect' => route('examination.passslips.index', $card->id),
        ]);
    }

    public function destroy($id)
    {
        PermissionHelper::denyUnlessFeature('delete_olevel_report_card');

        $card = $this->findCard($id);

        DB::transaction(function () use ($card) {
            $componentIds = OLevelReportCardComponent::where('examination_id', $card->id)->pluck('id');
            DB::table('olevel_report_card_component_assessments')->whereIn('component_id', $componentIds)->delete();
            OLevelReportCardComponent::where('examination_id', $card->id)->delete();
            ExaminationClass::where('examination_id', $card->id)->delete();
            $card->delete();
        });

        return response()->json(['success' => true, 'message' => 'Report card deleted.']);
    }

    // ── Internals ───────────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'exam_name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'academic_year' => 'required|digits:4',
            'grading_scheme_id' => 'required|integer|exists:grading_schemes,id',
            'description' => 'nullable|string',
            'class_streams' => 'required|array|min:1',
            'class_streams.*' => 'string',
            'components' => 'required|array|min:1',
            'components.*.type' => 'required|in:assessments,exam',
            'components.*.label' => 'required|string|max:80',
            'components.*.weight' => 'required|numeric|gt:0|max:1000',
            'components.*.source_examination_id' => 'nullable|integer',
            'components.*.assessment_ids' => 'nullable|array',
            'components.*.assessment_ids.*' => 'integer',
        ], [
            'class_streams.required' => 'Pick at least one class.',
            'components.required' => 'Add at least one component (assessments or an examination).',
        ]);

        $schoolId = Session('LoggedSchool');

        if (!GradingScheme::availableTo($schoolId)->where('id', $data['grading_scheme_id'])->exists()) {
            abort(422, 'The selected grading scheme is not available to your school.');
        }

        foreach ($data['components'] as $i => $c) {
            $n = $i + 1;

            if ($c['type'] === 'exam') {
                $ok = !empty($c['source_examination_id'])
                    && Examination::where('school_id', $schoolId)
                        ->where('id', $c['source_examination_id'])
                        ->where('o_level_mode', Examination::MODE_STANDARD)
                        ->exists();

                if (!$ok) {
                    abort(422, "Component {$n}: choose a standard examination.");
                }
            } else {
                $ids = array_values(array_unique($c['assessment_ids'] ?? []));
                $valid = NlscAssessment::where('school_id', $schoolId)->whereIn('id', $ids)->count();

                if (empty($ids) || $valid !== count($ids)) {
                    abort(422, "Component {$n}: tick at least one assessment.");
                }
            }
        }

        return $data;
    }

    private function syncClassesAndComponents(Examination $card, array $data, $schoolId): void
    {
        // Classes
        ExaminationClass::where('examination_id', $card->id)->delete();
        foreach ($this->parseClassStreams($data['class_streams']) as [$classId, $streamId]) {
            ExaminationClass::create([
                'examination_id' => $card->id,
                'class_id' => $classId,
                'stream_id' => $streamId ?: null,
                'school_id' => $schoolId,
            ]);
        }

        // Components (replaced wholesale — a card is small)
        $oldIds = OLevelReportCardComponent::where('examination_id', $card->id)->pluck('id');
        DB::table('olevel_report_card_component_assessments')->whereIn('component_id', $oldIds)->delete();
        OLevelReportCardComponent::where('examination_id', $card->id)->delete();

        foreach (array_values($data['components']) as $i => $c) {
            $component = OLevelReportCardComponent::create([
                'examination_id' => $card->id,
                'school_id' => $schoolId,
                'type' => $c['type'],
                'label' => $c['label'],
                'weight' => $c['weight'],
                'source_examination_id' => $c['type'] === 'exam' ? $c['source_examination_id'] : null,
                'sort_order' => $i,
            ]);

            if ($c['type'] === 'assessments') {
                $component->assessments()->sync(array_values(array_unique($c['assessment_ids'])));
            }
        }
    }

    private function findCard($id): Examination
    {
        return Examination::withReportCards()
            ->where('id', $id)
            ->where('school_id', Session('LoggedSchool'))
            ->where('o_level_mode', Examination::MODE_REPORT_CARD)
            ->firstOrFail();
    }

    /** "12_A" / "12_NO_STREAM" → [[12,'A'], [12,'NO_STREAM']]  (stream ids may contain underscores) */
    private function parseClassStreams(array $values): array
    {
        return collect($values)->map(function ($cs) {
            [$classId, $streamId] = array_pad(explode('_', (string) $cs, 2), 2, null);
            return [$classId, $streamId === '' ? null : $streamId];
        })->filter(fn($p) => !empty($p[0]))->values()->all();
    }

    /** Report-card exams get their own code series so they never collide with real exam codes. */
    private function nextCode(): string
    {
        $year = date('Y');
        $last = Examination::withReportCards()
            ->where('exam_code', 'like', 'RCARD-' . $year . '-%')
            ->orderByDesc('id')
            ->value('exam_code');

        $n = $last && preg_match('/(\d+)$/', $last, $m) ? ((int) $m[1] + 1) : 1;

        return 'RCARD-' . $year . '-' . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    private function assessmentTitle(NlscAssessment $a): string
    {
        return match ($a->assessment_type) {
            'projects' => optional($a->project)->project_name ?? 'Project',
            default => optional($a->topic)->topic_name ?? 'Topic',
        };
    }
}

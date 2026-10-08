<?php

namespace App\Services;

use App\Models\Examination;
use App\Models\ExaminationMark;
use App\Models\NlscAssessment;
use App\Models\NlscAssessmentMark;
use App\Models\OLevelReportCardComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Computes the subject marks of an O-Level (Senior 1-4, NLSC) report card.
 *
 * A report card is a list of COMPONENTS, each rescaled to its own weight:
 *
 *   assessments  The student's average NLSC competency score (calculated_score,
 *                the fixed 0-3 scale) across the selected assessments of that
 *                subject, shown as  avg / 3 * weight.   e.g. weight 20.
 *   exam         A standard examination's mark for that subject, shown as
 *                marks_obtained / total_marks * weight.   e.g. weight 80.
 *
 * A subject's card mark is the sum of its components' points, out of the sum
 * of the weights that actually have data for that student+subject. A card with
 * a single component therefore converts that source onto any scale (an exam
 * "out of 80", assessments "out of 20"); a card with both gives 20 + 80 = 100.
 *
 * Output rows are shaped exactly like ExaminationMark rows (marks_obtained,
 * total_marks, subject_id, custom_subject_id, class_id, stream_id ...) so the
 * existing pass-slip pipeline (grading bands, rank, aggregate, templates) runs
 * on them unchanged. Each row also carries `components`, the per-component
 * breakdown the secondary template prints as extra columns.
 */
class OLevelReportCardService
{
    /** @var array<string,array<int,Collection>> memo: "exam|class|stream" => [studentId => rows] */
    private array $memo = [];

    /** @var array<int,Collection> */
    private array $componentMemo = [];

    public function components(Examination $exam): Collection
    {
        return $this->componentMemo[$exam->id] ??= OLevelReportCardComponent::with('assessments:id')
            ->where('examination_id', $exam->id)
            ->orderBy('sort_order')
            ->get();
    }

    /** Subject rows for one student (empty collection when there is nothing to show). */
    public function studentMarks(Examination $exam, $studentId, $schoolId): Collection
    {
        $student = DB::table('students')->where('id', $studentId)->where('school_id', $schoolId)->first();

        if (!$student) {
            return collect();
        }

        return $this->forClass($exam, $student->senior, $student->stream, $schoolId)[(int) $studentId] ?? collect();
    }

    /**
     * Rank input: one row per student with a grand total, highest first —
     * the same shape the pipeline builds from examination_marks.
     */
    public function classTotals(Examination $exam, $classId, $streamId, $schoolId): Collection
    {
        return collect($this->forClass($exam, $classId, $streamId, $schoolId))
            ->map(fn(Collection $rows, $studentId) => (object) [
                'student_id' => (int) $studentId,
                'grand_total' => (float) $rows->whereNotNull('marks_obtained')->sum('marks_obtained'),
            ])
            ->sortByDesc('grand_total')
            ->values();
    }

    /** Sum of a student's card marks (used to sort the pass-slip index). */
    public function studentTotal(Examination $exam, $studentId, $schoolId): float
    {
        return (float) $this->studentMarks($exam, $studentId, $schoolId)
            ->whereNotNull('marks_obtained')->sum('marks_obtained');
    }

    /** @return array<int,Collection> studentId => subject rows, for every student of the class-stream. */
    public function forClass(Examination $exam, $classId, $streamId, $schoolId): array
    {
        $key = $exam->id . '|' . $classId . '|' . $streamId;

        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $studentIds = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classId)
            ->where('stream', $streamId)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        if (empty($studentIds)) {
            return $this->memo[$key] = [];
        }

        // [studentId][subjectKey] => ['meta' => [...], 'components' => [index => data]]
        $cells = [];
        $components = $this->components($exam)->values();

        foreach ($components as $index => $component) {
            $data = $component->type === 'assessments'
                ? $this->assessmentComponent($component, $studentIds, $schoolId)
                : $this->examComponent($component, $studentIds, $schoolId);

            foreach ($data as $studentId => $bySubject) {
                foreach ($bySubject as $subjectKey => $cell) {
                    $cells[$studentId][$subjectKey]['meta'] ??= $cell['meta'];
                    $cells[$studentId][$subjectKey]['components'][$index] = $cell['ratio'];
                }
            }
        }

        $result = [];

        foreach ($cells as $studentId => $bySubject) {
            $rows = collect();

            foreach ($bySubject as $cell) {
                $points = 0.0;
                $possible = 0.0;
                $breakdown = [];

                foreach ($components as $index => $component) {
                    $ratio = $cell['components'][$index] ?? null;
                    $weight = (float) $component->weight;

                    $breakdown[] = [
                        'label' => $component->label,
                        'type' => $component->type,
                        'weight' => $weight,
                        'points' => $ratio === null ? null : round($ratio * $weight, 2),
                    ];

                    if ($ratio !== null) {
                        $points += $ratio * $weight;
                        $possible += $weight;
                    }
                }

                $rows->push((object) [
                    'student_id' => $studentId,
                    'subject_id' => $cell['meta']['subject_id'],
                    'custom_subject_id' => $cell['meta']['custom_subject_id'],
                    'class_id' => $classId,
                    'stream_id' => $streamId,
                    'subject_type' => 'secondary_olevel',
                    'marks_obtained' => round($points, 2),
                    'total_marks' => round($possible, 2),
                    'grade' => null,
                    'grade_remark' => null,
                    'grade_points' => null,
                    'teacher_comment' => null,
                    'class_average' => null,
                    'components' => $breakdown,
                ]);
            }

            $result[$studentId] = $rows->values();
        }

        return $this->memo[$key] = $result;
    }

    /**
     * assessments component → [studentId][subjectKey] => ['meta' => ..., 'ratio' => avg/3]
     * Assessments a teacher unticked "include in report" on stay out, exactly
     * as they do on the ordinary NLSC reports.
     */
    private function assessmentComponent(OLevelReportCardComponent $component, array $studentIds, $schoolId): array
    {
        $assessmentIds = $component->assessments->pluck('id')->all();

        if (empty($assessmentIds)) {
            return [];
        }

        $assessments = NlscAssessment::where('school_id', $schoolId)
            ->whereIn('id', $assessmentIds)
            ->where('include_in_report', true)
            ->get()
            ->keyBy('id');

        if ($assessments->isEmpty()) {
            return [];
        }

        $marks = NlscAssessmentMark::whereIn('nlsc_assessment_id', $assessments->keys())
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('marks_obtained')
            ->get();

        $scores = []; // [student][subject] => [calculated scores]

        foreach ($marks as $mark) {
            $assessment = $assessments[$mark->nlsc_assessment_id];
            $max = (float) $assessment->max_marks;
            $score = $mark->calculated_score !== null
                ? (float) $mark->calculated_score
                : ($max > 0 ? round(((float) $mark->marks_obtained / $max) * 3, 1) : 0.0);

            $scores[(int) $mark->student_id]['s' . $assessment->subject_id][] = [$assessment->subject_id, $score];
        }

        $out = [];

        foreach ($scores as $studentId => $bySubject) {
            foreach ($bySubject as $subjectKey => $list) {
                $avg = array_sum(array_column($list, 1)) / count($list);

                $out[$studentId][$subjectKey] = [
                    'meta' => ['subject_id' => $list[0][0], 'custom_subject_id' => null],
                    'ratio' => max(0.0, min(1.0, $avg / 3)),
                ];
            }
        }

        return $out;
    }

    /** exam component → [studentId][subjectKey] => ['meta' => ..., 'ratio' => marks/total] */
    private function examComponent(OLevelReportCardComponent $component, array $studentIds, $schoolId): array
    {
        if (!$component->source_examination_id) {
            return [];
        }

        $marks = ExaminationMark::where('examination_id', $component->source_examination_id)
            ->where('school_id', $schoolId)
            ->whereIn('student_id', $studentIds)
            ->visibleOnReport()
            ->whereNotNull('marks_obtained')
            ->get();

        $out = [];

        foreach ($marks as $mark) {
            if ((float) $mark->total_marks <= 0) {
                continue;
            }

            $subjectKey = $mark->subject_id ? 's' . $mark->subject_id : 'c' . $mark->custom_subject_id;

            $out[(int) $mark->student_id][$subjectKey] = [
                'meta' => ['subject_id' => $mark->subject_id, 'custom_subject_id' => $mark->custom_subject_id],
                'ratio' => max(0.0, min(1.0, (float) $mark->marks_obtained / (float) $mark->total_marks)),
            ];
        }

        return $out;
    }
}

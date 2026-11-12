<?php

namespace App\Services;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Helper;
use App\Models\AssessmentScale;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use App\Models\ExaminationSubjectSetting;
use App\Support\MarksSheetParser as Parser;
use Illuminate\Support\Facades\DB;

/**
 * Import the marks of one class-stream for one examination from an Excel/CSV sheet.
 *
 * It follows the Marks Entry screen's rules exactly (same grade bands, same Early-Years scale handling, same
 * "subject not sat" / eligible-pupil / paper-teacher checks, same ExaminationMark::updateOrCreate keys) and
 * never creates pupils or subjects. Subjects that save per-paper marks, and Secondary O-Level assessment
 * subjects, are reported and skipped - those keep using the Marks Entry screen.
 *
 * Two phases so nothing is written until the user has seen the preview:
 *   buildPlan()  - read-only: matches pupils, maps columns, validates cells, says what would change.
 *   commit()     - re-builds the plan from the same file (never trusts the browser) and writes it in one transaction.
 */
class ExaminationMarksImportService
{
    public const OPEN_STATUSES = ['marks_entry', 'active'];

    /** @return array ['subjects'=>list, 'skipped'=>list of ['name','reason']] */
    public function importableSubjects(Examination $exam, int $classId, string $streamId, $teacherId): array
    {
        $schoolId = $exam->school_id;
        $examClass = $this->examClass($exam, $classId, $streamId);

        $rows = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->orderBy('id')
            ->get();

        $subjects = [];
        $skipped = [];

        foreach ($rows as $cs) {
            $name = Helper::classSubjectName($cs);
            $code = $cs->custom_subject_id
                ? DB::table('custom_subjects')->where('id', $cs->custom_subject_id)->value('subject_code')
                : null;
            $code = ($code && preg_match('/^[\x20-\x7E]+$/', $code)) ? $code : null;   // Arabic codes are display-only

            if (!ExaminationSubjectSetting::marksEntryOpen($exam->id, $cs)) {
                $skipped[] = ['name' => $name, 'reason' => 'not being examined in this examination'];
                continue;
            }
            if ($cs->subject_type === 'secondary_olevel' && $exam->usesNlscAssessments()) {
                $skipped[] = ['name' => $name, 'reason' => 'O-Level assessment subject - use Marks Entry'];
                continue;
            }
            $scale = $cs->assessment_scale_id ? AssessmentScale::with('presets')->find($cs->assessment_scale_id) : null;

            // Same condition ExaminationController::saveMarks() uses to switch to per-paper saving.
            if ((!$scale || $scale->supportsPapers())
                && ExaminationPaperService::supports($exam, $classId)
                && ExaminationPaperService::papers($exam, $classId, $streamId, $cs->subject_id, $cs->custom_subject_id)->isNotEmpty()) {
                $skipped[] = ['name' => $name, 'reason' => 'marks are entered per paper - use Marks Entry'];
                continue;
            }
            $paperAccess = PaperTeacherAccess::forActor($cs, $teacherId, PermissionHelper::canFeature('manage_exam_subjects'));
            if ($paperAccess && !PaperTeacherAccess::canEnterSingleScore($paperAccess)) {
                $skipped[] = ['name' => $name, 'reason' => 'papers are assigned to other teachers'];
                continue;
            }

            $subjects[] = [
                'key' => (int) $cs->id,
                'name' => $name,
                'code' => $code,
                'row' => $cs,
                'scale' => $scale,
                'min' => $scale ? (float) $scale->min_score : 0.0,
                'max' => $scale ? (float) $scale->max_score : (float) $exam->total_marks,
                'eligible' => Helper::studentIdsTakingClassSubject($schoolId, $cs),   // null = every pupil
                'bands' => $this->bandsFor($exam, $examClass, $schoolId, $cs),
            ];
        }

        return ['subjects' => $subjects, 'skipped' => $skipped];
    }

    public function students(Examination $exam, int $classId, string $streamId): array
    {
        return DB::table('students')
            ->where('school_id', $exam->school_id)
            ->where('senior', $classId)
            ->where('stream', $streamId)
            ->orderBy('firstname')->orderBy('lastname')
            ->get(['id', 'firstname', 'lastname'])
            ->map(fn($s) => ['id' => (int) $s->id, 'firstname' => (string) $s->firstname, 'lastname' => (string) $s->lastname])
            ->all();
    }

    /**
     * @param array $sheetRows 2-D cell array of the chosen sheet
     * @param array $mapping   [sheet column index => class_subjects.id | '' (ignore)]; null = use the automatic guess
     */
    public function buildPlan(Examination $exam, int $classId, string $streamId, array $sheetRows, ?array $mapping, bool $overwrite, $teacherId): array
    {
        $ctx = $this->importableSubjects($exam, $classId, $streamId, $teacherId);
        $subjects = collect($ctx['subjects'])->keyBy('key');
        $students = $this->students($exam, $classId, $streamId);

        $layout = Parser::detectLayout($sheetRows);
        if (!$layout) {
            return ['ok' => false, 'error' => 'Could not find a header row with pupil names in this sheet. The sheet needs a "First Name" and "Last Name" (or "Name") column.', 'skipped_subjects' => $ctx['skipped']];
        }

        // Column -> subject. Automatic guess first; each subject is taken by one column only (the left-most), so a
        // second block repeating "ENG / MTC" further right is ignored instead of overwriting the first.
        $auto = [];
        $taken = [];
        foreach ($layout['columns'] as $col => $label) {
            $key = Parser::matchSubject($label, array_values(array_map(fn($s) => ['key' => $s['key'], 'name' => $s['name'], 'code' => $s['code']], $subjects->all())));
            if ($key !== null && !isset($taken[$key])) {
                $auto[$col] = $key;
                $taken[$key] = true;
            }
        }
        $chosen = [];
        foreach ($layout['columns'] as $col => $label) {
            $v = $mapping === null ? ($auto[$col] ?? '') : ($mapping[$col] ?? '');
            $chosen[$col] = ($v !== '' && $subjects->has((int) $v)) ? (int) $v : '';
        }
        // a subject picked on two columns: keep the left-most
        $seen = [];
        $duplicateCols = [];
        foreach ($chosen as $col => $key) {
            if ($key === '') {
                continue;
            }
            if (isset($seen[$key])) {
                $chosen[$col] = '';
                $duplicateCols[] = $col;
            }
            $seen[$key] = true;
        }

        $existing = ExaminationMark::where('examination_id', $exam->id)
            ->where('school_id', $exam->school_id)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->get()
            ->keyBy(fn($m) => $m->student_id . '|' . ($m->custom_subject_id ?? 0) . '|' . ($m->subject_id ?? 0));

        $rows = Parser::readRows($sheetRows, $layout);
        $problems = [];
        $changes = [];
        $stats = ['sheet_rows' => count($rows), 'pupils_matched' => 0, 'pupils_unmatched' => 0, 'new' => 0, 'updated' => 0, 'same' => 0, 'kept' => 0, 'blank' => 0, 'absent' => 0, 'invalid' => 0, 'not_taking' => 0];
        $used = [];

        foreach ($rows as $row) {
            $match = Parser::matchStudent($row, $students);
            $display = trim($row['first'] . ' ' . $row['middle'] . ' ' . $row['last']);

            if ($match['status'] !== 'ok') {
                $stats['pupils_unmatched']++;
                $problems[] = [
                    'line' => $row['line'], 'type' => $match['status'] === 'ambiguous' ? 'Name matches several pupils' : 'Pupil not found in this class',
                    'name' => $display,
                    'detail' => $match['suggestions']
                        ? 'Did you mean: ' . implode(' / ', array_map(fn($s) => $s['firstname'] . ' ' . $s['lastname'], $match['suggestions'])) . '?'
                        : 'Add the pupil first (or correct the spelling in the sheet).',
                ];
                continue;
            }
            $student = $match['student'];
            if (isset($used[$student['id']])) {
                $problems[] = ['line' => $row['line'], 'type' => 'Pupil appears twice in the sheet', 'name' => $display, 'detail' => 'Only the first row (line ' . $used[$student['id']] . ') is used.'];
                continue;
            }
            $used[$student['id']] = $row['line'];
            $stats['pupils_matched']++;

            foreach ($chosen as $col => $key) {
                if ($key === '') {
                    continue;
                }
                $subject = $subjects[$key];
                $cell = Parser::parseMark($row['cells'][$col] ?? null, $subject['min'], $subject['max']);

                if ($cell['state'] === 'blank') {
                    $stats['blank']++;
                    continue;
                }
                if ($cell['state'] === 'absent') {
                    $stats['absent']++;
                    continue;
                }
                if ($cell['state'] === 'invalid') {
                    $stats['invalid']++;
                    $problems[] = ['line' => $row['line'], 'type' => 'Invalid mark', 'name' => $display,
                        'detail' => $subject['name'] . ': "' . $cell['raw'] . '" is not a number from ' . rtrim(rtrim(number_format($subject['min'], 2, '.', ''), '0'), '.') . ' to ' . rtrim(rtrim(number_format($subject['max'], 2, '.', ''), '0'), '.') . '.'];
                    continue;
                }
                if ($subject['eligible'] !== null && !$subject['eligible']->contains($student['id'])) {
                    $stats['not_taking']++;
                    $problems[] = ['line' => $row['line'], 'type' => 'Pupil does not take this subject', 'name' => $display, 'detail' => $subject['name'] . ' skipped.'];
                    continue;
                }

                $old = $existing->get($student['id'] . '|' . ($subject['row']->custom_subject_id ?? 0) . '|' . ($subject['row']->subject_id ?? 0));
                $oldVal = $old && $old->marks_obtained !== null ? (float) $old->marks_obtained : null;

                if ($oldVal !== null && abs($oldVal - $cell['value']) < 0.005) {
                    $stats['same']++;
                    continue;
                }
                if ($oldVal !== null && !$overwrite) {
                    $stats['kept']++;
                    continue;
                }

                $oldVal === null ? $stats['new']++ : $stats['updated']++;
                $changes[] = ['student_id' => $student['id'], 'student' => $student['firstname'] . ' ' . $student['lastname'], 'subject_key' => $key, 'subject' => $subject['name'], 'old' => $oldVal, 'new' => $cell['value']];
            }
        }

        return [
            'ok' => true, 'layout' => $layout, 'chosen' => $chosen, 'auto' => $auto, 'duplicate_cols' => $duplicateCols,
            'subjects' => $subjects->map(fn($s) => ['key' => $s['key'], 'name' => $s['name']])->values()->all(),
            'skipped_subjects' => $ctx['skipped'], 'stats' => $stats, 'problems' => $problems, 'changes' => $changes,
        ];
    }

    /** Write a plan produced by buildPlan(). Returns the number of marks saved. */
    public function commit(Examination $exam, int $classId, string $streamId, array $plan, $teacherId): int
    {
        $ctx = collect($this->importableSubjects($exam, $classId, $streamId, $teacherId))->get('subjects');
        $subjects = collect($ctx)->keyBy('key');
        $schoolId = $exam->school_id;
        $saved = 0;

        DB::transaction(function () use ($plan, $subjects, $exam, $classId, $streamId, $schoolId, $teacherId, &$saved) {
            foreach ($plan['changes'] as $c) {
                $subject = $subjects->get($c['subject_key']);
                if (!$subject) {
                    continue;
                }
                [$grade, $remark, $points, $total, $value] = $this->grade((float) $c['new'], $exam, $subject);

                ExaminationMark::updateOrCreate(
                    [
                        'examination_id' => $exam->id,
                        'student_id' => $c['student_id'],
                        'subject_id' => $subject['row']->subject_id,
                        'custom_subject_id' => $subject['row']->custom_subject_id,
                    ],
                    [
                        'class_id' => $classId,
                        'stream_id' => $streamId,
                        'school_id' => $schoolId,
                        'marks_obtained' => $value,
                        'total_marks' => $total,
                        'grade' => $grade,
                        'grade_remark' => $remark,
                        'grade_points' => $points,
                        'entered_by' => $teacherId,
                        'entered_at' => now(),
                        'status' => 'entered',
                    ]
                );
                $saved++;
            }
        });

        return $saved;
    }

    // ── internals ────────────────────────────────────────────────────────────────

    private function examClass(Examination $exam, int $classId, string $streamId): ?ExaminationClass
    {
        return ExaminationClass::where('examination_id', $exam->id)
            ->where('school_id', $exam->school_id)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->first();
    }

    /** Same resolution as ExaminationController::saveMarks(). */
    private function bandsFor(Examination $exam, ?ExaminationClass $examClass, $schoolId, $cs)
    {
        $bands = $examClass ? $examClass->resolvedGradingBands() : $exam->resolvedGradingBands();

        return PassFailGradingService::scale($schoolId, $bands, $cs->subject_id, $cs->custom_subject_id);
    }

    /** @return array [grade, remark, points, total_marks, marks_value]  - mirrors ExaminationController::saveMarks() */
    private function grade(float $marks, Examination $exam, array $subject): array
    {
        $scale = $subject['scale'];

        if ($scale) {
            $total = $scale->max_score;
            $marks = $scale->allow_custom_score
                ? $scale->normalizeScore($marks)
                : max((float) $scale->min_score, min((float) $scale->max_score, round($marks)));
            $preset = $scale->presetForScore($marks);
            $remark = $preset->remark ?? null;
            $grade = null;
            $points = null;

            if ($scale->usesLinkedGrading()) {
                $percentage = $scale->max_score > 0 ? round(($marks / $scale->max_score) * 100, 4) : 0;
                $band = $scale->gradingScheme->bands->first(fn($g) => $percentage >= $g->min_mark && $percentage <= $g->max_mark);
                if ($band) {
                    $grade = $band->grade;
                    $points = $band->points;
                    $remark = $remark ?? $band->remark;
                }
            }

            return [$grade, $remark, $points, $total, $marks];
        }

        $percentage = $exam->total_marks > 0 ? round(($marks / $exam->total_marks) * 100, 4) : 0;
        $row = $subject['bands']->first(fn($g) => $percentage >= $g->min_mark && $percentage <= $g->max_mark);

        if ($row) {
            return [$row->grade, $row->remark, $row->points, $exam->total_marks, $marks];
        }

        return ['Ungraded', "No grade band covers {$percentage}% — check the exam's grading scheme.", null, $exam->total_marks, $marks];
    }
}

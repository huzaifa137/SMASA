<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * OrphanCleanupService
 * ─────────────────────────────────────────────────────────────────────────
 * Most of SMASA's tables store a raw examination_id / student_id /
 * teacher_id / nlsc_assessment_id with no foreign key, so deleting the parent
 * row leaves its children behind. This service is the one place that knows
 * those relationships, and offers both halves of the fix:
 *
 *   PREVENTION  purgeExamination(), purgeAssessments(), purgeClassStream()
 *               are called by the delete paths themselves (model `deleting`
 *               hooks and the class / stream controllers), so nothing is left
 *               behind from now on.
 *
 *   CURE        scan() / purge() find and remove rows that are ALREADY
 *               orphaned (deleted a long time ago, before the paths above
 *               existed). Driven by `php artisan smasa:purge-orphans`.
 *
 * Everything is guarded with Schema::hasTable / hasColumn so it is safe to
 * run on any environment, whichever migrations it has.
 */
class OrphanCleanupService
{
    /** school_id sweep never touches these (see SchoolDeletionService::SKIP_TABLES). */
    protected const SCHOOL_SWEEP_SKIP = ['schools', 'school_passwords', 'houses', 'migrations'];

    // ═════════════════════════════════════════════════════════════════════
    //  PREVENTION
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Everything that hangs off ONE examination. Called from
     * Examination::deleting, so every `$exam->delete()` is complete.
     */
    public function purgeExamination(int $examId): void
    {
        // NLSC assessments created under this exam, with their marks and any
        // report-card picks pointing at them.
        $this->purgeAssessments(
            DB::table('nlsc_assessments')->where('examination_id', $examId)->pluck('id')->all()
        );

        foreach (['examination_classes', 'examination_marks', 'examination_subject_settings', 'examination_subject_papers', 'examination_mark_papers', 'report_card_remarks', 'student_discipline_ratings'] as $table) {
            $this->deleteWhere($table, 'examination_id', $examId);
        }

        $this->deleteWhere('student_exam_summaries', 'exam_id', $examId);

        // If this examination IS an O-Level report card, its own parts go too…
        $this->purgeComponents(
            DB::table('olevel_report_card_components')->where('examination_id', $examId)->pluck('id')->all()
        );

        // …and where it was the "exam" part of somebody else's card, that part
        // can no longer be computed (controllers refuse to delete such exams;
        // this is the safety net for every other path).
        $this->purgeComponents(
            DB::table('olevel_report_card_components')->where('source_examination_id', $examId)->pluck('id')->all()
        );
    }

    /** NLSC assessments + their marks + report-card picks. */
    public function purgeAssessments(array $assessmentIds, bool $deleteAssessments = true): void
    {
        $assessmentIds = array_values(array_filter($assessmentIds));

        if (empty($assessmentIds)) {
            return;
        }

        foreach (array_chunk($assessmentIds, 500) as $chunk) {
            $this->deleteWhereIn('nlsc_assessment_marks', 'nlsc_assessment_id', $chunk);
            $this->deleteWhereIn('olevel_report_card_component_assessments', 'nlsc_assessment_id', $chunk);
            if ($deleteAssessments) {
                $this->deleteWhereIn('nlsc_assessments', 'id', $chunk);
            }
        }
    }

    /** Open (free-form) assessments + their marks + report-card picks. */
    public function purgeOpenAssessments(array $assessmentIds, bool $deleteAssessments = true): void
    {
        $assessmentIds = array_values(array_filter($assessmentIds));

        if (empty($assessmentIds) || !Schema::hasTable('open_assessments')) {
            return;
        }

        foreach (array_chunk($assessmentIds, 500) as $chunk) {
            $this->deleteWhereIn('open_assessment_marks', 'open_assessment_id', $chunk);
            $this->deleteWhereIn('olevel_report_card_component_open_assessments', 'open_assessment_id', $chunk);
            if ($deleteAssessments) {
                $this->deleteWhereIn('open_assessments', 'id', $chunk);
            }
        }
    }

    /** O-Level report-card components + their assessment picks. */
    public function purgeComponents(array $componentIds): void
    {
        $componentIds = array_values(array_filter($componentIds));

        if (empty($componentIds)) {
            return;
        }

        $this->deleteWhereIn('olevel_report_card_component_assessments', 'component_id', $componentIds);
        if (Schema::hasTable('olevel_report_card_component_open_assessments')) {
            $this->deleteWhereIn('olevel_report_card_component_open_assessments', 'component_id', $componentIds);
        }
        $this->deleteWhereIn('olevel_report_card_components', 'id', $componentIds);
    }

    /**
     * Report cards (name list) that use this examination as their exam part.
     * Used to refuse deleting an exam a report card still depends on.
     */
    public function reportCardsUsingExam(int $examId): Collection
    {
        if (!Schema::hasTable('olevel_report_card_components')) {
            return collect();
        }

        return DB::table('olevel_report_card_components as c')
            ->join('examinations as e', 'e.id', '=', 'c.examination_id')
            ->where('c.source_examination_id', $examId)
            ->where('e.id', '!=', $examId)
            ->distinct()
            ->pluck('e.exam_name');
    }

    /**
     * Everything class- or stream-scoped that is NOT a student's own data
     * (students go through StudentDeletionService): exam enrolment, marks,
     * subject settings, NLSC assessments, attendance, timetables, pass-slip
     * settings. Must run BEFORE the class_subjects rows are deleted, because
     * timetable slots / attendance point at class_subjects.id.
     *
     * @param string|null $streamId null = every stream of the class
     */
    public function purgeClassStream($schoolId, $classId, ?string $streamId = null): void
    {
        $scope = function ($query) use ($schoolId, $classId, $streamId) {
            $query->where('school_id', $schoolId)->where('class_id', $classId);

            if ($streamId !== null) {
                $query->where('stream_id', $streamId);
            }

            return $query;
        };

        // class_subjects ids first — slots / attendance reference them.
        $classSubjectIds = $scope(DB::table('class_subjects'))->pluck('id')->all();

        if (Schema::hasTable('timetable_slots')) {
            $timetableIds = $scope(DB::table('timetables'))->pluck('id')->all();

            if (!empty($timetableIds)) {
                $this->deleteWhereIn('timetable_slots', 'timetable_id', $timetableIds);
                $this->deleteWhereIn('timetables', 'id', $timetableIds);
            }

            if (!empty($classSubjectIds)) {
                $this->deleteWhereIn('timetable_slots', 'class_subject_id', $classSubjectIds);
            }
        }

        $this->purgeAssessments($scope(DB::table('nlsc_assessments'))->pluck('id')->all());

        foreach (['examination_classes', 'examination_marks', 'examination_subject_settings', 'examination_subject_papers', 'examination_mark_papers', 'student_attendances', 'student_discipline_ratings', 'student_exam_summaries'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'class_id') && Schema::hasColumn($table, 'stream_id')) {
                $scope(DB::table($table))->delete();
            }
        }

        // Pass-slip designs are per class, not per stream.
        if ($streamId === null && Schema::hasTable('passslip_settings')) {
            DB::table('passslip_settings')->where('school_id', $schoolId)->where('class_id', $classId)->delete();
        }
    }

    /**
     * Everything that would be lost if these class_subjects rows were removed
     * (the admin un-ticked the subject on the class). Used to ask for
     * confirmation first. $rows: ClassSubject models / stdClass rows.
     *
     * @return array{marks:int,assessments:int,timetable:int,attendance:int,electives:int,names:array,total:int}
     */
    public function classSubjectImpact($schoolId, Collection $rows): array
    {
        $impact = ['marks' => 0, 'assessments' => 0, 'timetable' => 0, 'attendance' => 0, 'electives' => 0, 'names' => []];

        foreach ($rows as $row) {
            if (empty($row->subject_id) && empty($row->custom_subject_id)) {
                continue;
            }

            $name = $row->display_name ?? \App\Http\Controllers\Helper::classSubjectName($row);
            $before = array_sum(array_intersect_key($impact, array_flip(['marks', 'assessments', 'timetable', 'attendance', 'electives'])));

            if (Schema::hasTable('examination_marks')) {
                $impact['marks'] += $this->subjectScope(DB::table('examination_marks'), $schoolId, $row)->count();
            }

            if (Schema::hasTable('nlsc_assessments') && !empty($row->subject_id)) {
                $impact['assessments'] += $this->assessmentIdsFor($schoolId, $row)->count();
            }

            if (Schema::hasTable('timetable_slots')) {
                $impact['timetable'] += DB::table('timetable_slots')->where('class_subject_id', $row->id)->count();
            }

            if (Schema::hasTable('student_attendances') && Schema::hasColumn('student_attendances', 'class_subject_id')) {
                $impact['attendance'] += DB::table('student_attendances')->where('class_subject_id', $row->id)->count();
            }

            $impact['electives'] += $this->electivesHolding($schoolId, $row)->count();

            $after = array_sum(array_intersect_key($impact, array_flip(['marks', 'assessments', 'timetable', 'attendance', 'electives'])));
            if ($after > $before && $name) {
                $impact['names'][] = $name;
            }
        }

        $impact['total'] = $impact['marks'] + $impact['assessments'] + $impact['timetable'] + $impact['attendance'] + $impact['electives'];

        return $impact;
    }

    /**
     * Remove class_subjects rows AND everything that hangs off them, so an
     * un-ticked subject is gone from marks entry, report cards, exam settings,
     * NLSC assessments, timetables, attendance and students' saved electives.
     * Must run before the rows themselves are deleted (this does that last).
     */
    public function removeClassSubjects($schoolId, Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        foreach ($rows as $row) {
            if (!empty($row->subject_id) || !empty($row->custom_subject_id)) {
                if (Schema::hasTable('examination_marks')) {
                    $this->subjectScope(DB::table('examination_marks'), $schoolId, $row)->delete();
                }

                if (Schema::hasTable('examination_subject_settings')) {
                    $this->subjectScope(DB::table('examination_subject_settings'), $schoolId, $row)->delete();
                }

                foreach (['examination_subject_papers', 'examination_mark_papers'] as $paperTable) {
                    if (Schema::hasTable($paperTable)) {
                        $this->subjectScope(DB::table($paperTable), $schoolId, $row)->delete();
                    }
                }

                if (Schema::hasTable('nlsc_assessments') && !empty($row->subject_id)) {
                    $this->purgeAssessments($this->assessmentIdsFor($schoolId, $row)->all());
                }

                // Take the subject off the students' saved electives, otherwise
                // the electives screen still shows it ticked and the sync
                // would simply put the subject back on the class.
                foreach ($this->electivesHolding($schoolId, $row) as $elective) {
                    $remaining = array_values(array_filter(
                        (array) $elective->elective_subject_ids,
                        fn($id) => (int) $id !== (int) $row->subject_id
                    ));

                    if (empty($remaining)) {
                        $elective->delete();
                    } else {
                        $elective->elective_subject_ids = $remaining;
                        $elective->save();
                    }
                }
            }

            if (Schema::hasTable('timetable_slots')) {
                $this->deleteWhere('timetable_slots', 'class_subject_id', $row->id);
            }

            if (Schema::hasTable('student_attendances')) {
                $this->deleteWhere('student_attendances', 'class_subject_id', $row->id);
            }
        }

        DB::table('class_subjects')->whereIn('id', $rows->pluck('id')->all())->delete();
    }

    /**
     * Everything that hangs off ONE school-added O-Level elective (a
     * school_olevel_electives row, identified by its synthetic id), whichever
     * class it was ever used in:
     *
     *   - the elective's own NLSC catalogue: Topics (Activities of
     *     Integration) + their Competency Areas + Subject Achievements, and
     *     Project Areas + Projects + their Competency Areas
     *   - every NLSC assessment created against that subject, topic,
     *     project or achievement, with its marks and report-card picks
     *   - the "already cloned" log rows, so a later elective can never
     *     inherit them
     *   - entered marks, per-exam subject settings and timetable slots
     *
     * Deleted explicitly, children first, rather than leaning on the FK
     * cascades — those only exist where the migrations ran cleanly.
     * class_subjects rows (and the attendance / timetable rows that point at
     * them) are handled by removeClassSubjects(), which the caller runs first.
     */
    public function purgeSchoolSubject($schoolId, int $subjectId): void
    {
        [$topicIds, $areaIds, $projectIds, $achievementIds] = $this->schoolSubjectCatalogueIds($schoolId, $subjectId);

        $this->purgeAssessments(
            $this->schoolSubjectAssessmentIds($schoolId, $subjectId, $topicIds, $projectIds, $achievementIds)
        );

        // Catalogue, children first.
        $this->deleteWhereIn('school_nlsc_project_competency_areas', 'school_nlsc_project_id', $projectIds);
        $this->deleteWhereIn('school_nlsc_projects', 'id', $projectIds);
        $this->deleteWhereIn('school_nlsc_project_areas', 'id', $areaIds);
        $this->deleteWhereIn('school_nlsc_subject_achievements', 'id', $achievementIds);
        $this->deleteWhereIn('school_nlsc_competency_areas', 'school_nlsc_topic_id', $topicIds);
        $this->deleteWhereIn('school_nlsc_topics', 'id', $topicIds);

        // Without these, a re-created elective that happened to reuse the id
        // would be treated as "already cloned" and open empty.
        foreach (['school_nlsc_clone_log', 'school_nlsc_project_clone_log'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where('school_id', $schoolId)->where('subject_id', $subjectId)->delete();
            }
        }

        // Marks / exam settings entered against the subject, including any
        // left on a class that no longer has a class_subjects row for it.
        foreach (['examination_marks', 'examination_subject_settings', 'examination_subject_papers', 'examination_mark_papers'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id') && Schema::hasColumn($table, 'subject_id')) {
                DB::table($table)->where('school_id', $schoolId)->where('subject_id', $subjectId)->delete();
            }
        }

        // timetable_slots has no school_id — go through this school's timetables.
        if (Schema::hasTable('timetable_slots') && Schema::hasColumn('timetable_slots', 'subject_id') && Schema::hasTable('timetables')) {
            $timetableIds = DB::table('timetables')->where('school_id', $schoolId)->pluck('id')->all();

            foreach (array_chunk($timetableIds, 500) as $chunk) {
                DB::table('timetable_slots')
                    ->whereIn('timetable_id', $chunk)
                    ->where('subject_id', (string) $subjectId)
                    ->delete();
            }
        }
    }

    /**
     * What purgeSchoolSubject() would remove, for the confirmation dialog.
     * Starts from classSubjectImpact() (timetable, attendance, names) and
     * replaces its marks / assessments with the full, class-independent
     * counts, then adds the elective's own NLSC catalogue.
     *
     * @return array{marks:int,assessments:int,timetable:int,attendance:int,electives:int,topics:int,achievements:int,projects:int,names:array,total:int}
     */
    public function schoolSubjectImpact($schoolId, int $subjectId, Collection $classRows): array
    {
        $impact = $this->classSubjectImpact($schoolId, $classRows);

        [$topicIds, , $projectIds, $achievementIds] = $this->schoolSubjectCatalogueIds($schoolId, $subjectId);

        $impact['topics'] = count($topicIds);
        $impact['achievements'] = count($achievementIds);
        $impact['projects'] = count($projectIds);

        $impact['assessments'] = count(
            $this->schoolSubjectAssessmentIds($schoolId, $subjectId, $topicIds, $projectIds, $achievementIds)
        );

        if (Schema::hasTable('examination_marks') && Schema::hasColumn('examination_marks', 'subject_id')) {
            $impact['marks'] = DB::table('examination_marks')
                ->where('school_id', $schoolId)
                ->where('subject_id', $subjectId)
                ->count();
        }

        $impact['total'] = $impact['marks'] + $impact['assessments'] + $impact['timetable'] + $impact['attendance']
            + $impact['electives'] + $impact['topics'] + $impact['achievements'] + $impact['projects'];

        return $impact;
    }

    /** @return array{0:array,1:array,2:array,3:array} topic, project-area, project, achievement ids */
    protected function schoolSubjectCatalogueIds($schoolId, int $subjectId): array
    {
        $ids = function (string $table, array $where, ?string $inColumn = null, array $in = []) {
            if (!Schema::hasTable($table) || ($inColumn !== null && empty($in))) {
                return [];
            }

            $query = DB::table($table)->where($where);

            if ($inColumn !== null) {
                $query->whereIn($inColumn, $in);
            }

            return $query->pluck('id')->all();
        };

        $scope = ['school_id' => $schoolId, 'subject_id' => $subjectId];

        $topicIds = $ids('school_nlsc_topics', $scope);
        $areaIds = $ids('school_nlsc_project_areas', $scope);
        $projectIds = $ids('school_nlsc_projects', [], 'school_nlsc_project_area_id', $areaIds);
        $achievementIds = $ids('school_nlsc_subject_achievements', [], 'school_nlsc_topic_id', $topicIds);

        return [$topicIds, $areaIds, $projectIds, $achievementIds];
    }

    /**
     * Every NLSC assessment belonging to the elective: by subject, or by
     * pointing at one of its topics / projects / achievement statements
     * (covers assessments whose class no longer has the subject).
     */
    protected function schoolSubjectAssessmentIds($schoolId, int $subjectId, array $topicIds, array $projectIds, array $achievementIds): array
    {
        if (!Schema::hasTable('nlsc_assessments')) {
            return [];
        }

        return DB::table('nlsc_assessments')
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($subjectId, $topicIds, $projectIds, $achievementIds) {
                $q->where('subject_id', $subjectId);

                if (!empty($topicIds)) {
                    $q->orWhereIn('nlsc_topic_id', $topicIds);
                }

                if (!empty($projectIds)) {
                    $q->orWhereIn('nlsc_project_id', $projectIds);
                }

                if (!empty($achievementIds) && Schema::hasColumn('nlsc_assessments', 'nlsc_subject_achievement_id')) {
                    $q->orWhereIn('nlsc_subject_achievement_id', $achievementIds);
                }
            })
            ->pluck('id')
            ->all();
    }

    /** Rows of a table (marks / settings) belonging to one class-stream + subject. */
    protected function subjectScope($query, $schoolId, $row)
    {
        $query->where('school_id', $schoolId)
            ->where('class_id', $row->class_id)
            ->where('stream_id', $row->stream_id);

        // Same identity rule as Helper's mark-name resolver: when
        // subject_id is present it is the identity, even if custom_subject_id
        // is also set.
        if (!empty($row->subject_id)) {
            return $query->where('subject_id', $row->subject_id);
        }

        return $query->whereNull('subject_id')->where('custom_subject_id', $row->custom_subject_id);
    }

    protected function assessmentIdsFor($schoolId, $row)
    {
        return DB::table('nlsc_assessments')
            ->where('school_id', $schoolId)
            ->where('class_id', $row->class_id)
            ->where('stream_id', $row->stream_id)
            ->where('subject_id', $row->subject_id)
            ->pluck('id');
    }

    /** Saved O-Level electives of this class-stream's students that include this subject. */
    protected function electivesHolding($schoolId, $row)
    {
        if (($row->subject_type ?? null) !== 'secondary_olevel' || empty($row->subject_id)) {
            return collect();
        }

        $studentIds = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $row->class_id)
            ->where('stream', $row->stream_id)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return collect();
        }

        return \App\Models\StudentOLevelElective::where('school_id', $schoolId)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->filter(fn($e) => in_array((int) $row->subject_id, array_map('intval', (array) $e->elective_subject_ids), true))
            ->values();
    }

    // ═════════════════════════════════════════════════════════════════════
    //  CURE — rows that are already orphaned
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Each rule: [childTable, childColumn, parentTable, parentColumn, action, label]
     * action = 'delete' (child is meaningless without parent) or 'null'
     * (child survives, the dangling reference is cleared).
     *
     * Order matters only loosely: purge() repeats until a pass finds nothing,
     * so removing a parent here that exposes new orphans is picked up again.
     */
    protected function rules(): array
    {
        $rules = [];

        // Examination children
        foreach (['examination_classes', 'examination_marks', 'examination_subject_settings', 'examination_subject_papers', 'examination_mark_papers', 'report_card_remarks', 'student_discipline_ratings', 'nlsc_assessments', 'olevel_report_card_components'] as $t) {
            $rules[] = [$t, 'examination_id', 'examinations', 'id', 'delete', 'deleted examination'];
        }
        $rules[] = ['student_exam_summaries', 'exam_id', 'examinations', 'id', 'delete', 'deleted examination'];
        $rules[] = ['olevel_report_card_components', 'source_examination_id', 'examinations', 'id', 'delete', 'deleted source examination'];

        // Assessment / report-card children
        $rules[] = ['nlsc_assessment_marks', 'nlsc_assessment_id', 'nlsc_assessments', 'id', 'delete', 'deleted assessment'];
        $rules[] = ['olevel_report_card_component_assessments', 'nlsc_assessment_id', 'nlsc_assessments', 'id', 'delete', 'deleted assessment'];
        $rules[] = ['olevel_report_card_component_assessments', 'component_id', 'olevel_report_card_components', 'id', 'delete', 'deleted report-card part'];

        // Student children
        foreach ([
            'examination_marks', 'examination_mark_papers', 'nlsc_assessment_marks', 'student_discipline_ratings', 'report_card_remarks',
            'student_exam_summaries', 'student_attendances', 'fee_payments', 'student_fee_allocations',
            'student_id_cards', 'student_alevel_combinations', 'student_olevel_electives',
        ] as $t) {
            $rules[] = [$t, 'student_id', 'students', 'id', 'delete', 'deleted student'];
        }
        $rules[] = ['student_match_dismissals', 'student_id_one', 'students', 'id', 'delete', 'deleted student'];
        $rules[] = ['student_match_dismissals', 'student_id_two', 'students', 'id', 'delete', 'deleted student'];
        $rules[] = ['students', 'linked_student_id', 'students', 'id', 'null', 'deleted linked student'];

        // Teacher children
        foreach (['teacher_school_roles', 'teacher_id_cards', 'teacher_attendances', 'payroll_slips', 'teacher_salary_structures'] as $t) {
            $rules[] = [$t, 'teacher_id', 'teachers', 'id', 'delete', 'deleted teacher'];
        }
        foreach ([
            ['timetable_slots', 'teacher_id'], ['class_subjects', 'subject_teacher_1'], ['class_subjects', 'subject_teacher_2'],
            ['streams', 'class_teacher'], ['classrooms', 'class_supervisor'],
        ] as [$t, $c]) {
            $rules[] = [$t, $c, 'teachers', 'id', 'null', 'deleted teacher (unassigned)'];
        }

        // Open (free-form) assessments — only once their migration has run.
        if (Schema::hasTable('open_assessments')) {
            $rules[] = ['open_assessment_marks', 'open_assessment_id', 'open_assessments', 'id', 'delete', 'deleted assessment'];
            $rules[] = ['open_assessment_marks', 'student_id', 'students', 'id', 'delete', 'deleted student'];
            $rules[] = ['olevel_report_card_component_open_assessments', 'open_assessment_id', 'open_assessments', 'id', 'delete', 'deleted assessment'];
            $rules[] = ['olevel_report_card_component_open_assessments', 'component_id', 'olevel_report_card_components', 'id', 'delete', 'deleted report-card part'];
        }

        return $rules;
    }

    /**
     * Count orphans without touching anything.
     *
     * @return array<int,array{table:string,column:string,reason:string,count:int}>
     */
    public function scan(?int $schoolId = null): array
    {
        $found = [];

        foreach ($this->allRules() as [$table, $column, $parentTable, $parentColumn, $action, $label]) {
            $count = $this->orphanQuery($table, $column, $parentTable, $parentColumn, $schoolId)?->count() ?? 0;

            if ($count > 0) {
                $found[] = ['table' => $table, 'column' => $column, 'reason' => $label . ($action === 'null' ? ' → reference cleared' : ' → row deleted'), 'count' => $count];
            }
        }

        return $found;
    }

    /**
     * Remove every orphan found by scan(). Repeats until a pass finds nothing
     * (deleting orphaned assessments, say, exposes their orphaned marks).
     *
     * @return array<string,int> "table.column" => rows removed / cleared
     */
    public function purge(?int $schoolId = null): array
    {
        $totals = [];

        DB::transaction(function () use (&$totals, $schoolId) {
            for ($pass = 0; $pass < 6; $pass++) {
                $changed = 0;

                foreach ($this->allRules() as [$table, $column, $parentTable, $parentColumn, $action, $label]) {
                    $query = $this->orphanQuery($table, $column, $parentTable, $parentColumn, $schoolId);

                    if (!$query) {
                        continue;
                    }

                    $n = $action === 'null' ? $query->update([$column => null]) : $query->delete();

                    if ($n > 0) {
                        $totals["{$table}.{$column}"] = ($totals["{$table}.{$column}"] ?? 0) + $n;
                        $changed += $n;
                    }
                }

                if ($changed === 0) {
                    break;
                }
            }
        });

        return $totals;
    }

    /** rules() + the dynamic "school no longer exists" sweep. */
    protected function allRules(): array
    {
        $rules = $this->rules();

        foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
            $table = array_values((array) $row)[0];

            if (!in_array($table, self::SCHOOL_SWEEP_SKIP, true) && Schema::hasColumn($table, 'school_id')) {
                $rules[] = [$table, 'school_id', 'schools', 'id', 'delete', 'deleted school'];
            }
        }

        return $rules;
    }

    protected function orphanQuery(string $table, string $column, string $parentTable, string $parentColumn, ?int $schoolId = null)
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)
            || !Schema::hasTable($parentTable) || !Schema::hasColumn($parentTable, $parentColumn)) {
            return null;
        }

        // A school-scoped run only looks at that school's rows.
        $scoped = $schoolId !== null && $column !== 'school_id' && Schema::hasColumn($table, 'school_id');

        return DB::table($table)
            ->whereNotNull("$table.$column")
            ->when($scoped, fn($q) => $q->where("$table.school_id", $schoolId))
            ->when($schoolId !== null && $column === 'school_id', fn($q) => $q->where("$table.school_id", $schoolId))
            ->whereNotExists(function ($q) use ($table, $column, $parentTable, $parentColumn) {
                $q->select(DB::raw(1))
                    ->from($parentTable)
                    ->whereColumn("$parentTable.$parentColumn", "$table.$column");
            });
    }

    // ═════════════════════════════════════════════════════════════════════

    protected function deleteWhere(string $table, string $column, $value): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            DB::table($table)->where($column, $value)->delete();
        }
    }

    protected function deleteWhereIn(string $table, string $column, array $values): void
    {
        if (!empty($values) && Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            DB::table($table)->whereIn($column, $values)->delete();
        }
    }
}

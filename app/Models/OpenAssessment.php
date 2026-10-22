<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One free-form assessment (quiz, test, assignment ...) a teacher created for a
 * class-subject in a given term and year. It is not tied to any examination.
 * See the create_open_assessments_tables migration for the full picture.
 */
class OpenAssessment extends Model
{
    protected $table = 'open_assessments';

    /** General, free-form types — no catalogue behind them. */
    public const CATEGORIES = ['Quiz', 'Test', 'Assignment', 'Practical', 'Homework', 'Other'];

    /** Curriculum types, set against the school's NLSC catalogue exactly like "Create Assessment". */
    public const NLSC_TYPES = [
        'activities_of_integration' => 'Activities of Integration',
        'projects' => 'Projects',
        'subject_achievement' => 'Subject Achievement',
    ];

    protected $fillable = [
        'school_id',
        'class_id',
        'stream_id',
        'subject_id',
        'custom_subject_id',
        'academic_year',
        'term',
        'title',
        'assessment_type',
        'nlsc_topic_id',
        'nlsc_project_id',
        'nlsc_competency_area_id',
        'nlsc_subject_achievement_id',
        'category',
        'description',
        'assessment_date',
        'max_marks',
        'teacher_id',
        'created_by',
    ];

    protected $casts = [
        'max_marks' => 'float',
        'assessment_date' => 'date',
    ];

    protected static function booted(): void
    {
        // Marks and report-card picks have no foreign key, so clear them with the assessment.
        static::deleting(function (OpenAssessment $assessment) {
            app(\App\Services\OrphanCleanupService::class)->purgeOpenAssessments([(int) $assessment->id], false);
        });
    }

    /**
     * Restrict to one class-subject (class + stream + subject). A pure custom
     * subject has no master subject_id, so it is matched on custom_subject_id.
     */
    public function scopeForSubject($query, $classId, $streamId, $subjectId, $customSubjectId = null)
    {
        $query->where('class_id', $classId)->where('stream_id', (string) $streamId);

        return empty($subjectId)
            ? $query->whereNull('subject_id')->where('custom_subject_id', $customSubjectId)
            : $query->where('subject_id', $subjectId);
    }

    /** "Activities of Integration" / "Quiz" / null — whatever kind of assessment this is. */
    public function typeLabel(): ?string
    {
        return self::NLSC_TYPES[$this->assessment_type] ?? ($this->category ?: null);
    }

    /**
     * What each NLSC-typed assessment is set against, resolved in a few batched
     * queries (no N+1):  [assessmentId => ['matter' => Topic/Project name,
     * 'matter_label' => 'Topic'|'Project', 'competency' => text, 'competency_label' => ...]].
     * General assessments are simply absent from the result.
     */
    public static function nlscDetails($assessments): array
    {
        $assessments = collect($assessments)->filter(fn($a) => !empty($a->assessment_type));

        if ($assessments->isEmpty()) {
            return [];
        }

        $ids = fn($col) => $assessments->pluck($col)->filter()->unique()->values()->all();

        $topics = SchoolNlscTopic::whereIn('id', $ids('nlsc_topic_id'))->pluck('topic_name', 'id');
        $projects = SchoolNlscProject::whereIn('id', $ids('nlsc_project_id'))->pluck('project_name', 'id');
        $areas = SchoolNlscCompetencyArea::whereIn('id', $ids('nlsc_competency_area_id'))->pluck('description', 'id');
        $projectAreas = SchoolNlscProjectCompetencyArea::whereIn('id', $ids('nlsc_competency_area_id'))->pluck('description', 'id');
        $achievements = SchoolNlscSubjectAchievement::whereIn('id', $ids('nlsc_subject_achievement_id'))->pluck('achievement_text', 'id');

        $out = [];

        foreach ($assessments as $a) {
            if ($a->assessment_type === 'projects') {
                $out[$a->id] = [
                    'matter_label' => 'Project',
                    'matter' => $projects[$a->nlsc_project_id] ?? null,
                    'competency_label' => 'Competency area',
                    'competency' => $projectAreas[$a->nlsc_competency_area_id] ?? null,
                ];
            } elseif ($a->assessment_type === 'activities_of_integration') {
                $out[$a->id] = [
                    'matter_label' => 'Topic',
                    'matter' => $topics[$a->nlsc_topic_id] ?? null,
                    'competency_label' => 'Competency area',
                    'competency' => $areas[$a->nlsc_competency_area_id] ?? null,
                ];
            } else { // subject_achievement
                $out[$a->id] = [
                    'matter_label' => 'Topic',
                    'matter' => $topics[$a->nlsc_topic_id] ?? null,
                    'competency_label' => 'Achievement statement',
                    'competency' => $achievements[$a->nlsc_subject_achievement_id] ?? null,
                ];
            }
        }

        return $out;
    }

    public function marks()
    {
        return $this->hasMany(OpenAssessmentMark::class, 'open_assessment_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}

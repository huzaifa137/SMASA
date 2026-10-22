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

    public const CATEGORIES = ['Quiz', 'Test', 'Assignment', 'Project', 'Practical', 'Homework', 'Other'];

    protected $fillable = [
        'school_id',
        'class_id',
        'stream_id',
        'subject_id',
        'custom_subject_id',
        'academic_year',
        'term',
        'title',
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

    public function marks()
    {
        return $this->hasMany(OpenAssessmentMark::class, 'open_assessment_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}

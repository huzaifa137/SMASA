<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * A standard paper attached to ONE class-stream's subject, with the optional
 * teacher(s) who mark it there. No teacher = the subject's own teachers mark
 * it (the behaviour before paper teachers existed).
 */
class ClassSubjectPaper extends Model
{
    use BelongsToSchool;

    protected $table = 'class_subject_papers';

    protected $fillable = [
        'school_id',
        'class_id',
        'stream_id',
        'subject_id',
        'custom_subject_id',
        'subject_paper_id',
        'teacher_1_id',
        'teacher_2_id',
        'created_by',
        'updated_by',
    ];

    public function paper()
    {
        return $this->belongsTo(SubjectPaper::class, 'subject_paper_id');
    }

    /** Teacher ids assigned to this paper here (0, 1 or 2 of them). */
    public function teacherIds(): array
    {
        return array_values(array_filter([$this->teacher_1_id, $this->teacher_2_id]));
    }
}

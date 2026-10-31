<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * One paper (P1, P2, ...) of a subject in ONE examination, and what it is
 * marked out of. See the create_examination_papers_tables migration.
 */
class ExaminationSubjectPaper extends Model
{
    use BelongsToSchool;

    protected $table = 'examination_subject_papers';

    protected $fillable = [
        'examination_id',
        'school_id',
        'class_id',
        'stream_id',
        'subject_id',
        'custom_subject_id',
        'paper_number',
        'label',
        'max_score',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'max_score' => 'float',
        'paper_number' => 'integer',
    ];

    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?: 'Paper ' . $this->paper_number;
    }
}

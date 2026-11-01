<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * A STANDARD paper (P1, P2, ...) of a subject, defined once for the school.
 * See the create_subject_papers_tables migration and
 * App\Services\SubjectPaperService.
 */
class SubjectPaper extends Model
{
    use BelongsToSchool;

    protected $table = 'subject_papers';

    protected $fillable = [
        'school_id',
        'subject_id',
        'custom_subject_id',
        'paper_number',
        'label',
        'paper_type',
        'default_max_score',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'paper_number' => 'integer',
        'default_max_score' => 'float',
    ];

    public function attachments()
    {
        return $this->hasMany(ClassSubjectPaper::class, 'subject_paper_id');
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?: 'Paper ' . $this->paper_number;
    }
}

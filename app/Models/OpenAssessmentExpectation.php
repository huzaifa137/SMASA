<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * One target the school sets for a term: "each class-subject must have N
 * assessments of this type". See the create_open_assessment_expectations_table
 * migration and App\Services\AssessmentExpectationService.
 */
class OpenAssessmentExpectation extends Model
{
    use BelongsToSchool;

    protected $table = 'open_assessment_expectations';

    protected $fillable = [
        'school_id',
        'academic_year',
        'term',
        'class_id',
        'subject_id',
        'custom_subject_id',
        'type_key',
        'required_count',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'required_count' => 'integer',
        'due_date' => 'date',
    ];
}

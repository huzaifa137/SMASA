<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentALevelCombination extends Model
{
    protected $table = 'student_alevel_combinations';

    protected $fillable = [
        'school_id',
        'student_id',
        'principal_subject_ids',
        'subsidiary_subject_id',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'principal_subject_ids' => 'array',
        'entered_at' => 'datetime',
    ];

    /**
     * Combinations that include this subject as a principal or the
     * subsidiary. principal_subject_ids holds ids as JSON strings
     * (["340","9000000007"]) or numbers depending on the save path, and
     * JSON_CONTAINS is type-strict — so check both forms.
     */
    public function scopeHoldingSubject($query, $subjectId)
    {
        return $query->where(function ($q) use ($subjectId) {
            $q->whereJsonContains('principal_subject_ids', (int) $subjectId)
                ->orWhereJsonContains('principal_subject_ids', (string) $subjectId)
                ->orWhere('subsidiary_subject_id', (int) $subjectId);
        });
    }
}

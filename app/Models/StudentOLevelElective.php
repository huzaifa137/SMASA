<?php

namespace App\Models;


use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class StudentOLevelElective extends Model
{
    use BelongsToSchool;

    protected $table = 'student_olevel_electives';

    protected $fillable = [
        'school_id',
        'student_id',
        'elective_subject_ids',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'elective_subject_ids' => 'array',
        'entered_at' => 'datetime',
    ];

    /**
     * Students whose saved electives include this subject id.
     *
     * elective_subject_ids is written from the browser, so ids are stored as
     * JSON strings (["9500000001"]) by one path and as numbers by another.
     * JSON_CONTAINS is type-strict, so a number never matches a string —
     * check both forms or an "in use" guard silently never fires.
     */
    public function scopeHoldingSubject($query, $subjectId)
    {
        return $query->where(function ($q) use ($subjectId) {
            $q->whereJsonContains('elective_subject_ids', (int) $subjectId)
                ->orWhereJsonContains('elective_subject_ids', (string) $subjectId);
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single school's own addition to the O-Level elective list — see the
 * create_school_olevel_electives_table migration for why this isn't just
 * another custom_subjects row.
 *
 * Rows here are given a synthetic id — ID_OFFSET + this row's own id —
 * wherever they're mixed in with real master_datas md_id values
 * (student_olevel_electives.elective_subject_ids is a JSON array of plain
 * integers, so this keeps everything a plain integer end-to-end instead of
 * introducing a second value type). Uses a different offset from
 * SchoolALevelSubject::ID_OFFSET so the two id spaces never collide even
 * if a value from one were ever accidentally compared against the other.
 */
class SchoolOLevelElective extends Model
{
    protected $table = 'school_olevel_electives';

    public const ID_OFFSET = 9_500_000_000;

    protected $fillable = [
        'school_id',
        'subject_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * This row's synthetic, offset id for use as an
     * elective_subject_ids value.
     */
    public function syntheticId(): int
    {
        return self::ID_OFFSET + $this->id;
    }

    /**
     * True if a given elective_subject_ids value refers to a
     * school-scoped subject rather than a real master_datas row.
     */
    public static function isSyntheticId($value): bool
    {
        return is_numeric($value) && (int) $value >= self::ID_OFFSET;
    }

    public static function realIdFromSynthetic($value): int
    {
        return (int) $value - self::ID_OFFSET;
    }
}
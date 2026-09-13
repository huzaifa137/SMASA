<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single school's own addition to the A-Level principal/subsidiary
 * subject list — see the create_school_alevel_subjects_table migration
 * for why this isn't just another custom_subjects row.
 *
 * Rows here are given a synthetic id — SCHOOL_SUBJECT_ID_OFFSET + this
 * row's own id — wherever they're mixed in with real master_datas md_id
 * values (principal_subject_ids / subsidiary_subject_id on
 * student_alevel_combinations are both plain integer columns, so this
 * keeps everything a plain integer end-to-end instead of introducing a
 * second value type). master_datas realistically never gets anywhere
 * near this range, so the two id spaces never collide.
 */
class SchoolALevelSubject extends Model
{
    /**
     * Explicitly specify the table name.
     *
     * Without this, Laravel/Eloquent derives the table name from the
     * model class "SchoolALevelSubject" as:
     *
     *     school_a_level_subjects
     *
     * But our migration intentionally creates:
     *
     *     school_alevel_subjects
     *
     * Therefore the table name must be explicitly defined here.
     */
    protected $table = 'school_alevel_subjects';

    public const ID_OFFSET = 9_000_000_000;

    protected $fillable = [
        'school_id',
        'subject_name',
        'subject_group',
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
     * This row's synthetic, offset id for use as a
     * principal_subject_ids / subsidiary_subject_id value.
     */
    public function syntheticId(): int
    {
        return self::ID_OFFSET + $this->id;
    }

    /**
     * True if a given principal_subject_ids/subsidiary_subject_id value
     * refers to a school-scoped subject rather than a real master_datas row.
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
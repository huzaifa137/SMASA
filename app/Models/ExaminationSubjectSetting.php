<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-examination subject exceptions (see the migration for the full
 * rules). Only rows that deviate from the default ("sat and shown") are
 * stored, so an examination with no rows behaves exactly as it always did.
 */
class ExaminationSubjectSetting extends Model
{
    protected $table = 'examination_subject_settings';

    protected $fillable = [
        'examination_id',
        'school_id',
        'class_id',
        'stream_id',
        'subject_id',
        'custom_subject_id',
        'marks_entry_enabled',
        'show_on_report',
    ];

    protected $casts = [
        'marks_entry_enabled' => 'boolean',
        'show_on_report' => 'boolean',
    ];

    /** Per-request cache: examination_id => [key => row]. */
    protected static array $cache = [];

    /**
     * Identity of one subject inside one class-stream. Accepts any row
     * with class_id / stream_id / subject_id / custom_subject_id
     * (class_subjects, examination_marks, or this model).
     */
    public static function keyFor($row): string
    {
        return ($row->class_id ?? '0') . '|'
            . (string) ($row->stream_id ?? '') . '|'
            . ($row->subject_id ?: '0') . '|'
            . ($row->custom_subject_id ?: '0');
    }

    /** All exception rows for an exam, keyed by keyFor(). */
    public static function forExam($examId): array
    {
        $examId = (int) $examId;

        if (!isset(static::$cache[$examId])) {
            static::$cache[$examId] = static::where('examination_id', $examId)
                ->get()
                ->keyBy(fn($r) => static::keyFor($r))
                ->all();
        }

        return static::$cache[$examId];
    }

    public static function forgetCache($examId = null): void
    {
        if ($examId === null) {
            static::$cache = [];
        } else {
            unset(static::$cache[(int) $examId]);
        }
    }

    /** Is this subject being sat (marks entry open) in this exam? */
    public static function marksEntryOpen($examId, $row): bool
    {
        $setting = static::forExam($examId)[static::keyFor($row)] ?? null;

        return $setting ? (bool) $setting->marks_entry_enabled : true;
    }

    /** Should this subject appear on the pass slip / report card? */
    public static function shownOnReport($examId, $row): bool
    {
        $setting = static::forExam($examId)[static::keyFor($row)] ?? null;

        return $setting ? (bool) $setting->show_on_report : true;
    }

    /**
     * Drop every class_subjects row that is not being sat in this exam.
     * Works on a query-builder Collection of class_subjects rows.
     */
    public static function filterSat($examId, $classSubjects)
    {
        if (empty(static::forExam($examId))) {
            return $classSubjects;
        }

        return $classSubjects
            ->filter(fn($cs) => static::marksEntryOpen($examId, $cs))
            ->values();
    }
}

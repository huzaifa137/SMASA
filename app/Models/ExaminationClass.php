<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminationClass extends Model
{
    protected $fillable = [
        'examination_id',
        'class_id',
        'stream_id',
        'school_id',
        'results_released_at',
        'released_by',
        'grading_scheme_id',
    ];

    protected $casts = [
        'results_released_at' => 'datetime',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }

    public function gradingScheme()
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    /**
     * Check if this class's results have been released.
     */
    public function isReleased(): bool
    {
        return !is_null($this->results_released_at);
    }

    /**
     * Resolve the grading bands for this specific class in this exam.
     * Falls back to the parent examination's default scheme if none is set.
     */
    public function resolvedGradingBands()
    {
        $scheme = $this->resolvedGradingScheme();
        return $scheme ? $scheme->bands : collect();
    }

    /**
     * Same resolution as resolvedGradingBands(), but returns the scheme
     * itself — needed for anything beyond grade bands, e.g. divisionFor()
     * for Aggregate/Division reporting. Falls back to the parent exam's
     * default scheme resolution (including the school-default fallback)
     * if this class has no scheme of its own.
     */
    public function resolvedGradingScheme()
    {
        // 1. A scheme explicitly chosen for this class always wins.
        if ($this->gradingScheme) {
            return $this->gradingScheme;
        }

        // 2. Senior 5 / Senior 6 (any stream) -> the school's UACE scheme.
        if (\App\Services\GradingSchemeDefaults::isALevelClass($this->class_id)) {
            $uace = \App\Services\GradingSchemeDefaults::uaceSchemeFor(
                $this->school_id ?: $this->examination?->school_id
            );
            if ($uace) {
                return $uace;
            }
        }

        // 3. Otherwise the exam's own default.
        return $this->examination?->resolvedGradingScheme();
    }
}

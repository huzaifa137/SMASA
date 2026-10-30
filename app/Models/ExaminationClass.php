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
                $this->school_id ?: $this->owningExamination()?->school_id
            );
            if ($uace) {
                return $uace;
            }
        }

        // 3. Otherwise the exam's own default.
        return $this->owningExamination()?->resolvedGradingScheme();
    }

    /**
     * The parent examination for GRADING purposes, including report cards.
     *
     * The plain `examination` relation goes through Examination's
     * ExcludeReportCardsScope, so for an O-Level report card
     * (o_level_mode = 'report_card') it returns null. That made
     * resolvedGradingScheme() fall through to null -> no grade bands ->
     * every Grade cell on the Senior pass slip rendered as "—".
     *
     * The relation itself is intentionally left untouched (other screens rely
     * on report cards staying hidden through it); only grading resolution
     * opts back in, exactly like the pass-slip screens do.
     */
    protected function owningExamination(): ?Examination
    {
        if (!$this->exists || !$this->examination_id) {
            return null;
        }

        if (!$this->relationLoaded('owningExamination')) {
            $this->setRelation(
                'owningExamination',
                Examination::withReportCards()->find($this->examination_id)
            );
        }

        return $this->getRelation('owningExamination');
    }
}

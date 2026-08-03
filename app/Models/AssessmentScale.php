<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'min_score',
        'max_score',
        'allow_custom_score',
        'grade_mode',
        'grading_scheme_id',
        'is_default',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'min_score' => 'float',
        'max_score' => 'float',
        'allow_custom_score' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function presets()
    {
        return $this->hasMany(AssessmentScalePreset::class)->orderBy('sort_order')->orderBy('score');
    }

    public function gradingScheme()
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'assessment_scale_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeAvailableTo($query, $schoolId)
    {
        return $query->forSchool($schoolId)->where('is_active', true);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    /**
     * A linked grading scheme converts score/max_score into a percentage
     * and looks that up against fixed 0-100 bands. That conversion only
     * means anything if the score has a real ceiling — so a scale that
     * allows custom (unbounded) scores can never meaningfully use a
     * linked grading scheme, even if grade_mode/grading_scheme_id were
     * somehow left set on the row. Encoding the rule here (rather than
     * only at validation time) guarantees every consumer of this method
     * — marks entry, pass slips, reports — stays consistent even if the
     * data itself is in an old/bad state.
     */
    public function usesLinkedGrading(): bool
    {
        return $this->grade_mode === 'linked_grading_scheme'
            && $this->grading_scheme_id
            && !$this->allow_custom_score;
    }

    /**
     * Find the preset matching a given score exactly. Used to auto-fill
     * the comment box and remark when a teacher picks (or types a score
     * that matches) a system comment.
     */
    /**
     * Find the preset whose band (min_score-max_score) covers a given
     * score — works for both single-value presets and true ranges
     * (e.g. a preset spanning 1-39).
     */
    public function presetForScore($score)
    {
        return $this->presets->first(fn($p) => $p->coversScore($score));
    }

    /**
     * Overall remark for an average score across several subjects on this
     * scale. Prefers a preset whose band actually contains the average;
     * falls back to the closest band by distance to its midpoint when the
     * average doesn't land inside any of them (e.g. it sits in a gap
     * between two configured ranges).
     */
    public function remarkForAverage(float $average): string
    {
        if ($this->presets->isEmpty()) {
            return '—';
        }

        $covering = $this->presets->first(fn($p) => $p->coversScore($average));
        if ($covering) {
            return $covering->remark ?? $covering->label;
        }

        $nearest = $this->presets->sortBy(function ($p) use ($average) {
            $min = $p->min_score ?? $p->score;
            $max = $p->max_score ?? $p->score;
            $midpoint = ($min + $max) / 2;
            return abs($midpoint - $average);
        })->first();

        return $nearest->remark ?? $nearest->label;
    }

    /**
     * Clamp / validate a raw posted score against this scale's rules.
     * Returns null if the score should be rejected outright (blank).
     */
    public function normalizeScore($rawScore)
    {
        if ($rawScore === null || $rawScore === '') {
            return null;
        }

        $score = (float) $rawScore;

        if ($this->allow_custom_score) {
            // Free entry: only guard against negative numbers.
            return max(0, $score);
        }

        // Snap to the configured range, matching the historic Early Years
        // behaviour of rounding to the nearest whole preset step.
        $score = max((float) $this->min_score, min((float) $this->max_score, $score));

        return $score;
    }
}
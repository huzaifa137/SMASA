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
     * Smallest top score for which a scale still counts as a "marks" scale.
     * Scales such as Early Years (1-3) are a handful of fixed steps: a score
     * like 2.4 means nothing on them, so they cannot be built from papers.
     * Anything 10 or wider (1-100, 0-50, 1-10...) is a real score range - the
     * mark is a number the teacher types, and the scale only turns it into a
     * comment (and optionally a letter grade) afterwards.
     */
    public const PAPER_MIN_TOP_SCORE = 10;

    /**
     * Can a subject graded on this scale be split into papers (P1, P2 ...)?
     *
     * Yes for range-based scales: the paper marks are combined into ONE
     * score on the scale's own range and the scale then picks the comment /
     * grade for that score exactly as if the teacher had typed it. No for
     * tiny fixed-step scales, where there is no number to derive.
     */
    public function supportsPapers(): bool
    {
        return (float) $this->max_score >= self::PAPER_MIN_TOP_SCORE
            && (float) $this->max_score > (float) $this->min_score;
    }

    /**
     * Turn "what share of the paper maximums did the student score" (0-1)
     * into this scale's result: the score on the scale, the matching
     * system comment, remark and (for linked scales) letter grade/points.
     * Mirrors what ExaminationController::saveMarks() does for a typed score.
     *
     * @return array ['score' => float, 'label' => ?string, 'remark' => ?string, 'grade' => ?string, 'points' => mixed]
     */
    public function resultForRatio(float $ratio): array
    {
        $ratio = max(0.0, min(1.0, $ratio));
        $raw = $ratio * (float) $this->max_score;

        $score = $this->allow_custom_score
            ? round($raw, 2)
            : max((float) $this->min_score, min((float) $this->max_score, round($raw)));

        $preset = $this->presetForScore($score);
        $remark = $preset->remark ?? null;
        $grade = null;
        $points = null;

        if ($this->usesLinkedGrading()) {
            $percentage = $this->max_score > 0 ? round(($score / $this->max_score) * 100, 4) : 0;
            $band = $this->gradingScheme->bands->first(
                fn($g) => $percentage >= $g->min_mark && $percentage <= $g->max_mark
            );

            if ($band) {
                $grade = $band->grade;
                $points = $band->points;
                $remark = $remark ?? $band->remark;
            }
        }

        return [
            'score' => $score,
            'label' => $preset->label ?? null,
            'remark' => $remark,
            'grade' => $grade,
            'points' => $points,
        ];
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
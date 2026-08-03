<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentScalePreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_scale_id',
        'score',
        'min_score',
        'max_score',
        'label',
        'remark',
        'sort_order',
    ];

    protected $casts = [
        'score' => 'float',
        'min_score' => 'float',
        'max_score' => 'float',
        'sort_order' => 'integer',
    ];

    public function scale()
    {
        return $this->belongsTo(AssessmentScale::class, 'assessment_scale_id');
    }

    /**
     * Whether this preset covers more than one value (e.g. 1-39) rather
     * than a single exact score (e.g. 5). min_score/max_score are the
     * source of truth; score/min_score/max_score are kept equal for a
     * single-value preset.
     */
    public function isRange(): bool
    {
        $min = $this->min_score ?? $this->score;
        $max = $this->max_score ?? $this->score;

        return $max > $min;
    }

    /**
     * Does this preset's band contain the given score? Used to auto-match
     * a typed/saved score to its System Comment, for both single-value
     * presets and true ranges.
     */
    public function coversScore($score): bool
    {
        if ($score === null || $score === '') {
            return false;
        }

        $score = (float) $score;
        $min = $this->min_score ?? $this->score;
        $max = $this->max_score ?? $this->score;

        return $score >= $min && $score <= $max;
    }

    /**
     * Human-readable score/range for display, e.g. "5" for a single value
     * or "1 - 39" for a band. Trims trailing zeros so whole numbers don't
     * show as "5.00".
     */
    public function rangeLabel(): string
    {
        $min = $this->min_score ?? $this->score;
        $max = $this->max_score ?? $this->score;

        if (!$this->isRange()) {
            return $this->formatScoreValue($min);
        }

        return $this->formatScoreValue($min) . ' - ' . $this->formatScoreValue($max);
    }

    private function formatScoreValue($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    }
}

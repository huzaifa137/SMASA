<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'total_marks',
        'pass_mark',
        'is_default',
        'is_active',
        'ungraded_on_fail',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
        'ungraded_on_fail' => 'boolean',
        'total_marks' => 'integer',
        'pass_mark'   => 'integer',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function bands()
    {
        return $this->hasMany(GradingScale::class, 'grading_scheme_id')
            ->orderBy('sort_order')
            ->orderByDesc('min_mark');
    }

    /**
     * Aggregate (sum of grade points across a student's "counts toward
     * aggregate" subjects) → Division bands, e.g. PLE's 4-12 => Division 1.
     * A scheme with none of these simply doesn't support Division — see
     * divisionFor() below, and hasDivisionBands().
     */
    public function divisionBands()
    {
        return $this->hasMany(DivisionBand::class, 'grading_scheme_id')
            ->orderBy('sort_order')
            ->orderBy('min_aggregate');
    }

    public function examinations()
    {
        return $this->hasMany(Examination::class, 'grading_scheme_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    /**
     * Grading schemes are per-school: this school's own schemes, full stop.
     * There is no global/system fallback — every school owns and manages
     * its own set from the moment it's created.
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Same as forSchool(), restricted to schemes currently switched on —
     * i.e. the ones selectable when setting up a new examination.
     */
    public function scopeAvailableTo($query, $schoolId)
    {
        return $query->forSchool($schoolId)->where('is_active', true);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    /**
     * Resolve the grade band for a given percentage (0-100).
     */
    public function bandFor(float $percentage)
    {
        return $this->bands->first(
            fn($b) => $percentage >= $b->min_mark && $percentage <= $b->max_mark
        );
    }

    /**
     * Whether this scheme offers Division reporting at all. Schemes with
     * no division bands configured (the overwhelming majority — Division
     * is a PLE-specific convention, not something every school's grading
     * scheme needs) simply don't show Aggregate/Division on the pass
     * slip; nothing else about them changes.
     */
    public function hasDivisionBands(): bool
    {
        return $this->divisionBands->isNotEmpty();
    }

    /**
     * Map an aggregate (sum of grade points across a student's
     * "counts toward aggregate" subjects) to this scheme's Division
     * label, honouring its own ungraded_on_fail setting.
     *
     * Returns null when the scheme has no division bands at all (Division
     * isn't applicable to this scheme) or when the aggregate falls
     * outside every configured band (a genuine gap in the school's own
     * setup — shown as such rather than guessed at).
     */
    public function divisionFor(?int $aggregate, bool $hasFail = false): ?string
    {
        if ($aggregate === null || $this->divisionBands->isEmpty()) {
            return null;
        }

        if ($hasFail && $this->ungraded_on_fail) {
            $ungraded = $this->divisionBands->first(
                fn($b) => str_contains(strtolower($b->division), 'ungraded')
                    || strtolower($b->division) === 'u'
            );
            if ($ungraded) {
                return $ungraded->division;
            }
        }

        $band = $this->divisionBands->first(
            fn($b) => $aggregate >= $b->min_aggregate && $aggregate <= $b->max_aggregate
        );

        return $band?->division;
    }

    /**
     * Basic sanity check for division bands, mirroring validateBands()
     * above but without the "must cover the whole range" requirement —
     * the valid aggregate range depends entirely on how many subjects a
     * school marks as counting toward it (4 subjects on a 9-point scale
     * spans 4-36; 5 subjects spans 5-45; etc.), so there's no single
     * universal range to enforce coverage against. Overlaps and
     * min > max are still hard errors.
     */
    public function validateDivisionBands(): array
    {
        $problems = [];
        $bands = $this->divisionBands()->get();

        foreach ($bands as $band) {
            if ($band->min_aggregate > $band->max_aggregate) {
                $problems[] = "{$band->division}: min aggregate cannot be greater than max aggregate.";
            }
        }

        foreach ($bands as $a) {
            foreach ($bands as $b) {
                if ($a->id === $b->id) {
                    continue;
                }
                if ($a->min_aggregate <= $b->max_aggregate && $a->max_aggregate >= $b->min_aggregate) {
                    $problems[] = "{$a->division} and {$b->division} overlap.";
                }
            }
        }

        return array_unique($problems);
    }

    /**
     * Basic sanity check: bands shouldn't overlap, should be within 0-100,
     * and together must cover the *entire* 0-100% range with no gaps.
     *
     * A gap left uncovered means a real student score can land between two
     * bands and end up with no grade at all — so this is treated as a hard
     * validation failure, not a warning (see saveMarks() in
     * ExaminationController for the runtime safety net that also guards
     * against this for schemes that predate this check).
     *
     * Returns an array of human-readable problems (empty = valid).
     */
    public function validateBands(): array
    {
        $problems = [];
        $bands = $this->bands()->orderByDesc('min_mark')->get();

        foreach ($bands as $band) {
            if ($band->min_mark > $band->max_mark) {
                $problems[] = "{$band->grade}: min mark cannot be greater than max mark.";
            }
            if ($band->min_mark < 0 || $band->max_mark > 100) {
                $problems[] = "{$band->grade}: bands must be within 0-100%.";
            }
        }

        foreach ($bands as $a) {
            foreach ($bands as $b) {
                if ($a->id === $b->id) {
                    continue;
                }
                if ($a->min_mark <= $b->max_mark && $a->max_mark >= $b->min_mark) {
                    $problems[] = "{$a->grade} and {$b->grade} overlap.";
                }
            }
        }

        return array_unique(array_merge($problems, $this->coverageProblems($bands)));
    }

    /**
     * Checks the bands, taken together, span 0% through 100% with no gap
     * wider than 1 point. A 1-point gap is allowed because whole-number
     * cutoffs (e.g. one band ending at "...39" and the next starting at
     * "40...") are the normal convention, not a real gap — anything wider
     * means a stretch of possible scores has no grade defined for it.
     */
    private function coverageProblems($bands): array
    {
        if ($bands->isEmpty()) {
            return ['At least one grade band is required.'];
        }

        $epsilon = 0.01;
        $sorted = $bands->sortBy('min_mark')->values();
        $problems = [];

        $lowest = $sorted->first();
        if ($lowest->min_mark > 0 + $epsilon) {
            $problems[] = 'Grade bands must start at 0% — missing coverage from 0% to '
                . self::trimPercent($lowest->min_mark) . '%.';
        }

        $highest = $sorted->last();
        if ($highest->max_mark < 100 - $epsilon) {
            $problems[] = 'Grade bands must reach 100% — missing coverage from '
                . self::trimPercent($highest->max_mark) . '% to 100%.';
        }

        for ($i = 0; $i < $sorted->count() - 1; $i++) {
            $current = $sorted[$i];
            $next = $sorted[$i + 1];
            $gap = $next->min_mark - $current->max_mark;

            if ($gap > 1 + $epsilon) {
                $problems[] = 'Missing coverage between '
                    . self::trimPercent($current->max_mark) . '% and '
                    . self::trimPercent($next->min_mark) . '% (between "'
                    . $current->grade . '" and "' . $next->grade . '").';
            }
        }

        return $problems;
    }

    private static function trimPercent(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2), '0'), '.');
    }
}
<?php

namespace App\Support;

use App\Models\AcademicTerm;
use App\Models\BillingTerm;
use App\Models\SalesTarget;
use Illuminate\Support\Carbon;

/**
 * The window every Sales Team figure is measured over.
 *
 *   term:12     a platform term from Academic Years & Terms (its dates)
 *   month       this calendar month
 *   last30      the last 30 days
 *   year        this calendar year
 *   all         since the beginning
 *   custom      ?from=YYYY-MM-DD&to=YYYY-MM-DD
 *
 * When the period is a term that has a billing term, invoice figures
 * ("expected") use that billing term's invoices; otherwise they use the
 * invoices issued inside the window.
 */
final class SalesPeriod
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly ?int $academicTermId = null,
        public readonly bool $allTime = false,
    ) {
    }

    // ── Factories ───────────────────────────────────────────────────────

    public static function resolve(?string $key, ?string $from = null, ?string $to = null, ?Carbon $today = null): self
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $key = trim((string) $key);

        if (str_starts_with($key, 'term:')) {
            $term = AcademicTerm::with('year')->find((int) substr($key, 5));
            if ($term) {
                return self::fromTerm($term);
            }
        }

        switch ($key) {
            case 'month':
                return new self('month', 'This month', $today->copy()->startOfMonth(), $today->copy()->endOfMonth());
            case 'last30':
                return new self('last30', 'Last 30 days', $today->copy()->subDays(29), $today->copy()->endOfDay());
            case 'year':
                return new self('year', 'This year', $today->copy()->startOfYear(), $today->copy()->endOfYear());
            case 'all':
                return new self('all', 'All time', Carbon::create(2000, 1, 1)->startOfDay(), $today->copy()->endOfDay(), null, true);
            case 'custom':
                $start = self::parseDate($from);
                $end = self::parseDate($to);
                if ($start && $end && $start->lte($end)) {
                    return new self('custom', $start->format('j M Y') . ' – ' . $end->format('j M Y'), $start->startOfDay(), $end->endOfDay());
                }
                break;
        }

        // Default: the platform's active term, else the most recent term, else this year.
        $term = AcademicTerm::with('year')->where('is_active', true)->first()
            ?? AcademicTerm::with('year')->orderByDesc('start_date')->first();

        return $term ? self::fromTerm($term) : self::resolve('year', null, null, $today);
    }

    public static function fromTerm(AcademicTerm $term): self
    {
        return new self(
            'term:' . $term->id,
            $term->name,
            Carbon::parse($term->start_date)->startOfDay(),
            Carbon::parse($term->end_date)->endOfDay(),
            (int) $term->id
        );
    }

    public static function fromTarget(SalesTarget $t): self
    {
        return new self(
            $t->academic_term_id ? 'term:' . $t->academic_term_id : 'custom',
            $t->period_label,
            Carbon::parse($t->period_start)->startOfDay(),
            Carbon::parse($t->period_end)->endOfDay(),
            $t->academic_term_id ? (int) $t->academic_term_id : null
        );
    }

    /** Choices for the period dropdown (most recent terms first). */
    public static function options(int $termLimit = 8): array
    {
        $options = [];

        foreach (AcademicTerm::with('year')->orderByDesc('start_date')->limit($termLimit)->get() as $term) {
            $options['term:' . $term->id] = $term->name . ($term->is_active ? ' (current)' : '');
        }

        return $options + [
            'month' => 'This month',
            'last30' => 'Last 30 days',
            'year' => 'This year',
            'all' => 'All time',
            'custom' => 'Custom range…',
        ];
    }

    // ── Behaviour ───────────────────────────────────────────────────────

    /** The billing term whose invoices make up "expected" for this period, if any. */
    public function billingTermId(): ?int
    {
        if (!$this->academicTermId) {
            return null;
        }

        $term = AcademicTerm::find($this->academicTermId);

        return $term ? BillingTerm::where('academic_year_id', $term->academic_year_id)->where('term', $term->term)->value('id') : null;
    }

    public function startDate(): string
    {
        return $this->start->toDateString();
    }

    public function endDate(): string
    {
        return $this->end->toDateString();
    }

    public function totalDays(): int
    {
        return max(1, (int) $this->start->copy()->startOfDay()->diffInDays($this->end->copy()->startOfDay()) + 1);
    }

    /** How much of the period has passed: 0.0 before it starts, 1.0 once it is over. */
    public function elapsedFraction(?Carbon $today = null): float
    {
        return self::elapsed($this->start, $this->end, $today);
    }

    public static function elapsed(Carbon $start, Carbon $end, ?Carbon $today = null): float
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->startOfDay();

        if ($today->lt($start)) {
            return 0.0;
        }

        if ($today->gte($end)) {
            return 1.0;
        }

        $total = max(1, $start->diffInDays($end) + 1);

        return min(1.0, (($start->diffInDays($today)) + 1) / $total);
    }

    public function isOver(?Carbon $today = null): bool
    {
        return ($today ?? now())->copy()->startOfDay()->gt($this->end->copy()->startOfDay());
    }

    public function daysLeft(?Carbon $today = null): int
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return max(0, (int) $today->diffInDays($this->end->copy()->startOfDay(), false));
    }

    public function query(): array
    {
        return $this->key === 'custom'
            ? ['period' => 'custom', 'from' => $this->startDate(), 'to' => $this->endDate()]
            : ['period' => $this->key];
    }

    private static function parseDate(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}

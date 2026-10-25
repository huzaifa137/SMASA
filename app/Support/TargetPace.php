<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Judges a target against the time elapsed. Pure logic: no database.
 *
 *   achieved   100% or more
 *   missed     period over, below 100%
 *   upcoming   period has not started
 *   on_track   progress is at least `on_track_ratio` of where it should be today
 *   at_risk    at least `at_risk_ratio`
 *   behind     below that
 */
final class TargetPace
{
    public const LABELS = [
        'achieved' => 'Achieved',
        'on_track' => 'On track',
        'at_risk' => 'At risk',
        'behind' => 'Behind',
        'missed' => 'Missed',
        'upcoming' => 'Upcoming',
        'no_target' => 'No target',
    ];

    /**
     * @return array{pct:float, status:string, label:string, expected_pct:float, remaining:float, days_left:int}
     */
    public static function evaluate(float $actual, float $target, Carbon $start, Carbon $end, ?Carbon $today = null): array
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $elapsed = SalesPeriod::elapsed($start, $end, $today);
        $pct = $target > 0 ? round(($actual / $target) * 100, 1) : 0.0;
        $expectedPct = round($elapsed * 100, 1);
        $over = $today->gt($end->copy()->startOfDay());
        $notStarted = $today->lt($start->copy()->startOfDay());

        $cfg = config('sales_team.pace', []);
        $onTrack = (float) ($cfg['on_track_ratio'] ?? 0.9);
        $atRisk = (float) ($cfg['at_risk_ratio'] ?? 0.6);

        if ($target <= 0) {
            $status = 'no_target';
        } elseif ($pct >= 100) {
            $status = 'achieved';
        } elseif ($over) {
            $status = 'missed';
        } elseif ($notStarted) {
            $status = 'upcoming';
        } elseif ($pct >= $expectedPct * $onTrack) {
            $status = 'on_track';
        } elseif ($pct >= $expectedPct * $atRisk) {
            $status = 'at_risk';
        } else {
            $status = 'behind';
        }

        return [
            'pct' => $pct,
            'status' => $status,
            'label' => self::LABELS[$status],
            'expected_pct' => $expectedPct,
            'remaining' => max(0.0, round($target - $actual, 2)),
            'days_left' => $over ? 0 : max(0, (int) $today->diffInDays($end->copy()->startOfDay(), false)),
        ];
    }

    /** Average of each target's % capped at `attainment_cap`; null when there are no targets. */
    public static function attainment(array $percentages): ?float
    {
        if (!$percentages) {
            return null;
        }

        $cap = (float) config('sales_team.attainment_cap', 100);
        $sum = 0.0;

        foreach ($percentages as $p) {
            $sum += min($cap, max(0.0, (float) $p));
        }

        return round($sum / count($percentages), 1);
    }
}

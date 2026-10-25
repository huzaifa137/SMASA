<?php

namespace Tests\Unit;

use App\Support\TargetPace;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Pure logic: boots the app only for config(); touches no database. */
class SalesPaceTest extends TestCase
{
    private function eval(float $actual, float $target, string $today, string $start = '2026-09-01', string $end = '2026-09-30'): array
    {
        return TargetPace::evaluate($actual, $target, Carbon::parse($start), Carbon::parse($end), Carbon::parse($today));
    }

    public function test_elapsed_fraction_bounds(): void
    {
        $s = Carbon::parse('2026-09-01');
        $e = Carbon::parse('2026-09-30');
        $this->assertSame(0.0, \App\Support\SalesPeriod::elapsed($s, $e, Carbon::parse('2026-08-31')));
        $this->assertSame(1.0, \App\Support\SalesPeriod::elapsed($s, $e, Carbon::parse('2026-09-30')));
        $this->assertSame(1.0, \App\Support\SalesPeriod::elapsed($s, $e, Carbon::parse('2027-01-01')));
        $this->assertEqualsWithDelta(0.5, \App\Support\SalesPeriod::elapsed($s, $e, Carbon::parse('2026-09-15')), 0.001);
    }

    public function test_achieved_beats_everything_even_after_the_period_ends(): void
    {
        $this->assertSame('achieved', $this->eval(10, 10, '2026-09-10')['status']);
        $this->assertSame('achieved', $this->eval(15, 10, '2026-12-01')['status']);
    }

    public function test_missed_only_after_the_period_is_over(): void
    {
        $this->assertSame('missed', $this->eval(5, 10, '2026-10-01')['status']);
        $this->assertNotSame('missed', $this->eval(5, 10, '2026-09-30')['status'], 'the last day is still live');
    }

    public function test_pacing_bands(): void
    {
        // Halfway through the month (15 Sep): should be at 50%
        $this->assertSame('on_track', $this->eval(5, 10, '2026-09-15')['status']);   // 50% vs 50%
        $this->assertSame('on_track', $this->eval(4.6, 10, '2026-09-15')['status']); // 46% >= 45% (90% of 50)
        $this->assertSame('at_risk', $this->eval(3.5, 10, '2026-09-15')['status']);  // 35% >= 30% (60% of 50)
        $this->assertSame('behind', $this->eval(1, 10, '2026-09-15')['status']);
    }

    public function test_upcoming_and_no_target(): void
    {
        $this->assertSame('upcoming', $this->eval(0, 10, '2026-08-20')['status']);
        $this->assertSame('no_target', $this->eval(5, 0, '2026-09-15')['status']);
        $this->assertSame(0.0, $this->eval(5, 0, '2026-09-15')['pct'], 'never divides by zero');
    }

    public function test_remaining_and_days_left(): void
    {
        $r = $this->eval(4, 10, '2026-09-20');
        $this->assertSame(6.0, $r['remaining']);
        $this->assertSame(10, $r['days_left']);
        $this->assertSame(0.0, $this->eval(12, 10, '2026-09-20')['remaining']);
    }

    public function test_attainment_caps_each_target(): void
    {
        $this->assertNull(TargetPace::attainment([]));
        $this->assertSame(75.0, TargetPace::attainment([150, 50]));
        $this->assertSame(0.0, TargetPace::attainment([-5]));
    }
}

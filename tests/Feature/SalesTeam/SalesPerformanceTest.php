<?php

namespace Tests\Feature\SalesTeam;

use App\Models\AcademicTerm;
use App\Models\SalesTarget;
use App\Services\SalesOwnershipService;
use App\Services\SalesPerformanceService;
use App\Services\SalesTargetService;
use App\Support\SalesPeriod;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalesPerformanceTest extends TestCase
{
    use BuildsSalesSchema;

    private int $ann;
    private int $ben;
    private int $cat;
    private int $termId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildBaseSchema();
        $this->runSalesMigration();
        Carbon::setTestNow('2026-10-25 10:00:00');

        $this->ann = $this->makeAdmin('Ann Alpha');
        $this->ben = $this->makeAdmin('Ben Beta');
        $this->cat = $this->makeAdmin('Cat Gamma');
        foreach ([$this->ann, $this->ben, $this->cat] as $u) {
            $this->makeRep($u);
        }

        // Term II 2026: 1 Sep – 15 Dec, with its billing term
        $yearId = DB::table('academic_years')->insertGetId(['name' => '2026', 'created_at' => now(), 'updated_at' => now()]);
        $this->termId = DB::table('academic_terms')->insertGetId([
            'academic_year_id' => $yearId, 'term' => 2, 'start_date' => '2026-09-01', 'end_date' => '2026-12-15',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('billing_terms')->insert(['id' => 1, 'academic_year_id' => $yearId, 'year_label' => '2026', 'term' => 2, 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function perf(): SalesPerformanceService
    {
        return app(SalesPerformanceService::class);
    }

    private function seedTeam(): void
    {
        // Ann: 2 schools enrolled in the term, collects 900k
        $a1 = $this->makeSchool('A1', $this->ann, null, ['created_at' => '2026-09-10 08:00:00']);
        $a2 = $this->makeSchool('A2', $this->ann, null, ['created_at' => '2026-10-01 08:00:00']);
        $i1 = $this->makeInvoice($a1, 1000000, 600000, 'partial', '2026-09-30');
        $i2 = $this->makeInvoice($a2, 500000, 300000, 'partial', '2026-12-01');
        $this->makePayment($a1, 600000, '2026-10-02', 'valid', $i1);
        $this->makePayment($a2, 300000, '2026-10-12', 'valid', $i2);

        // Ben: 1 school, collects 1.2M (more cash, fewer schools)
        $b1 = $this->makeSchool('B1', $this->ben, null, ['created_at' => '2026-09-12 08:00:00']);
        $j1 = $this->makeInvoice($b1, 1200000, 1200000, 'paid', '2026-10-15');
        $this->makePayment($b1, 1200000, '2026-10-05', 'valid', $j1);

        // Cat: nothing at all
    }

    public function test_leaderboard_figures_and_cash_ranking(): void
    {
        $this->seedTeam();
        $p = SalesPeriod::resolve('term:' . $this->termId);

        $this->assertSame($this->termId, $p->academicTermId);
        $this->assertSame(1, $p->billingTermId());

        $rows = $this->perf()->leaderboard($p, ['sort' => 'cash'])->keyBy('user_id');

        $ann = $rows[$this->ann];
        $this->assertSame(2, $ann['schools']);
        $this->assertSame(2, $ann['enrolled']);
        $this->assertEquals(1500000, $ann['expected']);
        $this->assertEquals(900000, $ann['invoice_paid']);
        $this->assertEquals(60.0, $ann['rate']);
        $this->assertEquals(600000, $ann['outstanding']);
        $this->assertEquals(400000, $ann['overdue'], 'only A1 is past its due date');
        $this->assertSame(1, $ann['overdue_schools']);
        $this->assertEquals(900000, $ann['cash']);

        $ben = $rows[$this->ben];
        $this->assertEquals(1200000, $ben['cash']);
        $this->assertEquals(100.0, $ben['rate']);
        $this->assertEquals(0, $ben['outstanding']);

        $cat = $rows[$this->cat];
        $this->assertSame(0, $cat['schools']);
        $this->assertNull($cat['rate'], 'no invoices -> no rate, not 0%');

        // Ranking by cash: Ben 1, Ann 2, Cat unranked (no medal for nothing)
        $this->assertSame(1, $rows[$this->ben]['rank']);
        $this->assertSame(2, $rows[$this->ann]['rank']);
        $this->assertNull($rows[$this->cat]['rank']);

        // Ranking by schools flips it
        $bySchools = $this->perf()->leaderboard($p, ['sort' => 'schools'])->keyBy('user_id');
        $this->assertSame(1, $bySchools[$this->ann]['rank']);
    }

    public function test_equal_values_share_a_rank(): void
    {
        $this->makeSchool('A1', $this->ann, null, ['created_at' => '2026-10-01 08:00:00']);
        $this->makeSchool('B1', $this->ben, null, ['created_at' => '2026-10-02 08:00:00']);
        $this->makeSchool('C1', $this->cat, null, ['created_at' => '2026-10-03 08:00:00']);
        $this->makeSchool('C2', $this->cat, null, ['created_at' => '2026-10-04 08:00:00']);

        $rows = $this->perf()->leaderboard(SalesPeriod::resolve('term:' . $this->termId), ['sort' => 'enrolled'])->keyBy('user_id');

        $this->assertSame(1, $rows[$this->cat]['rank']);
        $this->assertSame(2, $rows[$this->ann]['rank']);
        $this->assertSame(2, $rows[$this->ben]['rank'], 'tie shares rank 2');
    }

    public function test_team_totals_and_unassigned_pool(): void
    {
        $this->seedTeam();
        $orphan = DB::table('schools')->insertGetId(['name' => 'Orphan', 'created_at' => now(), 'updated_at' => now()]);
        $this->makeInvoice($orphan, 400000, 0, 'issued');

        $rows = $this->perf()->leaderboard(SalesPeriod::resolve('term:' . $this->termId));
        $t = $this->perf()->teamTotals($rows);

        $this->assertSame(3, $t['reps']);
        $this->assertSame(3, $t['schools']);
        $this->assertEquals(2700000, $t['expected']);
        $this->assertEquals(2100000, $t['cash']);
        $this->assertSame(1, $t['unassigned_schools']);
        $this->assertEquals(400000, $t['unassigned_outstanding']);
    }

    public function test_drafts_and_cancelled_invoices_are_not_expected_money(): void
    {
        $s = $this->makeSchool('A1', $this->ann);
        $this->makeInvoice($s, 100000, 0, 'draft');
        $this->makeInvoice($s, 200000, 0, 'cancelled', null, 1);
        DB::table('billing_invoices')->where('status', 'cancelled')->update(['billing_term_id' => 1]);
        $this->makeInvoice($s, 300000, 0, 'issued', null, 1);

        $row = $this->perf()->row($this->ann, SalesPeriod::resolve('term:' . $this->termId));

        $this->assertEquals(300000, $row['expected'] + 0 - 0 === 300000.0 ? 300000 : $row['expected']);
        $this->assertEquals(300000, $row['outstanding']);
    }

    public function test_targets_are_measured_over_their_own_period_and_paced(): void
    {
        $this->seedTeam();
        $svc = app(SalesTargetService::class);

        $svc->set([$this->ann, $this->ben, $this->cat], 'schools_enrolled', 4, ['type' => 'term', 'academic_term_id' => $this->termId], null, 1);
        $svc->set([$this->ann], 'amount_collected', 2000000, ['type' => 'term', 'academic_term_id' => $this->termId], null, 1);

        $rows = $this->perf()->leaderboard(SalesPeriod::resolve('term:' . $this->termId))->keyBy('user_id');

        $annTargets = collect($rows[$this->ann]['targets'])->keyBy(fn ($t) => $t['target']->metric);
        $this->assertEquals(2, $annTargets['schools_enrolled']['actual']);
        $this->assertEquals(50.0, $annTargets['schools_enrolled']['eval']['pct']);
        $this->assertEquals(900000, $annTargets['amount_collected']['actual']);
        $this->assertEquals(45.0, $annTargets['amount_collected']['eval']['pct']);
        $this->assertEquals(47.5, $rows[$this->ann]['attainment'], 'average of 50% and 45%');

        $this->assertEquals(25.0, $rows[$this->ben]['attainment']);      // 1 of 4
        $this->assertEquals(0.0, $rows[$this->cat]['attainment']);
    }

    public function test_attainment_is_null_without_targets_and_capped_per_target(): void
    {
        $this->seedTeam();
        $svc = app(SalesTargetService::class);
        $this->assertNull($this->perf()->row($this->ann, SalesPeriod::resolve('term:' . $this->termId))['attainment']);

        $svc->set([$this->ann], 'schools_enrolled', 1, ['type' => 'term', 'academic_term_id' => $this->termId], null, 1);   // 200%
        $svc->set([$this->ann], 'amount_collected', 10000000, ['type' => 'term', 'academic_term_id' => $this->termId], null, 1); // 9%

        $row = $this->perf()->row($this->ann, SalesPeriod::resolve('term:' . $this->termId));
        $this->assertEquals(54.5, $row['attainment'], '(100 capped + 9) / 2 — an overshoot cannot hide a miss');
    }

    public function test_setting_a_target_twice_updates_instead_of_duplicating(): void
    {
        $svc = app(SalesTargetService::class);
        $period = ['type' => 'term', 'academic_term_id' => $this->termId];

        $this->assertSame(['created' => 3, 'updated' => 0], $svc->set([$this->ann, $this->ben, $this->cat], 'schools_enrolled', 5, $period, null, 1));
        $this->assertSame(['created' => 0, 'updated' => 1], $svc->set([$this->ann], 'schools_enrolled', 8, $period, 'raised', 1));

        $this->assertSame(3, SalesTarget::count());
        $this->assertEquals(8, SalesTarget::where('user_id', $this->ann)->value('target_value'));
    }

    public function test_target_validation(): void
    {
        $svc = app(SalesTargetService::class);
        $term = ['type' => 'term', 'academic_term_id' => $this->termId];

        foreach ([
            fn () => $svc->set([$this->ann], 'nonsense', 5, $term, null, 1),
            fn () => $svc->set([$this->ann], 'schools_enrolled', 0, $term, null, 1),
            fn () => $svc->set([$this->ann], 'schools_enrolled', -3, $term, null, 1),
            fn () => $svc->set([$this->ann], 'collection_rate', 120, $term, null, 1),
            fn () => $svc->set([], 'schools_enrolled', 5, $term, null, 1),
            fn () => $svc->set([$this->ann], 'schools_enrolled', 5, ['type' => 'term', 'academic_term_id' => 999], null, 1),
            fn () => $svc->set([$this->ann], 'schools_enrolled', 5, ['type' => 'custom', 'start' => '2026-12-01', 'end' => '2026-11-01'], null, 1),
            fn () => $svc->set([$this->ann], 'schools_enrolled', 5, ['type' => 'custom', 'start' => 'garbage', 'end' => '2026-11-01'], null, 1),
        ] as $i => $attempt) {
            try {
                $attempt();
                $this->fail("Invalid target #{$i} was accepted.");
            } catch (DomainException) {
                $this->assertTrue(true);
            }
        }

        $this->assertSame(0, SalesTarget::count());
    }

    public function test_inactive_reps_cannot_receive_targets(): void
    {
        DB::table('sales_rep_profiles')->where('user_id', $this->cat)->update(['status' => 'inactive']);

        $this->expectException(DomainException::class);
        app(SalesTargetService::class)->set([$this->cat], 'schools_enrolled', 5, ['type' => 'term', 'academic_term_id' => $this->termId], null, 1);
    }

    public function test_monthly_trend_is_zero_filled_and_attributed(): void
    {
        $this->seedTeam();
        $trend = $this->perf()->monthlyTrend($this->ann, 3);

        $this->assertCount(3, $trend);
        $this->assertSame(['2026-08', '2026-09', '2026-10'], array_column($trend, 'key'));
        $this->assertSame([0, 1, 1], array_column($trend, 'enrolled'));
        $this->assertEquals([0.0, 0.0, 900000.0], array_column($trend, 'cash'));

        $team = $this->perf()->monthlyTrend(null, 3);
        $this->assertEquals(2100000, array_sum(array_column($team, 'cash')));
    }

    public function test_portfolio_schools_carry_money_and_overdue_days(): void
    {
        $this->seedTeam();
        $schools = $this->perf()->portfolioSchools($this->ann, SalesPeriod::resolve('term:' . $this->termId))->keyBy('name');

        $this->assertEquals(1000000, $schools['A1']['expected']);
        $this->assertEquals(400000, $schools['A1']['outstanding']);
        $this->assertEquals(400000, $schools['A1']['overdue']);
        $this->assertSame(25, $schools['A1']['days_overdue'], 'due 30 Sep, today 25 Oct');
        $this->assertSame(0, $schools['A2']['days_overdue']);
        $this->assertSame('Active', $schools['A1']['status_label']);
    }

    public function test_transfer_moves_the_portfolio_but_not_the_enrolment_target_progress(): void
    {
        $this->seedTeam();
        app(SalesTargetService::class)->set([$this->ann, $this->ben], 'schools_enrolled', 2, ['type' => 'term', 'academic_term_id' => $this->termId], null, 1);

        app(SalesOwnershipService::class)->transfer($this->ann, $this->ben, null, 'rep_left', null, 1);

        $rows = $this->perf()->leaderboard(SalesPeriod::resolve('term:' . $this->termId))->keyBy('user_id');

        $this->assertSame(0, $rows[$this->ann]['schools']);
        $this->assertSame(3, $rows[$this->ben]['schools']);
        $this->assertEquals(600000, $rows[$this->ben]['outstanding'], 'the debt moved with the schools');
        $this->assertEquals(0, $rows[$this->ann]['outstanding']);

        $this->assertSame(2, $rows[$this->ann]['enrolled'], 'Ann keeps enrolment credit');
        $this->assertEquals(100.0, $rows[$this->ann]['attainment']);
        $this->assertEquals(900000, $rows[$this->ann]['cash'], 'and the cash she collected before the transfer');
        $this->assertEquals(1200000, $rows[$this->ben]['cash'], 'Ben is not credited with her past collections');
    }
}

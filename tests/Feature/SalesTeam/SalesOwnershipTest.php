<?php

namespace Tests\Feature\SalesTeam;

use App\Models\School;
use App\Services\SalesOwnershipService;
use App\Services\SalesPerformanceService;
use App\Services\SalesTeamService;
use App\Support\SalesPeriod;
use DomainException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalesOwnershipTest extends TestCase
{
    use BuildsSalesSchema;

    private int $alice;
    private int $bob;
    private int $carol;   // admin who is not a representative

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildBaseSchema();
        $this->runSalesMigration();

        $this->alice = $this->makeAdmin('Alice Rep');
        $this->bob = $this->makeAdmin('Bob Rep');
        $this->carol = $this->makeAdmin('Carol Super', ['is_super_admin' => true]);
        $this->makeRep($this->alice);
        $this->makeRep($this->bob);

        \Illuminate\Support\Carbon::setTestNow('2026-10-25 10:00:00');
    }

    protected function tearDown(): void
    {
        \Illuminate\Support\Carbon::setTestNow();
        parent::tearDown();
    }

    private function ownership(): SalesOwnershipService
    {
        return app(SalesOwnershipService::class);
    }

    public function test_enrolment_by_a_rep_makes_them_owner_and_enroller(): void
    {
        $id = DB::table('schools')->insertGetId(['name' => 'New School', 'created_at' => now(), 'updated_at' => now()]);

        $owner = $this->ownership()->registerEnrollment(School::find($id), $this->alice);

        $this->assertSame($this->alice, $owner);
        $row = DB::table('schools')->find($id);
        $this->assertEquals($this->alice, $row->sales_rep_id);
        $this->assertEquals($this->alice, $row->enrolled_by_id);
        $this->assertSame(1, DB::table('school_rep_assignments')->where('school_id', $id)->whereNull('ended_at')->count());
    }

    public function test_enrolment_by_a_non_rep_admin_is_unassigned_but_credited(): void
    {
        $id = DB::table('schools')->insertGetId(['name' => 'Orphan', 'created_at' => now(), 'updated_at' => now()]);

        $owner = $this->ownership()->registerEnrollment(School::find($id), $this->carol);

        $this->assertNull($owner);
        $row = DB::table('schools')->find($id);
        $this->assertNull($row->sales_rep_id);
        $this->assertEquals($this->carol, $row->enrolled_by_id);
        $this->assertSame(0, DB::table('school_rep_assignments')->where('school_id', $id)->count());
    }

    public function test_admin_can_enrol_on_behalf_of_a_rep(): void
    {
        $id = DB::table('schools')->insertGetId(['name' => 'Delegated', 'created_at' => now(), 'updated_at' => now()]);

        $this->ownership()->registerEnrollment(School::find($id), $this->carol, $this->bob);

        $row = DB::table('schools')->find($id);
        $this->assertEquals($this->bob, $row->sales_rep_id);
        $this->assertEquals($this->bob, $row->enrolled_by_id);
    }

    public function test_cannot_enrol_for_an_inactive_or_unknown_rep(): void
    {
        DB::table('sales_rep_profiles')->where('user_id', $this->bob)->update(['status' => 'inactive']);
        $id = DB::table('schools')->insertGetId(['name' => 'X', 'created_at' => now(), 'updated_at' => now()]);

        $this->expectException(DomainException::class);
        $this->ownership()->registerEnrollment(School::find($id), $this->carol, $this->bob);
    }

    public function test_transfer_moves_ownership_and_history_and_snapshots_the_debt(): void
    {
        $s1 = $this->makeSchool('Alpha', $this->alice);
        $s2 = $this->makeSchool('Beta', $this->alice);
        $this->makeInvoice($s1, 1000000, 400000, 'partial', '2026-09-01');   // overdue 600k
        $this->makeInvoice($s2, 500000, 0, 'issued', '2026-12-01');          // not due yet
        DB::table('students')->insert([
            ['school_id' => $s1, 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $s1, 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $s2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $batch = $this->ownership()->transfer($this->alice, $this->bob, null, 'rep_left', null, $this->carol);

        $this->assertSame(2, $batch->school_count);
        $this->assertSame(3, $batch->student_count);
        $this->assertEquals(1100000, $batch->outstanding_amount);
        $this->assertEquals(600000, $batch->overdue_amount);
        $this->assertMatchesRegularExpression('/^TRF-2026-\d{5}$/', $batch->reference);

        foreach ([$s1, $s2] as $sid) {
            $this->assertEquals($this->bob, DB::table('schools')->find($sid)->sales_rep_id);
            // exactly one open stretch, and it is Bob's
            $open = DB::table('school_rep_assignments')->where('school_id', $sid)->whereNull('ended_at')->get();
            $this->assertCount(1, $open);
            $this->assertEquals($this->bob, $open[0]->user_id);
            $this->assertEquals($batch->id, $open[0]->transfer_batch_id);
            $this->assertEquals($this->alice, $open[0]->from_user_id);
            // Alice's stretch is closed, not deleted
            $this->assertNotNull(DB::table('school_rep_assignments')->where('school_id', $sid)->where('user_id', $this->alice)->value('ended_at'));
        }
    }

    public function test_partial_transfer_only_moves_selected_schools_owned_by_the_sender(): void
    {
        $mine = $this->makeSchool('Mine', $this->alice);
        $other = $this->makeSchool('NotAlices', $this->bob);

        $batch = $this->ownership()->transfer($this->alice, $this->bob, [$mine, $other], 'workload', null, $this->carol);

        $this->assertSame(1, $batch->school_count);          // $other is Bob's, ignored
        $this->assertEquals($this->bob, DB::table('schools')->find($mine)->sales_rep_id);
        $this->assertEquals($this->bob, DB::table('schools')->find($other)->sales_rep_id);
        $this->assertSame(1, DB::table('school_rep_assignments')->where('school_id', $other)->count());   // untouched
    }

    public function test_transfer_validation(): void
    {
        $this->makeSchool('Alpha', $this->alice);

        foreach ([
            fn () => $this->ownership()->transfer($this->alice, $this->alice, null, 'workload', null, 1),
            fn () => $this->ownership()->transfer($this->alice, $this->bob, null, 'not_a_reason', null, 1),
            fn () => $this->ownership()->transfer($this->alice, $this->bob, null, 'other', '  ', 1),
            fn () => $this->ownership()->transfer($this->alice, $this->carol, null, 'workload', null, 1),   // Carol is not a rep
            fn () => $this->ownership()->transfer($this->alice, $this->bob, [], 'workload', null, 1),
            fn () => $this->ownership()->transfer($this->bob, $this->alice, null, 'workload', null, 1),       // Bob owns nothing
        ] as $i => $attempt) {
            try {
                $attempt();
                $this->fail("Attempt #{$i} should have been rejected.");
            } catch (DomainException) {
                $this->assertTrue(true);
            }
        }

        $this->assertSame(0, DB::table('sales_transfer_batches')->count(), 'Rejected transfers must leave no trace.');
    }

    public function test_release_to_pool_then_assign(): void
    {
        $s = $this->makeSchool('Alpha', $this->alice);

        $batch = $this->ownership()->transfer($this->alice, null, null, 'rep_left', null, 1);

        $this->assertNull($batch->to_user_id);
        $this->assertNull(DB::table('schools')->find($s)->sales_rep_id);
        $this->assertSame(0, DB::table('school_rep_assignments')->where('school_id', $s)->whereNull('ended_at')->count());

        $n = $this->ownership()->assign([$s], $this->bob, 1, 'Picked up from pool');
        $this->assertSame(1, $n);
        $this->assertEquals($this->bob, DB::table('schools')->find($s)->sales_rep_id);

        // assigning a school that already has an owner is a no-op
        $this->assertSame(0, $this->ownership()->assign([$s], $this->alice, 1));
        $this->assertEquals($this->bob, DB::table('schools')->find($s)->sales_rep_id);
    }

    public function test_assign_can_credit_enrolment_only_when_nobody_has_it(): void
    {
        $a = DB::table('schools')->insertGetId(['name' => 'NoEnroller', 'created_at' => now(), 'updated_at' => now()]);
        $b = DB::table('schools')->insertGetId(['name' => 'HasEnroller', 'enrolled_by_id' => $this->carol, 'created_at' => now(), 'updated_at' => now()]);

        $this->ownership()->assign([$a, $b], $this->alice, 1, null, true);

        $this->assertEquals($this->alice, DB::table('schools')->find($a)->enrolled_by_id);
        $this->assertEquals($this->carol, DB::table('schools')->find($b)->enrolled_by_id, 'existing enrolment credit must not be overwritten');
    }

    public function test_deactivating_a_rep_requires_a_destination_for_their_schools(): void
    {
        $s = $this->makeSchool('Alpha', $this->alice);
        $profile = \App\Models\SalesRepProfile::where('user_id', $this->alice)->first();
        $team = app(SalesTeamService::class);

        try {
            $team->deactivate($profile, null, 1);
            $this->fail('Should require a destination.');
        } catch (DomainException) {
        }
        $this->assertSame('active', $profile->fresh()->status);

        $moved = $team->deactivate($profile, (string) $this->bob, 1);

        $this->assertSame(1, $moved);
        $this->assertSame('inactive', $profile->fresh()->status);
        $this->assertEquals($this->bob, DB::table('schools')->find($s)->sales_rep_id);
    }

    public function test_cash_credit_follows_the_owner_on_the_payment_date(): void
    {
        // Alice owns the school from 1 Aug; it moves to Bob on 25 Oct (test "now").
        $s = $this->makeSchool('Alpha', $this->alice, null, ['created_at' => '2026-08-01 08:00:00']);
        $this->makePayment($s, 100000, '2026-09-10');        // Alice's
        $this->makePayment($s, 50000, '2026-10-24');         // Alice's (day before the transfer)
        $this->makePayment($s, 999, '2026-10-10', 'reversed'); // never counts

        $this->ownership()->transfer($this->alice, $this->bob, null, 'workload', null, 1);

        $this->makePayment($s, 70000, '2026-10-25');         // same day as transfer -> new owner
        $this->makePayment($s, 30000, '2026-11-02');         // future -> new owner (outside window below)

        $perf = app(SalesPerformanceService::class);
        $cash = $perf->cashCredited('2026-08-01', '2026-10-31');

        $this->assertEquals(150000, $cash[$this->alice] ?? 0);
        $this->assertEquals(70000, $cash[$this->bob] ?? 0);
        $this->assertEquals(220000, array_sum($cash), 'every shilling is credited exactly once');
    }

    public function test_money_is_never_double_counted_across_several_same_day_transfers(): void
    {
        $s = $this->makeSchool('Alpha', $this->alice, null, ['created_at' => '2026-08-01 08:00:00']);
        $this->makePayment($s, 10000, '2026-10-25');

        $this->ownership()->transfer($this->alice, $this->bob, null, 'workload', null, 1);
        $this->ownership()->transfer($this->bob, $this->alice, null, 'workload', null, 1);   // back again, same day

        $cash = app(SalesPerformanceService::class)->cashCredited('2026-10-01', '2026-10-31');

        $this->assertEquals(10000, array_sum($cash));
        $this->assertEquals(10000, $cash[$this->alice] ?? 0);
    }

    public function test_portfolio_figures_follow_the_current_owner(): void
    {
        $s = $this->makeSchool('Alpha', $this->alice);
        $this->makeInvoice($s, 1000000, 250000, 'partial', '2026-09-01');

        $perf = app(SalesPerformanceService::class);
        $before = $perf->balances([$this->alice, $this->bob]);
        $this->assertEquals(750000, $before[$this->alice]->outstanding);
        $this->assertNull($before->get($this->bob));

        $this->ownership()->transfer($this->alice, $this->bob, null, 'workload', null, 1);

        $after = $perf->balances([$this->alice, $this->bob]);
        $this->assertNull($after->get($this->alice));
        $this->assertEquals(750000, $after[$this->bob]->outstanding);
        $this->assertEquals(750000, $after[$this->bob]->overdue);
    }

    public function test_enrolment_credit_stays_with_the_enroller_after_a_transfer(): void
    {
        $s = $this->makeSchool('Alpha', $this->alice, null, ['created_at' => '2026-10-05 09:00:00']);
        $this->ownership()->transfer($this->alice, $this->bob, null, 'workload', null, 1);

        $perf = app(SalesPerformanceService::class);
        $period = SalesPeriod::resolve('custom', '2026-10-01', '2026-10-31');
        $enrolled = $perf->enrolled($period->start, $period->end);

        $this->assertSame(1, $enrolled[$this->alice] ?? 0);
        $this->assertArrayNotHasKey($this->bob, $enrolled);
    }
}

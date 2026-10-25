<?php

namespace Tests\Feature\SalesTeam;

use App\Services\SalesOwnershipService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/** Every page rendered through the real routes + middleware, and the permission gates. */
class SalesTeamPagesTest extends TestCase
{
    use BuildsSalesSchema;

    protected int $boss;
    protected int $ann;
    protected int $ben;
    protected int $viewer;
    protected int $termId;
    protected string $stubDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildBaseSchema();
        $this->runSalesMigration();
        Carbon::setTestNow('2026-10-25 10:00:00');

        // The real master layout pulls in the whole app shell; pages are tested against a stub.
        $this->stubDir = sys_get_temp_dir() . '/st-views-' . uniqid();
        File::ensureDirectoryExists($this->stubDir . '/layouts-side-bar');
        File::put($this->stubDir . '/layouts-side-bar/master.blade.php', "@yield('content')");
        View::prependLocation($this->stubDir);

        $this->boss = $this->makeAdmin('Big Boss', ['is_super_admin' => true]);

        $salesRole = DB::table('admin_roles')->insertGetId(['name' => 'Sales Representative', 'slug' => 'sales-representative', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('admin_role_permissions')->insert(['admin_role_id' => $salesRole, 'permission' => 'sales_team.view_own', 'created_at' => now(), 'updated_at' => now()]);
        $viewerRole = DB::table('admin_roles')->insertGetId(['name' => 'Viewer', 'slug' => 'viewer', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('admin_role_permissions')->insert(['admin_role_id' => $viewerRole, 'permission' => 'sales_team.view_team', 'created_at' => now(), 'updated_at' => now()]);

        $this->ann = $this->makeAdmin('Ann Alpha', ['admin_role_id' => $salesRole]);
        $this->ben = $this->makeAdmin('Ben Beta', ['admin_role_id' => $salesRole]);
        $this->viewer = $this->makeAdmin('Just Viewer', ['admin_role_id' => $viewerRole]);
        $this->makeRep($this->ann);
        $this->makeRep($this->ben);

        $yearId = DB::table('academic_years')->insertGetId(['name' => '2026', 'created_at' => now(), 'updated_at' => now()]);
        $this->termId = DB::table('academic_terms')->insertGetId(['academic_year_id' => $yearId, 'term' => 2, 'start_date' => '2026-09-01', 'end_date' => '2026-12-15', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('billing_terms')->insert(['id' => 1, 'academic_year_id' => $yearId, 'year_label' => '2026', 'term' => 2, 'created_at' => now(), 'updated_at' => now()]);

        $s1 = $this->makeSchool('Alpha Primary', $this->ann, null, ['created_at' => '2026-09-10 08:00:00']);
        $this->makeSchool('Beta High', $this->ben, null, ['created_at' => '2026-09-12 08:00:00']);
        DB::table('schools')->insert(['name' => 'Orphan Academy', 'created_at' => now(), 'updated_at' => now()]);
        $inv = $this->makeInvoice($s1, 1000000, 400000, 'partial', '2026-09-30');
        $this->makePayment($s1, 400000, '2026-10-02', 'valid', $inv);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->stubDir);
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function as(int $uid)
    {
        return $this->withSession(['LoggedAdmin' => $uid]);
    }

    public function test_every_page_renders_for_a_super_admin(): void
    {
        $urls = [
            '/admin/sales-team', '/admin/sales-team?sort=attainment&status=all&q=ann',
            '/admin/sales-team?period=custom&from=2026-09-01&to=2026-10-31', '/admin/sales-team?period=all',
            '/admin/sales-team/reps', "/admin/sales-team/reps/{$this->ann}", '/admin/sales-team/targets',
            '/admin/sales-team/transfers', '/admin/sales-team/transfers/new', "/admin/sales-team/transfers/new?from={$this->ann}",
            '/admin/sales-team/unassigned',
        ];

        foreach ($urls as $url) {
            $res = $this->as($this->boss)->get($url);
            $this->assertSame(200, $res->getStatusCode(), "{$url} => {$res->getStatusCode()}");
        }

        $this->as($this->boss)->get('/admin/sales-team')->assertSee('Leaders Dashboard')->assertSee('Ann Alpha')->assertSee('have no representative')->assertDontSee('Undefined');
        $this->as($this->boss)->get("/admin/sales-team/reps/{$this->ann}")->assertSee('Alpha Primary')->assertSee('Ownership history');
        $this->as($this->boss)->get('/admin/sales-team/unassigned')->assertSee('Orphan Academy');
    }

    public function test_pages_render_with_no_data_at_all(): void
    {
        DB::table('sales_rep_profiles')->delete();
        DB::table('school_rep_assignments')->delete();
        DB::table('schools')->update(['sales_rep_id' => null]);

        $this->as($this->boss)->get('/admin/sales-team/me')->assertOk()->assertSee('not been set up');

        foreach (['/admin/sales-team', '/admin/sales-team/reps', '/admin/sales-team/targets', '/admin/sales-team/transfers', '/admin/sales-team/unassigned'] as $url) {
            $this->as($this->boss)->get($url)->assertOk();
        }
    }

    public function test_a_rep_sees_only_their_own_performance(): void
    {
        $this->as($this->ann)->get('/admin/sales-team/me')->assertRedirect("/admin/sales-team/reps/{$this->ann}");
        $this->as($this->ann)->get("/admin/sales-team/reps/{$this->ann}")->assertOk()->assertSee('My performance')->assertSee('Alpha Primary');

        // not a colleague, not the leaderboard, not the pools, not any management screen
        foreach (["/admin/sales-team/reps/{$this->ben}", '/admin/sales-team', '/admin/sales-team/reps', '/admin/sales-team/targets', '/admin/sales-team/transfers', '/admin/sales-team/unassigned', '/admin/sales-team/transfers/new'] as $url) {
            $this->assertSame(403, $this->as($this->ann)->get($url)->getStatusCode(), $url);
        }
    }

    public function test_a_viewer_can_look_but_not_change_anything(): void
    {
        $this->as($this->viewer)->get('/admin/sales-team')->assertOk();
        $this->as($this->viewer)->get("/admin/sales-team/reps/{$this->ann}")->assertOk();

        $this->assertSame(403, $this->as($this->viewer)->get('/admin/sales-team/transfers/new')->getStatusCode());
        $this->assertSame(403, $this->as($this->viewer)->get('/admin/sales-team/export/leaderboard')->getStatusCode());

        $this->as($this->viewer)->post('/admin/sales-team/transfers', ['from_user_id' => $this->ann, 'to' => $this->ben, 'mode' => 'all', 'reason' => 'workload'])->assertForbidden();
        $this->as($this->viewer)->post('/admin/sales-team/targets', ['scope' => 'all', 'metric' => 'schools_enrolled', 'target_value' => 5, 'period_type' => 'term', 'academic_term_id' => $this->termId])->assertForbidden();
        $this->as($this->viewer)->post('/admin/sales-team/reps', ['user_id' => $this->viewer])->assertForbidden();
        $this->as($this->viewer)->post('/admin/sales-team/unassigned', ['school_ids' => [1], 'user_id' => $this->ann])->assertForbidden();
        $this->assertSame(0, DB::table('sales_transfer_batches')->count());
    }

    public function test_anonymous_and_school_sessions_are_turned_away(): void
    {
        $this->assertNotSame(200, $this->get('/admin/sales-team')->getStatusCode());
        $this->assertNotSame(200, $this->withSession(['LoggedStudent' => 1])->get('/admin/sales-team')->getStatusCode());
    }

    public function test_transfer_flow_end_to_end(): void
    {
        $alpha = DB::table('schools')->where('name', 'Alpha Primary')->value('id');

        $res = $this->as($this->boss)->post('/admin/sales-team/transfers', [
            'from_user_id' => $this->ann, 'to' => (string) $this->ben, 'mode' => 'selected', 'school_ids' => [$alpha], 'reason' => 'rep_left', 'notes' => 'Ann resigned',
        ]);

        $batch = DB::table('sales_transfer_batches')->first();
        $this->assertNotNull($batch);
        $res->assertRedirect("/admin/sales-team/transfers/{$batch->id}");
        $this->assertEquals($this->ben, DB::table('schools')->find($alpha)->sales_rep_id);
        $this->assertEquals(600000, $batch->outstanding_amount);

        $this->as($this->boss)->get("/admin/sales-team/transfers/{$batch->id}")->assertOk()->assertSee('Alpha Primary')->assertSee($batch->reference);
        $this->as($this->boss)->get('/admin/sales-team/transfers')->assertOk()->assertSee($batch->reference);
    }

    public function test_transfer_validation_errors_come_back_as_messages_not_crashes(): void
    {
        $this->as($this->boss)->from('/admin/sales-team/transfers/new')
            ->post('/admin/sales-team/transfers', ['from_user_id' => $this->ann, 'to' => (string) $this->ann, 'mode' => 'all', 'reason' => 'workload'])
            ->assertRedirect('/admin/sales-team/transfers/new')->assertSessionHas('error');

        $this->as($this->boss)->post('/admin/sales-team/transfers', ['from_user_id' => $this->ann, 'mode' => 'all'])->assertSessionHasErrors(['to', 'reason']);
        $this->assertSame(0, DB::table('sales_transfer_batches')->count());
    }

    public function test_targets_crud_over_http(): void
    {
        $this->as($this->boss)->post('/admin/sales-team/targets', ['scope' => 'all', 'metric' => 'schools_enrolled', 'target_value' => 5, 'period_type' => 'term', 'academic_term_id' => $this->termId])
            ->assertSessionHas('success');
        $this->assertSame(2, DB::table('sales_targets')->count());

        $this->as($this->boss)->get('/admin/sales-team/targets')->assertOk()->assertSee('Schools enrolled')->assertSee('Ann Alpha');

        $id = DB::table('sales_targets')->where('user_id', $this->ann)->value('id');
        $this->as($this->boss)->put("/admin/sales-team/targets/{$id}", ['target_value' => 9])->assertSessionHas('success');
        $this->assertEquals(9, DB::table('sales_targets')->find($id)->target_value);

        $this->as($this->boss)->delete("/admin/sales-team/targets/{$id}")->assertSessionHas('success');
        $this->assertSame(1, DB::table('sales_targets')->count());

        $this->as($this->boss)->post('/admin/sales-team/targets', ['scope' => 'all', 'metric' => 'schools_enrolled', 'target_value' => -1, 'period_type' => 'term', 'academic_term_id' => $this->termId])
            ->assertSessionHasErrors('target_value');
    }

    public function test_assigning_unassigned_schools_and_adding_a_rep(): void
    {
        $orphan = DB::table('schools')->where('name', 'Orphan Academy')->value('id');
        $this->as($this->boss)->post('/admin/sales-team/unassigned', ['school_ids' => [$orphan], 'user_id' => $this->ben])->assertSessionHas('success');
        $this->assertEquals($this->ben, DB::table('schools')->find($orphan)->sales_rep_id);

        $new = $this->makeAdmin('Newbie Admin');
        $this->as($this->boss)->post('/admin/sales-team/reps', ['user_id' => $new, 'region' => 'North', 'commission_rate' => 2.5])->assertRedirect("/admin/sales-team/reps/{$new}");
        $codes = DB::table('sales_rep_profiles')->pluck('rep_code', 'user_id');
        $this->assertCount(3, $codes->unique(), 'rep codes are unique');
        $this->assertSame($codes->except($new)->map(fn ($c) => (int) substr($c, 4))->max() + 1, (int) substr($codes[$new], 4), 'new code is max + 1');

        // the same person cannot be added twice
        $this->as($this->boss)->post('/admin/sales-team/reps', ['user_id' => $new])->assertSessionHas('error');
    }

    public function test_deactivating_a_rep_requires_somewhere_for_their_schools_to_go(): void
    {
        $this->as($this->boss)->post("/admin/sales-team/reps/{$this->ann}/deactivate", [])->assertSessionHas('error');
        $this->assertSame('active', DB::table('sales_rep_profiles')->where('user_id', $this->ann)->value('status'));

        $this->as($this->boss)->post("/admin/sales-team/reps/{$this->ann}/deactivate", ['destination' => 'pool'])->assertRedirect('/admin/sales-team/reps');
        $this->assertSame('inactive', DB::table('sales_rep_profiles')->where('user_id', $this->ann)->value('status'));
        $this->assertNull(DB::table('schools')->where('name', 'Alpha Primary')->value('sales_rep_id'));
    }

    public function test_csv_exports_download_and_neutralise_formulas(): void
    {
        DB::table('users')->where('id', $this->ben)->update(['name' => '=HYPERLINK("http://evil")']);

        foreach (['leaderboard', 'targets', 'transfers'] as $type) {
            $res = $this->as($this->boss)->get("/admin/sales-team/export/{$type}");
            $this->assertSame(200, $res->getStatusCode(), $type);
            $this->assertStringContainsString('text/csv', $res->headers->get('Content-Type'));
        }

        $csv = $this->as($this->boss)->get('/admin/sales-team/export/leaderboard')->streamedContent();
        $this->assertStringContainsString('Ann Alpha', $csv);
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);

        $this->as($this->boss)->get("/admin/sales-team/export/portfolio?rep={$this->ann}")->assertOk();
        $this->assertSame(404, $this->as($this->boss)->get('/admin/sales-team/export/portfolio?rep=99999')->getStatusCode());
        $this->assertSame(404, $this->as($this->boss)->get('/admin/sales-team/export/everything')->getStatusCode());
    }
}

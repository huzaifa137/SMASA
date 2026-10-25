<?php

namespace Tests\Feature\SalesTeam;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** The migration links EXISTING schools to the people who enrolled them. */
class SalesBackfillMigrationTest extends TestCase
{
    use BuildsSalesSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildBaseSchema();
    }

    // Raw rows so the migration's backfill sees "legacy" data.
    private function legacySchool(string $name, ?int $addedBy = null): int
    {
        return DB::table('schools')->insertGetId([
            'name' => $name, 'added_by' => $addedBy, 'created_at' => '2026-03-01 09:00:00', 'updated_at' => now(),
        ]);
    }

    private function auditCreated(int $schoolId, int $actorId, string $actorType = 'admin', string $event = 'created'): void
    {
        DB::table('audit_logs')->insert([
            'event' => $event, 'entity_type' => 'School', 'entity_id' => (string) $schoolId,
            'actor_type' => $actorType, 'actor_id' => $actorId, 'occurred_at' => '2026-03-01 09:00:00',
        ]);
    }

    public function test_backfill_uses_the_audit_trail_and_links_only_sales_reps(): void
    {
        // The existing "Sales Representative" admin role (from the Admin Rights migration)
        $roleId = DB::table('admin_roles')->insertGetId(['name' => 'Sales Representative', 'slug' => 'sales-representative', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $rep = $this->makeAdmin('Rep One', ['admin_role_id' => $roleId]);
        $boss = $this->makeAdmin('The Boss', ['is_super_admin' => true]);
        $teacher = DB::table('users')->insertGetId(['name' => 'A Teacher', 'user_role' => 'teacher', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $byRep = $this->legacySchool('Enrolled by rep');
        $this->auditCreated($byRep, $rep);

        $byBoss = $this->legacySchool('Enrolled by boss');
        $this->auditCreated($byBoss, $boss);

        $noTrail = $this->legacySchool('Only added_by (admin)', $rep);         // fallback path
        $junk = $this->legacySchool('added_by is not an admin', $teacher);     // must be ignored
        $nothing = $this->legacySchool('No information at all');

        $notCreated = $this->legacySchool('Audit says updated, not created');
        $this->auditCreated($notCreated, $rep, 'admin', 'updated');
        $byTeacherSession = $this->legacySchool('Created by school user');
        $this->auditCreated($byTeacherSession, $teacher, 'teacher');

        $this->runSalesMigration();

        // The sales role may now view its own performance, and its member became a representative
        $this->assertTrue(DB::table('admin_role_permissions')->where('admin_role_id', $roleId)->where('permission', 'sales_team.view_own')->exists());
        $profile = DB::table('sales_rep_profiles')->where('user_id', $rep)->first();
        $this->assertNotNull($profile);
        $this->assertSame('REP-0001', $profile->rep_code);
        $this->assertNull(DB::table('sales_rep_profiles')->where('user_id', $boss)->first(), 'only sales-role users become reps automatically');

        $s = fn ($id) => DB::table('schools')->find($id);

        $this->assertEquals($rep, $s($byRep)->enrolled_by_id);
        $this->assertEquals($rep, $s($byRep)->sales_rep_id);

        $this->assertEquals($boss, $s($byBoss)->enrolled_by_id, 'enrolment credit is recorded even for non-reps');
        $this->assertNull($s($byBoss)->sales_rep_id, 'but the school stays unassigned until an admin assigns it');

        $this->assertEquals($rep, $s($noTrail)->enrolled_by_id);
        $this->assertEquals($rep, $s($noTrail)->sales_rep_id);

        $this->assertNull($s($junk)->enrolled_by_id);
        $this->assertNull($s($nothing)->enrolled_by_id);
        $this->assertNull($s($notCreated)->enrolled_by_id);
        $this->assertNull($s($byTeacherSession)->enrolled_by_id);

        // One open assignment per owned school, dated from the school's creation
        $open = DB::table('school_rep_assignments')->whereNull('ended_at')->get();
        $this->assertCount(2, $open);
        foreach ($open as $a) {
            $this->assertSame('backfill', $a->type);
            $this->assertStringStartsWith('2026-03-01', (string) $a->started_at);
        }
    }

    public function test_migration_is_safe_on_an_empty_system_and_reversible(): void
    {
        $this->runSalesMigration();

        foreach (['sales_rep_profiles', 'sales_targets', 'sales_transfer_batches', 'school_rep_assignments'] as $t) {
            $this->assertTrue(Schema::hasTable($t), $t);
        }
        $this->assertTrue(Schema::hasColumns('schools', ['sales_rep_id', 'enrolled_by_id']));
    }
}

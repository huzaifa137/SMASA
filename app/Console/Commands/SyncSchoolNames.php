<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\SchoolIdentityService;
use Illuminate\Console\Command;

/**
 * One-off repair for schools renamed before SchoolIdentityService existed:
 * lists schools whose name differs between schools / houses / school_profiles
 * and (with --apply) makes all three agree.
 *
 *   php artisan smasa:sync-school-names           # dry run, changes nothing
 *   php artisan smasa:sync-school-names --apply
 */
class SyncSchoolNames extends Command
{
    protected $signature = 'smasa:sync-school-names {--apply : Write the changes (default is a dry run)}';

    protected $description = 'Make schools.name, houses.House and school_profiles.name agree for every school';

    public function handle(SchoolIdentityService $service): int
    {
        $drift = $service->findDrift();

        if (empty($drift)) {
            $this->info('All school names already agree.');
            return self::SUCCESS;
        }

        $this->table(
            ['school_id', 'schools.name', 'houses.House', 'profile.name', 'will become'],
            array_map(fn($r) => [$r['school_id'], $r['schools.name'], $r['houses.House'], $r['profile.name'], $r['canonical']], $drift)
        );

        if (!$this->option('apply')) {
            $this->warn(count($drift) . ' school(s) out of sync. Re-run with --apply to fix.');
            return self::SUCCESS;
        }

        foreach ($drift as $row) {
            $school = School::find($row['school_id']);
            $service->syncName($school, $row['canonical'], $row['canonical_arabic'] !== '' ? $row['canonical_arabic'] : null);
        }

        $this->info('Synced ' . count($drift) . ' school(s).');

        return self::SUCCESS;
    }
}

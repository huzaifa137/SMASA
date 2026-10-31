<?php

namespace App\Console\Commands;

use App\Support\SchoolName;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-off: upper-case the names of schools that were saved before names were
 * forced to UPPER CASE. Touches the three places a name lives
 * (schools.name, houses.House, school_profiles.name) and nothing else;
 * updated_at is left alone so the name-sync logic isn't disturbed.
 *
 *   php artisan smasa:uppercase-school-names           # dry run, changes nothing
 *   php artisan smasa:uppercase-school-names --apply
 */
class UppercaseSchoolNames extends Command
{
    protected $signature = 'smasa:uppercase-school-names {--apply : Write the changes (default is a dry run)}';

    protected $description = 'Convert existing school names (schools, houses, school_profiles) to UPPER CASE';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $changes = [];

        $schools = DB::table('schools')->select('id', 'name', 'registration_code')->get();

        foreach ($schools as $school) {
            $new = SchoolName::normalize($school->name);

            if ($new !== '' && $new !== $school->name) {
                $changes[] = ['table' => 'schools', 'key' => $school->id, 'from' => $school->name, 'to' => $new];
            }
        }

        foreach (DB::table('houses')->whereIn('Number', $schools->pluck('registration_code')->filter())->get(['ID', 'House']) as $house) {
            $new = SchoolName::normalize($house->House);

            if ($new !== '' && $new !== $house->House) {
                $changes[] = ['table' => 'houses', 'key' => $house->ID, 'from' => $house->House, 'to' => $new];
            }
        }

        if (Schema::hasTable('school_profiles')) {
            foreach (DB::table('school_profiles')->whereNotNull('name')->get(['id', 'name']) as $profile) {
                $new = SchoolName::normalize($profile->name);

                if ($new !== '' && $new !== $profile->name) {
                    $changes[] = ['table' => 'school_profiles', 'key' => $profile->id, 'from' => $profile->name, 'to' => $new];
                }
            }
        }

        if (empty($changes)) {
            $this->info('Every school name is already UPPER CASE.');
            return self::SUCCESS;
        }

        $this->table(['table', 'id', 'from', 'to'], array_map(fn($c) => [$c['table'], $c['key'], $c['from'], $c['to']], $changes));

        // Warn about names that become identical once upper-cased.
        $dupes = collect($schools)->map(fn($s) => SchoolName::normalize($s->name))->filter()->countBy()->filter(fn($n) => $n > 1)->keys();
        if ($dupes->isNotEmpty()) {
            $this->warn('These names are used by more than one school (they stay separate schools): ' . $dupes->implode('; '));
        }

        if (!$apply) {
            $this->warn(count($changes) . ' name(s) would change. Re-run with --apply to write them.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes) {
            foreach ($changes as $c) {
                $keyColumn = $c['table'] === 'houses' ? 'ID' : 'id';
                $column = match ($c['table']) {
                    'houses' => 'House',
                    default => 'name',
                };

                DB::table($c['table'])->where($keyColumn, $c['key'])->update([$column => $c['to']]);
            }
        });

        $this->info(count($changes) . ' name(s) converted to UPPER CASE.');

        return self::SUCCESS;
    }
}

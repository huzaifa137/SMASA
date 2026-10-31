<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\SchoolDeletionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deletes every school whose status is not Active (school_status != 10),
 * using SchoolDeletionService, the exact code behind the delete icon on
 * /all-schools (students, teachers, marks, finance, files, profile, login).
 *
 *   php artisan smasa:purge-inactive-schools                      # dry run, deletes nothing
 *   php artisan smasa:purge-inactive-schools --apply              # asks you to type DELETE first
 *   php artisan smasa:purge-inactive-schools --apply --except=200 --except=SCH-2026-0004
 *   php artisan smasa:purge-inactive-schools --apply --only-empty # only schools with 0 students
 *   php artisan smasa:purge-inactive-schools --apply --include-orphan-houses
 *
 * --except accepts a school id or a registration code (IT-001, SCH-2026-0007).
 */
class PurgeInactiveSchools extends Command
{
    protected $signature = 'smasa:purge-inactive-schools
        {--apply : Actually delete (default is a dry run)}
        {--except=* : School id or registration code to keep, repeatable}
        {--only-empty : Only delete schools that have no students}
        {--include-orphan-houses : Also remove houses rows that have no school}
        {--force : Skip the typed confirmation}';

    protected $description = 'Permanently delete all schools that are not Active, with all their data';

    public function handle(SchoolDeletionService $service): int
    {
        $except = array_map('strval', (array) $this->option('except'));

        $schools = School::where('school_status', '!=', 10)->orderBy('id')->get()
            ->reject(fn($s) => in_array((string) $s->id, $except, true)
                || in_array((string) $s->registration_code, $except, true));

        $rows = [];
        foreach ($schools as $s) {
            $students = $this->count('students', $s->id);
            $teachers = $this->count('teachers', $s->id);

            if ($this->option('only-empty') && $students > 0) {
                continue;
            }

            $rows[] = [$s->id, $s->registration_code, $s->name, $s->school_status, $students, $teachers];
        }

        $orphans = [];
        if ($this->option('include-orphan-houses')) {
            $orphans = DB::table('houses')
                ->whereNotIn('Number', DB::table('schools')->select('registration_code'))
                ->get(['ID', 'House', 'Number'])->all();
        }

        if (empty($rows) && empty($orphans)) {
            $this->info('Nothing to delete. Every school is Active.');
            return self::SUCCESS;
        }

        $this->table(['id', 'code', 'name', 'status', 'students', 'teachers'], $rows);
        $this->line(count($rows) . ' non-active school(s), ' . array_sum(array_column($rows, 4)) . ' student(s) in total.');

        if ($orphans) {
            $this->table(['house ID', 'House', 'Number'], array_map(fn($o) => [$o->ID, $o->House, $o->Number], $orphans));
        }

        if (!$this->option('apply')) {
            $this->warn('Dry run, nothing deleted. Re-run with --apply to delete the schools listed above.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && $this->ask('Type DELETE to permanently remove the schools above') !== 'DELETE') {
            $this->error('Cancelled, nothing deleted.');
            return self::FAILURE;
        }

        $deleted = 0;
        $failed = [];

        foreach ($rows as [$id, $code, $name]) {
            try {
                $result = $service->delete(School::findOrFail($id));
                $deleted++;
                $this->info("Deleted #{$id} {$name} ({$result['students']} students, {$result['teachers']} teachers)");
            } catch (\Throwable $e) {
                $failed[] = [$id, $name, $e->getMessage()];
                $this->error("FAILED #{$id} {$name}: " . $e->getMessage());
            }
        }

        foreach ($orphans as $o) {
            DB::table('houses')->where('ID', $o->ID)->delete();
            $this->info("Removed orphan house {$o->Number} {$o->House}");
        }

        $this->line("Done. {$deleted} school(s) deleted, " . count($failed) . ' failed.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function count(string $table, int $schoolId): int
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')
            ? DB::table($table)->where('school_id', $schoolId)->count()
            : 0;
    }
}

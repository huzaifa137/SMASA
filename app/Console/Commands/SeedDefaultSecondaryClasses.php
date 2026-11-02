<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\DefaultSecondaryClassesService;
use Illuminate\Console\Command;

class SeedDefaultSecondaryClasses extends Command
{
    // php artisan classes:seed-secondary-defaults 12      (one school: adds any missing Senior 1-6, never touches existing ones)
    // php artisan classes:seed-secondary-defaults --all   (every Secondary school that has NO classes yet)
    protected $signature = 'classes:seed-secondary-defaults {school_id? : School id} {--all : Every Secondary school that has no classes yet}';
    protected $description = 'Create Senior 1-6 (no streams, compulsory subjects ticked) for existing Secondary schools';

    public function handle(): int
    {
        $all = (bool) $this->option('all');

        $schools = $all
            ? School::withoutGlobalScopes()->get()
            : School::withoutGlobalScopes()->where('id', (int) $this->argument('school_id'))->get();

        if ($schools->isEmpty()) {
            $this->error('No school found. Pass a school id or --all.');
            return self::FAILURE;
        }

        $seeded = 0;
        $untouched = 0;

        foreach ($schools as $school) {
            $result = DefaultSecondaryClassesService::seedForSchool($school->id, null, $all);

            if ($result['note'] !== null && empty($result['created'])) {
                $untouched++;
                if (!$all) {
                    $this->line(sprintf('#%d %s: %s', $school->id, $school->name, $result['note']));
                }
                continue;
            }

            $seeded++;
            $this->line(sprintf(
                '#%d %s: created [%s]%s',
                $school->id,
                $school->name,
                implode(', ', $result['created']) ?: 'none',
                $result['skipped'] ? ' kept existing [' . implode(', ', $result['skipped']) . ']' : ''
            ));

            if (!empty($result['missing_subjects'])) {
                $this->warn('  Subjects not found in master data: ' . implode(', ', $result['missing_subjects']));
            }
        }

        if ($all) {
            $this->info("Done: {$seeded} school(s) seeded, {$untouched} left alone (not Secondary, or already have classes).");
        }

        return self::SUCCESS;
    }
}

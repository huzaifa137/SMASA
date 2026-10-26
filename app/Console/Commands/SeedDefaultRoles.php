<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\SchoolRole;
use App\Services\DefaultRolesService;
use Illuminate\Console\Command;

class SeedDefaultRoles extends Command
{
    // php artisan roles:seed-defaults 12      (one school)
    // php artisan roles:seed-defaults --all   (every school)
    protected $signature = 'roles:seed-defaults {school_id? : School id} {--all : Every school}';
    protected $description = 'Create the default HeadTeacher and Teacher roles for existing schools (missing ones only)';

    public function handle(): int
    {
        $schools = $this->option('all')
            ? School::all()
            : School::where('id', (int) $this->argument('school_id'))->get();

        if ($schools->isEmpty()) {
            $this->error('No school found. Pass a school id or --all.');
            return self::FAILURE;
        }

        foreach ($schools as $school) {
            $hadRoles = SchoolRole::where('school_id', $school->id)->exists();
            $result = DefaultRolesService::seedForSchool($school->id);

            $this->line(sprintf(
                '#%d %s: %s',
                $school->id,
                $school->name,
                collect($result)->map(fn ($c, $n) => $n . ($c ? ' created' : ' kept'))->implode(', ')
            ));

            if (!$hadRoles && collect($result)->contains(true)) {
                $this->warn('  This school had no roles before: assign HeadTeacher to its admin teacher(s), or they lose bootstrap access.');
            }
        }

        return self::SUCCESS;
    }
}

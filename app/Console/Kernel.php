<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Audit trail retention (see config/audit.php)
        $schedule->command('audit:prune')->dailyAt('02:30');

        // Automatic student promotion (schools set to "Automatic" under Students → Promote Students)
        $schedule->command('promotion:run-auto')->dailyAt('03:00')->withoutOverlapping();

        // Backups + restore test (see docs/SCALING.md). Output goes to the log; a failure exits non-zero.
        $schedule->command('smasa:backup')->dailyAt('01:30')->withoutOverlapping(120)->onOneServer();
        if (config('smasa.backup.verify_database')) {
            $schedule->command('smasa:backup-verify')->weeklyOn(0, '04:30')->withoutOverlapping(240)->onOneServer();
        }

        // Housekeeping for the queue: failed-job records and finished report-card files.
        $schedule->command('queue:prune-failed --hours=336')->weekly();
        $schedule->call(function () {
            foreach (glob(storage_path('app/report-cards/*/*.html')) ?: [] as $f) {
                if (filemtime($f) < strtotime('-1 day')) {
                    @unlink($f);
                }
            }
        })->dailyAt('03:30')->name('prune-report-card-files');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

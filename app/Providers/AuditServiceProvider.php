<?php

namespace App\Providers;

use App\Audit\ActorResolver;
use App\Audit\AuditContext;
use App\Audit\AuditRecorder;
use App\Audit\ModelEventSubscriber;
use App\Audit\QueryTally;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the audit trail into the application:
 *   - model events (every Eloquent model, automatically)
 *   - the query write tally (DB::table() / mass update & delete)
 *   - artisan command auditing
 * HTTP requests are covered by App\Http\Middleware\RecordAuditTrail.
 */
class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditContext::class);
        $this->app->singleton(ActorResolver::class);
        $this->app->singleton(AuditRecorder::class);
        $this->app->singleton(ModelEventSubscriber::class);
        $this->app->singleton(QueryTally::class);
    }

    public function boot(): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        Event::listen(
            ['eloquent.created: *', 'eloquent.updated: *', 'eloquent.deleted: *', 'eloquent.restored: *'],
            function (string $event, array $payload) {
                $this->app->make(ModelEventSubscriber::class)->handle($event, $payload);
            }
        );

        DB::listen(function (QueryExecuted $query) {
            $this->app->make(QueryTally::class)->handle($query);
        });

        if ($this->app->runningInConsole()) {
            $this->registerConsoleListeners();
        }
    }

    private function registerConsoleListeners(): void
    {
        // Each queued job is its own audit scope (otherwise a long-running
        // worker would share one correlation id and one entry budget).
        Event::listen(JobProcessing::class, function () {
            $this->app->make(AuditContext::class)->begin();
        });

        if (!config('audit.console.enabled', true)) {
            return;
        }

        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $context = $this->app->make(AuditContext::class);

            // Nested commands (db:seed -> migrate...) stay in the outer scope.
            if ($context->command !== null || !$event->command) {
                return;
            }

            $context->begin();
            $context->command = $event->command;
        });

        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            $context = $this->app->make(AuditContext::class);

            if ($context->command === null || $context->command !== $event->command) {
                return;
            }

            $command = $event->command;
            $excluded = (array) config('audit.console.excluded_commands', []);

            try {
                if (!in_array($command, $excluded, true) && !str_starts_with($command, 'audit:')) {
                    $failed = (int) $event->exitCode !== 0;
                    $meta = [
                        'command' => $command,
                        'exit_code' => (int) $event->exitCode,
                        'queries' => $context->queryCount,
                    ];

                    if ($context->hasWrites()) {
                        $meta['db_writes'] = $context->writeSummary();
                    }
                    if ($context->modelEntries > 0) {
                        $meta['model_entries'] = $context->modelEntries;
                    }
                    if ($context->modelEntriesSuppressed > 0) {
                        $meta['model_entries_not_itemised'] = $context->modelEntriesSuppressed;
                    }

                    $this->app->make(AuditRecorder::class)->record([
                        'event' => 'console_command',
                        'category' => 'system',
                        'severity' => $failed ? 'warning' : 'info',
                        'outcome' => $failed ? 'failure' : 'success',
                        'description' => "Ran artisan {$command}" . ($failed ? " (failed, exit {$event->exitCode})" : ''),
                        'metadata' => $meta,
                        'duration_ms' => $context->durationMs(),
                    ]);
                }
            } finally {
                $context->command = null;
            }
        });
    }
}

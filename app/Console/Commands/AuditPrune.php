<?php

namespace App\Console\Commands;

use App\Audit\Audit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditPrune extends Command
{
    protected $signature = 'audit:prune {--days= : Keep this many days (default: audit.retention_days)} {--dry-run : Only report what would be deleted}';

    protected $description = 'Delete audit trail entries older than the retention period';

    public function handle(): int
    {
        $days = $this->option('days') !== null ? (int) $this->option('days') : (int) config('audit.retention_days');

        if ($days <= 0) {
            $this->info('Retention is disabled (audit.retention_days = 0). Nothing pruned.');

            return self::SUCCESS;
        }

        $cutoff = now('UTC')->subDays($days)->format('Y-m-d H:i:s');

        $count = DB::table('audit_logs')->where('occurred_at', '<', $cutoff)->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} entr" . ($count === 1 ? 'y is' : 'ies are') . " older than {$days} days (before {$cutoff} UTC). Dry run - nothing deleted.");

            return self::SUCCESS;
        }

        $deleted = 0;
        do {
            $ids = DB::table('audit_logs')->where('occurred_at', '<', $cutoff)->orderBy('id')->limit(5000)->pluck('id');
            if ($ids->isEmpty()) {
                break;
            }
            $deleted += DB::table('audit_logs')->whereIn('id', $ids)->delete();
        } while (true);

        // Pruning is itself a recorded, signed event.
        Audit::log('audit_pruned', "Pruned {$deleted} audit entries older than {$days} days", [
            'category' => 'security',
            'severity' => 'notice',
            'metadata' => ['deleted' => $deleted, 'retention_days' => $days, 'cutoff_utc' => $cutoff],
        ]);

        $this->info("Deleted {$deleted} audit entries older than {$days} days.");

        return self::SUCCESS;
    }
}

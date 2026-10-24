<?php

namespace App\Console\Commands;

use App\Audit\AuditVerifier;
use Illuminate\Console\Command;

class AuditVerify extends Command
{
    protected $signature = 'audit:verify {--limit=20000 : How many of the newest entries to verify}';

    protected $description = 'Verify audit trail signatures and look for gaps in the entry sequence';

    public function handle(AuditVerifier $verifier): int
    {
        $result = $verifier->run((int) $this->option('limit'));

        $this->line("Checked {$result['checked']} entries (ids {$result['first_id']} - {$result['last_id']}).");

        if ($result['gaps']) {
            $this->warn(count($result['gaps']) . ' gap(s) in the id sequence (can be rolled-back transactions or pruning):');
            foreach (array_slice($result['gaps'], 0, 10) as $gap) {
                $this->line("  ids {$gap['from']} - {$gap['to']}");
            }
        }

        if ($result['tampered']) {
            $this->error(count($result['tampered']) . ' entr' . (count($result['tampered']) === 1 ? 'y has' : 'ies have') . ' a signature mismatch (modified outside the application):');
            $this->line('  ids: ' . implode(', ', array_slice($result['tampered'], 0, 50)));

            return self::FAILURE;
        }

        $this->info('All signatures valid.');

        return self::SUCCESS;
    }
}

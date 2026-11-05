<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * php artisan smasa:backup
 *
 * Streams a consistent mysqldump (single transaction: no table locks, safe on
 * a live system) into a gzip file, writes a .sha256 next to it, and deletes
 * backups older than BACKUP_KEEP_DAYS. Exits non-zero on any failure so cron /
 * monitoring notices. Copy the files off the server too (see docs/SCALING.md):
 * a backup on the same disk is not a backup.
 */
class BackupDatabase extends Command
{
    protected $signature = 'smasa:backup';

    protected $description = 'Dump the database to a compressed, checksummed file and prune old backups';

    public function handle(): int
    {
        $cfg = config('smasa.backup');
        $db = config('database.connections.' . config('database.default'));

        if (($db['driver'] ?? null) !== 'mysql') {
            $this->error('smasa:backup supports MySQL / MariaDB only.');

            return self::FAILURE;
        }

        if (!is_dir($cfg['path']) && !@mkdir($cfg['path'], 0750, true) && !is_dir($cfg['path'])) {
            $this->error('Cannot create backup folder ' . $cfg['path']);

            return self::FAILURE;
        }

        $file = rtrim($cfg['path'], '/\\') . '/smasa-' . date('Ymd-His') . '.sql.gz';

        $process = new Process([
            $cfg['mysqldump'],
            '--single-transaction', '--quick', '--routines', '--triggers', '--no-tablespaces',
            '--default-character-set=utf8mb4',
            '-h', (string) $db['host'], '-P', (string) ($db['port'] ?? 3306), '-u', (string) $db['username'],
            (string) $db['database'],
        ], null, ['MYSQL_PWD' => (string) ($db['password'] ?? '')], null, 3600);

        $gz = gzopen($file, 'wb6');
        if (!$gz) {
            $this->error('Cannot write ' . $file);

            return self::FAILURE;
        }

        $stderr = '';
        $process->run(function ($type, $buffer) use ($gz, &$stderr) {
            $type === Process::OUT ? gzwrite($gz, $buffer) : $stderr .= $buffer;
        });
        gzclose($gz);

        if (!$process->isSuccessful() || filesize($file) < 200) {
            @unlink($file);
            Log::error('smasa:backup failed', ['exit' => $process->getExitCode(), 'stderr' => mb_substr($stderr, 0, 500)]);
            $this->error('mysqldump failed: ' . trim($stderr));

            return self::FAILURE;
        }

        file_put_contents($file . '.sha256', hash_file('sha256', $file) . '  ' . basename($file) . "\n");

        $pruned = 0;
        foreach (glob(rtrim($cfg['path'], '/\\') . '/smasa-*.sql.gz*') as $old) {
            if (filemtime($old) < strtotime('-' . $cfg['keep_days'] . ' days')) {
                @unlink($old);
                $pruned++;
            }
        }

        Log::info('smasa:backup ok', ['file' => basename($file), 'bytes' => filesize($file)]);
        $this->info(sprintf('Backup written: %s (%s MB). %d old file(s) pruned.', basename($file), round(filesize($file) / 1048576, 1), $pruned));

        return self::SUCCESS;
    }
}

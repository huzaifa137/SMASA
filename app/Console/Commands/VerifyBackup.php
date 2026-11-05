<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * php artisan smasa:backup-verify [--file=path] [--keep]
 *
 * The restore TEST. Takes the newest backup, checks its checksum, restores it
 * into the scratch database BACKUP_VERIFY_DATABASE, then compares it with the
 * live database (same tables, key tables not empty) and reports. A backup you
 * have never restored is only a hope.
 *
 * The scratch database is DROPPED and recreated every run, so it must be a
 * dedicated empty database - the command refuses to run if it is the live one.
 */
class VerifyBackup extends Command
{
    protected $signature = 'smasa:backup-verify {--file= : Backup file to test (default: newest)} {--keep : Keep the restored scratch database afterwards}';

    protected $description = 'Restore the newest backup into a scratch database and check it against the live one';

    private const KEY_TABLES = ['schools', 'students', 'teachers', 'examination_marks', 'fee_payments', 'student_attendances'];

    public function handle(): int
    {
        $cfg = config('smasa.backup');
        $db = config('database.connections.' . config('database.default'));
        $scratch = (string) $cfg['verify_database'];

        if ($scratch === '' || !preg_match('/^[A-Za-z0-9_]+$/', $scratch)) {
            $this->error('Set BACKUP_VERIFY_DATABASE in .env to the name of an empty scratch database (letters, numbers, underscore).');

            return self::FAILURE;
        }

        if (strcasecmp($scratch, (string) $db['database']) === 0) {
            $this->error('BACKUP_VERIFY_DATABASE is the LIVE database. Refusing to continue.');

            return self::FAILURE;
        }

        $file = $this->option('file') ?: collect(glob(rtrim($cfg['path'], '/\\') . '/smasa-*.sql.gz'))->sort()->last();

        if (!$file || !is_file($file)) {
            $this->error('No backup file found. Run php artisan smasa:backup first.');

            return self::FAILURE;
        }

        $this->line('Testing ' . basename($file));

        // 1. Checksum
        if (is_file($file . '.sha256')) {
            $expected = strtok(trim(file_get_contents($file . '.sha256')), ' ');
            if (!hash_equals($expected, hash_file('sha256', $file))) {
                return $this->abortRestoreTest('Checksum mismatch: the backup file is damaged.');
            }
            $this->info('  checksum ok');
        }

        // 2. Fresh scratch database
        DB::statement("DROP DATABASE IF EXISTS `{$scratch}`");
        DB::statement("CREATE DATABASE `{$scratch}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // 3. Restore
        $restore = Process::fromShellCommandline(
            'gzip -dc ' . escapeshellarg($file) . ' | ' . escapeshellarg($cfg['mysql'])
            . ' -h ' . escapeshellarg((string) $db['host']) . ' -P ' . escapeshellarg((string) ($db['port'] ?? 3306))
            . ' -u ' . escapeshellarg((string) $db['username']) . ' ' . escapeshellarg($scratch),
            null,
            ['MYSQL_PWD' => (string) ($db['password'] ?? '')],
            null,
            7200
        );
        $restore->run();

        if (!$restore->isSuccessful()) {
            return $this->abortRestoreTest('Restore failed: ' . trim($restore->getErrorOutput()));
        }
        $this->info('  restored into ' . $scratch);

        // 4. Compare with the live database
        $problems = [];
        $liveTables = $this->tables((string) $db['database']);
        $restoredTables = $this->tables($scratch);

        if ($missing = array_diff($liveTables, $restoredTables)) {
            $problems[] = 'Tables missing from the restore: ' . implode(', ', array_slice($missing, 0, 10));
        }

        $rows = [];
        foreach (self::KEY_TABLES as $table) {
            if (!in_array($table, $liveTables, true)) {
                continue;
            }

            $live = (int) DB::table($table)->count();
            $restored = in_array($table, $restoredTables, true)
                ? (int) DB::table("{$scratch}.{$table}")->count()
                : 0;

            $rows[] = [$table, $live, $restored];

            if ($live > 0 && $restored === 0) {
                $problems[] = "{$table} is empty in the restore but has {$live} rows live.";
            }
        }

        $this->table(['Table', 'Live rows', 'Restored rows'], $rows);

        if (!$this->option('keep')) {
            DB::statement("DROP DATABASE IF EXISTS `{$scratch}`");
        }

        if ($problems) {
            return $this->abortRestoreTest(implode(' ', $problems));
        }

        Cache::forever('backup:last_verify', ['ok' => true, 'file' => basename($file), 'at' => now()->toDateTimeString()]);
        Log::info('smasa:backup-verify ok', ['file' => basename($file)]);
        $this->info('RESTORE TEST PASSED: ' . basename($file) . ' restores cleanly.');

        return self::SUCCESS;
    }

    private function tables(string $database): array
    {
        return DB::table('information_schema.tables')
            ->where('table_schema', $database)->where('table_type', 'BASE TABLE')
            ->pluck('table_name')->map(fn($t) => (string) $t)->all();
    }

    private function abortRestoreTest(string $why): int
    {
        Cache::forever('backup:last_verify', ['ok' => false, 'why' => $why, 'at' => now()->toDateTimeString()]);
        Log::critical('smasa:backup-verify FAILED: ' . $why);
        $this->error('RESTORE TEST FAILED: ' . $why);

        return self::FAILURE;
    }
}

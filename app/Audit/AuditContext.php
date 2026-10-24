<?php

namespace App\Audit;

use Illuminate\Support\Str;

/**
 * Mutable state shared by everything that records audit entries during one
 * HTTP request or artisan command: the correlation id, who was signed in
 * when it started, a tally of query-builder writes, and counters used to
 * keep one giant import from flooding the trail.
 */
class AuditContext
{
    public ?string $requestId = null;

    public float $startedAt;

    /** Actor snapshot taken BEFORE the request ran (needed for logout). */
    public ?array $preActor = null;

    /** @var array<string, array{insert:int, update:int, delete:int}> */
    public array $writes = [];

    public int $queryCount = 0;

    public int $modelEntries = 0;

    public int $modelEntriesSuppressed = 0;

    /** > 0 while Audit::withoutAuditing() is running. */
    public int $suppressDepth = 0;

    /** True while the recorder itself is writing (prevents recursion). */
    public bool $recording = false;

    public ?string $command = null;

    public function __construct()
    {
        $this->startedAt = microtime(true);
    }

    /** Start a fresh request / command scope. */
    public function begin(): void
    {
        $this->requestId = (string) Str::uuid();
        $this->startedAt = microtime(true);
        $this->preActor = null;
        $this->writes = [];
        $this->queryCount = 0;
        $this->modelEntries = 0;
        $this->modelEntriesSuppressed = 0;
        $this->suppressDepth = 0;
        $this->recording = false;
    }

    /**
     * True when a web session is available. Laravel flips Store::isStarted()
     * back to false once it has saved the session (before terminate()), yet
     * the attributes stay readable - so "has data" counts as active too.
     */
    public static function sessionActive(): bool
    {
        try {
            if (!app()->bound('session.store')) {
                return false;
            }

            $store = app('session.store');

            return $store->isStarted() || $store->all() !== [];
        } catch (\Throwable) {
            return false;
        }
    }

    public function requestId(): string
    {
        return $this->requestId ??= (string) Str::uuid();
    }

    public function durationMs(): int
    {
        return (int) round((microtime(true) - $this->startedAt) * 1000);
    }

    public function isSuppressed(): bool
    {
        return $this->suppressDepth > 0 || $this->recording;
    }

    public function tallyWrite(string $table, string $verb): void
    {
        $this->writes[$table] ??= ['insert' => 0, 'update' => 0, 'delete' => 0];
        $this->writes[$table][$verb]++;
    }

    public function hasWrites(): bool
    {
        return $this->writes !== [];
    }

    /** Compact view of the tally, largest tables first, bounded in size. */
    public function writeSummary(int $limit = 40): array
    {
        $writes = $this->writes;
        uasort($writes, fn ($a, $b) => array_sum($b) <=> array_sum($a));

        return array_slice(array_filter($writes, fn ($w) => array_sum($w) > 0), 0, $limit, true);
    }
}

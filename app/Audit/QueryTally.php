<?php

namespace App\Audit;

use Illuminate\Database\Events\QueryExecuted;

/**
 * Tallies INSERT / UPDATE / DELETE statements per table for the current
 * request or command.
 *
 * Eloquent model events cannot see writes made through DB::table(...) or
 * Model::where(...)->update()/delete(), and this codebase uses both heavily
 * (e.g. the school / student deletion services). The tally closes that gap:
 * the request's audit entry lists every table it wrote to and how many
 * statements of each kind, e.g. {"examination_marks": {"delete": 3}}.
 */
class QueryTally
{
    public function __construct(private AuditContext $context)
    {
    }

    public function handle(QueryExecuted $query): void
    {
        $this->context->queryCount++;

        if ($this->context->isSuppressed()) {
            return;
        }

        $sql = ltrim($query->sql);

        if (!preg_match('/^(insert|replace|update|delete)\b/i', $sql, $verb)) {
            return;
        }

        $table = $this->tableOf(strtolower($verb[1]), $sql);

        if ($table === null || in_array($table, (array) config('audit.tally_ignored_tables', []), true)) {
            return;
        }

        $kind = match (strtolower($verb[1])) {
            'insert', 'replace' => 'insert',
            default => strtolower($verb[1]),
        };

        $this->context->tallyWrite($table, $kind);
    }

    private function tableOf(string $verb, string $sql): ?string
    {
        $pattern = match ($verb) {
            'insert', 'replace' => '/^(?:insert|replace)\s+(?:ignore\s+)?into\s+[`"\[]?([\w.]+)/i',
            'update' => '/^update\s+(?:ignore\s+)?[`"\[]?([\w.]+)/i',
            'delete' => '/^delete\s+(?:ignore\s+)?from\s+[`"\[]?([\w.]+)/i',
        };

        if (!preg_match($pattern, $sql, $m)) {
            return null;
        }

        // "db.table" -> "table"
        $parts = explode('.', $m[1]);

        return end($parts) ?: null;
    }
}

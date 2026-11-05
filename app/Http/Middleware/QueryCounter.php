<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Counts the database queries of each request (config smasa.query_counter).
 * Off by default: zero cost in normal operation.
 */
class QueryCounter
{
    private int $count = 0;
    private float $ms = 0;
    private array $patterns = [];

    public function handle(Request $request, Closure $next)
    {
        if (!config('smasa.query_counter')) {
            return $next($request);
        }

        DB::listen(function ($query) {
            $this->count++;
            $this->ms += $query->time;

            // Same query with different ids/values = one pattern: this is how an N+1 shows up.
            $shape = preg_replace('/\s+/', ' ', $query->sql);
            $this->patterns[$shape] = ($this->patterns[$shape] ?? 0) + 1;
        });

        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-DB-Queries', (string) $this->count);
            $response->header('X-DB-Time-ms', (string) round($this->ms, 1));
        }

        return $response;
    }

    public function terminate(Request $request, $response): void
    {
        if (!config('smasa.query_counter') || $this->count <= (int) config('smasa.query_warn_threshold', 60)) {
            return;
        }

        arsort($this->patterns);

        Log::warning(sprintf(
            'QUERY-COUNT %d queries / %.0f ms on %s %s',
            $this->count,
            $this->ms,
            $request->method(),
            $request->path()
        ), [
            'most_repeated' => array_map(
                fn($sql, $n) => $n . 'x ' . mb_substr($sql, 0, 160),
                array_keys(array_slice($this->patterns, 0, 5, true)),
                array_values(array_slice($this->patterns, 0, 5, true))
            ),
        ]);
    }
}

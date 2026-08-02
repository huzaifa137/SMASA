<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Root-cause fix for the mismatched "no stream" marker between the
     * class side and the student side.
     *
     * Before "no streams" support was added, a class with no stream simply
     * had an empty/NULL stream_id, and students in that class often ended
     * up with an empty/NULL 'stream' value too (nothing forced it either
     * way). Once NO_STREAM_SENTINEL ('NO_STREAM') was introduced for
     * classes, any *old* rows that still had '' / NULL no longer matched
     * new-style rows using the sentinel, and vice versa. Depending on which
     * side of a query had which value, this produced either:
     *   - undercounts (0 students) when a class-subject row uses the new
     *     sentinel but its students still have '' / NULL, or
     *   - very large, cross-class-looking counts when several *different*
     *     legacy rows all shared the same generic '' / NULL value and a
     *     query happened to match on that shared blank instead of a real,
     *     specific stream.
     *
     * This migration is purely a normalization: it only touches rows whose
     * stream value is already blank/NULL and rewrites it to the same
     * sentinel used everywhere else, for this same "no stream" state. It
     * does not touch any row that already has a real stream name.
     */
    public function up(): void
    {
        $sentinel = 'NO_STREAM';

        DB::table('streams')
            ->where(function ($q) {
                $q->whereNull('stream_id')->orWhere('stream_id', '');
            })
            ->update(['stream_id' => $sentinel]);

        DB::table('class_stream_assignments')
            ->where(function ($q) {
                $q->whereNull('stream_id')->orWhere('stream_id', '');
            })
            ->update(['stream_id' => $sentinel]);

        DB::table('class_subjects')
            ->where(function ($q) {
                $q->whereNull('stream_id')->orWhere('stream_id', '');
            })
            ->update(['stream_id' => $sentinel]);

        DB::table('students')
            ->where(function ($q) {
                $q->whereNull('stream')->orWhere('stream', '');
            })
            ->update(['stream' => $sentinel]);
    }

    /**
     * Not reversible in a meaningful way — we no longer know which rows
     * were originally NULL vs '' before the normalization, and reverting
     * would just reintroduce the mismatch this migration fixes. Left as a
     * no-op on purpose.
     */
    public function down(): void
    {
        // Intentionally left blank.
    }
};
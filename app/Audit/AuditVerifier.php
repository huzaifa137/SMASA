<?php

namespace App\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

/**
 * Integrity check for the audit trail.
 *
 *  - tampered: rows whose stored signature no longer matches their content
 *              (someone edited the row outside the application).
 *  - gaps:     breaks in the id sequence (rows may have been deleted). Gaps
 *              are also produced legitimately by rolled-back transactions
 *              and by retention pruning, so they are informational.
 */
class AuditVerifier
{
    /**
     * @return array{checked:int, tampered:int[], gaps:array<int, array{from:int, to:int}>, first_id:?int, last_id:?int}
     */
    public function run(int $limit = 20000): array
    {
        $result = ['checked' => 0, 'tampered' => [], 'gaps' => [], 'first_id' => null, 'last_id' => null];

        // Newest N rows, verified in id order.
        $floor = DB::table('audit_logs')->orderByDesc('id')->skip(max($limit - 1, 0))->take(1)->value('id') ?? 0;

        $previous = null;

        DB::table('audit_logs')
            ->where('id', '>=', $floor)
            ->orderBy('id')
            ->chunkById(1000, function ($rows) use (&$result, &$previous) {
                foreach ($rows as $row) {
                    $data = (array) $row;
                    $result['checked']++;
                    $result['first_id'] ??= (int) $row->id;
                    $result['last_id'] = (int) $row->id;

                    if (!hash_equals((string) $row->signature, AuditLog::sign($data))) {
                        if (count($result['tampered']) < 500) {
                            $result['tampered'][] = (int) $row->id;
                        }
                    }

                    if ($previous !== null && $row->id - $previous > 1 && count($result['gaps']) < 200) {
                        $result['gaps'][] = ['from' => $previous + 1, 'to' => (int) $row->id - 1];
                    }

                    $previous = (int) $row->id;
                }
            });

        return $result;
    }
}

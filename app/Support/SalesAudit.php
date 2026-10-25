<?php

namespace App\Support;

use App\Audit\Audit;

/** Records Sales Team business events in the audit trail; never lets a logging problem break the action. */
final class SalesAudit
{
    public static function log(string $event, string $description, array $metadata = [], ?int $schoolId = null, string $severity = 'notice'): void
    {
        try {
            $options = ['category' => 'data', 'severity' => $severity, 'metadata' => $metadata];

            if ($schoolId) {
                $options['school_id'] = $schoolId;
            }

            Audit::log($event, $description, $options);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

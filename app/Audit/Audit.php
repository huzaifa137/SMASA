<?php

namespace App\Audit;

use Illuminate\Database\Eloquent\Model;

/**
 * Public API for writing audit entries by hand. Almost everything is
 * recorded automatically; use this for business events that deserve an
 * explicit, well-worded entry.
 *
 *   Audit::log('fee_waived', 'Waived term 2 fees for Jane Doe', [
 *       'subject' => $student,
 *       'metadata' => ['amount' => 150000],
 *   ]);
 *
 *   Audit::critical('school_deleted', 'Deleted school X', [...]);
 *
 *   Audit::withoutAuditing(fn () => $heavyImport());   // suppress model/write tracking
 *
 * Options: subject (Model), entity_type/entity_id/entity_label, old, new,
 * metadata, category, severity, outcome, school_id, actor.
 */
class Audit
{
    public static function log(string $event, string $description, array $options = []): ?string
    {
        return self::recorder()->record(self::build($event, $description, $options));
    }

    public static function notice(string $event, string $description, array $options = []): ?string
    {
        return self::log($event, $description, $options + ['severity' => 'notice']);
    }

    public static function warning(string $event, string $description, array $options = []): ?string
    {
        return self::log($event, $description, $options + ['severity' => 'warning']);
    }

    public static function critical(string $event, string $description, array $options = []): ?string
    {
        return self::log($event, $description, $options + ['severity' => 'critical']);
    }

    public static function security(string $event, string $description, array $options = []): ?string
    {
        return self::log($event, $description, $options + ['category' => 'security', 'severity' => 'warning']);
    }

    /**
     * Run $callback without model-event or write-tally auditing (bulk
     * imports that already summarise themselves, maintenance jobs...).
     * Explicit Audit::log() calls inside still record.
     */
    public static function withoutAuditing(callable $callback): mixed
    {
        $context = app(AuditContext::class);
        $context->suppressDepth++;

        try {
            return $callback();
        } finally {
            $context->suppressDepth--;
        }
    }

    public static function recorder(): AuditRecorder
    {
        return app(AuditRecorder::class);
    }

    private static function build(string $event, string $description, array $options): array
    {
        $entry = [
            'event' => $event,
            'description' => $description,
            'category' => $options['category'] ?? 'system',
            'severity' => $options['severity'] ?? 'info',
            'outcome' => $options['outcome'] ?? 'success',
            'old' => $options['old'] ?? null,
            'new' => $options['new'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ];

        if (($subject = $options['subject'] ?? null) instanceof Model) {
            $entry['entity_type'] = class_basename($subject);
            $entry['entity_id'] = $subject->getKey();
            $entry['entity_label'] = ModelEventSubscriber::labelFor($subject);

            if (!array_key_exists('school_id', $options)) {
                $entry['school_id'] = ModelEventSubscriber::schoolIdFor($subject);
            }
        }

        foreach (['entity_type', 'entity_id', 'entity_label', 'school_id', 'actor', 'status_code'] as $key) {
            if (array_key_exists($key, $options)) {
                $entry[$key] = $options[$key];
            }
        }

        return $entry;
    }
}

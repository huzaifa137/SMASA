<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * One row of the append-only audit trail.
 *
 * This model is READ-ONLY from the application's point of view: rows are
 * written exclusively by App\Audit\AuditRecorder (query builder) and any
 * attempt to save, update or delete one through Eloquent throws. Retention
 * clean-up is the single sanctioned exception (`php artisan audit:prune`).
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $guarded = ['*'];

    /** Fields covered by the signature, in this exact order. */
    public const SIGNED_FIELDS = [
        'uuid', 'request_id', 'occurred_at', 'event', 'category', 'severity', 'outcome',
        'description', 'actor_type', 'actor_id', 'actor_name', 'actor_role',
        'school_id', 'school_name', 'entity_type', 'entity_id', 'entity_label',
        'old_values', 'new_values', 'metadata', 'ip_address', 'method', 'url',
        'route_name', 'status_code', 'source',
    ];

    protected static function booted(): void
    {
        $block = function () {
            throw new LogicException('Audit log entries are immutable.');
        };

        static::saving($block);
        static::updating($block);
        static::deleting($block);
    }

    // ── Signing ──────────────────────────────────────────────────────────

    public static function signingKey(): string
    {
        $key = config('audit.signing_key') ?: config('app.key');

        if (is_string($key) && str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7)) ?: $key;
        }

        return (string) $key;
    }

    /**
     * HMAC-SHA256 over the signed fields of a row (array or stdClass-like).
     */
    public static function sign(array $row): string
    {
        $payload = [];

        foreach (self::SIGNED_FIELDS as $field) {
            $value = $row[$field] ?? '';
            $payload[] = $value === null ? '' : (string) $value;
        }

        return hash_hmac(
            'sha256',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            self::signingKey()
        );
    }

    public function signatureIsValid(): bool
    {
        $row = [];
        foreach (self::SIGNED_FIELDS as $field) {
            $row[$field] = $this->getRawOriginal($field);
        }

        return hash_equals($this->getRawOriginal('signature') ?? '', self::sign($row));
    }

    // ── Presentation helpers ─────────────────────────────────────────────

    /** Occurrence time converted from stored UTC to the app timezone. */
    public function getOccurredLocalAttribute(): Carbon
    {
        return Carbon::parse($this->getRawOriginal('occurred_at'), 'UTC')
            ->setTimezone(config('app.timezone'));
    }

    public function getOldArrayAttribute(): array
    {
        return self::decode($this->getRawOriginal('old_values'));
    }

    public function getNewArrayAttribute(): array
    {
        return self::decode($this->getRawOriginal('new_values'));
    }

    public function getMetaArrayAttribute(): array
    {
        return self::decode($this->getRawOriginal('metadata'));
    }

    /**
     * Field-level before/after pairs.
     *
     * @return array<int, array{field:string, old:mixed, new:mixed, kind:string}>
     */
    public function getDiffAttribute(): array
    {
        $old = $this->old_array;
        $new = $this->new_array;
        $rows = [];

        foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $field) {
            $hasOld = array_key_exists($field, $old);
            $hasNew = array_key_exists($field, $new);

            $rows[] = [
                'field' => (string) $field,
                'old' => $old[$field] ?? null,
                'new' => $new[$field] ?? null,
                'kind' => $hasOld && $hasNew ? 'changed' : ($hasNew ? 'added' : 'removed'),
            ];
        }

        return $rows;
    }

    public function getChangeCountAttribute(): int
    {
        return count(array_unique(array_merge(
            array_keys($this->old_array),
            array_keys($this->new_array)
        )));
    }

    public function getActorInitialsAttribute(): string
    {
        $name = trim((string) $this->actor_name);
        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/', $name);
        $initials = mb_substr($parts[0], 0, 1);
        if (count($parts) > 1) {
            $initials .= mb_substr(end($parts), 0, 1);
        }

        return mb_strtoupper($initials);
    }

    public function getEventLabelAttribute(): string
    {
        return config('audit.event_labels.' . $this->event)
            ?? ucfirst(str_replace(['_', '.'], ' ', (string) $this->event));
    }

    private static function decode(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }
}

<?php

namespace App\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Listens to Eloquent's wildcard model events, so EVERY model - existing and
 * future - is audited without touching the model or controller.
 *
 * Optional hooks a model may define:
 *   public array $auditExclude = ['some_noisy_column'];
 *   public function auditLabel(): string   // human name shown in the trail
 */
class ModelEventSubscriber
{
    /** @var array<string, bool> */
    private array $audited = [];

    public function __construct(
        private AuditContext $context,
        private AuditRecorder $recorder,
    ) {
    }

    /**
     * @param  array<int, mixed>  $payload  [Model]
     */
    public function handle(string $eventName, array $payload): void
    {
        $model = $payload[0] ?? null;

        if (!$model instanceof Model
            || $this->context->isSuppressed()
            || !config('audit.models.enabled', true)
        ) {
            return;
        }

        if (!preg_match('/^eloquent\.(created|updated|deleted|restored): /', $eventName, $m)) {
            return;
        }

        if (!$this->shouldAudit(get_class($model))) {
            return;
        }

        if ($this->context->modelEntries >= (int) config('audit.models.max_per_request', 5000)) {
            $this->context->modelEntriesSuppressed++;

            return;
        }

        $entry = match ($m[1]) {
            'created' => $this->created($model),
            'updated' => $this->updated($model),
            'deleted' => $this->deleted($model),
            'restored' => $this->restored($model),
        };

        if ($entry === null) {
            return;
        }

        $this->context->modelEntries++;
        $this->recorder->record($entry);
    }

    // ── per-event builders ───────────────────────────────────────────────

    private function created(Model $model): array
    {
        return $this->base($model, 'created', [
            'new' => $this->filter($model, $model->getAttributes()),
        ]);
    }

    private function updated(Model $model): ?array
    {
        $changes = $this->filter($model, $model->getChanges());

        if ($changes === []) {
            return null; // only timestamps / ignored columns changed
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getRawOriginal($key);
        }

        $event = 'updated';
        $extra = [];

        // Password changes get their own, security-tagged event. The values
        // themselves are always redacted by the masker.
        if (array_key_exists('password', $changes)) {
            $event = 'password_changed';
            $extra = ['category' => 'security', 'severity' => 'notice'];
        } elseif (array_intersect(array_keys($changes), (array) config('audit.models.security_attributes', []))) {
            // Role / super-admin / active-status changes are access control.
            $extra = ['category' => 'security', 'severity' => 'notice'];
        }

        return $this->base($model, $event, ['old' => $old, 'new' => $changes] + $extra);
    }

    private function deleted(Model $model): array
    {
        $soft = method_exists($model, 'trashed') && $model->trashed();

        return $this->base($model, 'deleted', [
            // Full snapshot: this is what lets an administrator see exactly
            // what was lost.
            'old' => $this->filter($model, $model->getAttributes()),
            'severity' => 'warning',
            'metadata' => $soft ? ['soft_deleted' => true] : null,
        ]);
    }

    private function restored(Model $model): array
    {
        return $this->base($model, 'restored', ['severity' => 'notice']);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function base(Model $model, string $event, array $extra): array
    {
        $isSecurity = $this->isInList($model, 'security_models');
        $isNotice = $isSecurity || $this->isInList($model, 'notice_models');
        $entity = class_basename($model);
        $label = self::labelFor($model);

        $entry = [
            'event' => $event,
            'category' => $isSecurity ? 'security' : 'data',
            'severity' => $isNotice ? 'notice' : 'info',
            'entity_type' => $entity,
            'entity_id' => $model->getKey(),
            'entity_label' => $label,
        ];

        $entry = array_merge($entry, array_filter($extra, fn ($v) => $v !== null));

        if (($schoolId = self::schoolIdFor($model)) !== null) {
            $entry['school_id'] = $schoolId;
        }

        $entry['description'] = $this->describe($event, $model, $entry);

        return $entry;
    }

    private function describe(string $event, Model $model, array $entry): string
    {
        $verb = match ($event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'password_changed' => 'Changed password of',
            default => ucfirst($event),
        };

        $text = $verb . ' ' . Str::headline(class_basename($model)) . ' #' . $model->getKey();

        if (!empty($entry['entity_label'])) {
            $text .= ' (' . $entry['entity_label'] . ')';
        }

        if ($event === 'updated' && !empty($entry['new'])) {
            $fields = array_keys($entry['new']);
            $shown = array_slice($fields, 0, 8);
            $text .= ' — changed: ' . implode(', ', $shown);
            if (count($fields) > 8) {
                $text .= ' (+' . (count($fields) - 8) . ' more)';
            }
        }

        return $text;
    }

    // ── helpers ──────────────────────────────────────────────────────────

    /** Drops excluded attributes (global, per-model hook). */
    private function filter(Model $model, array $attributes): array
    {
        $excluded = (array) config('audit.models.excluded_attributes', []);

        if (property_exists($model, 'auditExclude')) {
            $excluded = array_merge($excluded, (array) $model->auditExclude);
        }

        return array_diff_key($attributes, array_flip($excluded));
    }

    private function shouldAudit(string $class): bool
    {
        if (isset($this->audited[$class])) {
            return $this->audited[$class];
        }

        $allowed = false;
        foreach ((array) config('audit.models.namespaces', ['App\\']) as $namespace) {
            if (str_starts_with($class, $namespace)) {
                $allowed = true;
                break;
            }
        }

        if ($allowed) {
            foreach ((array) config('audit.models.excluded', []) as $excluded) {
                if (is_a($class, $excluded, true)) {
                    $allowed = false;
                    break;
                }
            }
        }

        return $this->audited[$class] = $allowed;
    }

    private function isInList(Model $model, string $list): bool
    {
        foreach ((array) config('audit.models.' . $list, []) as $class) {
            if ($model instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /** Human-readable name of a record, without triggering relation queries. */
    public static function labelFor(Model $model): ?string
    {
        if (method_exists($model, 'auditLabel')) {
            return Str::limit((string) $model->auditLabel(), 150, '…');
        }

        $attrs = $model->getAttributes();
        $get = fn (string $key) => isset($attrs[$key]) && is_scalar($attrs[$key]) && trim((string) $attrs[$key]) !== ''
            ? trim((string) $attrs[$key]) : null;

        $first = $get('firstname') ?? $get('first_name');
        $last = $get('lastname') ?? $get('surname') ?? $get('last_name');
        if ($first || $last) {
            return Str::limit(trim($first . ' ' . $last), 150, '…');
        }

        foreach (['name', 'title', 'school_name', 'subject_name', 'exam_name', 'House', 'username', 'email', 'phone', 'phonenumber', 'registration_number', 'code', 'reference'] as $key) {
            if ($value = $get($key)) {
                return Str::limit($value, 150, '…');
            }
        }

        return null;
    }

    /** Tenant (school) a record belongs to, when it can be told from the record itself. */
    public static function schoolIdFor(Model $model): ?int
    {
        if ($model instanceof \App\Models\School) {
            return (int) $model->getKey() ?: null;
        }

        $attrs = $model->getAttributes();
        $value = $attrs['school_id'] ?? null;

        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }
}

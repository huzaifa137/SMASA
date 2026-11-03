<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * One school's choice of who receives one automatic notification.
 * Intentionally NOT using BelongsToSchool: it is always read/written with an
 * explicit school_id, also from queues and platform-admin screens.
 */
class NotificationRoutingRule extends Model
{
    public const MODE_DEFAULT = 'default';
    public const MODE_CUSTOM = 'custom';
    public const MODE_DISABLED = 'disabled';

    protected $table = 'notification_routing_rules';

    protected $fillable = ['school_id', 'event_key', 'mode', 'role_ids', 'teacher_ids', 'updated_by'];

    protected $casts = [
        'role_ids' => 'array',
        'teacher_ids' => 'array',
    ];

    /**
     * The school's rule for an event, or null (= built-in default). Never
     * throws: if the migration has not run yet, notifications keep working
     * with the defaults.
     */
    public static function lookup(int $schoolId, string $eventKey): ?self
    {
        try {
            return static::where('school_id', $schoolId)->where('event_key', $eventKey)->first();
        } catch (QueryException $e) {
            return null;
        }
    }
}

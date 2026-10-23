<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'action', 'subject_type', 'subject_id',
        'description', 'meta', 'ip_address', 'created_at',
    ];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function record(string $action, string $description, ?string $subjectType = null, ?int $subjectId = null, array $meta = []): void
    {
        try {
            static::create([
                'actor_id' => session('LoggedAdmin'),
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'description' => mb_substr($description, 0, 500),
                'meta' => $meta ?: null,
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Auditing must never break the action being audited.
            report($e);
        }
    }
}

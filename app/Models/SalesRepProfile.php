<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An admin user who works as a sales representative.
 * The person's name, e-mail and login live on `users`; this row holds what is
 * specific to the sales role (code, region, commission, status).
 */
class SalesRepProfile extends Model
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';

    protected $fillable = [
        'user_id', 'rep_code', 'region', 'commission_rate', 'joined_on',
        'status', 'deactivated_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'commission_rate' => 'float',
        'joined_on' => 'date',
        'deactivated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function targets()
    {
        return $this->hasMany(SalesTarget::class, 'user_id', 'user_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function scopeActive($q)
    {
        return $q->where('status', self::ACTIVE);
    }

    /** "BUKENYA HUZAIFA" — falls back to username / e-mail. */
    public static function displayName(?object $user): string
    {
        if (!$user) {
            return 'Unknown user';
        }

        $name = trim((string) ($user->name ?? ''));

        if ($name === '') {
            $name = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
        }

        return $name !== '' ? $name : (string) ($user->username ?? $user->email ?? 'User #' . ($user->id ?? '?'));
    }

    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = '';

        foreach (array_slice(array_filter($parts), 0, 2) as $p) {
            $letters .= mb_strtoupper(mb_substr($p, 0, 1));
        }

        return $letters !== '' ? $letters : '?';
    }
}

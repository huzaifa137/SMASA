<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPasswordReset extends Model
{
    protected $table = 'teacher_password_resets';

    protected $fillable = [
        'email',
        'school_id',
        'token',
        'token_hash',
        'link_status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check whether this reset token is still valid:
     * - link_status must be 0 (not yet used)
     * - expires_at must be in the future
     */
    public function isValid(): bool
    {
        return $this->link_status === 0 && now()->lessThan($this->expires_at);
    }

    /**
     * Mark this token as consumed so it cannot be used again.
     */
    public function markUsed(): void
    {
        $this->update(['link_status' => 1]);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

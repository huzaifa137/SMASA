<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DemoRequest extends Model
{
    protected $fillable = [
        'reference', 'school_name', 'contact_person', 'phone', 'email', 'school_type',
        'student_range', 'message', 'status', 'internal_notes', 'handled_by', 'contacted_at',
        'notified_at', 'notify_error', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public static function makeReference(): string
    {
        do {
            $ref = 'DEMO-' . strtoupper(Str::random(6));
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }

    public function schoolTypeLabel(): string
    {
        return config('demo_requests.school_types.' . $this->school_type, $this->school_type);
    }

    public function studentRangeLabel(): string
    {
        return config('demo_requests.student_ranges.' . $this->student_range, $this->student_range);
    }

    public function statusMeta(): array
    {
        return config('demo_requests.statuses.' . $this->status, ['label' => ucfirst($this->status), 'color' => '#6b7280']);
    }

    public function scopeNewOnes($q)
    {
        return $q->where('status', 'new');
    }
}

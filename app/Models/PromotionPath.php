<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class PromotionPath extends Model
{
    use BelongsToSchool;

    public const STREAM_SAME_NAME = 'same_name';
    public const STREAM_FIXED = 'fixed';
    public const STREAM_MANUAL = 'manual';

    protected $fillable = [
        'school_id', 'from_class_id', 'to_class_id', 'is_final',
        'stream_mode', 'fallback_stream', 'auto_eligible',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'auto_eligible' => 'boolean',
    ];
}

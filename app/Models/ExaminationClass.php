<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminationClass extends Model
{
    protected $fillable = [
        'examination_id',
        'class_id',
        'stream_id',
        'school_id',
        'results_released_at',
        'released_by',
        'grading_scheme_id',
    ];

    protected $casts = [
        'results_released_at' => 'datetime',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }

    public function gradingScheme()
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    /**
     * Check if this class's results have been released.
     */
    public function isReleased(): bool
    {
        return !is_null($this->results_released_at);
    }

    /**
     * Resolve the grading bands for this specific class in this exam.
     * Falls back to the parent examination's default scheme if none is set.
     */
    public function resolvedGradingBands()
    {
        $scheme = $this->gradingScheme ?? $this->examination?->gradingScheme;
        return $scheme ? $scheme->bands : collect();
    }
}
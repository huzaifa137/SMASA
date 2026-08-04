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
    ];

    protected $casts = [
        'results_released_at' => 'datetime',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }

    /**
     * Released independently of the exam-level status. Also true once the
     * whole exam moves to results_released / closed with released results
     * — a class row is the exception (release early), not the only path.
     */
    public function isReleased(): bool
    {
        return $this->results_released_at !== null
            || $this->examination?->status === 'results_released';
    }
}

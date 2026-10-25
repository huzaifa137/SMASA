<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One stretch of time during which one representative owned one school.
 * ended_at = null means "owns it now". Rows are never edited after they are
 * closed: they are the evidence behind cash credit and the transfer log.
 */
class SchoolRepAssignment extends Model
{
    protected $fillable = [
        'school_id', 'user_id', 'started_at', 'ended_at', 'type',
        'transfer_batch_id', 'from_user_id', 'note', 'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function batch()
    {
        return $this->belongsTo(SalesTransferBatch::class, 'transfer_batch_id');
    }

    public function scopeCurrent($q)
    {
        return $q->whereNull('ended_at');
    }
}

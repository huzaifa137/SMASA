<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class PromotionBatch extends Model
{
    use BelongsToSchool;

    public const PENDING = 'pending';
    public const COMPLETED = 'completed';
    public const PARTIAL = 'partially_reverted';
    public const REVERTED = 'reverted';
    public const DISCARDED = 'discarded';

    protected $fillable = [
        'school_id', 'reference', 'source', 'status', 'from_academic_year', 'to_academic_year',
        'scope_label', 'total', 'promoted', 'repeated', 'graduated', 'note',
        'created_by', 'created_by_name', 'executed_at', 'reverted_at', 'reverted_by_name',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'reverted_at' => 'datetime',
    ];

    public function records()
    {
        return $this->hasMany(PromotionRecord::class, 'batch_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function canBeReverted(): bool
    {
        return in_array($this->status, [self::COMPLETED, self::PARTIAL], true);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class PromotionRecord extends Model
{
    use BelongsToSchool;

    public const PROMOTED = 'promoted';
    public const REPEATED = 'repeated';
    public const GRADUATED = 'graduated';

    public const PLANNED = 'planned';
    public const APPLIED = 'applied';
    public const REVERTED = 'reverted';
    public const SKIPPED = 'skipped';

    protected $fillable = [
        'batch_id', 'school_id', 'student_id', 'student_name', 'admission_number',
        'outcome', 'status', 'from_class_id', 'from_stream', 'to_class_id', 'to_stream',
        'from_academic_year', 'average', 'fee_balance', 'notes', 'reverted_at', 'reverted_by_name',
    ];

    protected $casts = [
        'average' => 'float',
        'fee_balance' => 'float',
        'reverted_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(PromotionBatch::class, 'batch_id');
    }

    /** Moved a student (as opposed to a "repeat" which only records the decision). */
    public function movesStudent(): bool
    {
        return in_array($this->outcome, [self::PROMOTED, self::GRADUATED], true);
    }
}

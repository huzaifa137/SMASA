<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One hand-over of schools from one representative to another (or to the unassigned pool). */
class SalesTransferBatch extends Model
{
    protected $fillable = [
        'reference', 'from_user_id', 'to_user_id', 'school_count', 'student_count',
        'outstanding_amount', 'overdue_amount', 'reason_code', 'notes', 'created_by',
    ];

    protected $casts = [
        'outstanding_amount' => 'float',
        'overdue_amount' => 'float',
    ];

    public function assignments()
    {
        return $this->hasMany(SchoolRepAssignment::class, 'transfer_batch_id');
    }

    public function getReasonLabelAttribute(): string
    {
        return config('sales_team.transfer_reasons.' . $this->reason_code, ucfirst(str_replace('_', ' ', $this->reason_code)));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Examination extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_code',
        'exam_name',
        'exam_type',
        'term',
        'academic_year',
        'start_date',
        'end_date',
        'marks_entry_deadline',
        'description',
        'total_marks',
        'pass_mark',
        'status',
        'school_id',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'start_date'            => 'date',
        'end_date'              => 'date',
        'marks_entry_deadline'  => 'date',
        'published_at'          => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function examinationClasses()
    {
        return $this->hasMany(ExaminationClass::class);
    }

    public function marks()
    {
        return $this->hasMany(ExaminationMark::class);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Auto-close examination if the marks entry deadline has passed.
     */
    public function syncStatus(): void
    {
        if ($this->status === 'marks_entry' && Carbon::now()->isAfter($this->marks_entry_deadline)) {
            $this->update(['status' => 'closed']);
        }

        if ($this->status === 'active' && Carbon::now()->isAfter($this->end_date)) {
            $this->update(['status' => 'marks_entry']);
        }
    }

    /**
     * Return a Bootstrap badge color for current status.
     */
    public function statusBadge(): string
    {
        return match($this->status) {
            'draft'            => 'secondary',
            'active'           => 'success',
            'marks_entry'      => 'warning',
            'closed'           => 'danger',
            'results_released' => 'primary',
            default            => 'secondary',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'draft'            => 'Draft',
            'active'           => 'Active',
            'marks_entry'      => 'Marks Entry Open',
            'closed'           => 'Closed',
            'results_released' => 'Results Released',
            default            => 'Unknown',
        };
    }
}

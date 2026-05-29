<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'name', 'academic_year', 'term', 'class_level',
        'student_type', 'total_amount', 'is_active', 'notes', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(FeeStructureItem::class)->orderBy('sort_order');
    }

    public function allocations()
    {
        return $this->hasMany(StudentFeeAllocation::class);
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('amount')]);
    }

    public function termLabel(): string
    {
        return match ((int) $this->term) {
            1 => 'Term 1', 2 => 'Term 2', 3 => 'Term 3', default => 'Term ' . $this->term,
        };
    }
}
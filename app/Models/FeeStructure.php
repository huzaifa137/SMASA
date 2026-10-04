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

    /** True when this structure is not tied to a single term (term is NULL). */
    public function appliesToAllTerms(): bool
    {
        return $this->term === null || $this->term === '';
    }

    /** Whether this structure can be billed for the given term (1-3). */
    public function appliesToTerm(int $term): bool
    {
        return $this->appliesToAllTerms() || (int) $this->term === $term;
    }

    public function termLabel(): string
    {
        if ($this->appliesToAllTerms()) {
            return 'All Terms';
        }

        return \App\Support\Term::label($this->term);
    }
}

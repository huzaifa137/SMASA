<?php

namespace App\Models;

use App\Support\Term;
use Illuminate\Database\Eloquent\Model;

class AcademicTerm extends Model
{
    protected $fillable = ['academic_year_id', 'term', 'start_date', 'end_date', 'is_active'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function getTermLabelAttribute(): string
    {
        return Term::label($this->term);
    }

    /** "Term II · 2026" — same wording as BillingTerm::name. */
    public function getNameAttribute(): string
    {
        return $this->term_label . ' · ' . ($this->year->name ?? '');
    }

    /** The platform's active term, if the administrators have set one. */
    public static function active(): ?self
    {
        return static::with('year')->where('is_active', true)->first();
    }

    /** The billing term (if created yet) that belongs to this calendar term. */
    public function billingTerm(): ?BillingTerm
    {
        return BillingTerm::where('academic_year_id', $this->academic_year_id)->where('term', $this->term)->first();
    }
}

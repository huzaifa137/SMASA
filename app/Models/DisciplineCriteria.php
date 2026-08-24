<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplineCriteria extends Model
{
    protected $table = 'discipline_criteria';

    protected $fillable = [
        'school_id',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ratings()
    {
        return $this->hasMany(StudentDisciplineRating::class, 'discipline_criteria_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}

<?php

namespace App\Models\Concerns;

use App\Models\Scopes\SchoolScope;
use App\Support\SchoolContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Use on every model whose table has a NOT NULL `school_id`.
 *
 *   Student::findOrFail($id)                  // already confined by the global scope
 *   Student::forCurrentSchool()->findOrFail() // explicit second layer, also for admins who entered a school
 */
trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope());

        // Never trust a school_id coming from the request on creation.
        static::creating(function ($model) {
            $schoolId = SchoolContext::enforcedId();

            if ($schoolId !== null) {
                $model->school_id = $schoolId;
            }
        });
    }

    public function scopeForCurrentSchool(Builder $query): Builder
    {
        $schoolId = SchoolContext::id();

        // No school in session (e.g. an admin on a cross-school page): fail closed.
        return $query->where($this->getTable() . '.school_id', $schoolId ?? 0);
    }
}

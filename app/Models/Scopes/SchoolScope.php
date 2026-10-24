<?php

namespace App\Models\Scopes;

use App\Support\SchoolContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Confines every query on a school-owned model to the school of the current
 * school / teacher / student session. Because route-model binding and
 * findOrFail() go through the model's query builder, changing an id in the URL
 * to a record of another school yields a 404 instead of that school's data.
 *
 * Not applied to platform administrators, the console, queues or guests.
 * Opt out explicitly with Model::withoutGlobalScope(SchoolScope::class).
 */
class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $schoolId = SchoolContext::enforcedId();

        if ($schoolId !== null) {
            $builder->where($model->getTable() . '.school_id', $schoolId);
        }
    }
}

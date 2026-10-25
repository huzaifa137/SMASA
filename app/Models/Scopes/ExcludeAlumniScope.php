<?php

namespace App\Models\Scopes;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Students who graduated out of a final class sit in the reserved class
 * marker Student::ALUMNI_CLASS. This keeps them out of every Eloquent
 * roster, head-count, search and billing count without touching each query.
 *
 * Opt back in with Student::withAlumni() / Student::onlyAlumni().
 * (Raw DB::table('students') queries filter by a real class id, so alumni
 * never match them either.)
 */
class ExcludeAlumniScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getTable() . '.senior', '!=', Student::ALUMNI_CLASS);
    }
}

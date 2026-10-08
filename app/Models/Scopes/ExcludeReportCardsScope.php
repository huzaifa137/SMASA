<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Report-card "exams" (examinations.o_level_mode = 'report_card') are computed
 * artefacts, not examinations anyone sits. Hiding them by default keeps them
 * out of the exam board, marks-entry portal, progress counters, reports, the
 * parent portal and previous-exam growth lookups without touching each one.
 * Only the pass-slip / report-card screens opt back in with
 * Examination::withReportCards().
 */
class ExcludeReportCardsScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        $builder->where(function ($q) use ($table) {
            $q->whereNull($table . '.o_level_mode')
                ->orWhere($table . '.o_level_mode', '!=', 'report_card');
        });
    }
}

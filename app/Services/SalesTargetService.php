<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\SalesRepProfile;
use App\Models\SalesTarget;
use App\Support\SalesAudit;
use DomainException;
use Illuminate\Support\Carbon;

/** Creating, changing and removing targets. Progress itself lives in SalesPerformanceService. */
class SalesTargetService
{
    /**
     * Set the same target for one or many representatives.
     * A representative who already has a target for that metric and period gets it updated.
     *
     * @param int[] $userIds
     * @param array{type:string,academic_term_id?:?int,label?:?string,start?:?string,end?:?string} $period
     * @return array{created:int, updated:int}
     */
    public function set(array $userIds, string $metric, float $value, array $period, ?string $notes, ?int $actorId): array
    {
        if (!isset(config('sales_team.metrics')[$metric])) {
            throw new DomainException('Unknown target type.');
        }

        if ($value <= 0) {
            throw new DomainException('The target must be greater than zero.');
        }

        if (config("sales_team.metrics.{$metric}.unit") === 'percent' && $value > 100) {
            throw new DomainException('A collection-rate target cannot be more than 100%.');
        }

        [$start, $end, $label, $termId] = $this->resolvePeriod($period);

        $userIds = array_values(array_unique(array_map('intval', $userIds)));

        $valid = SalesRepProfile::whereIn('user_id', $userIds)->where('status', SalesRepProfile::ACTIVE)->pluck('user_id')->map(fn ($v) => (int) $v)->all();

        if (!$valid) {
            throw new DomainException('Choose at least one active representative.');
        }

        $created = 0;
        $updated = 0;

        foreach ($valid as $uid) {
            $existing = SalesTarget::where('user_id', $uid)->where('metric', $metric)
                ->whereDate('period_start', $start->toDateString())->whereDate('period_end', $end->toDateString())->first();

            $attrs = [
                'target_value' => $value,
                'period_label' => $label,
                'academic_term_id' => $termId,
                'notes' => $notes !== null && trim($notes) !== '' ? mb_substr(trim($notes), 0, 500) : null,
            ];

            if ($existing) {
                $existing->update($attrs);
                $updated++;
            } else {
                SalesTarget::create($attrs + [
                    'user_id' => $uid,
                    'metric' => $metric,
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                    'created_by' => $actorId,
                ]);
                $created++;
            }
        }

        SalesAudit::log(
            'sales_target_set',
            sprintf('%s target of %s set for %d representative(s) for %s.', config("sales_team.metrics.{$metric}.label"), $value, count($valid), $label),
            ['metric' => $metric, 'value' => $value, 'period' => $label, 'users' => $valid, 'created' => $created, 'updated' => $updated],
            null,
            'info'
        );

        return ['created' => $created, 'updated' => $updated];
    }

    public function update(SalesTarget $target, float $value, ?string $notes): SalesTarget
    {
        if ($value <= 0) {
            throw new DomainException('The target must be greater than zero.');
        }

        if (($target->metricConfig()['unit'] ?? null) === 'percent' && $value > 100) {
            throw new DomainException('A collection-rate target cannot be more than 100%.');
        }

        $old = $target->target_value;
        $target->update([
            'target_value' => $value,
            'notes' => $notes !== null && trim($notes) !== '' ? mb_substr(trim($notes), 0, 500) : null,
        ]);

        SalesAudit::log('sales_target_changed', "Target #{$target->id} changed from {$old} to {$value}.", ['target_id' => $target->id, 'old' => $old, 'new' => $value], null, 'info');

        return $target;
    }

    public function delete(SalesTarget $target): void
    {
        $id = $target->id;
        $target->delete();

        SalesAudit::log('sales_target_deleted', "Target #{$id} deleted.", ['target_id' => $id, 'user_id' => $target->user_id, 'metric' => $target->metric], null, 'notice');
    }

    /** @return array{0:Carbon,1:Carbon,2:string,3:?int} start, end, label, academic term id */
    public function resolvePeriod(array $period): array
    {
        if (($period['type'] ?? 'term') === 'term') {
            $term = AcademicTerm::with('year')->find((int) ($period['academic_term_id'] ?? 0));

            if (!$term) {
                throw new DomainException('Choose a term for the target.');
            }

            return [Carbon::parse($term->start_date)->startOfDay(), Carbon::parse($term->end_date)->startOfDay(), $term->name, (int) $term->id];
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', (string) ($period['start'] ?? ''))->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', (string) ($period['end'] ?? ''))->startOfDay();
        } catch (\Throwable) {
            throw new DomainException('Enter a valid start and end date.');
        }

        if ($end->lt($start)) {
            throw new DomainException('The end date cannot be before the start date.');
        }

        $label = trim((string) ($period['label'] ?? ''));

        return [$start, $end, $label !== '' ? mb_substr($label, 0, 80) : $start->format('j M Y') . ' – ' . $end->format('j M Y'), null];
    }
}

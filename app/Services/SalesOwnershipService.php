<?php

namespace App\Services;

use App\Models\School;
use App\Models\SalesRepProfile;
use App\Models\SalesTransferBatch;
use App\Support\SalesAudit;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Who is responsible for each school — and every change of hands.
 *
 * schools.sales_rep_id is the CURRENT owner. school_rep_assignments records
 * every stretch of ownership so that, after a transfer, the previous owner
 * keeps credit for the cash they collected while they owned the school and
 * the new owner takes over everything from that moment on.
 *
 * Ownership can only be changed through this class (it is deliberately not in
 * School::$fillable), and every change is written to the audit trail.
 */
class SalesOwnershipService
{
    public function __construct(private SalesPerformanceService $performance)
    {
    }

    // ── New enrolments ──────────────────────────────────────────────────

    /**
     * Called right after a school is created.
     *
     * Owner: the representative chosen on the form; otherwise the admin who is
     * enrolling, if they are an active representative; otherwise nobody (the
     * school lands in the Unassigned pool).
     * Enrolment credit: the chosen representative, otherwise whoever enrolled it.
     */
    public function registerEnrollment(School $school, ?int $actorId, ?int $chosenRepId = null): ?int
    {
        if ($chosenRepId) {
            $this->requireActiveRep($chosenRepId);
        }

        $actorIsRep = $actorId && SalesRepProfile::where('user_id', $actorId)->where('status', SalesRepProfile::ACTIVE)->exists();

        $enrolledBy = $chosenRepId ?: $actorId;
        $owner = $chosenRepId ?: ($actorIsRep ? $actorId : null);

        $now = now();

        DB::transaction(function () use ($school, $enrolledBy, $owner, $actorId, $now) {
            DB::table('schools')->where('id', $school->id)->update([
                'enrolled_by_id' => $enrolledBy,
                'sales_rep_id' => $owner,
            ]);

            if ($owner) {
                DB::table('school_rep_assignments')->insert([
                    'school_id' => $school->id,
                    'user_id' => $owner,
                    'started_at' => $now,
                    'type' => 'enrolled',
                    'created_by' => $actorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        SalesAudit::log(
            'sales_school_enrolled',
            $owner
                ? "School {$school->name} enrolled and assigned to representative #{$owner}."
                : "School {$school->name} enrolled with no sales representative (unassigned).",
            ['school_id' => $school->id, 'owner' => $owner, 'enrolled_by' => $enrolledBy],
            (int) $school->id,
            'info'
        );

        return $owner;
    }

    // ── Giving an owner to schools that have none ───────────────────────

    /**
     * @param int[] $schoolIds
     * @return int how many schools were actually assigned
     */
    public function assign(array $schoolIds, int $repId, ?int $actorId, ?string $note = null, bool $creditEnrollment = false): int
    {
        $this->requireActiveRep($repId);

        $schoolIds = array_values(array_unique(array_map('intval', $schoolIds)));

        if (!$schoolIds) {
            return 0;
        }

        $assigned = DB::transaction(function () use ($schoolIds, $repId, $actorId, $note, $creditEnrollment) {
            $now = now();
            $count = 0;

            foreach (array_chunk($schoolIds, 500) as $chunk) {
                $schools = DB::table('schools')->whereIn('id', $chunk)->whereNull('sales_rep_id')->lockForUpdate()->get(['id', 'enrolled_by_id']);

                foreach ($schools as $s) {
                    $update = ['sales_rep_id' => $repId];
                    if ($creditEnrollment && !$s->enrolled_by_id) {
                        $update['enrolled_by_id'] = $repId;
                    }

                    DB::table('schools')->where('id', $s->id)->update($update);

                    DB::table('school_rep_assignments')->insert([
                        'school_id' => $s->id,
                        'user_id' => $repId,
                        'started_at' => $now,
                        'type' => 'assigned',
                        'note' => $note ? Str::limit($note, 500, '') : null,
                        'created_by' => $actorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $count++;
                }
            }

            return $count;
        });

        if ($assigned > 0) {
            SalesAudit::log(
                'sales_schools_assigned',
                "{$assigned} unassigned school(s) assigned to representative #{$repId}.",
                ['rep' => $repId, 'count' => $assigned, 'school_ids' => array_slice($schoolIds, 0, 200), 'note' => $note]
            );
        }

        return $assigned;
    }

    // ── Transfers ───────────────────────────────────────────────────────

    /**
     * Hand schools from one representative to another (or release them to the
     * unassigned pool when $toId is null).
     *
     * @param int[]|null $schoolIds null = every school the representative owns
     */
    public function transfer(int $fromId, ?int $toId, ?array $schoolIds, string $reason, ?string $notes, ?int $actorId): SalesTransferBatch
    {
        if ($toId !== null && $toId === $fromId) {
            throw new DomainException('Choose a different representative to receive the schools.');
        }

        if (!array_key_exists($reason, config('sales_team.transfer_reasons', []))) {
            throw new DomainException('Choose a reason for the transfer.');
        }

        $notes = $notes !== null ? trim($notes) : null;

        if ($reason === 'other' && ($notes === null || $notes === '')) {
            throw new DomainException('Explain the reason for the transfer in the note.');
        }

        if ($toId !== null) {
            $this->requireActiveRep($toId);
        }

        if ($schoolIds !== null) {
            $schoolIds = array_values(array_unique(array_map('intval', $schoolIds)));

            if (!$schoolIds) {
                throw new DomainException('Select at least one school to transfer.');
            }
        }

        $batch = DB::transaction(function () use ($fromId, $toId, $schoolIds, $reason, $notes, $actorId) {
            $ids = DB::table('schools')
                ->where('sales_rep_id', $fromId)
                ->when($schoolIds !== null, fn ($q) => $q->whereIn('id', $schoolIds))
                ->lockForUpdate()
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->all();

            if (!$ids) {
                throw new DomainException('None of the selected schools belong to that representative any more. Reload the page and try again.');
            }

            $figures = $this->performance->handoverFigures($ids);
            $now = now();

            $batch = SalesTransferBatch::create([
                'reference' => 'TRF-' . Str::uuid(),
                'from_user_id' => $fromId,
                'to_user_id' => $toId,
                'school_count' => count($ids),
                'student_count' => $figures['students'],
                'outstanding_amount' => $figures['outstanding'],
                'overdue_amount' => $figures['overdue'],
                'reason_code' => $reason,
                'notes' => $notes ? Str::limit($notes, 2000, '') : null,
                'created_by' => $actorId,
            ]);

            $batch->update(['reference' => sprintf('TRF-%s-%05d', $now->format('Y'), $batch->id)]);

            foreach (array_chunk($ids, 500) as $chunk) {
                // Close the previous owner's stretch of ownership …
                DB::table('school_rep_assignments')
                    ->whereIn('school_id', $chunk)
                    ->whereNull('ended_at')
                    ->update(['ended_at' => $now, 'updated_at' => $now]);

                // … and open the new owner's.
                if ($toId !== null) {
                    DB::table('school_rep_assignments')->insert(array_map(fn ($sid) => [
                        'school_id' => $sid,
                        'user_id' => $toId,
                        'started_at' => $now,
                        'type' => 'transfer',
                        'transfer_batch_id' => $batch->id,
                        'from_user_id' => $fromId,
                        'created_by' => $actorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $chunk));
                }

                DB::table('schools')->whereIn('id', $chunk)->update(['sales_rep_id' => $toId]);
            }

            return $batch;
        });

        SalesAudit::log(
            'sales_schools_transferred',
            sprintf(
                '%s: %d school(s) moved from representative #%d to %s.',
                $batch->reference,
                $batch->school_count,
                $fromId,
                $toId ? "representative #{$toId}" : 'the unassigned pool'
            ),
            [
                'reference' => $batch->reference,
                'from' => $fromId,
                'to' => $toId,
                'schools' => $batch->school_count,
                'outstanding_handed_over' => $batch->outstanding_amount,
                'reason' => $reason,
                'notes' => $notes,
            ],
            null,
            'warning'
        );

        return $batch;
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    public function requireActiveRep(int $userId): SalesRepProfile
    {
        $profile = SalesRepProfile::where('user_id', $userId)->first();

        if (!$profile || !$profile->isActive()) {
            throw new DomainException('That person is not an active sales representative.');
        }

        return $profile;
    }
}

<?php

namespace App\Services;

use App\Models\SalesRepProfile;
use App\Support\SalesAudit;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Representatives: who they are, and when they start or stop working as one. */
class SalesTeamService
{
    public function __construct(private SalesOwnershipService $ownership)
    {
    }

    /** Active admin accounts that are not representatives yet. @return Collection<int, object> */
    public function candidates(): Collection
    {
        return DB::table('users')
            ->where('user_role', 'admin')
            ->where(function ($q) {
                $q->whereNull('is_active')->orWhere('is_active', '!=', 0);
            })
            ->whereNotIn('id', SalesRepProfile::select('user_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'firstname', 'lastname', 'email', 'username']);
    }

    public function create(int $userId, array $data, ?int $actorId): SalesRepProfile
    {
        $user = DB::table('users')->where('id', $userId)->where('user_role', 'admin')->first();

        if (!$user || (isset($user->is_active) && (int) $user->is_active === 0)) {
            throw new DomainException('Only an active admin account can be added as a sales representative.');
        }

        if (SalesRepProfile::where('user_id', $userId)->exists()) {
            throw new DomainException('That person is already a sales representative.');
        }

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $profile = SalesRepProfile::create([
                    'user_id' => $userId,
                    'rep_code' => $this->nextCode(),
                    'region' => $this->clean($data['region'] ?? null),
                    'commission_rate' => $data['commission_rate'] ?? null,
                    'joined_on' => $data['joined_on'] ?? now()->toDateString(),
                    'status' => SalesRepProfile::ACTIVE,
                    'notes' => $this->clean($data['notes'] ?? null),
                    'created_by' => $actorId,
                ]);

                SalesAudit::log('sales_rep_added', SalesRepProfile::displayName($user) . " added as sales representative {$profile->rep_code}.", ['user_id' => $userId], null, 'info');

                return $profile;
            } catch (QueryException $e) {
                // Two admins adding at once can collide on rep_code: try the next one.
                if ($attempt === 2) {
                    throw $e;
                }
            }
        }

        throw new DomainException('Could not create the representative. Please try again.');
    }

    public function update(SalesRepProfile $profile, array $data): SalesRepProfile
    {
        $profile->update([
            'region' => $this->clean($data['region'] ?? null),
            'commission_rate' => $data['commission_rate'] ?? null,
            'joined_on' => $data['joined_on'] ?? $profile->joined_on,
            'notes' => $this->clean($data['notes'] ?? null),
        ]);

        SalesAudit::log('sales_rep_updated', "Sales representative {$profile->rep_code} updated.", ['user_id' => $profile->user_id], null, 'info');

        return $profile;
    }

    /**
     * Stop a representative working. Their schools must go somewhere:
     * $destination is a user id of another active rep, or 'pool'.
     */
    public function deactivate(SalesRepProfile $profile, ?string $destination, ?int $actorId): int
    {
        if (!$profile->isActive()) {
            throw new DomainException('This representative is already inactive.');
        }

        $owned = DB::table('schools')->where('sales_rep_id', $profile->user_id)->count();
        $moved = 0;

        DB::transaction(function () use ($profile, $destination, $actorId, $owned, &$moved) {
            if ($owned > 0) {
                if ($destination === null || $destination === '') {
                    throw new DomainException("This representative owns {$owned} school(s). Choose who takes them over, or release them to the unassigned pool.");
                }

                $to = $destination === 'pool' ? null : (int) $destination;
                $batch = $this->ownership->transfer((int) $profile->user_id, $to, null, 'rep_left', 'Representative deactivated.', $actorId);
                $moved = $batch->school_count;
            }

            $profile->update(['status' => SalesRepProfile::INACTIVE, 'deactivated_at' => now()]);
        });

        SalesAudit::log('sales_rep_deactivated', "Sales representative {$profile->rep_code} deactivated; {$moved} school(s) handed over.", ['user_id' => $profile->user_id, 'moved' => $moved], null, 'warning');

        return $moved;
    }

    public function reactivate(SalesRepProfile $profile): void
    {
        $profile->update(['status' => SalesRepProfile::ACTIVE, 'deactivated_at' => null]);

        SalesAudit::log('sales_rep_reactivated', "Sales representative {$profile->rep_code} reactivated.", ['user_id' => $profile->user_id], null, 'info');
    }

    public function nextCode(): string
    {
        $max = 0;

        foreach (SalesRepProfile::pluck('rep_code') as $code) {
            if (preg_match('/(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return sprintf('REP-%04d', $max + 1);
    }

    private function clean(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value === '' ? null : $value;
    }
}

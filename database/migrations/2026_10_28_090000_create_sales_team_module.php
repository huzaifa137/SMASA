<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SALES TEAM module.
 *
 *  sales_rep_profiles       an admin user who works as a sales representative
 *  sales_targets            what a representative is expected to achieve in a period
 *  school_rep_assignments   full ownership history of every school (who, from when, until when)
 *  sales_transfer_batches   one row per hand-over of schools between representatives
 *  schools.sales_rep_id     the CURRENT owner (responsible for the school and its balances)
 *  schools.enrolled_by_id   who enrolled the school (enrolment credit never moves)
 *
 * schools.added_by is NOT used: on admin logins it is written from a student
 * session key and is empty or wrong for most schools. Existing schools are
 * backfilled from the audit trail instead (the actor of the School "created"
 * entry), falling back to added_by only when it points at an admin account.
 *
 * Plain unsignedBigInteger columns and no foreign keys, matching the rest of
 * this codebase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_rep_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('rep_code', 20)->unique();
            $table->string('region', 120)->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable();   // % of cash collected; null = not tracked
            $table->date('joined_on')->nullable();
            $table->string('status', 10)->default('active');         // active | inactive
            $table->timestamp('deactivated_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('metric', 30);                            // see config/sales_team.php
            $table->decimal('target_value', 16, 2);
            $table->string('period_label', 80);
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedBigInteger('academic_term_id')->nullable();
            $table->string('notes', 500)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'metric', 'period_start', 'period_end'], 'sales_targets_unique_period');
            $table->index(['period_start', 'period_end'], 'sales_targets_period_idx');
        });

        Schema::create('sales_transfer_batches', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->unsignedBigInteger('from_user_id')->nullable()->index();
            $table->unsignedBigInteger('to_user_id')->nullable()->index();   // null = released to the unassigned pool
            $table->unsignedInteger('school_count')->default(0);
            $table->unsignedInteger('student_count')->default(0);
            $table->decimal('outstanding_amount', 16, 2)->default(0);        // handed over at the time
            $table->decimal('overdue_amount', 16, 2)->default(0);
            $table->string('reason_code', 30);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('school_rep_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();                // null = current owner
            $table->string('type', 12);                              // enrolled | assigned | transfer | backfill
            $table->unsignedBigInteger('transfer_batch_id')->nullable()->index();
            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'ended_at'], 'sra_school_open_idx');
            $table->index(['user_id', 'ended_at'], 'sra_user_open_idx');
        });

        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'sales_rep_id')) {
                $table->unsignedBigInteger('sales_rep_id')->nullable()->index();
            }
            if (!Schema::hasColumn('schools', 'enrolled_by_id')) {
                $table->unsignedBigInteger('enrolled_by_id')->nullable()->index();
            }
        });

        $this->backfill();
    }

    private function backfill(): void
    {
        $now = now();

        // 1) The existing "Sales Representative" admin role may see its own performance,
        //    and its members become representatives.
        $roleId = Schema::hasTable('admin_roles')
            ? DB::table('admin_roles')->where('slug', 'sales-representative')->value('id')
            : null;

        if ($roleId) {
            $exists = DB::table('admin_role_permissions')
                ->where('admin_role_id', $roleId)->where('permission', 'sales_team.view_own')->exists();

            if (!$exists) {
                DB::table('admin_role_permissions')->insert([
                    'admin_role_id' => $roleId,
                    'permission' => 'sales_team.view_own',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $n = 0;
            foreach (DB::table('users')->where('admin_role_id', $roleId)->where('user_role', 'admin')->orderBy('id')->pluck('id') as $userId) {
                DB::table('sales_rep_profiles')->insert([
                    'user_id' => $userId,
                    'rep_code' => sprintf('REP-%04d', ++$n),
                    'status' => 'active',
                    'joined_on' => $now->toDateString(),
                    'notes' => 'Created automatically from the Sales Representative admin role.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 2) Who enrolled each existing school — from the audit trail.
        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')
                ->where('entity_type', 'School')
                ->where('event', 'created')
                ->where('actor_type', 'admin')
                ->whereNotNull('actor_id')
                ->orderBy('id')
                ->select('id', 'entity_id', 'actor_id')
                ->chunkById(500, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('schools')
                            ->where('id', (int) $row->entity_id)
                            ->whereNull('enrolled_by_id')
                            ->update(['enrolled_by_id' => (int) $row->actor_id]);
                    }
                });
        }

        // 3) Fallback: added_by, only when it is an admin account.
        DB::table('schools')
            ->whereNull('enrolled_by_id')
            ->whereNotNull('added_by')
            ->whereIn('added_by', DB::table('users')->where('user_role', 'admin')->select('id'))
            ->update(['enrolled_by_id' => DB::raw('added_by')]);

        // 4) Schools enrolled by an active representative belong to that representative.
        $repIds = DB::table('sales_rep_profiles')->where('status', 'active')->pluck('user_id')->all();

        if ($repIds) {
            DB::table('schools')
                ->whereNull('sales_rep_id')
                ->whereIn('enrolled_by_id', $repIds)
                ->orderBy('id')
                ->select('id', 'enrolled_by_id', 'created_at')
                ->chunkById(500, function ($schools) use ($now) {
                    foreach ($schools as $s) {
                        DB::table('schools')->where('id', $s->id)->update(['sales_rep_id' => $s->enrolled_by_id]);

                        DB::table('school_rep_assignments')->insert([
                            'school_id' => $s->id,
                            'user_id' => $s->enrolled_by_id,
                            'started_at' => $s->created_at ?: $now,
                            'type' => 'backfill',
                            'note' => 'Existing school linked to its enrolling representative.',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            foreach (['sales_rep_id', 'enrolled_by_id'] as $col) {
                if (Schema::hasColumn('schools', $col)) {
                    $table->dropIndex([$col]);
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasTable('admin_roles')) {
            DB::table('admin_role_permissions')->where('permission', 'like', 'sales_team.%')->delete();
        }

        Schema::dropIfExists('school_rep_assignments');
        Schema::dropIfExists('sales_transfer_batches');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('sales_rep_profiles');
    }
};

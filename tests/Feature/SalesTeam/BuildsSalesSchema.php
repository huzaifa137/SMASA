<?php

namespace Tests\Feature\SalesTeam;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A minimal in-memory schema with just the tables the Sales Team module reads,
 * so these tests run on SQLite without the project's full migration history.
 * The module's OWN migration is run for real (including its backfill).
 */
trait BuildsSalesSchema
{
    protected function buildBaseSchema(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('users', function ($t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('firstname')->nullable();
            $t->string('lastname')->nullable();
            $t->string('email')->nullable();
            $t->string('username')->nullable();
            $t->string('phonenumber')->nullable();
            $t->string('user_role')->default('admin');
            $t->boolean('is_active')->default(true);
            $t->boolean('is_super_admin')->default(false);
            $t->unsignedBigInteger('admin_role_id')->nullable();
            $t->timestamps();
        });

        Schema::create('admin_roles', function ($t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('admin_role_permissions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('admin_role_id');
            $t->string('permission');
            $t->timestamps();
        });

        Schema::create('schools', function ($t) {
            $t->id();
            $t->string('name');
            $t->string('registration_code')->nullable();
            $t->string('school_type')->nullable();
            $t->integer('school_status')->default(10);
            $t->integer('added_by')->nullable();
            $t->timestamps();
        });

        Schema::create('students', function ($t) {
            $t->id();
            $t->unsignedBigInteger('school_id');
            $t->unsignedBigInteger('linked_student_id')->nullable();
            $t->timestamps();
        });

        Schema::create('master_data', function ($t) {
            $t->unsignedBigInteger('md_id')->primary();
            $t->string('md_name');
        });

        Schema::create('academic_years', function ($t) {
            $t->id();
            $t->string('name');
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->boolean('is_active')->default(false);
            $t->timestamps();
        });

        Schema::create('academic_terms', function ($t) {
            $t->id();
            $t->unsignedBigInteger('academic_year_id');
            $t->unsignedTinyInteger('term');
            $t->date('start_date');
            $t->date('end_date');
            $t->boolean('is_active')->default(false);
            $t->timestamps();
        });

        Schema::create('billing_settings', function ($t) {
            $t->id();
            $t->string('currency')->default('UGX');
            $t->timestamps();
        });

        Schema::create('billing_terms', function ($t) {
            $t->id();
            $t->unsignedBigInteger('academic_year_id')->nullable();
            $t->string('year_label');
            $t->unsignedTinyInteger('term');
            $t->boolean('is_current')->default(false);
            $t->timestamps();
        });

        Schema::create('billing_invoices', function ($t) {
            $t->id();
            $t->string('invoice_number')->nullable();
            $t->unsignedBigInteger('billing_term_id');
            $t->unsignedBigInteger('school_id');
            $t->decimal('total', 14, 2)->default(0);
            $t->decimal('amount_paid', 14, 2)->default(0);
            $t->decimal('balance', 14, 2)->default(0);
            $t->string('status')->default('issued');
            $t->date('issue_date')->nullable();
            $t->date('due_date')->nullable();
            $t->timestamps();
        });

        Schema::create('billing_payments', function ($t) {
            $t->id();
            $t->unsignedBigInteger('invoice_id')->nullable();
            $t->unsignedBigInteger('billing_term_id')->nullable();
            $t->unsignedBigInteger('school_id');
            $t->decimal('amount', 14, 2);
            $t->string('status')->default('valid');
            $t->date('paid_on');
            $t->timestamps();
        });

        Schema::create('audit_logs', function ($t) {
            $t->id();
            $t->string('event')->nullable();
            $t->string('entity_type')->nullable();
            $t->string('entity_id')->nullable();
            $t->string('actor_type')->nullable();
            $t->unsignedBigInteger('actor_id')->nullable();
            $t->dateTime('occurred_at')->nullable();
        });
    }

    protected function runSalesMigration(): void
    {
        $migration = require base_path('database/migrations/2026_10_28_090000_create_sales_team_module.php');
        $migration->up();
    }

    // ── Fixtures ────────────────────────────────────────────────────────

    protected function makeAdmin(string $name, array $extra = []): int
    {
        return DB::table('users')->insertGetId($extra + [
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . '@example.test',
            'username' => strtolower(str_replace(' ', '', $name)),
            'user_role' => 'admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function makeRep(int $userId, array $extra = []): int
    {
        return DB::table('sales_rep_profiles')->insertGetId($extra + [
            'user_id' => $userId,
            'rep_code' => sprintf('REP-%04d', $userId),
            'status' => 'active',
            'joined_on' => '2026-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function makeSchool(string $name, ?int $owner = null, ?int $enrolledBy = null, array $extra = []): int
    {
        $id = DB::table('schools')->insertGetId($extra + [
            'name' => $name,
            'registration_code' => 'SCH-' . strtoupper(substr(md5($name), 0, 6)),
            'school_status' => 10,
            'sales_rep_id' => $owner,
            'enrolled_by_id' => $enrolledBy ?? $owner,
            'created_at' => $extra['created_at'] ?? now()->subMonths(2),
            'updated_at' => now(),
        ]);

        if ($owner) {
            DB::table('school_rep_assignments')->insert([
                'school_id' => $id,
                'user_id' => $owner,
                'started_at' => $extra['created_at'] ?? now()->subMonths(2),
                'type' => 'enrolled',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $id;
    }

    protected function makeInvoice(int $schoolId, float $total, float $paid, string $status = 'issued', ?string $due = null, int $termId = 1, ?string $issued = null): int
    {
        return DB::table('billing_invoices')->insertGetId([
            'invoice_number' => 'INV-' . uniqid(),
            'billing_term_id' => $termId,
            'school_id' => $schoolId,
            'total' => $total,
            'amount_paid' => $paid,
            'balance' => $total - $paid,
            'status' => $status,
            'issue_date' => $issued ?? now()->subDays(30)->toDateString(),
            'due_date' => $due ?? now()->addDays(10)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function makePayment(int $schoolId, float $amount, string $paidOn, string $status = 'valid', ?int $invoiceId = null): int
    {
        return DB::table('billing_payments')->insertGetId([
            'invoice_id' => $invoiceId,
            'school_id' => $schoolId,
            'billing_term_id' => 1,
            'amount' => $amount,
            'status' => $status,
            'paid_on' => $paidOn,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The admin "User Profile" screen (and legacy UserController code) read and
 * write these columns on `users`, but no earlier migration creates them.
 * Add whichever are missing (safe to run where they already exist), then
 * backfill first/last name for admin accounts from the existing `name`
 * column, which is what the top navbar shows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'firstname')) {
                $table->string('firstname', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'lastname')) {
                $table->string('lastname', 100)->nullable()->after('firstname');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'phonenumber')) {
                $table->string('phonenumber', 30)->nullable();
            }
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable();
            }
        });

        // Backfill: "BUKENYA HUZAIFA" -> firstname "BUKENYA", lastname "HUZAIFA".
        // Only fills blanks; never overwrites anything already entered.
        DB::table('users')
            ->where('user_role', 'admin')
            ->where(function ($q) {
                $q->whereNull('firstname')->orWhere('firstname', '');
            })
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    $parts = preg_split('/\s+/', trim((string) $user->name), 2);

                    DB::table('users')->where('id', $user->id)->update([
                        'firstname' => $parts[0] ?? null,
                        'lastname' => (isset($user->lastname) && $user->lastname !== '') ? $user->lastname : ($parts[1] ?? null),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Intentionally left empty: these columns may pre-exist on some
        // databases (legacy schema), so dropping them could destroy data.
    }
};

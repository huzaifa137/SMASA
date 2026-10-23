<?php

namespace App\Console\Commands;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * Creates (or promotes) a Super Administrator — the account that bootstraps the
 * Admin Rights system. Works from the terminal only, so there is no
 * "first visitor becomes admin" web hole.
 *
 *   php artisan admin:create-super-admin
 *   php artisan admin:create-super-admin --username=root --email=root@example.com --name="Root Admin"
 *   php artisan admin:create-super-admin --email=existing@example.com      (promotes an existing admin)
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'admin:create-super-admin
                            {--name= : Full name}
                            {--username= : Login username}
                            {--email= : Login e-mail}
                            {--password= : Password (omit to be prompted; avoid on shared shells)}';

    protected $description = 'Create a Super Administrator, or promote an existing admin account to one';

    public function handle(): int
    {
        if (!Schema::hasColumn('users', 'is_super_admin')) {
            $this->error('Run "php artisan migrate" first (users.is_super_admin is missing).');

            return self::FAILURE;
        }

        $email = $this->option('email') ?: $this->ask('E-mail');
        $existing = User::where('email', $email)->first();

        if ($existing) {
            if ($existing->is_super_admin) {
                $this->info("{$existing->email} is already a Super Administrator.");

                return self::SUCCESS;
            }

            if (!$this->confirm("An account with {$email} already exists. Promote it to Super Administrator?", true)) {
                return self::SUCCESS;
            }

            $existing->forceFill([
                'user_role' => 'admin',
                'is_active' => 1,
                'is_super_admin' => true,
                'admin_role_id' => null,
            ])->save();

            AdminAuditLog::record('super_admin.promoted', "Account {$existing->email} promoted to Super Administrator from the console.", 'user', $existing->id);
            $this->info("{$existing->email} is now a Super Administrator.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: $this->ask('Full name');
        $username = $this->option('username') ?: $this->ask('Username');
        $password = $this->option('password') ?: $this->secret('Password (min 8 characters)');

        $validator = Validator::make(
            compact('name', 'username', 'email', 'password'),
            [
                'name' => 'required|string|max:150',
                'username' => 'required|string|max:100|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $attributes = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'user_role' => 'admin',
            'is_active' => 1,
        ];

        // The production users table also carries firstname/lastname.
        if (Schema::hasColumn('users', 'firstname')) {
            $parts = preg_split('/\s+/', trim($name), 2);
            $attributes['firstname'] = $parts[0];
            $attributes['lastname'] = $parts[1] ?? '';
        }

        $user = new User();
        $user->forceFill($attributes + ['is_super_admin' => true]);
        $user->save();

        AdminAuditLog::record('super_admin.created', "Super Administrator {$email} created from the console.", 'user', $user->id);

        $this->info("Super Administrator created: {$email}");
        $this->line('Sign in, then open Admin Rights to create roles and admin users.');

        return self::SUCCESS;
    }
}

<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Support\AdminAccess;
use Illuminate\Database\Seeder;

/**
 * OPTIONAL starter roles for the Admin Portal. Safe to re-run: existing roles
 * are left untouched (so edits made in the UI are never overwritten).
 *
 *   php artisan db:seed --class=AdminRolesSeeder
 */
class AdminRolesSeeder extends Seeder
{
    public function run(): void
    {
        $all = AdminAccess::allKeys();
        $viewOnly = array_values(array_filter($all, fn ($k) => !str_starts_with($k, 'admin_rights.') && preg_match('/\.(view|view_data|view_codes)$/', $k)));

        $roles = [
            'Schools Manager' => [
                'Register and maintain schools, academic calendars and school staff roles.',
                array_merge(
                    AdminAccess::moduleKeys('schools'),
                    AdminAccess::moduleKeys('academic_calendar'),
                    AdminAccess::moduleKeys('school_rights')
                ),
            ],
            'Curriculum Manager' => [
                'Maintain master data, subject lists and the NLSC curriculum.',
                array_merge(
                    AdminAccess::moduleKeys('master_data'),
                    AdminAccess::moduleKeys('secondary_subjects'),
                    AdminAccess::moduleKeys('nlsc_curriculum'),
                    AdminAccess::moduleKeys('custom_report_cards')
                ),
            ],
            'Support (Read-only)' => [
                'Can look at schools, curriculum and notifications but change nothing.',
                $viewOnly,
            ],
            'Rights Administrator' => [
                'Manages admin roles and users, but cannot grant more than they hold themselves.',
                AdminAccess::moduleKeys('admin_rights'),
            ],
        ];

        foreach ($roles as $name => [$description, $keys]) {
            if (AdminRole::where('name', $name)->exists()) {
                continue;
            }

            $role = AdminRole::create([
                'name' => $name,
                'slug' => AdminRole::uniqueSlug($name),
                'description' => $description,
                'is_active' => true,
            ]);

            $role->syncPermissions(array_values(array_filter($keys, [AdminAccess::class, 'isValidKey'])));
        }
    }
}

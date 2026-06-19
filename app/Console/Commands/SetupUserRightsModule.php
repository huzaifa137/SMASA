<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SetupUserRightsModule extends Command
{
    // php artisan userrights:setup
    protected $signature = 'userrights:setup';
    protected $description = 'Setup complete User Rights and Privilege module with migrations, models, and seeders';

    public function handle()
    {
        $this->info('Setting up User Rights and Privilege Module...');
        $this->newLine();

        // Create Models directory if not exists
        $modelsPath = app_path('Models');
        if (!File::exists($modelsPath)) {
            File::makeDirectory($modelsPath, 0755, true);
        }

        // Model files content
        $models = [
            'SchoolRole.php' => $this->getSchoolRoleModel(),
            'SystemModule.php' => $this->getSystemModuleModel(),
            'ModuleFeature.php' => $this->getModuleFeatureModel(),
            'RoleModuleAccess.php' => $this->getRoleModuleAccessModel(),
            'RoleFeatureAccess.php' => $this->getRoleFeatureAccessModel(),
            'TeacherSchoolRole.php' => $this->getTeacherSchoolRoleModel(),
        ];

        // Create Models
        $this->info('Creating Models...');
        foreach ($models as $filename => $content) {
            $path = app_path("Models/{$filename}");
            if (!File::exists($path)) {
                File::put($path, $content);
                $this->line("Created: {$filename}");
            } else {
                $this->line("Skipped (exists): {$filename}");
            }
        }

        $this->newLine();

        // Create Migrations
        $this->info('Creating Migrations...');

        $migrations = [
            '2024_01_02_000001_create_school_roles_table.php' => $this->getSchoolRolesMigration(),
            '2024_01_02_000002_create_system_modules_table.php' => $this->getSystemModulesMigration(),
            '2024_01_02_000003_create_module_features_table.php' => $this->getModuleFeaturesMigration(),
            '2024_01_02_000004_create_role_module_access_table.php' => $this->getRoleModuleAccessMigration(),
            '2024_01_02_000005_create_role_feature_access_table.php' => $this->getRoleFeatureAccessMigration(),
            '2024_01_02_000006_create_teacher_school_roles_table.php' => $this->getTeacherSchoolRolesMigration(),
        ];

        $migrationsPath = database_path('migrations');

        foreach ($migrations as $filename => $content) {
            $path = $migrationsPath . '/' . $filename;
            if (!File::exists($path)) {
                File::put($path, $content);
                $this->line("Created: {$filename}");
            } else {
                $this->line("Skipped (exists): {$filename}");
            }
        }

        $this->newLine();

        // Create seeders directory if it doesn't exist
        $seedersPath = database_path('seeders');
        if (!File::exists($seedersPath)) {
            File::makeDirectory($seedersPath, 0755, true);
            $this->line("Created seeders directory");
        }

        // Create Seeder
        $this->info('Creating System Modules Seeder...');
        $seederPath = database_path('seeders/SystemModulesSeeder.php');
        if (!File::exists($seederPath)) {
            File::put($seederPath, $this->getSystemModulesSeeder());
            $this->line("Created: SystemModulesSeeder.php");
        } else {
            $this->line("Skipped (exists): SystemModulesSeeder.php");
        }

        $this->newLine();

        // Run migrations
        if ($this->confirm('Run migrations now?')) {
            $this->info('Running migrations...');
            Artisan::call('migrate');
            $this->line(Artisan::output());
        }

        $this->newLine();

        // Run seeder
        if ($this->confirm('Run SystemModulesSeeder now?')) {
            $this->info('Running SystemModulesSeeder...');
            Artisan::call('db:seed', ['--class' => 'SystemModulesSeeder']);
            $this->line(Artisan::output());
        }

        $this->newLine();
        $this->info('User Rights and Privilege module setup completed successfully!');

        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Create middleware for checking permissions');
        $this->line('  2. Create controllers for role management');
        $this->line('  3. Add routes for role and permission management');
        $this->line('  4. Create views for assigning roles to teachers');
        $this->line('  5. Implement permission checks in blade templates');
    }

    // Model content methods
    private function getSchoolRoleModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolRole extends Model
{
    protected $table = "school_roles";
    
    protected $fillable = ["school_id", "name", "description", "is_active"];

    protected $casts = [
        "is_active" => "boolean"
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function moduleAccess()
    {
        return $this->hasMany(RoleModuleAccess::class, "school_role_id");
    }

    public function featureAccess()
    {
        return $this->hasMany(RoleFeatureAccess::class, "school_role_id");
    }

    public function teachers()
    {
        return $this->hasMany(TeacherSchoolRole::class, "school_role_id");
    }

    /**
     * Check if this role has access to a module
     */
    public function canAccessModule($moduleKey): bool
    {
        return $this->moduleAccess()
            ->whereHas("module", fn($q) => $q->where("key", $moduleKey))
            ->where("can_access", true)
            ->exists();
    }

    /**
     * Check if this role has access to a specific feature
     */
    public function canAccessFeature($featureKey): bool
    {
        return $this->featureAccess()
            ->whereHas("feature", fn($q) => $q->where("key", $featureKey))
            ->where("can_access", true)
            ->exists();
    }
    
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
    
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where("school_id", $schoolId);
    }
}
';
    }

    private function getSystemModuleModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    protected $table = "system_modules";
    
    protected $fillable = ["key", "name", "icon", "route", "description", "sort_order", "is_active"];

    protected $casts = [
        "is_active" => "boolean"
    ];

    public function features()
    {
        return $this->hasMany(ModuleFeature::class, "module_id")->orderBy("sort_order");
    }

    public function roleAccess()
    {
        return $this->hasMany(RoleModuleAccess::class, "module_id");
    }
    
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
}
';
    }

    private function getModuleFeatureModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleFeature extends Model
{
    protected $table = "module_features";
    
    protected $fillable = ["module_id", "key", "name", "description", "sort_order"];

    public function module()
    {
        return $this->belongsTo(SystemModule::class, "module_id");
    }

    public function roleAccess()
    {
        return $this->hasMany(RoleFeatureAccess::class, "feature_id");
    }
}
';
    }

    private function getRoleModuleAccessModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleModuleAccess extends Model
{
    protected $table = "role_module_access";
    
    protected $fillable = ["school_role_id", "module_id", "can_access"];

    protected $casts = [
        "can_access" => "boolean"
    ];

    public function schoolRole()
    {
        return $this->belongsTo(SchoolRole::class, "school_role_id");
    }

    public function module()
    {
        return $this->belongsTo(SystemModule::class, "module_id");
    }
}
';
    }

    private function getRoleFeatureAccessModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleFeatureAccess extends Model
{
    protected $table = "role_feature_access";
    
    protected $fillable = ["school_role_id", "feature_id", "can_access"];

    protected $casts = [
        "can_access" => "boolean"
    ];

    public function schoolRole()
    {
        return $this->belongsTo(SchoolRole::class, "school_role_id");
    }

    public function feature()
    {
        return $this->belongsTo(ModuleFeature::class, "feature_id");
    }
}
';
    }

    private function getTeacherSchoolRoleModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSchoolRole extends Model
{
    protected $table = "teacher_school_roles";
    
    protected $fillable = ["teacher_id", "school_id", "school_role_id"];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolRole()
    {
        return $this->belongsTo(SchoolRole::class, "school_role_id");
    }
}
';
    }

    // Migration content methods
    private function getSchoolRolesMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("school_roles", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("name");
            $table->string("description")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();

            $table->foreign("school_id")->references("id")->on("schools")->onDelete("cascade");
            $table->unique(["school_id", "name"]);
            $table->index(["school_id", "is_active"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("school_roles");
    }
};
';
    }

    private function getSystemModulesMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("system_modules", function (Blueprint $table) {
            $table->id();
            $table->string("key")->unique();
            $table->string("name");
            $table->string("icon")->default("fa fa-circle");
            $table->string("route")->nullable();
            $table->text("description")->nullable();
            $table->integer("sort_order")->default(0);
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("system_modules");
    }
};
';
    }

    private function getModuleFeaturesMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("module_features", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("module_id");
            $table->string("key");
            $table->string("name");
            $table->text("description")->nullable();
            $table->integer("sort_order")->default(0);
            $table->timestamps();

            $table->foreign("module_id")->references("id")->on("system_modules")->onDelete("cascade");
            $table->unique(["module_id", "key"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("module_features");
    }
};
';
    }

    private function getRoleModuleAccessMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("role_module_access", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_role_id");
            $table->unsignedBigInteger("module_id");
            $table->boolean("can_access")->default(true);
            $table->timestamps();

            $table->foreign("school_role_id")->references("id")->on("school_roles")->onDelete("cascade");
            $table->foreign("module_id")->references("id")->on("system_modules")->onDelete("cascade");
            $table->unique(["school_role_id", "module_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("role_module_access");
    }
};
';
    }

    private function getRoleFeatureAccessMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("role_feature_access", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_role_id");
            $table->unsignedBigInteger("feature_id");
            $table->boolean("can_access")->default(true);
            $table->timestamps();

            $table->foreign("school_role_id")->references("id")->on("school_roles")->onDelete("cascade");
            $table->foreign("feature_id")->references("id")->on("module_features")->onDelete("cascade");
            $table->unique(["school_role_id", "feature_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("role_feature_access");
    }
};
';
    }

    private function getTeacherSchoolRolesMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("teacher_school_roles", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("teacher_id");
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("school_role_id");
            $table->timestamps();

            $table->foreign("teacher_id")->references("id")->on("teachers")->onDelete("cascade");
            $table->foreign("school_id")->references("id")->on("schools")->onDelete("cascade");
            $table->foreign("school_role_id")->references("id")->on("school_roles")->onDelete("cascade");
            $table->unique(["teacher_id", "school_id"]);
            $table->index(["school_id", "school_role_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("teacher_school_roles");
    }
};
';
    }

    // Seeder content
    private function getSystemModulesSeeder()
    {
        return '<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemModule;
use App\Models\ModuleFeature;

class SystemModulesSeeder extends Seeder
{
    public function run(): void
    {
        // Define all system modules with their features
        $modules = [
            [
                "key" => "dashboard",
                "name" => "Dashboard",
                "icon" => "fa fa-tachometer-alt",
                "route" => "dashboard",
                "description" => "Main dashboard and overview",
                "sort_order" => 1,
                "features" => [
                    ["key" => "view_dashboard", "name" => "View Dashboard", "sort_order" => 1],
                    ["key" => "view_statistics", "name" => "View Statistics", "sort_order" => 2],
                ]
            ],
            [
                "key" => "students",
                "name" => "Students",
                "icon" => "fa fa-users",
                "route" => "students.index",
                "description" => "Student management",
                "sort_order" => 2,
                "features" => [
                    ["key" => "view_students", "name" => "View Students", "sort_order" => 1],
                    ["key" => "add_student", "name" => "Add Student", "sort_order" => 2],
                    ["key" => "edit_student", "name" => "Edit Student", "sort_order" => 3],
                    ["key" => "delete_student", "name" => "Delete Student", "sort_order" => 4],
                    ["key" => "import_students", "name" => "Import Students", "sort_order" => 5],
                    ["key" => "export_students", "name" => "Export Students", "sort_order" => 6],
                    ["key" => "view_student_details", "name" => "View Student Details", "sort_order" => 7],
                    ["key" => "manage_parents", "name" => "Manage Parents", "sort_order" => 8],
                ]
            ],
            [
                "key" => "teachers",
                "name" => "Teachers",
                "icon" => "fa fa-chalkboard-user",
                "route" => "teachers.index",
                "description" => "Teacher management",
                "sort_order" => 3,
                "features" => [
                    ["key" => "view_teachers", "name" => "View Teachers", "sort_order" => 1],
                    ["key" => "add_teacher", "name" => "Add Teacher", "sort_order" => 2],
                    ["key" => "edit_teacher", "name" => "Edit Teacher", "sort_order" => 3],
                    ["key" => "delete_teacher", "name" => "Delete Teacher", "sort_order" => 4],
                    ["key" => "assign_subjects", "name" => "Assign Subjects", "sort_order" => 5],
                    ["key" => "view_teacher_schedule", "name" => "View Schedule", "sort_order" => 6],
                ]
            ],
            [
                "key" => "classes",
                "name" => "Classes",
                "icon" => "fa fa-graduation-cap",
                "route" => "classes.index",
                "description" => "Class and section management",
                "sort_order" => 4,
                "features" => [
                    ["key" => "view_classes", "name" => "View Classes", "sort_order" => 1],
                    ["key" => "add_class", "name" => "Add Class", "sort_order" => 2],
                    ["key" => "edit_class", "name" => "Edit Class", "sort_order" => 3],
                    ["key" => "delete_class", "name" => "Delete Class", "sort_order" => 4],
                    ["key" => "manage_sections", "name" => "Manage Sections", "sort_order" => 5],
                    ["key" => "assign_class_teacher", "name" => "Assign Class Teacher", "sort_order" => 6],
                ]
            ],
            [
                "key" => "subjects",
                "name" => "Subjects",
                "icon" => "fa fa-book",
                "route" => "subjects.index",
                "description" => "Subject management",
                "sort_order" => 5,
                "features" => [
                    ["key" => "view_subjects", "name" => "View Subjects", "sort_order" => 1],
                    ["key" => "add_subject", "name" => "Add Subject", "sort_order" => 2],
                    ["key" => "edit_subject", "name" => "Edit Subject", "sort_order" => 3],
                    ["key" => "delete_subject", "name" => "Delete Subject", "sort_order" => 4],
                    ["key" => "assign_teachers", "name" => "Assign Teachers", "sort_order" => 5],
                ]
            ],
            [
                "key" => "examinations",
                "name" => "Examinations",
                "icon" => "fa fa-file-alt",
                "route" => "exams.index",
                "description" => "Exam management",
                "sort_order" => 6,
                "features" => [
                    ["key" => "view_exams", "name" => "View Exams", "sort_order" => 1],
                    ["key" => "create_exam", "name" => "Create Exam", "sort_order" => 2],
                    ["key" => "edit_exam", "name" => "Edit Exam", "sort_order" => 3],
                    ["key" => "delete_exam", "name" => "Delete Exam", "sort_order" => 4],
                    ["key" => "manage_grades", "name" => "Manage Grades", "sort_order" => 5],
                    ["key" => "publish_results", "name" => "Publish Results", "sort_order" => 6],
                    ["key" => "generate_reports", "name" => "Generate Reports", "sort_order" => 7],
                ]
            ],
            [
                "key" => "attendance",
                "name" => "Attendance",
                "icon" => "fa fa-calendar-check",
                "route" => "attendance.index",
                "description" => "Student and staff attendance",
                "sort_order" => 7,
                "features" => [
                    ["key" => "view_attendance", "name" => "View Attendance", "sort_order" => 1],
                    ["key" => "mark_attendance", "name" => "Mark Attendance", "sort_order" => 2],
                    ["key" => "edit_attendance", "name" => "Edit Attendance", "sort_order" => 3],
                    ["key" => "attendance_reports", "name" => "Attendance Reports", "sort_order" => 4],
                ]
            ],
            [
                "key" => "finance",
                "name" => "Finance",
                "icon" => "fa fa-chart-line",
                "route" => "finance.index",
                "description" => "Financial management",
                "sort_order" => 8,
                "features" => [
                    ["key" => "view_finance", "name" => "View Finance", "sort_order" => 1],
                    ["key" => "create_invoice", "name" => "Create Invoice", "sort_order" => 2],
                    ["key" => "record_payment", "name" => "Record Payment", "sort_order" => 3],
                    ["key" => "manage_expenses", "name" => "Manage Expenses", "sort_order" => 4],
                    ["key" => "financial_reports", "name" => "Financial Reports", "sort_order" => 5],
                    ["key" => "manage_fees", "name" => "Manage Fees", "sort_order" => 6],
                ]
            ],
            [
                "key" => "library",
                "name" => "Library",
                "icon" => "fa fa-book-open",
                "route" => "library.index",
                "description" => "Library management",
                "sort_order" => 9,
                "features" => [
                    ["key" => "view_dashboard", "name" => "View Dashboard", "sort_order" => 0],
                    ["key" => "view_books", "name" => "View Books", "sort_order" => 1],
                    ["key" => "add_book", "name" => "Add Book", "sort_order" => 2],
                    ["key" => "edit_book", "name" => "Edit Book", "sort_order" => 3],
                    ["key" => "delete_book", "name" => "Delete Book", "sort_order" => 4],
                    ["key" => "manage_borrowing", "name" => "Manage Borrowing", "sort_order" => 5],
                    ["key" => "manage_members", "name" => "Manage Members", "sort_order" => 6],
                    ["key" => "library_reports", "name" => "Library Reports", "sort_order" => 7],
                    ["key" => "manage_settings", "name" => "Manage Settings", "sort_order" => 8],
                ]
            ],
            [
                "key" => "school_management",
                "name" => "School Management",
                "icon" => "fa fa-school",
                "route" => "school.allSchools",
                "description" => "Manage schools, academic years, and terms",
                "sort_order" => 1,
                "features" => [
                    ["key" => "view_schools", "name" => "View Schools", "sort_order" => 1],
                    ["key" => "create_school", "name" => "Create School", "sort_order" => 2],
                    ["key" => "edit_school", "name" => "Edit School", "sort_order" => 3],
                    ["key" => "delete_school", "name" => "Delete School", "sort_order" => 4],
                    ["key" => "manage_academic_years", "name" => "Manage Academic Years", "sort_order" => 5],
                    ["key" => "manage_term_dates", "name" => "Manage Term Dates", "sort_order" => 6],
                ]
            ],
            [
                "key" => "user_rights",
                "name" => "User Rights & Permissions",
                "icon" => "fa fa-user-shield",
                "route" => "urp.dashboard",
                "description" => "Manage user roles and permissions",
                "sort_order" => 2,
                "features" => [
                    ["key" => "view_roles", "name" => "View Roles", "sort_order" => 1],
                    ["key" => "create_role", "name" => "Create Role", "sort_order" => 2],
                    ["key" => "edit_role", "name" => "Edit Role", "sort_order" => 3],
                    ["key" => "delete_role", "name" => "Delete Role", "sort_order" => 4],
                    ["key" => "assign_permissions", "name" => "Assign Permissions", "sort_order" => 5],
                    ["key" => "assign_roles_to_users", "name" => "Assign Roles to Users", "sort_order" => 6],
                ]
            ],
            [
                "key" => "master_data",
                "name" => "Master Data",
                "icon" => "fa fa-database",
                "route" => "master-code-to-data",
                "description" => "Manage system master data and codes",
                "sort_order" => 3,
                "features" => [
                    ["key" => "view_master_data", "name" => "View Master Data", "sort_order" => 1],
                    ["key" => "create_master_data", "name" => "Create Master Data", "sort_order" => 2],
                    ["key" => "edit_master_data", "name" => "Edit Master Data", "sort_order" => 3],
                    ["key" => "delete_master_data", "name" => "Delete Master Data", "sort_order" => 4],
                ]
            ],
            [
                "key" => "timetable",
                "name" => "Timetable",
                "icon" => "fa fa-clock",
                "route" => "timetable.dashboard",
                "description" => "Manage class timetables and schedules",
                "sort_order" => 11,
                "features" => [
                    ["key" => "view_timetable", "name" => "View Timetable", "sort_order" => 1],
                    ["key" => "create_timetable", "name" => "Create Timetable", "sort_order" => 2],
                    ["key" => "edit_timetable", "name" => "Edit Timetable", "sort_order" => 3],
                    ["key" => "delete_timetable", "name" => "Delete Timetable", "sort_order" => 4],
                    ["key" => "manage_periods", "name" => "Manage Periods", "sort_order" => 5],
                    ["key" => "assign_teachers", "name" => "Assign Teachers to Slots", "sort_order" => 6],
                ]
            ],
            [
                "key" => "notifications",
                "name" => "Notifications",
                "icon" => "fa fa-bell",
                "route" => "notifications.index",
                "description" => "Manage system notifications",
                "sort_order" => 101,
                "features" => [
                    ["key" => "view_notifications", "name" => "View Notifications", "sort_order" => 1],
                    ["key" => "create_notification", "name" => "Create Notification", "sort_order" => 2],
                    ["key" => "edit_notification", "name" => "Edit Notification", "sort_order" => 3],
                    ["key" => "delete_notification", "name" => "Delete Notification", "sort_order" => 4],
                    ["key" => "broadcast_notifications", "name" => "Broadcast Notifications", "sort_order" => 5],
                ]
            ],
            [
            "key" => "card_scan",
            "name" => "Card Scan",
            "icon" => "fa-qrcode",
            "route" => "card-scan.hub",
            "description" => "Card scanning and verification system",
            "sort_order" => 12,
            "features" => [
                ["key" => "view_hub", "name" => "View Scan Hub", "sort_order" => 1],
                ["key" => "scan_cards", "name" => "Scan Cards", "sort_order" => 2],
                ["key" => "manage_arrival_attendance", "name" => "Manage Arrival Attendance", "sort_order" => 3],
                ["key" => "view_scan_logs", "name" => "View Scan Logs", "sort_order" => 4],
                ["key" => "view_arrival_reports", "name" => "View Arrival Reports", "sort_order" => 5],
            ]
        ],
        [
            "key" => "student_id_cards",
            "name" => "Student ID Cards",
            "icon" => "fa-id-card",
            "route" => "id-cards.index",
            "description" => "Student ID card management system",
            "sort_order" => 13,
            "features" => [
                ["key" => "view_cards", "name" => "View ID Cards", "sort_order" => 1],
                ["key" => "generate_cards", "name" => "Generate ID Cards", "sort_order" => 2],
                ["key" => "print_cards", "name" => "Print ID Cards", "sort_order" => 3],
                ["key" => "revoke_cards", "name" => "Revoke ID Cards", "sort_order" => 4],
                ["key" => "verify_cards", "name" => "Verify ID Cards", "sort_order" => 5],
                ["key" => "reactivate_cards", "name" => "Reactivate ID Cards", "sort_order" => 6],
            ]
        ],
        [
            "key" => "teacher_id_cards",
            "name" => "Teacher ID Cards",
            "icon" => "fa-id-card",
            "route" => "teacher-id-cards.index",
            "description" => "Teacher ID card management system",
            "sort_order" => 14,
            "features" => [
                ["key" => "view_teacher_cards", "name" => "View Teacher ID Cards", "sort_order" => 1],
                ["key" => "generate_teacher_cards", "name" => "Generate Teacher ID Cards", "sort_order" => 2],
                ["key" => "print_teacher_cards", "name" => "Print Teacher ID Cards", "sort_order" => 3],
                ["key" => "revoke_teacher_cards", "name" => "Revoke Teacher ID Cards", "sort_order" => 4],
                ["key" => "verify_teacher_cards", "name" => "Verify Teacher ID Cards", "sort_order" => 5],
                ["key" => "reactivate_teacher_cards", "name" => "Reactivate Teacher ID Cards", "sort_order" => 6],
            ]
        ],
        [
            "key" => "master_data",
            "name" => "Master Data",
            "icon" => "fa fa-database",
            "route" => "master-code-to-data",
            "description" => "Manage system master data and codes",
            "sort_order" => 3,
            "features" => [
                ["key" => "view_master_data", "name" => "View Master Data", "sort_order" => 1],
                ["key" => "create_master_data", "name" => "Create Master Data", "sort_order" => 2],
                ["key" => "edit_master_data", "name" => "Edit Master Data", "sort_order" => 3],
                ["key" => "delete_master_data", "name" => "Delete Master Data", "sort_order" => 4],
                ["key" => "view_master_codes", "name" => "View Master Codes", "sort_order" => 5],
                ["key" => "create_master_codes", "name" => "Create Master Codes", "sort_order" => 6],
                ["key" => "edit_master_codes", "name" => "Edit Master Codes", "sort_order" => 7],
                ["key" => "delete_master_codes", "name" => "Delete Master Codes", "sort_order" => 8],
            ]
        ],
        [
            "key" => "user_rights",
            "name" => "User Rights & Permissions",
            "icon" => "fa fa-user-shield",
            "route" => "urp.dashboard",
            "description" => "Manage user roles and permissions",
            "sort_order" => 2,
            "features" => [
                ["key" => "view_dashboard", "name" => "View Dashboard", "sort_order" => 1],
                ["key" => "view_roles", "name" => "View Roles", "sort_order" => 2],
                ["key" => "create_role", "name" => "Create Role", "sort_order" => 3],
                ["key" => "edit_role", "name" => "Edit Role", "sort_order" => 4],
                ["key" => "delete_role", "name" => "Delete Role", "sort_order" => 5],
                ["key" => "view_permissions", "name" => "View Permissions", "sort_order" => 6],
                ["key" => "assign_permissions", "name" => "Assign Permissions", "sort_order" => 7],
                ["key" => "assign_roles_to_users", "name" => "Assign Roles to Users", "sort_order" => 8],
                ["key" => "remove_roles_from_users", "name" => "Remove Roles from Users", "sort_order" => 9],
            ]
        ]
        ];

        foreach ($modules as $moduleData) {
            $features = $moduleData["features"] ?? [];
            unset($moduleData["features"]);
            
            $module = SystemModule::updateOrCreate(
                ["key" => $moduleData["key"]],
                $moduleData
            );
            
            foreach ($features as $featureData) {
                ModuleFeature::updateOrCreate(
                    [
                        "module_id" => $module->id,
                        "key" => $featureData["key"]
                    ],
                    [
                        "name" => $featureData["name"],
                        "sort_order" => $featureData["sort_order"]
                    ]
                );
            }
        }
        
        $this->command->info("System modules and features seeded successfully!");
    }
}
';
    }
}
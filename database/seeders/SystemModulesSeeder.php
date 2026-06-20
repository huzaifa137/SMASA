<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemModule;
use App\Models\ModuleFeature;
use Illuminate\Support\Facades\DB;

class SystemModulesSeeder extends Seeder
{
    /**
     * IMPORTANT: This list mirrors the REAL guards in the codebase:
     *   - route middleware  ->middleware(['module:xxx'])               in routes/web.php
     *   - PermissionHelper::canModule() / canFeature()                  in the sidebar
     *     (resources/views/layouts-side-bar/side-menu.blade.php)
     *   - PermissionHelper::denyUnlessFeature() calls inside every
     *     controller listed below
     *
     * Module keys MUST exactly match the strings passed to canModule() / 'module:'.
     * Feature keys MUST exactly match the strings passed to canFeature() / denyUnlessFeature().
     *
     * Feature keys are unique GLOBALLY (not just per-module) because
     * SchoolRole::canAccessFeature() looks features up by key only —
     * never reuse a feature key across two different modules.
     *
     * NOTE: "Dashboard" is intentionally NOT seeded as its own module.
     * It has no PermissionHelper guard in the sidebar (it's the safe
     * landing page every logged-in teacher sees, even with zero modules
     * assigned), so seeding it as a toggleable module would be misleading.
     *
     * KNOWN CODE-LEVEL ISSUES this seeder cannot silently fix (see chat
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        ModuleFeature::truncate();
        SystemModule::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $modules = [

            // ── Classes ─────────────────────────────────────────────
            // NOTE: TeacherController is ALSO routed under module:classes
            // in routes/web.php (web.php:203), even though `teachers` exists
            // as its own module below for the sidebar's separate teachers
            // menu item. That's existing route-design, left as-is.
            [
                "key" => "classes",
                "name" => "Classes",
                "icon" => "fa fa-chalkboard-teacher",
                "route" => "all.my-classes",
                "description" => "Class, stream and class-teacher management",
                "sort_order" => 10,
                "features" => [
                    ["key" => "view_classes", "name" => "View Classes", "sort_order" => 1],
                    ["key" => "add_class", "name" => "Add Class", "sort_order" => 2],
                    ["key" => "edit_class", "name" => "Edit Class", "sort_order" => 3],
                    ["key" => "delete_class", "name" => "Delete Class", "sort_order" => 4],
                    ["key" => "manage_streams", "name" => "Manage Streams", "sort_order" => 5],
                    ["key" => "assign_class_teacher", "name" => "Assign Class/Stream Teacher", "sort_order" => 6],
                    ["key" => "assign_subject_teachers", "name" => "Assign Subject Teachers", "sort_order" => 7],
                ],
            ],

            // ── Teachers ────────────────────────────────────────────
            [
                "key" => "teachers",
                "name" => "Teachers",
                "icon" => "fa fa-user-tie",
                "route" => "school.teachers",
                "description" => "Teacher records and accounts",
                "sort_order" => 20,
                "features" => [
                    ["key" => "view_teachers", "name" => "View Teachers", "sort_order" => 1],
                    ["key" => "add_teacher", "name" => "Add Teacher", "sort_order" => 2],
                    ["key" => "edit_teacher", "name" => "Edit Teacher", "sort_order" => 3],
                    ["key" => "delete_teacher", "name" => "Delete Teacher", "sort_order" => 4],
                    ["key" => "manage_teacher_status", "name" => "Manage Teacher Status", "sort_order" => 5],
                ],
            ],

            // ── Students ────────────────────────────────────────────
            [
                "key" => "students",
                "name" => "Students",
                "icon" => "fa fa-user-graduate",
                "route" => "students.individual.search",
                "description" => "Student records",
                "sort_order" => 30,
                "features" => [
                    ["key" => "view_students", "name" => "View Students", "sort_order" => 1],
                    ["key" => "add_student", "name" => "Add Student", "sort_order" => 2],
                    ["key" => "edit_student", "name" => "Edit Student", "sort_order" => 3],
                    ["key" => "delete_student", "name" => "Delete Student", "sort_order" => 4],
                    ["key" => "view_student_details", "name" => "View Student Details", "sort_order" => 5],
                    ["key" => "import_students", "name" => "Import Students", "sort_order" => 6],
                    ["key" => "export_students", "name" => "Export Students", "sort_order" => 7],
                ],
            ],

            // ── Card Scan ───────────────────────────────────────────
            // (Cardscancontroller — separate module key from the two
            // ID-card modules below; sidebar checks all three independently)
            [
                "key" => "card_scan",
                "name" => "Card Scan",
                "icon" => "fa fa-qrcode",
                "route" => "card-scan.hub",
                "description" => "Card scanning hub and arrival attendance logging",
                "sort_order" => 40,
                "features" => [
                    ["key" => "view_hub", "name" => "View Scan Hub", "sort_order" => 1],
                    ["key" => "scan_cards", "name" => "Scan Cards", "sort_order" => 2],
                    ["key" => "manage_arrival_attendance", "name" => "Manage Arrival Attendance", "sort_order" => 3],
                    ["key" => "view_scan_logs", "name" => "View Scan Logs", "sort_order" => 4],
                    ["key" => "view_arrival_reports", "name" => "View Arrival Reports", "sort_order" => 5],
                ],
            ],

            // ── Student ID Cards ────────────────────────────────────
            [
                "key" => "student_id_cards",
                "name" => "Student ID Cards",
                "icon" => "fas fa-id-card",
                "route" => "id-cards.index",
                "description" => "Student ID card issuance and verification",
                "sort_order" => 50,
                "features" => [
                    ["key" => "view_cards", "name" => "View ID Cards", "sort_order" => 1],
                    ["key" => "generate_cards", "name" => "Generate ID Cards", "sort_order" => 2],
                    ["key" => "print_cards", "name" => "Print ID Cards", "sort_order" => 3],
                    ["key" => "verify_cards", "name" => "Verify ID Cards", "sort_order" => 4],
                    ["key" => "revoke_cards", "name" => "Revoke ID Cards", "sort_order" => 5],
                    ["key" => "reactivate_cards", "name" => "Reactivate ID Cards", "sort_order" => 6],
                ],
            ],

            // ── Teacher ID Cards ────────────────────────────────────
            [
                "key" => "teacher_id_cards",
                "name" => "Teacher ID Cards",
                "icon" => "fas fa-id-card",
                "route" => "teacher-id-cards.index",
                "description" => "Teacher ID card issuance and verification",
                "sort_order" => 60,
                "features" => [
                    ["key" => "view_teacher_cards", "name" => "View Teacher ID Cards", "sort_order" => 1],
                    ["key" => "generate_teacher_cards", "name" => "Generate Teacher ID Cards", "sort_order" => 2],
                    ["key" => "print_teacher_cards", "name" => "Print Teacher ID Cards", "sort_order" => 3],
                    ["key" => "verify_teacher_cards", "name" => "Verify Teacher ID Cards", "sort_order" => 4],
                    ["key" => "revoke_teacher_cards", "name" => "Revoke Teacher ID Cards", "sort_order" => 5],
                    ["key" => "reactivate_teacher_cards", "name" => "Reactivate Teacher ID Cards", "sort_order" => 6],
                ],
            ],

            // ── Library ─────────────────────────────────────────────
            // See class docblock note #1 above re: view_library_dashboard.
            [
                "key" => "library",
                "name" => "Library",
                "icon" => "fas fa-landmark",
                "route" => "library.dashboard",
                "description" => "Library catalogue, borrowing and member management",
                "sort_order" => 70,
                "features" => [
                    ["key" => "view_library_dashboard", "name" => "View Library Dashboard", "sort_order" => 1], // see note #1: controller currently calls 'view_dashboard'
                    ["key" => "view_books", "name" => "View Books", "sort_order" => 2],
                    ["key" => "add_book", "name" => "Add Book", "sort_order" => 3],
                    ["key" => "edit_book", "name" => "Edit Book", "sort_order" => 4],
                    ["key" => "delete_book", "name" => "Delete Book", "sort_order" => 5],
                    ["key" => "manage_borrowing", "name" => "Manage Borrowing", "sort_order" => 6],
                    ["key" => "manage_members", "name" => "Manage Members", "sort_order" => 7],
                    ["key" => "library_reports", "name" => "Library Reports", "sort_order" => 8],
                    ["key" => "manage_settings", "name" => "Manage Library Settings", "sort_order" => 9],
                ],
            ],

            // ── Finance ─────────────────────────────────────────────
            [
                "key" => "finance",
                "name" => "Finance",
                "icon" => "fas fa-wallet",
                "route" => "finance.dashboard",
                "description" => "Fees, payments, expenses, payroll and financial reports",
                "sort_order" => 80,
                "features" => [
                    ["key" => "view_finance", "name" => "View Finance Dashboard / Payments", "sort_order" => 1],
                    ["key" => "manage_fees", "name" => "Manage Fees", "sort_order" => 2],
                    ["key" => "record_payment", "name" => "Record Fee Payment", "sort_order" => 3],
                    ["key" => "manage_expenses", "name" => "Manage Expenses", "sort_order" => 4],
                    ["key" => "manage_payroll", "name" => "Manage Payroll", "sort_order" => 5],
                    ["key" => "financial_reports", "name" => "Financial Reports", "sort_order" => 6],
                ],
            ],

            // ── Attendance ──────────────────────────────────────────
            [
                "key" => "attendance",
                "name" => "Attendance",
                "icon" => "fas fa-user-check",
                "route" => "attendance.dashboard",
                "description" => "Student and staff attendance tracking",
                "sort_order" => 90,
                "features" => [
                    ["key" => "view_attendance", "name" => "View Attendance", "sort_order" => 1],
                    ["key" => "mark_attendance", "name" => "Mark Attendance", "sort_order" => 2],
                    ["key" => "attendance_reports", "name" => "Attendance Reports", "sort_order" => 3],
                ],
            ],

            // ── Timetable ───────────────────────────────────────────
            // See class docblock note #2 above re: TimetableController
            // currently reusing the Examinations feature keys.
            [
                "key" => "timetable",
                "name" => "Timetable",
                "icon" => "fas fa-calendar-alt",
                "route" => "timetable.dashboard",
                "description" => "Periods, class timetables and teacher schedules",
                "sort_order" => 100,
                "features" => [
                    ["key" => "view_teacher_schedule", "name" => "View Teacher Schedule", "sort_order" => 1],
                    ["key" => "view_timetable", "name" => "View Timetable", "sort_order" => 2],
                    ["key" => "create_timetable", "name" => "Create Timetable", "sort_order" => 3],
                    ["key" => "edit_timetable", "name" => "Edit Timetable", "sort_order" => 4],
                    ["key" => "delete_timetable", "name" => "Delete Timetable", "sort_order" => 5]
                ],
            ],

            // ── Examinations ────────────────────────────────────────
            // Also covers the keys TimetableController currently borrows
            // (view_exams / create_exam / edit_exam / delete_exam) — see note #2.
            [
                "key" => "examinations",
                "name" => "Examinations",
                "icon" => "fas fa-layer-group",
                "route" => "examination.index",
                "description" => "Examinations, marks entry, grading and results",
                "sort_order" => 110,
                "features" => [
                    ["key" => "view_exams", "name" => "View Exams", "sort_order" => 1],
                    ["key" => "create_exam", "name" => "Create Exam", "sort_order" => 2],
                    ["key" => "edit_exam", "name" => "Edit Exam", "sort_order" => 3],
                    ["key" => "delete_exam", "name" => "Delete Exam", "sort_order" => 4],
                    ["key" => "publish_results", "name" => "Publish Results", "sort_order" => 5],
                    ["key" => "generate_reports", "name" => "Generate Reports", "sort_order" => 6],
                ],
            ],

            // ── Notifications ───────────────────────────────────────
            [
                "key" => "notifications",
                "name" => "Notifications",
                "icon" => "fas fa-bell",
                "route" => "notifications.my",
                "description" => "View and broadcast school notifications",
                "sort_order" => 120,
                "features" => [
                    ["key" => "view_notifications", "name" => "View Notifications", "sort_order" => 1],
                    ["key" => "view_my_notifications", "name" => "View My Notifications", "sort_order" => 2],
                    ["key" => "create_notification", "name" => "Create Notification", "sort_order" => 3],
                    ["key" => "delete_notification", "name" => "Delete Notification", "sort_order" => 4],
                    ["key" => "push_notifications", "name" => "Push Notifications", "sort_order" => 5],
                    ["key" => "send_notification", "name" => "Broadcast Notification", "sort_order" => 6],
                    ["key" => "mark_notification_read", "name" => "Mark Notification Read", "sort_order" => 7],
                    ["key" => "mark_all_read", "name" => "Mark All Notifications Read", "sort_order" => 8],
                ],
            ],

            // ── Master Data ─────────────────────────────────────────
            [
                "key" => "master_data",
                "name" => "Master Data",
                "icon" => "fa fa-database",
                "route" => "master-code-to-data",
                "description" => "Manage system master data and master codes",
                "sort_order" => 130,
                "features" => [
                    ["key" => "view_master_data", "name" => "View Master Data", "sort_order" => 1],
                    ["key" => "create_master_data", "name" => "Create Master Data", "sort_order" => 2],
                    ["key" => "edit_master_data", "name" => "Edit Master Data", "sort_order" => 3],
                    ["key" => "delete_master_data", "name" => "Delete Master Data", "sort_order" => 4],
                    ["key" => "view_master_codes", "name" => "View Master Codes", "sort_order" => 5],
                    ["key" => "create_master_codes", "name" => "Create Master Codes", "sort_order" => 6],
                    ["key" => "edit_master_codes", "name" => "Edit Master Codes", "sort_order" => 7],
                    ["key" => "delete_master_codes", "name" => "Delete Master Codes", "sort_order" => 8],
                ],
            ],

            // ── User Rights & Permissions ───────────────────────────
            [
                "key" => "user_rights",
                "name" => "User Rights & Permissions",
                "icon" => "fas fa-shield-alt",
                "route" => "urp.dashboard",
                "description" => "Manage roles, module/feature permissions and staff role assignment",
                "sort_order" => 140,
                "features" => [
                    ["key" => "view_dashboard", "name" => "View URP Dashboard", "sort_order" => 1],
                    ["key" => "view_roles", "name" => "View Roles", "sort_order" => 2],
                    ["key" => "create_role", "name" => "Create Role", "sort_order" => 3],
                    ["key" => "edit_role", "name" => "Edit Role", "sort_order" => 4],
                    ["key" => "delete_role", "name" => "Delete Role", "sort_order" => 5],
                    ["key" => "view_permissions", "name" => "View Permissions", "sort_order" => 6],
                    ["key" => "assign_permissions", "name" => "Assign Permissions", "sort_order" => 7],
                    ["key" => "assign_roles_to_users", "name" => "Assign Roles to Staff", "sort_order" => 8],
                    ["key" => "remove_roles_from_users", "name" => "Remove Roles from Staff", "sort_order" => 9],
                ],
            ],

        ];

        foreach ($modules as $moduleData) {
            $features = $moduleData["features"] ?? [];
            unset($moduleData["features"]);

            $moduleData["is_active"] = $moduleData["is_active"] ?? true;

            $module = SystemModule::updateOrCreate(
                ["key" => $moduleData["key"]],
                $moduleData
            );

            foreach ($features as $featureData) {
                ModuleFeature::updateOrCreate(
                    [
                        "module_id" => $module->id,
                        "key" => $featureData["key"],
                    ],
                    [
                        "name" => $featureData["name"],
                        "sort_order" => $featureData["sort_order"],
                    ]
                );
            }
        }

        $this->command->info("System modules and features seeded successfully (synced with actual controller/middleware/sidebar guards).");
    }
}
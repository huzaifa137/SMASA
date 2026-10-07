<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemModule;
use App\Models\ModuleFeature;
use Illuminate\Support\Facades\DB;

/**
 * SMASA — modules & features catalogue for User Rights & Permissions.
 *
 *   php artisan db:seed --class=SystemModulesSeeder
 *
 * ── Design rules (read before adding a feature) ─────────────────────────
 *  1. ONE feature = ONE capability. A feature key is checked in exactly one
 *     kind of place: the controller method(s) that perform the capability
 *     (PermissionHelper::denyUnlessFeature() / canFeature()), and the
 *     Blade buttons/links that lead to it (@if(PermissionHelper::canFeature())).
 *     Never reuse a key for something else "because it was handy".
 *  2. Feature keys are unique GLOBALLY, not per module, because
 *     SchoolRole::canAccessFeature() looks features up by key only.
 *  3. Module keys must match the strings used in `module:xxx` route
 *     middleware and PermissionHelper::canModule().
 *  4. Naming:  view_*  (open pages / lists)   add_* / create_*  (new records)
 *              edit_*  (change records)       delete_*          (remove records)
 *              manage_* = add + edit + delete of a small lookup list
 *              (categories, authors, scales ...) where splitting three ways
 *              would only add noise;  export_* / import_* / print_* /
 *              approve_* / assign_* for the obvious actions.
 *  5. Platform-level screens (schools, users, ITEB, grading imports,
 *     custom report cards, parent portal) are NOT role-based: they are
 *     governed by the platform/school-administrator checks in Helper.
 *
 * ── Safe to re-run ──────────────────────────────────────────────────────
 *  This seeder NEVER truncates. Existing modules/features keep their ids, so
 *  every role's saved toggles survive. It only:
 *    • creates / renames / re-orders modules and features,
 *    • for each feature or module that is NEW in this run, copies access
 *      from the old keys listed under `inherits` ("module:xyz" = every role
 *      that had module xyz). That way no role loses anything it could do
 *      before the rewrite, and roles do not silently gain more than they had.
 *      (Inheritance only happens at creation time, so re-running later never
 *      re-grants something an administrator has since switched off.)
 *    • removes features/modules that are no longer in this catalogue.
 *  After the first run, open /user-rights/permissions and review each role.
 */
class SystemModulesSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->catalog();

        DB::transaction(function () use ($catalog) {

            // 1) Snapshot who had what BEFORE anything is changed ───────────
            $featureRoles = DB::table('role_feature_access as a')
                ->join('module_features as f', 'f.id', '=', 'a.feature_id')
                ->where('a.can_access', true)
                ->get(['f.key as k', 'a.school_role_id as r'])
                ->groupBy('k')->map(fn ($rows) => $rows->pluck('r')->unique()->values()->all())->all();

            $moduleRoles = DB::table('role_module_access as a')
                ->join('system_modules as m', 'm.id', '=', 'a.module_id')
                ->where('a.can_access', true)
                ->get(['m.key as k', 'a.school_role_id as r'])
                ->groupBy('k')->map(fn ($rows) => $rows->pluck('r')->unique()->values()->all())->all();

            $now = now();
            $keepModules = [];
            $keepFeatures = [];

            foreach ($catalog as $row) {
                $features = $row["features"];
                $inheritsModule = $row["inherits_module"] ?? null;
                unset($row["features"], $row["inherits_module"]);
                $row["is_active"] = true;

                // 2) Upsert the module ──────────────────────────────────────
                $module = SystemModule::updateOrCreate(["key" => $row["key"]], $row);
                $keepModules[] = $module->key;

                if ($module->wasRecentlyCreated && $inheritsModule) {
                    foreach ($moduleRoles[$inheritsModule] ?? [] as $roleId) {
                        DB::table('role_module_access')->insertOrIgnore([
                            'school_role_id' => $roleId, 'module_id' => $module->id,
                            'can_access' => true, 'created_at' => $now, 'updated_at' => $now,
                        ]);
                    }
                }

                // 3) Upsert its features (+ inherit access for NEW ones) ─────
                foreach ($features as $i => $f) {
                    $feature = ModuleFeature::updateOrCreate(
                        ["module_id" => $module->id, "key" => $f["key"]],
                        ["name" => $f["name"], "sort_order" => $i + 1]
                    );
                    $keepFeatures[] = $feature->key;

                    if (!$feature->wasRecentlyCreated) {
                        continue;
                    }

                    $roles = [];
                    foreach ($f["inherits"] ?? [] as $old) {
                        $source = str_starts_with($old, 'module:')
                            ? ($moduleRoles[substr($old, 7)] ?? [])
                            : ($featureRoles[$old] ?? []);
                        $roles = array_merge($roles, $source);
                    }
                    foreach (array_unique($roles) as $roleId) {
                        DB::table('role_feature_access')->insertOrIgnore([
                            'school_role_id' => $roleId, 'feature_id' => $feature->id,
                            'can_access' => true, 'created_at' => $now, 'updated_at' => $now,
                        ]);
                    }
                }
            }

            // 4) Prune anything that is no longer in the catalogue ───────────
            ModuleFeature::whereNotIn('key', $keepFeatures)->delete();
            SystemModule::whereNotIn('key', $keepModules)->delete();
        });

        $this->command?->info("System modules and features synced (" . count($this->catalog()) . " modules).");
    }

    /**
     * The catalogue. Order here = order shown on /user-rights/permissions.
     */
    private function catalog(): array
    {
        return [

            // ── Classes & Streams ─────────────────────────────────────────
            [
                "key" => "classes",
                "name" => "Classes & Streams",
                "icon" => "fa fa-chalkboard-teacher",
                "route" => "all.my-classes",
                "description" => "Classes, streams, class teachers and subject-teacher assignments",
                "sort_order" => 10,
                "features" => [
                    ["key" => "view_classes", "name" => "View Classes"],
                    ["key" => "add_class", "name" => "Add Class"],
                    ["key" => "edit_class", "name" => "Edit Class & Class Subjects"],
                    ["key" => "delete_class", "name" => "Delete Class"],
                    ["key" => "manage_streams", "name" => "Manage Streams"],
                    ["key" => "delete_stream", "name" => "Delete Stream", "inherits" => ["manage_streams"]],
                    ["key" => "view_teacher_assignments", "name" => "View Teacher Assignments", "inherits" => ["assign_class_teacher", "assign_subject_teachers"]],
                    ["key" => "assign_class_teacher", "name" => "Assign / Remove Class Teacher"],
                    ["key" => "assign_class_supervisor", "name" => "Assign / Remove Class Supervisor", "inherits" => ["assign_class_teacher"]],
                    ["key" => "assign_subject_teachers", "name" => "Assign / Remove Subject Teachers"],
                ],
            ],

            // ── Subjects & Curriculum ─────────────────────────────────────
            [
                "key" => "curriculum",
                "name" => "Subjects & Curriculum",
                "icon" => "fa fa-book-open",
                "route" => "custom-subjects.manage",
                "description" => "Custom subjects, subject products, A-Level combinations, O-Level electives and NLSC school curriculum",
                "sort_order" => 15,
                "inherits_module" => "classes",
                "features" => [
                    ["key" => "view_custom_subjects", "name" => "View Custom Subjects", "inherits" => ["module:classes"]],
                    ["key" => "add_custom_subject", "name" => "Add Custom Subject", "inherits" => ["module:classes"]],
                    ["key" => "edit_custom_subject", "name" => "Edit Custom Subject", "inherits" => ["module:classes"]],
                    ["key" => "delete_custom_subject", "name" => "Delete Custom Subject", "inherits" => ["module:classes"]],
                    ["key" => "switch_subject_mode", "name" => "Switch / Revert Subject Mode", "inherits" => ["module:classes"]],
                    ["key" => "view_subject_products", "name" => "View Subject Products", "inherits" => ["module:classes"]],
                    ["key" => "merge_subject_products", "name" => "Merge Subject Products", "inherits" => ["module:classes"]],
                    ["key" => "split_subject_products", "name" => "Split Subject Products", "inherits" => ["module:classes"]],
                    ["key" => "view_alevel_combinations", "name" => "View A-Level Combinations", "inherits" => ["add_class"]],
                    ["key" => "save_alevel_combinations", "name" => "Assign A-Level Combinations", "inherits" => ["add_class"]],
                    ["key" => "manage_alevel_subjects", "name" => "Manage A-Level School Subjects", "inherits" => ["add_class"]],
                    ["key" => "view_olevel_electives", "name" => "View O-Level Electives", "inherits" => ["add_class"]],
                    ["key" => "save_olevel_electives", "name" => "Assign O-Level Electives", "inherits" => ["add_class"]],
                    ["key" => "manage_olevel_subjects", "name" => "Manage O-Level School Subjects", "inherits" => ["add_class"]],
                    ["key" => "view_nlsc_projects", "name" => "View NLSC Projects", "inherits" => ["view_classes"]],
                    ["key" => "add_nlsc_project", "name" => "Add NLSC Project / Competency Area", "inherits" => ["add_class"]],
                    ["key" => "edit_nlsc_project", "name" => "Edit NLSC Project / Competency Area", "inherits" => ["edit_class"]],
                    ["key" => "delete_nlsc_project", "name" => "Delete NLSC Project / Competency Area", "inherits" => ["delete_class"]],
                    ["key" => "view_nlsc_topics", "name" => "View NLSC Topics", "inherits" => ["view_classes"]],
                    ["key" => "add_nlsc_topic", "name" => "Add NLSC Topic / Competency Area", "inherits" => ["add_class"]],
                    ["key" => "edit_nlsc_topic", "name" => "Edit NLSC Topic / Competency Area", "inherits" => ["edit_class"]],
                    ["key" => "delete_nlsc_topic", "name" => "Delete NLSC Topic / Competency Area", "inherits" => ["delete_class"]],
                    ["key" => "view_nlsc_achievements", "name" => "View NLSC Subject Achievements", "inherits" => ["view_classes"]],
                    ["key" => "add_nlsc_achievement", "name" => "Add NLSC Subject Achievement", "inherits" => ["add_class"]],
                    ["key" => "edit_nlsc_achievement", "name" => "Edit NLSC Subject Achievement", "inherits" => ["edit_class"]],
                    ["key" => "delete_nlsc_achievement", "name" => "Delete NLSC Subject Achievement", "inherits" => ["delete_class"]],
                ],
            ],

            // ── Teachers ──────────────────────────────────────────────────
            [
                "key" => "teachers",
                "name" => "Teachers",
                "icon" => "fa fa-user-tie",
                "route" => "school.teachers",
                "description" => "Teacher records, accounts, roles and bulk import",
                "sort_order" => 20,
                "inherits_module" => "classes",
                "features" => [
                    ["key" => "view_teachers", "name" => "View Teachers & Profiles"],
                    ["key" => "add_teacher", "name" => "Add Teacher"],
                    ["key" => "import_teachers", "name" => "Bulk Import Teachers", "inherits" => ["add_teacher"]],
                    ["key" => "edit_teacher", "name" => "Edit Teacher"],
                    ["key" => "change_teacher_role", "name" => "Change Teacher Role", "inherits" => ["edit_teacher"]],
                    ["key" => "delete_teacher", "name" => "Delete Teacher"],
                    ["key" => "manage_teacher_status", "name" => "Manage Teacher Status"],
                ],
            ],

            // ── Students ──────────────────────────────────────────────────
            [
                "key" => "students",
                "name" => "Students",
                "icon" => "fa fa-user-graduate",
                "route" => "students.individual.search",
                "description" => "Student records, transfers, bulk import/export and duplicate consolidation",
                "sort_order" => 30,
                "features" => [
                    ["key" => "view_students", "name" => "View & Search Students"],
                    ["key" => "view_student_details", "name" => "View Student Details"],
                    ["key" => "add_student", "name" => "Add Student"],
                    ["key" => "edit_student", "name" => "Edit Student"],
                    ["key" => "transfer_student", "name" => "Transfer Student (Class / Stream)", "inherits" => ["edit_student"]],
                    ["key" => "delete_student", "name" => "Delete Student"],
                    ["key" => "import_students", "name" => "Bulk Import Students"],
                    ["key" => "import_student_photos", "name" => "Bulk Import Student Photos", "inherits" => ["import_students"]],
                    ["key" => "export_students", "name" => "Export Students"],
                    ["key" => "view_student_consolidation", "name" => "View Student Consolidation", "inherits" => ["view_students"]],
                    ["key" => "manage_student_consolidation", "name" => "Link / Unlink / Dismiss Duplicates", "inherits" => ["add_student"]],
                    ["key" => "delete_duplicate_students", "name" => "Delete Duplicate Students", "inherits" => ["add_student"]],
                ],
            ],

            // ── Card Scan ─────────────────────────────────────────────────
            [
                "key" => "card_scan",
                "name" => "Card Scan",
                "icon" => "fa fa-qrcode",
                "route" => "card-scan.hub",
                "description" => "Card scanning hub and arrival attendance logging",
                "sort_order" => 40,
                "features" => [
                    ["key" => "view_hub", "name" => "View Scan Hub"],
                    ["key" => "scan_cards", "name" => "Scan Cards"],
                    ["key" => "view_scan_logs", "name" => "View Scan Logs"],
                    ["key" => "manage_arrival_attendance", "name" => "Record Arrival Attendance"],
                    ["key" => "view_arrival_reports", "name" => "View Arrival Reports"],
                ],
            ],

            // ── Student ID Cards ──────────────────────────────────────────
            [
                "key" => "student_id_cards",
                "name" => "Student ID Cards",
                "icon" => "fas fa-id-card",
                "route" => "id-cards.index",
                "description" => "Student ID card issuance and verification",
                "sort_order" => 50,
                "features" => [
                    ["key" => "view_cards", "name" => "View Student ID Cards"],
                    ["key" => "generate_cards", "name" => "Generate Student ID Cards"],
                    ["key" => "print_cards", "name" => "Print Student ID Cards"],
                    ["key" => "verify_cards", "name" => "Verify Student ID Cards"],
                    ["key" => "revoke_cards", "name" => "Revoke Student ID Cards"],
                    ["key" => "reactivate_cards", "name" => "Reactivate Student ID Cards"],
                ],
            ],

            // ── Teacher ID Cards ──────────────────────────────────────────
            [
                "key" => "teacher_id_cards",
                "name" => "Teacher ID Cards",
                "icon" => "fas fa-id-card",
                "route" => "teacher-id-cards.index",
                "description" => "Teacher ID card issuance and verification",
                "sort_order" => 60,
                "features" => [
                    ["key" => "view_teacher_cards", "name" => "View Teacher ID Cards"],
                    ["key" => "generate_teacher_cards", "name" => "Generate Teacher ID Cards"],
                    ["key" => "print_teacher_cards", "name" => "Print Teacher ID Cards"],
                    ["key" => "verify_teacher_cards", "name" => "Verify Teacher ID Cards"],
                    ["key" => "revoke_teacher_cards", "name" => "Revoke Teacher ID Cards"],
                    ["key" => "reactivate_teacher_cards", "name" => "Reactivate Teacher ID Cards"],
                ],
            ],

            // ── Library ───────────────────────────────────────────────────
            [
                "key" => "library",
                "name" => "Library",
                "icon" => "fas fa-landmark",
                "route" => "library.dashboard",
                "description" => "Library catalogue, borrowing, members, fines and reports",
                "sort_order" => 70,
                "features" => [
                    ["key" => "view_library_dashboard", "name" => "View Library Dashboard"],
                    ["key" => "view_books", "name" => "View Books", "inherits" => ["add_book"]],
                    ["key" => "add_book", "name" => "Add Book"],
                    ["key" => "edit_book", "name" => "Edit Book"],
                    ["key" => "delete_book", "name" => "Delete Book"],
                    ["key" => "import_books", "name" => "Import Books", "inherits" => ["add_book"]],
                    ["key" => "export_books", "name" => "Export Books", "inherits" => ["library_reports"]],
                    ["key" => "download_ebook", "name" => "Download E-Books", "inherits" => ["library_reports"]],
                    ["key" => "browse_catalogue", "name" => "Browse Catalogue", "inherits" => ["view_books"]],
                    ["key" => "view_my_borrowings", "name" => "View My Borrowings", "inherits" => ["view_books"]],
                    ["key" => "view_book_categories", "name" => "View Book Categories", "inherits" => ["view_books"]],
                    ["key" => "manage_book_categories", "name" => "Add / Edit / Delete Book Categories", "inherits" => ["add_book", "edit_book", "delete_book"]],
                    ["key" => "view_book_authors", "name" => "View Book Authors", "inherits" => ["view_books"]],
                    ["key" => "manage_book_authors", "name" => "Add / Edit / Delete Book Authors", "inherits" => ["add_book", "edit_book", "delete_book"]],
                    ["key" => "view_book_subjects", "name" => "View Book Subjects", "inherits" => ["view_books"]],
                    ["key" => "manage_book_subjects", "name" => "Add / Edit / Delete Book Subjects", "inherits" => ["add_book", "edit_book", "delete_book"]],
                    ["key" => "view_members", "name" => "View Library Members", "inherits" => ["manage_members"]],
                    ["key" => "add_member", "name" => "Add Library Member", "inherits" => ["manage_members"]],
                    ["key" => "edit_member", "name" => "Edit Library Member", "inherits" => ["manage_members"]],
                    ["key" => "delete_member", "name" => "Delete Library Member", "inherits" => ["manage_members"]],
                    ["key" => "view_borrowings", "name" => "View Borrowings", "inherits" => ["manage_borrowing"]],
                    ["key" => "borrow_book", "name" => "Issue Book to Member", "inherits" => ["manage_borrowing"]],
                    ["key" => "return_book", "name" => "Return Book", "inherits" => ["manage_borrowing"]],
                    ["key" => "renew_borrowing", "name" => "Renew Borrowing", "inherits" => ["manage_borrowing"]],
                    ["key" => "mark_book_lost", "name" => "Mark Book Lost", "inherits" => ["manage_borrowing"]],
                    ["key" => "view_reservations", "name" => "View Reservations", "inherits" => ["manage_borrowing"]],
                    ["key" => "manage_reservations", "name" => "Create / Update Reservations", "inherits" => ["manage_borrowing"]],
                    ["key" => "view_fines", "name" => "View Fines", "inherits" => ["manage_borrowing"]],
                    ["key" => "collect_fines", "name" => "Collect Fine Payment", "inherits" => ["manage_borrowing"]],
                    ["key" => "waive_fines", "name" => "Waive Fines", "inherits" => ["manage_borrowing"]],
                    ["key" => "view_book_requests", "name" => "View Book Requests", "inherits" => ["manage_members"]],
                    ["key" => "create_book_request", "name" => "Submit Book Request", "inherits" => ["manage_members"]],
                    ["key" => "review_book_request", "name" => "Review Book Request", "inherits" => ["manage_members"]],
                    ["key" => "library_reports", "name" => "View Library Reports"],
                    ["key" => "view_library_settings", "name" => "View Library Settings", "inherits" => ["manage_settings"]],
                    ["key" => "manage_library_settings", "name" => "Update Library Settings", "inherits" => ["manage_settings"]],
                ],
            ],

            // ── Finance ───────────────────────────────────────────────────
            [
                "key" => "finance",
                "name" => "Finance",
                "icon" => "fas fa-wallet",
                "route" => "finance.dashboard",
                "description" => "Fees, payments, expenses, payroll, budgets, ledgers and financial reports",
                "sort_order" => 80,
                "features" => [
                    ["key" => "view_finance", "name" => "View Finance Dashboard"],
                    ["key" => "view_fee_structures", "name" => "View Fee Structures", "inherits" => ["manage_fees"]],
                    ["key" => "add_fee_structure", "name" => "Add Fee Structure", "inherits" => ["manage_fees"]],
                    ["key" => "edit_fee_structure", "name" => "Edit Fee Structure", "inherits" => ["manage_fees"]],
                    ["key" => "delete_fee_structure", "name" => "Delete Fee Structure", "inherits" => ["manage_fees"]],
                    ["key" => "view_fee_categories", "name" => "View Fee Categories", "inherits" => ["manage_fees"]],
                    ["key" => "manage_fee_categories", "name" => "Add / Edit / Delete Fee Categories", "inherits" => ["manage_fees"]],
                    ["key" => "view_fee_allocations", "name" => "View Fee Allocations", "inherits" => ["manage_fees"]],
                    ["key" => "allocate_fees", "name" => "Allocate Fees to Students", "inherits" => ["manage_fees"]],
                    ["key" => "edit_fee_allocation", "name" => "Edit Fee Allocation", "inherits" => ["manage_fees"]],
                    ["key" => "delete_fee_allocation", "name" => "Delete Fee Allocation", "inherits" => ["manage_fees"]],
                    ["key" => "recalculate_balances", "name" => "Recalculate Student Balances", "inherits" => ["manage_fees"]],
                    ["key" => "view_payments", "name" => "View Payments", "inherits" => ["view_finance"]],
                    ["key" => "record_payment", "name" => "Record Fee Payment"],
                    ["key" => "reverse_payment", "name" => "Reverse Payment", "inherits" => ["record_payment"]],
                    ["key" => "print_receipt", "name" => "Print Payment Receipt", "inherits" => ["view_finance"]],
                    ["key" => "view_expenses", "name" => "View Expenses", "inherits" => ["manage_expenses"]],
                    ["key" => "add_expense", "name" => "Add Expense", "inherits" => ["manage_expenses"]],
                    ["key" => "edit_expense", "name" => "Edit Expense", "inherits" => ["manage_expenses"]],
                    ["key" => "delete_expense", "name" => "Delete Expense", "inherits" => ["manage_expenses"]],
                    ["key" => "view_expense_categories", "name" => "View Expense Categories", "inherits" => ["manage_expenses"]],
                    ["key" => "manage_expense_categories", "name" => "Add / Edit / Delete Expense Categories", "inherits" => ["manage_expenses"]],
                    ["key" => "view_payroll", "name" => "View Payroll & Payslips", "inherits" => ["manage_payroll"]],
                    ["key" => "create_payroll_period", "name" => "Create Payroll Period", "inherits" => ["manage_payroll"]],
                    ["key" => "approve_payroll", "name" => "Approve Payroll", "inherits" => ["manage_payroll"]],
                    ["key" => "mark_payroll_paid", "name" => "Mark Payroll as Paid", "inherits" => ["manage_payroll"]],
                    ["key" => "view_salary_structures", "name" => "View Salary Structures", "inherits" => ["manage_payroll"]],
                    ["key" => "manage_salary_structures", "name" => "Manage Salary Structures", "inherits" => ["manage_payroll"]],
                    ["key" => "view_budgets", "name" => "View Budgets", "inherits" => ["view_finance"]],
                    ["key" => "add_budget", "name" => "Add Budget", "inherits" => ["view_finance"]],
                    ["key" => "edit_budget", "name" => "Edit Budget", "inherits" => ["view_finance"]],
                    ["key" => "approve_budget", "name" => "Approve Budget", "inherits" => ["financial_reports"]],
                    ["key" => "financial_reports", "name" => "View Financial Reports"],
                    ["key" => "export_financial_reports", "name" => "Export Financial Reports", "inherits" => ["financial_reports"]],
                    ["key" => "view_outstanding_fees", "name" => "View Outstanding Fees", "inherits" => ["financial_reports"]],
                    ["key" => "export_outstanding_fees", "name" => "Export Outstanding Fees", "inherits" => ["financial_reports"]],
                    ["key" => "view_chart_of_accounts", "name" => "View Chart of Accounts", "inherits" => ["manage_ledger"]],
                    ["key" => "manage_chart_of_accounts", "name" => "Manage Chart of Accounts", "inherits" => ["manage_ledger"]],
                    ["key" => "view_general_ledger", "name" => "View General Ledger", "inherits" => ["financial_reports"]],
                    ["key" => "view_student_fee_ledger", "name" => "View Student Fee Ledger", "inherits" => ["financial_reports"]],
                    ["key" => "view_trial_balance", "name" => "View Trial Balance", "inherits" => ["financial_reports"]],
                ],
            ],

            // ── Attendance ────────────────────────────────────────────────
            [
                "key" => "attendance",
                "name" => "Attendance",
                "icon" => "fas fa-user-check",
                "route" => "attendance.dashboard",
                "description" => "Student and staff attendance tracking and reports",
                "sort_order" => 90,
                "features" => [
                    ["key" => "view_attendance", "name" => "View Attendance Dashboard"],
                    ["key" => "view_student_attendance", "name" => "View Student Attendance", "inherits" => ["view_attendance"]],
                    ["key" => "mark_student_attendance", "name" => "Mark Student Attendance", "inherits" => ["mark_attendance"]],
                    ["key" => "view_student_attendance_report", "name" => "View Student Attendance Report", "inherits" => ["attendance_reports"]],
                    ["key" => "view_teacher_attendance", "name" => "View Staff Attendance", "inherits" => ["view_attendance"]],
                    ["key" => "mark_teacher_attendance", "name" => "Mark Staff Attendance", "inherits" => ["mark_attendance"]],
                    ["key" => "view_teacher_attendance_report", "name" => "View Staff Attendance Report", "inherits" => ["attendance_reports"]],
                    ["key" => "view_class_attendance_summary", "name" => "View Class Attendance Summary", "inherits" => ["attendance_reports"]],
                ],
            ],

            // ── Timetable ─────────────────────────────────────────────────
            [
                "key" => "timetable",
                "name" => "Timetable",
                "icon" => "fas fa-calendar-alt",
                "route" => "timetable.dashboard",
                "description" => "Periods, class timetables, master timetable and teacher schedules",
                "sort_order" => 100,
                "features" => [
                    ["key" => "view_timetable", "name" => "View Timetables"],
                    ["key" => "view_master_timetable", "name" => "View Master Timetable", "inherits" => ["view_timetable"]],
                    ["key" => "view_teachers_summary", "name" => "View Teachers Workload Summary", "inherits" => ["view_timetable"]],
                    ["key" => "view_teacher_schedule", "name" => "View My Teaching Schedule"],
                    ["key" => "view_periods", "name" => "View Periods", "inherits" => ["view_timetable"]],
                    ["key" => "manage_periods", "name" => "Add / Edit / Delete Periods", "inherits" => ["edit_timetable"]],
                    ["key" => "create_timetable", "name" => "Create Timetable"],
                    ["key" => "duplicate_timetable", "name" => "Duplicate Timetable", "inherits" => ["create_timetable"]],
                    ["key" => "edit_timetable", "name" => "Edit Timetable & Slots"],
                    ["key" => "change_timetable_status", "name" => "Change Timetable Status", "inherits" => ["edit_timetable"]],
                    ["key" => "delete_timetable", "name" => "Delete Timetable"],
                ],
            ],

            // ── Examinations ──────────────────────────────────────────────
            [
                "key" => "examinations",
                "name" => "Examinations",
                "icon" => "fas fa-layer-group",
                "route" => "examination.index",
                "description" => "Examinations, subjects, marks entry, grading setup, remarks, results and NLSC assessments",
                "sort_order" => 110,
                "features" => [
                    ["key" => "view_exams", "name" => "View Examinations"],
                    ["key" => "create_exam", "name" => "Create Examination"],
                    ["key" => "edit_exam", "name" => "Edit Examination Details"],
                    ["key" => "change_exam_status", "name" => "Change Examination Status", "inherits" => ["edit_exam", "publish_results"]],
                    ["key" => "delete_exam", "name" => "Delete Examination"],
                    ["key" => "manage_exam_subjects", "name" => "Select Examination Subjects", "inherits" => ["edit_exam"]],
                    ["key" => "view_marks_entry", "name" => "Open Marks Entry", "inherits" => ["view_exams"]],
                    ["key" => "enter_marks", "name" => "Enter / Save Marks", "inherits" => ["edit_exam"]],
                    ["key" => "view_discipline", "name" => "View Discipline Ratings", "inherits" => ["view_exams"]],
                    ["key" => "enter_discipline_ratings", "name" => "Enter Discipline Ratings", "inherits" => ["edit_exam"]],
                    ["key" => "manage_discipline_criteria", "name" => "Manage Discipline Criteria", "inherits" => ["edit_exam"]],
                    ["key" => "view_remarks", "name" => "View Remarks Entry", "inherits" => ["view_exams"]],
                    ["key" => "enter_remarks", "name" => "Enter Remarks", "inherits" => ["edit_exam"]],
                    ["key" => "publish_results", "name" => "Release / Publish Results"],
                    ["key" => "view_grading_schemes", "name" => "View Grading Schemes", "inherits" => ["view_exams"]],
                    ["key" => "manage_grading_schemes", "name" => "Add / Edit / Delete Grading Schemes", "inherits" => ["create_exam", "edit_exam"]],
                    ["key" => "view_assessment_scales", "name" => "View Assessment Scales", "inherits" => ["view_exams"]],
                    ["key" => "manage_assessment_scales", "name" => "Add / Edit / Delete Assessment Scales", "inherits" => ["create_exam", "edit_exam"]],
                    ["key" => "assign_assessment_scales", "name" => "Assign Assessment Scales to Subjects", "inherits" => ["edit_class"]],
                    ["key" => "view_aggregate_subjects", "name" => "View Aggregate Subjects", "inherits" => ["view_exams"]],
                    ["key" => "manage_aggregate_subjects", "name" => "Manage Aggregate Subjects", "inherits" => ["edit_class"]],
                    ["key" => "view_nlsc_assessments", "name" => "View NLSC Assessments", "inherits" => ["view_exams"]],
                    ["key" => "manage_nlsc_assessments", "name" => "Add / Edit NLSC Assessments", "inherits" => ["edit_exam"]],
                    ["key" => "delete_nlsc_assessments", "name" => "Delete NLSC Assessments", "inherits" => ["delete_exam"]],
                    ["key" => "enter_nlsc_marks", "name" => "Enter NLSC Assessment Marks", "inherits" => ["edit_exam"]],
                    ["key" => "view_report_cards", "name" => "View & Print Report Cards / Passlips", "inherits" => ["generate_reports"]],
                    ["key" => "customize_report_cards", "name" => "Customize Report Card Layout", "inherits" => ["generate_reports"]],
                    ["key" => "manage_report_card_settings", "name" => "Save / Copy / Delete Report Card Settings", "inherits" => ["generate_reports"]],
                    ["key" => "view_exam_reports", "name" => "View Examination Reports & Analysis", "inherits" => ["generate_reports"]],
                    ["key" => "export_exam_reports", "name" => "Export Examination Reports (PDF / Excel)", "inherits" => ["generate_reports"]],
                ],
            ],

            // ── Notifications ─────────────────────────────────────────────
            [
                "key" => "notifications",
                "name" => "Notifications",
                "icon" => "fas fa-bell",
                "route" => "notifications.my",
                "description" => "View and broadcast school notifications",
                "sort_order" => 120,
                "features" => [
                    ["key" => "view_my_notifications", "name" => "View My Notifications"],
                    ["key" => "mark_notification_read", "name" => "Mark Notification Read"],
                    ["key" => "mark_all_read", "name" => "Mark All Notifications Read"],
                    ["key" => "push_notifications", "name" => "Enable Push Notifications"],
                    ["key" => "view_notifications", "name" => "View Sent Notifications"],
                    ["key" => "create_notification", "name" => "Open Create-Notification Form"],
                    ["key" => "send_notification", "name" => "Broadcast Notification"],
                    ["key" => "delete_notification", "name" => "Delete Notification"],
                ],
            ],

            // ── Master Data ───────────────────────────────────────────────
            [
                "key" => "master_data",
                "name" => "Master Data",
                "icon" => "fa fa-database",
                "route" => "master-code-to-data",
                "description" => "System master data, master codes, NLSC curriculum master and secondary subject lists",
                "sort_order" => 130,
                "features" => [
                    ["key" => "view_master_data", "name" => "View Master Data"],
                    ["key" => "create_master_data", "name" => "Create Master Data"],
                    ["key" => "edit_master_data", "name" => "Edit Master Data"],
                    ["key" => "delete_master_data", "name" => "Delete Master Data"],
                    ["key" => "view_master_codes", "name" => "View Master Codes"],
                    ["key" => "create_master_codes", "name" => "Create Master Codes"],
                    ["key" => "edit_master_codes", "name" => "Edit Master Codes"],
                    ["key" => "delete_master_codes", "name" => "Delete Master Codes"],
                    ["key" => "view_nlsc_master", "name" => "View NLSC Master Curriculum", "inherits" => ["view_master_data"]],
                    ["key" => "add_nlsc_master", "name" => "Add NLSC Master Records", "inherits" => ["create_master_data"]],
                    ["key" => "import_nlsc_master", "name" => "Bulk Import NLSC Master Records", "inherits" => ["create_master_data"]],
                    ["key" => "edit_nlsc_master", "name" => "Edit NLSC Master Records", "inherits" => ["edit_master_data"]],
                    ["key" => "delete_nlsc_master", "name" => "Delete NLSC Master Records", "inherits" => ["delete_master_data"]],
                    ["key" => "view_secondary_subjects", "name" => "View Secondary A/O-Level Subject Lists", "inherits" => ["view_master_data"]],
                    ["key" => "add_secondary_subject", "name" => "Add Secondary Subject", "inherits" => ["create_master_data"]],
                    ["key" => "edit_secondary_subject", "name" => "Edit Secondary Subject", "inherits" => ["edit_master_data"]],
                    ["key" => "delete_secondary_subject", "name" => "Delete Secondary Subject", "inherits" => ["delete_master_data"]],
                ],
            ],

            // ── User Rights & Permissions ─────────────────────────────────
            [
                "key" => "user_rights",
                "name" => "User Rights & Permissions",
                "icon" => "fas fa-shield-alt",
                "route" => "urp.dashboard",
                "description" => "Roles, module/feature permissions and staff role assignment",
                "sort_order" => 140,
                "features" => [
                    ["key" => "view_urp_dashboard", "name" => "View User Rights Dashboard", "inherits" => ["view_dashboard"]],
                    ["key" => "view_roles", "name" => "View Roles"],
                    ["key" => "create_role", "name" => "Create Role"],
                    ["key" => "edit_role", "name" => "Edit Role"],
                    ["key" => "delete_role", "name" => "Delete Role"],
                    ["key" => "view_permissions", "name" => "View Permissions"],
                    ["key" => "assign_permissions", "name" => "Assign Permissions"],
                    ["key" => "view_role_assignments", "name" => "View Staff Role Assignments", "inherits" => ["view_roles"]],
                    ["key" => "assign_roles_to_users", "name" => "Assign Roles to Staff"],
                    ["key" => "remove_roles_from_users", "name" => "Remove Roles from Staff"],
                ],
            ],

        ];
    }
}

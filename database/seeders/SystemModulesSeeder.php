<?php

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
                    ["key" => "view_books", "name" => "View Books", "sort_order" => 1],
                    ["key" => "add_book", "name" => "Add Book", "sort_order" => 2],
                    ["key" => "edit_book", "name" => "Edit Book", "sort_order" => 3],
                    ["key" => "delete_book", "name" => "Delete Book", "sort_order" => 4],
                    ["key" => "manage_borrowing", "name" => "Manage Borrowing", "sort_order" => 5],
                    ["key" => "manage_members", "name" => "Manage Members", "sort_order" => 6],
                    ["key" => "library_reports", "name" => "Library Reports", "sort_order" => 7],
                ]
            ],
            [
                "key" => "human_resources",
                "name" => "Human Resources",
                "icon" => "fa fa-users-gear",
                "route" => "hr.index",
                "description" => "Staff and HR management",
                "sort_order" => 10,
                "features" => [
                    ["key" => "view_staff", "name" => "View Staff", "sort_order" => 1],
                    ["key" => "add_staff", "name" => "Add Staff", "sort_order" => 2],
                    ["key" => "edit_staff", "name" => "Edit Staff", "sort_order" => 3],
                    ["key" => "delete_staff", "name" => "Delete Staff", "sort_order" => 4],
                    ["key" => "manage_payroll", "name" => "Manage Payroll", "sort_order" => 5],
                    ["key" => "manage_leave", "name" => "Manage Leave", "sort_order" => 6],
                    ["key" => "attendance_staff", "name" => "Staff Attendance", "sort_order" => 7],
                ]
            ],
            [
                "key" => "settings",
                "name" => "Settings",
                "icon" => "fa fa-cog",
                "route" => "settings.index",
                "description" => "System settings",
                "sort_order" => 99,
                "features" => [
                    ["key" => "view_settings", "name" => "View Settings", "sort_order" => 1],
                    ["key" => "edit_settings", "name" => "Edit Settings", "sort_order" => 2],
                    ["key" => "manage_roles", "name" => "Manage Roles", "sort_order" => 3],
                    ["key" => "manage_permissions", "name" => "Manage Permissions", "sort_order" => 4],
                    ["key" => "system_backup", "name" => "System Backup", "sort_order" => 5],
                ]
            ],
            [
                "key" => "reports",
                "name" => "Reports",
                "icon" => "fa fa-chart-bar",
                "route" => "reports.index",
                "description" => "System reports",
                "sort_order" => 100,
                "features" => [
                    ["key" => "view_reports", "name" => "View Reports", "sort_order" => 1],
                    ["key" => "generate_custom_reports", "name" => "Custom Reports", "sort_order" => 2],
                    ["key" => "export_reports", "name" => "Export Reports", "sort_order" => 3],
                ]
            ],
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

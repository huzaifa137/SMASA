# SMASA — Permissions catalogue

Generated from `database/seeders/SystemModulesSeeder.php`. One feature = one capability; the same key is used by the controller guard and by the Blade button/link that leads to it.

Run: `php artisan db:seed --class=SystemModulesSeeder` (safe to re-run; never truncates).

Guard cheat-sheet:

```php
// Controller
PermissionHelper::denyUnlessFeature('add_student');          // 403 if not allowed
if (!PermissionHelper::canFeature('enter_marks')) { ... }     // custom response
// Blade
@if(PermissionHelper::canFeature('delete_student')) ... @endif
// Route
Route::middleware(['module:finance'])->group(...);
```

## Classes & Streams  (`classes`)

Classes, streams, class teachers and subject-teacher assignments.

| Feature key | Meaning |
|---|---|
| `view_classes` | View Classes |
| `add_class` | Add Class |
| `edit_class` | Edit Class & Class Subjects |
| `delete_class` | Delete Class |
| `manage_streams` | Manage Streams |
| `delete_stream` | Delete Stream |
| `view_teacher_assignments` | View Teacher Assignments |
| `assign_class_teacher` | Assign / Remove Class Teacher |
| `assign_class_supervisor` | Assign / Remove Class Supervisor |
| `assign_subject_teachers` | Assign / Remove Subject Teachers |

## Subjects & Curriculum  (`curriculum`)

Custom subjects, subject products, A-Level combinations, O-Level electives and NLSC school curriculum.

| Feature key | Meaning |
|---|---|
| `view_custom_subjects` | View Custom Subjects |
| `add_custom_subject` | Add Custom Subject |
| `edit_custom_subject` | Edit Custom Subject |
| `delete_custom_subject` | Delete Custom Subject |
| `switch_subject_mode` | Switch / Revert Subject Mode |
| `view_subject_products` | View Subject Products |
| `merge_subject_products` | Merge Subject Products |
| `split_subject_products` | Split Subject Products |
| `view_alevel_combinations` | View A-Level Combinations |
| `save_alevel_combinations` | Assign A-Level Combinations |
| `manage_alevel_subjects` | Manage A-Level School Subjects |
| `view_olevel_electives` | View O-Level Electives |
| `save_olevel_electives` | Assign O-Level Electives |
| `manage_olevel_subjects` | Manage O-Level School Subjects |
| `view_nlsc_projects` | View NLSC Projects |
| `add_nlsc_project` | Add NLSC Project / Competency Area |
| `edit_nlsc_project` | Edit NLSC Project / Competency Area |
| `delete_nlsc_project` | Delete NLSC Project / Competency Area |
| `view_nlsc_topics` | View NLSC Topics |
| `add_nlsc_topic` | Add NLSC Topic / Competency Area |
| `edit_nlsc_topic` | Edit NLSC Topic / Competency Area |
| `delete_nlsc_topic` | Delete NLSC Topic / Competency Area |
| `view_nlsc_achievements` | View NLSC Subject Achievements |
| `add_nlsc_achievement` | Add NLSC Subject Achievement |
| `edit_nlsc_achievement` | Edit NLSC Subject Achievement |
| `delete_nlsc_achievement` | Delete NLSC Subject Achievement |

## Teachers  (`teachers`)

Teacher records, accounts, roles and bulk import.

| Feature key | Meaning |
|---|---|
| `view_teachers` | View Teachers & Profiles |
| `add_teacher` | Add Teacher |
| `import_teachers` | Bulk Import Teachers |
| `edit_teacher` | Edit Teacher |
| `change_teacher_role` | Change Teacher Role |
| `delete_teacher` | Delete Teacher |
| `manage_teacher_status` | Manage Teacher Status |

## Students  (`students`)

Student records, transfers, bulk import/export and duplicate consolidation.

| Feature key | Meaning |
|---|---|
| `view_students` | View & Search Students |
| `view_student_details` | View Student Details |
| `add_student` | Add Student |
| `edit_student` | Edit Student |
| `transfer_student` | Transfer Student (Class / Stream) |
| `delete_student` | Delete Student |
| `import_students` | Bulk Import Students |
| `import_student_photos` | Bulk Import Student Photos |
| `export_students` | Export Students |
| `view_student_consolidation` | View Student Consolidation |
| `manage_student_consolidation` | Link / Unlink / Dismiss Duplicates |
| `delete_duplicate_students` | Delete Duplicate Students |

## Card Scan  (`card_scan`)

Card scanning hub and arrival attendance logging.

| Feature key | Meaning |
|---|---|
| `view_hub` | View Scan Hub |
| `scan_cards` | Scan Cards |
| `view_scan_logs` | View Scan Logs |
| `manage_arrival_attendance` | Record Arrival Attendance |
| `view_arrival_reports` | View Arrival Reports |

## Student ID Cards  (`student_id_cards`)

Student ID card issuance and verification.

| Feature key | Meaning |
|---|---|
| `view_cards` | View Student ID Cards |
| `generate_cards` | Generate Student ID Cards |
| `print_cards` | Print Student ID Cards |
| `verify_cards` | Verify Student ID Cards |
| `revoke_cards` | Revoke Student ID Cards |
| `reactivate_cards` | Reactivate Student ID Cards |

## Teacher ID Cards  (`teacher_id_cards`)

Teacher ID card issuance and verification.

| Feature key | Meaning |
|---|---|
| `view_teacher_cards` | View Teacher ID Cards |
| `generate_teacher_cards` | Generate Teacher ID Cards |
| `print_teacher_cards` | Print Teacher ID Cards |
| `verify_teacher_cards` | Verify Teacher ID Cards |
| `revoke_teacher_cards` | Revoke Teacher ID Cards |
| `reactivate_teacher_cards` | Reactivate Teacher ID Cards |

## Library  (`library`)

Library catalogue, borrowing, members, fines and reports.

| Feature key | Meaning |
|---|---|
| `view_library_dashboard` | View Library Dashboard |
| `view_books` | View Books |
| `add_book` | Add Book |
| `edit_book` | Edit Book |
| `delete_book` | Delete Book |
| `import_books` | Import Books |
| `export_books` | Export Books |
| `download_ebook` | Download E-Books |
| `browse_catalogue` | Browse Catalogue |
| `view_my_borrowings` | View My Borrowings |
| `view_book_categories` | View Book Categories |
| `manage_book_categories` | Add / Edit / Delete Book Categories |
| `view_book_authors` | View Book Authors |
| `manage_book_authors` | Add / Edit / Delete Book Authors |
| `view_book_subjects` | View Book Subjects |
| `manage_book_subjects` | Add / Edit / Delete Book Subjects |
| `view_members` | View Library Members |
| `add_member` | Add Library Member |
| `edit_member` | Edit Library Member |
| `delete_member` | Delete Library Member |
| `view_borrowings` | View Borrowings |
| `borrow_book` | Issue Book to Member |
| `return_book` | Return Book |
| `renew_borrowing` | Renew Borrowing |
| `mark_book_lost` | Mark Book Lost |
| `view_reservations` | View Reservations |
| `manage_reservations` | Create / Update Reservations |
| `view_fines` | View Fines |
| `collect_fines` | Collect Fine Payment |
| `waive_fines` | Waive Fines |
| `view_book_requests` | View Book Requests |
| `create_book_request` | Submit Book Request |
| `review_book_request` | Review Book Request |
| `library_reports` | View Library Reports |
| `view_library_settings` | View Library Settings |
| `manage_library_settings` | Update Library Settings |

## Finance  (`finance`)

Fees, payments, expenses, payroll, budgets, ledgers and financial reports.

| Feature key | Meaning |
|---|---|
| `view_finance` | View Finance Dashboard |
| `view_fee_structures` | View Fee Structures |
| `add_fee_structure` | Add Fee Structure |
| `edit_fee_structure` | Edit Fee Structure |
| `delete_fee_structure` | Delete Fee Structure |
| `view_fee_categories` | View Fee Categories |
| `manage_fee_categories` | Add / Edit / Delete Fee Categories |
| `view_fee_allocations` | View Fee Allocations |
| `allocate_fees` | Allocate Fees to Students |
| `edit_fee_allocation` | Edit Fee Allocation |
| `delete_fee_allocation` | Delete Fee Allocation |
| `recalculate_balances` | Recalculate Student Balances |
| `view_payments` | View Payments |
| `record_payment` | Record Fee Payment |
| `reverse_payment` | Reverse Payment |
| `print_receipt` | Print Payment Receipt |
| `view_expenses` | View Expenses |
| `add_expense` | Add Expense |
| `edit_expense` | Edit Expense |
| `delete_expense` | Delete Expense |
| `view_expense_categories` | View Expense Categories |
| `manage_expense_categories` | Add / Edit / Delete Expense Categories |
| `view_payroll` | View Payroll & Payslips |
| `create_payroll_period` | Create Payroll Period |
| `approve_payroll` | Approve Payroll |
| `mark_payroll_paid` | Mark Payroll as Paid |
| `view_salary_structures` | View Salary Structures |
| `manage_salary_structures` | Manage Salary Structures |
| `view_budgets` | View Budgets |
| `add_budget` | Add Budget |
| `edit_budget` | Edit Budget |
| `approve_budget` | Approve Budget |
| `financial_reports` | View Financial Reports |
| `export_financial_reports` | Export Financial Reports |
| `view_outstanding_fees` | View Outstanding Fees |
| `export_outstanding_fees` | Export Outstanding Fees |
| `view_chart_of_accounts` | View Chart of Accounts |
| `manage_chart_of_accounts` | Manage Chart of Accounts |
| `view_general_ledger` | View General Ledger |
| `view_student_fee_ledger` | View Student Fee Ledger |
| `view_trial_balance` | View Trial Balance |

## Attendance  (`attendance`)

Student and staff attendance tracking and reports.

| Feature key | Meaning |
|---|---|
| `view_attendance` | View Attendance Dashboard |
| `view_student_attendance` | View Student Attendance |
| `mark_student_attendance` | Mark Student Attendance |
| `view_student_attendance_report` | View Student Attendance Report |
| `view_teacher_attendance` | View Staff Attendance |
| `mark_teacher_attendance` | Mark Staff Attendance |
| `view_teacher_attendance_report` | View Staff Attendance Report |
| `view_class_attendance_summary` | View Class Attendance Summary |

## Timetable  (`timetable`)

Periods, class timetables, master timetable and teacher schedules.

| Feature key | Meaning |
|---|---|
| `view_timetable` | View Timetables |
| `view_master_timetable` | View Master Timetable |
| `view_teachers_summary` | View Teachers Workload Summary |
| `view_teacher_schedule` | View My Teaching Schedule |
| `view_periods` | View Periods |
| `manage_periods` | Add / Edit / Delete Periods |
| `create_timetable` | Create Timetable |
| `duplicate_timetable` | Duplicate Timetable |
| `edit_timetable` | Edit Timetable & Slots |
| `change_timetable_status` | Change Timetable Status |
| `delete_timetable` | Delete Timetable |

## Examinations  (`examinations`)

Examinations, subjects, marks entry, grading setup, remarks, results and NLSC assessments.

| Feature key | Meaning |
|---|---|
| `view_exams` | View Examinations |
| `create_exam` | Create Examination |
| `edit_exam` | Edit Examination Details |
| `change_exam_status` | Change Examination Status |
| `delete_exam` | Delete Examination |
| `manage_exam_subjects` | Select Examination Subjects |
| `view_marks_entry` | Open Marks Entry |
| `enter_marks` | Enter / Save Marks |
| `view_discipline` | View Discipline Ratings |
| `enter_discipline_ratings` | Enter Discipline Ratings |
| `manage_discipline_criteria` | Manage Discipline Criteria |
| `view_remarks` | View Remarks Entry |
| `enter_remarks` | Enter Remarks |
| `publish_results` | Release / Publish Results |
| `view_grading_schemes` | View Grading Schemes |
| `manage_grading_schemes` | Add / Edit / Delete Grading Schemes |
| `view_assessment_scales` | View Assessment Scales |
| `manage_assessment_scales` | Add / Edit / Delete Assessment Scales |
| `assign_assessment_scales` | Assign Assessment Scales to Subjects |
| `view_aggregate_subjects` | View Aggregate Subjects |
| `manage_aggregate_subjects` | Manage Aggregate Subjects |
| `view_nlsc_assessments` | View NLSC Assessments |
| `manage_nlsc_assessments` | Add / Edit NLSC Assessments |
| `delete_nlsc_assessments` | Delete NLSC Assessments |
| `enter_nlsc_marks` | Enter NLSC Assessment Marks |
| `view_report_cards` | View & Print Report Cards / Passlips |
| `customize_report_cards` | Customize Report Card Layout |
| `manage_report_card_settings` | Save / Copy / Delete Report Card Settings |
| `view_exam_reports` | View Examination Reports & Analysis |
| `export_exam_reports` | Export Examination Reports (PDF / Excel) |
| `view_olevel_report_cards` | View O-Level Report Cards (Senior 1-4) |
| `create_olevel_report_card` | Create O-Level Report Card |
| `edit_olevel_report_card` | Edit O-Level Report Card |
| `delete_olevel_report_card` | Delete O-Level Report Card |

## Notifications  (`notifications`)

View and broadcast school notifications.

| Feature key | Meaning |
|---|---|
| `view_my_notifications` | View My Notifications |
| `mark_notification_read` | Mark Notification Read |
| `mark_all_read` | Mark All Notifications Read |
| `push_notifications` | Enable Push Notifications |
| `view_notifications` | View Sent Notifications |
| `create_notification` | Open Create-Notification Form |
| `send_notification` | Broadcast Notification |
| `delete_notification` | Delete Notification |

## Master Data  (`master_data`)

System master data, master codes, NLSC curriculum master and secondary subject lists.

| Feature key | Meaning |
|---|---|
| `view_master_data` | View Master Data |
| `create_master_data` | Create Master Data |
| `edit_master_data` | Edit Master Data |
| `delete_master_data` | Delete Master Data |
| `view_master_codes` | View Master Codes |
| `create_master_codes` | Create Master Codes |
| `edit_master_codes` | Edit Master Codes |
| `delete_master_codes` | Delete Master Codes |
| `view_nlsc_master` | View NLSC Master Curriculum |
| `add_nlsc_master` | Add NLSC Master Records |
| `import_nlsc_master` | Bulk Import NLSC Master Records |
| `edit_nlsc_master` | Edit NLSC Master Records |
| `delete_nlsc_master` | Delete NLSC Master Records |
| `view_secondary_subjects` | View Secondary A/O-Level Subject Lists |
| `add_secondary_subject` | Add Secondary Subject |
| `edit_secondary_subject` | Edit Secondary Subject |
| `delete_secondary_subject` | Delete Secondary Subject |

## User Rights & Permissions  (`user_rights`)

Roles, module/feature permissions and staff role assignment.

| Feature key | Meaning |
|---|---|
| `view_urp_dashboard` | View User Rights Dashboard |
| `view_roles` | View Roles |
| `create_role` | Create Role |
| `edit_role` | Edit Role |
| `delete_role` | Delete Role |
| `view_permissions` | View Permissions |
| `assign_permissions` | Assign Permissions |
| `view_role_assignments` | View Staff Role Assignments |
| `assign_roles_to_users` | Assign Roles to Staff |
| `remove_roles_from_users` | Remove Roles from Staff |

**15 modules, 237 features.**

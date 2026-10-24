<?php

/**
 * School Activity Monitor — tuning knobs.
 *
 * Activity is derived from the existing audit trail (audit_logs), so every
 * module that already writes to the database is covered automatically and no
 * module needs to be instrumented. Only actions performed by SCHOOL users
 * (teacher / student / parent sessions) count — a platform admin working
 * inside a school never makes that school look "active".
 */
return [

    // Days since the last school-user action, for a live school.
    'active_days' => (int) env('SCHOOL_ACTIVITY_ACTIVE_DAYS', 7),    // <= 7  => Active
    'idle_days' => (int) env('SCHOOL_ACTIVITY_IDLE_DAYS', 30),       // <= 30 => Idle, otherwise Dormant

    // A school registered less than this many days ago is "New" (onboarding),
    // not "Never used", until the grace period has passed.
    'new_school_grace_days' => (int) env('SCHOOL_ACTIVITY_GRACE_DAYS', 14),

    // Window used for per-module counts, active users and the daily trend.
    'window_days' => (int) env('SCHOOL_ACTIVITY_WINDOW_DAYS', 30),

    // Results are cached so the dashboard stays fast (seconds). 0 disables.
    'cache_seconds' => (int) env('SCHOOL_ACTIVITY_CACHE_SECONDS', 300),

    'per_page' => 25,

    // schools.school_status value meaning "live" (see BillingService::ACTIVE_SCHOOL_STATUS).
    'live_status' => 10,

    // Audit actor types that count as genuine school usage.
    'actor_types' => ['teacher', 'student', 'parent'],

    /*
    | Modules shown in the matrix. `entities` are the model class basenames
    | recorded in audit_logs.entity_type. Add a model here and it is counted.
    */
    'modules' => [
        'students' => [
            'label' => 'Students',
            'icon' => 'fa-user-graduate',
            'entities' => ['Student', 'StudentBasic', 'StudentIdCard', 'StudentALevelCombination', 'StudentOLevelElective', 'StudentDisciplineRating'],
        ],
        'staff' => [
            'label' => 'Staff',
            'icon' => 'fa-chalkboard-user',
            'entities' => ['Teacher', 'TeacherSchoolRole', 'TeacherIdCard', 'TeacherSalaryStructure'],
        ],
        'exams' => [
            'label' => 'Examinations',
            'icon' => 'fa-file-pen',
            'entities' => [
                'Examination', 'ExaminationClass', 'ExaminationMark', 'ExaminationSubjectSetting', 'Mark',
                'StudentResult', 'StudentExamResult', 'StudentExamSummary', 'AnnualExamination', 'CreatedExam',
                'OpenAssessment', 'OpenAssessmentMark', 'NlscAssessment', 'NlscAssessmentMark', 'ReportCardRemark',
            ],
        ],
        'attendance' => [
            'label' => 'Attendance',
            'icon' => 'fa-calendar-check',
            'entities' => ['StudentAttendance', 'TeacherAttendance', 'SchoolArrivalAttendance'],
        ],
        'finance' => [
            'label' => 'Finance',
            'icon' => 'fa-coins',
            'entities' => [
                'FeePayment', 'FeePaymentItem', 'FeeStructure', 'FeeStructureItem', 'StudentFeeAllocation',
                'FinanceTransaction', 'Expense', 'Budget', 'PayrollPeriod', 'PayrollSlip',
            ],
        ],
        'library' => [
            'label' => 'Library',
            'icon' => 'fa-book',
            'entities' => ['LibraryBook', 'LibraryBorrowing', 'LibraryMember', 'LibraryFine', 'LibraryReservation', 'LibraryBookRequest'],
        ],
        'timetable' => [
            'label' => 'Timetable',
            'icon' => 'fa-table-cells',
            'entities' => ['Timetable', 'TimetablePeriod', 'TimetableSlot'],
        ],
    ],
];

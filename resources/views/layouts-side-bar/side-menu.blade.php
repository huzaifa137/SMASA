<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>

<div class="app-sidebar app-sidebar2">
    <div class="app-sidebar__logo text-center">
        @if (Session('LoggedAdmin'))
            <a class="header-brand" href="{{ url('/admin/dashboard') }}">
                <img src="{{ URL::asset('assets/images/brand/uplogolight.png') }}" alt="SMASA" class="sidebar-logo">
            </a>
        @elseif(Session('LoggedSchool'))
            <a class="header-brand" href="{{ url('/school/dashboard') }}">
                <img src="{{ URL::asset('assets/images/brand/uplogolight.png') }}" alt="SMASA" class="sidebar-logo">
            </a>
        @endif
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<aside class="app-sidebar app-sidebar3">
    <ul class="side-menu" style="margin-top:100px !important;">

        {{-- ═══════════════════════════════════════════════════
        SECTION A: SUPER-ADMIN + SCHOOL (both sessions)
        Full access — no permission checks needed
        ════════════════════════════════════════════════════ --}}
        @if (Session('LoggedAdmin') && Session('LoggedSchool'))

            {{-- ── Resolve school status once ── --}}
            @php
                $__schoolStatus = \App\Http\Controllers\Helper::currentSchoolStatus();
                // 10 = Active | 1 = Pending Activation | null = unknown (treat as pending)
            @endphp

            {{-- ══════════════════════════════════════════════════════
            STATUS: PENDING ACTIVATION
            School exists but is not yet active — show only a
            "pending" notice. No modules accessible until active.
            ═══════════════════════════════════════════════════════ --}}
            @if($__schoolStatus !== 10)

                <li class="slide px-3 py-3">
                    <div
                        style="background:#fff7ed;border:1px dashed #f97316;border-radius:12px;padding:1rem 1rem;font-size:.8rem;color:#92400e;line-height:1.55;">
                        <div style="font-size:1.3rem;text-align:center;margin-bottom:.5rem;">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <div style="font-weight:700;font-size:.85rem;text-align:center;margin-bottom:.4rem;">
                            Pending Activation
                        </div>
                        Your school account is awaiting activation by the system administrator. Full access will be granted once
                        your account is active.
                    </div>
                </li>

                {{-- ══════════════════════════════════════════════════════
                STATUS: ACTIVE (school_status = 10)
                Show only what the logged-in user's role permits.
                ═══════════════════════════════════════════════════════ --}}
            @else

                {{-- Current Role Tag --}}
                @php $roleName = PermissionHelper::currentRoleName(); @endphp
                @if($roleName !== 'System Administrator')
                    <li class="slide px-3 pb-1" style="pointer-events:none;">
                        <div
                            style="font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;padding:.4rem .5rem .1rem;">
                            Role
                        </div>
                        <div
                            style="background:#eef2ff;color:#4f46e5;border-radius:8px;padding:.3rem .75rem;font-size:.78rem;font-weight:600;">
                            <i class="fa fa-crown mr-1"></i>{{ $roleName }}
                        </div>
                    </li>
                @endif

                {{-- Dashboard — always visible --}}
                <li class="slide">
                    <a class="side-menu__item" href="{{ url('/school/dashboard') }}">
                        <i class="fa fa-home fa-2x mr-3"></i>Dashboard
                    </a>
                </li>

                {{-- Classes --}}
                @if(PermissionHelper::canModule('classes'))
                    <li class="slide">
                        <a class="side-menu__item" href="{{ route('all.my-classes') }}">
                            <i class="fa fa-chalkboard-teacher fa-2x mr-3"></i>Classes
                        </a>
                    </li>
                @endif

                {{-- Teachers --}}
                @if(PermissionHelper::canModule('teachers'))
                    <li class="slide">
                        <a class="side-menu__item" href="{{ route('school.teachers') }}">
                            <i class="fa fa-user-tie fa-2x mr-3"></i>Teachers
                        </a>
                    </li>
                @endif

                {{-- Students --}}
                @if(PermissionHelper::canModule('students'))
                    <li class="slide">
                        <a class="side-menu__item" href="{{ route('students.individual.search') }}">
                            <i class="fa fa-user-graduate fa-2x mr-3"></i>Students
                        </a>
                    </li>
                @endif

                {{-- ID Cards --}}
                @if(PermissionHelper::canModule('card_scan') || PermissionHelper::canModule('student_id_cards') || PermissionHelper::canModule('teacher_id_cards'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-id-card fa-2x mr-3"></i>
                            <span>ID Cards</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canModule('card_scan'))
                                @if(PermissionHelper::canFeature('view_hub'))
                                    <li><a href="{{ route('card-scan.hub') }}"><i class="fas fa-barcode mr-2"></i>QR Scanner</a></li>
                                @endif
                                @if(PermissionHelper::canFeature('manage_arrival_attendance'))
                                    <li><a href="{{ url('/card-scan/arrival') }}"><i class="fas fa-user-check mr-2"></i>School Arrival</a>
                                    </li>
                                @endif
                                @if(PermissionHelper::canFeature('view_arrival_reports'))
                                    <li><a href="{{ url('/card-scan/arrival/report') }}"><i class="fas fa-chart-line mr-2"></i>Arrival
                                            Report</a></li>
                                @endif
                            @endif
                            @if(PermissionHelper::canModule('student_id_cards'))
                                @if(PermissionHelper::canFeature('view_cards'))
                                    <li><a href="{{ route('id-cards.index') }}"><i class="fas fa-id-badge mr-2"></i>Student ID Cards</a>
                                    </li>
                                @endif
                                @if(PermissionHelper::canFeature('generate_cards'))
                                    <li><a href="{{ route('id-cards.create') }}"><i class="fas fa-user-plus mr-2"></i>Create Student ID</a>
                                    </li>
                                @endif
                                @if(PermissionHelper::canFeature('verify_cards'))
                                    <li><a href="{{ route('id-cards.scanner') }}"><i class="fas fa-qrcode mr-2"></i>Student QR Verifier</a>
                                    </li>
                                @endif
                            @endif
                            @if(PermissionHelper::canModule('teacher_id_cards'))
                                @if(PermissionHelper::canFeature('view_teacher_cards'))
                                    <li><a href="{{ route('teacher-id-cards.index') }}"><i class="fas fa-id-badge mr-2"></i>Teacher ID
                                            Cards</a></li>
                                @endif
                                @if(PermissionHelper::canFeature('generate_teacher_cards'))
                                    <li><a href="{{ route('teacher-id-cards.create') }}"><i
                                                class="fas fa-chalkboard-teacher mr-2"></i>Create Teacher ID</a></li>
                                @endif
                                @if(PermissionHelper::canFeature('verify_teacher_cards'))
                                    <li><a href="{{ route('teacher-id-cards.scanner') }}"><i class="fas fa-qrcode mr-2"></i>Teacher QR
                                            Verifier</a></li>
                                @endif
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Library --}}
                @if(PermissionHelper::canModule('library'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-landmark fa-2x mr-3"></i>
                            <span>Library</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_library_dashboard'))
                                <li><a href="{{ route('library.dashboard') }}"><i class="fas fa-chart-bar mr-2"></i>Library
                                        Dashboard</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.catalogue') }}"><i class="fas fa-book-reader mr-2"></i>Catalogue</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.authors') }}"><i class="fas fa-user-edit mr-2"></i>Authors</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.categories') }}"><i class="fas fa-tags mr-2"></i>Categories</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.subjects') }}"><i class="fas fa-book mr-2"></i>Subjects</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.books') }}"><i class="fas fa-book-open mr-2"></i>Books</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_members'))
                                <li><a href="{{ route('library.members') }}"><i class="fas fa-users mr-2"></i>Members</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_borrowing'))
                                <li><a href="{{ route('library.borrowings') }}"><i class="fas fa-exchange-alt mr-2"></i>Borrowings</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_borrowing'))
                                <li><a href="{{ route('library.reservations') }}"><i
                                            class="fas fa-calendar-check mr-2"></i>Reservations</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_borrowing'))
                                <li><a href="{{ route('library.book-requests') }}"><i class="fas fa-file-signature mr-2"></i>Book
                                        Requests</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_borrowing'))
                                <li><a href="{{ route('library.fines') }}"><i class="fas fa-money-bill-wave mr-2"></i>Fines</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('library_reports'))
                                <li><a href="{{ route('library.reports') }}"><i class="fas fa-chart-line mr-2"></i>Reports</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_settings'))
                                <li><a href="{{ route('library.settings') }}"><i class="fas fa-cog mr-2"></i>Settings</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Finance --}}
                @if(PermissionHelper::canModule('finance'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-wallet fa-2x mr-3"></i>
                            <span>Finance</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_finance'))
                                <li><a href="{{ route('finance.dashboard') }}"><i class="fas fa-sack-dollar mr-2"></i>Finance
                                        Dashboard</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_fees'))
                                <li><a href="{{ route('finance.fee-structures.index') }}"><i class="fas fa-money-check-alt mr-2"></i>Fee
                                        Structure</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_fees'))
                                <li><a href="{{ route('finance.fee-allocations') }}"><i class="fas fa-layer-group mr-2"></i>Fee
                                        Allocations</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('record_payment'))
                                <li><a href="{{ route('finance.payments.create') }}"><i class="fas fa-hand-holding-usd mr-2"></i>Fee
                                        Payment</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('record_payment'))
                                <li><a href="{{ route('finance.payments.index') }}"><i class="fas fa-receipt mr-2"></i>Payments</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_expenses'))
                                <li><a href="{{ route('finance.expenses.index') }}"><i
                                            class="fas fa-money-bill-transfer mr-2"></i>Expenses</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_expenses'))
                                <li><a href="{{ route('finance.expense-categories.index') }}"><i class="fas fa-tags mr-2"></i>Expense
                                        Categories</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_payroll'))
                                <li><a href="{{ route('finance.payroll.index') }}"><i class="fas fa-chart-line mr-2"></i>Payroll</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_payroll'))
                                <li><a href="{{ route('finance.salary-structures') }}"><i class="fas fa-clipboard-list mr-2"></i>Salary
                                        Structures</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.budgets.index') }}"><i class="fas fa-scale-balanced mr-2"></i>Budget</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.reports') }}"><i class="fas fa-chart-pie mr-2"></i></i>Reports</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.outstanding-fees') }}"><i
                                            class="fas fa-hourglass-half mr-2"></i>Outstanding Fees</a></li>
                            @endif
                            <!-- @if(PermissionHelper::canFeature('manage_ledger'))
                                <li><a href="{{ route('finance.ledger.accounts.index') }}"><i class="fas fa-book mr-2"></i>Chart of
                                        Accounts</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.ledger.general') }}"><i class="fas fa-book-open mr-2"></i>General
                                        Ledger</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.ledger.student-fees') }}"><i
                                            class="fas fa-user-graduate mr-2"></i>Student Fee Ledger</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.ledger.trial-balance') }}"><i class="fas fa-balance-scale mr-2"></i>Trial
                                        Balance</a></li>
                            @endif -->
                        </ul>
                    </li>
                @endif

                {{-- Attendance --}}
                @if(PermissionHelper::canModule('attendance'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-user-check fa-2x mr-3"></i>
                            <span>Attendance</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_attendance'))
                                <li><a href="{{ route('attendance.dashboard') }}"><i class="fas fa-chart-line mr-2"></i>Dashboard</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('mark_attendance'))
                                <li><a href="{{ route('attendance.students') }}"><i class="fas fa-user-graduate mr-2"></i>Student
                                        Check-In</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('attendance_reports'))
                                <li><a href="{{ route('attendance.students.report') }}"><i class="fas fa-file-alt mr-2"></i>Student
                                        Report</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('mark_attendance'))
                                <li><a href="{{ route('attendance.teachers') }}"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher
                                        Check-In</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('attendance_reports'))
                                <li><a href="{{ route('attendance.teachers.report') }}"><i
                                            class="fas fa-file-signature mr-2"></i>Teachers Report</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Timetable --}}
                @if(PermissionHelper::canModule('timetable'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-calendar-alt fa-2x mr-3"></i>
                            <span>Timetable</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_timetable'))
                                <li><a href="{{ route('timetable.dashboard') }}"><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('view_timetable'))
                                <li><a href="{{ route('timetable.periods.index') }}"><i class="fas fa-clock mr-2"></i>Periods</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_timetable'))
                                <li><a href="{{ route('timetable.master') }}"><i class="fas fa-th-large mr-2"></i>General Timetable</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_timetable'))
                                <li><a href="{{ route('timetable.teachers-summary') }}"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher Teaching Days</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('create_timetable'))
                                <li><a href="{{ route('timetable.create') }}"><i class="fas fa-calendar-plus mr-2"></i>Create
                                        Timetable</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Examinations --}}
                @if(PermissionHelper::canModule('examinations'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-layer-group fa-2x mr-3"></i>
                            <span>Examinations</span>
                            @php
                                $pendingMarksCountRaw = Helper::getHelperMarksEntryProgress();
                                $pendingMarksCount = is_array($pendingMarksCountRaw) ? count($pendingMarksCountRaw) : (int) $pendingMarksCountRaw;
                            @endphp
                            @if ($pendingMarksCount > 0)
                                <span class="badge badge-danger ml-2">{{ $pendingMarksCount }}</span>
                            @endif
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_exams'))
                                <li><a href="{{ route('examination.index') }}"><i class="fas fa-list mr-2"></i>All Examinations</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('create_exam'))
                                <li><a href="{{ route('examination.create') }}"><i class="fas fa-plus-circle mr-2"></i>Create
                                        Examination</a></li>
                            @endif
                            @if ($pendingMarksCount > 0 && PermissionHelper::canFeature('view_exams'))
                                <li>
                                    <a href="{{ route('examination.marks-entry-portal') }}">
                                        <i class="fas fa-pen-to-square mr-2"></i>Marks Entry
                                        <span class="badge badge-danger float-right">{{ $pendingMarksCount }}</span>
                                    </a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('generate_reports'))
                                <li><a href="{{ route('examination.reports.index') }}"><i class="fas fa-chart-column mr-2"></i>Reports &amp; Summaries</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Notifications --}}
                @if(PermissionHelper::canModule('notifications'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-bell fa-2x mr-3"></i>
                            <span>Notifications</span>
                            @php
                                $unreadCount = 0;
                            @endphp
                            @if($unreadCount > 0)
                                <span class="badge badge-danger ml-2">{{ $unreadCount }}</span>
                            @endif
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_my_notifications'))
                                <li><a href="{{ route('notifications.my') }}"><i class="fas fa-envelope-open-text mr-2"></i>My
                                        Notifications</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_notifications'))
                                <li><a href="{{ route('notifications.index') }}"><i class="fas fa-cogs mr-2"></i>Manage
                                        Notifications</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('create_notification'))
                                <li><a href="{{ route('notifications.create') }}"><i class="fas fa-paper-plane mr-2"></i>Broadcast</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- User Rights --}}
                @if(PermissionHelper::canModule('user_rights'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-shield-alt fa-2x mr-3"></i>
                            <span>User Rights</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_dashboard'))
                                <li><a href="{{ route('urp.dashboard') }}"><i class="fas fa-tachometer-alt mr-2"></i>Overview</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_roles'))
                                <li><a href="{{ route('urp.roles.index') }}"><i class="fas fa-user-tag mr-2"></i>Manage Roles</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_permissions'))
                                <li><a href="{{ route('urp.permissions.index') }}"><i class="fas fa-sliders-h mr-2"></i>Module
                                        Permissions</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_roles'))
                                <li><a href="{{ route('urp.assign.index') }}"><i class="fas fa-users-cog mr-2"></i>Assign to Staff</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

            @endif {{-- End of ACTIVE school status --}}

            {{-- ═══════════════════════════════════════════════════
            SECTION B: SYSTEM ADMIN ONLY (no school selected)
            ════════════════════════════════════════════════════ --}}
        @elseif(Session('LoggedAdmin'))

            <li class="slide">
                <a class="side-menu__item" href="{{ url('/admin/dashboard') }}">
                    <i class="fa fa-home fa-2x mr-3"></i>Dashboard
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('school.allSchools') }}">
                    <i class="fa fa-school fa-2x mr-3"></i>Schools
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ url('/user-rights-and-previledges/setup') }}">
                    <i class="fas fa-user-shield fa-2x mr-3"></i>Rights &amp; Privileges
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('urp.admin.index') }}">
                    <i class="fas fa-sitemap fa-2x mr-3"></i>Schools &amp; Roles
                </a>
            </li>

            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-database fa-2x mr-3"></i>
                    <span>Master Data</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>
                <ul class="sub-menu">
                    @if(PermissionHelper::canFeature('view_master_codes'))
                        <li><a href="{{ route('master-code') }}"><i class="fas fa-code mr-2"></i>Master Data</a></li>
                    @endif
                    @if(PermissionHelper::canFeature('view_master_data'))
                        <li><a href="{{ route('master-code-to-data') }}"><i class="fas fa-list mr-2"></i>Master Codes</a></li>
                    @endif
                </ul>
            </li>

            {{-- ═══════════════════════════════════════════════════
            SECTION C: SCHOOL (TEACHER) LOGIN
            All items are permission-guarded via PermissionHelper
            ════════════════════════════════════════════════════ --}}
        @elseif(Session('LoggedSchool'))

            {{-- ── Resolve school status once ── --}}
            @php
                $__schoolStatus = \App\Http\Controllers\Helper::currentSchoolStatus();
                // 10 = Active | 1 = Pending Activation | null = unknown (treat as pending)
            @endphp

            {{-- ══════════════════════════════════════════════════════
            STATUS: PENDING ACTIVATION
            School exists but is not yet active — show only a
            "pending" notice. No modules accessible until active.
            ═══════════════════════════════════════════════════════ --}}
            @if($__schoolStatus !== 10)

                <li class="slide px-3 py-3">
                    <div
                        style="background:#fff7ed;border:1px dashed #f97316;border-radius:12px;padding:1rem 1rem;font-size:.8rem;color:#92400e;line-height:1.55;">
                        <div style="font-size:1.3rem;text-align:center;margin-bottom:.5rem;">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <div style="font-weight:700;font-size:.85rem;text-align:center;margin-bottom:.4rem;">
                            Pending Activation
                        </div>
                        Your school account is awaiting activation by the system administrator. Full access will be granted once
                        your account is active.
                    </div>
                </li>

                {{-- ══════════════════════════════════════════════════════
                STATUS: ACTIVE (school_status = 10)
                Show only what the logged-in user's role permits.
                ═══════════════════════════════════════════════════════ --}}
            @else

                {{-- Current Role Tag --}}
                @php $roleName = PermissionHelper::currentRoleName(); @endphp
                @if($roleName !== 'System Administrator')
                    <li class="slide px-3 pb-1" style="pointer-events:none;">
                        <div
                            style="font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;padding:.4rem .5rem .1rem;">
                            Role
                        </div>
                        <div
                            style="background:#eef2ff;color:#4f46e5;border-radius:8px;padding:.3rem .75rem;font-size:.78rem;font-weight:600;">
                            <i class="fa fa-crown mr-1"></i>{{ $roleName }}
                        </div>
                    </li>
                @endif

                {{-- Dashboard — always visible --}}
                <li class="slide">
                    <a class="side-menu__item" href="{{ url('/school/dashboard') }}">
                        <i class="fa fa-home fa-2x mr-3"></i>Dashboard
                    </a>
                </li>

                {{-- Classes --}}
                @if(PermissionHelper::canModule('classes'))
                    <li class="slide">
                        <a class="side-menu__item" href="{{ route('all.my-classes') }}">
                            <i class="fa fa-chalkboard-teacher fa-2x mr-3"></i>Classes
                        </a>
                    </li>
                @endif

                {{-- Teachers --}}
                @if(PermissionHelper::canModule('teachers'))
                    <li class="slide">
                        <a class="side-menu__item" href="{{ route('school.teachers') }}">
                            <i class="fa fa-user-tie fa-2x mr-3"></i>Teachers
                        </a>
                    </li>
                @endif

                {{-- Students --}}
                @if(PermissionHelper::canModule('students'))
                    <li class="slide">
                        <a class="side-menu__item" href="{{ route('students.individual.search') }}">
                            <i class="fa fa-user-graduate fa-2x mr-3"></i>Students
                        </a>
                    </li>
                @endif

                {{-- ID Cards --}}
                @if(PermissionHelper::canModule('card_scan') || PermissionHelper::canModule('student_id_cards') || PermissionHelper::canModule('teacher_id_cards'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-id-card fa-2x mr-3"></i>
                            <span>ID Cards</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canModule('card_scan'))
                                @if(PermissionHelper::canFeature('view_hub'))
                                    <li><a href="{{ route('card-scan.hub') }}"><i class="fas fa-barcode mr-2"></i>QR Scanner</a></li>
                                @endif
                                @if(PermissionHelper::canFeature('manage_arrival_attendance'))
                                    <li><a href="{{ url('/card-scan/arrival') }}"><i class="fas fa-user-check mr-2"></i>School Arrival</a>
                                    </li>
                                @endif
                                @if(PermissionHelper::canFeature('view_arrival_reports'))
                                    <li><a href="{{ url('/card-scan/arrival/report') }}"><i class="fas fa-chart-line mr-2"></i>Arrival
                                            Report</a></li>
                                @endif
                            @endif
                            @if(PermissionHelper::canModule('student_id_cards'))
                                @if(PermissionHelper::canFeature('view_cards'))
                                    <li><a href="{{ route('id-cards.index') }}"><i class="fas fa-id-badge mr-2"></i>Student ID Cards</a>
                                    </li>
                                @endif
                                @if(PermissionHelper::canFeature('generate_cards'))
                                    <li><a href="{{ route('id-cards.create') }}"><i class="fas fa-user-plus mr-2"></i>Create Student ID</a>
                                    </li>
                                @endif
                                @if(PermissionHelper::canFeature('verify_cards'))
                                    <li><a href="{{ route('id-cards.scanner') }}"><i class="fas fa-qrcode mr-2"></i>Student QR Verifier</a>
                                    </li>
                                @endif
                            @endif
                            @if(PermissionHelper::canModule('teacher_id_cards'))
                                @if(PermissionHelper::canFeature('view_teacher_cards'))
                                    <li><a href="{{ route('teacher-id-cards.index') }}"><i class="fas fa-id-badge mr-2"></i>Teacher ID
                                            Cards</a></li>
                                @endif
                                @if(PermissionHelper::canFeature('generate_teacher_cards'))
                                    <li><a href="{{ route('teacher-id-cards.create') }}"><i
                                                class="fas fa-chalkboard-teacher mr-2"></i>Create Teacher ID</a></li>
                                @endif
                                @if(PermissionHelper::canFeature('verify_teacher_cards'))
                                    <li><a href="{{ route('teacher-id-cards.scanner') }}"><i class="fas fa-qrcode mr-2"></i>Teacher QR
                                            Verifier</a></li>
                                @endif
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Library --}}
                @if(PermissionHelper::canModule('library'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-landmark fa-2x mr-3"></i>
                            <span>Library</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_library_dashboard'))
                                <li><a href="{{ route('library.dashboard') }}"><i class="fas fa-chart-bar mr-2"></i>Library
                                        Dashboard</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.catalogue') }}"><i class="fas fa-book-reader mr-2"></i>Catalogue</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.authors') }}"><i class="fas fa-user-edit mr-2"></i>Authors</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.categories') }}"><i class="fas fa-tags mr-2"></i>Categories</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.subjects') }}"><i class="fas fa-book mr-2"></i>Subjects</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_books'))
                                <li><a href="{{ route('library.books') }}"><i class="fas fa-book-open mr-2"></i>Books</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_members'))
                                <li><a href="{{ route('library.members') }}"><i class="fas fa-users mr-2"></i>Members</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_borrowing'))
                                <li><a href="{{ route('library.borrowings') }}"><i class="fas fa-exchange-alt mr-2"></i>Borrowings</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_borrowing'))
                                <li><a href="{{ route('library.reservations') }}"><i
                                            class="fas fa-calendar-check mr-2"></i>Reservations</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_borrowing'))
                                <li><a href="{{ route('library.book-requests') }}"><i class="fas fa-file-signature mr-2"></i>Book
                                        Requests</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_borrowing'))
                                <li><a href="{{ route('library.fines') }}"><i class="fas fa-money-bill-wave mr-2"></i>Fines</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('library_reports'))
                                <li><a href="{{ route('library.reports') }}"><i class="fas fa-chart-line mr-2"></i>Reports</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_settings'))
                                <li><a href="{{ route('library.settings') }}"><i class="fas fa-cog mr-2"></i>Settings</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Finance --}}
                @if(PermissionHelper::canModule('finance'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-wallet fa-2x mr-3"></i>
                            <span>Finance</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_finance'))
                                <li><a href="{{ route('finance.dashboard') }}"><i class="fas fa-sack-dollar mr-2"></i>Finance
                                        Dashboard</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_fees'))
                                <li><a href="{{ route('finance.fee-structures.index') }}"><i class="fas fa-money-check-alt mr-2"></i>Fee
                                        Structure</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_fees'))
                                <li><a href="{{ route('finance.fee-allocations') }}"><i class="fas fa-layer-group mr-2"></i>Fee
                                        Allocations</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('record_payment'))
                                <li><a href="{{ route('finance.payments.create') }}"><i class="fas fa-hand-holding-usd mr-2"></i>Fee
                                        Payment</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('record_payment'))
                                <li><a href="{{ route('finance.payments.index') }}"><i class="fas fa-receipt mr-2"></i>Payments</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_expenses'))
                                <li><a href="{{ route('finance.expenses.index') }}"><i
                                            class="fas fa-money-bill-transfer mr-2"></i>Expenses</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_expenses'))
                                <li><a href="{{ route('finance.expense-categories.index') }}"><i class="fas fa-tags mr-2"></i>Expense
                                        Categories</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_payroll'))
                                <li><a href="{{ route('finance.payroll.index') }}"><i class="fas fa-chart-line mr-2"></i>Payroll</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('manage_payroll'))
                                <li><a href="{{ route('finance.salary-structures') }}"><i class="fas fa-clipboard-list mr-2"></i>Salary
                                        Structures</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.budgets.index') }}"><i class="fas fa-scale-balanced mr-2"></i>Budget</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.reports') }}"><i class="fas fa-chart-pie mr-2"></i></i>Reports</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                              <li>
    <a href="{{ route('finance.outstanding-fees') }}">
        <i class="fas fa-money-bill-wave mr-2"></i>
        Fees Collections
    </a>
</li>
                            @endif
                            <!-- @if(PermissionHelper::canFeature('manage_ledger'))
                                <li><a href="{{ route('finance.ledger.accounts.index') }}"><i class="fas fa-book mr-2"></i>Chart of
                                        Accounts</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.ledger.general') }}"><i class="fas fa-book-open mr-2"></i>General
                                        Ledger</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.ledger.student-fees') }}"><i
                                            class="fas fa-user-graduate mr-2"></i>Student Fee Ledger</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('financial_reports'))
                                <li><a href="{{ route('finance.ledger.trial-balance') }}"><i class="fas fa-balance-scale mr-2"></i>Trial
                                        Balance</a></li>
                            @endif -->
                        </ul>
                    </li>
                @endif

                {{-- Attendance --}}
                @if(PermissionHelper::canModule('attendance'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-user-check fa-2x mr-3"></i>
                            <span>Attendance</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_attendance'))
                                <li><a href="{{ route('attendance.dashboard') }}"><i class="fas fa-chart-line mr-2"></i>Dashboard</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('mark_attendance'))
                                <li><a href="{{ route('attendance.students') }}"><i class="fas fa-user-graduate mr-2"></i>Student
                                        Check-In</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('attendance_reports'))
                                <li><a href="{{ route('attendance.students.report') }}"><i class="fas fa-file-alt mr-2"></i>Student
                                        Report</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('mark_attendance'))
                                <li><a href="{{ route('attendance.teachers') }}"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher
                                        Check-In</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('attendance_reports'))
                                <li><a href="{{ route('attendance.teachers.report') }}"><i
                                            class="fas fa-file-signature mr-2"></i>Teachers Report</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Timetable --}}
                @if(PermissionHelper::canModule('timetable'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-calendar-alt fa-2x mr-3"></i>
                            <span>Timetable</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_timetable'))
                                <li><a href="{{ route('timetable.dashboard') }}"><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('view_timetable'))
                                <li><a href="{{ route('timetable.periods.index') }}"><i class="fas fa-clock mr-2"></i>Periods</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_timetable'))
                                <li><a href="{{ route('timetable.master') }}"><i class="fas fa-th-large mr-2"></i>General Timetable</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_timetable'))
                                <li><a href="{{ route('timetable.teachers-summary') }}"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher Teaching Days</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('create_timetable'))
                                <li><a href="{{ route('timetable.create') }}"><i class="fas fa-calendar-plus mr-2"></i>Create
                                        Timetable</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Examinations --}}
                @if(PermissionHelper::canModule('examinations'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-layer-group fa-2x mr-3"></i>
                            <span>Examinations</span>
                            @php
                                $pendingMarksCountRaw = Helper::getHelperMarksEntryProgress();
                                $pendingMarksCount = is_array($pendingMarksCountRaw) ? count($pendingMarksCountRaw) : (int) $pendingMarksCountRaw;
                            @endphp
                            @if ($pendingMarksCount > 0)
                                <span class="badge badge-danger ml-2">{{ $pendingMarksCount }}</span>
                            @endif
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_exams'))
                                <li><a href="{{ route('examination.index') }}"><i class="fas fa-list mr-2"></i>All Examinations</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('create_exam'))
                                <li><a href="{{ route('examination.create') }}"><i class="fas fa-plus-circle mr-2"></i>Create
                                        Examination</a>
                                </li>
                                <li>
                                    <a href="{{ route('examination.grading-schemes.index') }}">
                                        <i class="fas fa-sort-amount-up mr-2"></i>
                                        Grading Scales
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('examination.assessment-scales.index') }}">
                                        <i class="fas fa-ruler-combined mr-2"></i>Assessment Scales
                                    </a>
                                </li>
                            @endif
                            @if ($pendingMarksCount > 0 && PermissionHelper::canFeature('view_exams'))
                                <li>
                                    <a href="{{ route('examination.marks-entry-portal') }}">
                                        <i class="fas fa-pen-to-square mr-2"></i>Marks Entry &nbsp; &nbsp;
                                        <span class="badge badge-danger float-right">{{ $pendingMarksCount }}</span>
                                    </a>
                                </li>
                            @endif
                            @if(PermissionHelper::canFeature('generate_reports'))
                                <li>
                                    <a href="{{ route('examination.reports.index') }}">
                                        <i class="fas fa-chart-column mr-2"></i>Reports &amp; Summaries
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Notifications --}}
                @if(PermissionHelper::canModule('notifications'))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-bell fa-2x mr-3"></i>
                            <span>Notifications</span>
                            @php
                                $unreadCount = 0;
                            @endphp
                            @if($unreadCount > 0)
                                <span class="badge badge-danger ml-2">{{ $unreadCount }}</span>
                            @endif
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_my_notifications'))
                                <li><a href="{{ route('notifications.my') }}"><i class="fas fa-envelope-open-text mr-2"></i>My
                                        Notifications</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_notifications'))
                                <li><a href="{{ route('notifications.index') }}"><i class="fas fa-cogs mr-2"></i>Manage
                                        Notifications</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('create_notification'))
                                <li><a href="{{ route('notifications.create') }}"><i class="fas fa-paper-plane mr-2"></i>Broadcast</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- User Rights --}}
                {{-- Always shown on an active school — a freshly-added school needs this
                to configure roles before any other module becomes accessible. --}}
                @if(PermissionHelper::canModule('user_rights') || empty(PermissionHelper::accessibleModuleKeys()))
                    <li class="slide has-sub">
                        <a class="side-menu__item" href="#" data-toggle="submenu">
                            <i class="fas fa-shield-alt fa-2x mr-3"></i>
                            <span>User Rights</span>
                            <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                        </a>
                        <ul class="sub-menu">
                            @if(PermissionHelper::canFeature('view_dashboard'))
                                <li><a href="{{ route('urp.dashboard') }}"><i class="fas fa-tachometer-alt mr-2"></i>Overview</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_roles'))
                                <li><a href="{{ route('urp.roles.index') }}"><i class="fas fa-user-tag mr-2"></i>Manage Roles</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_permissions'))
                                <li><a href="{{ route('urp.permissions.index') }}"><i class="fas fa-sliders-h mr-2"></i>Module
                                        Permissions</a></li>
                            @endif
                            @if(PermissionHelper::canFeature('view_roles'))
                                <li><a href="{{ route('urp.assign.index') }}"><i class="fas fa-users-cog mr-2"></i>Assign to Staff</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @php
                    $accessible = PermissionHelper::accessibleModuleKeys();
                    $hasRole = PermissionHelper::getCurrentSchoolRole() !== null;
                @endphp
                {{-- Only show "no modules" warning when a role IS assigned but grants nothing --}}
                @if($hasRole && empty($accessible))
                    <li class="slide px-3 py-2">
                        <div
                            style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:.75rem 1rem;font-size:.78rem;color:#92400e;">
                            <i class="fa fa-lock mr-2"></i>
                            <strong>No modules assigned to your role.</strong><br>
                            Contact your school administrator.
                        </div>
                    </li>
                @endif

            @endif {{-- End of ACTIVE school status --}}

        @endif
        {{-- ── END SECTIONS ── --}}

        {{-- LOGOUT (always) --}}
        <li class="slide">
            <a class="side-menu__item" href="#" id="logoutMenu">
                <i class="fa fa-sign-out fa-2x mr-3"></i>Logout
            </a>
        </li>
    </ul>
</aside>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('[data-toggle="submenu"]').on('click', function (e) {
            e.preventDefault();
            var $slide = $(this).closest('.slide');
            $('.slide').not($slide).removeClass('active');
            $slide.toggleClass('active');
        });
    });

    document.getElementById('logoutMenu').addEventListener('click', function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Are you sure?",
            text: "Do you really want to Logout?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Logout",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("student-logout") }}';
            }
        });
    });

    // Scroll-aware logo shrink
    (function () {
        const sidebar = document.querySelector('.app-sidebar3');
        const logoWrapper = document.querySelector('.app-sidebar__logo');
        if (!sidebar || !logoWrapper) return;
        sidebar.addEventListener('scroll', () => {
            logoWrapper.classList.toggle('scrolled', sidebar.scrollTop > 20);
        });
    })();

    // ── Correct "current page" highlighting ──────────────────────────────
    // The bundled assets/plugins/sidemenu/sidemenu.js plugin was written for a
    // more deeply-nested menu structure. On this markup (<li class="slide"><a
    // class="side-menu__item">...) its `$(this).parent().prev()` call ends up
    // grabbing the PREVIOUS <li> sibling instead of the current one — e.g.
    // selecting "Students" highlights "Teachers". We clear whatever it set
    // and recompute against our actual markup, once everything has loaded.
    $(window).on('load', function () {
        var pageUrl = window.location.href.split(/[?#]/)[0];

        $('.app-sidebar3 .slide').removeClass('active is-expanded');

        $('.app-sidebar3 .side-menu__item, .app-sidebar3 .sub-menu a').each(function () {
            if (this.href === pageUrl) {
                $(this).closest('.slide').addClass('active');
                $(this).addClass('active');
            }
        });
    });
</script>

<style>
    /* ── Sidebar base ── */
    .side-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .slide {
        position: relative;
        margin-bottom: 4px;
    }

    .side-menu__item {
        display: flex;
        align-items: center;
        padding: 11px 20px;
        color: #333;
        text-decoration: none;
        transition: all .25s ease;
        border-radius: 8px;
        margin: 0 8px;
    }

    .side-menu__item:hover,
    .slide.active>.side-menu__item {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #fff !important;
        transform: translateX(4px);
    }

    .app-sidebar3 ul.side-menu li.slide.active>a.side-menu__item,
    .app-sidebar3 ul.side-menu li.slide.active>a.side-menu__item i,
    .app-sidebar3 ul.side-menu li.slide.active>a.side-menu__item span {
        color: #fff !important;
    }

    .side-menu__item i:first-child {
        width: 35px;
    }

    .dropdown-icon {
        transition: transform .25s;
        font-size: 12px;
    }

    .slide.active .dropdown-icon {
        transform: rotate(180deg);
    }

    /* Sub-menus */
    .sub-menu {
        display: none;
        list-style: none;
        padding: 6px 0 6px 44px;
        margin: 4px 0;
        background: rgba(79, 70, 229, .04);
        border-radius: 8px;
    }

    .slide.active>.sub-menu {
        display: block;
        animation: fadeIn .25s ease;
    }

    .sub-menu li {
        margin: 3px 0;
    }

    .sub-menu li a {
        display: flex;
        align-items: center;
        padding: 7px 14px;
        color: #555;
        text-decoration: none;
        border-radius: 6px;
        transition: all .2s;
        font-size: 13.5px;
    }

    .sub-menu li a:hover {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #fff !important;
        transform: translateX(4px);
    }

    .sub-menu li a i {
        width: 24px;
        font-size: 13px;
    }

    /* Badges */
    .badge-danger {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.18);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Logo */
    .app-sidebar__logo {
        padding: 20px 0 30px;
        text-align: center;
        border-bottom: 1px solid #eee;
        margin-bottom: 14px;
        transition: padding .3s;
    }

    .app-sidebar__logo.scrolled {
        padding: 10px 0 14px;
    }

    .sidebar-logo {
        width: 130px;
        height: 130px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #4f46e5;
        padding: 4px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
        transition: all .3s;
        display: block;
        margin: 0 auto;
    }

    .app-sidebar__logo.scrolled .sidebar-logo {
        width: 62px;
        height: 62px;
        border-width: 2px;
    }

    .sidenav-toggled .sidebar-logo {
        width: 40px !important;
        height: 40px !important;
        border-width: 2px !important;
    }

    .sidebar-logo:hover {
        transform: scale(1.04);
    }

    @media(max-width:768px) {
        div.app-sidebar.app-sidebar2 {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 250px !important;
            height: auto !important;
            z-index: 10001 !important;
            background: transparent !important;
            pointer-events: none !important;
            transform: translateX(-250px) !important;
            transition: transform .3s ease !important;
        }

        .sidenav-toggled div.app-sidebar.app-sidebar2 {
            transform: translateX(0) !important;
        }

        div.app-sidebar.app-sidebar2 .app-sidebar__logo {
            display: block !important;
            pointer-events: all !important;
            background: transparent !important;
            padding: 12px 0 14px !important;
        }

        div.app-sidebar.app-sidebar2 .sidebar-logo {
            width: 70px !important;
            height: 70px !important;
            display: block !important;
            margin: 0 auto !important;
        }
    }

    /* ── Perfect circle always ── */
    .app-sidebar3 .side-menu .badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 24px !important;
        height: 24px !important;
        padding: 0 !important;
        border-radius: 50% !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        line-height: 24px !important;
        text-align: center !important;
        background-color: #dc3545 !important;
        color: #fff !important;
        flex-shrink: 0 !important;
    }

    /* For 2-digit numbers - keep it a circle but slightly larger */
    .app-sidebar3 .side-menu .badge:not(:empty):not(:has(span)) {
        width: 30px !important;
        height: 30px !important;
        line-height: 30px !important;
        font-size: 12px !important;
    }
</style>
<div class="app-sidebar app-sidebar2">
    <div class="app-sidebar__logo text-center">

        @if (Session('LoggedAdmin'))
            <a class="header-brand" href="{{ url('/admin/dashboard') }}">
                <img src="{{ URL::asset('assets/images/brand/uplogolight.png') }}" alt="Covido logo" class="sidebar-logo">
            </a>

        @elseif(Session('LoggedSchool'))
            <a class="header-brand" href="{{ url('/school/dashboard') }}">
                <img src="{{ URL::asset('assets/images/brand/uplogolight.png') }}" alt="Covido logo" class="sidebar-logo">
            </a>
        @endif

    </div>
</div>

<?php
use App\Http\Controllers\Helper;
?>

<aside class="app-sidebar app-sidebar3">
    <ul class="side-menu" style="margin-top:100px !important;">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        @if (Session('LoggedAdmin') && Session('LoggedSchool'))
            <li class="slide">
                <a class="side-menu__item" href="{{ url('/admin/dashboard') }}">
                    <i class="fa fa-home fa-2x mr-3"></i>
                    Dashboard
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('school.allSchools') }}">
                    <i class="fa fa-school fa-2x mr-3"></i>
                    Schools
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('all.my-classes') }}">
                    <i class="fa fa-chalkboard-teacher fa-2x mr-3"></i>
                    Classes
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('school.teachers') }}">
                    <i class="fa fa-user-tie fa-2x mr-3"></i>
                    Teachers
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('students.individual.search') }}">
                    <i class="fa fa-user-graduate fa-2x mr-3"></i>
                    Students
                </a>
            </li>

                 <!-- Finance Dropdown -->
            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-wallet fa-2x mr-3"></i>
                    <span>Finance</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>

                <ul class="sub-menu">
                    <li>
                        <a href="{{ route('finance.dashboard') }}">
                            <i class="fas fa-sack-dollar mr-2"></i>
                            Finance Dashboard
                        </a>

                        <a href="{{ route('finance.fee-structures.index') }}">
                            <i class="fas fa-money-check-alt mr-2"></i>
                            Fee Structure
                        </a>

                        <a href="{{ route('finance.fee-allocations') }}">
                            <i class="fas fa-layer-group mr-2"></i>
                            Fee Allocations
                        </a>

                        <a href="{{ route('finance.payments.create') }}">
                            <i class="fas fa-hand-holding-usd mr-2"></i>
                            Fee Payment
                        </a>

                        <a href="{{ route('finance.payments.index') }}">
                            <i class="fas fa-receipt mr-2"></i>
                            Payments
                        </a>

                        <a href="{{ route('finance.expenses.index') }}">
                            <i class="fas fa-money-bill-transfer mr-2"></i>
                            Expenses Dashboard
                        </a>

                        <a href="{{ route('finance.expense-categories.index') }}">
                            <i class="fas fa-tags mr-2"></i>
                            Expense Categories
                        </a>

                        <a href="{{ route('finance.expenses.index') }}">
                            <i class="fas fa-circle-plus mr-2"></i>
                            Add Expense
                        </a>

                        <a href="{{ route('finance.payroll.index') }}">
                            <i class="fas fa-chart-line mr-2"></i>
                            Payroll Dashboard
                        </a>

                        <a href="{{ route('finance.salary-structures') }}">
                            <i class="fas fa-clipboard-list mr-2"></i>
                            Salary Structures
                        </a>

                        <a href="{{ route('finance.budgets.index') }}">
                            <i class="fas fa-scale-balanced mr-2"></i>
                            Budget Dashboard
                        </a>

                        <a href="{{ route('finance.reports') }}">
                            <i class="fas fa-file-chart-column mr-2"></i>
                            Reports
                        </a>

                        <a href="{{ route('finance.outstanding-fees') }}">
                            <i class="fas fa-hourglass-half mr-2"></i>
                            Outstanding Fees
                        </a>

                    </li>
                </ul>
            </li>


            <!-- Attendance Dropdown -->
            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-user-check fa-2x mr-3"></i>
                    <span>Attendance</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('attendance.dashboard') }}"><i class="fas fa-chart-line mr-2"></i>Attendance
                            Dashboard</a></li>
                    <li><a href="{{ route('attendance.students') }}"><i class="fas fa-user-graduate mr-2"></i>Student
                            Check-In</a></li>
                    <li><a href="{{ route('attendance.students.report') }}"><i class="fas fa-file-alt mr-2"></i>Student
                            Report</a></li>
                    <li><a href="{{ route('attendance.teachers') }}"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher
                            Check-In</a></li>
                    <li><a href="{{ route('attendance.teachers.report') }}"><i
                                class="fas fa-file-signature mr-2"></i>Teachers Report</a></li>
                </ul>
            </li>

            <!-- Timetable Dropdown -->
            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-calendar-alt fa-2x mr-3"></i>
                    <span>Timetable</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('timetable.dashboard') }}"><i class="fas fa-tachometer-alt mr-2"></i>Timetable
                            Dashboard</a></li>
                    <li><a href="{{ route('timetable.periods.index') }}"><i class="fas fa-clock mr-2"></i>Periods</a></li>
                    <li><a href="{{ route('timetable.create') }}"><i class="fas fa-calendar-plus mr-2"></i>Create
                            Timetable</a></li>
                    <li><a href="{{ route('attendance.teachers') }}"><i class="fas fa-chalkboard mr-2"></i>Teacher
                            Timetable</a></li>
                    <li><a href="{{ route('attendance.teachers.report') }}"><i class="fas fa-print mr-2"></i>Print
                            Timetables</a></li>
                </ul>
            </li>

            <!-- Examinations Dropdown -->
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
                    <li><a href="{{ route('examination.index') }}"><i class="fas fa-list mr-2"></i>All Examinations</a></li>
                    <li><a href="{{ route('examination.create') }}"><i class="fas fa-plus-circle mr-2"></i>Create
                            Examination</a></li>
                    @if ($pendingMarksCount > 0)
                        <li>
                            <a href="{{ route('examination.marks-entry-portal') }}">
                                <i class="fas fa-pen-to-square mr-2"></i>Marks Entry
                                <span class="badge badge-danger float-right">{{ $pendingMarksCount }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

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
                    <i class="fas fa-user-shield fa-2x mr-3"></i>Rights & Privileges
                </a>
            </li>

        @elseif(Session('LoggedSchool'))
            <li class="slide">
                <a class="side-menu__item" href="{{ url('/school/dashboard') }}">
                    <i class="fa fa-home fa-2x mr-3"></i>Dashboard
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('all.my-classes') }}">
                    <i class="fa fa-chalkboard-teacher fa-2x mr-3"></i>Classes
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('school.teachers') }}">
                    <i class="fa fa-user-tie fa-2x mr-3"></i>Teachers
                </a>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('students.individual.search') }}">
                    <i class="fa fa-user-graduate fa-2x mr-3"></i>Students
                </a>
            </li>

            <!-- Finance Dropdown -->
            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-wallet fa-2x mr-3"></i>
                    <span>Finance</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>

                <ul class="sub-menu">
                    <li>
                        <a href="{{ route('finance.dashboard') }}">
                            <i class="fas fa-sack-dollar mr-2"></i>
                            Finance Dashboard
                        </a>

                        <a href="{{ route('finance.fee-structures.index') }}">
                            <i class="fas fa-money-check-alt mr-2"></i>
                            Fee Structure
                        </a>

                        <a href="{{ route('finance.fee-allocations') }}">
                            <i class="fas fa-layer-group mr-2"></i>
                            Fee Allocations
                        </a>

                        <a href="{{ route('finance.payments.create') }}">
                            <i class="fas fa-hand-holding-usd mr-2"></i>
                            Fee Payment
                        </a>

                        <a href="{{ route('finance.payments.index') }}">
                            <i class="fas fa-receipt mr-2"></i>
                            Payments
                        </a>

                        <a href="{{ route('finance.expenses.index') }}">
                            <i class="fas fa-money-bill-transfer mr-2"></i>
                            Expenses Dashboard
                        </a>

                        <a href="{{ route('finance.expense-categories.index') }}">
                            <i class="fas fa-tags mr-2"></i>
                            Expense Categories
                        </a>

                        <a href="{{ route('finance.expenses.index') }}">
                            <i class="fas fa-circle-plus mr-2"></i>
                            Add Expense
                        </a>

                        <a href="{{ route('finance.payroll.index') }}">
                            <i class="fas fa-chart-line mr-2"></i>
                            Payroll Dashboard
                        </a>

                        <a href="{{ route('finance.salary-structures') }}">
                            <i class="fas fa-clipboard-list mr-2"></i>
                            Salary Structures
                        </a>

                        <a href="{{ route('finance.budgets.index') }}">
                            <i class="fas fa-scale-balanced mr-2"></i>
                            Budget Dashboard
                        </a>

                        <a href="{{ route('finance.reports') }}">
                            <i class="fas fa-file-chart-column mr-2"></i>
                            Reports
                        </a>

                        <a href="{{ route('finance.outstanding-fees') }}">
                            <i class="fas fa-hourglass-half mr-2"></i>
                            Outstanding Fees
                        </a>

                    </li>
                </ul>
            </li>

            <!-- Attendance Dropdown -->
            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-user-check fa-2x mr-3"></i>
                    <span>Attendance</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('attendance.dashboard') }}"><i class="fas fa-chart-line mr-2"></i>Attendance
                            Dashboard</a></li>
                    <li><a href="{{ route('attendance.students') }}"><i class="fas fa-user-graduate mr-2"></i>Student
                            Check-In</a></li>
                    <li><a href="{{ route('attendance.students.report') }}"><i class="fas fa-file-alt mr-2"></i>Student
                            Report</a></li>
                    <li><a href="{{ route('attendance.teachers') }}"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher
                            Check-In</a></li>
                    <li><a href="{{ route('attendance.teachers.report') }}"><i
                                class="fas fa-file-signature mr-2"></i>Teachers Report</a></li>
                </ul>
            </li>

            <!-- Timetable Dropdown -->
            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-calendar-alt fa-2x mr-3"></i>
                    <span>Timetable</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('timetable.dashboard') }}"><i class="fas fa-tachometer-alt mr-2"></i>Timetable
                            Dashboard</a></li>
                    <li><a href="{{ route('timetable.periods.index') }}"><i class="fas fa-clock mr-2"></i>Periods</a></li>
                    <li><a href="{{ route('timetable.create') }}"><i class="fas fa-calendar-plus mr-2"></i>Create
                            Timetable</a></li>
                </ul>
            </li>

            <!-- Examinations Dropdown -->
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
                    <li><a href="{{ route('examination.index') }}"><i class="fas fa-list mr-2"></i>All Examinations</a></li>
                    <li><a href="{{ route('examination.create') }}"><i class="fas fa-plus-circle mr-2"></i>Create
                            Examination</a></li>
                    @if ($pendingMarksCount > 0)
                        <li>
                            <a href="{{ route('examination.marks-entry-portal') }}">
                                <i class="fas fa-pen-to-square mr-2"></i>Marks Entry
                                <span class="badge badge-danger float-right">{{ $pendingMarksCount }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

        @endif

        <li class="slide">
            <a class="side-menu__item" href="#" id="logoutMenu">
                <i class="fa fa-sign-out fa-2x mr-3"></i>
                Logout
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

        $('#logoutMenu').on('click', function (event) {
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
                    window.location.href = '{{ route('student-logout') }}';
                }
            });
        });
    });
</script>

<style>
    .sub-menu {
        display: none;
        padding-left: 40px;
    }

    .slide.active>.sub-menu {
        display: block;
    }

    .has-sub>a {
        cursor: pointer;
    }

    .side-menu__item .badge,
    .sub-menu .badge {
        position: relative;
        top: -2px;
        font-size: 10px;
        padding: 3px 6px;
        border-radius: 10px;
        min-width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 0 2px white;
    }

    .side-menu__item .badge {
        margin-left: 5px;
    }

    .sub-menu .badge.float-right {
        margin-left: auto;
        margin-right: 10px;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }

        100% {
            transform: scale(1);
        }
    }

    .badge-danger {
        animation: pulse 2s infinite;
    }

    .side-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .slide {
        position: relative;
        margin-bottom: 5px;
    }

    .side-menu__item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 0 8px;
    }

    .side-menu__item:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        transform: translateX(5px);
    }

    .side-menu__item i:first-child {
        width: 35px;
    }

    .dropdown-icon {
        transition: transform 0.3s ease;
        font-size: 12px;
    }

    .slide.active .dropdown-icon {
        transform: rotate(180deg);
    }

    .sub-menu {
        display: none;
        list-style: none;
        padding: 8px 0 8px 45px;
        margin: 5px 0;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 8px;
    }

    .slide.active .sub-menu {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .sub-menu li {
        margin: 5px 0;
    }

    .sub-menu li a {
        display: flex;
        align-items: center;
        padding: 8px 15px;
        color: #555;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .sub-menu li a:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        transform: translateX(5px);
    }

    .sub-menu li a i {
        width: 25px;
        font-size: 14px;
    }

    .side-menu__item.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .has-sub:hover>.side-menu__item {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
    }

    .sub-menu li {
        animation: slideIn 0.3s ease backwards;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .sub-menu li:nth-child(1) {
        animation-delay: 0.05s;
    }

    .sub-menu li:nth-child(2) {
        animation-delay: 0.1s;
    }

    .sub-menu li:nth-child(3) {
        animation-delay: 0.15s;
    }

    .sub-menu li:nth-child(4) {
        animation-delay: 0.2s;
    }

    .sub-menu li:nth-child(5) {
        animation-delay: 0.25s;
    }

    @media (max-width: 768px) {
        .sub-menu {
            padding-left: 30px;
        }

        .side-menu__item {
            padding: 10px 15px;
        }
    }

    /* ── Sidebar Logo: large screens (theme handles positioning) ── */
    .app-sidebar__logo {
        padding: 20px 0 30px 0;
        text-align: center;
        border-bottom: 1px solid #eee;
        margin-bottom: 15px;
        transition: padding 0.3s ease;
    }

    .app-sidebar__logo.scrolled {
        padding: 10px 0 15px 0;
    }

    .app-sidebar__logo .header-brand {
        display: block !important;
        text-align: center;
    }

    .sidebar-logo {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 50% !important;
        border: 4px solid #667eea;
        padding: 5px;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        display: block !important;
        visibility: visible !important;
        margin: 0 auto;
    }

    .app-sidebar__logo.scrolled .sidebar-logo {
        width: 70px;
        height: 70px;
        border-width: 2px;
        padding: 3px;
    }

    /* Minimized sidebar on large screens */
    .sidenav-toggled .app-sidebar__logo {
        padding: 10px 0 !important;
    }

    .sidenav-toggled .sidebar-logo {
        width: 45px !important;
        height: 45px !important;
        border-width: 2px !important;
        padding: 3px !important;
        border-radius: 50% !important;
    }

    .sidebar-logo:hover {
        transform: scale(1.05);
    }

    /* ── Mobile logo: fixed overlay that slides with the sidebar ── */
    @media (max-width: 768px) {

        /* The logo wrapper div follows the sidebar slide-in/out */
        div.app-sidebar.app-sidebar2,
        .app-sidebar2 {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 250px !important;
            height: auto !important;
            overflow: visible !important;
            z-index: 10001 !important;
            background: transparent !important;
            pointer-events: none !important;
            /* hidden by default — matches closed sidebar */
            transform: translateX(-250px) !important;
            transition: transform 0.3s ease !important;
        }

        /* Slide in when sidebar opens (.sidenav-toggled on body) */
        .sidenav-toggled div.app-sidebar.app-sidebar2,
        .sidenav-toggled .app-sidebar2 {
            transform: translateX(0) !important;
        }

        div.app-sidebar.app-sidebar2 .app-sidebar__logo,
        .app-sidebar2 .app-sidebar__logo {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: all !important;
            background: transparent !important;
            padding: 12px 0 14px 0 !important;
            text-align: center !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
            margin-bottom: 0 !important;
        }

        div.app-sidebar.app-sidebar2 .sidebar-logo,
        .app-sidebar2 .sidebar-logo {
            width: 75px !important;
            height: 75px !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            margin: 0 auto !important;
            border-radius: 50% !important;
            border: 3px solid #667eea !important;
            background: white !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
            object-fit: cover !important;
            pointer-events: all !important;
        }
    }

    @media (max-width: 480px) {

        div.app-sidebar.app-sidebar2 .sidebar-logo,
        .app-sidebar2 .sidebar-logo {
            width: 60px !important;
            height: 60px !important;
        }
    }
</style>

<script>
    (function () {
        const sidebar = document.querySelector('.app-sidebar3');
        const logoWrapper = document.querySelector('.app-sidebar__logo');

        if (!sidebar || !logoWrapper) return;

        function handleScroll() {
            if (sidebar.scrollTop > 20) {
                logoWrapper.classList.add('scrolled');
            } else {
                logoWrapper.classList.remove('scrolled');
            }
        }

        sidebar.addEventListener('scroll', handleScroll);

        const toggleButtons = document.querySelectorAll('.app-sidebar__toggle');
        toggleButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setTimeout(function () {
                    sidebar.scrollTop = 0;
                    logoWrapper.classList.remove('scrolled');
                }, 150);
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            sidebar.scrollTop = 0;
            logoWrapper.classList.remove('scrolled');
        });
    })();
</script>

<script>
    $(document).ready(function () {
        $('#helpSupportToggle').on('click', function (e) {
            e.preventDefault();
            $(this).parent('.slide').toggleClass('active');
        });
    });

    document.getElementById('logoutMenu').addEventListener('click', function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Are you sure?",
            text: "Do you really want to Logout ?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Logout",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route('student-logout') }}';
            }
        });
    });
</script>

<!--aside closed-->
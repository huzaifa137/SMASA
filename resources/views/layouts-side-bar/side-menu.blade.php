<div class="app-sidebar app-sidebar2">
    <div class="app-sidebar__logo">
        @if (Session('LoggedAdmin'))
            <a class="header-brand" href="{{ url('/admin/dashboard') }}">
                <img src="{{ URL::asset('assets/images/brand/uplogolight.png') }}" alt="Covido logo"
                    style="width: 100%; height: auto; max-width: 170px;">
            </a>
        @elseif(Session('LoggedSchool'))
            <a class="header-brand" href="{{ url('/school/dashboard') }}">
                <img src="{{ URL::asset('assets/images/brand/uplogolight.png') }}" alt="Covido logo"
                    style="width: 100%; height: auto; max-width: 170px;">
            </a>
        @endif
    </div>
</div>

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
                <a class="side-menu__item" href="{{ route('manage.classes') }}">
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

            <li class="slide">
                <a class="side-menu__item" href="{{ url('/enter-marks') }}">
                    <i class="fas fa-balance-scale-right fa-2x mr-3"></i>
                    Grading Marks
                </a>
            </li>

        @elseif(Session('LoggedAdmin'))
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
                <a class="side-menu__item" href="{{ url('/user-rights-and-previledges/setup') }}">
                    <i class="fas fa-user-shield fa-2x mr-3"></i>
                    Rights & Privileges
                </a>
            </li>
        @elseif(Session('LoggedSchool'))
            <li class="slide">
                <a class="side-menu__item" href="{{ url('/school/dashboard') }}">
                    <i class="fa fa-home fa-2x mr-3"></i>
                    Dashboard
                </a>
            </li>

            <li class="slide">
                <a class="side-menu__item" href="{{ route('manage.classes') }}">
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

            <!-- Report & Marks Dropdown -->
            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-balance-scale-right fa-2x mr-3"></i>
                    <span>Report & Marks</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a href="{{ url('/enter-marks') }}">
                            <i class="fas fa-pen-alt mr-2"></i>
                            Enter Marks
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/view-marks') }}">
                            <i class="fas fa-eye mr-2"></i>
                            View Marks
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/generate-report') }}">
                            <i class="fas fa-file-alt mr-2"></i>
                            Generate Report
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/marks-analysis') }}">
                            <i class="fas fa-chart-line mr-2"></i>
                            Marks Analysis
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/download-reports') }}">
                            <i class="fas fa-download mr-2"></i>
                            Download Reports
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Examinations Dropdown (Updated) -->
            <li class="slide has-sub">
                <a class="side-menu__item" href="#" data-toggle="submenu">
                    <i class="fas fa-layer-group fa-2x mr-3"></i>
                    <span>Examinations</span>
                    <i class="fas fa-chevron-down dropdown-icon ml-auto"></i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a href="{{ route('examination.create') }}">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Create Examination
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('examination.index') }}">
                            <i class="fas fa-list mr-2"></i>
                            All Examinations
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('examination.demo') }}">
                            <i class="fas fa-flask mr-2"></i>
                            Demo Exams
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('examination.passslips.index', $exam->id) }}" class="btn btn-sm fw-semibold"
                            style="background: linear-gradient(135deg, #2C29CA, #5351e4);
                  color: #fff; border-radius: .6rem; font-size: .75rem;">
                            <i class="fas fa-id-card me-1"></i> Pass Slips
                        </a>
                    </li>
                </ul>
            </li>

            <style>
                /* Sidebar Styles */
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
                    color: white;
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

                /* Submenu Styles */
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
                    color: white;
                    transform: translateX(5px);
                }

                .sub-menu li a i {
                    width: 25px;
                    font-size: 14px;
                }

                /* Active menu item */
                .side-menu__item.active {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
                }

                /* Hover effects for parent items */
                .has-sub:hover>.side-menu__item {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }

                /* Submenu item animations */
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

                /* Stagger animations for submenu items */
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

                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .sub-menu {
                        padding-left: 30px;
                    }

                    .side-menu__item {
                        padding: 10px 15px;
                    }
                }
            </style>

            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

            <script>
                $(document).ready(function () {
                    // Handle submenu toggles
                    $('[data-toggle="submenu"]').on('click', function (e) {
                        e.preventDefault();

                        // Get the parent slide
                        var $slide = $(this).closest('.slide');

                        // Close other open submenus (optional - comment out if you want multiple open)
                        $('.slide').not($slide).removeClass('active');

                        // Toggle current
                        $slide.toggleClass('active');
                    });

                    // Keep submenu open if a child link is active
                    // var currentUrl = window.location.href;
                    // $('.sub-menu a').each(function () {
                    //     if (currentUrl.indexOf($(this).attr('href')) !== -1) {
                    //         $(this).closest('.slide').addClass('active');
                    //     }
                    // });

                    // Logout functionality
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
        @endif

        <li class="slide">
            <a class="side-menu__item" href="#" id="logoutMenu">
                <i class="fa fa-sign-out fa-2x mr-3"></i>
                Logout
            </a>
        </li>

    </ul>
</aside>

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
</style>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
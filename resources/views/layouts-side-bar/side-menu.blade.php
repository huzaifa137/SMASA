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

            {{-- <li class="slide">
                <a class="side-menu__item" href="{{ url('/user-rights-and-previledges/setup') }}">
                    <i class="fas fa-user-shield fa-2x mr-3"></i>
                    Rights & Privileges
                </a>
            </li> --}}
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

            <li class="slide">
                <a class="side-menu__item" href="{{ url('/enter-marks') }}">
                    <i class="fas fa-balance-scale-right fa-2x mr-3"></i>
                    Grading Marks
                </a>
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
    $(document).ready(function() {
        $('#helpSupportToggle').on('click', function(e) {
            e.preventDefault();
            $(this).parent('.slide').toggleClass('active');
        });
    });

    document.getElementById('logoutMenu').addEventListener('click', function(event) {
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

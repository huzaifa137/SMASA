<!--app header-->
<div class="app-header header top-header">
    <div class="container-fluid">
        <div class="d-flex">
            <a class="header-brand" href="{{ url('/' . ($page = '#')) }}">
                <img src="{{ URL::asset('assets/images/brand/logo.png') }}" class="header-brand-img desktop-lgo"
                    alt="SMASA logo">
            </a>

            @php
                use App\Http\Controllers\Helper;
            @endphp

            <div class="dropdown side-nav">
                <div class="app-sidebar__toggle" data-toggle="sidebar">
                    <a class="open-toggle" href="#">
                        <svg class="header-icon mt-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </a>
                    <a class="close-toggle" href="#">
                        <svg class="header-icon mt-1" xmlns="http://www.w3.org/2000/svg" height="24"
                            viewBox="0 0 24 24" width="24">
                            <path d="M0 0h24v24H0V0z" fill="none" />
                            <path
                                d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="d-none d-md-block">
                @include('layouts.language-switcher')
            </div>

            @php
                use Illuminate\Support\Facades\DB;
                $schools = DB::table('schools')->latest()->get();
                $selectedSchool = session('LoggedSchool')
                    ? DB::table('schools')->where('id', session('LoggedSchool'))->first()
                    : null;
            @endphp

            @if (session('LoggedAdmin'))
                <div class="mt-3 ml-3 col-6">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle font-weight-bold w-100" type="button"
                            id="schoolDropdownButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ $selectedSchool ? $selectedSchool->name : 'Select School' }}
                        </button>
                        <div class="dropdown-menu w-100 p-2" aria-labelledby="schoolDropdownButton"
                            style="max-height: 300px; overflow-y: auto;">
                            <input type="text" class="form-control mb-2" id="schoolSearch"
                                placeholder="Search school...">
                            <div id="schoolList">
                                @forelse ($schools as $school)
                                    <a class="dropdown-item school-item" href="#" data-id="{{ $school->id }}"
                                        data-name="{{ $school->name }}">
                                        {{ $school->name }}
                                    </a>
                                @empty
                                    <a class="dropdown-item" href="#">No schools found.</a>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="d-flex order-lg-2 ml-auto" style="margin-top:0.7rem;">
                <div class="display-name">
                    @if (Session('LoggedSchool'))
                        <span style="line-height:40px;">
                            School :
                            <span class="text-primary font-weight-bold">
                                {{ Helper::schoolNameByID(Session('LoggedSchool')) }}
                            </span>
                        </span>
                    @else
                        <span style="line-height:40px;">
                            Admin :
                            <span class="text-primary font-weight-bold">
                                {{ Helper::logged_admin_user() }}
                            </span>
                        </span>
                    @endif
                </div>


                <div class="dropdown profile-dropdown">
                    <a href="#" class="nav-link pr-0 leading-none" data-toggle="dropdown">
                        <i class="fa fa-fw fa-cog fa-2x"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow animated">
                        <div class="text-center">
                            <a href="#" class="dropdown-item text-center user pb-0 font-weight-bold">

                            </a>
                            <div class="dropdown-divider"></div>
                        </div>

                        @if (!Session('LoggedSchool'))

                            @php
                                $teacherId = Session::get('LoggedTeacher');
                                $user = \App\Models\Teacher::where('id', $teacherId)->first();
                            @endphp

                            @if ($user && $user->must_change_password)
                                <a class="dropdown-item d-flex text-danger font-weight-bold"
                                    href="{{ url('/change-password') }}">
                                    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                    <div class="mt-1">Update Your Password</div>
                                </a>
                            @else
                                <a class="dropdown-item d-flex" href="{{ url('/add-academic-year') }}">
                                    <i class="fas fa-clock fa-2x mr-3"></i>
                                    <div class="mt-1">Active Year</div>
                                </a>

                                <a class="dropdown-item d-flex"
                                    href="{{ url('/update-teacher-profile', $teacherId) }}">
                                    <i class="fa fa-user fa-2x mr-3"></i>
                                    <div class="mt-1">User Profile</div>
                                </a>
                            @endif
                        @endif

                        <a class="dropdown-item d-flex" href="#" id="logoutLink">
                            <i class="fa fa-sign-out fa-2x mr-3"></i>
                            <div class="mt-1">Sign Out</div>
                        </a>

                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                        <script>
                            document.getElementById('logoutLink').addEventListener('click', function(event) {
                                event.preventDefault();

                                Swal.fire({
                                    title: "Are you sure?",
                                    text: "Do you really want to sign out?",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, Sign out",
                                    cancelButtonText: "Cancel",
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href =
                                            '{{ route('student-logout') }}';
                                    }
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>

                                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                        <script>
                            document.addEventListener("DOMContentLoaded", function () {
                                const searchInput = document.getElementById('schoolSearch');
                                const schoolItems = document.querySelectorAll('.school-item');
                                const dropdownBtn = document.getElementById('schoolDropdownButton');

                                // Live filtering
                                searchInput.addEventListener('keyup', function () {
                                    const searchValue = this.value.toLowerCase();
                                    schoolItems.forEach(item => {
                                        const schoolName = item.textContent.toLowerCase();
                                        item.style.display = schoolName.includes(searchValue) ? '' : 'none';
                                    });
                                });

                                // School select and SweetAlert
                                schoolItems.forEach(item => {
                                    item.addEventListener('click', function (e) {
                                        e.preventDefault();
                                        const schoolId = this.dataset.id;
                                        const schoolName = this.dataset.name;

                                        Swal.fire({
                                            title: "Switch School?",
                                            text: `Are you sure you want to switch to "${schoolName}"?`,
                                            icon: "warning",
                                            showCancelButton: true,
                                            confirmButtonText: "Yes, switch",
                                            cancelButtonText: "Cancel"
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                // Send to backend via AJAX
fetch("{{ route('school.select') }}", {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ school_id: schoolId })
})
.then(response => response.text()) // 👈 change this
.then(data => {
    document.body.innerHTML = data; // 👈 show dd() output
})
.catch(() => {
    alert("Something went wrong!");
});
                                            }
                                        });
                                    });
                                });
                            });
                        </script>
        </div>
    </div>
</div>
<!--/app header-->

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

            <style>
                .swal2-container {
                    z-index: 20000 !important;
                }

                /* Hide by default (small devices) */
                .responsive-user-section {
                    display: none !important;
                }

                /* Show only on large screens (1372px and above) */
                @media (min-width: 1372px) {
                    .responsive-user-section {
                        display: flex !important;
                    }
                }

                @media (max-width: 576px) {
                    .dropdown-menu {
                        width: 95% !important;
                        left: 2.5% !important;
                    }
                }

                .dropdown-menu .form-control {
                    width: 100%;
                    box-sizing: border-box;
                }

                /* Hide for screen width below 990px and height below 703px */
                @media (max-width: 989px),
                (max-height: 702px) {
                    .admin-school-dropdown {
                        display: none !important;
                    }
                }
            </style>

            <style>
                @keyframes blink {
                    0% {
                        opacity: 1;
                        background-color: #dc3545;
                        color: white;
                    }

                    50% {
                        opacity: 0.7;
                        background-color: #ffc107;
                        color: #dc3545;
                    }

                    100% {
                        opacity: 1;
                        background-color: #dc3545;
                        color: white;
                    }
                }

                @keyframes glow {
                    0% {
                        text-shadow: 0 0 5px #dc3545;
                    }

                    50% {
                        text-shadow: 0 0 20px #ff0000, 0 0 30px #ff0000;
                    }

                    100% {
                        text-shadow: 0 0 5px #dc3545;
                    }
                }

                .password-warning-link {
                    animation: blink 1s infinite;
                    padding: 8px 20px;
                    border-radius: 50px;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    border: 2px solid #fff;
                    box-shadow: 0 0 15px rgba(220, 53, 69, 0.5);
                    cursor: pointer;
                    transition: transform 0.3s ease;
                }

                .password-warning-link:hover {
                    transform: scale(1.05);
                }

                .password-warning-link i {
                    animation: glow 1s infinite;
                }

                .password-warning-link span {
                    animation: glow 1s infinite;
                }
            </style>

            <style>
                /* Modal Animation */
                @keyframes modalSlideIn {
                    from {
                        transform: translateY(-100px);
                        opacity: 0;
                    }

                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }

                .modal-content {
                    animation: modalSlideIn 0.3s ease-out;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                }

                .modal-header {
                    border-radius: 15px 15px 0 0;
                    background: linear-gradient(135deg, #dc3545, #c82333);
                }

                /* Input focus effects */
                .form-control:focus {
                    border-color: #dc3545;
                    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
                }

                /* Toggle password button hover */
                .toggle-password:hover {
                    background-color: #dc3545;
                    border-color: #dc3545;
                    color: white;
                }

                /* Password strength indicator */
                .password-strength-weak {
                    color: #dc3545;
                }

                .password-strength-medium {
                    color: #ffc107;
                }

                .password-strength-strong {
                    color: #28a745;
                }

                /* Requirement check icons */
                .requirement-met {
                    color: #28a745;
                }

                .requirement-unmet {
                    color: #dc3545;
                }
            </style>

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
                        <svg class="header-icon mt-1" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24"
                            width="24">
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

                // Fetch only id and name for the dropdown
                $schools = DB::table('schools')
                    ->select('id', 'name') // select only needed columns
                    ->latest()
                    ->get();

                // Fetch only id and name for the selected school
                $selectedSchool = session('LoggedSchool')
                    ? DB::table('schools')
                        ->select('id', 'name') // select only needed columns
                        ->where('id', session('LoggedSchool'))
                        ->first()
                    : null;
            @endphp

            @if (session('LoggedAdmin'))
                <div class="admin-school-dropdown mt-3 ml-3 col-12 col-md-6">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle font-weight-bold w-100" type="button"
                            id="schoolDropdownButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ $selectedSchool ? $selectedSchool->name : 'Select School' }}
                        </button>
                        <div class="dropdown-menu w-100 p-2" aria-labelledby="schoolDropdownButton"
                            style="max-height: 300px; overflow-y: auto;">
                            <input type="text" class="form-control mb-2" id="schoolSearch" placeholder="Search school...">
                            <div id="schoolList">
                                <a class="dropdown-item clear-school bg-light text-primary font-weight-bold rounded"
                                    href="#" style="border: 1px dashed #2C29CA; margin-bottom: 5px;">
                                    <i class="fas fa-undo-alt mr-2"></i> Clear School Selection
                                </a>
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
                <div class="display-name responsive-user-section">
                    <!-- existing display name content -->
                </div>

                @if (Session('LoggedSchool'))
                    @php
                        $teacherId = Session::get('LoggedTeacher');
                        $user = \App\Models\Teacher::where('id', $teacherId)->first();
                    @endphp

                    @if ($user && $user->must_change_password)
                        <div class="d-flex align-items-center mx-3">
                            <a class="text-danger font-weight-bold d-flex align-items-center password-warning-link"
                                href="javascript:void(0);" onclick="showPasswordModal()" style="text-decoration: none;">
                                <i class="fas fa-exclamation-triangle fa-2x mr-2"></i>
                                <span style="color:#FFF;">⚠️ URGENT: Update Your Password ⚠️</span>
                            </a>
                        </div>
                    @endif
                @endif

                <div class="dropdown profile-dropdown">
                    <!-- existing dropdown content -->
                </div>
            </div>

            <div class="d-flex order-lg-2 ml-auto" style="margin-top:0.7rem;">
                <div class="display-name responsive-user-section">
                    @if (Session('LoggedSchool'))
                        <span style="line-height:40px;">
                            School :
                            <span class="text-primary font-weight-bold">
                                {{ Helper::schoolNameBySchoolID(Session('LoggedSchool')) }}
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

                        @if (Session('LoggedSchool'))

                            @php
                                $teacherId = Session::get('LoggedTeacher');
                                $user = \App\Models\Teacher::where('id', $teacherId)->first();
                            @endphp

                            @if ($user && $user->must_change_password)
                                <a class="dropdown-item d-flex text-danger font-weight-bold" href="javascript:void(0);"
                                    onclick="showPasswordModal()">
                                    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                    <div class="mt-1">Update Your Password</div>
                                </a>
                            @else
                                <a class="dropdown-item d-flex" href="{{ url('/add-academic-year') }}">
                                    <i class="fas fa-clock fa-2x mr-3"></i>
                                    <div class="mt-1">Active Year</div>
                                </a>

                                <a class="dropdown-item d-flex" href="{{ url('/update-teacher-profile', $teacherId) }}">
                                    <i class="fa fa-user fa-2x mr-3"></i>
                                    <div class="mt-1">User Profile</div>
                                </a>
                            @endif
                        @endif

                        <a class="dropdown-item d-flex" href="#" id="logoutLink">
                            <i class="fa fa-sign-out fa-2x mr-3"></i>
                            <div class="mt-1">Sign Out</div>
                        </a>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.getElementById('logoutLink').addEventListener('click', function (event) {
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
                            window.location.href = '{{ route('student-logout') }}';
                        }
                    });
                });
            </script>
        </div>
    </div>
</div>
<!--/app header-->

<!-- Password Update Modal - MOVED OUTSIDE HEADER -->
<div class="modal fade" id="passwordUpdateModal" tabindex="-1" role="dialog" aria-labelledby="passwordUpdateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="passwordUpdateModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Security Alert: Password Update Required
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="closeModalX">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="passwordUpdateForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle mr-2"></i>
                        For security reasons, you must update your password before continuing.
                    </div>

                    <!-- New Password Field -->
                    <div class="form-group mb-4">
                        <label for="new_password" class="font-weight-bold">
                            <i class="fas fa-lock mr-2 text-danger"></i>
                            New Password
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0">
                                    <i class="fas fa-key text-danger"></i>
                                </span>
                            </div>
                            <input type="password" class="form-control border-left-0" id="new_password"
                                name="new_password" placeholder="Enter new password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted" id="passwordStrength"></small>
                        <div class="invalid-feedback" id="new_password_error"></div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group mb-4">
                        <label for="confirm_password" class="font-weight-bold">
                            <i class="fas fa-check-circle mr-2 text-danger"></i>
                            Confirm Password
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0">
                                    <i class="fas fa-check text-danger"></i>
                                </span>
                            </div>
                            <input type="password" class="form-control border-left-0" id="confirm_password"
                                name="confirm_password" placeholder="Confirm new password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="confirm_password_error"></div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="alert alert-light border">
                        <small class="text-muted d-block mb-2">Password must contain:</small>
                        <small class="d-block" id="lengthCheck">
                            <i class="far fa-circle mr-2" id="lengthIcon"></i>
                            At least 8 characters
                        </small>
                        <small class="d-block" id="upperCheck">
                            <i class="far fa-circle mr-2" id="upperIcon"></i>
                            At least one uppercase letter
                        </small>
                        <small class="d-block" id="numberCheck">
                            <i class="far fa-circle mr-2" id="numberIcon"></i>
                            At least one number
                        </small>
                        <small class="d-block" id="specialCheck">
                            <i class="far fa-circle mr-2" id="specialIcon"></i>
                            At least one special character
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModalBtn">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="submitPasswordBtn">
                        <i class="fas fa-save mr-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Function to show modal
        window.showPasswordModal = function () {
            $('#passwordUpdateModal').modal('show');
        };

        // Auto-show modal if password needs update and not already shown
        @if(isset($user) && $user && $user->must_change_password)
            // Small delay to ensure DOM is fully loaded
            setTimeout(function () {
                $('#passwordUpdateModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#passwordUpdateModal').modal('show');
            }, 500);
        @endif

        // Toggle password visibility
        $('.toggle-password').click(function () {
            const target = $(this).data('target');
            const input = $('#' + target);
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Real-time password validation
        $('#new_password').on('keyup', function () {
            const password = $(this).val();
            validatePassword(password);
            checkPasswordMatch();
        });

        $('#confirm_password').on('keyup', function () {
            checkPasswordMatch();
        });

        function validatePassword(password) {
            const checks = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            // Update icons
            updateCheckIcon('length', checks.length);
            updateCheckIcon('upper', checks.upper);
            updateCheckIcon('number', checks.number);
            updateCheckIcon('special', checks.special);

            // Calculate strength
            const strength = Object.values(checks).filter(Boolean).length;
            updatePasswordStrength(strength);

            return checks.length && checks.upper && checks.number && checks.special;
        }

        function updateCheckIcon(checkId, isValid) {
            const icon = $('#' + checkId + 'Icon');
            if (isValid) {
                icon.removeClass('fa-circle').addClass('fa-check-circle');
                icon.css('color', '#28a745');
            } else {
                icon.removeClass('fa-check-circle').addClass('fa-circle');
                icon.css('color', '#dc3545');
            }
        }

        function updatePasswordStrength(strength) {
            const strengthText = $('#passwordStrength');
            if (strength === 4) {
                strengthText.html('<i class="fas fa-shield-alt text-success"></i> Strong password!');
                strengthText.removeClass('password-strength-weak password-strength-medium').addClass('password-strength-strong');
            } else if (strength === 3) {
                strengthText.html('<i class="fas fa-shield-alt text-warning"></i> Medium strength');
                strengthText.removeClass('password-strength-weak password-strength-strong').addClass('password-strength-medium');
            } else if (strength >= 2) {
                strengthText.html('<i class="fas fa-shield-alt text-danger"></i> Weak password');
                strengthText.removeClass('password-strength-medium password-strength-strong').addClass('password-strength-weak');
            } else {
                strengthText.html('');
            }
        }

        function checkPasswordMatch() {
            const password = $('#new_password').val();
            const confirm = $('#confirm_password').val();

            if (confirm.length > 0) {
                if (password === confirm) {
                    $('#confirm_password').removeClass('is-invalid').addClass('is-valid');
                    $('#confirm_password_error').text('');
                    return true;
                } else {
                    $('#confirm_password').removeClass('is-valid').addClass('is-invalid');
                    $('#confirm_password_error').text('Passwords do not match');
                    return false;
                }
            } else {
                $('#confirm_password').removeClass('is-valid is-invalid');
                return false;
            }
        }

        // Form submission
        $('#passwordUpdateForm').on('submit', function (e) {
            e.preventDefault();

            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();

            // Validate password requirements
            const isValid = validatePassword(newPassword);

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Password',
                    text: 'Please ensure your password meets all requirements',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'New password and confirm password do not match',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // ✅ NEW: Confirmation before submission
            Swal.fire({
                title: "Confirm Password Update",
                text: "Are you sure you want to update your password?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, update it",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {

                    // Disable button
                    const submitBtn = $('#submitPasswordBtn');
                    submitBtn.prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...');

                    // AJAX request
                    $.ajax({
                        url: '{{ route("teacher.update-password") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            password: newPassword,
                            teacher_id: '{{ Session::get("LoggedTeacher") }}'
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#28a745',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    confirmButtonColor: '#dc3545'
                                });

                                submitBtn.prop('disabled', false)
                                    .html('<i class="fas fa-save mr-2"></i>Update Password');
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Something went wrong. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage,
                                confirmButtonColor: '#dc3545'
                            });

                            submitBtn.prop('disabled', false)
                                .html('<i class="fas fa-save mr-2"></i>Update Password');
                        }
                    });

                }
            });
        });

        let allowClose = false;

        $('#closeModalX, #closeModalBtn').on('click', function () {
            allowClose = true;
        });

        // Prevent modal close when clicking outside if password must be changed
        $('#passwordUpdateModal').on('hide.bs.modal', function (e) {
            @if(isset($user) && $user && $user->must_change_password)
                if (!allowClose) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Required Action',
                        text: 'You must update your password to continue using the system.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            @endif
});
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('schoolSearch');
        const schoolItems = document.querySelectorAll('.school-item');
        const dropdownBtn = document.getElementById('schoolDropdownButton');

        // Live filtering
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                const searchValue = this.value.toLowerCase();
                schoolItems.forEach(item => {
                    const schoolName = item.textContent.toLowerCase();
                    item.style.display = schoolName.includes(searchValue) ? '' : 'none';
                });
            });
        }

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
                        fetch("{{ route('school.select') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                school_id: schoolId
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status) {
                                    Swal.fire({
                                        title: "School Changed!",
                                        text: data.message,
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("Error", data.message, "error");
                                }
                            })
                            .catch(() => {
                                Swal.fire("Error", "Something went wrong!", "error");
                            });
                    }
                });
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const clearSchoolBtn = document.querySelector('.clear-school');

        if (clearSchoolBtn) {
            clearSchoolBtn.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: "Clear School?",
                    text: "Do you want to clear the selected school?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, clear",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('school.clear') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status) {
                                    Swal.fire({
                                        title: "Cleared!",
                                        text: data.message,
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href = '/';
                                    });
                                } else {
                                    Swal.fire("Error", data.message, "error");
                                }
                            })
                            .catch(() => {
                                Swal.fire("Error", "Something went wrong!", "error");
                            });
                    }
                });
            });
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Function to set sidebar state
        function setSidebarState(isMinimized) {
            if (isMinimized) {
                document.body.classList.add('sidenav-toggled');
                localStorage.setItem('sidebarMinimized', 'true');
            } else {
                document.body.classList.remove('sidenav-toggled');
                localStorage.setItem('sidebarMinimized', 'false');
            }
        }

        // Restore sidebar state on page load
        const savedState = localStorage.getItem('sidebarMinimized');
        if (savedState === 'true') {
            document.body.classList.add('sidenav-toggled');
        }

        // Listen for sidebar toggle clicks
        const toggleButtons = document.querySelectorAll('.app-sidebar__toggle');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                setTimeout(() => {
                    const isMinimized = document.body.classList.contains('sidenav-toggled');
                    localStorage.setItem('sidebarMinimized', isMinimized);
                }, 100);
            });
        });
    });
</script>
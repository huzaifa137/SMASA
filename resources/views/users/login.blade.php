<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SMASA </title>
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 Bootstrap5 Theme (optional but recommended for better styling) -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        /* ------------------------------------------------------------
           ORANGE & BLACK THEME – FREST MINIMAL CARD DESIGN
           All original functionality preserved (role selector, password toggle,
           AJAX, CSRF, error handling, dynamic labels)
        ------------------------------------------------------------ */
        :root {
            --orange: #2C29CA;
            /* repurposed variable name kept for minimal changes */
            --orange-dark: #14136e;
            --orange-light: #4aa3de;
            --orange-subtle: #ecfdf5;
            --black: #0a0a0a;
            --gray-900: #18181b;
            --gray-700: #3f3f46;
            --gray-500: #71717a;
            --gray-300: #d4d4d8;
            --gray-100: #f4f4f5;
            --white: #ffffff;
            --radius: 20px;
            --radius-sm: 12px;
            --shadow: 0 20px 40px -12px rgba(22, 163, 74, 0.12), 0 8px 24px -6px rgba(0, 0, 0, 0.04);
            --transition: all 0.2s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(145deg, #f0fdf4 0%, #ecfdf5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* CARD – exactly like the Frest screenshot */
        .login-card {
            max-width: 500px;
            width: 100%;
            background: var(--white);
            border-radius: 32px;
            padding: 2.5rem 2.2rem;
            box-shadow: var(--shadow);
            transition: transform 0.2s;
        }

        .login-card:hover {
            transform: scale(1.01);
        }

        /* Brand / Logo */
        .brand {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--black);
            margin-bottom: 0.5rem;
        }

        .brand span {
            color: var(--orange);
        }

        /* Welcome message – exactly as described */
        .welcome-text {
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .welcome-text i,
        .welcome-text .emoji {
            color: var(--orange);
        }

        /* ---------- ROLE SELECTOR (kept original functionality) ---------- */
        .user-role-selector {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
        }

        .role-btn {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 1rem 0.5rem;
            background: var(--gray-100);
            border: 2px solid transparent;
            border-radius: var(--radius-sm);
            color: var(--gray-700);
            font-weight: 600;
            font-size: 0.85rem;
            transition: var(--transition);
            cursor: pointer;
        }

        .role-btn i {
            font-size: 1.3rem;
            color: var(--gray-500);
            transition: var(--transition);
        }

        .role-btn.active {
            background: var(--orange-subtle);
            border-color: var(--orange);
            color: var(--orange-dark);
        }

        .role-btn.active i {
            color: var(--orange);
        }

        .role-btn:hover:not(.active) {
            background: #fafafa;
            border-color: var(--gray-300);
        }

        /* ---------- SCHOOL DROPDOWN (NEW) ---------- */
        .school-dropdown-container {
            margin-bottom: 1.75rem;
            display: none;
            /* Hidden by default */
        }

        .school-dropdown-container.visible {
            display: block;
        }

        .school-select {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1.5px solid var(--gray-300);
            border-radius: 40px;
            font-size: 0.95rem;
            background: var(--white);
            transition: var(--transition);
            appearance: none;
            cursor: pointer;
            color: var(--gray-900);
        }

        .school-select:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.08);
        }

        .school-select option {
            padding: 1rem;
        }

        .input-group.select-group {
            position: relative;
        }

        .select-arrow {
            position: absolute;
            right: 1.2rem;
            color: var(--gray-500);
            font-size: 1rem;
            pointer-events: none;
        }

        /* ---------- FORM ---------- */
        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-900);
            margin-bottom: 0.6rem;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1.5px solid var(--gray-300);
            border-radius: 40px;
            font-size: 0.95rem;
            background: var(--white);
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.08);
        }

        .input-icon {
            position: absolute;
            left: 1.2rem;
            color: var(--gray-500);
            font-size: 1.1rem;
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 1.2rem;
            background: none;
            border: none;
            color: var(--gray-500);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Row: Remember me + Forgot password */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0 2rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            font-size: 0.95rem;
            color: var(--gray-700);
            font-weight: 500;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--orange);
            border-radius: 4px;
            cursor: pointer;
        }

        .forgot-password {
            color: var(--orange);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--orange-dark);
            text-decoration: underline;
        }

        /* Primary button – orange */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background: var(--orange);
            color: white;
            box-shadow: 0 8px 16px -4px rgba(22, 163, 74, 0.28);
            margin-bottom: 1.5rem;
        }

        .btn-primary:hover {
            background: var(--orange-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -6px rgba(22, 78, 163, 0.36);
        }

        .btn-primary i {
            font-size: 1rem;
        }

        /* "New on our platform? Create an account" */
        .signup-link {
            text-align: center;
            margin: 1.8rem 0 1.5rem;
            font-size: 0.95rem;
            color: var(--gray-700);
            font-weight: 500;
        }


        .signup-link a {
            color: var(--orange);
            font-weight: 700;
            text-decoration: none;
            margin-left: 4px;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        /* OR divider – exactly as in the screenshot */
        .divider {
            display: flex;
            align-items: center;
            color: var(--gray-500);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 1.2rem 0 1.5rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--gray-300);
        }

        .divider span {
            margin: 0 1rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        /* Secondary button (Back to Homepage) – subtle outline, keeps original link */
        .btn-secondary {
            background: transparent;
            color: var(--gray-900);
            border: 2px solid var(--gray-300);
            box-shadow: none;
            margin-top: 0.5rem;
        }

        .btn-secondary:hover {
            border-color: var(--orange);
            color: var(--orange);
            background: rgba(22, 163, 74, 0.04);
            transform: translateY(-2px);
        }

        /* Error messages – kept original style */
        .error-text {
            display: block;
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 6px;
            margin-left: 12px;
            font-weight: 500;
        }

        button:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .brand {
                font-size: 2rem;
            }

            .user-role-selector {
                flex-wrap: wrap;
            }

            .role-btn {
                min-width: 100px;
            }
        }

        /* Select2 Custom Styling to fix dropdown positioning */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 40px !important;
            padding: 0.65rem 0.5rem 0.65rem 2.8rem !important;
            border: 1.5px solid var(--gray-300) !important;
            min-height: 52px !important;
            display: flex !important;
            align-items: center !important;
            background-color: var(--white) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--gray-900) !important;
            line-height: 1.5 !important;
            padding-left: 0 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: var(--gray-500) !important;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: var(--orange) !important;
            box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.08) !important;
        }

        /* Fix dropdown positioning */
        .select2-container {
            z-index: 1050 !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: var(--orange) !important;
            border-radius: var(--radius-sm) !important;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-top: 4px !important;
            z-index: 1060 !important;
        }

        /* Ensure dropdown stays within card boundaries */
        .select2-container--open {
            position: fixed !important;
            left: auto !important;
            top: auto !important;
        }

        .select2-container--bootstrap-5 .select2-results__option {
            padding: 10px 15px !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--selected {
            background-color: var(--orange-subtle) !important;
            color: var(--orange-dark) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--orange) !important;
            color: white !important;
        }

        /* Search box styling */
        .select2-container--bootstrap-5 .select2-search--dropdown {
            padding: 10px !important;
            border-bottom: 1px solid var(--gray-300) !important;
        }

        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border: 1.5px solid var(--gray-300) !important;
            border-radius: 30px !important;
            padding: 8px 15px !important;
            font-size: 0.9rem !important;
        }

        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--orange) !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.08) !important;
        }

        /* Fix for icon positioning */
        .input-group.select-group {
            position: relative;
        }

        .input-group.select-group .input-icon {
            z-index: 1070 !important;
        }

        /* Remove default arrow since we have custom one */
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            display: none !important;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <!-- === FREST BRAND (exactly as in the image) === -->
        <div class="brand">
            SM<span>A</span>SA
        </div>
        <div class="welcome-text">
            <i class="fas fa-hand-sparkles" style="color: var(--orange);"></i> Welcome to
            SM<span>A</span>SA! <br>
            Please sign-in to your account
        </div>

        <!-- === ORIGINAL ROLE SELECTOR – fully functional, now in orange === -->
        <div class="user-role-selector">
            <!-- <button class="role-btn active" data-role="student">
                <i class="fas fa-user-graduate"></i>
                <span>Student</span>
            </button> -->
            <button class="role-btn active" data-role="school">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>School</span>
            </button>
            <button class="role-btn" data-role="admin">
                <i class="fas fa-user-cog"></i>
                <span>Admin</span>
            </button>
        </div>

        <!-- === LOGIN FORM – everything original: action, CSRF, ids, dynamic labels, password toggle, AJAX === -->
        <form class="login-form" id="loginForm" action="{{ route('auth-user-check') }}" method="POST">
            @csrf
            <input type="hidden" name="role" id="login_role" value="student">

            <!-- NEW: School Dropdown (only visible when School role is selected) -->
            <div class="school-dropdown-container" id="schoolDropdownContainer">
                <label for="school_id" class="form-label">SELECT SCHOOL</label>
                <div class="input-group select-group">
                    <i class="fas fa-school input-icon"></i>

                    <select name="school_id" id="school_id" class="school-select select2">
                        <option value="" disabled selected>Choose your school</option>

                        @foreach($schools as $school)
                        <option value="{{ $school->ID }}">
                            {{ $school->House }}
                        </option>
                        @endforeach

                    </select>

                    <i class="fas fa-chevron-down select-arrow"></i>
                </div>
                <small class="error-text" id="school_id-error"></small>
            </div>

            <!-- Username / Registration field -->
            <div class="form-group">
                <label for="username" class="form-label" id="usernameLabel">REGISTRATION NUMBER121</label>
                <div class="input-group">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="username" name="username" class="form-input"
                        placeholder="Enter your student registration number">
                </div>
                <small class="error-text" id="username-error"></small>
            </div>

            <!-- Password field with toggle -->
            <div class="form-group">
                <label for="password" class="form-label" id="passwordLabel">STUDENT PASSWORD</label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-input"
                        placeholder="Enter your secure password">
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="error-text" id="password-error"></small>
            </div>

            <!-- Remember me & Forgot password row -->
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="javascript:void();" class="forgot-password" style="text-decoration: none;">Forgot password
                    ?</a>
            </div>

            <!-- SIGN IN BUTTON (orange) -->
            <button type="submit" class="btn btn-primary" id="loginBtn">
                <i class="fas fa-arrow-right-to-bracket"></i> Sign in
            </button>


            <div class="divider">
                <span>or</span>
            </div>

            <!-- Back to Homepage – original link kept, now as subtle outline button -->
            <a href="{{ url('/') }}" class="btn btn-secondary" style="text-decoration: none;">
                <i class="fas fa-home"></i> Back to Homepage
            </a>
        </form>
    </div>


    <!-- jQuery (required for Select2) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // Destroy any existing Select2 instance first
            if ($('#school_id').hasClass("select2-hidden-accessible")) {
                $('#school_id').select2('destroy');
            }

            // Initialize Select2 with better positioning
            $('#school_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Choose your school',
                allowClear: true,
                dropdownParent: $('#schoolDropdownContainer'),
                dropdownAutoWidth: true,
                minimumResultsForSearch: 0, // Always show search
                closeOnSelect: true
            });

            // Handle role change
            const roleButtons = document.querySelectorAll('.role-btn');

            roleButtons.forEach(button => {
                button.addEventListener('click', function () {
                    setTimeout(() => {
                        const role = this.getAttribute('data-role');
                        if (role === 'school') {
                            // Small delay to ensure DOM is updated
                            setTimeout(() => {
                                if ($('#school_id').hasClass("select2-hidden-accessible")) {
                                    $('#school_id').select2('destroy');
                                }
                                $('#school_id').select2({
                                    theme: 'bootstrap-5',
                                    width: '100%',
                                    placeholder: 'Choose your school',
                                    allowClear: true,
                                    dropdownParent: $('#schoolDropdownContainer'),
                                    dropdownAutoWidth: true,
                                    minimumResultsForSearch: 0,
                                    closeOnSelect: true
                                });
                            }, 100);
                        }
                    }, 50);
                });
            });
        });

        $(document).ready(function () {
            // Hide the custom arrow when select2 is active
            $('#school_id').on('select2:open select2:close select2:opening', function () {
                $('.select-arrow').css('opacity', '0.5');
            }).on('select2:closing', function () {
                $('.select-arrow').css('opacity', '1');
            });
        });
    </script>

    <!-- ============ ORIGINAL JAVASCRIPT – FULLY INTACT + NEW SCHOOL DROPDOWN LOGIC ============ -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ------------------------------
            // 1. ROLE SELECTOR – updates hidden input, labels & placeholders, and toggles school dropdown
            // ------------------------------
            const roleButtons = document.querySelectorAll('.role-btn');
            const roleInput = document.getElementById('login_role');
            const usernameLabel = document.querySelector('label[for="username"]');
            const usernameInput = document.getElementById('username');
            const passwordLabel = document.querySelector('label[for="password"]');

            // New elements for school dropdown
            const schoolDropdownContainer = document.getElementById('schoolDropdownContainer');
            const schoolSelect = document.getElementById('school_id');

            function updateFormForRole(role) {
                // Toggle school dropdown visibility
                if (role === 'school') {
                    schoolDropdownContainer.classList.add('visible');
                    // Make school select required when visible
                    if (schoolSelect) schoolSelect.required = true;
                } else {
                    schoolDropdownContainer.classList.remove('visible');
                    // Remove required attribute when hidden
                    if (schoolSelect) schoolSelect.required = false;
                }

                // Update labels and placeholders based on role
                switch (role) {
                    case 'student':
                        usernameLabel.textContent = 'REGISTRATION NUMBER';
                        usernameInput.placeholder = 'Enter your student registration number';
                        passwordLabel.textContent = 'STUDENT PASSWORD';
                        break;
                    case 'school':
                        usernameLabel.textContent = 'TEACHER PHONE NUMBER';
                        usernameInput.placeholder = 'Enter teacher phonenumber';
                        passwordLabel.textContent = 'TEACHER PASSWORD';
                        break;
                    case 'admin':
                        usernameLabel.textContent = 'ADMINISTRATOR ID / EMAIL';
                        usernameInput.placeholder = 'Enter your administrator credentials';
                        passwordLabel.textContent = 'ADMIN PASSWORD';
                        break;
                }
            }

            // Set initial active role
            let activeRole = 'school';
            roleInput.value = activeRole;
            updateFormForRole(activeRole);

            roleButtons.forEach(button => {
                button.addEventListener('click', function () {
                    roleButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    activeRole = this.getAttribute('data-role');
                    roleInput.value = activeRole;
                    updateFormForRole(activeRole);
                });
            });

            // ------------------------------
            // 2. PASSWORD TOGGLE (eye icon)
            // ------------------------------
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    const eyeIcon = this.querySelector('i');
                    if (type === 'text') {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                });
            }

            // ------------------------------
            // 3. AJAX FORM SUBMISSION (spinner, error display, redirect)
            // ------------------------------
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const originalBtnHtml = loginBtn.innerHTML;

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Clear previous errors
                document.querySelectorAll('.error-text').forEach(el => el.textContent = '');

                // Validate school dropdown if school role is selected
                if (activeRole === 'school' && schoolSelect && !schoolSelect.value) {
                    const errorEl = document.getElementById('school_id-error');
                    if (errorEl) {
                        errorEl.textContent = 'Please select a school';
                    }
                    return;
                }

                // Disable button + show spinner
                loginBtn.disabled = true;
                loginBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Signing in...`;

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) throw data;
                        return data;
                    })
                    .then(data => {
                        if (data.status && data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            throw { message: 'Redirect missing' };
                        }
                    })
                    .catch(data => {
                        // Re-enable button on error
                        loginBtn.disabled = false;
                        loginBtn.innerHTML = originalBtnHtml;

                        // Validation errors
                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                const errorEl = document.getElementById(`${key}-error`);
                                if (errorEl) {
                                    errorEl.textContent = data.errors[key][0];
                                }
                            });
                        }
                        // General error
                        if (data.message && !data.errors) {
                            alert(data.message);
                        }
                    });
            });

                    //             form.addEventListener('submit', function (e) {
                    //     e.preventDefault();

                    //     document.querySelectorAll('.error-text').forEach(el => el.textContent = '');

                    //     if (activeRole === 'school' && schoolSelect && !schoolSelect.value) {
                    //         const errorEl = document.getElementById('school_id-error');
                    //         if (errorEl) errorEl.textContent = 'Please select a school';
                    //         return;
                    //     }

                    //     loginBtn.disabled = true;
                    //     loginBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Signing in...`;

                    //     const formData = new FormData(form);

                    //     fetch(form.action, {
                    //         method: 'POST',
                    //         headers: {
                    //             'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    //             'Accept': 'application/json'
                    //         },
                    //         body: formData
                    //     })
                    //     .then(async response => {
                    //         if (!response.ok) {
                    //             // throw the full Response object so we can get HTML in catch
                    //             throw response;
                    //         }
                    //         return response.json(); // normal JSON success
                    //     })
                    //     .then(data => {
                    //         if (data.status && data.redirect) {
                    //             window.location.href = data.redirect;
                    //         } else {
                    //             throw { message: 'Redirect missing' };
                    //         }
                    //     })
                    //     .catch(async err => {
                    //         // Re-enable button
                    //         loginBtn.disabled = false;
                    //         loginBtn.innerHTML = originalBtnHtml;

                    //         // If err is a Response, it means server returned HTML
                    //         if (err.text) {
                    //             const text = await err.text();
                    //             document.body.innerHTML = text; // dump Laravel dd() output
                    //         } else if (err.errors) {
                    //             // normal validation errors
                    //             Object.keys(err.errors).forEach(key => {
                    //                 const errorEl = document.getElementById(`${key}-error`);
                    //                 if (errorEl) errorEl.textContent = err.errors[key][0];
                    //             });
                    //         } else if (err.message) {
                    //             alert(err.message);
                    //         }
                    //     });
                    // });
        });
    </script>
</body>

</html>
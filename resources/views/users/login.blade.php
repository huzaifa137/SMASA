<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SMASA </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        :root {
            --orange: #2C29CA;
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

        .welcome-text {
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

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

        .school-dropdown-container {
            margin-bottom: 1.75rem;
            display: none;
        }

        .school-dropdown-container.visible {
            display: block;
        }

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
            z-index: 2;
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

        /* ── SELECT2 STYLES (single clean set, matches forgot password) ── */
        .select-wrapper {
            position: relative;
            display: block;
            isolation: isolate;
        }

        .select-wrapper .input-icon {
            z-index: 1070 !important;
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            font-size: 1.05rem;
            pointer-events: none;
        }

        .select2-container {
            z-index: 9999 !important;
        }

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

        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: var(--orange) !important;
            box-shadow: 0 0 0 4px rgba(44, 41, 202, 0.08) !important;
            outline: none !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: var(--orange) !important;
            border-radius: var(--radius-sm) !important;
            box-shadow: var(--shadow) !important;
            margin-top: 4px !important;
            overflow: hidden;
        }

        .select2-container--bootstrap-5 .select2-results__option {
            padding: 10px 15px !important;
            font-size: 0.9rem !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--selected {
            background-color: var(--orange-subtle) !important;
            color: var(--orange-dark) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--orange) !important;
            color: white !important;
        }

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
            box-shadow: 0 0 0 3px rgba(44, 41, 202, 0.08) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            display: none !important;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="brand">SM<span>A</span>SA</div>
        <div class="welcome-text">
            <i class="fas fa-hand-sparkles" style="color: var(--orange);"></i> Welcome to SM<span
                style="color:var(--orange)">A</span>SA! <br>
            Please sign-in to your account
        </div>

        <div class="user-role-selector">
            <button class="role-btn active" data-role="school">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>School</span>
            </button>
            <button class="role-btn" data-role="admin">
                <i class="fas fa-user-cog"></i>
                <span>Admin</span>
            </button>
        </div>

        <form class="login-form" id="loginForm" action="{{ route('auth-user-check') }}" method="POST">
            @csrf
            <input type="hidden" name="role" id="login_role" value="student">

            <!-- School Dropdown -->
            <div class="school-dropdown-container" id="schoolDropdownContainer">
                <label for="school_id" class="form-label">SELECT SCHOOL</label>
                <div class="select-wrapper">
                    <i class="fas fa-school input-icon"></i>
                    <select name="school_id" id="school_id" class="select2">
                        <option value="" disabled selected>Choose your school</option>

                        @foreach($schools as $school)
                            <option value="{{ $school->ID }}">
                                {{ $school->House }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <small class="error-text" id="school_id-error"></small>
            </div>

            <div class="form-group">
                <label for="username" class="form-label" id="usernameLabel">REGISTRATION NUMBER</label>
                <div class="input-group">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="username" name="username" class="form-input"
                        placeholder="Enter your student registration number">
                </div>
                <small class="error-text" id="username-error"></small>
            </div>

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

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="{{route('forgot-password')}}" class="forgot-password" style="text-decoration: none;">Forgot
                    password ?</a>
            </div>

            <button type="submit" class="btn btn-primary" id="loginBtn">
                <i class="fas fa-arrow-right-to-bracket"></i> Sign in
            </button>

            <div class="divider"><span>or</span></div>

            <a href="{{ url('/') }}" class="btn btn-secondary" style="text-decoration: none;">
                <i class="fas fa-home"></i> Back to Homepage
            </a>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // ── Single Select2 init, same pattern as forgot password ──
            $('#school_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Choose your school',
                allowClear: true,
                dropdownParent: $('body'),
                minimumResultsForSearch: 0,
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleButtons = document.querySelectorAll('.role-btn');
            const roleInput = document.getElementById('login_role');
            const usernameLabel = document.querySelector('label[for="username"]');
            const usernameInput = document.getElementById('username');
            const passwordLabel = document.querySelector('label[for="password"]');
            const schoolDropdownContainer = document.getElementById('schoolDropdownContainer');
            const schoolSelect = document.getElementById('school_id');

            function updateFormForRole(role) {
                if (role === 'school') {
                    schoolDropdownContainer.classList.add('visible');
                    if (schoolSelect) schoolSelect.required = true;
                } else {
                    schoolDropdownContainer.classList.remove('visible');
                    if (schoolSelect) schoolSelect.required = false;
                }
                switch (role) {
                    case 'student':
                        usernameLabel.textContent = 'REGISTRATION NUMBER';
                        usernameInput.placeholder = 'Enter your student registration number';
                        passwordLabel.textContent = 'STUDENT PASSWORD';
                        break;
                    case 'school':
                        usernameLabel.textContent = 'TEACHER PHONE NUMBER';
                        usernameInput.placeholder = 'Enter teacher phone number';
                        passwordLabel.textContent = 'TEACHER PASSWORD';
                        break;
                    case 'admin':
                        usernameLabel.textContent = 'ADMINISTRATOR ID / EMAIL';
                        usernameInput.placeholder = 'Enter your administrator credentials';
                        passwordLabel.textContent = 'ADMIN PASSWORD';
                        break;
                }
            }

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

            // Password toggle
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    const eyeIcon = this.querySelector('i');
                    eyeIcon.classList.toggle('fa-eye', type === 'password');
                    eyeIcon.classList.toggle('fa-eye-slash', type === 'text');
                });
            }

            // AJAX form submission
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const originalBtnHtml = loginBtn.innerHTML;

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                document.querySelectorAll('.error-text').forEach(el => el.textContent = '');

                if (activeRole === 'school' && schoolSelect && !schoolSelect.value) {
                    const errorEl = document.getElementById('school_id-error');
                    if (errorEl) errorEl.textContent = 'Please select a school';
                    return;
                }

                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

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
                        loginBtn.disabled = false;
                        loginBtn.innerHTML = originalBtnHtml;
                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                const errorEl = document.getElementById(`${key}-error`);
                                if (errorEl) errorEl.textContent = data.errors[key][0];
                            });
                        }
                        if (data.message && !data.errors) {
                            alert(data.message);
                        }
                    });
            });
        });
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password | SMASA</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />

    <!-- Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* ─────────────────────────────────────────────────────────────
           SMASA Design System – exact same CSS variables as login page
        ───────────────────────────────────────────────────────────── */
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
            --danger: #dc2626;
            --success: #16a34a;
            --radius: 20px;
            --radius-sm: 12px;
            --shadow: 0 20px 40px -12px rgba(44, 41, 202, 0.12), 0 8px 24px -6px rgba(0, 0, 0, 0.04);
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

        /* ── Card ── */
        .card {
            max-width: 520px;
            width: 100%;
            background: var(--white);
            border-radius: 32px;
            padding: 2.5rem 2.2rem;
            box-shadow: var(--shadow);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.005);
        }

        /* ── Brand ── */
        .brand {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--black);
            margin-bottom: 0.4rem;
        }

        .brand span {
            color: var(--orange);
        }

        .subtitle {
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        /* ── Step indicator ── */
        .steps {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--gray-500);
        }

        .step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--gray-100);
            color: var(--gray-500);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .step.active .step-num {
            background: var(--orange);
            color: white;
        }

        .step.active {
            color: var(--orange);
        }

        .step-divider {
            flex: 1;
            height: 2px;
            background: var(--gray-100);
            border-radius: 2px;
        }

        /* ── Form ── */
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
            color: var(--gray-900);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(44, 41, 202, 0.08);
        }

        .input-icon {
            position: absolute;
            left: 1.2rem;
            color: var(--gray-500);
            font-size: 1.05rem;
            pointer-events: none;
            z-index: 2;
        }

        .error-text {
            display: block;
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 6px;
            margin-left: 12px;
            font-weight: 500;
        }

        /* ── Alert banners ── */
        .alert {
            display: none;
            padding: 0.9rem 1.1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert.show {
            display: flex;
        }

        .alert-success {
            background: rgba(22, 163, 74, 0.08);
            border-left: 4px solid var(--success);
            color: #15803d;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.08);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        /* ── Buttons ── */
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
            box-shadow: 0 8px 16px -4px rgba(44, 41, 202, 0.28);
            margin-bottom: 1rem;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--orange-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -6px rgba(44, 41, 202, 0.36);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: transparent;
            color: var(--gray-900);
            border: 2px solid var(--gray-300);
            box-shadow: none;
        }

        .btn-secondary:hover {
            border-color: var(--orange);
            color: var(--orange);
            background: rgba(44, 41, 202, 0.04);
            transform: translateY(-2px);
        }

        /* ── Security info box ── */
        .info-box {
            background: rgba(44, 41, 202, 0.05);
            border-left: 3px solid var(--orange);
            border-radius: var(--radius-sm);
            padding: 1rem 1.2rem;
            margin-bottom: 1.75rem;
            font-size: 0.87rem;
            color: var(--gray-700);
            line-height: 1.6;
        }

        .info-box strong {
            color: var(--orange-dark);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 0.3rem;
        }

        /* ── Select2 overrides (matching login page) ── */
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
            box-shadow: 0 0 0 4px rgba(44, 41, 202, 0.08) !important;
        }

        .select2-container {
            z-index: 1050 !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: var(--orange) !important;
            border-radius: var(--radius-sm) !important;
            box-shadow: var(--shadow);
            margin-top: 4px !important;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
            z-index: 1;
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

        /* ── Back link ── */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 1.5rem;
            color: var(--gray-500);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .back-link a {
            color: var(--orange);
            font-weight: 700;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* ── Success state card ── */
        .success-state {
            display: none;
            text-align: center;
        }

        .success-state.show {
            display: block;
        }

        .form-fields.hidden {
            display: none;
        }

        .success-icon-wrap {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(22, 163, 74, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .success-icon-wrap i {
            font-size: 2.5rem;
            color: var(--success);
        }

        .success-state h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--gray-900);
        }

        .success-state p {
            color: var(--gray-700);
            font-size: 0.97rem;
            line-height: 1.7;
            margin-bottom: 0.5rem;
        }

        .email-highlight {
            display: inline-block;
            background: rgba(44, 41, 202, 0.07);
            color: var(--orange-dark);
            font-weight: 700;
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.95rem;
        }

        .checklist {
            text-align: left;
            background: var(--gray-100);
            border-radius: var(--radius-sm);
            padding: 1rem 1.25rem;
            margin: 1.25rem 0;
            list-style: none;
        }

        .checklist li {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            font-size: 0.875rem;
            color: var(--gray-700);
            padding: 0.3rem 0;
        }

        .checklist li i {
            color: var(--success);
            margin-top: 2px;
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .card {
                padding: 2rem 1.4rem;
            }

            .brand {
                font-size: 1.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="card">

        <!-- ── Brand ── -->
        <div class="brand">SM<span>A</span>SA</div>
        <div class="subtitle">
            <i class="fas fa-shield-alt" style="color: var(--orange);"></i>
            Teacher Account Recovery
        </div>

        <!-- ── Step indicator ── -->
        <div class="steps">
            <div class="step active">
                <div class="step-num">1</div>
                <span>Identify Account</span>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-num">2</div>
                <span>Check Email</span>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-num">3</div>
                <span>Set New Password</span>
            </div>
        </div>

        <!-- ── Alert banners ── -->
        <div class="alert alert-danger" id="errorAlert" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <div id="errorText">Something went wrong.</div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- ── SUCCESS STATE (shown after submission) ── -->
        <div class="success-state" id="successState">
            <div class="success-icon-wrap">
                <i class="fas fa-paper-plane"></i>
            </div>
            <h2>Check Your Inbox!</h2>
            <p>
                If <span class="email-highlight" id="sentEmailDisplay"></span> is registered under the selected school,
                we've sent a password reset link to that address.
            </p>

            <ul class="checklist">
                <li><i class="fas fa-check-circle"></i> Check your inbox and spam/junk folder</li>
                <li><i class="fas fa-check-circle"></i> The link expires in <strong>60 minutes</strong></li>
                <li><i class="fas fa-check-circle"></i> Only the most recent link will work</li>
                <li><i class="fas fa-check-circle"></i> Do not share the link with anyone</li>
            </ul>

            <p style="font-size:0.85rem; color:var(--gray-500);">
                Didn't receive it?
                <a href="#" id="tryAgainLink" style="color:var(--orange); font-weight:700;">Try again</a>
                or contact your school administrator.
            </p>

            <br>
            <a href="{{ route('users.login') }}" class="btn btn-secondary"
                style="text-decoration:none; margin-top:0.5rem;">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>

        <!-- ── FORM FIELDS ── -->
        <div class="form-fields" id="formFields">

            <div class="info-box">
                <strong><i class="fas fa-info-circle"></i> How it works</strong>
                Select your school from the dropdown, enter your registered email address, and we'll send you
                a secure link to reset your password. The link expires in <strong>60 minutes</strong>.
            </div>

            <form id="forgotPasswordForm" novalidate>
                @csrf

                <!-- School dropdown (same as login page) -->
                <div class="form-group">
                    <label class="form-label" for="school_id">
                        <i class="fas fa-school" style="color:var(--orange); margin-right:4px;"></i> SELECT YOUR SCHOOL
                    </label>
                    <div class="select-wrapper">
                        <i class="fas fa-school input-icon"></i>
                        <select name="school_id" id="school_id" class="select2">
                            <option value="" disabled selected>Choose your school</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->ID }}">{{ $school->House }}</option>
                            @endforeach
                        </select>
                    </div>
                    <small class="error-text" id="school_id-error"></small>
                </div>

                <!-- Email field -->
                <div class="form-group">
                    <label class="form-label" for="email">EMAIL ADDRESS</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-input"
                            placeholder="Enter your registered email address" autocomplete="email" required>
                    </div>
                    <small class="error-text" id="email-error"></small>
                </div>

                <!-- Submit button -->
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>

                <div style="text-align:center; color:var(--gray-500); font-size:0.8rem; margin-bottom:1rem;">
                    ── or ──
                </div>

                <a href="{{ route('users.login') }}" class="btn btn-secondary" style="text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </form>

        </div><!-- /form-fields -->

    </div><!-- /card -->

    <!-- jQuery + Select2 + SweetAlert -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {

            // ── Initialize Select2 ──────────────────────────────────────────────
            $('#school_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Choose your school',
                allowClear: true,
                dropdownParent: $('body'),
                minimumResultsForSearch: 0,
            });

            // ── Form submission ─────────────────────────────────────────────────
            $('#forgotPasswordForm').on('submit', function (e) {

                e.preventDefault();

                const email = $('#email').val().trim();
                const schoolId = $('#school_id').val();
                const submitBtn = $('#submitBtn');

                // Clear previous inline errors
                $('.error-text').text('');

                // ── Client-side validation ──────────────────────────────────────
                let valid = true;

                if (!schoolId) {
                    $('#school_id-error').text('Please select your school.');
                    valid = false;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!email || !emailRegex.test(email)) {
                    $('#email-error').text('Please enter a valid email address.');
                    valid = false;
                }

                if (!valid) return;

                // ── Confirmation dialog ─────────────────────────────────────────
                Swal.fire({
                    title: 'Send Reset Link?',
                    html: `We'll send a secure reset link to:<br><strong>${email}</strong>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2C29CA',
                    cancelButtonColor: '#71717a',
                    confirmButtonText: 'Yes, send it',
                    cancelButtonText: 'Cancel',

                }).then((result) => {

                    if (!result.isConfirmed) return;

                    // ── Loading state ───────────────────────────────────────────
                    const originalHtml = submitBtn.html();
                    submitBtn.prop('disabled', true);
                    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...');

                    // ── AJAX REQUEST ────────────────────────────────────────────
                    $.ajax({
                        url: '{{ route("teacher.send.reset.link") }}',
                        type: 'POST',
                        data: $('#forgotPasswordForm').serialize(),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },

                        success: function (data) {

                            if (data.success) {
                                // ── Show success state ──────────────────────────
                                $('#sentEmailDisplay').text(email);
                                $('#formFields').addClass('hidden');
                                $('#successState').addClass('show');
                                window.scrollTo({ top: 0, behavior: 'smooth' });

                            } else {
                                // ── Unexpected success=false with 200 status ────
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Something Went Wrong',
                                    text: data.message || 'Please try again.',
                                    confirmButtonColor: '#2C29CA',
                                    confirmButtonText: 'Try Again',
                                });
                            }

                        },

                        error: function (xhr) {

                            let errorTitle = 'Error';
                            let errorMessage = 'Something went wrong. Please try again.';

                            if (xhr.responseJSON) {

                                // ── Laravel validation errors (422) ────────────
                                if (xhr.responseJSON.errors) {
                                    errorTitle = 'Validation Error';
                                    errorMessage = '';
                                    Object.values(xhr.responseJSON.errors).forEach(function (errorArray) {
                                        errorMessage += errorArray[0] + '<br>';
                                    });

                                    // ── Our custom error messages (404, 500, etc.) ──
                                } else if (xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;

                                    // Give a more specific title based on HTTP status
                                    if (xhr.status === 404) {
                                        errorTitle = 'Account Not Found';
                                    } else if (xhr.status === 500) {
                                        errorTitle = 'Mail Error';
                                    }
                                }
                            }

                            Swal.fire({
                                icon: 'error',
                                title: errorTitle,
                                html: errorMessage,
                                confirmButtonColor: '#2C29CA',
                                confirmButtonText: 'Try Again',
                            });

                        },
                        // error: function (data) {
                        //     $('body').html(data.responseText);
                        // },

                        complete: function () {
                            submitBtn.prop('disabled', false);
                            submitBtn.html(originalHtml);
                        }

                    });

                });

            });

            // ── "Try again" link in success state ──────────────────────────────
            $('#tryAgainLink').on('click', function (e) {
                e.preventDefault();
                $('#successState').removeClass('show');
                $('#formFields').removeClass('hidden');
                $('#forgotPasswordForm')[0].reset();
                $('#school_id').val(null).trigger('change');
            });

        });
    </script>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set New Password | SMASA</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />

    <style>
        /* ─────────────────────────────────────────────────────────────────────
           Same SMASA design system tokens as login & forgot-password pages
        ───────────────────────────────────────────────────────────────────── */
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
            --warning: #d97706;
            --radius: 20px;
            --radius-sm: 12px;
            --shadow: 0 20px 40px -12px rgba(44, 41, 202, 0.12), 0 8px 24px -6px rgba(0,0,0,0.04);
            --transition: all 0.2s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(145deg, #f0fdf4 0%, #ecfdf5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            max-width: 520px;
            width: 100%;
            background: var(--white);
            border-radius: 32px;
            padding: 2.5rem 2.2rem;
            box-shadow: var(--shadow);
        }

        /* ── Brand ── */
        .brand { font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; color: var(--black); margin-bottom: 0.4rem; }
        .brand span { color: var(--orange); }
        .subtitle { font-size: 1rem; font-weight: 500; color: var(--gray-700); margin-bottom: 2rem; line-height: 1.6; }

        /* ── Step indicator ── */
        .steps { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; }
        .step { display: flex; align-items: center; gap: 0.4rem; font-size: 0.78rem; font-weight: 600; color: var(--gray-500); }
        .step-num { width: 24px; height: 24px; border-radius: 50%; background: var(--gray-100); color: var(--gray-500); display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; }
        .step.done .step-num { background: var(--success); color: white; }
        .step.done { color: var(--success); }
        .step.active .step-num { background: var(--orange); color: white; }
        .step.active { color: var(--orange); }
        .step-divider { flex: 1; height: 2px; background: var(--gray-100); border-radius: 2px; }
        .step-divider.done { background: var(--success); }

        /* ── School badge ── */
        .school-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(44, 41, 202, 0.07);
            color: var(--orange-dark);
            font-size: 0.83rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            margin-bottom: 1.75rem;
        }

        /* ── Form ── */
        .form-group { margin-bottom: 1.75rem; }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-900);
            margin-bottom: 0.6rem;
        }

        .input-group { position: relative; display: flex; align-items: center; }

        .form-input {
            width: 100%;
            padding: 1rem 3rem 1rem 3rem;
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

        .form-input.is-valid { border-color: var(--success); }
        .form-input.is-invalid { border-color: var(--danger); }

        .input-icon { position: absolute; left: 1.2rem; color: var(--gray-500); font-size: 1.05rem; pointer-events: none; }

        .password-toggle {
            position: absolute;
            right: 1.2rem;
            background: none;
            border: none;
            color: var(--gray-500);
            font-size: 1.05rem;
            cursor: pointer;
            padding: 0;
        }

        .password-toggle:hover { color: var(--orange); }

        .error-text { display: block; color: var(--danger); font-size: 0.8rem; margin-top: 6px; margin-left: 12px; font-weight: 500; }

        /* ── Password strength meter ── */
        .strength-wrap { margin-top: 0.75rem; padding: 0 0.5rem; }

        .strength-bar {
            display: flex;
            gap: 4px;
            margin-bottom: 6px;
        }

        .strength-bar span {
            flex: 1;
            height: 4px;
            border-radius: 4px;
            background: var(--gray-200, #e4e4e7);
            transition: background 0.3s;
        }

        .strength-label { font-size: 0.78rem; font-weight: 600; color: var(--gray-500); }

        .strength-label.weak    { color: var(--danger); }
        .strength-label.fair    { color: var(--warning); }
        .strength-label.good    { color: #2563eb; }
        .strength-label.strong  { color: var(--success); }

        /* ── Requirements checklist ── */
        .requirements {
            background: var(--gray-100);
            border-radius: var(--radius-sm);
            padding: 0.9rem 1.1rem;
            margin-bottom: 1.75rem;
            list-style: none;
        }

        .requirements li {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 0.83rem;
            color: var(--gray-500);
            padding: 0.2rem 0;
            transition: color 0.2s;
        }

        .requirements li i { font-size: 0.8rem; width: 14px; }
        .requirements li.met { color: var(--success); }
        .requirements li.met i::before { content: '\f058'; }  /* fa-circle-check */
        .requirements li i::before { content: '\f111'; }       /* fa-circle (empty) */

        /* ── Alert banners ── */
        .alert { display: none; padding: 0.9rem 1.1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 500; align-items: flex-start; gap: 0.75rem; }
        .alert.show { display: flex; }
        .alert-danger { background: rgba(220,38,38,.08); border-left: 4px solid var(--danger); color: var(--danger); }
        .alert-success { background: rgba(22,163,74,.08); border-left: 4px solid var(--success); color: #15803d; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 1rem 1.5rem; border: none; border-radius: 40px;
            font-weight: 700; font-size: 1rem; cursor: pointer; transition: var(--transition);
            text-decoration: none;
        }
        .btn-primary { background: var(--orange); color: white; box-shadow: 0 8px 16px -4px rgba(44,41,202,.28); margin-bottom: 1rem; }
        .btn-primary:hover:not(:disabled) { background: var(--orange-dark); transform: translateY(-2px); }
        .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
        .btn-secondary { background: transparent; color: var(--gray-900); border: 2px solid var(--gray-300); }
        .btn-secondary:hover { border-color: var(--orange); color: var(--orange); transform: translateY(-2px); }

        /* ── Success state ── */
        .success-state { display: none; text-align: center; }
        .success-state.show { display: block; }
        .form-fields.hidden { display: none; }

        .success-icon-wrap { width: 90px; height: 90px; border-radius: 50%; background: rgba(22,163,74,.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .success-icon-wrap i { font-size: 2.5rem; color: var(--success); }

        .countdown {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin-top: 1rem;
        }

        .countdown strong { color: var(--orange); }

        @media (max-width: 480px) { .card { padding: 2rem 1.4rem; } .brand { font-size: 1.9rem; } }
    </style>
</head>

<body>
    <div class="card">

        <!-- ── Brand ── -->
        <div class="brand">SM<span>A</span>SA</div>
        <div class="subtitle">
            <i class="fas fa-key" style="color: var(--orange);"></i>
            Set Your New Password
        </div>

        <!-- ── Step indicator ── -->
        <div class="steps">
            <div class="step done">
                <div class="step-num"><i class="fas fa-check" style="font-size:0.65rem;"></i></div>
                <span>Identified</span>
            </div>
            <div class="step-divider done"></div>
            <div class="step done">
                <div class="step-num"><i class="fas fa-check" style="font-size:0.65rem;"></i></div>
                <span>Email Sent</span>
            </div>
            <div class="step-divider"></div>
            <div class="step active">
                <div class="step-num">3</div>
                <span>New Password</span>
            </div>
        </div>

        <!-- ── School context badge ── -->
        @if(!empty($schoolName))
        <div>
            <span class="school-badge">
                <i class="fas fa-school"></i> {{ $schoolName }}
            </span>
        </div>
        @endif

        <!-- ── Alert banners ── -->
        <div class="alert alert-danger" id="errorAlert" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <div id="errorText">Something went wrong.</div>
        </div>

        <!-- ── SUCCESS STATE ── -->
        <div class="success-state" id="successState">
            <div class="success-icon-wrap">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="font-size:1.6rem; font-weight:700; margin-bottom:0.75rem;">Password Updated!</h2>
            <p style="color:var(--gray-700); line-height:1.7; margin-bottom:1rem;">
                Your password has been changed successfully. You can now sign in with your new password.
            </p>
            <p class="countdown">
                Redirecting to login in <strong id="countdownNum">5</strong> seconds...
            </p>
            <br>
            <a href="{{ route('users.login') }}" class="btn btn-primary" id="goToLoginBtn" style="text-decoration:none;">
                <i class="fas fa-sign-in-alt"></i> Sign In Now
            </a>
        </div>

        <!-- ── FORM ── -->
        <div class="form-fields" id="formFields">

            <form id="resetPasswordForm" novalidate>
                @csrf
                <!-- Pass token + school_id securely as hidden fields -->
                <input type="hidden" name="token"     value="{{ $token }}">
                <input type="hidden" name="school_id" value="{{ $school_id }}">

                <!-- New Password -->
                <div class="form-group">
                    <label class="form-label" for="password">NEW PASSWORD</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-input"
                               placeholder="Enter your new password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <!-- Strength meter -->
                    <div class="strength-wrap">
                        <div class="strength-bar">
                            <span id="sb1"></span>
                            <span id="sb2"></span>
                            <span id="sb3"></span>
                            <span id="sb4"></span>
                        </div>
                        <span class="strength-label" id="strengthLabel">Enter a password</span>
                    </div>
                    <small class="error-text" id="password-error"></small>
                </div>

                <!-- Password requirements -->
                <ul class="requirements" id="requirements">
                    <li id="req-len"><i class="fas"></i> At least 4 characters</li>
                    <li id="req-upper"><i class="fas"></i> One uppercase letter (A–Z)</li>
                    <li id="req-lower"><i class="fas"></i> One lowercase letter (a–z)</li>
                    <li id="req-num"><i class="fas"></i> One number (0–9)</li>
                    <li id="req-special"><i class="fas"></i> One special character (@$!%*?&#)</li>
                </ul>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">CONFIRM NEW PASSWORD</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-input" placeholder="Repeat your new password"
                               autocomplete="new-password" required>
                        <button type="button" class="password-toggle" id="toggleConfirm" aria-label="Toggle confirm password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="error-text" id="confirm-error"></small>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Update Password
                </button>

                <div style="text-align:center; color:var(--gray-500); font-size:0.8rem; margin-bottom:1rem;">── or ──</div>

                <a href="{{ route('users.login') }}" class="btn btn-secondary" style="text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </form>

        </div><!-- /form-fields -->

    </div><!-- /card -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ── Password toggles ────────────────────────────────────────────────
        function setupToggle(toggleId, inputId) {
            document.getElementById(toggleId).addEventListener('click', function () {
                const input = document.getElementById(inputId);
                const icon  = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        }

        setupToggle('togglePassword', 'password');
        setupToggle('toggleConfirm', 'password_confirmation');

        // ── Password strength meter + requirements checker ──────────────────
        const bars      = ['sb1','sb2','sb3','sb4'].map(id => document.getElementById(id));
        const barColors = ['#dc2626', '#d97706', '#2563eb', '#16a34a'];
        const reqMap = {
            'req-len':     p => p.length >= 4,
            // 'req-upper':   p => /[A-Z]/.test(p),
            // 'req-lower':   p => /[a-z]/.test(p),
            // 'req-num':     p => /[0-9]/.test(p),
            // 'req-special': p => /[@$!%*?&#]/.test(p),
        };

        document.getElementById('password').addEventListener('input', function () {
            const p = this.value;
            let score = 0;

            // Requirements
            Object.entries(reqMap).forEach(([id, fn]) => {
                const li = document.getElementById(id);
                if (fn(p)) { li.classList.add('met'); score++; }
                else         { li.classList.remove('met'); }
            });

            // Bars
            bars.forEach((bar, i) => {
                bar.style.background = i < score ? barColors[Math.min(score - 1, 3)] : 'var(--gray-300, #d4d4d8)';
            });

            // Label
            const label  = document.getElementById('strengthLabel');
            const labels = ['', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
            label.textContent = p.length === 0 ? 'Enter a password' : (labels[score] || 'Very Strong');
            label.className   = 'strength-label ' + ['','weak','fair','good','strong','strong'][score] || 'strong';

            // Live confirm check
            checkConfirmMatch();
        });

        // ── Live match indicator on confirm field ───────────────────────────
        document.getElementById('password_confirmation').addEventListener('input', checkConfirmMatch);

        function checkConfirmMatch() {
            const pw  = document.getElementById('password').value;
            const cpw = document.getElementById('password_confirmation').value;
            const conf = document.getElementById('password_confirmation');

            if (cpw === '') {
                conf.classList.remove('is-valid', 'is-invalid');
                return;
            }

            if (pw === cpw) {
                conf.classList.add('is-valid');
                conf.classList.remove('is-invalid');
                document.getElementById('confirm-error').textContent = '';
            } else {
                conf.classList.add('is-invalid');
                conf.classList.remove('is-valid');
            }
        }

        // ── Form submission ─────────────────────────────────────────────────
        document.getElementById('resetPasswordForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            // Clear errors
            document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
            document.getElementById('errorAlert').classList.remove('show');

            const password = document.getElementById('password').value;
            const confirm  = document.getElementById('password_confirmation').value;
            const submitBtn = document.getElementById('submitBtn');

            // ── Client validation ──
            let valid = true;

            const failedReq = Object.entries(reqMap).find(([, fn]) => !fn(password));
            if (!password || failedReq) {
                document.getElementById('password-error').textContent =
                    'Password does not meet the requirements listed above.';
                valid = false;
            }

            if (password !== confirm) {
                document.getElementById('confirm-error').textContent = 'Passwords do not match.';
                valid = false;
            }

            if (!valid) return;

            // ── Confirmation ──
            const result = await Swal.fire({
                title: 'Update Password?',
                text: 'Are you sure you want to set this as your new password?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2C29CA',
                cancelButtonColor: '#71717a',
                confirmButtonText: '<i class="fas fa-save"></i> Yes, update it',
                cancelButtonText: 'Cancel',
            });

            if (!result.isConfirmed) return;

            // ── Loading ──
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            try {
                const formData = new FormData(document.getElementById('resetPasswordForm'));

                const response = await fetch('{{ route("teacher.password.reset") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // ── Show success state and start countdown ──
                    document.getElementById('formFields').classList.add('hidden');
                    document.getElementById('successState').classList.add('show');

                    let secs = 5;
                    const counter = document.getElementById('countdownNum');
                    const interval = setInterval(() => {
                        secs--;
                        counter.textContent = secs;
                        if (secs <= 0) {
                            clearInterval(interval);
                            window.location.href = '{{ route("users.login") }}';
                        }
                    }, 1000);

                } else if (response.status === 422 && data.errors) {
                    // Laravel validation errors
                    Object.entries(data.errors).forEach(([field, msgs]) => {
                        const el = document.getElementById(field + '-error') ||
                                   document.getElementById('errorText');
                        if (el) el.textContent = msgs[0];
                    });
                    document.getElementById('errorAlert').classList.add('show');
                } else {
                    showError(data.message || 'Something went wrong. The link may have expired.');
                }

            } catch (err) {
                showError('A network error occurred. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });

        function showError(message) {
            const alert = document.getElementById('errorAlert');
            document.getElementById('errorText').textContent = message;
            alert.classList.add('show');
            alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>

</body>
</html>

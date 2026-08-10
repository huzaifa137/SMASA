<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set Your Password - SMASA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    <style>
        :root {
            --brand: #2C29CA;
            --brand-mid: #5351e4;
            --gray-900: #18181b;
            --gray-500: #71717a;
            --gray-300: #d4d4d8;
            --radius: 20px;
            --shadow: 0 20px 40px -12px rgba(44, 41, 202, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-mid) 55%, #8b89f0 100%);
            padding: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 420px;
            padding: 2.25rem 2rem;
        }

        .icon-badge {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
            margin: 0 auto 1rem;
        }

        h1 {
            text-align: center;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.3rem;
        }

        .subtitle {
            text-align: center;
            font-size: 0.85rem;
            color: var(--gray-500);
            margin-bottom: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--gray-500);
            margin-bottom: 0.4rem;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-300);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 0.9rem 0.75rem 2.5rem;
            border: 1.5px solid var(--gray-300);
            border-radius: 0.7rem;
            font-size: 0.92rem;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--brand);
        }

        .error-text {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.3rem;
            display: block;
        }

        .btn-primary {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            color: #fff;
            border: none;
            border-radius: 0.7rem;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.4rem;
        }

        .hint {
            font-size: 0.78rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
        }

        .pp-alert {
            border-radius: 0.7rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .pp-alert-info {
            background: #ede9ff;
            color: #14136e;
            border: 1px solid #d8d4ff;
        }

        .btn-primary {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            color: #fff;
            border: none;
            border-radius: 0.7rem;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.4rem;
        }

        .btn-outline {
            width: 100%;
            padding: 0.85rem;
            background: transparent;
            color: var(--brand);
            border: 1.5px solid var(--brand);
            border-radius: 0.7rem;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            text-decoration: none;
        }

        .btn-outline:hover {
            background: var(--brand);
            color: #fff;
        }

.password-toggle {
    position: absolute;
    right: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: var(--gray-300);
    cursor: pointer;
    padding: 0.25rem;
    font-size: 1rem;
}

.password-toggle:hover {
    color: var(--brand);
}

.form-input.has-toggle {
    padding-right: 2.8rem;
}
.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
    </style>
</head>

<body>
    <div class="card">
        <div class="icon-badge"><i class="fas fa-key"></i></div>
        <h1>Set Your Password</h1>
        <p class="subtitle">Choose a new password for your parent portal account.</p>

        @if (session('info'))
            <div class="pp-alert pp-alert-info"><i class="fas fa-circle-info me-1"></i> {{ session('info') }}</div>
        @endif

<form action="{{ route('parents.change-password.submit') }}" method="POST" id="changePasswordForm">
    @csrf

    <div class="form-group">
        <label class="form-label">New Password</label>

        <div class="input-group">
            <i class="fas fa-lock input-icon"></i>

            <input
                type="password"
                name="password"
                id="password"
                class="form-input has-toggle"
                placeholder="At least 4 characters"
                required
                minlength="4"
            >

            <button
                type="button"
                class="password-toggle"
                onclick="togglePassword('password', 'eyePassword')"
            >
                <i class="fas fa-eye" id="eyePassword"></i>
            </button>
        </div>

        @error('password')
            <small class="error-text">{{ $message }}</small>
        @enderror

        <p class="hint">
            Minimum 4 characters — letters or numbers, no special characters required.
        </p>
    </div>

    <div class="form-group">
        <label class="form-label">Confirm Password</label>

        <div class="input-group">
            <i class="fas fa-lock input-icon"></i>

            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="form-input has-toggle"
                placeholder="Type it again"
                required
                minlength="4"
            >

            <button
                type="button"
                class="password-toggle"
                onclick="togglePassword('password_confirmation', 'eyeConfirmPassword')"
            >
                <i class="fas fa-eye" id="eyeConfirmPassword"></i>
            </button>
        </div>

        @error('password_confirmation')
            <small class="error-text">{{ $message }}</small>
        @enderror
    </div>

    <button type="submit" class="btn-primary" id="savePasswordBtn">
        <i class="fas fa-check"></i>
        <span>Save Password</span>
    </button>

    <a href="{{ url('/users/login') }}" class="btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</form>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.getElementById('changePasswordForm').addEventListener('submit', function () {
        if (!this.checkValidity()) {
            return;
        }

        const button = document.getElementById('savePasswordBtn');

        button.disabled = true;
        button.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            <span>Saving...</span>
        `;
    });

    // Browsers may restore this page from the back/forward cache (bfcache)
    // instead of re-requesting it from the server. When that happens the
    // page shows exactly what was last rendered — including whatever flash
    // message ("Welcome! Please set your own password...") was on screen
    // at the time — even though that message has already been consumed on
    // the server. Force a real reload in that case so the page always
    // reflects the current session state instead of a stale snapshot.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>





    </div>
</body>

</html>
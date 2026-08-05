<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Parent Login - SMASA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    <style>
        :root {
            --brand: #2C29CA;
            --brand-dark: #14136e;
            --brand-mid: #5351e4;
            --gray-900: #18181b;
            --gray-500: #71717a;
            --gray-300: #d4d4d8;
            --gray-100: #f4f4f5;
            --radius: 20px;
            --shadow: 0 20px 40px -12px rgba(44, 41, 202, 0.15), 0 8px 24px -6px rgba(0, 0, 0, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        h1 { text-align: center; font-size: 1.3rem; font-weight: 800; color: var(--gray-900); margin-bottom: 0.3rem; }
        .subtitle { text-align: center; font-size: 0.85rem; color: var(--gray-500); margin-bottom: 1.75rem; }

        .form-group { margin-bottom: 1.1rem; }
        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--gray-500);
            margin-bottom: 0.4rem;
        }

        .input-group { position: relative; }
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
            transition: border-color 0.2s ease;
        }

        .form-input:focus { outline: none; border-color: var(--brand); }

        .password-toggle {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
        }

        .error-text { color: #ef4444; font-size: 0.75rem; margin-top: 0.3rem; display: block; }

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

        .helper-text {
            text-align: center;
            font-size: 0.78rem;
            color: var(--gray-500);
            margin-top: 1.5rem;
        }

        .helper-text a { color: var(--brand-mid); font-weight: 600; text-decoration: none; }

        .pp-alert {
            border-radius: 0.7rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .pp-alert-fail { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .pp-alert-info { background: #ede9ff; color: #14136e; border: 1px solid #d8d4ff; }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon-badge"><i class="fas fa-people-roof"></i></div>
        <h1>Parent / Guardian Portal</h1>
        <p class="subtitle">Check your child's results, attendance and fees.</p>

        @if (session('fail'))
            <div class="pp-alert pp-alert-fail"><i class="fas fa-circle-exclamation me-1"></i> {{ session('fail') }}</div>
        @endif
        @if (session('info'))
            <div class="pp-alert pp-alert-info"><i class="fas fa-circle-info me-1"></i> {{ session('info') }}</div>
        @endif

        <form action="{{ route('parents.login.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <div class="input-group">
                    <i class="fas fa-phone input-icon"></i>
                    <input type="text" name="phone" class="form-input" placeholder="The number on file with your child's school"
                        value="{{ old('phone') }}" required autofocus>
                </div>
                @error('phone')<small class="error-text">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-input" placeholder="Your password" required>
                    <button type="button" class="password-toggle" id="togglePassword"><i class="fas fa-eye"></i></button>
                </div>
                @error('password')<small class="error-text">{{ $message }}</small>@enderror
            </div>

            <button type="submit" class="btn-primary"><i class="fas fa-arrow-right-to-bracket"></i> Sign in</button>
        </form>

        <p class="helper-text">
            First time here? Use the phone number registered with your child's school and password
            <strong>1234</strong> — you'll be asked to set your own right after.
        </p>
        <p class="helper-text"><a href="{{ url('/users/login') }}"><i class="fas fa-arrow-left me-1"></i>Back to staff login</a></p>
    </div>

    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye', type === 'password');
            icon.classList.toggle('fa-eye-slash', type === 'text');
        });
    </script>
</body>

</html>

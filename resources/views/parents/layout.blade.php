<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Parent Portal') — SMASA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    <style>
        :root {
            --brand: #2C29CA;
            --brand-dark: #14136e;
            --brand-mid: #5351e4;
            --brand-pale: #ede9ff;
            --brand-ultra: #f8f7ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-900: #18181b;
            --gray-700: #3f3f46;
            --gray-500: #71717a;
            --gray-300: #d4d4d8;
            --gray-100: #f4f4f5;
            --radius: 1rem;
            --shadow: 0 8px 30px rgba(44, 41, 202, 0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            color: var(--gray-900);
            min-height: 100vh;
        }

        a { color: inherit; }

        .pp-topbar {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-mid) 100%);
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .pp-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.05rem;
        }

        .pp-brand img { height: 32px; border-radius: 6px; background: #fff; padding: 2px; }

        .pp-topbar-right {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .pp-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.4rem 0.85rem;
            border-radius: 99px;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .pp-pill:hover { background: rgba(255, 255, 255, 0.28); color: #fff; }

        .pp-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.5rem 1.25rem 3rem;
        }

        .pp-crumbs {
            font-size: 0.82rem;
            color: var(--gray-500);
            margin-bottom: 1rem;
        }

        .pp-crumbs a { color: var(--brand-mid); text-decoration: none; font-weight: 600; }

        .pp-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .pp-child-strip {
            display: flex;
            gap: 0.6rem;
            overflow-x: auto;
            padding-bottom: 0.4rem;
            margin-bottom: 1.25rem;
        }

        .pp-child-chip {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #fff;
            border: 2px solid var(--brand-pale);
            border-radius: 99px;
            padding: 0.45rem 1rem 0.45rem 0.5rem;
            text-decoration: none;
            color: var(--gray-900);
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .pp-child-chip:hover, .pp-child-chip.active {
            border-color: var(--brand);
            background: var(--brand-pale);
            color: var(--brand-dark);
        }

        .pp-child-chip .avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--brand);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .pp-tabs {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .pp-tab {
            padding: 0.55rem 1.1rem;
            border-radius: 0.6rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--gray-700);
            background: #fff;
            border: 1px solid var(--brand-pale);
        }

        .pp-tab:hover { background: var(--brand-pale); color: var(--brand-dark); }
        .pp-tab.active { background: var(--brand); color: #fff; border-color: var(--brand); }

        .pp-alert {
            border-radius: 0.75rem;
            padding: 0.85rem 1.1rem;
            margin-bottom: 1.25rem;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .pp-alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .pp-alert-fail { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .pp-alert-info { background: var(--brand-pale); color: var(--brand-dark); border: 1px solid #d8d4ff; }

        .pp-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }

        .pp-stat {
            background: var(--brand-ultra);
            border-radius: 0.85rem;
            padding: 1rem 1.15rem;
            border-left: 4px solid var(--brand);
        }

        .pp-stat.success { border-left-color: var(--success); }
        .pp-stat.warning { border-left-color: var(--warning); }
        .pp-stat.danger { border-left-color: var(--danger); }

        .pp-stat-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--gray-500);
            margin-bottom: 0.3rem;
        }

        .pp-stat-value { font-size: 1.5rem; font-weight: 800; }

        table.pp-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        table.pp-table th {
            text-align: left;
            background: var(--brand-ultra);
            color: var(--brand-dark);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 0.65rem 0.7rem;
            border-bottom: 2px solid var(--brand-pale);
        }
        table.pp-table td { padding: 0.6rem 0.7rem; border-bottom: 1px solid #f1f0ff; }
        table.pp-table tbody tr:hover { background: var(--brand-ultra); }

        .pp-empty {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--gray-500);
        }
        .pp-empty i { font-size: 2.25rem; color: var(--brand-pale); margin-bottom: 0.6rem; display: block; }

        .pp-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            border-radius: 0.6rem;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .pp-btn-primary { background: linear-gradient(135deg, var(--brand), var(--brand-mid)); color: #fff; }
        .pp-btn-outline { background: #fff; color: var(--brand); border: 1px solid var(--brand-pale); }

        @media (max-width: 600px) {
            .pp-topbar { padding: 0.75rem 1rem; }
            .pp-container { padding: 1rem 0.85rem 2.5rem; }
        }
    </style>
    @yield('head')
</head>

<body>
    <div class="pp-topbar">
        <a href="{{ route('parents.dashboard') }}" class="pp-brand">
            <img src="{{ URL::asset('assets/images/brand/logo.png') }}" alt="SMASA">
            <span>Parent Portal</span>
        </a>
        <div class="pp-topbar-right">
            <a href="{{ route('parents.dashboard') }}" class="pp-pill"><i class="fas fa-house"></i> My Children</a>
            <a href="{{ route('parents.change-password') }}" class="pp-pill"><i class="fas fa-key"></i> Password</a>
            <a href="{{ route('parents.logout') }}" class="pp-pill"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="pp-container">
        @if (session('success'))
            <div class="pp-alert pp-alert-success"><i class="fas fa-circle-check me-1"></i> {{ session('success') }}</div>
        @endif
        @if (session('fail'))
            <div class="pp-alert pp-alert-fail"><i class="fas fa-circle-exclamation me-1"></i> {{ session('fail') }}</div>
        @endif
        @if (session('info'))
            <div class="pp-alert pp-alert-info"><i class="fas fa-circle-info me-1"></i> {{ session('info') }}</div>
        @endif

        @yield('content')
    </div>
</body>

</html>

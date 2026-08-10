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
            --sidebar-width: 264px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            color: var(--gray-900);
            min-height: 100vh;
        }

        a {
            color: inherit;
        }

        /* ── Shell: fixed sidebar + main column ── */
        .pp-shell {
            display: flex;
            min-height: 100vh;
        }

        .pp-sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--brand-dark) 0%, var(--brand) 100%);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 40;
            transition: transform 0.25s ease;
        }

        .pp-sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 1.35rem 1.25rem;
            text-decoration: none;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .pp-sidebar-brand img {
            height: 32px;
            border-radius: 6px;
            background: #fff;
            padding: 2px;
        }

        .pp-sidebar-brand span {
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: -0.01em;
        }

        .pp-sidebar-brand small {
            display: block;
            font-weight: 500;
            font-size: 0.68rem;
            opacity: 0.7;
        }

        .pp-sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.85rem 1.25rem;
        }

        .pp-sidebar-heading {
            font-size: 0.66rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.55);
            padding: 1.1rem 0.6rem 0.4rem;
        }

        .pp-nav-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.65rem 0.75rem;
            border-radius: 0.65rem;
            font-size: 0.86rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            margin-bottom: 0.2rem;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .pp-nav-link i {
            width: 18px;
            text-align: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .pp-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .pp-nav-link.active {
            background: #fff;
            color: var(--brand-dark);
        }

        .pp-nav-link.logout:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #fff;
        }

        /* Per-school groups of children */
        .pp-nav-school {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: rgba(255, 255, 255, 0.5);
            padding: 0.6rem 0.75rem 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .pp-nav-child {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.6rem;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 0.15rem;
        }

        .pp-nav-child:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .pp-nav-child.active {
            background: #fff;
            color: var(--brand-dark);
        }

        .pp-nav-child .avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .pp-nav-child.active .avatar {
            background: var(--brand-pale);
            color: var(--brand-dark);
        }

        .pp-nav-child-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pp-sidebar-empty {
            padding: 0.5rem 0.75rem;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.55);
            line-height: 1.45;
        }

        .pp-sidebar-footer {
            padding: 0.85rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        /* ── Main column ── */
        .pp-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .pp-topbar {
            background: #fff;
            border-bottom: 1px solid var(--gray-300);
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .pp-topbar-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .pp-sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--gray-700);
            cursor: pointer;
            padding: 0.3rem 0.5rem;
        }

        .pp-topbar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pp-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--brand-pale);
            color: var(--brand-dark);
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.4rem 0.85rem;
            border-radius: 99px;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .pp-pill:hover {
            background: #e0dcff;
        }

        .pp-sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 10, 20, 0.45);
            z-index: 30;
        }

        .pp-container {
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            padding: 1.5rem 1.25rem 3rem;
        }

        .pp-crumbs {
            font-size: 0.82rem;
            color: var(--gray-500);
            margin-bottom: 1rem;
        }

        .pp-crumbs a {
            color: var(--brand-mid);
            text-decoration: none;
            font-weight: 600;
        }

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

        .pp-child-chip:hover,
        .pp-child-chip.active {
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

        .pp-tab:hover {
            background: var(--brand-pale);
            color: var(--brand-dark);
        }

        .pp-tab.active {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
        }

        .pp-alert {
            border-radius: 0.75rem;
            padding: 0.85rem 1.1rem;
            margin-bottom: 1.25rem;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .pp-alert-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .pp-alert-fail {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .pp-alert-info {
            background: var(--brand-pale);
            color: var(--brand-dark);
            border: 1px solid #d8d4ff;
        }

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

        .pp-stat.success {
            border-left-color: var(--success);
        }

        .pp-stat.warning {
            border-left-color: var(--warning);
        }

        .pp-stat.danger {
            border-left-color: var(--danger);
        }

        .pp-stat-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--gray-500);
            margin-bottom: 0.3rem;
        }

        .pp-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
        }

        table.pp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

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

        table.pp-table td {
            padding: 0.6rem 0.7rem;
            border-bottom: 1px solid #f1f0ff;
        }

        table.pp-table tbody tr:hover {
            background: var(--brand-ultra);
        }

        .pp-empty {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--gray-500);
        }

        .pp-empty i {
            font-size: 2.25rem;
            color: var(--brand-pale);
            margin-bottom: 0.6rem;
            display: block;
        }

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

        .pp-btn-primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            color: #fff;
        }

        .pp-btn-outline {
            background: #fff;
            color: var(--brand);
            border: 1px solid var(--brand-pale);
        }

        @media (max-width: 900px) {
            .pp-sidebar {
                transform: translateX(-100%);
            }

            .pp-sidebar.open {
                transform: translateX(0);
            }

            .pp-sidebar-backdrop.open {
                display: block;
            }

            .pp-main {
                margin-left: 0;
            }

            .pp-sidebar-toggle {
                display: inline-flex;
            }
        }

        @media (max-width: 600px) {
            .pp-topbar {
                padding: 0.75rem 1rem;
            }

            .pp-container {
                padding: 1rem 0.85rem 2.5rem;
            }
        }

        /* Add school separation */
        .pp-nav-school {
            border-top: 1px solid #ffffff;
            margin-top: 0.5rem;
            padding-top: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #ffffff;
            margin-bottom: 0.25rem;
        }

        .pp-nav-school:first-of-type {
            border-top: none;
            margin-top: 0;
            padding-top: 0.25rem;
        }

        /* Make school headers more visible */
        .pp-nav-school {
            color: #ffffff !important;
            font-size: 0.72rem;
            letter-spacing: 0.05em;
        }

        .pp-nav-school i {
            opacity: 0.6;
            font-size: 0.8rem;
        }
    </style>
    @yield('head')
</head>

<body>
    <div class="pp-shell">
        {{-- ═══════════════════════ SIDEBAR ═══════════════════════
        Navigation here is data-driven: it only lists the schools and
        children this logged-in parent account actually has access to
        (resolved once via the "parents.*" view composer), rather than
        a static menu. ════════════════════════════════════════════ --}}
        <div class="pp-sidebar-backdrop" id="ppSidebarBackdrop"></div>

        <aside class="pp-sidebar" id="ppSidebar">
            <a href="{{ route('parents.dashboard') }}" class="pp-sidebar-brand">
                <img src="{{ URL::asset('assets/images/brand/logo.png') }}" alt="SMASA">
                <span>
                    SMASA
                    <small>Parent Portal</small>
                </span>
            </a>

            <div class="pp-sidebar-scroll">
                <div class="pp-sidebar-heading">Overview</div>
                <a href="{{ route('parents.dashboard') }}"
                    class="pp-nav-link {{ request()->routeIs('parents.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-house"></i> My Children
                </a>

                {{-- ── Access-driven: children grouped by school ── --}}
                @if (isset($sidebarChildren) && $sidebarChildren->count())
                    <div class="pp-sidebar-heading">My Children</div>
                    @foreach ($sidebarChildren as $schoolId => $kids)
                        <div class="pp-nav-school">
                            <i class="fas fa-school"></i> {{ $kids->first()->school_name ?? 'School' }}
                        </div>
                        @foreach ($kids as $child)
                            @php $isActiveChild = (string) ($sidebarActiveStudentId ?? '') === (string) $child->id; @endphp
                            <a href="{{ route('parents.child', $child->id) }}"
                                class="pp-nav-child {{ $isActiveChild ? 'active' : '' }}">
                                <span
                                    class="avatar">{{ strtoupper(substr($child->firstname, 0, 1) . substr($child->lastname, 0, 1)) }}</span>
                                <span class="pp-nav-child-name">{{ $child->firstname }} {{ $child->lastname }}</span>
                            </a>
                        @endforeach
                    @endforeach
                @else
                    <div class="pp-sidebar-heading">My Children</div>
                    <div class="pp-sidebar-empty">
                        <i class="fas fa-user-slash me-1"></i> No children linked to this phone number yet. Contact
                        your child's school if this doesn't look right.
                    </div>
                @endif
            </div>

            <div class="pp-sidebar-footer">
                <a href="{{ route('parents.change-password') }}"
                    class="pp-nav-link {{ request()->routeIs('parents.change-password') ? 'active' : '' }}">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <a href="{{ route('parents.logout') }}" class="pp-nav-link logout">
                    <i class="fas fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </aside>

        {{-- ═══════════════════════ MAIN COLUMN ═══════════════════════ --}}
        <div class="pp-main">
            <div class="pp-topbar">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <button class="pp-sidebar-toggle" id="ppSidebarToggle" aria-label="Toggle menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="pp-topbar-title">@yield('title', 'Parent Portal')</span>
                </div>
                <div class="pp-topbar-right">
                    <a href="{{ route('parents.logout') }}" class="pp-pill"><i class="fas fa-right-from-bracket"></i>
                        Logout</a>
                </div>
            </div>

            <div class="pp-container">
                @if (session('success'))
                    <div class="pp-alert pp-alert-success"><i class="fas fa-circle-check me-1"></i> {{ session('success') }}
                    </div>
                @endif
                @if (session('fail'))
                    <div class="pp-alert pp-alert-fail"><i class="fas fa-circle-exclamation me-1"></i> {{ session('fail') }}
                    </div>
                @endif
                @if (session('info'))
                    <div class="pp-alert pp-alert-info"><i class="fas fa-circle-info me-1"></i> {{ session('info') }}</div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('ppSidebar');
            const backdrop = document.getElementById('ppSidebarBackdrop');
            const toggle = document.getElementById('ppSidebarToggle');

            function closeSidebar() {
                sidebar.classList.remove('open');
                backdrop.classList.remove('open');
            }

            toggle?.addEventListener('click', function () {
                sidebar.classList.toggle('open');
                backdrop.classList.toggle('open');
            });

            backdrop?.addEventListener('click', closeSidebar);
        })();
    </script>
    @yield('scripts')
</body>

</html>
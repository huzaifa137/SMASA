@extends('layouts-side-bar.master')

@section('title', 'Notifications')

@section('css')
    {{-- Include the same fonts and icons as the dashboard --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ── Copy the entire style block from the dashboard ── */
        :root {
            --lib-blue: #2c29ca;
            --lib-blue-l: rgba(44, 41, 202, .12);
            --lib-blue-d: #2420a8;
            --lib-amber: #f59e0b;
            --lib-amber-l: rgba(245, 158, 11, .12);
            --lib-rose: #f43f5e;
            --lib-rose-l: rgba(244, 63, 94, .12);
            --lib-violet: #7c3aed;
            --lib-violet-l: rgba(124, 58, 237, .12);
            --lib-green: #10b981;
            --lib-green-l: rgba(16, 185, 129, .12);
            --surface: #ffffff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 20px rgba(0, 0, 0, .05);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, .10);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
        }

        /* ── Hero (optional, we'll keep a light version) ── */
        .lib-hero-sm {
            background: linear-gradient(135deg, #1a1869 0%, #2c29ca 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.75rem;
            margin-top: 1.75rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .lib-hero-sm h4 {
            font-weight: 700;
            margin: 0;
        }

        .lib-hero-sm small {
            color: rgba(255, 255, 255, .7);
        }

        .lib-hero-sm .btn-primary-lib {
            background: #fff;
            color: var(--lib-blue);
        }

        .lib-hero-sm .btn-primary-lib:hover {
            background: #f0f0ff;
            color: var(--lib-blue-d);
        }

        /* ── Stat Grid ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        @media(max-width:900px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:540px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.4rem 1.5rem;
            box-shadow: var(--shadow);
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .stat-card .val {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-1);
            line-height: 1;
        }

        .stat-card .label {
            font-size: .8rem;
            color: var(--text-3);
            margin-top: .3rem;
            font-weight: 500;
        }

        .stat-card .sub {
            font-size: .75rem;
            color: var(--text-3);
            margin-top: .4rem;
        }

        /* ── Cards ── */
        .lib-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            margin-top: 1.5rem;
            overflow: hidden;
        }

        .lib-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbff;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .lib-card-header h6 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-1);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .lib-card-header h6 i {
            color: var(--lib-blue);
        }

        .lib-card-body {
            padding: 1.25rem 1.5rem;
        }

        .lib-card-footer {
            padding: 0.75rem 1.5rem;
            background: #fafbff;
            border-top: 1px solid var(--border);
        }

        /* ── Table ── */
        .lib-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
        }

        .lib-table th {
            padding: .75rem 1rem;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid var(--border);
            background: #3431ca;
            color: #FFF;
        }

        .lib-table td {
            padding: .85rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: var(--text-1);
        }

        .lib-table tr:last-child td {
            border-bottom: none;
        }

        .lib-table tr:hover td {
            background: #f8faff;
        }

        /* ── Badges (reuse dashboard badges) ── */
        .badge-lib {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .22rem .65rem;
            border-radius: 20px;
            font-size: .73rem;
            font-weight: 600;
        }

        .badge-teal {
            background: var(--lib-blue-l);
            color: var(--lib-blue);
        }

        .badge-amber {
            background: var(--lib-amber-l);
            color: var(--lib-amber);
        }

        .badge-rose {
            background: var(--lib-rose-l);
            color: var(--lib-rose);
        }

        .badge-green {
            background: var(--lib-green-l);
            color: var(--lib-green);
        }

        .badge-violet {
            background: var(--lib-violet-l);
            color: var(--lib-violet);
        }

        .badge-gray {
            background: #f1f5f9;
            color: var(--text-2);
        }

        /* ── Buttons ── */
        .btn-lib {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.1rem;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }

        .btn-primary-lib {
            background: linear-gradient(135deg, #2c29ca, #2420a8);
            color: #fff;
        }

        .btn-primary-lib:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(44, 41, 202, .4);
            color: #fff;
        }

        .btn-sm-lib {
            padding: .35rem .75rem;
            font-size: .78rem;
        }

        .btn-outline-lib {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-2);
        }

        .btn-outline-lib:hover {
            border-color: var(--lib-blue);
            color: var(--lib-blue);
        }

        .btn-danger-lib {
            background: var(--lib-rose);
            color: #fff;
        }

        .btn-danger-lib:hover {
            background: #e11d48;
            color: #fff;
        }

        /* ── Misc ── */
        .text-muted-custom {
            color: var(--text-3) !important;
        }

        .fw-semibold {
            font-weight: 600;
        }

        .gap-2 {
            gap: .5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .align-items-start {
            align-items: flex-start;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .mt-1 {
            margin-top: .25rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .p-0 {
            padding: 0;
        }

        .p-2 {
            padding: .5rem;
        }

        .p-3 {
            padding: 1rem;
        }

        .py-3 {
            padding-top: .75rem;
            padding-bottom: .75rem;
        }

        .py-5 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--text-3);
        }

        .small {
            font-size: .8rem;
        }

        .fs-4 {
            font-size: 1.5rem;
        }

        .fa-lg {
            font-size: 1.25em;
        }

        .d-block {
            display: block;
        }

        .rounded-circle {
            border-radius: 50%;
        }

        .lib-table td.actions-cell {
    white-space: nowrap;
}

    </style>
@endsection

@section('content')
    <div class="container-fluid px-0">

        {{-- Hero-like Header (same style as dashboard but smaller) --}}
        <div class="lib-hero-sm d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fas fa-bell me-2"></i> Notifications</h4>
                <small>Manage and broadcast notifications to staff & students</small>
            </div>
            <a href="{{ route('notifications.create') }}" class="btn-lib btn-primary-lib">
                <i class="fas fa-paper-plane me-1"></i> Send Notification
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="icon" style="background:var(--lib-blue-l); color:#3431ca;">
                    <i class="fas fa-bell text-primary"></i>
                </div>
                <div class="val">{{ $stats['total'] }}</div>
                <div class="label">Total Sent</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background:var(--lib-green-l);color:var(--lib-green);">
                    <i class="fas fa-calendar-day text-success"></i>
                </div>
                <div class="val">{{ $stats['today'] }}</div>
                <div class="label">Sent Today</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background:var(--lib-amber-l);color:var(--lib-amber);">
                    <i class="fas fa-envelope-open text-warning"></i>
                </div>
                <div class="val">{{ $stats['unread'] }}</div>
                <div class="label">Unread Deliveries</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background:var(--lib-violet-l);color:var(--lib-violet);">
                    <i class="fas fa-layer-group text-dark"></i>
                </div>
                <div class="val">{{ count($stats['byType']) }}</div>
                <div class="label">Types Used</div>
            </div>
        </div>

        {{-- Notifications Table --}}
        <div class="lib-card">
            <div class="lib-card-header">
                <h6><i class="fas fa-list-ul"></i> All Notifications</h6>
                {{-- Optional extra button --}}
            </div>
            <div class="lib-card-body p-0">
                <div style="overflow-x:auto;">
                    <table class="lib-table">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Notification</th>
                                <th>Type</th>
                                <th>Module</th>
                                <th>Recipients</th>
                                <th>Sent</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notif)
                                <tr>
                                    <td class="text-muted small">{{ $notif->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge-lib badge-{{ $notif->color }} p-2 mt-1"
                                                style="background:{{ $notif->color === 'teal' ? 'var(--lib-blue-l)' : '' }};
                                                                 color:{{ $notif->color === 'teal' ? 'var(--lib-blue)' : '' }};">
                                                <i class="fas fa-{{ $notif->icon }}"></i>
                                            </span>
                                            <div>
                                                <div class="fw-semibold">{{ $notif->title }}</div>
                                                <div class="text-muted small">{{ \Str::limit($notif->body, 80) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-lib badge-{{ $notif->color }}">
                                            {{ ucfirst($notif->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $notif->module ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge-lib badge-gray">{{ $notif->recipients_count }} recipients</span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $notif->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td style="white-space: nowrap;">
    <a href="{{ route('notifications.show', $notif->id) }}"
        class="btn-lib btn-sm-lib btn-outline-lib">
        <i class="fas fa-eye"></i>
    </a>
    <button class="btn-lib btn-sm-lib btn-danger-lib"
        onclick="deleteNotification({{ $notif->id }})">
        <i class="fas fa-trash"></i>
    </button>
</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                                        No notifications sent yet.
                                        <a href="{{ route('notifications.create') }}">Send your first one</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($notifications->hasPages())
                <div class="lib-card-footer">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
     </div>
    </div>

    {{-- Delete Form (hidden) --}}
    <form id="deleteForm" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>
@endsection

@section('js')
    <script>
        function deleteNotification(id) {
            Swal.fire({
                title: 'Delete Notification?',
                text: 'This will remove the notification and all its delivery records.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete it',
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteForm');
                    form.action = `/notifications/${id}`;
                    form.submit();
                }
            });
        }
    </script>
@endsection
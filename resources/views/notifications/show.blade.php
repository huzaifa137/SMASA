@extends('layouts-side-bar.master')

@section('title', 'Notification Details')

@php
    use App\Models\User;
    use App\Models\Teacher;
    use App\Models\Student;
    use App\Helpers\PermissionHelper;

    $sender = null;
    if ($notification->triggered_by) {
        $sender = User::find($notification->triggered_by);
        if (!$sender) {
            $t = Teacher::find($notification->triggered_by);
            if ($t) $sender = (object) ['name' => trim($t->firstname.' '.$t->surname)];
        }
    }

    // Batch-resolve recipient names for the current page to avoid N+1 queries
    $teacherIds = $recipients->where('recipient_type', 'teacher')->pluck('recipient_id')->unique();
    $studentIds = $recipients->where('recipient_type', 'student')->pluck('recipient_id')->unique();
    $adminIds   = $recipients->where('recipient_type', 'admin')->pluck('recipient_id')->unique();

    $teacherNames = Teacher::whereIn('id', $teacherIds)->get()->mapWithKeys(fn($t) => [$t->id => trim($t->firstname.' '.$t->surname)]);
    $studentNames = Student::whereIn('id', $studentIds)->get()->mapWithKeys(fn($s) => [$s->id => trim($s->firstname.' '.$s->lastname)]);
    $adminNames   = User::whereIn('id', $adminIds)->get()->mapWithKeys(fn($u) => [$u->id => $u->name]);

    $resolveName = function ($type, $id) use ($teacherNames, $studentNames, $adminNames) {
        return match ($type) {
            'teacher' => $teacherNames[$id] ?? 'Unknown Teacher',
            'student' => $studentNames[$id] ?? 'Unknown Student',
            'admin'   => $adminNames[$id] ?? 'Unknown Admin',
            default   => 'Unknown',
        };
    };
@endphp

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ── Same style block as dashboard ── */
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

        /* ── Hero ── */
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

        .lib-hero-sm .btn-outline-lib {
            border-color: rgba(255, 255, 255, .3);
            color: #fff;
        }

        .lib-hero-sm .btn-outline-lib:hover {
            background: rgba(255, 255, 255, .1);
            border-color: #fff;
            color: #fff;
        }

        .lib-hero-sm .btn-danger-lib {
            background: var(--lib-rose);
            color: #fff;
        }

        .lib-hero-sm .btn-danger-lib:hover {
            background: #e11d48;
            color: #fff;
        }

        /* ── Stat Grid ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        @media(max-width:768px) {
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
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
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

        /* ── Cards ── */
        .lib-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
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

        /* ── Badges ── */
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

        .badge-light {
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

        .mb-1 {
            margin-bottom: .25rem;
        }

        .mb-2 {
            margin-bottom: .5rem;
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

        .py-4 {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
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

        .fs-3 {
            font-size: 1.5rem;
        }

        .fa-lg {
            font-size: 1.25em;
        }

        .d-block {
            display: block;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        .bg-white {
            background: #fff;
        }

        .text-success {
            color: var(--lib-green) !important;
        }

        .text-warning {
            color: var(--lib-amber) !important;
        }

        .text-dark {
            color: var(--text-1) !important;
        }

        .alert {
            border-radius: var(--radius);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-0">

        {{-- Hero Header with Back & Delete buttons --}}
        <div class="lib-hero-sm d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4><i class="fas fa-bell me-2"></i> Notification Details</h4>
                <small>View notification and its delivery status</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('notifications.index') }}" class="btn-lib btn-outline-lib">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                @if(PermissionHelper::canFeature('delete_notification'))
                <button class="btn-lib btn-danger-lib" onclick="deleteNotification({{ $notification->id }})">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Notification Details Card --}}
        <div class="lib-card">
            <div class="lib-card-body">
                <div class="d-flex align-items-start gap-3">
                    <span class="badge-lib badge-{{ $notification->color }} p-3" style="font-size:1.2rem; border-radius:12px;">
                        <i class="fas fa-{{ $notification->icon }}"></i>
                    </span>
                    <div class="flex-grow-1">
                        <h5 class="mb-1 fw-semibold">{{ $notification->title }}</h5>
                        <p class="text-muted mb-2">{{ $notification->body }}</p>
                        <div class="d-flex flex-wrap gap-3 small text-muted">
                            <span><span class="badge-lib badge-{{ $notification->color }}">{{ ucfirst($notification->type) }}</span></span>
                            <span><i class="fas fa-layer-group me-1"></i>{{ $notification->module ?? '—' }}</span>
                            <span><i class="fas fa-user me-1"></i>Sent by {{ $sender->name ?? 'System' }}</span>
                            <span><i class="fas fa-clock me-1"></i>{{ $notification->created_at->format('d M Y, h:i A') }}</span>
                            @if($notification->url)
                                <a href="{{ $notification->url }}" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-link me-1"></i>Open link
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delivery Stats --}}
        <div class="stat-grid mb-4">
            <div class="stat-card">
                <div class="val">{{ $deliveryStats['total'] }}</div>
                <div class="label">Total Recipients</div>
            </div>
            <div class="stat-card">
                <div class="val" style="color:var(--lib-green);">{{ $deliveryStats['read'] }}</div>
                <div class="label">Read</div>
            </div>
            <div class="stat-card">
                <div class="val" style="color:var(--lib-amber);">{{ $deliveryStats['unread'] }}</div>
                <div class="label">Unread</div>
            </div>
        </div>

        {{-- Recipients Table --}}
        <div class="lib-card">
            <div class="lib-card-header">
                <h6><i class="fas fa-users"></i> Recipients</h6>
                <span class="badge-lib badge-gray">{{ $recipients->total() }} total</span>
            </div>
            <div class="lib-card-body p-0">
                <div style="overflow-x:auto;">
                    <table class="lib-table">
                        <thead>
                            <tr>
                                <th>Recipient</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Read At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recipients as $r)
                                <tr>
                                    <td>{{ $resolveName($r->recipient_type, $r->recipient_id) }}</td>
                                    <td><span class="badge-lib badge-gray">{{ ucfirst($r->recipient_type) }}</span></td>
                                    <td>
                                        @if($r->is_read)
                                            <span class="badge-lib badge-green"><i class="fas fa-check me-1"></i>Read</span>
                                        @else
                                            <span class="badge-lib badge-amber"><i class="fas fa-clock me-1"></i>Unread</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $r->read_at ? $r->read_at->format('d M Y, h:i A') : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recipients found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recipients->hasPages())
                <div class="lib-card-footer">
                    {{ $recipients->links() }}
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
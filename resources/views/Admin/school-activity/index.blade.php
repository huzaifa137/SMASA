@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.school-activity._styles')

    <style>
        table thead th {
            background-color: #2C29CA !important;
            color: #FFFFFF !important;
        }

        /* ── Card grid: 4 per row on large screens ── */
        .sam-cards {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 14px !important;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .sam-cards { grid-template-columns: repeat(3, 1fr) !important; }
        }
        @media (max-width: 900px) {
            .sam-cards { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 560px) {
            .sam-cards { grid-template-columns: 1fr !important; }
        }

        .sam-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px 20px;
            text-decoration: none;
            color: inherit;
            transition: all .18s ease;
            box-shadow: 0 1px 2px rgba(16,24,40,.04);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .sam-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(44,41,202,.10);
            border-color: var(--sam-brand, #2C29CA);
        }
        .sam-card.on {
            border-color: var(--sam-brand, #2C29CA);
            background: linear-gradient(180deg, rgba(44,41,202,.05), transparent);
            box-shadow: 0 4px 14px rgba(44,41,202,.12);
        }
        .sam-card .n {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.1;
            color: #111827;
        }
        .sam-card .l {
            margin-top: 4px;
            font-size: .82rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* ── Beautified filter inputs ── */
        .sam-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .sam-filters input[type="text"],
        .sam-filters select {
            height: 38px;
            padding: 0 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff;
            color: #111827;
            font-size: .86rem;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            min-width: 130px;
        }
        .sam-filters input[type="text"]:focus,
        .sam-filters select:focus {
            border-color: var(--sam-brand, #2C29CA);
            box-shadow: 0 0 0 3px rgba(44,41,202,.12);
        }
        .sam-filters input[type="text"]::placeholder {
            color: #9ca3af;
        }
        .sam-filters select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 30px;
        }
        .sam-filters .sam-btn,
        .sam-filters .sam-btn.ghost {
            height: 38px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 16px;
            border-radius: 8px;
            font-size: .86rem;
            font-weight: 500;
            cursor: pointer;
        }
        .sam-filters input[name="q"] {
            min-width: 200px;
            flex: 1 1 180px;
        }

        /* ── Beautified action buttons (top right) ── */
        .sam-top-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .sam-top-actions .sam-btn,
        .sam-top-actions .sam-btn.ghost {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 40px;
            padding: 0 18px;
            border-radius: 9px;
            font-size: .88rem;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all .15s ease;
        }
        .sam-top-actions .sam-btn {
            background: var(--sam-brand, #2C29CA);
            color: #fff;
            border-color: var(--sam-brand, #2C29CA);
        }
        .sam-top-actions .sam-btn:hover {
            background: #1f1da0;
            border-color: #1f1da0;
        }
        .sam-top-actions .sam-btn.ghost {
            background: #fff;
            color: #374151;
            border-color: #d1d5db;
        }
        .sam-top-actions .sam-btn.ghost:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #111827;
        }

        /* ── Panel header layout ── */
        .sam-panel .hd {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }
    </style>

    @php
        $fmt = fn($c) => $c ? $c->format('d M Y, H:i') : '—';
        $ago = fn($c) => $c ? $c->diffForHumans() : 'Never';
        $q = fn(array $over = []) => array_filter(array_merge(request()->query(), ['page' => null], $over), fn($v) => $v !== null && $v !== '' && $v !== 'all');
    @endphp

    <div class="sam mt-5">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap">
            <div>
                <h1><i class="fas fa-heart-pulse" style="color:var(--sam-brand)"></i> School Activity</h1>
                <div class="sub">
                    Which schools are really using SMASA. Based on actions by school users in the last
                    {{ $cfg['window_days'] }} days; "Active" means an action within {{ $cfg['active_days'] }} days,
                    "Idle" within {{ $cfg['idle_days'] }}. Figures refresh every
                    {{ max(1, intdiv($cfg['cache_seconds'], 60)) }} min.
                </div>
            </div>
            <div class="sam-top-actions mb-3">
                <form method="POST" action="{{ route('school-activity.refresh') }}" style="margin:0">@csrf
                    <button class="sam-btn ghost" type="submit"><i class="fas fa-rotate"></i> Refresh now</button>
                </form>
                @if($canExport)
                    <a class="sam-btn" href="{{ route('school-activity.export', $q()) }}"><i class="fas fa-file-csv"></i> Export
                        CSV</a>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="sam-flash">{{ session('success') }}</div>@endif

        {{-- Status cards (click to filter) --}}
        <div class="sam-cards">
            <a class="sam-card {{ $filters['status'] === 'all' ? 'on' : '' }}"
                href="{{ route('school-activity.index', $q(['status' => 'all'])) }}">
                <div class="n">{{ $overview['total'] }}</div>
                <div class="l">All schools</div>
            </a>
            @foreach(['active' => '--sam-ok', 'idle' => '--sam-warn', 'dormant' => '--sam-bad', 'never' => '--sam-bad', 'new' => '--sam-brand', 'not_live' => '--sam-mute'] as $key => $color)
                <a class="sam-card {{ $filters['status'] === $key ? 'on' : '' }}"
                    href="{{ route('school-activity.index', $q(['status' => $key])) }}">
                    <div class="n" style="color:var({{ $color }})">{{ $overview['counts'][$key] }}</div>
                    <div class="l">{{ $statusLabels[$key] }}</div>
                </a>
            @endforeach
            <div class="sam-card" title="Live schools that were active or idle">
                <div class="n">{{ $overview['using_pct'] }}%</div>
                <div class="l">Live schools using it</div>
            </div>
        </div>

        <div class="sam-panel">
            <div class="hd">
                <form class="sam-filters" method="GET" action="{{ route('school-activity.index') }}">
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search school or code">
                    <select name="status">
                        <option value="all">All statuses</option>
                        @foreach($statusLabels as $k => $label)
                            <option value="{{ $k }}" @selected($filters['status'] === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="type">
                        <option value="">All types</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected($filters['type'] === $t)>
                                {{ \App\Http\Controllers\Helper::recordMdname($t) }}
                            </option>
                        @endforeach
                    </select>
                    <select name="usage">
                        <option value="">Module usage…</option>
                        <option value="unused" @selected($filters['usage'] === 'unused')>NOT using</option>
                        <option value="used" @selected($filters['usage'] === 'used')>Using</option>
                    </select>
                    <select name="module">
                        <option value="">…which module</option>
                        @foreach($modules as $k => $m)
                            <option value="{{ $k }}" @selected($filters['module'] === $k)>{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    <select name="sort">
                        <option value="attention" @selected($filters['sort'] === 'attention')>Needs attention first</option>
                        <option value="recent" @selected($filters['sort'] === 'recent')>Most recently active</option>
                        <option value="actions" @selected($filters['sort'] === 'actions')>Most actions</option>
                        <option value="name" @selected($filters['sort'] === 'name')>Name A–Z</option>
                    </select>
                    <button class="sam-btn" type="submit">Apply</button>
                    <a class="sam-btn ghost" href="{{ route('school-activity.index') }}">Reset</a>
                </form>
                <span class="sam-muted">{{ $schools->total() }} school(s)</span>
            </div>

            <div class="sam-wrap">
                <table class="sam-table">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Status</th>
                            <th>Last activity</th>
                            <th>Last sign-in</th>
                            <th>Users</th>
                            <th>Actions</th>
                            <th>Modules in use</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schools as $s)
                            <tr>
                                <td>
                                    <strong>{{ $s['name'] }}</strong>
                                    <div class="sam-muted">{{ $s['code'] }} · {{ number_format($s['students']) }} students ·
                                        {{ number_format($s['teachers']) }} staff
                                    </div>
                                </td>
                                <td><span class="sam-badge {{ $s['status'] }}">{{ $statusLabels[$s['status']] }}</span></td>
                                <td title="{{ $fmt($s['last_activity_at']) }}">{{ $ago($s['last_activity_at']) }}</td>
                                <td title="{{ $fmt($s['last_login_at']) }}">{{ $ago($s['last_login_at']) }}</td>
                                <td>{{ $s['active_users'] }}</td>
                                <td>{{ number_format($s['actions']) }}</td>
                                <td>
                                    <div class="sam-dots">
                                        @foreach($modules as $k => $m)
                                            <span class="sam-dot {{ ($s['modules'][$k]['recent'] ?? 0) > 0 ? 'on' : '' }}"
                                                title="{{ $m['label'] }}: {{ $s['modules'][$k]['recent'] }} action(s) in {{ $cfg['window_days'] }} days">
                                                <i class="fas {{ $m['icon'] }}"></i>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td><a href="{{ route('school-activity.show', $s['id']) }}">Details →</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center;padding:30px" class="sam-muted">No schools match these
                                    filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bd">{{ $schools->links() }}</div>
        </div>

        <div class="sam-muted">
            Counts reflect saved actions (records created, edited or deleted, marks and attendance entered, payments,
            sign-ins) by school users.
            Platform-admin actions are excluded. A school that only views pages without saving anything will not register as
            active.
        </div>
    </div>
@endsection
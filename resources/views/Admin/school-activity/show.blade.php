@extends('layouts-side-bar.master')

@section('content')
@include('Admin.school-activity._styles')

@php
    $fmt = fn ($c) => $c ? $c->format('d M Y, H:i') : '—';
    $ago = fn ($c) => $c ? $c->diffForHumans() : 'Never';
    $max = max(1, collect($trend)->max('count'));
    $canAudit = \App\Audit\AuditAccess::isPlatformAdmin();
@endphp

<style>
    table thead th {
        background-color: #2C29CA !important;
        color: #FFFFFF !important;
    }

    /* ── Card grid: 3 per row on large screens ── */
    .sam-cards {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 14px !important;
        margin-bottom: 20px;
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
        padding: 16px 18px;
        text-decoration: none;
        color: inherit;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        box-shadow: 0 1px 2px rgba(16,24,40,.04);
        display: flex;
        flex-direction: column;
        justify-content: center;
        animation: samCardIn .35s ease both;
    }
    .sam-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(44,41,202,.12);
        border-color: var(--sam-brand, #2C29CA);
    }
    .sam-card .n {
        font-weight: 700;
        line-height: 1.15;
        color: #111827;
        transition: color .18s ease;
    }
    .sam-card:hover .n { color: var(--sam-brand, #2C29CA); }
    .sam-card .l {
        margin-top: 4px;
        font-size: .82rem;
        color: #6b7280;
        font-weight: 500;
    }

    @keyframes samCardIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Staggered entrance */
    .sam-cards .sam-card:nth-child(1) { animation-delay: .02s; }
    .sam-cards .sam-card:nth-child(2) { animation-delay: .06s; }
    .sam-cards .sam-card:nth-child(3) { animation-delay: .10s; }
    .sam-cards .sam-card:nth-child(4) { animation-delay: .14s; }
    .sam-cards .sam-card:nth-child(5) { animation-delay: .18s; }
    .sam-cards .sam-card:nth-child(6) { animation-delay: .22s; }

    /* Accent color per card via modifier class */
    .sam-card.accent-ok   { border-left: 4px solid var(--sam-ok, #16a34a); }
    .sam-card.accent-warn { border-left: 4px solid var(--sam-warn, #f59e0b); }
    .sam-card.accent-bad  { border-left: 4px solid var(--sam-bad, #dc2626); }
    .sam-card.accent-info { border-left: 4px solid var(--sam-brand, #2C29CA); }
    .sam-card.accent-mute { border-left: 4px solid #9ca3af; }

    .sam-card.accent-ok:hover   { box-shadow: 0 8px 22px rgba(22,163,74,.15); }
    .sam-card.accent-warn:hover { box-shadow: 0 8px 22px rgba(245,158,11,.15); }
    .sam-card.accent-bad:hover  { box-shadow: 0 8px 22px rgba(220,38,38,.15); }
    .sam-card.accent-info:hover { box-shadow: 0 8px 22px rgba(44,41,202,.15); }
</style>

<div class="sam">
    <a href="{{ route('school-activity.index') }}" class="sam-muted">← All schools</a>

    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin:6px 0 16px">
        <div>
            <h1>{{ $school['name'] }}</h1>
            <div class="sub" style="margin-bottom:0">
                {{ $school['code'] }} · {{ \App\Http\Controllers\Helper::recordMdname($school['school_type']) }}
                · registered {{ $school['created_at'] ? $school['created_at']->format('d M Y') : '—' }}
            </div>
        </div>
        <span class="sam-badge {{ $school['status'] }}" style="font-size:.85rem;padding:5px 14px">{{ $statusLabels[$school['status']] }}</span>
    </div>

    <div class="sam-cards">
        <div class="sam-card accent-info">
            <div class="n" style="font-size:1.05rem">{{ $ago($school['last_activity_at']) }}</div>
            <div class="l">Last activity</div>
            <div class="sam-muted" style="font-size:.75rem">{{ $fmt($school['last_activity_at']) }}</div>
        </div>
        <div class="sam-card accent-info">
            <div class="n" style="font-size:1.05rem">{{ $ago($school['last_login_at']) }}</div>
            <div class="l">Last sign-in ({{ $cfg['window_days'] }}d)</div>
            <div class="sam-muted" style="font-size:.75rem">{{ $fmt($school['last_login_at']) }}</div>
        </div>
        <div class="sam-card accent-ok">
            <div class="n">{{ $school['active_users'] }}</div>
            <div class="l">Active users</div>
            <div class="sam-muted" style="font-size:.75rem">of {{ $school['teachers'] }} staff</div>
        </div>
        <div class="sam-card accent-warn">
            <div class="n">{{ number_format($school['actions']) }}</div>
            <div class="l">Actions ({{ $cfg['window_days'] }}d)</div>
        </div>
        <div class="sam-card accent-info">
            <div class="n">{{ $school['modules_used'] }}/{{ count($modules) }}</div>
            <div class="l">Modules in use</div>
        </div>
        <div class="sam-card accent-ok">
            <div class="n">{{ number_format($school['students']) }}</div>
            <div class="l">Students enrolled</div>
        </div>
    </div>

    <div class="sam-grid2">
        <div>
            <div class="sam-panel">
                <div class="hd">Module usage — last {{ $cfg['window_days'] }} days</div>
                <div class="sam-wrap">
                    <table class="sam-table">
                        <thead><tr><th>Module</th><th>Actions</th><th>Last used</th><th>State</th></tr></thead>
                        <tbody>
                        @foreach($modules as $k => $m)
                            @php $mm = $school['modules'][$k]; @endphp
                            <tr>
                                <td><i class="fas {{ $m['icon'] }}" style="color:var(--sam-brand);width:20px"></i> {{ $m['label'] }}</td>
                                <td>{{ number_format($mm['recent']) }}</td>
                                <td title="{{ $fmt($mm['last_at']) }}">{{ $ago($mm['last_at']) }}</td>
                                <td>
                                    @if($mm['recent'] > 0)<span class="sam-badge active">In use</span>
                                    @else<span class="sam-badge never">Not used</span>@endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="sam-panel">
                <div class="hd">Daily actions — last {{ count($trend) }} days</div>
                <div class="bd">
                    <div class="sam-spark">
                        @foreach($trend as $d)
                            <span class="{{ $d['count'] === 0 ? 'z' : '' }}"
                                  style="height:{{ $d['count'] ? max(4, round($d['count'] / $max * 100)) : 2 }}%"
                                  title="{{ \Carbon\Carbon::parse($d['date'])->format('d M') }}: {{ $d['count'] }}"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="sam-panel">
                <div class="hd">Contact</div>
                <div class="bd" style="font-size:.88rem">
                    <div><i class="fas fa-envelope sam-muted"></i> {{ $school['email'] ?: '—' }}</div>
                    <div style="margin-top:6px"><i class="fas fa-phone sam-muted"></i> {{ $school['phone'] ?: '—' }}</div>
                    @if(in_array($school['status'], ['dormant', 'never', 'idle']))
                        <div class="sam-muted" style="margin-top:12px">
                            Suggested follow-up: reach out to this school to find out whether they need training or support.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="sam-panel">
        <div class="hd">Latest school-user activity</div>
        <div class="sam-wrap">
            <table class="sam-table">
                <thead><tr><th>When</th><th>Who</th><th>What</th>@if($canAudit)<th></th>@endif</tr></thead>
                <tbody>
                @forelse($events as $e)
                    <tr>
                        <td title="{{ $fmt($e->occurred_at) }}">{{ $ago($e->occurred_at) }}</td>
                        <td>{{ $e->actor_name ?: '—' }}<div class="sam-muted">{{ $e->actor_role }}</div></td>
                        <td>{{ \Illuminate\Support\Str::limit($e->description, 110) }}</td>
                        @if($canAudit)<td><a href="{{ route('audit.show', $e->id) }}">Audit entry</a></td>@endif
                    </tr>
                @empty
                    <tr><td colspan="4" class="sam-muted" style="text-align:center;padding:24px">No school-user activity has ever been recorded for this school.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
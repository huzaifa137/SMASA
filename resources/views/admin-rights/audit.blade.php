@extends('layouts-side-bar.master')

@section('css')
    @include('admin-rights._styles')
@endsection

@section('content')
    @include('admin-rights._nav', ['arpActive' => 'audit'])

    <style>
/* ============================================================
   AUDIT LOG PAGE ENHANCEMENTS
   ============================================================ */

.arp-audit-card { overflow: hidden; position: relative; }

/* ---------- Toolbar ---------- */
.arp-audit-toolbar {
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
    padding-bottom: 8px;
}

.arp-audit-filter {
    position: relative;
    flex: 1 1 260px;
    max-width: 340px;
}
.arp-audit-filter select {
    width: 100%;
    appearance: none;
    padding-right: 38px !important;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
}
.arp-audit-filter select:focus {
    border-color: #2C29CA;
    box-shadow: 0 0 0 4px rgba(44, 41, 202, .12);
    outline: none;
}
.arp-audit-filter::after {
    content: '\f107';
    font-family: 'Font Awesome 5 Free', 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9aa3b2;
    pointer-events: none;
    font-size: 12px;
    transition: color .2s;
}
.arp-audit-filter:focus-within::after { color: #2C29CA; }

.arp-audit-count {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 14px;
    border-radius: 999px;
    background: #eef0ff;
    color: #2C29CA;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #dfe2ff;
}
.arp-audit-count i { font-size: 11px; }

/* ---------- Stats strip ---------- */
.arp-stats-strip {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin: 14px 0 6px;
    margin-bottom: 1rem;
}
.arp-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 12px;
    background: #f6f8fc;
    border: 1px solid #e6ebf4;
    flex: 1 1 140px;
    transition: transform .2s, box-shadow .2s, border-color .2s;
}
.arp-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(28, 43, 82, .08);
    border-color: #cfd9ec;
}
.arp-stat-icon {
    width: 36px; height: 36px;
    display: grid; place-items: center;
    border-radius: 10px;
    background: #e6e5fb;
    color: #2C29CA;
    font-size: 15px;
    flex-shrink: 0;
}
.arp-stat-icon.green  { background: #e3f7ea; color: #1a9d54; }
.arp-stat-icon.amber  { background: #fff4d6; color: #d99a00; }
.arp-stat-icon.red    { background: #fdecec; color: #c53030; }
.arp-stat-value { font-size: 18px; font-weight: 700; color: #1c2b52; line-height: 1.1; }
.arp-stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #8a94a6; }

/* ---------- Table header ---------- */
.arp-table-audit thead th {
    background: #2C29CA !important;
    color: #ffffff !important;
    font-weight: 600;
    letter-spacing: .02em;
}
.arp-table-audit thead th:first-child { border-top-left-radius: 10px; }
.arp-table-audit thead th:last-child  { border-top-right-radius: 10px; }

/* ---------- Rows ---------- */
.arp-table-audit tbody tr.arp-row {
    transition: background .2s, box-shadow .2s;
    position: relative;
    animation: arpRowIn .35s ease both;
}
.arp-table-audit tbody tr.arp-row:hover {
    background: #f8faff;
    box-shadow: inset 3px 0 0 #2C29CA;
}
.arp-table-audit tbody tr.arp-row:nth-child(1)  { animation-delay: .02s; }
.arp-table-audit tbody tr.arp-row:nth-child(2)  { animation-delay: .05s; }
.arp-table-audit tbody tr.arp-row:nth-child(3)  { animation-delay: .08s; }
.arp-table-audit tbody tr.arp-row:nth-child(4)  { animation-delay: .11s; }
.arp-table-audit tbody tr.arp-row:nth-child(5)  { animation-delay: .14s; }
.arp-table-audit tbody tr.arp-row:nth-child(6)  { animation-delay: .17s; }
.arp-table-audit tbody tr.arp-row:nth-child(7)  { animation-delay: .20s; }
.arp-table-audit tbody tr.arp-row:nth-child(8)  { animation-delay: .23s; }
@keyframes arpRowIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ---------- Cells ---------- */
.arp-audit-when {
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
    color: #4a5568;
    font-size: 13px;
}
.arp-audit-when .arp-time {
    font-weight: 600;
    color: #1c2b52;
    display: block;
}
.arp-audit-when .arp-date {
    font-size: 11px;
    color: #8a94a6;
}

.arp-actor {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    color: #1c2b52;
}
.arp-actor-badge {
    width: 26px; height: 26px;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #6f6dff, #2C29CA);
    flex-shrink: 0;
}
.arp-actor-badge.system {
    background: linear-gradient(135deg, #9aa3b2, #6b7280);
}
.arp-actor-badge.system i { font-size: 10px; }

/* ---------- Action badges (color-coded) ---------- */
.arp-action-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
    white-space: nowrap;
}
.arp-action-badge i { font-size: 10px; }
.arp-action-badge.role      { background: #f1e9fe; color: #7a2fd1; border: 1px solid #e2d1fb; }
.arp-action-badge.user      { background: #e8f0fe; color: #1a56c4; border: 1px solid #cdddfb; }
.arp-action-badge.super     { background: #fdf0d1; color: #9a6700; border: 1px solid #f6dfa5; }
.arp-action-badge.danger    { background: #fdeaea; color: #b91c1c; border: 1px solid #f9c9c9; }
.arp-action-badge.success   { background: #e0f5f0; color: #0e7c66; border: 1px solid #bce9de; }
.arp-action-badge.neutral   { background: #eef1f6; color: #4a5568; border: 1px solid #dfe5ee; }

/* ---------- Details / changes disclosure ---------- */
.arp-audit-details { color: #4a5568; font-size: 13px; }
.arp-audit-details p { margin: 0 0 4px; }

.arp-changes {
    margin-top: 6px;
    border-radius: 8px;
    border: 1px solid #e6ebf4;
    background: #fafbfe;
    overflow: hidden;
}
.arp-changes summary {
    cursor: pointer;
    list-style: none;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 600;
    color: #2C29CA;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    user-select: none;
    transition: background .2s;
}
.arp-changes summary::-webkit-details-marker { display: none; }
.arp-changes summary::before {
    content: '\f054';
    font-family: 'Font Awesome 5 Free', 'Font Awesome 6 Free';
    font-weight: 900;
    font-size: 9px;
    transition: transform .2s;
}
.arp-changes[open] summary::before { transform: rotate(90deg); }
.arp-changes summary:hover { background: #eef0ff; }

.arp-changes-body {
    padding: 6px 12px 10px;
    display: flex;
    flex-direction: column;
    gap: 3px;
    font-size: 12px;
    border-top: 1px solid #e6ebf4;
}
.arp-change-line { display: flex; align-items: center; gap: 6px; }
.arp-change-line.added   { color: #059669; }
.arp-change-line.removed { color: #dc2626; }
.arp-change-line .arp-change-mark {
    width: 16px; height: 16px;
    display: grid; place-items: center;
    border-radius: 4px;
    font-weight: 700;
    font-size: 10px;
}
.arp-change-line.added   .arp-change-mark { background: #dcfce7; }
.arp-change-line.removed .arp-change-mark { background: #fee2e2; }

/* ---------- IP cell ---------- */
.arp-ip {
    font-family: 'SFMono-Regular', Menlo, Consolas, monospace;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 6px;
    background: #f1f4fa;
    color: #4a5568;
    border: 1px solid #e6ebf4;
}

/* ---------- Empty state ---------- */
.arp-empty { padding: 40px 16px !important; }
.arp-empty-state {
    display: flex; flex-direction: column; align-items: center;
    gap: 10px; color: #8a94a6;
}
.arp-empty-state i { font-size: 34px; opacity: .5; }
.arp-empty-state p { margin: 0; }

/* ---------- Pagination polish ---------- */
.arp-audit-card .pagination { margin: 0; gap: 4px; }
.arp-audit-card .pagination .page-link {
    border-radius: 8px !important;
    border: 1px solid #e6ebf4;
    color: #4a5568;
    transition: background .2s, color .2s, border-color .2s, transform .15s;
}
.arp-audit-card .pagination .page-link:hover {
    background: #eef0ff;
    border-color: #2C29CA;
    color: #2C29CA;
    transform: translateY(-1px);
}
.arp-audit-card .pagination .page-item.active .page-link {
    background: #2C29CA;
    border-color: #2C29CA;
    color: #fff;
    box-shadow: 0 4px 12px rgba(44, 41, 202, .3);
}

/* ---------- Responsive ---------- */
@media (max-width: 720px) {
    .arp-audit-toolbar { flex-direction: column; align-items: stretch; }
    .arp-audit-filter { max-width: none; }
    .arp-stats-strip { flex-direction: column; }
    .arp-stat { flex: 1 1 100%; }
}
    </style>

    <div class="arp-card arp-audit-card">
        {{-- Toolbar --}}
        <div class="arp-toolbar arp-audit-toolbar">
            <form method="GET" class="d-flex gap-2 align-items-center">
                <div class="arp-audit-filter">
                    <select name="action" class="arp-input" onchange="this.form.submit()">
                        <option value="">All activity</option>
                        @foreach (['role' => 'Roles', 'user' => 'Admin users', 'super_admin' => 'Super administrators'] as $k => $label)
                            <option value="{{ $k }}" @selected($action === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <span class="arp-audit-count">
                <i class="fa fa-list-ul"></i>
                {{ number_format($logs->total()) }} {{ Str::plural('entry', $logs->total()) }}
            </span>
        </div>

        {{-- Stats strip --}}
        @php
            $addedCount   = collect($logs->items())->sum(fn ($l) => count($l->meta['added'] ?? []));
            $removedCount = collect($logs->items())->sum(fn ($l) => count($l->meta['removed'] ?? []));
            $uniqueActors = collect($logs->items())->pluck('actor.username')->filter()->unique()->count();
        @endphp
        <div class="arp-stats-strip">
            <div class="arp-stat">
                <span class="arp-stat-icon"><i class="fa fa-history"></i></span>
                <div>
                    <div class="arp-stat-value">{{ number_format($logs->total()) }}</div>
                    <div class="arp-stat-label text-dark">Total entries</div>
                </div>
            </div>
            <div class="arp-stat">
                <span class="arp-stat-icon green"><i class="fa fa-user-check"></i></span>
                <div>
                    <div class="arp-stat-value">{{ $uniqueActors }}</div>
                    <div class="arp-stat-label text-dark">Actors on page</div>
                </div>
            </div>
            <div class="arp-stat">
                <span class="arp-stat-icon amber"><i class="fa fa-plus-circle"></i></span>
                <div>
                    <div class="arp-stat-value">{{ $addedCount }}</div>
                    <div class="arp-stat-label text-dark">Rights added</div>
                </div>
            </div>
            <div class="arp-stat">
                <span class="arp-stat-icon red"><i class="fa fa-minus-circle"></i></span>
                <div>
                    <div class="arp-stat-value">{{ $removedCount }}</div>
                    <div class="arp-stat-label text-dark">Rights removed</div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="arp-wrap">
            <table class="arp-table arp-table-audit">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Who</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($logs as $log)
                    @php
                        $actionKey = strtolower(str_replace([' ', '-'], '_', $log->action));
                        $actionClass = match (true) {
                            str_contains($actionKey, 'super')    => 'super',
                            str_contains($actionKey, 'role')     => 'role',
                            str_contains($actionKey, 'user'),
                            str_contains($actionKey, 'password') => 'user',
                            str_contains($actionKey, 'delete'),
                            str_contains($actionKey, 'deactivat')=> 'danger',
                            str_contains($actionKey, 'activat'),
                            str_contains($actionKey, 'create')   => 'success',
                            default                              => 'neutral',
                        };
                        $actorName = optional($log->actor)->username;
                        $actorInitial = $actorName ? strtoupper(mb_substr($actorName, 0, 1)) : null;
                    @endphp
                    <tr class="arp-row">
                        <td>
                            <div class="arp-audit-when">
                                <span class="arp-time">{{ $log->created_at?->format('H:i') ?? '—' }}</span>
                                <span class="arp-date">{{ $log->created_at?->format('d M Y') ?? '' }}</span>
                            </div>
                        </td>
                        <td>
                            @if ($actorName)
                                <span class="arp-actor">
                                    <span class="arp-actor-badge">{{ $actorInitial }}</span>
                                    {{ $actorName }}
                                </span>
                            @else
                                <span class="arp-actor">
                                    <span class="arp-actor-badge system"><i class="fa fa-terminal"></i></span>
                                    <span class="arp-muted">system / console</span>
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="arp-action-badge {{ $actionClass }}">
                                <i class="fa fa-circle" style="font-size:6px"></i>
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>
                            <div class="arp-audit-details">
                                {{ $log->description }}
                                @if (!empty($log->meta['added']) || !empty($log->meta['removed']))
                                    <details class="arp-changes">
                                        <summary>View changes</summary>
                                        <div class="arp-changes-body">
                                            @foreach (($log->meta['added'] ?? []) as $k)
                                                <div class="arp-change-line added">
                                                    <span class="arp-change-mark">+</span>{{ $k }}
                                                </div>
                                            @endforeach
                                            @foreach (($log->meta['removed'] ?? []) as $k)
                                                <div class="arp-change-line removed">
                                                    <span class="arp-change-mark">−</span>{{ $k }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if ($log->ip_address)
                                <code class="arp-ip">{{ $log->ip_address }}</code>
                            @else
                                <span class="arp-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="arp-empty">
                            <div class="arp-empty-state">
                                <i class="fa fa-clock-rotate-left"></i>
                                <p>No activity recorded yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="mt-3">{{ $logs->links('pagination::bootstrap-4') }}</div>
        @endif
    </div></div></div>
@endsection
<?php use App\Http\Controllers\Helper; use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --brand: #5351e4; --brand-light: #2C29CA;
        --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
        --text-primary: #1e293b; --text-secondary: #475569; --text-muted: #94a3b8;
        --border: #e2e8f0;
    }

    /* RPT Hero Card Styles */
    .rpt-hero-card {
        background: linear-gradient(135deg, #000000 0%, #070189 100%);
        border-radius: 20px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 24px rgba(7, 1, 137, 0.3);
    }

    .rpt-hero-main {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .rpt-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .rpt-hero-left {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
    }

    .rpt-hero-icon-wrapper {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        margin-top: 0.25rem;
    }

    .rpt-hero-info {
        flex: 1;
    }

    .rpt-hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .rpt-hero-badge {
        display: inline-flex;
        align-items: center;
        backdrop-filter: blur(4px);
        padding: 0.4rem 1rem;
        border-radius: 99px;
        font-size: 0.75rem;
        color: #FFF;
    }

    .rpt-hero-info h4 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 0.25rem;
    }

    .rpt-hero-info p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
        margin: 0;
    }

    .rpt-hero-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .rpt-hero-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 1.1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        backdrop-filter: blur(10px);
        text-align: center;
        white-space: nowrap;
    }

    .rpt-hero-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        text-decoration: none;
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    .rpt-hero-meta {
        padding-top: 0.25rem;
    }

    .rpt-meta-items {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.5rem 0.75rem;
        width: 100%;
    }

    .rpt-meta-item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        color: rgba(255, 255, 255, 0.65);
        font-size: 0.78rem;
        padding: 0.35rem 0.75rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 99px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        text-align: center;
        transition: all 0.3s ease;
    }

    .rpt-meta-item:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-1px);
    }

    .rpt-meta-item i {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.4);
    }

    .rpt-meta-highlight {
        background: rgba(102, 126, 234, 0.15);
        border-color: rgba(102, 126, 234, 0.2);
        color: rgba(255, 255, 255, 0.9);
    }

    .rpt-meta-highlight i {
        color: #818cf8;
    }

    .filter-card { background:white; border-radius:20px; padding:1.4rem 1.6rem; margin-bottom:1.5rem; box-shadow:0 4px 24px rgba(0,0,0,0.07); border:1px solid rgba(83,81,228,0.08); }
    .filter-group-label { font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--brand); margin-bottom:0.6rem; }

    .day-tabs { display:flex; flex-wrap:wrap; gap:0.5rem; }
    .day-tab {
        padding:0.5rem 1.1rem; border-radius:99px; font-size:0.8rem; font-weight:700;
        text-decoration:none; color:var(--text-secondary); background:#f5f5fb; border:1.5px solid transparent;
        transition:all .15s;
    }
    .day-tab:hover { background:#ede9ff; color:var(--brand-light); text-decoration:none; }
    .day-tab.active { background:var(--brand); color:white; box-shadow:0 4px 12px rgba(83,81,228,0.3); }

    .class-chip {
        display:inline-flex; align-items:center; gap:0.4rem; padding:0.45rem 0.9rem;
        border-radius:10px; border:1.5px solid #e2e8f0; background:#fafbff; cursor:pointer;
        font-size:0.78rem; font-weight:600; color:var(--text-secondary); transition:all .15s;
    }
    .class-chip:hover { border-color:#c4c0ff; }
    .class-chip.selected { background:#ede9ff; border-color:var(--brand); color:var(--brand-light); }
    .class-chip input { display:none; }

    .orientation-select {
        border:1.5px solid #e2e8f0; border-radius:10px; padding:0.5rem 0.9rem; font-size:0.82rem;
        font-weight:600; color:var(--text-primary); background:white;
    }

    .btn-apply {
        background:var(--brand); color:white; border:none; border-radius:10px; padding:0.55rem 1.4rem;
        font-size:0.82rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;
    }
    .btn-apply:hover { background:var(--brand-light); }

    .summary-strip { display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
    .summary-pill {
        flex:1; min-width:150px; background:white; border-radius:16px; padding:1rem 1.2rem;
        box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid rgba(83,81,228,0.08);
        display:flex; align-items:center; gap:0.8rem;
    }
    .summary-pill i { font-size:1.1rem; color:var(--brand); width:36px; height:36px; border-radius:10px; background:rgba(83,81,228,0.1); display:flex; align-items:center; justify-content:center; }
    .summary-pill-num { font-size:1.3rem; font-weight:800; color:var(--text-primary); line-height:1; }
    .summary-pill-label { font-size:0.7rem; color:var(--text-muted); font-weight:600; }

    .tt-container { background:white; border-radius:24px; box-shadow:0 4px 24px rgba(0,0,0,0.07); border:1px solid rgba(83,81,228,0.08); overflow:hidden; }
    .tt-wrapper { overflow-x:auto; }
    .tt-grid { display:grid; min-width:800px; }
    .tt-head-row, .tt-body-row { display:grid; }
    .tt-head-cell {
        background:#5351e4; color:white; padding:0.8rem 0.5rem; text-align:center;
        font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em;
        border-right:1px solid rgba(255,255,255,0.12);
    }
    .tt-head-cell:last-child { border-right:none; }
    .tt-head-cell.corner-col { background:#2C29CA; text-align:left; padding:0.8rem 1rem; }

    .tt-row-label {
        background:#f8fafc; padding:0.6rem 1rem;
        border-bottom:1px solid var(--border); border-right:1px solid var(--border);
        display:flex; flex-direction:column; justify-content:center; min-height:72px;
    }
    .row-label-name { font-size:0.75rem; font-weight:700; color:var(--text-primary); }
    .row-label-sub { font-size:0.63rem; color:var(--text-muted); margin-top:1px; }

    .tt-slot { border-bottom:1px solid var(--border); border-right:1px solid var(--border); min-height:72px; padding:6px; }
    .tt-slot:last-child { border-right:none; }
    .tt-body-row:last-child .tt-slot, .tt-body-row:last-child .tt-row-label { border-bottom:none; }

    .slot-chip { border-radius:10px; padding:0.4rem 0.55rem; height:100%; min-height:60px; display:flex; flex-direction:column; justify-content:center; gap:2px; color:white; }
    .slot-subject { font-size:0.74rem; font-weight:800; line-height:1.2; }
    .slot-teacher { font-size:0.63rem; opacity:0.85; margin-top:2px; }
    .slot-room { font-size:0.58rem; opacity:0.7; }

    .empty-cell { width:100%; height:100%; min-height:60px; display:flex; align-items:center; justify-content:center; }
    .empty-dash { color:var(--border); font-size:1.2rem; }

    .empty-state { text-align:center; padding:4rem 2rem; color:var(--text-muted); }
    .empty-state i { font-size:2.5rem; opacity:0.3; margin-bottom:1rem; display:block; }

    .print-btn { background:var(--brand); color:white; border:none; border-radius:12px; padding:0.6rem 1.4rem; font-size:0.85rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; }
    .print-btn:hover { background:var(--brand-light); }

    @media (max-width: 768px) {
        .rpt-hero-card { padding: 1.25rem; }
        .rpt-hero-top { flex-direction: column; align-items: flex-start; }
        .rpt-hero-left { flex-direction: column; align-items: flex-start; }
        .rpt-hero-info h4 { font-size: 1.2rem; }
        .rpt-hero-info p { font-size: 0.85rem; }
        .rpt-meta-items { grid-template-columns: 1fr 1fr; }
        .rpt-hero-badges { flex-wrap: wrap; }
        .rpt-hero-actions { width: 100%; }
        .rpt-hero-btn-secondary { flex: 1; justify-content: center; font-size: 0.75rem; padding: 0.5rem 0.8rem; }
    }

    @media (max-width: 480px) {
        .rpt-hero-left { width: 100%; }
        .rpt-hero-icon-wrapper { width: 48px; height: 48px; font-size: 1.2rem; }
        .rpt-meta-items { grid-template-columns: 1fr; }
        .rpt-hero-badge { font-size: 0.65rem !important; padding: 0.3rem 0.7rem !important; }
        .rpt-hero-actions { flex-direction: column; }
        .rpt-hero-btn-secondary { width: 100%; }
    }

    @media print {
        .rpt-hero-card, .filter-card, .print-btn, nav, aside, .summary-strip { display:none !important; }
        body { background:white !important; }
        .tt-container { box-shadow:none !important; border:none !important; }
    }
</style>
@endsection

@section('content')
<div class="side-app" style="padding: 1.5rem;">

    {{-- New Dark Gradient Header --}}
    <div class="rpt-hero-card" style="margin-bottom: 1.5rem;">
        <div class="rpt-hero-main" style="margin-bottom: 0.75rem; padding-bottom: 0.75rem;">
            <div class="rpt-hero-top">
                <div class="rpt-hero-left">
                    <div class="rpt-hero-icon-wrapper">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <div class="rpt-hero-info">
                        <div class="rpt-hero-badges" style="margin-bottom: 0.5rem;">
                            <span class="rpt-hero-badge" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(4px); padding: 0.4rem 1rem; border-radius: 99px; font-size: 0.75rem; color: #FFF; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-globe me-1"></i> General School Timetable
                            </span>
                        </div>
                        <h4 style="font-size: 1.5rem; font-weight: 800; color: #ffffff; margin-bottom: 0.25rem;">
                            <i class="fas fa-th-large me-3"></i> Master Timetable
                        </h4>
                        <p style="color: #ffffff; font-size: 0.95rem; margin: 0;">
                            Every active class, side by side, one day at a time.
                        </p>
                    </div>
                </div>
                <div class="rpt-hero-actions">
                    <a href="{{ route('timetable.dashboard') }}" class="rpt-hero-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
        <div class="rpt-hero-meta">
            <div class="rpt-meta-items">
                <div class="rpt-meta-item rpt-meta-highlight">
                    <i class="fas fa-globe"></i>
                    <span>General School Timetable</span>
                </div>
                <div class="rpt-meta-item" style="color: #ffffff !important;">
                    <i class="fas fa-th-large" style="color: #818CF8 !important;"></i>
                    <span style="#ffffff !important;">Master Timetable</span>
                </div>
                <div class="rpt-meta-item"style="color: #ffffff !important;">
                    <i class="fas fa-calendar-alt" style="color: #818CF8 !important;"></i>
                    <span style="#ffffff !important;">All Active Classes</span>
                </div>
                <div class="rpt-meta-item" style="color: #ffffff !important;">
                    <i class="fas fa-layer-group" style="color: #818CF8 !important;"></i>
                    <span>{{ $activeTimetables->count() }} Classes</span>
                </div>
            </div>
        </div>
    </div>

    @if($activeTimetables->isEmpty())
        <div class="tt-container">
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h5 style="color:var(--text-primary);font-weight:700;">No active timetables yet</h5>
                <p>Activate at least one class timetable to build the general school view.</p>
                <a href="{{ route('timetable.dashboard') }}" class="print-btn" style="display:inline-flex;text-decoration:none;margin-top:0.5rem;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    @else
    <form method="GET" action="{{ route('timetable.master') }}" id="masterFilterForm">
        <div class="filter-card">
            <div class="row">
                <div class="col-lg-7">
                    <div class="filter-group-label"><i class="fas fa-layer-group me-1"></i> Classes to include ({{ count($selectedIds) }} of {{ $activeTimetables->count() }} selected)</div>
                    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;max-height:140px;overflow-y:auto;">
                        @foreach($activeTimetables as $tt)
                            <label class="class-chip {{ in_array($tt->id, $selectedIds) ? 'selected' : '' }}">
                                <input type="checkbox" name="timetables[]" value="{{ $tt->id }}" {{ in_array($tt->id, $selectedIds) ? 'checked' : '' }} onchange="this.closest('label').classList.toggle('selected', this.checked)">
                                <i class="fas fa-check" style="{{ in_array($tt->id, $selectedIds) ? '' : 'display:none;' }}"></i>
                                {{ $tt->label }}
                            </label>
                        @endforeach
                    </div>
                    <div style="margin-top:0.6rem;">
                        <a href="#" onclick="event.preventDefault();document.querySelectorAll('.class-chip input').forEach(c=>{c.checked=true;c.closest('label').classList.add('selected');});" style="font-size:0.75rem;color:var(--brand);font-weight:600;text-decoration:none;">Select all</a>
                        &nbsp;·&nbsp;
                        <a href="#" onclick="event.preventDefault();document.querySelectorAll('.class-chip input').forEach(c=>{c.checked=false;c.closest('label').classList.remove('selected');});" style="font-size:0.75rem;color:var(--text-muted);font-weight:600;text-decoration:none;">Clear</a>
                    </div>
                </div>
                <div class="col-lg-3 mt-3 mt-lg-0">
                    <div class="filter-group-label"><i class="fas fa-arrows-alt-v me-1"></i> Layout</div>
                    <select name="orientation" class="orientation-select" style="width:100%;">
                        <option value="periods_vertical" {{ $orientation === 'periods_vertical' ? 'selected' : '' }}>Periods vertical (classes across top)</option>
                        <option value="classes_vertical" {{ $orientation === 'classes_vertical' ? 'selected' : '' }}>Classes vertical (periods across top)</option>
                    </select>
                    <input type="hidden" name="day" value="{{ $day }}" id="dayInput">
                </div>
                <div class="col-lg-2 mt-3 mt-lg-0 d-flex align-items-end">
                    <button type="submit" class="btn-apply" style="width:100%;justify-content:center;">
                        <i class="fas fa-check"></i> Apply
                    </button>
                </div>
            </div>

            <div style="margin-top:1.2rem;padding-top:1.2rem;border-top:1px solid var(--border);">
                <div class="filter-group-label"><i class="fas fa-calendar-day me-1"></i> Day</div>
                <div class="day-tabs">
                    @foreach($days as $dayNum => $dayName)
                        <a href="#" class="day-tab {{ $day == $dayNum ? 'active' : '' }}" onclick="event.preventDefault();document.getElementById('dayInput').value={{ $dayNum }};document.getElementById('masterFilterForm').submit();">{{ $dayName }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </form>

    @php
        $lessonSlotsToday = $slots->filter(function ($s) use ($periods) {
            $p = $periods->firstWhere('id', $s->period_id);
            return $p && $p->type === 'lesson' && $s->subject_id;
        });
        $teachersOnToday = $lessonSlotsToday->pluck('teacher_id')->filter()->unique()->count();
    @endphp

    <div class="summary-strip">
        <div class="summary-pill">
            <i class="fas fa-layer-group"></i>
            <div><div class="summary-pill-num">{{ $selectedTimetables->count() }}</div><div class="summary-pill-label">Classes shown</div></div>
        </div>
        <div class="summary-pill">
            <i class="fas fa-book-open"></i>
            <div><div class="summary-pill-num">{{ $lessonSlotsToday->count() }}</div><div class="summary-pill-label">Lessons on {{ $days[$day] }}</div></div>
        </div>
        <div class="summary-pill">
            <i class="fas fa-user-tie"></i>
            <div><div class="summary-pill-num">{{ $teachersOnToday }}</div><div class="summary-pill-label">Teachers active</div></div>
        </div>
        <div class="summary-pill">
            <i class="fas fa-clock"></i>
            <div><div class="summary-pill-num">{{ $periods->count() }}</div><div class="summary-pill-label">Periods / day</div></div>
        </div>
    </div>

    <div class="tt-container">
        <div style="padding:1.2rem 1.6rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
            <div>
                <h5 style="font-weight:800;color:var(--text-primary);margin:0;">{{ $days[$day] }} — All Selected Classes</h5>
                <p style="font-size:0.8rem;color:var(--text-muted);margin:0;">{{ $orientation === 'periods_vertical' ? 'Periods vertical, classes across the top' : 'Classes vertical, periods across the top' }}</p>
            </div>
            <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>

        @if($selectedTimetables->isEmpty())
            <div class="empty-state">
                <i class="fas fa-filter"></i>
                <p>No classes selected. Pick at least one class above and click Apply.</p>
            </div>
        @else
        <div class="tt-wrapper">
            <div class="tt-grid">
                @if($orientation === 'periods_vertical')
                    {{-- Rows = periods, Columns = selected classes --}}
                    <div class="tt-head-row" style="grid-template-columns:140px repeat({{ $selectedTimetables->count() }},1fr);">
                        <div class="tt-head-cell corner-col"><i class="fas fa-clock me-1"></i> Period</div>
                        @foreach($selectedTimetables as $tt)
                            <div class="tt-head-cell">{{ $tt->label }}</div>
                        @endforeach
                    </div>

                    @foreach($periods as $period)
                    @php $isLesson = $period->type === 'lesson'; @endphp
                    <div class="tt-body-row" style="grid-template-columns:140px repeat({{ $selectedTimetables->count() }},1fr);">
                        <div class="tt-row-label">
                            <div class="row-label-name">{{ $period->name }}</div>
                            <div class="row-label-sub">{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</div>
                        </div>
                        @foreach($selectedTimetables as $tt)
                            @php $slot = $slots[$tt->id . '_' . $period->id] ?? null; @endphp
                            <div class="tt-slot">
                                @if($slot && $slot->subject_id)
                                    @php
                                        $color = $slot->color ?? '#5351e4';
                                        $subjName = Helper::recordMdname($slot->subject_id);
                                        $teacherName = $slot->teacher_id ? Helper::get_teacher_name($slot->teacher_id) : null;
                                    @endphp
                                    <div class="slot-chip" style="background:{{ $color }};">
                                        <div class="slot-subject">{{ $subjName }}</div>
                                        @if($teacherName)<div class="slot-teacher"><i class="fas fa-user-tie" style="font-size:0.5rem;"></i> {{ $teacherName }}</div>@endif
                                        @if($slot->room)<div class="slot-room"><i class="fas fa-map-marker-alt" style="font-size:0.45rem;"></i> {{ $slot->room }}</div>@endif
                                    </div>
                                @elseif($isLesson)
                                    <div class="empty-cell"><span class="empty-dash">—</span></div>
                                @else
                                    <div class="empty-cell" style="color:var(--text-muted);font-size:0.65rem;">{{ ucfirst($period->type) }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @endforeach
                @else
                    {{-- Rows = selected classes, Columns = periods --}}
                    <div class="tt-head-row" style="grid-template-columns:160px repeat({{ $periods->count() }},1fr);">
                        <div class="tt-head-cell corner-col"><i class="fas fa-layer-group me-1"></i> Class</div>
                        @foreach($periods as $period)
                            <div class="tt-head-cell">{{ $period->name }}</div>
                        @endforeach
                    </div>

                    @foreach($selectedTimetables as $tt)
                    <div class="tt-body-row" style="grid-template-columns:160px repeat({{ $periods->count() }},1fr);">
                        <div class="tt-row-label">
                            <div class="row-label-name">{{ $tt->label }}</div>
                        </div>
                        @foreach($periods as $period)
                            @php $isLesson = $period->type === 'lesson'; $slot = $slots[$tt->id . '_' . $period->id] ?? null; @endphp
                            <div class="tt-slot">
                                @if($slot && $slot->subject_id)
                                    @php
                                        $color = $slot->color ?? '#5351e4';
                                        $subjName = Helper::recordMdname($slot->subject_id);
                                        $teacherName = $slot->teacher_id ? Helper::get_teacher_name($slot->teacher_id) : null;
                                    @endphp
                                    <div class="slot-chip" style="background:{{ $color }};">
                                        <div class="slot-subject">{{ $subjName }}</div>
                                        @if($teacherName)<div class="slot-teacher"><i class="fas fa-user-tie" style="font-size:0.5rem;"></i> {{ $teacherName }}</div>@endif
                                        @if($slot->room)<div class="slot-room"><i class="fas fa-map-marker-alt" style="font-size:0.45rem;"></i> {{ $slot->room }}</div>@endif
                                    </div>
                                @elseif($isLesson)
                                    <div class="empty-cell"><span class="empty-dash">—</span></div>
                                @else
                                    <div class="empty-cell" style="color:var(--text-muted);font-size:0.65rem;">{{ ucfirst($period->type) }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
 </div>
    </div>
@endsection